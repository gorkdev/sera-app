# Sera — Proje Durumu

B2B toptan çiçek satış platformu. Bu dosya projenin genel durumunu özetler.

---

## Genel Bakış

**Sera**, çiçek tedarikçilerinin bayilere (çiçekçilere) toptan satış yaptığı bir e-ticaret platformudur.

- **Stack:** Laravel 12, Tailwind CSS 4, DaisyUI 5, Vite 7
- **Kullanıcı tipleri:** Admin (yonetim), Bayi (çiçekçi), Ziyaretçi

---

## Mevcut Durum

### Tamamlanan Modüller
- **Auth** — Admin ve Bayi giriş/kayıt, çoklu guard, email doğrulama, admin onay akışı
- **Bayi kayıt akışı** — Livewire tabanlı form (sayfa yenilenmeden), TR telefon formatı, il/ilçe doğrulaması, KVKK onayı, vergi no/TCKN otomatik algılama
- **Bayi email doğrulama** — 6 haneli kod, süre (TTL), tekrar gönderme, deneme sayısı limiti, rate limiting, maskeleme, detaylı hata mesajları
- **Admin panel** — Dashboard, Kategoriler, Ürünler (CRUD)
- **Kategoriler** — Hiyerarşik yapı, görsel, aktif/pasif (her zaman açık; panelde pasife alırsa görünmez)
- **Ürünler** — Kategori, SKU, fiyat, birim, stok, min sipariş (her zaman açık; panelde pasife alırsa görünmez)

### Testler
- **Feature testler** — Bayi kayıt + email doğrulama + login akışları (tüm kritik durumlar), Admin login, Admin kategori/ürün oluşturma uçtan uca test ediliyor.

### Henüz Tamamlanmamış
- Bayi katalog (ürün listesi)
- Sepet (zamanlayıcılı)
- Sipariş modülü
- Parti modülü
- Bayi yönetimi (admin)
- Şifremi unuttum (gerçek akış)

---

## Detaylı Dokümantasyon

Proje hakkında daha detaylı bilgi için **`docs/`** klasörüne bakın:

| Dosya | İçerik |
|-------|--------|
| [docs/README.md](docs/README.md) | Dokümantasyon genel bakış, içindekiler, hızlı başlangıç |
| [docs/DATABASE.md](docs/DATABASE.md) | Veritabanı şeması, modeller, migrations |
| [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) | Auth, middleware, rota organizasyonu |
| [docs/CONFIG.md](docs/CONFIG.md) | config/sera.php ayarları |
| [docs/ROUTES.md](docs/ROUTES.md) | URL yapısı ve route referansı |
| [docs/PROJECT-STRUCTURE.md](docs/PROJECT-STRUCTURE.md) | Dizin yapısı, dosya konumları |
| [docs/FRONTEND.md](docs/FRONTEND.md) | Layouts, Tailwind, DaisyUI |
| [docs/AUTH-FORMS.md](docs/AUTH-FORMS.md) | Giriş/kayıt formları |

---

## Hızlı Başlangıç

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
php artisan storage:link   # Kategori görselleri için
npm run build
php artisan serve
```

Tarayıcıda: http://localhost:8000

| Rol | E-posta | Şifre | Giriş |
|----|---------|-------|-------|
| Admin | admin@sera.com | 123456 | /yonetim/giris |
| Bayi | bayi@test.com | 123456 | /giris |
