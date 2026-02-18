# Sera — Dokümantasyon

B2B toptan çiçek satış platformu projesinin teknik dokümantasyonu.

---

## İçindekiler

| Doküman | Açıklama |
|---------|----------|
| [Proje Yapısı](./PROJECT-STRUCTURE.md) | Dizin yapısı, dosya konumları |
| [Mimari](./ARCHITECTURE.md) | Auth, middleware, rota organizasyonu |
| [Frontend](./FRONTEND.md) | Layouts, Tailwind, DaisyUI |
| [Auth Formları](./AUTH-FORMS.md) | Giriş/kayıt formları, validasyon, flip kart, modal |
| [Veritabanı](./DATABASE.md) | Modeller, migrations, seeders |
| [Rotalar](./ROUTES.md) | URL yapısı ve route referansı |
| [Konfigürasyon](./CONFIG.md) | config/sera.php ayarları |

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
7. **Auth formları** — Admin ve Bayi giriş, Bayi kayıt (flip kart)
8. **Form validasyonu** — Client-side (login-form.js, register-form.js)
9. **Şifremi unuttum** — DaisyUI modal (tasarım amaçlı)
10. **Heroicons** — blade-ui-kit/blade-heroicons ile ikon kullanımı

### Kategoriler Modülü
11. **Kategoriler CRUD** — Hiyerarşik (parent_id), slug, sıra, aktif/pasif
12. **Sezon & erişim** — Sezon başlangıç/bitiş ayı, sezon dışı pasif, bayi gruplarına görünürlük, parti başına kota
13. **Ürün nitelikleri** — Zorunlu/görünür nitelikler (config'den), bölge kısıtı (şehir/bölge tag input)
14. **Gelişmiş ayarlar** — Hasat süresi, ideal iklim (sıcaklık/nem), zorunlu belgeler, MoQ, kâr marjı, renk, ikon, rozetler
15. **Kategori görselleri** — `storage/app/public/categories/` altında yükleme
16. **Dealer groups** — Bayi grupları tablosu, kategori görünürlük kısıtı

### Ürünler Modülü
17. **Ürünler CRUD** — Kategori, SKU, fiyat, birim, stok, min sipariş, filtreleme
