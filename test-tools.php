<?php
// Test file to verify PHP environment and tool setup

echo "<h1>SEO Tools Test Page</h1>";

// Check PHP version
echo "<h2>PHP Environment</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";

// Check if cURL is available
echo "<p><strong>cURL Extension:</strong> " . (extension_loaded('curl') ? '✅ Available' : '❌ Not Available') . "</p>";

// Check if JSON is available
echo "<p><strong>JSON Extension:</strong> " . (extension_loaded('json') ? '✅ Available' : '❌ Not Available') . "</p>";

// Check file permissions
echo "<h2>File Access</h2>";
$tools = [
    'stealth writer tool' => 'stealth writer tool/cookie.json',
    'writehuman' => 'writehuman/cookie.json',
    'testing-code' => 'testing-code/cookie.json'
];

foreach ($tools as $toolName => $cookieFile) {
    if (file_exists($cookieFile)) {
        echo "<p><strong>{$toolName}:</strong> ✅ Cookie file exists</p>";
        
        // Try to read and parse JSON
        $content = file_get_contents($cookieFile);
        $data = json_decode($content, true);
        if ($data) {
            echo "<p><strong>{$toolName} JSON:</strong> ✅ Valid JSON</p>";
            
            // Check if proxy settings are empty
            $firstKey = array_key_first($data);
            if (isset($data[$firstKey]['proxy'])) {
                $proxy = $data[$firstKey]['proxy'];
                $hasProxy = !empty($proxy['ip']) && !empty($proxy['port']);
                echo "<p><strong>{$toolName} Proxy:</strong> " . ($hasProxy ? '✅ Configured' : '⚠️ Empty (will work without proxy)') . "</p>";
            }
        } else {
            echo "<p><strong>{$toolName} JSON:</strong> ❌ Invalid JSON</p>";
        }
    } else {
        echo "<p><strong>{$toolName}:</strong> ❌ Cookie file missing</p>";
    }
}

// Test basic cURL functionality
echo "<h2>cURL Test</h2>";
if (extension_loaded('curl')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://httpbin.org/ip');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response && $httpCode == 200) {
        echo "<p><strong>cURL Test:</strong> ✅ Working (HTTP {$httpCode})</p>";
    } else {
        echo "<p><strong>cURL Test:</strong> ❌ Failed (HTTP {$httpCode})</p>";
    }
}

// Tool links
echo "<h2>Tool Links</h2>";
echo "<p><a href='stealth writer tool/' target='_blank'>🚀 Stealth Writer Tool</a></p>";
echo "<p><a href='writehuman/' target='_blank'>✍️ WriteHuman Tool</a></p>";
echo "<p><a href='testing-code/' target='_blank'>🧪 Testing Code Tool</a></p>";

echo "<h2>Instructions</h2>";
echo "<ol>";
echo "<li>Make sure you're running this through a web server (Apache/Nginx/XAMPP/WAMP)</li>";
echo "<li>Click on the tool links above to test each tool</li>";
echo "<li>If you see errors, check the browser console and server error logs</li>";
echo "<li>The tools should now work without proxy settings</li>";
echo "</ol>";

echo "<h2>Common Issues & Solutions</h2>";
echo "<ul>";
echo "<li><strong>Blank page:</strong> Check PHP error logs</li>";
echo "<li><strong>cURL errors:</strong> Make sure cURL extension is enabled</li>";
echo "<li><strong>Permission denied:</strong> Check file permissions</li>";
echo "<li><strong>JSON errors:</strong> Verify cookie.json files are valid</li>";
echo "</ul>";
?> 