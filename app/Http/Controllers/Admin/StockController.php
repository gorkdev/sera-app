<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Party;
use App\Models\PartyStock;
use App\Models\Product;
use App\Models\WasteLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        $partyId = $request->get('partyId') ? (int) $request->get('partyId') : ($request->get('party_id') ? (int) $request->get('party_id') : null);
        return view('admin.stocks.index', compact('partyId'));
    }

    public function create(Request $request): View
    {
        $partyId = $request->get('party_id') ? (int) $request->get('party_id') : null;
        $categoryId = $request->get('category_id');
        
        $parties = Party::whereIn('status', ['draft', 'active'])
            ->orderByDesc('created_at')
            ->get();
        
        $categories = \App\Models\Category::where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        
        $productsQuery = Product::where('is_active', true);
        if ($categoryId) {
            $productsQuery->where('category_id', $categoryId);
        }
        $products = $productsQuery->orderBy('name')->get();

        return view('admin.stocks.create', compact('parties', 'products', 'categories', 'partyId', 'categoryId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'party_id' => ['required', 'exists:parties,id'],
            'product_id' => ['required', 'exists:products,id'],
            'location' => ['nullable', 'string', 'max:255'],
            'total_quantity' => ['required', 'integer', 'min:0'],
        ], [
            'party_id.required' => 'Parti seçimi gerekli.',
            'product_id.required' => 'Ürün seçimi gerekli.',
            'total_quantity.required' => 'Stok miktarı gerekli.',
            'total_quantity.min' => 'Stok miktarı 0 veya daha büyük olmalı.',
        ]);

        // Aynı parti-ürün kombinasyonu zaten var mı kontrol et
        $existing = PartyStock::where('party_id', $validated['party_id'])
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($existing) {
            return back()
                ->withInput()
                ->with('error', 'Bu parti için bu ürünün stoku zaten tanımlı. Düzenleme sayfasından güncelleyin.');
        }

        $stock = PartyStock::create($validated);
        
        // Tazelik skorunu hesapla ve kaydet
        $stock->load(['party', 'product']);
        $stock->freshness_score = $stock->calculateFreshnessScore();
        $stock->save();

        return redirect()->route('admin.stocks.index', ['partyId' => $validated['party_id']])
            ->with('success', 'Stok başarıyla eklendi.');
    }

    public function edit(PartyStock $stock): View
    {
        $stock->load(['party', 'product', 'wasteLogs.recordedByAdmin']);
        return view('admin.stocks.edit', compact('stock'));
    }

    public function update(Request $request, PartyStock $stock): RedirectResponse
    {
        $validated = $request->validate([
            'location' => ['nullable', 'string', 'max:255'],
            'total_quantity' => ['required', 'integer', 'min:0'],
            'waste_quantity' => ['required', 'integer', 'min:0'],
        ], [
            'total_quantity.required' => 'Stok miktarı gerekli.',
            'total_quantity.min' => 'Stok miktarı 0 veya daha büyük olmalı.',
            'waste_quantity.required' => 'Çöp miktarı gerekli.',
            'waste_quantity.min' => 'Çöp miktarı 0 veya daha büyük olmalı.',
        ]);
        
        // Tazelik skorunu hesapla ve kaydet
        $stock->load(['party', 'product']);
        $validated['freshness_score'] = $stock->calculateFreshnessScore();

        // Rezerve edilmiş + satılmış + çöp miktardan az olamaz
        $minQuantity = $stock->reserved_quantity + $stock->sold_quantity + $validated['waste_quantity'];
        if ($validated['total_quantity'] < $minQuantity) {
            return back()
                ->withInput()
                ->with('error', "Toplam stok miktarı, rezerve edilmiş ({$stock->reserved_quantity}) + satılmış ({$stock->sold_quantity}) + çöp ({$validated['waste_quantity']}) miktardan az olamaz. Minimum: {$minQuantity}");
        }

        $stock->update($validated);

        return redirect()->route('admin.stocks.index', ['partyId' => $stock->party_id])
            ->with('success', 'Stok güncellendi.');
    }

    public function destroy(PartyStock $stock): RedirectResponse
    {
        // Rezerve veya satılmış stok varsa silinemez
        if ($stock->reserved_quantity > 0 || $stock->sold_quantity > 0) {
            return back()->with('error', 'Rezerve edilmiş veya satılmış stoku olan kayıt silinemez.');
        }

        $partyId = $stock->party_id;
        $stock->delete();

        return redirect()->route('admin.stocks.index', ['partyId' => $partyId])
            ->with('success', 'Stok silindi.');
    }

    public function addWaste(Request $request, PartyStock $stock): RedirectResponse
    {
        $validated = $request->validate([
            'waste_type' => ['required', 'in:pest,fungus,dehydration,breakage,expired'],
            'quantity' => ['required', 'integer', 'min:1'],
            'waste_date' => ['required', 'date'],
            'waste_time' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string'],
        ], [
            'waste_type.required' => 'Zayiat tipi gerekli.',
            'quantity.required' => 'Miktar gerekli.',
            'quantity.min' => 'Miktar en az 1 olmalı.',
            'waste_date.required' => 'Zayiat tarihi gerekli.',
            'waste_time.date_format' => 'Saat formatı geçersiz (HH:MM).',
        ]);

        // Miktar kontrolü
        $currentWaste = $stock->waste_quantity;
        $newTotalWaste = $currentWaste + $validated['quantity'];
        $maxWaste = $stock->total_quantity - $stock->reserved_quantity - $stock->sold_quantity;
        
        if ($newTotalWaste > $maxWaste) {
            return back()
                ->withInput()
                ->with('error', "Toplam zayiat miktarı mevcut stoktan fazla olamaz. Maksimum: {$maxWaste}");
        }

        // Tarih ve saati birleştir
        $wasteDateTime = \Carbon\Carbon::parse($validated['waste_date']);
        if (!empty($validated['waste_time'])) {
            $timeParts = explode(':', $validated['waste_time']);
            $wasteDateTime->setTime((int)$timeParts[0], (int)($timeParts[1] ?? 0));
        }

        // Parti geliş tarihinden kaç gün sonra?
        $daysSinceArrival = null;
        if ($stock->party->arrived_at) {
            // Eğer zayiat tarihi parti gelişinden önceyse null, değilse gün farkını hesapla
            if ($wasteDateTime->gte($stock->party->arrived_at)) {
                $daysSinceArrival = (int) abs($wasteDateTime->diffInDays($stock->party->arrived_at));
            }
            // Eğer önceyse null kalır (negatif değerler kaydedilmez)
        }

        WasteLog::create([
            'party_stock_id' => $stock->id,
            'waste_type' => $validated['waste_type'],
            'quantity' => $validated['quantity'],
            'waste_date' => $wasteDateTime,
            'days_since_party_arrival' => $daysSinceArrival,
            'recorded_by' => auth('admin')->id(),
            'notes' => $validated['notes'] ?? null,
        ]);

        // Stok waste_quantity güncelle
        $stock->increment('waste_quantity', $validated['quantity']);
        
        // Tazelik skorunu güncelle
        $stock->freshness_score = $stock->calculateFreshnessScore();
        $stock->save();

        return redirect()->route('admin.stocks.edit', $stock)
            ->with('success', 'Zayiat kaydı eklendi ve stok güncellendi.');
    }

    public function deleteWaste(WasteLog $wasteLog): RedirectResponse
    {
        $stock = $wasteLog->partyStock;
        $quantity = $wasteLog->quantity;

        // Stok waste_quantity'dan düş
        $stock->decrement('waste_quantity', $quantity);
        
        // Tazelik skorunu güncelle
        $stock->freshness_score = $stock->calculateFreshnessScore();
        $stock->save();

        // Zayiat kaydını sil
        $wasteLog->delete();

        return redirect()->route('admin.stocks.edit', $stock)
            ->with('success', 'Zayiat kaydı silindi ve stok güncellendi.');
    }
}
