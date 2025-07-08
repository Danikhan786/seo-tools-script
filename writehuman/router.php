<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// List of asset extensions to proxy
$asset_extensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'woff2', 'woff', 'ttf', 'eot', 'ico', 'map'];

// Allow static assets
foreach ($asset_extensions as $ext) {
    if (preg_match('/\\.' . $ext . '$/', $path)) {
        $remoteUrl = 'https://writehuman.ai/' . $path;
        $opts = [
            'http' => [
                'header' => "Cookie: " . ($_SERVER['HTTP_COOKIE'] ?? '') . "\r\n"
            ]
        ];
        $context = stream_context_create($opts);
        $content = @file_get_contents($remoteUrl, false, $context);
        if ($content !== false) {
            $extToMime = [
                'css' => 'text/css',
                'js' => 'application/javascript',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
                'woff2' => 'font/woff2',
                'woff' => 'font/woff',
                'ttf' => 'font/ttf',
                'eot' => 'application/vnd.ms-fontobject',
                'ico' => 'image/x-icon',
                'map' => 'application/json'
            ];
            $mime = $extToMime[$ext] ?? 'application/octet-stream';
            header('Content-Type: ' . $mime);
            echo $content;
        } else {
            http_response_code(404);
            echo "Not found";
        }
        exit;
    }
}

// Allow only /humanizer or /humanizer/
// if ($path === '/humanizer' || $path === '/humanizer/') {
//     include_once "index.php";
//     exit;
// }

// For everything else, return 404
http_response_code(404);
echo "404 Not Found";
exit; 