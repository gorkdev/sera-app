# Sera — B2B Toptan Çiçek Satış Platformu

Laravel 12 tabanlı B2B çiçek satış platformu. Admin paneli ve bayi girişi.

## Kurulum

### 1. Bağımlılıklar

```bash
composer install
npm install && npm run build
```

### 2. Ortam dosyası

```bash
cp .env.example .env
php artisan key:generate
```

`.env` içinde veritabanı ayarlarını yapın:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sera_app
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Veritabanı

MySQL'de veritabanını oluşturun:

```bash
mysql -u root -e "CREATE DATABASE sera_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Tabloları ve test kullanıcılarını oluşturun:

```bash
php artisan migrate --seed
```

### 4. Çalıştırma

```bash
php artisan serve
```

Tarayıcıda: http://localhost:8000

### Test hesapları

| Rol   | E-posta        | Şifre  | Giriş URL      |
| ----- | -------------- | ------ | -------------- |
| Admin | admin@sera.com | 123456 | /yonetim/giris |
| Bayi  | bayi@test.com  | 123456 | /giris         |
