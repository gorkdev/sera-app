<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sepet Ayarları
    |--------------------------------------------------------------------------
    |
    | Zamanlayıcılı sepet sistemi için süreler (dakika cinsinden).
    |
    */

    'cart' => [
        'timer_duration_minutes' => 30,      // Sepete ilk ürün eklenince başlayan süre
        'penalty_duration_minutes' => 10,    // Süre dolunca uygulanan ceza (alışveriş yapamaz)
        'extension_minutes' => 5,            // Tek seferlik uzatma hakkı
        'warning_before_minutes' => 5,       // Süre dolmadan kaç dk önce uyarı gösterilecek
        'checkout_extra_minutes' => 2,      // Checkout sayfasında ekstra süre
        'popup_timeout_minutes' => 2,       // Uyarı popup kapanma süresi
    ],

    /*
    |--------------------------------------------------------------------------
    | Ürün Birimleri
    |--------------------------------------------------------------------------
    |
    | Ürünlerde kullanılabilecek satış birimleri.
    |
    */

    'product_units' => [
        'adet' => 'Adet',
        'demet' => 'Demet',
        'buket' => 'Buket',
        'koli' => 'Koli',
        'paket' => 'Paket',
        'kutu' => 'Kutu',
        'saksı' => 'Saksı',
        'metre' => 'Metre',
    ],

    /*
    |--------------------------------------------------------------------------
    | Vergi Ayarları
    |--------------------------------------------------------------------------
    */

    'tax' => [
        'vat_rate' => 20,                    // KDV oranı (%)
    ],

    /*
    |--------------------------------------------------------------------------
    | Sipariş Ayarları
    |--------------------------------------------------------------------------
    */

    'order' => [
        'number_prefix' => 'SIP',            // Sipariş no ön eki (SIP-YYYYMM-XXXXX)
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Ayarları (NetGSM)
    |--------------------------------------------------------------------------
    */

    'sms' => [
        'provider' => 'netgsm',
        'netgsm_username' => env('NETGSM_USERNAME'),
        'netgsm_password' => env('NETGSM_PASSWORD'),
        'netgsm_sender' => env('NETGSM_SENDER'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Site Ayarları
    |--------------------------------------------------------------------------
    */

    'site' => [
        'name' => env('APP_NAME', 'Sera'),
        'maintenance_mode' => env('SERA_MAINTENANCE_MODE', false),
        'maintenance_message' => env('SERA_MAINTENANCE_MESSAGE', 'Bakım yapılıyor. Kısa süre içinde hizmetinizdeyiz.'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Ürün Öne Çıkan Etiketler
    |--------------------------------------------------------------------------
    */
    'product_featured_badges' => [
        'new_season' => 'Yeni Sezon',
        'best_seller' => 'Çok Satan',
        'durable' => 'Dayanıklı Tür',
        'premium' => 'Premium',
        'eco' => 'Eko Dostu',
    ],

    /*
    |--------------------------------------------------------------------------
    | Menşei Seçenekleri
    |--------------------------------------------------------------------------
    */
    'origin_options' => [
        'turkiye' => 'Türkiye',
        'hollanda' => 'Hollanda',
        'ekvador' => 'Ekvador',
        'kenya' => 'Kenya',
        'kolombiya' => 'Kolombiya',
        'israil' => 'İsrail',
        'diger' => 'Diğer',
    ],

];
