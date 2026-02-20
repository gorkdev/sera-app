# Sera — Dokümantasyon

B2B toptan çiçek satış platformu projesinin teknik dokümantasyonu.

---

## İçindekiler

| Doküman                                | Açıklama                                           |
| -------------------------------------- | -------------------------------------------------- |
| [Proje Yapısı](./PROJECT-STRUCTURE.md) | Dizin yapısı, dosya konumları                      |
| [Mimari](./ARCHITECTURE.md)            | Auth, middleware, rota organizasyonu               |
| [Frontend](./FRONTEND.md)              | Layouts, Tailwind, DaisyUI                         |
| [Auth Formları](./AUTH-FORMS.md)       | Giriş/kayıt formları, validasyon, flip kart, modal |
| [Veritabanı](./DATABASE.md)            | Modeller, migrations, seeders                      |
| [Sepet](./CART.md)                     | DB sepeti, Livewire, anında güncelleme, polling    |
| [Rotalar](./ROUTES.md)                 | URL yapısı ve route referansı                      |
| [Konfigürasyon](./CONFIG.md)           | config/sera.php ayarları                           |

---

## Proje Özeti

**Sera**, çiçek tedarikçilerinin bayilere (çiçekçilere) toptan satış yaptığı B2B e-ticaret platformudur.

- **Teknoloji:** Laravel 12, PHP 8.2+, MySQL/SQLite
- **Frontend:** Tailwind CSS 4, DaisyUI 5, Vite 7
- **Kullanıcı tipleri:** Ziyaretçi, Bayi, Admin, Super Admin

---

## Hızlı Başlangıç

```bash
# Bağımlılıklar
composer install
npm install

# Ortam
cp .env.example .env
php artisan key:generate

# Veritabanı
php artisan migrate --seed

# Storage link (kategori görselleri için)
php artisan storage:link

# Asset build
npm run build

# Sunucu
php artisan serve
```

**URL'ler:**

- Anasayfa: `http://localhost:8000`
- Bayi giriş: `http://localhost:8000/giris`
- Admin giriş: `http://localhost:8000/yonetim/giris`
- Admin kategoriler: `http://localhost:8000/yonetim/kategoriler`
- Admin ürünler: `http://localhost:8000/yonetim/urunler`

---

## Şu Ana Kadar Yapılanlar

### Temel Altyapı

1. **Temel kurulum** — Laravel 12, Tailwind, DaisyUI
2. **Çoklu auth** — Admin ve Dealer guard'ları
3. **Layouts** — Public, Admin, Dealer layout'ları
4. **Responsive UI** — Mobil uyumlu navbar, drawer sidebar
5. **Modeller** — AdminUser, Dealer, Category, Product, DealerGroup
6. **config/sera.php** — Sepet, KDV, sipariş, SMS, site, kategori ayarları

### Auth

7. **Auth formları** — Admin ve Bayi giriş, Bayi kayıt (artık ayrı sayfalar; solda görsel, sağda form layout)
8. **Form validasyonu** — Client-side (login-form.js, register-form.js) + Livewire tarafında server-side doğrulama
9. **Bayi kayıt akışı** — Livewire ile sayfa yenilenmeden kayıt, TR telefon formatı, il/ilçe doğrulaması, KVKK onayı, vergi no/TCKN otomatik algılama
10. **Bayi email doğrulama** — 6 haneli kod, TTL, tekrar gönderme (rate limited), deneme limiti, maskeleme, detaylı hata mesajları
11. **Şifremi unuttum** — DaisyUI modal (tasarım amaçlı)
12. **Heroicons** — blade-ui-kit/blade-heroicons ile ikon kullanımı

### Kategoriler Modülü

11. **Kategoriler CRUD** — Hiyerarşik (parent_id), slug, sıra, aktif/pasif
12. **Kategori görselleri** — `storage/app/public/categories/` altında yükleme
13. Kategori her zaman açık; panelde pasife alırsa görünmez. Bölge/sezon kısıtı yok.

### Ürünler Modülü

14. **Ürünler CRUD** — Kategori, SKU, fiyat, birim, stok, min sipariş, filtreleme
15. Ürün her zaman açık; panelde pasife alırsa görünmez.

### Sepet

17. **Veritabanı sepeti** — Bayi girişli sepet `carts` / `cart_items` tablolarında; aynı bayi tüm cihazlarda aynı sepeti görür.
18. **CartService** — Sepet CRUD, getOrCreateCart, getItemsForDisplay, getTotalQuantity, login sonrası session sepetini DB’ye merge.
19. **Livewire CartIcon** — Navbar badge + sağ drawer; `cart-updated` event’i ile sayfa yenilenmeden güncelleme; badge ve drawer içeriği `render()` ile güncel veri.
20. **Global toast** — Layout’ta `show-toast` dinleyicisi; sepete ekleme vb. mesajları tüm sayfalarda gösterilir.
21. **Çoklu cihaz/sekme** — Bayi girişliyken `wire:poll.5s` ile periyodik yenileme; ikinci cihazda yapılan değişiklik en geç 5 saniyede yansır.

### Testler

22. **Feature testler** — Bayi kayıt + email doğrulama + login akışları (tüm kritik durumlar), Admin login, Admin kategori/ürün oluşturma için uçtan uca testler.
