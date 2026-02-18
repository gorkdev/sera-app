# Proje Yapısı

Proje dizinlerinin ve dosyaların konumları ile açıklamaları.

---

## Dizin Ağacı

```
sera-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Controller.php          # Base controller
│   │   │   ├── Admin/
│   │   │   │   ├── AuthController.php  # Admin giriş/çıkış
│   │   │   │   ├── CategoryController.php  # Kategoriler CRUD
│   │   │   │   └── ProductController.php    # Ürünler CRUD
│   │   │   └── Dealer/
│   │   │       ├── AuthController.php  # Bayi giriş/çıkış
│   │   │       └── DealerController.php # Bayi panel controller
│   │   └── Middleware/
│   │       ├── EnsureAdminAuthenticated.php
│   │       └── EnsureDealerAuthenticated.php
│   ├── Models/
│   │   ├── User.php                     # Standart Laravel user (minimal kullanım)
│   │   ├── AdminUser.php                # Yönetim paneli kullanıcıları
│   │   ├── Dealer.php                   # Bayi hesapları
│   │   ├── DealerGroup.php              # Bayi grupları
│   │   ├── Category.php                 # Kategoriler (hiyerarşik, sezon, nitelikler)
│   │   └── Product.php                  # Ürünler
│   └── Providers/
│       └── AppServiceProvider.php
│
├── bootstrap/
│   └── app.php                          # Rota kayıtları, middleware alias
│
├── config/
│   ├── auth.php                        # 3 guard: web, admin, dealer
│   ├── sera.php                        # Sera özel ayarları (sepet, KDV, sipariş, SMS)
│   └── [diğer Laravel config]
│
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2026_02_17_210654_create_admin_users_table.php
│   │   ├── 2026_02_17_210953_create_dealers_table.php
│   │   ├── 2026_02_18_120000_create_categories_table.php
│   │   ├── 2026_02_18_120001_create_products_table.php
│   │   ├── 2026_02_18_200000_create_dealer_groups_table.php
│   │   ├── 2026_02_18_200001_add_extended_fields_to_categories_table.php
│   │   ├── 2026_02_18_210000_add_inactive_outside_season_to_categories.php
│   │   └── 2026_02_18_220000_add_advanced_category_fields.php
│   ├── seeders/
│   │   ├── DatabaseSeeder.php
│   │   ├── AdminSeeder.php
│   │   ├── DealerGroupSeeder.php
│   │   └── DealerSeeder.php
│   └── factories/
│       └── UserFactory.php
│
├── docs/                                # Bu dokümantasyon
│   ├── README.md
│   ├── PROJECT-STRUCTURE.md
│   ├── ARCHITECTURE.md
│   ├── FRONTEND.md
│   ├── AUTH-FORMS.md
│   ├── DATABASE.md
│   ├── ROUTES.md
│   └── CONFIG.md
│
├── public/
│   ├── build/                           # Vite build çıktısı
│   │   ├── manifest.json
│   │   └── assets/
│   ├── index.php
│   └── .htaccess
│
├── resources/
│   ├── css/
│   │   └── app.css                      # Tailwind + DaisyUI giriş noktası
│   ├── js/
│   │   ├── app.js
│   │   └── bootstrap.js                 # Axios config
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php            # Public layout
│       │   ├── admin.blade.php          # Admin panel layout
│       │   └── dealer.blade.php         # Bayi panel layout
│       ├── home.blade.php               # Anasayfa
│       ├── auth/
│       │   ├── admin/login.blade.php    # Admin giriş formu
│       │   └── dealer/login.blade.php  # Bayi giriş formu
│       ├── admin/
│       │   ├── dashboard.blade.php
│       │   ├── categories/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php
│       │   │   └── edit.blade.php
│       │   ├── products/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php
│       │   │   └── edit.blade.php
│       │   └── partials/
│       │       └── category-slug-script.blade.php
│       └── dealer/
│           └── index.blade.php          # Bayi panel ana sayfa
│
├── routes/
│   ├── web.php                          # Public rotalar
│   ├── admin.php                        # /yonetim/* rotalar
│   └── dealer.php                       # Bayi panel rotalar
│
├── .env
├── .env.example
├── composer.json              # blade-ui-kit/blade-heroicons dahil
├── package.json
├── vite.config.js
└── artisan
```

---

## Dosya Açıklamaları

### `app/`

| Dosya | Açıklama |
|-------|----------|
| `Http/Controllers/Controller.php` | Tüm controller'ların extend ettiği base sınıf |
| `Http/Controllers/Admin/CategoryController.php` | Kategoriler CRUD |
| `Http/Controllers/Admin/ProductController.php` | Ürünler CRUD |
| `Http/Controllers/Dealer/DealerController.php` | Bayi panel sayfaları (index vb.) |
| `Http/Middleware/EnsureAdminAuthenticated.php` | Admin giriş kontrolü, yoksa login'e yönlendirir |
| `Http/Middleware/EnsureDealerAuthenticated.php` | Bayi giriş kontrolü (şu an passthrough) |
| `Models/User.php` | Standart Laravel User modeli |
| `Models/AdminUser.php` | admin_users tablosu, role: super_admin/admin |
| `Models/Dealer.php` | dealers tablosu, SoftDeletes, belongsTo(DealerGroup) |
| `Models/DealerGroup.php` | dealer_groups tablosu |
| `Models/Category.php` | categories tablosu, hiyerarşik, sezon, nitelikler |
| `Models/Product.php` | products tablosu, belongsTo(Category) |

### `bootstrap/app.php`

- Ana rota dosyası: `web.php`
- Admin rotaları: `admin.php` → prefix `/yonetim`
- Bayi rotaları: `dealer.php`
- Middleware alias: `admin.auth`, `dealer.auth`

### `resources/views/`

| Dosya | Kullanım |
|-------|----------|
| `layouts/app.blade.php` | Anasayfa, giriş, public sayfalar |
| `layouts/admin.blade.php` | Yönetim paneli (drawer sidebar) |
| `layouts/dealer.blade.php` | Bayi paneli |
| `home.blade.php` | Anasayfa içeriği |
| `admin/categories/*` | Kategori CRUD formları |
| `admin/products/*` | Ürün CRUD formları |
| `admin/partials/category-slug-script.blade.php` | Kategori slug otomatik oluşturma |
| `dealer/index.blade.php` | Bayi panel ana sayfa |

### `resources/css/app.css`

- Tailwind CSS giriş noktası
- DaisyUI plugin
- Inter font
- `@source` ile Blade/JS dosyalarının taranması
