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
| phone | string (nullable) | Telefon (TR GSM, 11 hane, 05xx) |
| tax_office | string (nullable) | Vergi dairesi |
| tax_number | string (nullable) | Vergi no veya TCKN (10/11 hane) |
| tax_type | enum (nullable) | 'tax' (10 haneli vergi no) veya 'tckn' (11 haneli) |
| city | string (nullable) | İl (TR, büyük harf) |
| district | string (nullable) | İlçe (TR, büyük harf) |
| address | text (nullable) | Açık adres |
| kvkk_consent | boolean | KVKK aydınlatma onayı |
| email_verified_at | timestamp (nullable) | E-posta doğrulama tarihi |
| status | enum | pending, active, passive, blocked |
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
| is_active | boolean | Aktif mi (panelde pasife alırsa görünmez) |
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
| image | string (nullable) | Görsel (storage/products/) |
| price | decimal(12,2) | Fiyat (₺) |
| unit | string | Birim (adet, demet, koli vb.) |
| stock_quantity | unsigned int | Stok miktarı |
| min_order_quantity | unsigned int | Min. sipariş adedi |
| is_active | boolean | Aktif mi |
| timestamps | | |

### `dealer_email_verifications`

| Alan | Tip | Açıklama |
|------|-----|----------|
| id | bigint | PK |
| dealer_id | bigint (FK) | İlgili bayi |
| code_hash | string | 6 haneli kodun HMAC-SHA256 hash'i |
| expires_at | timestamp | Kodun geçerlilik bitişi |
| last_sent_at | timestamp (nullable) | Son gönderim zamanı |
| send_count | unsigned int | Kaç kere kod gönderildi |
| attempts | unsigned int | Doğrulama deneme sayısı |
| verified_at | timestamp (nullable) | Kod başarıyla doğrulandığında doldurulur |
| created_at / updated_at | timestamps | |

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
- **Fillable (özet):** dealer_group_id, company_name, contact_name, email, password, phone, tax_office, tax_number, tax_type, city, district, address, kvkk_consent, email_verified_at, status

### DealerGroup

- **Tablo:** dealer_groups
- **Fillable:** name, code, delay_minutes, is_default, sort_order

### Category

- **Tablo:** categories
- **İlişkiler:** parent, children, products
- **Fillable:** parent_id, name, slug, description, image, sort_order, is_active
- **Casts:** is_active → boolean
- Kategori her zaman açık; sadece panelde aktif/pasif ile görünürlük kontrol edilir.

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
