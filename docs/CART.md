# Sepet Sistemi

Bayi sepeti veritabanına bağlıdır; aynı bayi tüm cihazlarda aynı sepeti görür. Livewire ile anında güncelleme, global toast ve çoklu cihaz/sekme senkronu (polling) bu dokümanda özetlenir.

---

## Genel Bakış

| Durum | Sepet nerede | Davranış |
|-------|----------------|----------|
| **Misafir** | Session (`session('cart')`) | Sadece o tarayıcıda geçerli |
| **Bayi girişli** | Veritabanı (`carts`, `cart_items`) | Aynı bayi tüm cihazlarda aynı sepeti görür |

- Sepete ekleme, miktar değiştirme, ürün silme ve sepet temizleme hem session hem DB senaryosunda desteklenir.
- Bayi giriş yaptığında, o ana kadar session’da biriken sepet **tek seferlik** DB sepetine merge edilir; sonrasında tüm işlemler DB üzerinden yürür.

---

## Veritabanı

### Tablolar

**`carts`**

| Alan | Tip | Açıklama |
|------|-----|----------|
| id | bigint | PK |
| dealer_id | bigint (FK) | Bayi; cascade on delete |
| timestamps | | created_at, updated_at |

**`cart_items`**

| Alan | Tip | Açıklama |
|------|-----|----------|
| id | bigint | PK |
| cart_id | bigint (FK) | Sepet; cascade on delete |
| product_id | bigint (FK) | Ürün; cascade on delete |
| quantity | unsigned int | Adet |
| unit_price | decimal(12,2) | Sepete eklendiği andaki birim fiyat |
| timestamps | | |

- Unique: `(cart_id, product_id)` — Aynı ürün sepette tek satırda, miktar artırılarak güncellenir.

### Modeller

- **Cart** — `dealer_id`, `dealer()`, `items()`
- **CartItem** — `cart_id`, `product_id`, `quantity`, `unit_price`, `cart()`, `product()`
- **Dealer** — `cart()` (hasOne)

Migration’lar: `database/migrations/2026_02_20_160000_create_carts_table.php`, `2026_02_20_160001_create_cart_items_table.php`

---

## CartService

**Dosya:** `app/Services/CartService.php`

Tüm sepet işlemleri bu servis üzerinden yapılır; performans için tek/az sorgu tercih edilir.

| Metot | Açıklama |
|-------|----------|
| `getOrCreateCart(Dealer $dealer)` | Bayi için sepeti bulur veya oluşturur (firstOrCreate) |
| `getCartWithItems(Dealer $dealer)` | Sepeti `items.product` ile tek sorguda getirir |
| `getItemsForDisplay(Dealer $dealer)` | Session ile aynı formatta dizi döner: `[product_id => [product_id, name, price, image, quantity]]` |
| `getTotalQuantity(Dealer $dealer)` | Toplam kalem sayısı (tek JOIN sorgusu) |
| `addItem(Dealer $dealer, int $productId, int $quantity)` | Ürün ekler veya miktarı artırır; aktif ürün kontrolü yapar |
| `removeItem`, `updateQuantity`, `incrementItem`, `decrementItem`, `clear` | Tekil/güncelleme/temizleme işlemleri |
| `mergeSessionCartIntoDealerCart(Dealer $dealer)` | Session sepetini bayi sepetine ekleyip session’ı temizler (login sonrası tek sefer) |

---

## Bayi Girişinde Session Merge

**Dosya:** `app/Http/Controllers/Dealer/AuthController.php` — `login()` metodu.

Başarılı giriş ve `session()->regenerate()` sonrası:

```php
app(CartService::class)->mergeSessionCartIntoDealerCart($dealer);
```

Böylece misafirken eklenen ürünler, giriş sonrası bayi sepetine taşınır ve aynı sepet tüm cihazlarda görünür.

---

## Livewire Bileşenleri

### CartIcon

**Sınıf:** `App\Livewire\CartIcon`  
**View:** `resources/views/components/⚡cart-icon.blade.php`  
**Layout’ta:** `resources/views/layouts/app.blade.php` içinde `<livewire:cart-icon />`

- Navbar’da sepet ikonu ve badge (toplam adet).
- Sağdan açılan drawer: sepet kalemleri, miktar artır/azalt, kaldır, sepeti temizle, “Satın Al” (şu an sadece sepeti temizleyip toast gösterir).
- **Bayi girişli:** Veriyi `CartService::getItemsForDisplay()` ve `render()` içinde DB’den alır; badge için `$totalQuantity` (render çıktısı) kullanılır.
- **Misafir:** `session('cart')` kullanılır.

**Event dinleme:** `#[On('cart-updated')]` ile `onCartUpdated()`. Başka bir bileşen (örn. ürün kataloğu) sepete ekleme yaptığında `cart-updated` dispatch edilir; CartIcon bu event’i alınca Livewire bileşeni yeniden render olur ve hem badge hem drawer güncel veriyi gösterir (sayfa yenilemeden).

**Çoklu cihaz/sekme:** Bayi girişli kullanıcıda `wire:poll.5s` açıktır (`$shouldPoll` sadece dealer için true). Her 5 saniyede bir bileşen sunucudan güncel sepeti çeker; ikinci cihazda veya sekmede yapılan ekleme/silme en geç 5 saniye içinde görünür (drawer’ı kapatıp açmaya gerek yok).

### Ürün kataloğu (sepete ekleme)

**View:** `resources/views/components/⚡product-catalog.blade.php` (anonim Livewire bileşeni).

- “Sepete ekle” tıklanınca: bayi girişliyse `CartService::addItem()` ile DB’ye yazar, ardından `dispatch('cart-updated', totalQuantity: $totalQuantity)` ve `dispatch('show-toast', ...)` çağrılır.
- `cart-updated` sayesinde CartIcon anında güncellenir; toast, layout’taki global dinleyici ile gösterilir.

---

## Anında Güncelleme Akışı (Tek cihaz/sekme)

1. Kullanıcı katalogda “Sepete ekle”ye tıklar.
2. Livewire isteği: `addToCart()` çalışır → `CartService::addItem()` → DB güncellenir.
3. Response içinde `dispatch('cart-updated', totalQuantity: ...)` ve `dispatch('show-toast', ...)` gider.
4. Tarayıcıda `cart-updated` event’i CartIcon’a ulaşır; `#[On('cart-updated')]` ile `onCartUpdated()` tetiklenir.
5. Livewire CartIcon’ı yeniden render eder; `render()` güncel sepeti DB/session’dan alır → badge ve drawer içeriği anında güncellenir.
6. `show-toast` event’i layout’taki global listener tarafından dinlenir → “X sepete eklendi” toast’ı gösterilir.

Böylece sayfa yenilenmeden hem sayı hem liste hem toast güncellenir.

---

## Global Toast

**Dosya:** `resources/views/layouts/app.blade.php`

- Sayfanın sonunda bir toast container ve `Livewire.on('show-toast', ...)` dinleyicisi vardır.
- Herhangi bir Livewire bileşeni `$this->dispatch('show-toast', ['type' => 'success'|'error', 'message' => '...'])` çağırdığında toast tüm layout sayfalarında (auth açık sayfalar dahil) görünür.
- Payload: `type` (success/error) ve `message`; ~3 saniye sonra kapanır.

---

## Çoklu Cihaz / Sekme (Polling)

- Bayi girişliyken CartIcon root element’inde `wire:poll.5s` koşullu eklenir (`$shouldPoll` sadece dealer için true).
- Her 5 saniyede bir Livewire CartIcon’ı sunucuya istek atıp `render()` ile güncel sepeti alır; DOM güncellenir.
- Sonuç: İkinci cihazda veya sekmede sepete ekleme/çıkarma yapıldığında, diğer cihaz/sekmede en geç 5 saniye içinde sepet (badge + drawer) güncellenir; sepeti kapatıp açmaya gerek kalmaz.
- Misafir kullanıcıda polling yok; gereksiz istek atılmaz.

İleride gerçek zamanlı (anlık) senkron istersen Laravel Echo + broadcasting ile “cart-updated” event’i yayınlanabilir; şu anki çözüm ek altyapı gerektirmez.

---

## Dosya Özeti

| Dosya | Rol |
|-------|-----|
| `app/Models/Cart.php` | Cart modeli |
| `app/Models/CartItem.php` | CartItem modeli |
| `app/Services/CartService.php` | Sepet iş mantığı |
| `app/Livewire/CartIcon.php` | Navbar sepet ikonu + drawer |
| `resources/views/components/⚡cart-icon.blade.php` | Sepet UI, wire:poll.5s (koşullu) |
| `resources/views/components/⚡product-catalog.blade.php` | Sepete ekle, cart-updated + show-toast dispatch |
| `resources/views/layouts/app.blade.php` | Global toast container + Livewire.on('show-toast') |
| `app/Http/Controllers/Dealer/AuthController.php` | Login sonrası mergeSessionCartIntoDealerCart |

---

## Konfigürasyon

Sepet zamanlayıcı, ceza süresi vb. için `config/sera.php` → `cart` anahtarı kullanılır (zamanlayıcılı sepet/rezervasyon senaryosu için ayrıca kullanılabilir).
