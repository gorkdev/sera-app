<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminCategoryProductTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsAdmin(): AdminUser
    {
        /** @var \App\Models\AdminUser $admin */
        $admin = AdminUser::factory()->create([
            'is_active' => true,
            'password' => bcrypt('secret123'),
        ]);

        $this->actingAs($admin, 'admin');

        return $admin;
    }

    #[Test]
    public function admin_can_view_category_and_product_pages()
    {
        $this->actingAsAdmin();

        $this->get('/yonetim/kategoriler')->assertStatus(200);
        $this->get('/yonetim/urunler')->assertStatus(200);
    }

    #[Test]
    public function admin_can_create_category_and_product()
    {
        $this->actingAsAdmin();

        Storage::fake('public');

        $categoryResponse = $this->post('/yonetim/kategoriler', [
            'name' => 'Kesme Çiçekler',
            'slug' => 'kesme-cicekler',
            'description' => 'Test kategori',
            'is_active' => true,
        ]);

        $categoryResponse->assertStatus(302);
        $category = Category::where('slug', 'kesme-cicekler')->firstOrFail();

        $productResponse = $this->post('/yonetim/urunler', [
            'category_id' => $category->id,
            'name' => 'Gül',
            'slug' => 'gul',
            'sku' => 'GUL-001',
            'description' => 'Bu bir test ürünüdür.',
            'image' => UploadedFile::fake()->image('main.jpg'),
            'gallery_images' => [
                UploadedFile::fake()->image('gallery-1.jpg'),
            ],
            'price' => 100,
            'cost_price' => 50,
            'unit' => 'adet',
            'stock_quantity' => 10,
            'min_order_quantity' => 1,
            'unit_conversions' => [
                ['unit' => 'adet', 'adet' => 1],
            ],
            'is_active' => 'on',
        ]);

        $productResponse->assertStatus(302);
        $product = Product::where('slug', 'gul')->firstOrFail();

        $this->assertEquals($category->id, $product->category_id);
    }

    #[Test]
    public function admin_can_update_category_and_delete_empty_category()
    {
        $this->actingAsAdmin();

        Storage::fake('public');

        $category = Category::create([
            'name' => 'Eski İsim',
            'slug' => 'eski-isim',
            'description' => 'Eski açıklama',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $response = $this->put("/yonetim/kategoriler/{$category->slug}", [
            'name' => 'Yeni İsim',
            'slug' => 'yeni-isim',
            'description' => 'Yeni açıklama',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.categories.index'));

        $category->refresh();
        $this->assertEquals('Yeni İsim', $category->name);
        $this->assertEquals('yeni-isim', $category->slug);

        $deleteResponse = $this->delete("/yonetim/kategoriler/{$category->slug}");
        $deleteResponse->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseMissing('categories', ['slug' => 'yeni-isim']);
    }

    #[Test]
    public function category_with_children_or_products_cannot_be_deleted()
    {
        $this->actingAsAdmin();

        $parent = Category::create([
            'name' => 'Üst Kategori',
            'slug' => 'ust-kategori',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $child = Category::create([
            'parent_id' => $parent->id,
            'name' => 'Alt Kategori',
            'slug' => 'alt-kategori',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $productCategory = Category::create([
            'name' => 'Ürünlü Kategori',
            'slug' => 'urunlu-kategori',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $productCategory->id,
            'name' => 'Test Ürün',
            'slug' => 'test-urun',
            'sku' => 'TST-001',
            'description' => 'Açıklama en az on karakter',
            'image' => null,
            'gallery_images' => null,
            'price' => 10,
            'cost_price' => 5,
            'unit' => 'adet',
            'stock_quantity' => 0,
            'min_order_quantity' => 1,
            'is_active' => true,
        ]);

        $responseParent = $this->from(route('admin.categories.index'))
            ->delete("/yonetim/kategoriler/{$parent->slug}");
        $responseParent->assertRedirect(route('admin.categories.index'));
        $responseParent->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $parent->id]);

        $responseProductCat = $this->from(route('admin.categories.index'))
            ->delete("/yonetim/kategoriler/{$productCategory->slug}");
        $responseProductCat->assertRedirect(route('admin.categories.index'));
        $responseProductCat->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $productCategory->id]);
    }

    #[Test]
    public function admin_can_reorder_child_categories()
    {
        $this->actingAsAdmin();

        $parent = Category::create([
            'name' => 'Üst Kategori',
            'slug' => 'ust-kategori',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $c1 = Category::create([
            'parent_id' => $parent->id,
            'name' => 'Alt 1',
            'slug' => 'alt-1',
            'sort_order' => 0,
            'is_active' => true,
        ]);
        $c2 = Category::create([
            'parent_id' => $parent->id,
            'name' => 'Alt 2',
            'slug' => 'alt-2',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->post('/yonetim/kategoriler/sirala', [
            'ids' => [$c2->id, $c1->id],
            'ust' => (string) $parent->id,
        ]);

        $response->assertOk();
        $this->assertSame(0, Category::find($c2->id)->sort_order);
        $this->assertSame(1, Category::find($c1->id)->sort_order);
    }

    #[Test]
    public function admin_can_update_and_delete_product_with_images()
    {
        $this->actingAsAdmin();
        Storage::fake('public');

        $category = Category::create([
            'name' => 'Kategori',
            'slug' => 'kategori',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Eski Ürün',
            'slug' => 'eski-urun',
            'sku' => 'ESK-001',
            'description' => 'Açıklama en az on karakter',
            'image' => 'products/old-main.jpg',
            'gallery_images' => ['products/old-1.jpg'],
            'price' => 10,
            'cost_price' => 5,
            'unit' => 'adet',
            'stock_quantity' => 0,
            'min_order_quantity' => 1,
            'is_active' => true,
            'unit_conversions' => [
                ['unit' => 'adet', 'adet' => 1],
            ],
        ]);

        $response = $this->put("/yonetim/urunler/{$product->slug}", [
            'category_id' => $category->id,
            'name' => 'Yeni Ürün',
            'slug' => 'yeni-urun',
            'sku' => 'YEN-001',
            'description' => 'Yeni açıklama en az on karakter',
            'image' => UploadedFile::fake()->image('new-main.jpg'),
            'gallery_images' => [
                UploadedFile::fake()->image('new-1.jpg'),
            ],
            'price' => 20,
            'cost_price' => 10,
            'unit' => 'adet',
            'stock_quantity' => 5,
            'min_order_quantity' => 1,
            'unit_conversions' => [
                ['unit' => 'adet', 'adet' => 1],
            ],
            'is_active' => 'on',
            'gallery_remove' => [0],
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product->refresh();
        $this->assertEquals('Yeni Ürün', $product->name);
        $this->assertEquals('yeni-urun', $product->slug);
        $this->assertEquals(1, count($product->gallery_images ?? []));

        $deleteResponse = $this->delete("/yonetim/urunler/{$product->slug}");
        $deleteResponse->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseMissing('products', ['slug' => 'yeni-urun']);
    }
}

