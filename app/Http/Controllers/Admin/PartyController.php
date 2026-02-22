<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DealerGroup;
use App\Models\Party;
use App\Models\PartyStock;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PartyController extends Controller
{
    public function index(): View
    {
        return view('admin.parties.index');
    }

    public function create(): View
    {
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with([
                'children' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
                'activeProducts' => fn ($q) => $q->orderBy('name'),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $dealerGroups = DealerGroup::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.parties.create', compact('categories', 'dealerGroups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $closeWhenStock = (bool) $request->input('close_when_stock_runs_out', false);
        $visibleToAll = (bool) $request->input('visible_to_all', true);

        $rules = [
            'party_code' => ['nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9\-]*$/', 'unique:parties,party_code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'supplier_name' => ['required', 'string', 'max:255'],
            'truck_plate' => ['required', 'string', 'max:20'],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'driver_contact' => ['nullable', 'string', 'max:255'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],
            'truck_status' => ['nullable', 'string', 'in:not_departed,on_road,arrived'],
            'departure_at' => ['nullable', 'date', Rule::when($request->filled('arrived_at'), 'before:arrived_at')],
            'estimated_arrival_at' => ['nullable', 'date'],
            'arrived_at' => ['nullable', 'date', Rule::when($request->filled('departure_at'), 'after:departure_at')],
            'florist_delivery_at' => ['nullable', 'date', Rule::when($request->filled('arrived_at'), 'after:arrived_at')],
            'journey_days' => ['nullable', 'integer', 'min:0'],
            'purchase_price_per_unit' => ['required', 'numeric', 'min:0'],
            'logistics_cost' => ['required', 'numeric', 'min:0'],
            'customs_cost' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'starts_at' => ['required', 'date'],
            'ends_at' => $closeWhenStock ? ['nullable'] : ['required', 'date', 'after:starts_at'],
            'close_when_stock_runs_out' => ['nullable', 'boolean'],
            'visible_to_all' => ['nullable', 'boolean'],
            'dealer_group_ids' => ['nullable', 'array'],
            'dealer_group_ids.*' => ['exists:dealer_groups,id'],
            'group_delays' => ['nullable', 'array'],
            'group_delays.*' => ['nullable', 'integer', 'min:0'],
            'stocks' => ['nullable', 'array'],
            'stocks.*.product_id' => ['required_with:stocks.*.quantity', 'exists:products,id'],
            'stocks.*.quantity' => ['nullable', 'integer', 'min:0'],
            'stocks.*.waste' => ['nullable', 'integer', 'min:0'],
            'stocks.*.cost_price' => ['nullable', 'numeric', 'min:0'],
            'stocks.*.price' => ['nullable', 'numeric', 'min:0'],
        ];

        $messages = [
            'name.required' => 'Parti adı gerekli.',
            'supplier_name.required' => 'Tedarikçi bilgisi gerekli.',
            'truck_plate.required' => 'Tır plakası gerekli.',
            'purchase_price_per_unit.required' => 'Birim alış fiyatı gerekli.',
            'logistics_cost.required' => 'Lojistik maliyeti gerekli.',
            'customs_cost.required' => 'Gümrük/vergi masrafları gerekli.',
            'starts_at.required' => 'Aktivasyon zamanı gerekli.',
            'party_code.unique' => 'Bu parti kodu zaten kullanılıyor.',
            'party_code.regex' => 'Parti kodu sadece İngilizce harf, rakam ve tire içerebilir.',
            'ends_at.required' => 'Bitiş tarihi gerekli veya "Stok bitene kadar açık tut" işaretleyin.',
            'departure_at.before' => 'Yola çıkış tarihi, varış tarihinden önce olmalıdır.',
            'arrived_at.after' => 'Varış tarihi, yola çıkış tarihinden sonra olmalıdır.',
            'florist_delivery_at.after' => 'Çiçekçilere teslimat tarihi, tır varış tarihinden sonra olmalıdır.',
        ];

        $validated = $request->validate($rules, $messages);

        // Format fields before save
        if (!empty($validated['party_code'])) {
            $validated['party_code'] = $this->sanitizePartyCode($validated['party_code']);
        }
        $validated['name'] = $this->titleCaseTr($validated['name'] ?? '');
        $validated['supplier_name'] = $this->titleCaseTr($validated['supplier_name'] ?? '');
        $validated['driver_name'] = $this->titleCaseTr($validated['driver_name'] ?? '');
        $validated['emergency_contact'] = $this->titleCaseTr($validated['emergency_contact'] ?? '');
        $validated['truck_plate'] = mb_strtoupper($validated['truck_plate'] ?? '');
        if (!empty($validated['driver_contact'])) {
            $validated['driver_contact'] = $this->normalizeTrPhone($validated['driver_contact']);
        }

        $partyData = [
            'status' => 'draft',
            'created_by' => auth('admin')->id(),
            'currency' => $validated['currency'] ?? 'EUR',
            'close_when_stock_runs_out' => $closeWhenStock,
            'visible_to_all' => $visibleToAll,
            'driver_name' => $validated['driver_name'] ?? null,
            'driver_contact' => $validated['driver_contact'] ?? null,
            'emergency_contact' => $validated['emergency_contact'] ?? null,
            'truck_status' => $validated['truck_status'] ?? null,
            'departure_at' => $validated['departure_at'] ?? null,
            'estimated_arrival_at' => in_array($validated['truck_status'] ?? '', ['not_departed', 'on_road']) ? ($validated['arrived_at'] ?? null) : null,
            'arrived_at' => (!in_array($validated['truck_status'] ?? '', ['not_departed', 'on_road'])) ? ($validated['arrived_at'] ?? null) : null,
            'florist_delivery_at' => $validated['florist_delivery_at'] ?? null,
        ];
        if ($closeWhenStock) {
            $validated['ends_at'] = null;
        }
        $partyData = array_merge($validated, $partyData);

        $party = DB::transaction(function () use ($partyData, $visibleToAll, $request) {
            $party = Party::create($partyData);

            $sync = [];
            if (!$visibleToAll && !empty($request->dealer_group_ids)) {
                foreach ($request->dealer_group_ids as $gid) {
                    $sync[$gid] = ['delay_minutes' => isset($request->group_delays[$gid]) && $request->group_delays[$gid] !== '' ? (int) $request->group_delays[$gid] : null];
                }
            } elseif ($visibleToAll && !empty($request->group_delays)) {
                foreach ($request->group_delays as $gid => $delay) {
                    if ($delay !== null && $delay !== '') {
                        $sync[$gid] = ['delay_minutes' => (int) $delay];
                    }
                }
            }
            $party->dealerGroups()->sync($sync);

            $stocks = $request->stocks ?? [];
            foreach ($stocks as $productId => $row) {
                $qty = (int) ($row['quantity'] ?? 0);
                if ($qty < 1) {
                    continue;
                }
                $pid = (int) ($row['product_id'] ?? $productId);
                PartyStock::create([
                    'party_id' => $party->id,
                    'product_id' => $pid,
                    'total_quantity' => $qty,
                    'waste_quantity' => isset($row['waste']) && $row['waste'] !== '' ? (int) $row['waste'] : 0,
                    'cost_price_override' => isset($row['cost_price']) && $row['cost_price'] !== '' ? $row['cost_price'] : null,
                    'price_override' => isset($row['price']) && $row['price'] !== '' ? $row['price'] : null,
                ]);
            }

            return $party;
        });

        return redirect()->route('admin.parties.index')
            ->with('success', 'Parti başarıyla oluşturuldu. Siparişler ' . \Carbon\Carbon::parse($validated['starts_at'])->format('d.m.Y H:i') . ' tarihinde otomatik açılacak.');
    }

    public function edit(Party $party): View
    {
        $party->load(['createdByAdmin', 'closedByAdmin']);
        return view('admin.parties.edit', compact('party'));
    }

    public function update(Request $request, Party $party): RedirectResponse
    {
        if ($party->isClosed()) {
            return back()->with('error', 'Kapalı partiler düzenlenemez.');
        }

        // Aktif partide sadece varış tarihi ve yolculuk süresi güncellenebilir (tır geldiğinde işaretlemek için)
        if ($party->isActive()) {
            $validated = $request->validate([
                'arrived_at' => ['nullable', 'date'],
                'journey_days' => ['nullable', 'integer', 'min:0'],
            ]);
            $party->update($validated);
            return redirect()->route('admin.parties.index')
                ->with('success', 'Varış bilgisi güncellendi.');
        }

        $closeWhenStock = (bool) $request->input('close_when_stock_runs_out', false);
        $rules = [
            'party_code' => ['nullable', 'string', 'max:50', 'unique:parties,party_code,' . $party->id],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'supplier_name' => ['nullable', 'string', 'max:255'],
            'truck_plate' => ['nullable', 'string', 'max:20'],
            'journey_days' => ['nullable', 'integer', 'min:0'],
            'purchase_price_per_unit' => ['nullable', 'numeric', 'min:0'],
            'logistics_cost' => ['nullable', 'numeric', 'min:0'],
            'customs_cost' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'arrived_at' => ['nullable', 'date'],
            'starts_at' => ['required', 'date'],
            'ends_at' => $closeWhenStock ? ['nullable', 'date'] : ['required', 'date', 'after:starts_at'],
            'close_when_stock_runs_out' => ['nullable', 'boolean'],
        ];

        $validated = $request->validate($rules, [
            'name.required' => 'Parti adı gerekli.',
            'party_code.unique' => 'Bu parti kodu zaten kullanılıyor.',
            'starts_at.required' => 'Sipariş başlangıç tarihi gerekli.',
        ]);

        $validated['close_when_stock_runs_out'] = $closeWhenStock;
        if ($closeWhenStock) {
            $validated['ends_at'] = null;
        }

        $party->update($validated);

        return redirect()->route('admin.parties.index')
            ->with('success', 'Parti güncellendi.');
    }

    public function destroy(Party $party): RedirectResponse
    {
        // Aktif partiler silinemez
        if ($party->isActive()) {
            return back()->with('error', 'Aktif partiler silinemez. Önce kapatın.');
        }

        $party->delete();

        return redirect()->route('admin.parties.index')
            ->with('success', 'Parti silindi.');
    }

    public function activate(Party $party): RedirectResponse
    {
        if ($party->isActive()) {
            return back()->with('error', 'Bu parti zaten aktif.');
        }

        if ($party->isClosed()) {
            return back()->with('error', 'Kapalı partiler tekrar aktif edilemez.');
        }

        // Aynı anda tek parti: biri kapanmadan diğeri açılamaz
        $otherActive = Party::where('status', 'active')->where('id', '!=', $party->id)->first();
        if ($otherActive) {
            return back()->with('error', 'Önce mevcut partiyi kapatın. "' . $otherActive->name . '" hâlâ aktiftir.');
        }

        $party->update([
            'status' => 'active',
            'activated_at' => now(),
        ]);

        return redirect()->route('admin.parties.index')
            ->with('success', 'Parti aktif edildi. Siparişler açıldı.');
    }

    public function close(Party $party): RedirectResponse
    {
        if ($party->isClosed()) {
            return back()->with('error', 'Bu parti zaten kapalı.');
        }

        if ($party->isDraft()) {
            return back()->with('error', 'Taslak partiler kapatılamaz. Silin.');
        }

        $party->markClosed();

        return redirect()->route('admin.parties.index')
            ->with('success', 'Parti kapatıldı.');
    }

    /** Parti kodu: sadece İngilizce harf, rakam, tire; tüm harfler büyük. */
    protected function sanitizePartyCode(string $value): string
    {
        $s = preg_replace('/[^A-Za-z0-9\-]/', '', $value);
        return Str::limit(mb_strtoupper($s, 'UTF-8'), 50, '');
    }

    /** Türkçe uyumlu title case (tüm kelimeler ilk harf büyük, İ/ı doğru). */
    protected function titleCaseTr(string $value): string
    {
        $words = preg_split('/\s+/u', trim($value), -1, PREG_SPLIT_NO_EMPTY);
        return collect($words)->map(function (string $w) {
            $len = mb_strlen($w, 'UTF-8');
            if ($len === 0) return $w;
            $first = mb_substr($w, 0, 1, 'UTF-8');
            $rest = mb_substr($w, 1, null, 'UTF-8');
            $firstUpper = match ($first) {
                'i' => 'İ',
                'ı' => 'I',
                default => mb_strtoupper($first, 'UTF-8'),
            };
            $rest = str_replace(['I', 'İ'], ['ı', 'i'], $rest);
            $rest = mb_strtolower($rest, 'UTF-8');
            return $firstUpper . $rest;
        })->implode(' ');
    }

    /** TR telefon formatı (0555 555 55 55). */
    protected function normalizeTrPhone(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value);
        if (Str::startsWith($digits, '5')) {
            $digits = '0' . $digits;
        }
        $digits = Str::substr($digits, 0, 11);
        if (strlen($digits) < 11) {
            return $value;
        }
        return substr($digits, 0, 4) . ' ' . substr($digits, 4, 3) . ' ' . substr($digits, 7, 2) . ' ' . substr($digits, 9, 2);
    }

    public function productsByCategory(Category $category): JsonResponse
    {
        $productIds = $category->activeProducts()->pluck('id');
        $childIds = $category->children()->where('is_active', true)->pluck('id');
        if ($childIds->isNotEmpty()) {
            $productIds = $productIds->merge(
                Product::whereIn('category_id', $childIds)->where('is_active', true)->pluck('id')
            )->unique();
        }
        $products = Product::whereIn('id', $productIds)
            ->with('category')
            ->orderBy('name')
            ->get();

        return response()->json($products->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'category_name' => $p->category?->name,
            'cost_price' => (float) $p->cost_price,
            'price' => (float) $p->price,
        ]));
    }
}
