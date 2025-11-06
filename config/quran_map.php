<?php

return [
    // Lokasi file JSON peta Juz → (Surah, ayat_mulai, ayat_akhir)
    'json_path' => storage_path('app/quran/data_quran.json'),

    // Cache 10 menit agar tidak baca file terus-menerus
    'cache_ttl_seconds' => 600,
];
