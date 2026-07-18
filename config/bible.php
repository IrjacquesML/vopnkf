<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Bible (scripture.api.bible)
    |--------------------------------------------------------------------------
    |
    | Priorité des sources de versets :
    | 1. Cache local (table versets)
    | 2. scripture.api.bible — Louis Segond 1910 (clé gratuite)
    | 3. bible-api.com — fallback sans clé (anglais WEB)
    |
    */
    'api_key' => env('BIBLE_API_KEY', ''),
    'api_url' => env('BIBLE_API_URL', 'https://api.scripture.api.bible/v1'),
    'lsg_id' => env('BIBLE_LSG_ID', ''),
    'cache_days' => (int) env('BIBLE_CACHE_DAYS', 30),
];
