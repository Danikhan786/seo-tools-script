<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/include.php";
$url = WEBSITE_URL . $_SERVER["REQUEST_URI"];
initRequest($url);
echo "\n";
?>