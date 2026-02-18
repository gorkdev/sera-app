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
| company_name | string | Şirket adı |
| contact_name | string | Yetkili kişi |
| email | string (unique) | E-posta |
| password | string | Hash'lenmiş şifre |
| phone | string (nullable) | Telefon |
| status | enum | pending, active, passive |
| remember_token | string | |
| timestamps | | |
| deleted_at | timestamp | Soft delete |

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
- **Fillable:** company_name, contact_name, email, password, phone, status

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

### DatabaseSeeder

`AdminSeeder` ve `DealerSeeder` çağrılır.

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
