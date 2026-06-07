<?php

return [
    'url' => env('SUPABASE_URL'),
    'anon_key' => env('SUPABASE_ANON_KEY'),
    'log_dedupe_seconds' => env('SUPABASE_LOG_DEDUPE_SECONDS', 0),
];
