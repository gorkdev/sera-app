<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        // Livewire component render ediyor
        return view('admin.categories.index');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:categories,id'],
            'ust' => ['required', 'string'], // tumu | ana | {id}
        ], [
            'ids.required' => 'Sıralama verisi gerekli.',
            'ids.array' => 'Sıralama verisi geçersiz.',
        ]);

        $ust = (string) $validated['ust'];
        if ($ust === 'all') { // eski client desteği
            $ust = 'tumu';
        }
        if ($ust === 'root') {
            $ust = 'ana';
        }

        if ($ust === 'tumu') {
            return response()->json(['message' => 'Tüm kategoriler görünümünde sıralama yapılamaz.'], 422);
        }

        $expectedParentId = null;
        if ($ust !== 'ana') {
            if (! ctype_digit($ust)) {
                return response()->json(['message' => 'Üst kategori filtresi geçersiz.'], 422);
            }
            $expectedParentId = (int) $ust;
        }

        $ids = array_values($validated['ids']);

        $cats = Category::query()
            ->whereIn('id', $ids)
            ->get(['id', 'parent_id']);

        if ($cats->count() !== count($ids)) {
            return response()->json(['message' => 'Bazı kategoriler bulunamadı.'], 422);
        }

        foreach ($cats as $c) {
            if ($expectedParentId === null) {
                if ($c->parent_id !== null) {
                    return response()->json(['message' => 'Sadece ana kategoriler sıralanabilir.'], 422);
                }
            } else {
                if ((int) $c->parent_id !== $expectedParentId) {
                    return response()->json(['message' => 'Sadece seçili üst kategori altındaki kategoriler sıralanabilir.'], 422);
                }
            }
        }

        DB::transaction(function () use ($ids) {
            foreach ($ids as $i => $id) {
                Category::whereKey($id)->update(['sort_order' => $i]);
            }
        });

        return response()->json(['ok' => true]);
    }

    public function create(): View
    {
        $parentCategories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.categories.create', compact('parentCategories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'parent_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:categories,slug'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ], [
            'name.required' => 'Kategori adı gerekli.',
            'slug.unique' => 'Bu slug zaten kullanılıyor.',
            'slug.regex' => 'Slug sadece küçük harf, rakam ve tire içerebilir. Türkçe karakter kullanılamaz.',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('categories', 'public');
        } else {
            unset($validated['image']);
        }

        Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori başarıyla oluşturuldu.');
    }

    public function edit(Category $category): View
    {
        $category->loadCount('products');
        $parentCategories = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'parent_id' => ['nullable', 'exists:categories,id', function ($attr, $value, $fail) use ($category) {
                if ($value == $category->id) {
                    $fail('Kategori kendisinin alt kategorisi olamaz.');
                }
            }],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:categories,slug,' . $category->id],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ], [
            'name.required' => 'Kategori adı gerekli.',
            'slug.unique' => 'Bu slug zaten kullanılıyor.',
            'slug.regex' => 'Slug sadece küçük harf, rakam ve tire içerebilir. Türkçe karakter kullanılamaz.',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $validated['image'] = $request->file('image')->store('categories', 'public');
        } else {
            unset($validated['image']);
        }

        if ($request->boolean('remove_image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $validated['image'] = null;
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori güncellendi.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'Bu kategoride ürün var. Önce ürünleri taşıyın veya silin.');
        }

        if ($category->children()->exists()) {
            return back()->with('error', 'Alt kategorisi olan kategori silinemez. Önce alt kategorileri silin.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori silindi.');
    }
}
