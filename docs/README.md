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

# Asset build
npm run build

# Sunucu
php artisan serve
```

**URL'ler:**
- Anasayfa: `http://localhost:8000`
- Bayi giriş: `http://localhost:8000/giris`
- Admin giriş: `http://localhost:8000/yonetim/giris`

---

## Şu Ana Kadar Yapılanlar

1. **Temel kurulum** — Laravel 12, Tailwind, DaisyUI
2. **Çoklu auth** — Admin ve Dealer guard'ları
3. **Layouts** — Public, Admin, Dealer layout'ları
4. **Responsive UI** — Mobil uyumlu navbar, drawer sidebar
5. **Modeller** — AdminUser, Dealer (User standart)
6. **Migrations** — admin_users, dealers tabloları
7. **Seeder** — Varsayılan admin (admin@sera.com / 123456)
8. **config/sera.php** — Sepet, KDV, sipariş, SMS, site ayarları
9. **Auth formları** — Admin ve Bayi giriş, Bayi kayıt (flip kart)
10. **Heroicons** — blade-ui-kit/blade-heroicons ile ikon kullanımı
11. **Form validasyonu** — Client-side (login-form.js, register-form.js), credential hataları sadece alert'ta
12. **Bayi kayıt** — 2 sütunlu grid, dinamik yükseklik, flip animasyonu
13. **Şifremi unuttum** — DaisyUI modal (tasarım amaçlı)
14. **Admin/Bayi form uyumu** — Birebir aynı yapı, genişlik, auth-card, Anasayfaya Dön alt kısımda
15. **Bayi login autofocus** — Sayfa açıldığında e-posta alanına focus
