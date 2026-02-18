# Konfigürasyon

## config/sera.php

Sera'ya özel uygulama ayarları.

### Kullanım

```php
config('sera.cart.timer_duration_minutes')  // 30
config('sera.tax.vat_rate')                  // 20
config('sera.order.number_prefix')           // 'SIP'
config('sera.site.name')                     // env('APP_NAME', 'Sera')
```

---

### Bölümler

| Bölüm | Açıklama |
|-------|----------|
| **cart** | Sepet zamanlayıcı süreleri (dakika) |
| **tax** | KDV oranı (%) |
| **order** | Sipariş numarası ön eki |
| **sms** | NetGSM API bilgileri |
| **site** | Site adı, bakım modu |
| **product_units** | Ürün birimleri (adet, demet, koli vb.) |

### cart

| Anahtar | Varsayılan | Açıklama |
|---------|------------|----------|
| timer_duration_minutes | 30 | Sepete ilk ürün eklenince başlayan süre |
| penalty_duration_minutes | 10 | Süre dolunca ceza süresi |
| extension_minutes | 5 | Tek seferlik uzatma hakkı |
| warning_before_minutes | 5 | Uyarı gösterilme zamanı |
| checkout_extra_minutes | 2 | Checkout sayfasında ekstra süre |
| popup_timeout_minutes | 2 | Uyarı popup kapanma süresi |

### sms (NetGSM)

| Env | Açıklama |
|-----|----------|
| NETGSM_USERNAME | NetGSM kullanıcı adı |
| NETGSM_PASSWORD | NetGSM şifre |
| NETGSM_SENDER | Gönderici adı |

### site

| Anahtar | Açıklama |
|---------|----------|
| name | Site adı (varsayılan: APP_NAME) |
| maintenance_mode | Bakım modu (env: SERA_MAINTENANCE_MODE) |
| maintenance_message | Bakım modu mesajı |

### product_units

Ürün birimleri: adet, demet, buket, koli, paket, kutu, saksı, metre.
