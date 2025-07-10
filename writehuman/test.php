<?php
// Test file for WriterHuman Proxy
echo "<h1>WriterHuman Proxy Test</h1>";

// Check if cookie file exists
if (file_exists(__DIR__ . "/cookie.json")) {
    echo "<p style='color: green;'>✓ Cookie file exists</p>";
    
    $jsonContent = file_get_contents(__DIR__ . "/cookie.json");
    $dataFile = json_decode($jsonContent, true);
    
    if ($dataFile && isset($dataFile["WRITERHUMAN_PROXY"])) {
        echo "<p style='color: green;'>✓ Cookie configuration is valid</p>";
        
        $proxyData = $dataFile["WRITERHUMAN_PROXY"]["proxy"];
        echo "<p><strong>Target URL:</strong> " . $proxyData["targeturl"] . "</p>";
        echo "<p><strong>User Agent:</strong> " . $proxyData["useragent"] . "</p>";
        
        if (!empty($proxyData["ip"])) {
            echo "<p style='color: green;'>✓ Proxy configured</p>";
        } else {
            echo "<p style='color: orange;'>⚠ No proxy configured (direct connection)</p>";
        }
        
        $cookieData = $dataFile["WRITERHUMAN_PROXY"]["cookie_data"];
        echo "<p><strong>Cookies:</strong> " . count($cookieData) . " cookies loaded</p>";
        
    } else {
        echo "<p style='color: red;'>✗ Invalid cookie configuration</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Cookie file not found</p>";
}

// Check if CSS file exists
if (file_exists(__DIR__ . "/css/styles.css")) {
    echo "<p style='color: green;'>✓ CSS file exists</p>";
} else {
    echo "<p style='color: red;'>✗ CSS file not found</p>";
}

// Check if access.php exists
if (file_exists(__DIR__ . "/access.php")) {
    echo "<p style='color: green;'>✓ Access file exists</p>";
} else {
    echo "<p style='color: red;'>✗ Access file not found</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Go to WriterHuman Proxy</a></p>";
?> 