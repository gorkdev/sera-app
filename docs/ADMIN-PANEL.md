# Yönetici Paneli

Admin kullanıcıların giriş yapabildiği ve kategoriler ile ürünleri yönettiği yönetim arayüzü.

---

## Genel Bakış

| Özellik | Açıklama |
|--------|----------|
| Giriş | `auth/admin/login.blade.php` üzerinden, `admin` guard ile |
| Dashboard | `admin.dashboard` — Özet panel, giriş sonrası açılış sayfası |
| Kategoriler | Çok seviyeli (parent/child) hiyerarşi, sıralama, görsel, aktif/pasif |
| Ürünler | Kategori, SKU, fiyat, stok, birim, galerili görseller, rozetler, menşei |

---

## Giriş ve Yetkilendirme

- **Guard:** `admin` (`config/auth.php` → `admin_users` provider).
- **Controller:** `App\Http\Controllers\Admin\AuthController`.
- **Rotalar:**
  - `GET /yonetim/giris` → `admin.login` → login formu.
  - `POST /yonetim/giris` → `admin.login.submit` → login işlemi.
  - `POST /yonetim/cikis` → `admin.logout` → çıkış.
  - `GET /yonetim` → `admin.dashboard` → dashboard.
- **Kurallar:**
  - `is_active = true` olmayan admin kullanıcılar giriş yapamaz (credential hatası döner).
  - Oturum açmış admin kullanıcılar `admin.auth` middleware’i ile `/yonetim/*` altında korunur.

---

## Kategori Yönetimi

### Rotalar

- `GET /yonetim/kategoriler` → `admin.categories.index`
- `POST /yonetim/kategoriler/sirala` → `admin.categories.reorder`
- `GET /yonetim/kategoriler/olustur` → `admin.categories.create`
- `POST /yonetim/kategoriler` → `admin.categories.store`
- `GET /yonetim/kategoriler/{category:slug}/duzenle` → `admin.categories.edit`
- `PUT /yonetim/kategoriler/{category:slug}` → `admin.categories.update`
- `DELETE /yonetim/kategoriler/{category:slug}` → `admin.categories.destroy`

Tümü `admin.auth` middleware’i ile korunur.

### Model ve Alanlar

- Model: `App\Models\Category`
- Önemli alanlar:
  - `parent_id` — Null ise ana kategori, doluysa alt kategori.
  - `name`, `slug`, `description`.
  - `image` — `storage/app/public/categories/` altında saklanır.
  - `sort_order` — Listeleme sırası.
  - `is_active` — Panelde pasife alınan kategoriler frontend’de gizlenebilir.

### Oluşturma (`store`)

- **Validasyon:**
  - `parent_id` → `nullable|exists:categories,id`
  - `name` → `required|string|max:255`
  - `slug` → `nullable|string|max:255|regex:/^[a-z0-9-]+$/|unique:categories,slug`
  - `description` → `nullable|string`
  - `image` → `nullable|image|max:2048`
  - `sort_order` → `nullable|integer|min:0`
  - `is_active` → `boolean`
- **Davranış:**
  - `is_active` → checkbox’a göre boolean’a çevrilir.
  - `sort_order` boş ise `0`.
  - Görsel yüklenmişse `public` diskine `categories/` altında kaydedilir.
  - Başarılı olursa `admin.categories.index`’e success flash mesajıyla redirect.

### Güncelleme (`update`)

- **Validasyon:**
  - `parent_id` → kendisini parent olarak seçemez (custom closure).
  - `name`, `slug`, `description`, `image`, `sort_order`, `is_active` alanları `store` ile benzer.
  - `slug` → güncel kategori haricinde unique.
- **Görsel Yönetimi:**
  - Yeni `image` yüklendiyse eski görsel `Storage::disk('public')` üzerinden silinir.
  - `remove_image` işaretliyse mevcut görsel silinir ve `image = null` olur.
- **Sonuç:**
  - Kategori güncellenir, `admin.categories.index`’e success mesajıyla redirect.

### Silme (`destroy`)

- **Kurallar:**
  - Eğer kategorinin **ürünü** varsa:
    - Silme yapılmaz, `error` flash mesajı ile geri dönülür:
      - "Bu kategoride ürün var. Önce ürünleri taşıyın veya silin."
  - Eğer kategorinin **alt kategorisi** varsa:
    - Silme yapılmaz, `error` flash mesajı ile geri dönülür:
      - "Alt kategorisi olan kategori silinemez. Önce alt kategorileri silin."
  - Hiç ürün ve alt kategori yoksa:
    - Soft delete ile kategori silinir ve `success` mesajıyla index’e redirect edilir.

### Sıralama (`reorder`)

- Endpoint: `POST /yonetim/kategoriler/sirala` (`admin.categories.reorder`)
- **Request:**
  - `ids` → sıralanacak kategori ID’leri [int,...].
  - `ust` → `"ana"` (root), `"tumu"` (izin verilmez) veya belirli bir parent_id (string).
- **Kurallar:**
  - `"tumu"` görünümünde sıralama yapılamaz → 422 JSON hatası.
  - `ust` sayısal değilse → 422 JSON hatası.
  - Gönderilen `ids`’deki tüm kategoriler:
    - Root sıralaması için `parent_id = null` olmalı,
    - Alt kategori sıralaması için hepsi aynı `parent_id`’ye sahip olmalı.
- **Davranış:**
  - Transaction içinde her kategoriye sırayla `sort_order = index` atanır.
  - Başarılı olursa `{"ok": true}` içeren JSON döner.

---

## Ürün Yönetimi

### Rotalar

- `GET /yonetim/urunler` → `admin.products.index`
- `GET /yonetim/urunler/olustur` → `admin.products.create`
- `POST /yonetim/urunler` → `admin.products.store`
- `GET /yonetim/urunler/{product:slug}/duzenle` → `admin.products.edit`
- `PUT /yonetim/urunler/{product:slug}` → `admin.products.update`
- `DELETE /yonetim/urunler/{product:slug}` → `admin.products.destroy`

Hepsi `admin.auth` middleware’i ile korunur.

### Model ve Alanlar

- Model: `App\Models\Product`
- Önemli alanlar:
  - `category_id` — Zorunlu; sadece mevcut kategorilere bağlanabilir.
  - `name`, `slug`, `sku`, `description`.
  - `image` — Ana görsel (`storage/app/public/products/`).
  - `gallery_images` — Ek görseller dizisi.
  - `price`, `cost_price`, `unit`, `stock_quantity`, `min_order_quantity`.
  - `unit_conversions` — Birim dönüşümleri (ör. koli → adet).
  - `featured_badges` — Öne çıkarma rozetleri (`config('sera.product_featured_badges')`’ten).
  - `origin` — Menşei bilgisi (`config('sera.origin_options')`’ten).
  - `shelf_life_days` — Raf ömrü (gün).

### Oluşturma (`store`)

- **Ön işleme:**
  - `name`, `slug`, `description` trim edilir.
  - `sku` uppercase’e çevrilir.

- **Validasyon (özet):**
  - `category_id` → `required|exists:categories,id`
  - `name` → `required|string|max:255`
  - `slug` → `required|string|max:255|regex:/^[a-z0-9-]+$/|unique:products,slug`
  - `sku` → `required|string|max:50|unique:products,sku`
  - `description` → `required|string|min:10`
  - `image` → `required|image|max:2048`
  - `gallery_images` → `required|array|min:1`, her biri `image|max:2048`
  - `price`, `cost_price` → `required|numeric|min:0`
  - `unit` → `required|in:{config('sera.product_units') anahtarları}`
  - `unit_conversions.*` → geçerli birim ve `adet >= 1`
  - `stock_quantity` → `required|integer|min:0`
  - `min_order_quantity` → `required|integer|min:1`
  - `is_active` → `required|accepted`
  - `featured_badges.*` ve `origin` → ilgili config anahtarlarıyla uyumlu.

- **Görseller:**
  - `image` → `products/` altında tek dosya.
  - `gallery_images` → döngüyle `products/` altında kaydedilir ve path listesi DB’ye yazılır.

- **Birim dönüşümleri:**
  - `parseUnitConversions` helper’ı ile filtrelenir.
  - En az bir geçerli satır yoksa:
    - `ValidationException` ile `unit_conversions` alanı için hata döner.

- **Sonuç:**
  - Ürün oluşturulur, `admin.products.index`’e success flash mesajıyla redirect edilir.

### Güncelleme (`update`)

- **Validasyon:** `store` ile büyük oranda aynı, ancak:
  - `slug` ve `sku` → kendi ID’si hariç unique.
  - `image` artık `nullable` (zorunlu değil).
  - `gallery_images` nullable; ama aşağıdaki kurallarla en az bir görsel korunur.

- **Ana Görsel Kuralı:**
  - Eğer yeni `image` yüklenmemişse **ve**:
    - `remove_image = true` **veya** üründe zaten image yoksa →  
      `image` alanı için `Ana görsel gerekli.` hatası ile geri döner.

- **Galeri Kuralı:**
  - Mevcut `gallery_images` dizisi + `gallery_remove` parametresi ile:
    - Kalan (silinmeyen) mevcut görseller sayılır.
    - Yeni yüklenen `gallery_images` adedi eklenir.
    - Toplam `< 1` ise → `gallery_images` için `En az 1 adet alt görsel gerekli.` hatasıyla geri döner.

- **Görsel Güncelleme:**
  - Yeni ana görsel yüklendiyse:
    - Eski görsel `Storage::disk('public')` üzerinden silinir.
  - `remove_image` işaretliyse:
    - Eski görsel silinir, `image = null`.
  - Galeri güncellemede:
    - `gallery_remove` ile işaretlenen eski görseller silinir.
    - Kalanlar + yeni yüklenenler birleştirilip dizi olarak kaydedilir.

- **Sonuç:**
  - Ürün güncellenir ve `admin.products.index`’e success mesajıyla redirect edilir.

### Silme (`destroy`)

- **Davranış:**
  - Varsa ana görsel dosyası silinir.
  - `gallery_images` içinde kayıtlı tüm görsel path’leri silinir.
  - Ürün soft delete veya normal delete (modele göre) ile silinir.
  - `admin.products.index`’e success flash mesajıyla redirect edilir.

---

## Test Kapsamı (Feature Testler)

`tests/Feature/AdminCategoryProductTest.php` dosyası, yönetici paneli için şu akışları **uçtan uca** test eder:

1. **admin_can_view_category_and_product_pages**
   - Admin login iken:
     - `/yonetim/kategoriler` ve `/yonetim/urunler` sayfalarının 200 OK döndüğünü doğrular.

2. **admin_can_create_category_and_product**
   - Yeni bir kategori oluşturur ve DB’de slug üzerinden bulur.
   - Bu kategoriye bağlı, zorunlu tüm alanlarla (ana görsel + en az bir galeri görseli + unit_conversions) bir ürün oluşturur.
   - Oluşan ürünün gerçekten bu kategoriye bağlı olduğunu assert eder.

3. **admin_can_update_category_and_delete_empty_category**
   - Basit bir kategori oluşturur.
   - `PUT /yonetim/kategoriler/{slug}` ile name/slug/description alanlarını günceller.
   - Güncellenmiş değerleri DB’den doğrular.
   - Ürün ve alt kategori ilişkisi olmayan bu kategoriyi `DELETE` ile siler ve DB’den kaybolduğunu kontrol eder.

4. **category_with_children_or_products_cannot_be_deleted**
   - Üst kategori + alt kategori senaryosu kurar:
     - Üst kategoriyi silmeye çalıştığında session’da `error` flash mesajı bekler ve kategorinin hâlâ DB’de olduğunu doğrular.
   - Ayrı bir kategoriyi bir ürün ile ilişkilendirir:
     - Ürün bağlıyken kategori silme denemesinde yine `error` flash mesajı ve kategori kaydının korunmasını bekler.

5. **admin_can_reorder_child_categories**
   - Aynı parent altında iki alt kategori oluşturur (`sort_order` 0 ve 1).
   - `POST /yonetim/kategoriler/sirala` ile ID dizisini ters çevirip gönderir.
   - Response’un `ok` JSON döndürdüğünü ve `sort_order` değerlerinin gerçekten tersine döndüğünü kontrol eder.

6. **admin_can_update_and_delete_product_with_images**
   - Ana ve galeri görsel path’leri olan bir ürün oluşturur.
   - `PUT /yonetim/urunler/{slug}` ile:
     - Yeni ana görsel ve yeni galeri görseli yükler,
     - Eski galeriyi `gallery_remove` ile sildirir,
     - Name/slug/description, stok, fiyat gibi alanları değiştirir.
   - Güncel ürün verilerini DB’den doğrular (isim, slug, galeri görsel sayısı).
   - Son olarak `DELETE /yonetim/urunler/{slug}` ile ürünü siler ve DB’de bulunmadığını assert eder.

Bu testler sayesinde:

- Yönetici panelinde kategori/ürün listeleme, oluşturma, güncelleme, silme ve kategori sıralama gibi temel tüm iş akışları **bozulduğunda kırılacak** şekilde güvence altına alınmıştır.

