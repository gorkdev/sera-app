# Frontend

Tailwind CSS, DaisyUI, layout'lar ve view yapısı.

---

## Teknoloji Yığını

| Teknoloji | Versiyon | Açıklama |
|-----------|----------|----------|
| Tailwind CSS | 4.x | Utility-first CSS |
| DaisyUI | 5.5.x | Component kütüphanesi |
| Heroicons | blade-heroicons | İkon seti (Blade component) |
| Vite | 7.x | Build tool, HMR |
| Inter | - | Google Fonts (tipografi) |

---

## Heroicons Kullanımı

```blade
{{-- Directive --}}
@svg('heroicon-o-bars-3', 'h-6 w-6')
@svg('heroicon-s-check-circle', 'h-5 w-5 shrink-0')

{{-- Varyantlar: o=outline, s=solid, m=mini, c=micro --}}
@svg('heroicon-o-home', 'h-6 w-6')
@svg('heroicon-s-shopping-cart', 'h-5 w-5')
```

**Yaygın ikonlar:** `bars-3`, `check-circle`, `x-circle`, `shopping-cart`, `user-group`, `clock`, `cube`, `home`, `arrow-right-on-rectangle` (logout)

---

## Build Süreci

### Giriş Noktaları

- **CSS:** `resources/css/app.css` → Tailwind + DaisyUI
- **JS:** `resources/js/app.js` → Axios bootstrap

### Vite Config

`vite.config.js`:
- Laravel Vite plugin
- `@tailwindcss/vite` — Tailwind v4 entegrasyonu
- Input: `app.css`, `app.js`
- Blade refresh aktif

### Komutlar

```bash
npm run dev    # HMR ile geliştirme
npm run build  # Production build → public/build/
```

**Not:** `npm run build` bir kez çalıştırıldıktan sonra `php artisan serve` tek başına yeterli (Vite çalıştırmaya gerek yok).

---

## Layout'lar

### 1. `layouts/app.blade.php` — Public

**Kullanım:** Anasayfa, giriş, kayıt, public sayfalar

**Özellikler:**
- Sticky navbar (responsive, mobilde dropdown)
- Flash mesajları (success, error)
- Footer
- Tema: `data-theme="corporate"`
- Font: Inter (Google Fonts)

**Navbar linkleri:** Anasayfa, Bayi Girişi, Yönetim

### 2. `layouts/admin.blade.php` — Yönetim Paneli

**Kullanım:** `/yonetim/*` sayfaları

**Özellikler:**
- Drawer sidebar (mobilde hamburger ile açılır)
- Üst bar: Logo, çıkış
- Sidebar menü: Dashboard, Kategoriler, Ürünler, Partiler, Siparişler, Bayiler, Ayarlar (placeholder)
- `lg:drawer-open` — Masaüstünde sidebar her zaman açık

### 3. `layouts/dealer.blade.php` — Bayi Paneli

**Kullanım:** Bayi panel sayfaları

**Özellikler:**
- Top navbar (Katalog, Siparişlerim, Profilim, Sepet ikonu)
- Mobilde dropdown menü
- Kısa footer

---

## View Kullanımı

```blade
{{-- Public sayfa --}}
@extends('layouts.app')
@section('title', 'Sayfa Başlığı')
@section('content')
    ...
@endsection

{{-- Admin sayfa --}}
@extends('layouts.admin')
@section('content') ... @endsection

{{-- Bayi sayfa --}}
@extends('layouts.dealer')
@section('content') ... @endsection
```

---

## CSS Yapısı

`resources/css/app.css`:

```css
@import 'tailwindcss';
@plugin "daisyui";

@source '../**/*.blade.php';
@source '../**/*.js';

@theme {
    --font-sans: 'Inter', ...;
}

@layer base {
    html { @apply scroll-smooth; }
    body { @apply text-base-content; }
}
```

- **@source:** Tailwind'in class'ları taradığı dosyalar
- **@theme:** Özel font tanımı
- **@layer base:** Temel stiller

---

## DaisyUI Tema

Varsayılan: `data-theme="corporate"` (B2B için uygun)

Değiştirmek için `<html>` tag'inde:
```html
<html data-theme="light">   <!-- veya dark, cupcake, business vb. -->
```

---

## Auth Form Özelleştirmeleri

Detaylı bilgi için [Auth Formları](./AUTH-FORMS.md) dokümanına bakın.

### app.css Özel Stiller

```css
/* Input focus outline kaldır */
.input:focus { outline: none !important; }

/* Ortak giriş kartı (admin + bayi aynı yapı) */
.auth-card { padding: 1.5rem; } /* sm: 2rem */

/* Auth sayfalarında iki sütunlu layout yardımcı sınıfları (örnek) */
/* Sol: görsel alanı, Sağ: form kartı */

/* Modal: sadece overlay fade, içerik animasyonsuz */
#forgot_password_modal.modal .modal-box { transition: opacity 0.25s; }
```

### JS Modülleri

| Dosya | Görevi |
|-------|--------|
| `login-form.js` | Giriş formu client-side validasyon |
| `register-form.js` | Bayi kayıt formu için client-side UX helper'ları (Livewire ile birlikte) |
| `dealer-email-verify.js` | Email doğrulama sayfasında 6 haneli kod girişi ve "Kodu tekrar gönder" akışı |

`app.js` içinde DOMContentLoaded ve Livewire hook'ları ile init edilir.
