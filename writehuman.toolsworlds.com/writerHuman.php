<?php
// This file handles special cases for WriteHuman.ai resources
// Main logic is now in include.php

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

// Handle special cases for WriteHuman.ai resources
if (strpos($requestUri, '/package/') === 0 || strpos($requestUri, '/api/') === 0) {
    // These are internal WriteHuman.ai resources, proxy them directly
    require_once __DIR__ . "/include.php";
    $targetUrl = WEBSITE_URL . ltrim($requestUri, '/');
    $response = makeRequest($targetUrl);
    $contentType = $response['responseInfo']['content_type'] ?? 'application/octet-stream';
    header('Content-Type: ' . $contentType);
    echo $response['body'];
    exit;
}

// Handle CDN resources - redirect to original
if (strpos($requestUri, '/cdn.') !== false || preg_match('/\/[a-z0-9-]+\.cdn\.bubble\.io\//', $requestUri)) {
    $targetUrl = 'https://writehuman.ai' . $requestUri;
    header('Location: ' . $targetUrl);
    exit;
}

// For all other requests, use the main include.php logic
require_once __DIR__ . "/include.php";
$url = WEBSITE_URL . $_SERVER["REQUEST_URI"];
initRequest($url);
exit;
?>
