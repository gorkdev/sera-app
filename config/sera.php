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

    'category_attributes' => [
        'expiry_date' => 'Son Kullanma Tarihi',
        'litre' => 'Litraj',
        'germination_days' => 'Çimlenme Süresi (gün)',
        'shelf_life_days' => 'Raf Ömrü (gün)',
        'origin' => 'Menşei',
        'stem_length_cm' => 'Dal Boyu (cm)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Bölge Kısıtı: Şehir ve Bölge Listesi
    |--------------------------------------------------------------------------
    |
    | Kategori bölge kısıtında otomatik tamamlama için kullanılır.
    |
    */

    'region_cities' => [
        'adana', 'adıyaman', 'afyonkarahisar', 'ağrı', 'aksaray', 'amasya', 'ankara', 'antalya',
        'ardahan', 'artvin', 'aydın', 'balıkesir', 'bartın', 'batman', 'bayburt', 'bilecik',
        'bingöl', 'bitlis', 'bolu', 'burdur', 'bursa', 'çanakkale', 'çankırı', 'çorum',
        'denizli', 'diyarbakır', 'düzce', 'edirne', 'elazığ', 'erzincan', 'erzurum', 'eskişehir',
        'gaziantep', 'giresun', 'gümüşhane', 'hakkari', 'hatay', 'ığdır', 'isparta', 'istanbul',
        'izmir', 'kahramanmaraş', 'karabük', 'karaman', 'kars', 'kastamonu', 'kayseri', 'kırıkkale',
        'kırklareli', 'kırşehir', 'kilis', 'kocaeli', 'konya', 'kütahya', 'malatya', 'manisa',
        'mardin', 'mersin', 'muğla', 'muş', 'nevşehir', 'niğde', 'ordu', 'osmaniye',
        'rize', 'sakarya', 'samsun', 'siirt', 'sinop', 'sivas', 'şanlıurfa', 'şırnak',
        'tekirdağ', 'tokat', 'trabzon', 'tunceli', 'uşak', 'van', 'yalova', 'yozgat', 'zonguldak',
    ],

    'region_regions' => [
        'marmara', 'ege', 'akdeniz', 'iç anadolu', 'karadeniz', 'doğu anadolu', 'güneydoğu anadolu',
    ],

    /*
    |--------------------------------------------------------------------------
    | Kategori Gelişmiş Ayarları
    |--------------------------------------------------------------------------
    */

    'category_required_documents' => [
        'organic_certificate' => 'Organik Sertifikası',
        'analysis_report' => 'Analiz Raporu',
        'phytosanitary' => 'Bitki Sağlık Sertifikası',
        'origin_certificate' => 'Menşei Belgesi',
    ],

    'category_icons' => [
        'leaf' => 'Yaprak',
        'sun' => 'Güneş',
        'droplet' => 'Su Damlası',
        'flower' => 'Çiçek',
        'sparkles' => 'Parlak',
        'fire' => 'Ateş',
        'bolt' => 'Şimşek',
        'beaker' => 'Deney',
    ],

    'category_featured_badges' => [
        'new_season' => 'Yeni Sezon',
        'best_seller' => 'Çok Satan',
        'durable' => 'Dayanıklı Tür',
        'premium' => 'Premium',
        'eco' => 'Eko Dostu',
    ],

    'category_colors' => [
        '#ef4444' => 'Kırmızı',
        '#f97316' => 'Turuncu',
        '#eab308' => 'Sarı',
        '#84cc16' => 'Lime',
        '#22c55e' => 'Yeşil',
        '#14b8a6' => 'Teal',
        '#06b6d4' => 'Cyan',
        '#3b82f6' => 'Mavi',
        '#6366f1' => 'İndigo',
        '#8b5cf6' => 'Mor',
        '#a855f7' => 'Violet',
        '#ec4899' => 'Pembe',
        '#f43f5e' => 'Rose',
        '#78716c' => 'Kahverengi',
    ],

];
