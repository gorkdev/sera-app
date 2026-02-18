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
| `dealer.auth` | EnsureDealerAuthenticated | Bayi giriş kontrolü (şu an passthrough) |

### Kayıt Yeri

`bootstrap/app.php` → `withMiddleware()` → `alias`

### Henüz Eklenmemiş (Spec'te Planlanan)

- `dealer.approved` — Email doğrulama + status kontrolü
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
├── Controller.php          # Base
├── Admin/
│   └── AuthController.php  # showLoginForm, login, logout
├── Dealer/
│   ├── AuthController.php  # showAuth, login, register, logout
│   └── DealerController.php
```

### Admin\AuthController

- **showLoginForm()** — `auth.admin.login` view
- **login()** — Validasyon, attempt, is_active kontrolü, credential hataları `credentials` key ile
- **logout()** — admin.login'e yönlendirme

### Dealer\AuthController

- **showAuth()** — `auth.dealer.auth` view (giriş + kayıt flip kart)
- **login()** — Validasyon, attempt, status kontrolü (pending/active/passive)
- **register()** — Dealer kayıt, success flash mesajı
- **logout()** — dealer.login'e yönlendirme
