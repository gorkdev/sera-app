<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with('category');

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')->paginate(15)->withQueryString();
        $categories = Category::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $badgeKeys = array_keys(config('sera.product_featured_badges', []));
        $originKeys = array_keys(config('sera.origin_options', []));
        $unitKeys = array_keys(config('sera.product_units', ['adet' => 'Adet']));

        // Trim string inputs (baş/son boşluk kontrolü)
        $request->merge([
            'name' => trim((string) $request->input('name', '')),
            'slug' => trim((string) $request->input('slug', '')),
            'sku' => trim((string) $request->input('sku', '')),
            'description' => trim((string) $request->input('description', '')),
        ]);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:products,slug'],
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'description' => ['required', 'string', 'min:10'],
            'image' => ['required', 'image', 'max:2048'],
            'gallery_images' => ['required', 'array', 'min:1'],
            'gallery_images.*' => ['required', 'image', 'max:2048'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'in:' . implode(',', $unitKeys) ?: 'adet'],
            // 1 satır yeterli; boş/eksik satırlar frontend’de name kaldırılarak gönderilmiyor,
            // yine de backend’de toleranslı kalalım (2. satır boş diye patlamasın).
            'unit_conversions' => ['nullable', 'array'],
            'unit_conversions.*.unit' => ['nullable', 'string', 'in:' . implode(',', $unitKeys) ?: 'adet'],
            'unit_conversions.*.adet' => ['nullable', 'integer', 'min:1'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'min_order_quantity' => ['required', 'integer', 'min:1'],
            'is_active' => ['required', 'accepted'],
            'critical_stock_type' => ['nullable', 'string', 'in:percent,quantity'],
            'critical_stock_value' => ['nullable', 'integer', 'min:0'],
            'critical_stock_reference' => ['nullable', 'integer', 'min:1'],
            'featured_badges' => ['nullable', 'array'],
            'featured_badges.*' => ['string', 'in:' . implode(',', $badgeKeys) ?: 'none'],
            'origin' => ['nullable', 'string', 'in:' . implode(',', $originKeys) ?: ''],
            'shelf_life_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ], [
            'category_id.required' => 'Kategori seçin.',
            'name.required' => 'Ürün adı gerekli.',
            'slug.required' => 'URL slug gerekli.',
            'slug.regex' => 'Slug sadece küçük harf, rakam ve tire içerebilir.',
            'sku.required' => 'Ürün kodu (SKU) gerekli.',
            'description.required' => 'Ürün açıklaması gerekli.',
            'description.min' => 'Ürün açıklaması en az 10 karakter olmalıdır.',
            'image.required' => 'Ana görsel gerekli.',
            'gallery_images.required' => 'En az 1 adet alt görsel gerekli.',
            'gallery_images.min' => 'En az 1 adet alt görsel gerekli.',
            'price.required' => 'Satış fiyatı gerekli.',
            'cost_price.required' => 'Maliyet gerekli.',
            'stock_quantity.required' => 'Stok miktarı gerekli.',
            'min_order_quantity.required' => 'Minimum sipariş miktarı gerekli.',
            'is_active.required' => 'Ürün aktif olmalıdır.',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['stock_quantity'] = $validated['stock_quantity'] ?? 0;
        $validated['min_order_quantity'] = $validated['min_order_quantity'] ?? 1;

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        } else {
            unset($validated['image']);
        }

        $gallery = [];
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $file) {
                $gallery[] = $file->store('products', 'public');
            }
        }
        $validated['gallery_images'] = $gallery ?: null;

        $unitConversions = $this->parseUnitConversions($request->input('unit_conversions'), $unitKeys);
        if (empty($unitConversions)) {
            throw ValidationException::withMessages([
                'unit_conversions' => ['En az 1 birim dönüşümü gerekli.'],
            ]);
        }
        $validated['unit_conversions'] = $unitConversions;
        $validated['featured_badges'] = ! empty($request->input('featured_badges')) ? array_values(array_filter($request->input('featured_badges'))) : null;

        Product::create($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Ürün oluşturuldu.');
    }

    public function edit(Product $product): View
    {
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $badgeKeys = array_keys(config('sera.product_featured_badges', []));
        $originKeys = array_keys(config('sera.origin_options', []));
        $unitKeys = array_keys(config('sera.product_units', ['adet' => 'Adet']));

        // Trim string inputs (baş/son boşluk kontrolü)
        $request->merge([
            'name' => trim((string) $request->input('name', '')),
            'slug' => trim((string) $request->input('slug', '')),
            'sku' => trim((string) $request->input('sku', '')),
            'description' => trim((string) $request->input('description', '')),
        ]);

        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:products,slug,' . $product->id],
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku,' . $product->id],
            'description' => ['required', 'string', 'min:10'],
            'image' => ['nullable', 'image', 'max:2048'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'max:2048'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'in:' . implode(',', $unitKeys) ?: 'adet'],
            'unit_conversions' => ['nullable', 'array'],
            'unit_conversions.*.unit' => ['nullable', 'string', 'in:' . implode(',', $unitKeys) ?: 'adet'],
            'unit_conversions.*.adet' => ['nullable', 'integer', 'min:1'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'min_order_quantity' => ['required', 'integer', 'min:1'],
            'is_active' => ['required', 'accepted'],
            'critical_stock_type' => ['nullable', 'string', 'in:percent,quantity'],
            'critical_stock_value' => ['nullable', 'integer', 'min:0'],
            'critical_stock_reference' => ['nullable', 'integer', 'min:1'],
            'featured_badges' => ['nullable', 'array'],
            'featured_badges.*' => ['string', 'in:' . implode(',', $badgeKeys) ?: 'none'],
            'origin' => ['nullable', 'string', 'in:' . implode(',', $originKeys) ?: ''],
            'shelf_life_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ], [
            'category_id.required' => 'Kategori seçin.',
            'name.required' => 'Ürün adı gerekli.',
            'slug.required' => 'URL slug gerekli.',
            'slug.regex' => 'Slug sadece küçük harf, rakam ve tire içerebilir.',
            'sku.required' => 'Ürün kodu (SKU) gerekli.',
            'description.required' => 'Ürün açıklaması gerekli.',
            'description.min' => 'Ürün açıklaması en az 10 karakter olmalıdır.',
            'gallery_images.required' => 'En az 1 adet alt görsel gerekli.',
            'gallery_images.min' => 'En az 1 adet alt görsel gerekli.',
            'price.required' => 'Satış fiyatı gerekli.',
            'cost_price.required' => 'Maliyet gerekli.',
            'stock_quantity.required' => 'Stok miktarı gerekli.',
            'min_order_quantity.required' => 'Minimum sipariş miktarı gerekli.',
            'is_active.required' => 'Ürün aktif olmalıdır.',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['stock_quantity'] = $validated['stock_quantity'] ?? 0;
        $validated['min_order_quantity'] = $validated['min_order_quantity'] ?? 1;

        // Ana görsel zorunlu (mevcut veya yeni)
        if (! $request->hasFile('image') && ($request->boolean('remove_image') || ! $product->image)) {
            return back()->withErrors(['image' => 'Ana görsel gerekli.'])->withInput();
        }

        // En az 1 alt görsel (mevcut kalan + yeni)
        $existingGallery = $product->gallery_images ?? [];
        $removeIndices = array_map('intval', $request->input('gallery_remove', []));
        $keptCount = 0;
        foreach (array_keys($existingGallery) as $i) {
            if (! in_array($i, $removeIndices, true)) {
                $keptCount++;
            }
        }
        $newCount = $request->hasFile('gallery_images') ? count($request->file('gallery_images')) : 0;
        if ($keptCount + $newCount < 1) {
            return back()->withErrors(['gallery_images' => 'En az 1 adet alt görsel gerekli.'])->withInput();
        }

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        } else {
            unset($validated['image']);
        }

        if ($request->boolean('remove_image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = null;
        }

        $gallery = [];
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $file) {
                $gallery[] = $file->store('products', 'public');
            }
        }
        if ($request->hasFile('gallery_images') || $request->filled('gallery_remove')) {
            $existing = $product->gallery_images ?? [];
            $removeIndices = array_map('intval', $request->input('gallery_remove', []));
            $kept = [];
            foreach ($existing as $i => $path) {
                if (! in_array($i, $removeIndices, true)) {
                    $kept[] = $path;
                } else {
                    Storage::disk('public')->delete($path);
                }
            }
            $validated['gallery_images'] = array_merge($kept, $gallery) ?: null;
        }

        $unitConversions = $this->parseUnitConversions($request->input('unit_conversions'), $unitKeys);
        if (empty($unitConversions)) {
            throw ValidationException::withMessages([
                'unit_conversions' => ['En az 1 birim dönüşümü gerekli.'],
            ]);
        }
        $validated['unit_conversions'] = $unitConversions;
        $validated['featured_badges'] = ! empty($request->input('featured_badges')) ? array_values(array_filter($request->input('featured_badges'))) : null;

        $product->update($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Ürün güncellendi.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        foreach ($product->gallery_images ?? [] as $path) {
            Storage::disk('public')->delete($path);
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Ürün silindi.');
    }

    private function parseUnitConversions(?array $rows, array $allowedUnitKeys = []): ?array
    {
        if (empty($rows)) {
            return null;
        }
        $result = [];
        foreach ($rows as $row) {
            $unit = (string) ($row['unit'] ?? '');
            $adet = $row['adet'] ?? null;
            if ($unit === '' || $adet === null || $adet === '') {
                continue;
            }
            if (! empty($allowedUnitKeys) && ! in_array($unit, $allowedUnitKeys, true)) {
                continue;
            }
            $adetInt = (int) $adet;
            if ($adetInt < 1) {
                continue;
            }

            if ($unit !== '' && $adetInt >= 1) {
                $result[] = [
                    'unit' => $unit,
                    'adet' => $adetInt,
                ];
            }
        }

        return $result ?: null;
    }
}
