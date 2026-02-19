# Auth Formları

Admin ve Bayi giriş/kayıt formları, validasyon, iki sütunlu layout (solda görsel, sağda form) ve şifremi unuttum modal'ı. Bayi kayıt formu Livewire ile sayfa yenilenmeden çalışır ve email doğrulama akışı ile entegredir.

---

## Genel Bakış

| Sayfa | View | Özellikler |
|-------|------|------------|
| Admin Giriş | `auth/admin/login.blade.php` | E-posta, şifre, beni hatırla, şifremi unuttum modal |
| Bayi Giriş | `auth/dealer/login.blade.php` | İki sütunlu layout (sol görsel, sağ form), email doğrulama ve admin onay uyarıları |
| Bayi Kayıt | `auth/dealer/register.blade.php` + `livewire/dealer/register-form.blade.php` | Livewire kayıt formu, TR telefon, il/ilçe, vergi no/TCKN, KVKK |
| Bayi Email Doğrulama | `auth/dealer/verify.blade.php` | 6 haneli kod girişi, tekrar gönderme butonu, cooldown göstergesi |

---

## Tasarım Prensipleri

- **İki sütunlu layout** — Admin ve Bayi login/register sayfalarında sol tarafta illüstrasyon/görsel, sağ tarafta form kartı.
- **Standart boyutlar** — `input`, `btn` varsayılan boyutları (input-sm/btn-sm yok)
- **Dinamik yükseklik** — Sabit height yok, içerik + padding ile belirlenir
- **Responsive** — Mobilde tek sütun (önce görsel, sonra form), tablet+ iki sütun
- **Input focus** — Outline kaldırıldı (`app.css` içinde `.input:focus { outline: none }`)
- **Label–input boşluğu** — `flex flex-col gap-2` ile tutarlı aralık
- **Anasayfaya Dön** — Kartın dışında, alt kısımda (`mt-6 text-center`)

---

## Form Validasyonu

HTML5 `required` kullanılmıyor. Özel client-side + server-side validasyon:

### login-form.js

- **E-posta:** Boş mu, geçerli format mı
- **Şifre:** Boş mu
- Hata durumunda input altında mesaj, `input-error` sınıfı
- Submit engellenir, ilk hatalı alana focus

### register-form.js (Livewire ile birlikte çalışma)

- Livewire kayıt formuna sadece **UX iyileştirmeleri** sağlar:
  - TR telefon formatlama (`0555 555 55 55`),
  - İl/ilçe inputları için autocomplete / datalist ve filtreleme,
  - KVKK checkbox ve hata gösterimi,
  - Şifre göster/gizle, baş harf büyük yazma vb.
- Asıl alan zorunlulukları ve iş kuralları **Livewire component** içinde (server-side) doğrulanır:
  - Şirket adı, yetkili adı, e-posta (unique),
  - TR telefon formatı,
  - Vergi no/TCKN (10 veya 11 hane, otomatik tip algılama),
  - TR il/ilçe listesine göre üyelik kontrolü,
  - KVKK onayı zorunluluğu.

### Sunucu Hataları

- **Credential hataları** (`Girdiğiniz bilgiler hatalı`, hesap pasif vb.) → Sadece **alert** içinde
- **Alan validasyon hataları** (required, format) → İlgili input altında (Blade + Livewire error bag'leri)
- **Bayi login özel durumları:**
  - Email doğrulanmamışsa → login sayfasında uyarı ve "Kodu tekrar gönder" / "Kodu gir" butonları.
  - Email doğrulanmış ama admin onayı yoksa → "Yönetici onayı bekleniyor" uyarısı.

---

## Bayi Auth: Layout ve Akış

### Yapı

- **Bayi Giriş Sayfası** (`auth/dealer/login.blade.php`):
  - Sol sütunda auth görseli (`public/images/auth-placeholder.svg`),
  - Sağ sütunda login formu,
  - Email doğrulama ve admin onayı uyarıları (alert bileşenleri).

- **Bayi Kayıt Sayfası** (`auth/dealer/register.blade.php`):
  - Sol sütunda aynı auth görseli,
  - Sağ sütunda `<livewire:dealer.register-form />` komponenti.

- **Email Doğrulama Sayfası** (`auth/dealer/verify.blade.php`):
  - Sol sütunda auth görseli,
  - Sağ sütunda 6 kutucuklu kod girişi, "Kodu tekrar gönder" butonu,
  - Maskelenmiş email (`m***@do****.com` gibi).

### Kayıt Formu Layout (Livewire)

Livewire component layout'u klasik form yapısını korur, ancak alanlar artık Livewire property'lerine bağlıdır:

- Şirket adı, yetkili adı, e-posta, telefon (zorunlu TR GSM),
- Vergi dairesi, vergi no/TCKN (10/11 hane, otomatik tip),
- İl ve ilçe (datalist ile, TR lokasyon verisinden),
- Adres, KVKK onay kutusu,
- Şifre ve şifre tekrar.

---

## Şifremi Unuttum Modal

- **DaisyUI** `<dialog class="modal">` kullanımı
- Açma: `forgot_password_modal.showModal()`
- Kapanma: Boş alana tıklama (modal-backdrop), ESC
- İçerik: E-posta input, Gönder butonu
- **Tasarım amaçlı** — Gerçek e-posta gönderimi yok, Gönder tıklanınca modal kapanır

### Modal Animasyon

- Overlay fade-in (DaisyUI varsayılan)
- Modal-box: Sadece opacity geçişi (scale/translate yok, içerik animasyonsuz)

---

## Ortak Yapı (auth-card)

Admin ve Bayi giriş formları aynı yapıyı kullanır:
- `auth-card` — Padding, border, shadow
- Aynı form alanları: E-posta, Şifre, Beni hatırla, Giriş Yap
- Şifre satırında sağda "Şifremi unuttum" linki
- Alt kısım: Admin'de "Anasayfaya Dön", Bayi'de "Kayıt ol" + kart dışında "Anasayfaya Dön"

---

## Dosya Yapısı

```
resources/
├── views/auth/
│   ├── admin/login.blade.php          # Admin giriş
│   ├── dealer/login.blade.php         # Bayi giriş (iki sütunlu layout)
│   ├── dealer/register.blade.php      # Bayi kayıt sayfası (Livewire wrapper)
│   └── dealer/verify.blade.php        # Bayi email doğrulama sayfası
├── views/livewire/
│   └── dealer/register-form.blade.php # Livewire bayi kayıt formu
├── js/
│   ├── login-form.js                 # Giriş validasyonu
│   ├── register-form.js              # Kayıt UX yardımcıları (Livewire ile birlikte)
│   └── dealer-email-verify.js        # Email doğrulama sayfası kod girişi + resend
└── css/
    └── app.css                       # Auth layout, input focus, modal override
```

---

## Controller Akışı

### Admin AuthController

- `showLoginForm()` — Giriş sayfası.
- `login()` — Validasyon, attempt, `is_active` kontrolü.
- `logout()` — Çıkış, `admin.login`'e yönlendirme.

### Dealer AuthController

- `showLoginForm()` — Bayi giriş sayfası (zaten giriş yapmışsa `panel`'e redirect).
- `showRegisterForm()` — Bayi kayıt sayfası (giriş yapmışsa `panel`'e redirect).
- `login()` — Validasyon, attempt, email doğrulama ve `status` kontrolü:
  - Email doğrulanmamışsa → logout + session'da `dealer_verification_id`, `needs_email_verification` flash'ı.
  - Email doğrulanmış ama `status !== active` ise → uygun flash mesajı (`admin_pending`, `dealer_passive`, `dealer_blocked`).
  - Email doğrulanmış ve `status = active` ise → `panel`'e redirect.
- `register()` — Validasyon, Dealer::create, `dealer_verification_id` session'a yazma, email doğrulama kodu gönderme ve `dealer.verify.show` sayfasına yönlendirme.
- `logout()` — Çıkış, `dealer.login`'e yönlendirme.

### Dealer EmailVerificationController

- `show()` — `dealer_verification_id` üzerinden bayi bulur, email zaten doğrulanmışsa login sayfasına success mesajıyla döner, aksi halde email maskelemiş halde verify sayfasını render eder.
- `verify()` — Rate limiting + kod format validasyonu + `DealerEmailVerificationService::verify` çağrısı; başarılıysa bayi `email_verified_at` alanı doldurulur ve login sayfasına success mesajıyla yönlendirilir.
- `resend()` — Rate limiting kurallarına uyarak yeni kod üretip mail gönderir, JSON veya redirect ile kullanıcıya bilgi döner.

---

## Test Hesapları

| Rol | E-posta | Şifre |
|-----|---------|-------|
| Admin | admin@sera.com | 123456 |
| Bayi | bayi@test.com | 123456 |
