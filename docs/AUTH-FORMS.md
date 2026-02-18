# Auth Formları

Admin ve Bayi giriş/kayıt formları, validasyon, flip animasyonu ve şifremi unuttum modal'ı.

---

## Genel Bakış

| Sayfa | View | Özellikler |
|-------|------|------------|
| Admin Giriş | `auth/admin/login.blade.php` | E-posta, şifre, beni hatırla, şifremi unuttum modal |
| Bayi Giriş/Kayıt | `auth/dealer/auth.blade.php` | Flip kart: giriş önde, kayıt arkada |

---

## Tasarım Prensipleri

- **Admin = Bayi login** — Her iki giriş formu birebir aynı yapı, genişlik (`max-w-sm sm:max-w-md md:max-w-lg`), `auth-card` sınıfı
- **Standart boyutlar** — `input`, `btn` varsayılan boyutları (input-sm/btn-sm yok)
- **Dinamik yükseklik** — Sabit height yok, içerik + padding ile belirlenir
- **Responsive** — Mobilde tek sütun, tablet+ iki sütun (kayıt formu)
- **Input focus** — Outline kaldırıldı (`app.css` içinde `.input:focus { outline: none }`)
- **Label–input boşluğu** — `flex flex-col gap-2` ile tutarlı aralık
- **Anasayfaya Dön** — Kartın dışında, alt kısımda (`mt-6 text-center`)

---

## Form Validasyonu

HTML5 `required` kullanılmıyor. Özel client-side validasyon:

### login-form.js

- **E-posta:** Boş mu, geçerli format mı
- **Şifre:** Boş mu
- Hata durumunda input altında mesaj, `input-error` sınıfı
- Submit engellenir, ilk hatalı alana focus

### register-form.js

- **Şirket adı, yetkili adı:** Zorunlu
- **E-posta:** Zorunlu, format kontrolü
- **Şifre:** Min 6 karakter
- **Şifre tekrar:** Eşleşme kontrolü
- Alan bazlı hata gösterimi

### Sunucu Hataları

- **Credential hataları** (`Girdiğiniz bilgiler hatalı`, hesap pasif vb.) → Sadece **alert** içinde
- **Alan validasyon hataları** (required, format) → İlgili input altında

---

## Bayi Auth: Flip Kart

### Yapı

- **Ön yüz:** Giriş formu (varsayılan)
- **Arka yüz:** Kayıt formu
- "Kayıt ol" tıklanınca 3D flip ile arka yüze geçiş
- "Giriş yap" ile geri dönüş

### Teknik

- `auth-flip.js` — Flip tetikleyici
- CSS: `perspective-1000`, `backface-hidden`, `rotate-y-180`
- Grid: Front ve back aynı hücrede (`grid-area: 1/1`), dinamik yükseklik
- `auth-card` — Ortak padding (mobil 1.5rem, tablet+ 2rem)
- **Autofocus** — Bayi login açıldığında e-posta alanına focus (kayıt formu gösteriliyorsa yok)

### Kayıt Formu Layout

2 sütunlu grid (sm ve üzeri):

| Satır 1 | Şirket Adı | Yetkili Adı |
| Satır 2 | E-posta | Telefon (opsiyonel) |
| Satır 3 | Şifre | Şifre Tekrar |

Mobilde tek sütun, alanlar alt alta.

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
│   ├── admin/login.blade.php    # Admin giriş (auth-card, bayi ile aynı yapı)
│   └── dealer/auth.blade.php   # Bayi giriş + kayıt (flip)
├── js/
│   ├── login-form.js           # Giriş validasyonu
│   ├── register-form.js        # Kayıt validasyonu
│   └── auth-flip.js            # Flip kart tetikleyici
└── css/
    └── app.css                 # Auth flip, input focus, modal override
```

---

## Controller Akışı

### Admin AuthController

- `showLoginForm()` — Giriş sayfası
- `login()` — Validasyon, attempt, is_active kontrolü
- `logout()` — Çıkış, admin.login'e yönlendirme

### Dealer AuthController

- `showAuth()` — Giriş + kayıt (tek sayfa, flip kart)
- `login()` — Validasyon, attempt, status kontrolü (pending/active/passive)
- `register()` — Validasyon, Dealer::create, success mesajı
- `logout()` — Çıkış, dealer.login'e yönlendirme

---

## Test Hesapları

| Rol | E-posta | Şifre |
|-----|---------|-------|
| Admin | admin@sera.com | 123456 |
| Bayi | bayi@test.com | 123456 |
