<?php
require __DIR__ . '/stealthWriter.php';

// Load custom CSS
$css = file_get_contents(__DIR__ . "/css/styles.css");

// Construct the full URL for the request to the target website
$url = WEBSITE_URL . $_SERVER["REQUEST_URI"];

// Initialize and execute the proxy request
initRequest($url);

echo "\n";
?>