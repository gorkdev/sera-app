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
        'maintenance_message' => 'Bakım yapılıyor. Kısa süre içinde hizmetinizdeyiz.',
    ],

];
