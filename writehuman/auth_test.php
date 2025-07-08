<?php
// Authentication test for StealthWriter.ai

echo "<h2>🔐 StealthWriter.ai Authentication Test</h2>";

// Load configuration
$cookieFile = __DIR__ . "/cookie.json";
$jsonContent = file_get_contents($cookieFile);
$dataFile = json_decode($jsonContent, true);
$config = $dataFile["WRITERHUMAN_PROXY"];

$targetUrl = $config["proxy"]["targeturl"];
$cookieData = $config["cookie_data"];

echo "<h3>Testing Authentication...</h3>";

// Test connection to StealthWriter.ai
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $targetUrl . "/humanizer",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_USERAGENT => $config["proxy"]["useragent"],
    CURLOPT_HEADER => true,
    CURLOPT_NOBODY => false
]);

// Build cookie header
$cookieHeader = "";
foreach ($cookieData as $key => $value) {
    $cookieHeader .= $key . "=" . $value . "; ";
}
curl_setopt($ch, CURLOPT_COOKIE, $cookieHeader);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
$contentLength = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
curl_close($ch);

echo "<div style='background: #f5f5f5; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
echo "<p><strong>Target URL:</strong> " . htmlspecialchars($targetUrl . "/humanizer") . "</p>";
echo "<p><strong>HTTP Status:</strong> " . $httpCode . "</p>";
echo "<p><strong>Content Length:</strong> " . $contentLength . " bytes</p>";

if ($error) {
    echo "<p style='color: red;'>❌ <strong>Error:</strong> " . htmlspecialchars($error) . "</p>";
} else {
    if ($httpCode == 200) {
        echo "<p style='color: green;'>✅ <strong>Success:</strong> Connection established</p>";
        
        // Check if we got the login page or the actual content
        if (strpos($response, 'Login') !== false || strpos($response, 'Enter your credentials') !== false) {
            echo "<p style='color: orange;'>⚠️ <strong>Warning:</strong> Redirected to login page - cookies may be expired</p>";
        } else {
            echo "<p style='color: green;'>✅ <strong>Success:</strong> Authenticated access confirmed</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ <strong>Warning:</strong> HTTP Status " . $httpCode . " - may need authentication refresh</p>";
    }
}
echo "</div>";

// Show cookie information
echo "<h3>Cookie Information:</h3>";
echo "<div style='background: #f5f5f5; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
foreach ($cookieData as $name => $value) {
    $displayValue = strlen($value) > 50 ? substr($value, 0, 50) . "..." : $value;
    echo "<p><strong>" . htmlspecialchars($name) . ":</strong> " . htmlspecialchars($displayValue) . "</p>";
}
echo "</div>";

// Test specific humanizer page
echo "<h3>Testing Humanizer Page Access:</h3>";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $targetUrl . "/humanizer",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_USERAGENT => $config["proxy"]["useragent"],
    CURLOPT_COOKIE => $cookieHeader
]);

$humanizerResponse = curl_exec($ch);
$humanizerHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<div style='background: #f5f5f5; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
echo "<p><strong>Humanizer Page Status:</strong> " . $humanizerHttpCode . "</p>";

if (strpos($humanizerResponse, 'Login') !== false) {
    echo "<p style='color: red;'>❌ <strong>Authentication Failed:</strong> Redirected to login page</p>";
    echo "<p><strong>Solution:</strong> You need to refresh your StealthWriter.ai cookies</p>";
} else {
    echo "<p style='color: green;'>✅ <strong>Authentication Working:</strong> Humanizer page accessible</p>";
}
echo "</div>";

echo "<hr>";
echo "<p><a href='index.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Launch Proxy</a></p>";
echo "<p><a href='launch.php' style='background: #ff5722; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Go to Launcher</a></p>";
?> 