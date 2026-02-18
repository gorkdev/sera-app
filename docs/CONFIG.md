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
| **category_attributes** | Kategori ürün nitelikleri |
| **region_cities** | Bölge kısıtı şehir listesi |
| **region_regions** | Bölge kısıtı bölge listesi |
| **category_required_documents** | Zorunlu belgeler |
| **category_icons** | İkon seçenekleri |
| **category_featured_badges** | Rozet seçenekleri |
| **category_colors** | Renk paleti (hex → etiket) |

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

### category_attributes

Ürün nitelikleri (kategori formunda zorunlu/görünür seçimi): expiry_date, litre, germination_days, shelf_life_days, origin, stem_length_cm.

### region_cities, region_regions

Bölge kısıtı için autocomplete: Türkiye şehirleri ve coğrafi bölgeler (Marmara, Ege vb.).

### category_required_documents

Zorunlu belgeler: organic_certificate, analysis_report, phytosanitary, origin_certificate.

### category_icons

İkon seçenekleri: leaf, sun, droplet, flower, sparkles, fire, bolt, beaker.

### category_featured_badges

Rozet seçenekleri: new_season, best_seller, durable, premium, eco.

### category_colors

Renk paleti: hex kodları → etiket (Kırmızı, Turuncu, Yeşil vb.).
