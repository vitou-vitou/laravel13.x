<?php

$publicPath = __DIR__ . '/public';

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');

$file = $publicPath . $uri;

if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    // Serve static file with correct MIME type
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $mime = match($ext) {
        'js'   => 'application/javascript',
        'css'  => 'text/css',
        'png'  => 'image/png',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
        'ttf'  => 'font/ttf',
        default => 'application/octet-stream',
    };
    header('Content-Type: ' . $mime);
    readfile($file);
    return;
}

require_once $publicPath . '/index.php';
