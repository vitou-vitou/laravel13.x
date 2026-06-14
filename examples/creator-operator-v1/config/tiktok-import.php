<?php

$repoRoot = dirname(base_path(), 3);

return [
    'python' => env('TIKTOK_PYTHON', 'python'),
    'script' => env('TIKTOK_METADATA_SCRIPT', $repoRoot.'/tools/tiktok-metadata/scrape_tiktok.py'),
    'timeout' => (int) env('TIKTOK_CLI_TIMEOUT', 120),
];
