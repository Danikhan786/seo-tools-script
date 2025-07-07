<?php
ob_start(function($buffer) {
    $customCss = '<style>
    header.sticky, aside.fixed { display: none !important; }
    body { margin: 0 !important; padding: 0 !important; }
    </style>';
    // Inject CSS before </head>
    return preg_replace('/<\\/head>/i', $customCss . '</head>', $buffer, 1);
});

require __DIR__ . '/stealthWriter.php';

// Load custom CSS
$css = file_get_contents(__DIR__ . "/css/styles.css");

// Construct the full URL for the request to the target website
$url = WEBSITE_URL . $_SERVER["REQUEST_URI"];

// Initialize and execute the proxy request
initRequest($url);

echo '<style>
header.sticky,
aside.fixed {
    display: none !important;
}
body {
    margin: 0 !important;
    padding: 0 !important;
}
</style>';

echo "\n";
?>