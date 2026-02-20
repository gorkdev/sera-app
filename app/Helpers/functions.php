<?php

function formatliTarih($gelenTarih)
{
    // Zaman dilimini Türkiye'ye ayarlayalım
    date_default_timezone_set('Europe/Istanbul');

    $zaman = new DateTime($gelenTarih);
    $simdi = new DateTime();
   
    $bugunTarih = $simdi->format('Y-m-d');
    $dunTarih = (clone $simdi)->modify('-1 day')->format('Y-m-d');
    $gelenTarihFormat = $zaman->format('Y-m-d');

    // Durum kontrolü
    $bugunMu = ($gelenTarihFormat == $bugunTarih);
    $dunMu   = ($gelenTarihFormat == $dunTarih);

    // Türkçe isim dizileri
    $aylar = ["", "Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", "Temmuz", "Ağustos", "Eylül", "Ekim", "Kasım", "Aralık"];
    $gunler = ["Pazar", "Pazartesi", "Salı", "Çarşamba", "Perşembe", "Cuma", "Cumartesi"];

    $saatDakika = $zaman->format('H:i');
    $gunIsmi = $gunler[$zaman->format('w')]; // 0 (Pazar) - 6 (Cumartesi)
    $ayIsmi = $aylar[$zaman->format('n')];   // 1 (Ocak) - 12 (Aralık)
    $gunSayi = $zaman->format('j');

    if ($bugunMu) {
        return "Bugün - " . $saatDakika;
    } elseif ($dunMu) {
        return "Dün - " . $saatDakika;
    } else {
        // Örn: 19 Şubat Perşembe - 14:14
        return "$gunSayi $ayIsmi $gunIsmi - $saatDakika";
    }
}

