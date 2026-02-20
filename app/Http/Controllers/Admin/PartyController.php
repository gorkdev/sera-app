<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Party;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PartyController extends Controller
{
    public function index(): View
    {
        return view('admin.parties.index');
    }

    public function create(): View
    {
        return view('admin.parties.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'party_code' => ['nullable', 'string', 'max:50', 'unique:parties,party_code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'supplier_name' => ['required', 'string', 'max:255'],
            'truck_plate' => ['required', 'string', 'max:20'],
            'journey_days' => ['nullable', 'integer', 'min:0'],
            'purchase_price_per_unit' => ['required', 'numeric', 'min:0'],
            'logistics_cost' => ['required', 'numeric', 'min:0'],
            'customs_cost' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
        ], [
            'name.required' => 'Parti adı gerekli.',
            'supplier_name.required' => 'Tedarikçi bilgisi gerekli.',
            'truck_plate.required' => 'Tır plakası gerekli.',
            'purchase_price_per_unit.required' => 'Birim alış fiyatı gerekli.',
            'purchase_price_per_unit.numeric' => 'Birim alış fiyatı sayısal bir değer olmalı.',
            'purchase_price_per_unit.min' => 'Birim alış fiyatı 0 veya daha büyük olmalı.',
            'logistics_cost.required' => 'Lojistik maliyeti gerekli.',
            'logistics_cost.numeric' => 'Lojistik maliyeti sayısal bir değer olmalı.',
            'logistics_cost.min' => 'Lojistik maliyeti 0 veya daha büyük olmalı.',
            'customs_cost.required' => 'Gümrük/vergi masrafları gerekli.',
            'customs_cost.numeric' => 'Gümrük/vergi masrafları sayısal bir değer olmalı.',
            'customs_cost.min' => 'Gümrük/vergi masrafları 0 veya daha büyük olmalı.',
            'party_code.unique' => 'Bu parti kodu zaten kullanılıyor.',
        ]);

        $validated['status'] = 'draft';
        $validated['created_by'] = auth('admin')->id();
        $validated['currency'] = $validated['currency'] ?? 'EUR';

        Party::create($validated);

        return redirect()->route('admin.parties.index')
            ->with('success', 'Parti başarıyla oluşturuldu.');
    }

    public function edit(Party $party): View
    {
        $party->load(['createdByAdmin', 'closedByAdmin']);
        return view('admin.parties.edit', compact('party'));
    }

    public function update(Request $request, Party $party): RedirectResponse
    {
        // Aktif veya kapalı partiler düzenlenemez
        if ($party->isActive() || $party->isClosed()) {
            return back()->with('error', 'Aktif veya kapalı partiler düzenlenemez.');
        }

        $validated = $request->validate([
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
        ], [
            'name.required' => 'Parti adı gerekli.',
            'party_code.unique' => 'Bu parti kodu zaten kullanılıyor.',
        ]);

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

        DB::transaction(function () use ($party) {
            // Diğer aktif partiyi kapat
            Party::where('status', 'active')
                ->where('id', '!=', $party->id)
                ->update([
                    'status' => 'closed',
                    'closed_at' => now(),
                    'closed_by' => auth('admin')->id(),
                ]);

            // Bu partiyi aktif et
            $updateData = [
                'status' => 'active',
                'activated_at' => now(),
            ];
            
            // Eğer arrived_at yoksa, şimdi set et
            if (!$party->arrived_at) {
                $updateData['arrived_at'] = now();
            }
            
            $party->update($updateData);
        });

        return redirect()->route('admin.parties.index')
            ->with('success', 'Parti aktif edildi. Diğer aktif parti varsa kapatıldı.');
    }

    public function close(Party $party): RedirectResponse
    {
        if ($party->isClosed()) {
            return back()->with('error', 'Bu parti zaten kapalı.');
        }

        if ($party->isDraft()) {
            return back()->with('error', 'Taslak partiler kapatılamaz. Silin.');
        }

        $party->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => auth('admin')->id(),
        ]);

        return redirect()->route('admin.parties.index')
            ->with('success', 'Parti kapatıldı.');
    }
}
