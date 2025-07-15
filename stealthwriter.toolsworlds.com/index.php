<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/include.php";

// If accessing root URL, redirect to humanizer directly
if ($_SERVER["REQUEST_URI"] === "/" || $_SERVER["REQUEST_URI"] === "/index.php") {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $proxyHost = $_SERVER["HTTP_HOST"];
    header("Location: " . $protocol . "://" . $proxyHost . "/humanizer");
    exit;
}

$url = WEBSITE_URL . $_SERVER["REQUEST_URI"];
initRequest($url);
echo "\n";
?>