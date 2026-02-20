<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DealerGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DealerGroupController extends Controller
{
    public function index(): View
    {
        return view('admin.groups.index');
    }

    public function create(): View
    {
        return view('admin.groups.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:dealer_groups,code'],
            'delay_minutes' => ['required', 'integer', 'min:0'],
            'is_default' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ], [
            'name.required' => 'Grup adı gerekli.',
            'code.required' => 'Grup kodu gerekli.',
            'code.unique' => 'Bu grup kodu zaten kullanılıyor.',
            'delay_minutes.required' => 'Gecikme süresi gerekli.',
            'delay_minutes.min' => 'Gecikme süresi 0 veya daha büyük olmalı.',
        ]);

        $validated['is_default'] = $request->boolean('is_default');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        // Eğer varsayılan grup işaretlenirse, diğerlerini kaldır
        if ($validated['is_default']) {
            DealerGroup::where('is_default', true)->update(['is_default' => false]);
        }

        DealerGroup::create($validated);

        return redirect()->route('admin.groups.index')
            ->with('success', 'Bayi grubu başarıyla oluşturuldu.');
    }

    public function edit(DealerGroup $group): View
    {
        $group->loadCount('dealers');
        return view('admin.groups.edit', compact('group'));
    }

    public function update(Request $request, DealerGroup $group): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:dealer_groups,code,' . $group->id],
            'delay_minutes' => ['required', 'integer', 'min:0'],
            'is_default' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ], [
            'name.required' => 'Grup adı gerekli.',
            'code.required' => 'Grup kodu gerekli.',
            'code.unique' => 'Bu grup kodu zaten kullanılıyor.',
            'delay_minutes.required' => 'Gecikme süresi gerekli.',
            'delay_minutes.min' => 'Gecikme süresi 0 veya daha büyük olmalı.',
        ]);

        $validated['is_default'] = $request->boolean('is_default');
        $validated['sort_order'] = $validated['sort_order'] ?? $group->sort_order;

        // Eğer varsayılan grup işaretlenirse, diğerlerini kaldır
        if ($validated['is_default']) {
            DealerGroup::where('is_default', true)
                ->where('id', '!=', $group->id)
                ->update(['is_default' => false]);
        }

        $group->update($validated);

        return redirect()->route('admin.groups.index')
            ->with('success', 'Bayi grubu güncellendi.');
    }

    public function destroy(DealerGroup $group): RedirectResponse
    {
        if ($group->dealers()->exists()) {
            return back()->with('error', 'Bu grupta bayi var. Önce bayileri başka gruba taşıyın.');
        }

        if ($group->is_default) {
            return back()->with('error', 'Varsayılan grup silinemez.');
        }

        $group->delete();

        return redirect()->route('admin.groups.index')
            ->with('success', 'Bayi grubu silindi.');
    }
}
