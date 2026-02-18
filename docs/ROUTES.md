# Rotalar

URL yapısı ve route referansı.

---

## Rota Özeti

| Method | URL | Ad | Açıklama |
|--------|-----|-----|----------|
| GET | `/` | home | Anasayfa |
| GET | `/giris` | dealer.login | Bayi giriş + kayıt sayfası |
| POST | `/giris` | dealer.login.submit | Bayi giriş işlemi |
| POST | `/kayit` | dealer.register.submit | Bayi kayıt işlemi |
| POST | `/cikis` | dealer.logout | Bayi çıkış |
| GET | `/yonetim/giris` | admin.login | Admin giriş sayfası |
| POST | `/yonetim/giris` | admin.login.submit | Admin giriş işlemi |
| POST | `/yonetim/cikis` | admin.logout | Admin çıkış |
| GET | `/panel` | panel | Bayi paneli (dealer.auth) |
| GET | `/yonetim` | dashboard | Admin dashboard (admin.auth) |

---

## Dosya Bazlı Detay

### web.php (Public)

| Route | Controller | View |
|-------|------------|------|
| `GET /` | Closure | home |
| `GET /giris` | Dealer\AuthController@showAuth | auth.dealer.auth |
| `POST /giris` | Dealer\AuthController@login | - |
| `POST /kayit` | Dealer\AuthController@register | redirect dealer.login |
| `GET /yonetim/giris` | Admin\AuthController@showLoginForm | auth.admin.login |
| `POST /yonetim/giris` | Admin\AuthController@login | - |

### admin.php (Prefix: /yonetim)

| Route | Middleware | Açıklama |
|-------|------------|----------|
| `GET /` | admin.auth | Dashboard (placeholder metin) |

### dealer.php

| Route | Middleware | Controller |
|-------|------------|------------|
| `GET /panel` | dealer.auth | DealerController@index |

---

## Route İsimleri

```php
route('home')               // /
route('dealer.login')        // /giris
route('dealer.register.submit')  // POST /kayit
route('admin.login')        // /yonetim/giris
route('panel')              // /panel
route('dashboard')          // /yonetim (admin içinde)
```

---

## Middleware Zinciri

### Public (web.php)
- `web` — Session, CSRF, cookie

### Admin (admin.php)
- `web`
- `admin.auth` — Giriş yoksa → admin.login

### Dealer (dealer.php)
- `web`
- `dealer.auth` — Şu an passthrough
