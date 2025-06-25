<?php
require_once __DIR__ . "/include.php";
$url = WEBSITE_URL . $_SERVER["REQUEST_URI"];
initRequest($url);
echo "\n";
?>