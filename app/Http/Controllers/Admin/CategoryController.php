<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DealerGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::with('parent')
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        $parentCategories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $dealerGroups = DealerGroup::orderBy('sort_order')->get();

        return view('admin.categories.create', compact('parentCategories', 'dealerGroups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $attrKeys = array_keys(config('sera.category_attributes', []));
        $validated = $request->validate([
            'parent_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:categories,slug'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'season_start_month' => ['nullable', 'integer', 'min:0', 'max:12'],
            'season_end_month' => ['nullable', 'integer', 'min:0', 'max:12'],
            'inactive_outside_season' => ['boolean'],
            'visible_to_group_ids' => ['nullable', 'array'],
            'visible_to_group_ids.*' => ['integer', 'exists:dealer_groups,id'],
            'max_quantity_per_dealer_per_party' => ['nullable', 'integer', 'min:1'],
            'display_priority' => ['nullable', 'integer'],
            'attribute_required' => ['nullable', 'array'],
            'attribute_required.*' => ['string', 'in:' . implode(',', $attrKeys) ?: 'none'],
            'attribute_visible' => ['nullable', 'array'],
            'attribute_visible.*' => ['string', 'in:' . implode(',', $attrKeys) ?: 'none'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'default_growth_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'ideal_temp_min' => ['nullable', 'numeric', 'min:-50', 'max:100'],
            'ideal_temp_max' => ['nullable', 'numeric', 'min:-50', 'max:100'],
            'ideal_humidity_min' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'ideal_humidity_max' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'required_documents' => ['nullable', 'array'],
            'required_documents.*' => ['string', 'in:' . implode(',', array_keys(config('sera.category_required_documents', [])))],
            'min_order_quantity' => ['nullable', 'integer', 'min:1'],
            'profit_margin_percent' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'color_code' => ['nullable', 'string', 'max:20', function ($attr, $value, $fail) {
                if ($value !== '' && $value !== null && ! array_key_exists($value, config('sera.category_colors', []))) {
                    $fail('Geçersiz renk seçimi.');
                }
            }],
            'icon' => ['nullable', 'string', 'max:50', function ($attr, $value, $fail) {
                if ($value && ! array_key_exists($value, config('sera.category_icons', []))) {
                    $fail('Geçersiz ikon seçimi.');
                }
            }],
            'featured_badges' => ['nullable', 'array'],
            'featured_badges.*' => ['string', 'in:' . implode(',', array_keys(config('sera.category_featured_badges', [])))],
        ], [
            'name.required' => 'Kategori adı gerekli.',
            'slug.unique' => 'Bu slug zaten kullanılıyor.',
            'slug.regex' => 'Slug sadece küçük harf, rakam ve tire içerebilir. Türkçe karakter kullanılamaz.',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['inactive_outside_season'] = $request->boolean('inactive_outside_season');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['display_priority'] = $validated['display_priority'] ?? 0;
        $validated['visible_to_group_ids'] = ! empty($validated['visible_to_group_ids']) ? $validated['visible_to_group_ids'] : null;
        $validated['attribute_set'] = $this->parseAttributeSet($request->input('attribute_required'), $request->input('attribute_visible'));
        $validated['region_restriction'] = $this->parseRegionRestriction($request->input('region_cities'), $request->input('region_regions'));
        $validated['season_start_month'] = ($validated['season_start_month'] ?? 0) ?: null;
        $validated['season_end_month'] = ($validated['season_end_month'] ?? 0) ?: null;
        $validated['required_documents'] = ! empty($request->input('required_documents')) ? array_values(array_filter($request->input('required_documents'))) : null;
        $validated['featured_badges'] = ! empty($request->input('featured_badges')) ? array_values(array_filter($request->input('featured_badges'))) : null;
        $validated['ideal_temp_min'] = $validated['ideal_temp_min'] ?? null;
        $validated['ideal_temp_max'] = $validated['ideal_temp_max'] ?? null;
        $validated['ideal_humidity_min'] = $validated['ideal_humidity_min'] ?? null;
        $validated['ideal_humidity_max'] = $validated['ideal_humidity_max'] ?? null;

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
        $dealerGroups = DealerGroup::orderBy('sort_order')->get();

        return view('admin.categories.edit', compact('category', 'parentCategories', 'dealerGroups'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $attrKeys = array_keys(config('sera.category_attributes', []));
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
            'season_start_month' => ['nullable', 'integer', 'min:0', 'max:12'],
            'season_end_month' => ['nullable', 'integer', 'min:0', 'max:12'],
            'inactive_outside_season' => ['boolean'],
            'visible_to_group_ids' => ['nullable', 'array'],
            'visible_to_group_ids.*' => ['integer', 'exists:dealer_groups,id'],
            'max_quantity_per_dealer_per_party' => ['nullable', 'integer', 'min:1'],
            'display_priority' => ['nullable', 'integer'],
            'attribute_required' => ['nullable', 'array'],
            'attribute_required.*' => ['string', 'in:' . implode(',', $attrKeys) ?: 'none'],
            'attribute_visible' => ['nullable', 'array'],
            'attribute_visible.*' => ['string', 'in:' . implode(',', $attrKeys) ?: 'none'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'default_growth_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'ideal_temp_min' => ['nullable', 'numeric', 'min:-50', 'max:100'],
            'ideal_temp_max' => ['nullable', 'numeric', 'min:-50', 'max:100'],
            'ideal_humidity_min' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'ideal_humidity_max' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'required_documents' => ['nullable', 'array'],
            'required_documents.*' => ['string', 'in:' . implode(',', array_keys(config('sera.category_required_documents', [])))],
            'min_order_quantity' => ['nullable', 'integer', 'min:1'],
            'profit_margin_percent' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'color_code' => ['nullable', 'string', 'max:20', function ($attr, $value, $fail) {
                if ($value !== '' && $value !== null && ! array_key_exists($value, config('sera.category_colors', []))) {
                    $fail('Geçersiz renk seçimi.');
                }
            }],
            'icon' => ['nullable', 'string', 'max:50', function ($attr, $value, $fail) {
                if ($value && ! array_key_exists($value, config('sera.category_icons', []))) {
                    $fail('Geçersiz ikon seçimi.');
                }
            }],
            'featured_badges' => ['nullable', 'array'],
            'featured_badges.*' => ['string', 'in:' . implode(',', array_keys(config('sera.category_featured_badges', [])))],
        ], [
            'name.required' => 'Kategori adı gerekli.',
            'slug.unique' => 'Bu slug zaten kullanılıyor.',
            'slug.regex' => 'Slug sadece küçük harf, rakam ve tire içerebilir. Türkçe karakter kullanılamaz.',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['inactive_outside_season'] = $request->boolean('inactive_outside_season');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['display_priority'] = $validated['display_priority'] ?? 0;
        $validated['visible_to_group_ids'] = ! empty($validated['visible_to_group_ids']) ? $validated['visible_to_group_ids'] : null;
        $validated['attribute_set'] = $this->parseAttributeSet($request->input('attribute_required'), $request->input('attribute_visible'));
        $validated['region_restriction'] = $this->parseRegionRestriction($request->input('region_cities'), $request->input('region_regions'));
        $validated['season_start_month'] = ($validated['season_start_month'] ?? 0) ?: null;
        $validated['season_end_month'] = ($validated['season_end_month'] ?? 0) ?: null;
        $validated['required_documents'] = ! empty($request->input('required_documents')) ? array_values(array_filter($request->input('required_documents'))) : null;
        $validated['featured_badges'] = ! empty($request->input('featured_badges')) ? array_values(array_filter($request->input('featured_badges'))) : null;
        $validated['ideal_temp_min'] = $validated['ideal_temp_min'] ?? null;
        $validated['ideal_temp_max'] = $validated['ideal_temp_max'] ?? null;
        $validated['ideal_humidity_min'] = $validated['ideal_humidity_min'] ?? null;
        $validated['ideal_humidity_max'] = $validated['ideal_humidity_max'] ?? null;

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

    private function parseAttributeSet(?array $required, ?array $visible): ?array
    {
        $required = array_values(array_filter($required ?? []));
        $visible = array_values(array_filter($visible ?? []));
        if (empty($required) && empty($visible)) {
            return null;
        }
        return ['required' => $required, 'visible' => $visible];
    }

    private function parseRegionRestriction(?string $cities, ?string $regions): ?array
    {
        $cities = array_filter(array_map('trim', explode(',', $cities ?? '')));
        $regions = array_filter(array_map('trim', explode(',', $regions ?? '')));
        if (empty($cities) && empty($regions)) {
            return null;
        }
        return array_filter([
            'cities' => $cities ?: null,
            'regions' => $regions ?: null,
        ]);
    }
}
