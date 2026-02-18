<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug'],
            'sku' => ['nullable', 'string', 'max:50', 'unique:products,sku'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'in:' . implode(',', array_keys(config('sera.product_units', ['adet' => 'Adet'])))],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'min_order_quantity' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ], [
            'category_id.required' => 'Kategori seçin.',
            'name.required' => 'Ürün adı gerekli.',
            'price.required' => 'Fiyat gerekli.',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['stock_quantity'] = $validated['stock_quantity'] ?? 0;
        $validated['min_order_quantity'] = $validated['min_order_quantity'] ?? 1;

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
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug,' . $product->id],
            'sku' => ['nullable', 'string', 'max:50', 'unique:products,sku,' . $product->id],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'in:' . implode(',', array_keys(config('sera.product_units', ['adet' => 'Adet'])))],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'min_order_quantity' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['boolean'],
        ], [
            'category_id.required' => 'Kategori seçin.',
            'name.required' => 'Ürün adı gerekli.',
            'price.required' => 'Fiyat gerekli.',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['stock_quantity'] = $validated['stock_quantity'] ?? 0;
        $validated['min_order_quantity'] = $validated['min_order_quantity'] ?? 1;

        $product->update($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Ürün güncellendi.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Ürün silindi.');
    }
}
