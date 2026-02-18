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
| GET | `/yonetim/kategoriler` | admin.categories.index | Kategori listesi |
| GET | `/yonetim/kategoriler/olustur` | admin.categories.create | Kategori oluştur |
| POST | `/yonetim/kategoriler` | admin.categories.store | Kategori kaydet |
| GET | `/yonetim/kategoriler/{slug}/duzenle` | admin.categories.edit | Kategori düzenle |
| PUT | `/yonetim/kategoriler/{slug}` | admin.categories.update | Kategori güncelle |
| DELETE | `/yonetim/kategoriler/{slug}` | admin.categories.destroy | Kategori sil |
| GET | `/yonetim/urunler` | admin.products.index | Ürün listesi |
| GET | `/yonetim/urunler/olustur` | admin.products.create | Ürün oluştur |
| POST | `/yonetim/urunler` | admin.products.store | Ürün kaydet |
| GET | `/yonetim/urunler/{slug}/duzenle` | admin.products.edit | Ürün düzenle |
| PUT | `/yonetim/urunler/{slug}` | admin.products.update | Ürün güncelle |
| DELETE | `/yonetim/urunler/{slug}` | admin.products.destroy | Ürün sil |

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

| Route | Middleware | Controller | Açıklama |
|-------|------------|------------|----------|
| `GET /` | admin.auth | Closure | Dashboard |
| `GET /kategoriler` | admin.auth | CategoryController@index | Kategori listesi |
| `GET /kategoriler/olustur` | admin.auth | CategoryController@create | Kategori oluştur formu |
| `POST /kategoriler` | admin.auth | CategoryController@store | Kategori kaydet |
| `GET /kategoriler/{slug}/duzenle` | admin.auth | CategoryController@edit | Kategori düzenle formu |
| `PUT /kategoriler/{slug}` | admin.auth | CategoryController@update | Kategori güncelle |
| `DELETE /kategoriler/{slug}` | admin.auth | CategoryController@destroy | Kategori sil |
| `GET /urunler` | admin.auth | ProductController@index | Ürün listesi |
| `GET /urunler/olustur` | admin.auth | ProductController@create | Ürün oluştur formu |
| `POST /urunler` | admin.auth | ProductController@store | Ürün kaydet |
| `GET /urunler/{slug}/duzenle` | admin.auth | ProductController@edit | Ürün düzenle formu |
| `PUT /urunler/{slug}` | admin.auth | ProductController@update | Ürün güncelle |
| `DELETE /urunler/{slug}` | admin.auth | ProductController@destroy | Ürün sil |

### dealer.php

| Route | Middleware | Controller |
|-------|------------|------------|
| `GET /panel` | dealer.auth | DealerController@index |

---

## Route İsimleri

```php
route('home')                    // /
route('dealer.login')            // /giris
route('dealer.register.submit')  // POST /kayit
route('admin.login')             // /yonetim/giris
route('panel')                   // /panel
route('dashboard')               // /yonetim
route('admin.categories.index')  // /yonetim/kategoriler
route('admin.categories.create') // /yonetim/kategoriler/olustur
route('admin.categories.edit', $category)   // /yonetim/kategoriler/{slug}/duzenle
route('admin.products.index')    // /yonetim/urunler
route('admin.products.create')   // /yonetim/urunler/olustur
route('admin.products.edit', $product)      // /yonetim/urunler/{slug}/duzenle
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
