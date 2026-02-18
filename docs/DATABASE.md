# Veritabanı

Modeller, migrations ve seeders.

---

## Tablolar

### `admin_users`

| Alan | Tip | Açıklama |
|------|-----|----------|
| id | bigint | PK |
| name | string | Admin adı |
| email | string (unique) | Giriş e-postası |
| password | string | Hash'lenmiş şifre |
| role | enum | super_admin, admin |
| is_active | boolean | Hesap aktif mi |
| remember_token | string | Beni hatırla |
| timestamps | | created_at, updated_at |

### `dealers`

| Alan | Tip | Açıklama |
|------|-----|----------|
| id | bigint | PK |
| dealer_group_id | bigint (nullable, FK) | Bayi grubu |
| company_name | string | Şirket adı |
| contact_name | string | Yetkili kişi |
| email | string (unique) | E-posta |
| password | string | Hash'lenmiş şifre |
| phone | string (nullable) | Telefon |
| status | enum | pending, active, passive |
| remember_token | string | |
| timestamps | | |
| deleted_at | timestamp | Soft delete |

### `dealer_groups`

| Alan | Tip | Açıklama |
|------|-----|----------|
| id | bigint | PK |
| name | string | Grup adı |
| code | string (unique) | Grup kodu |
| delay_minutes | unsigned int | Gecikme (dakika) |
| is_default | boolean | Varsayılan grup mu |
| sort_order | unsigned int | Sıralama |
| timestamps | | |

### `categories`

| Alan | Tip | Açıklama |
|------|-----|----------|
| id | bigint | PK |
| parent_id | bigint (nullable, FK) | Üst kategori |
| name | string | Kategori adı |
| slug | string (unique) | URL slug |
| description | text (nullable) | Açıklama |
| image | string (nullable) | Görsel (storage/categories/) |
| sort_order | unsigned int | Sıralama |
| is_active | boolean | Aktif mi |
| season_start_month | tinyint (nullable) | Sezon başlangıç ayı (1-12) |
| season_end_month | tinyint (nullable) | Sezon bitiş ayı |
| inactive_outside_season | boolean | Sezon dışı pasif |
| visible_to_group_ids | json (nullable) | Görünür olduğu bayi grupları [1,2,3] |
| max_quantity_per_dealer_per_party | int (nullable) | Parti başına kota |
| display_priority | int | Katalog sıralama |
| attribute_set | json (nullable) | {"required":[],"visible":[]} ürün nitelikleri |
| region_restriction | json (nullable) | {"cities":[],"regions":[]} bölge kısıtı |
| default_growth_days | smallint (nullable) | Varsayılan yetişme süresi (gün) |
| ideal_temp_min, ideal_temp_max | decimal (nullable) | İdeal sıcaklık aralığı (°C) |
| ideal_humidity_min, ideal_humidity_max | decimal (nullable) | İdeal nem aralığı (%) |
| required_documents | json (nullable) | Zorunlu belgeler |
| min_order_quantity | int (nullable) | Min. sipariş adedi |
| profit_margin_percent | decimal (nullable) | Kâr marjı (%) |
| color_code | string (nullable) | Renk (hex) |
| icon | string (nullable) | İkon adı |
| featured_badges | json (nullable) | Rozetler |
| timestamps | | |

### `products`

| Alan | Tip | Açıklama |
|------|-----|----------|
| id | bigint | PK |
| category_id | bigint (FK) | Kategori |
| name | string | Ürün adı |
| slug | string (unique) | URL slug |
| sku | string (nullable, unique) | Stok kodu |
| description | text (nullable) | Açıklama |
| price | decimal(12,2) | Fiyat (₺) |
| unit | string | Birim (adet, demet, koli vb.) |
| stock_quantity | unsigned int | Stok miktarı |
| min_order_quantity | unsigned int | Min. sipariş adedi |
| is_active | boolean | Aktif mi |
| timestamps | | |

### Standart Laravel Tabloları

- `users` — Standart kullanıcı (minimal kullanım)
- `cache` — Cache driver
- `jobs` — Queue jobs
- `sessions` — Session driver (users migration içinde)
- `migrations` — Migration takibi

---

## Modeller

### AdminUser

- **Tablo:** admin_users
- **Auth:** Authenticatable, Notifiable
- **Fillable:** name, email, password, role, is_active

### Dealer

- **Tablo:** dealers
- **Auth:** Authenticatable, Notifiable
- **Trait:** SoftDeletes
- **İlişki:** belongsTo(DealerGroup)
- **Fillable:** dealer_group_id, company_name, contact_name, email, password, phone, status

### DealerGroup

- **Tablo:** dealer_groups
- **Fillable:** name, code, delay_minutes, is_default, sort_order

### Category

- **Tablo:** categories
- **İlişkiler:** parent, children, products
- **Fillable:** parent_id, name, slug, description, image, season_start_month, season_end_month, inactive_outside_season, visible_to_group_ids, max_quantity_per_dealer_per_party, display_priority, attribute_set, region_restriction, default_growth_days, ideal_temp_min/max, ideal_humidity_min/max, required_documents, min_order_quantity, profit_margin_percent, color_code, icon, featured_badges, sort_order, is_active
- **Casts:** visible_to_group_ids, attribute_set, region_restriction, required_documents, featured_badges → array
- **Yardımcı:** isInSeason(), isEffectivelyActive(), isVisibleToDealerGroup(), season_label accessor

### Product

- **Tablo:** products
- **İlişki:** belongsTo(Category)

### User

- **Tablo:** users
- **Standart Laravel User** — Projede minimal kullanım

---

## Seeders

### AdminSeeder

Varsayılan admin kullanıcı oluşturur:

- **Email:** admin@sera.com
- **Şifre:** 123456 (Hash'lenmiş)
- **Role:** super_admin
- **is_active:** true

### DealerSeeder

Test bayi hesabı oluşturur:

- **Email:** bayi@test.com
- **Şifre:** 123456
- **Status:** active

### DealerGroupSeeder

Varsayılan bayi grubu oluşturur (is_default: true). DealerSeeder'dan önce çalışmalı.

### DatabaseSeeder

`AdminSeeder`, `DealerGroupSeeder` ve `DealerSeeder` çağrılır.

```bash
php artisan db:seed
# veya
php artisan migrate --seed
```

---

## Migration Komutları

```bash
php artisan migrate           # Migration'ları çalıştır
php artisan migrate:fresh     # Tabloları sil, yeniden oluştur
php artisan migrate:fresh --seed  # + Seed
```
