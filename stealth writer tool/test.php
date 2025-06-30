<?php
// Test file to verify StealthWriter.ai proxy configuration

// Load the cookie configuration
$cookieFile = __DIR__ . "/cookie.json";
if (!file_exists($cookieFile)) {
    die("Cookie file not found!");
}

$jsonContent = file_get_contents($cookieFile);
$dataFile = json_decode($jsonContent, true);

if (!$dataFile || !isset($dataFile["STEALTHWRITER_PROXY"])) {
    die("Invalid cookie configuration!");
}

$config = $dataFile["STEALTHWRITER_PROXY"];
$targetUrl = $config["proxy"]["targeturl"];
$cookieData = $config["cookie_data"];

echo "<h2>StealthWriter.ai Proxy Configuration Test</h2>";
echo "<p><strong>Target URL:</strong> " . htmlspecialchars($targetUrl) . "</p>";
echo "<p><strong>User Agent:</strong> " . htmlspecialchars($config["proxy"]["useragent"]) . "</p>";

echo "<h3>Cookie Data:</h3>";
echo "<ul>";
foreach ($cookieData as $name => $value) {
    $displayValue = strlen($value) > 50 ? substr($value, 0, 50) . "..." : $value;
    echo "<li><strong>" . htmlspecialchars($name) . ":</strong> " . htmlspecialchars($displayValue) . "</li>";
}
echo "</ul>";

// Test the connection
echo "<h3>Testing Connection...</h3>";
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $targetUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_USERAGENT => $config["proxy"]["useragent"],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HEADER => true,
    CURLOPT_NOBODY => true
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
curl_close($ch);

if ($error) {
    echo "<p style='color: red;'><strong>Error:</strong> " . htmlspecialchars($error) . "</p>";
} else {
    echo "<p style='color: green;'><strong>HTTP Status:</strong> " . $httpCode . "</p>";
    if ($httpCode == 200) {
        echo "<p style='color: green;'>✅ Connection successful! The proxy should work correctly.</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Connection established but HTTP status is " . $httpCode . "</p>";
    }
}

echo "<hr>";
echo "<p><a href='index.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Launch StealthWriter Proxy</a></p>";
?> 