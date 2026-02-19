# Mimari

Auth yapısı, middleware zinciri ve rota organizasyonu.

---

## Authentication Guards

`config/auth.php` içinde 3 guard tanımlı:

| Guard | Provider | Model | Kullanım |
|-------|----------|-------|----------|
| `web` | users | User | Standart Laravel (minimal) |
| `admin` | admin_users | AdminUser | Yönetim paneli |
| `dealer` | dealers | Dealer | Bayi paneli |

### Kullanım

```php
// Admin giriş kontrolü
auth()->guard('admin')->check();
auth()->guard('admin')->user();

// Bayi giriş kontrolü
auth()->guard('dealer')->check();
auth()->guard('dealer')->user();
```

---

## Middleware

### Tanımlı Middleware

| Alias | Sınıf | Görevi |
|-------|-------|--------|
| `admin.auth` | EnsureAdminAuthenticated | Admin giriş yoksa `admin.login`'e yönlendirir |
| `dealer.auth` | EnsureDealerAuthenticated | Bayi giriş kontrolü (panel erişimi) |

### Kayıt Yeri

`bootstrap/app.php` → `withMiddleware()` → `alias`

### Henüz Eklenmemiş (Spec'te Planlanan)

- `dealer.approved` — Email doğrulama + status kontrolü (şu anda login içinde yapılmakta)
- `dealer.group.time` — Grup bazlı stok erişim zamanlaması
- `party.active` — Aktif parti var mı kontrolü

---

## Rota Organizasyonu

### Dosya Bazlı

| Dosya | Prefix | Middleware | İçerik |
|-------|--------|------------|--------|
| `web.php` | - | web | Anasayfa, giriş sayfaları |
| `admin.php` | /yonetim | web, admin.auth | Yönetim paneli |
| `dealer.php` | - | web, dealer.auth | Bayi paneli |

### Yükleme Sırası

1. `web.php` — Ana rota dosyası
2. `admin.php` — `Route::prefix('yonetim')->group()`
3. `dealer.php` — Prefix yok

---

## Controller Yapısı

```
app/Http/Controllers/
├── Controller.php                    # Base
├── Admin/
│   └── AuthController.php            # showLoginForm, login, logout
├── Dealer/
│   ├── AuthController.php            # showLoginForm, showRegisterForm, login, register, logout
│   ├── EmailVerificationController.php # Email doğrulama akışı (show/verify/resend)
│   └── DealerController.php
```

### Admin\AuthController

- **showLoginForm()** — `auth.admin.login` view
- **login()** — Validasyon, attempt, is_active kontrolü, credential hataları `credentials` key ile
- **logout()** — admin.login'e yönlendirme

### Dealer\AuthController

- **showLoginForm()** — `auth.dealer.login` view (eğer zaten giriş yapmışsa `panel`'e redirect)
- **showRegisterForm()** — `auth.dealer.register` view (Livewire kayıt formunu içerir)
- **login()** — Validasyon, attempt, email doğrulama ve status (pending/active/passive/blocked) kontrolü:
  - Email doğrulanmamışsa → logout + session'da `dealer_verification_id` ve `needs_email_verification` flash'ı,
  - Email doğrulanmış ama `status !== active` ise → uygun flash mesajı (`admin_pending`, `dealer_passive`, `dealer_blocked`),
  - Email doğrulanmış ve `status = active` ise → `panel`'e redirect.
- **register()** — Validasyon, Dealer oluşturma, `dealer_verification_id` session'a yazma, `DealerEmailVerificationService` ile kod gönderme ve `dealer.verify.show` sayfasına redirect.
- **logout()** — dealer.login'e yönlendirme

### Dealer\EmailVerificationController

- **show()** — Session'daki `dealer_verification_id` üzerinden bayi bulur; email zaten doğrulanmışsa login sayfasına success mesajıyla döner, aksi halde maskeli email ve kalan cooldown ile verify sayfasını render eder.
- **verify()** — RateLimiter ile deneme limiti + kod format validasyonu + `DealerEmailVerificationService::verify` çağrısı; başarılıysa bayi `email_verified_at` doldurulur, session'dan `dealer_verification_id` silinir ve login sayfasına success mesajıyla redirect edilir.
- **resend()** — Rate limiting kurallarına göre yeni kod üretip gönderir, JSON veya redirect ile kullanıcıya cooldown bilgisini döner.
