<?php
// Comprehensive local server diagnostic for WriterHuman tool
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Local Server Diagnostic - WriterHuman Tool</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .test-section {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .success { background: #d4edda; border-color: #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .warning { background: #fff3cd; border-color: #ffeaa7; color: #856404; }
        .info { background: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007cba;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
        }
        .btn:hover { background: #005a87; }
        .code {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 12px;
            margin: 10px 0;
            overflow-x: auto;
        }
        .solution {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Local Server Diagnostic</h1>
        <p>This tool will help identify issues with your WriterHuman tool on local server.</p>
        
        <?php
        // Test 1: Server Environment
        echo '<div class="test-section info">';
        echo '<h3>1. Server Environment</h3>';
        echo '<p><strong>Server Software:</strong> ' . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . '</p>';
        echo '<p><strong>Document Root:</strong> ' . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . '</p>';
        echo '<p><strong>Current Domain:</strong> ' . ($_SERVER['HTTP_HOST'] ?? 'Unknown') . '</p>';
        echo '<p><strong>Request URI:</strong> ' . ($_SERVER['REQUEST_URI'] ?? 'Unknown') . '</p>';
        echo '<p><strong>PHP Version:</strong> ' . phpversion() . '</p>';
        echo '</div>';

        // Test 2: PHP Extensions
        echo '<div class="test-section info">';
        echo '<h3>2. PHP Extensions</h3>';
        
        $requiredExtensions = ['curl', 'json', 'openssl'];
        foreach ($requiredExtensions as $ext) {
            if (extension_loaded($ext)) {
                echo '<p>✅ <strong>' . $ext . '</strong>: Available</p>';
            } else {
                echo '<p>❌ <strong>' . $ext . '</strong>: Not Available</p>';
            }
        }
        echo '</div>';

        // Test 3: File System
        echo '<div class="test-section info">';
        echo '<h3>3. File System Check</h3>';
        
        $requiredFiles = [
            'index.php' => 'Main entry point',
            'writerHuman.php' => 'Core proxy logic',
            'cookie.json' => 'Configuration file',
            'css/styles.css' => 'CSS styling',
            'js-fix.js' => 'JavaScript fixes',
            '.htaccess' => 'URL rewriting'
        ];
        
        foreach ($requiredFiles as $file => $description) {
            if (file_exists($file)) {
                $size = filesize($file);
                $readable = is_readable($file);
                echo '<p>✅ <strong>' . $description . '</strong> (' . $file . '): Found (' . $size . ' bytes, ' . ($readable ? 'readable' : 'not readable') . ')</p>';
            } else {
                echo '<p>❌ <strong>' . $description . '</strong> (' . $file . '): Missing</p>';
            }
        }
        echo '</div>';

        // Test 4: Cookie Configuration
        echo '<div class="test-section info">';
        echo '<h3>4. Cookie Configuration</h3>';
        
        if (file_exists('cookie.json')) {
            $jsonContent = file_get_contents('cookie.json');
            $dataFile = json_decode($jsonContent, true);
            
            if ($dataFile && isset($dataFile["WRITERHUMAN_PROXY"])) {
                $config = $dataFile["WRITERHUMAN_PROXY"];
                $cookieCount = count($config["cookie_data"]);
                echo '<p>✅ <strong>Configuration Structure:</strong> Valid</p>';
                echo '<p>✅ <strong>Target URL:</strong> ' . htmlspecialchars($config["proxy"]["targeturl"]) . '</p>';
                echo '<p>✅ <strong>Cookies:</strong> ' . $cookieCount . ' cookies configured</p>';
                
                // Check for essential cookies
                $essentialCookies = ['rephrasegpt_live_u2main', 'rephrasegpt_live_u2main.sig', 'rephrasegpt_u1main'];
                $missingCookies = [];
                foreach ($essentialCookies as $cookie) {
                    if (!isset($config["cookie_data"][$cookie])) {
                        $missingCookies[] = $cookie;
                    }
                }
                
                if (empty($missingCookies)) {
                    echo '<p>✅ <strong>Essential Cookies:</strong> All present</p>';
                } else {
                    echo '<p>⚠️ <strong>Missing Essential Cookies:</strong> ' . implode(', ', $missingCookies) . '</p>';
                }
            } else {
                echo '<p>❌ <strong>Configuration Structure:</strong> Invalid</p>';
            }
        } else {
            echo '<p>❌ <strong>Cookie File:</strong> Not found</p>';
        }
        echo '</div>';

        // Test 5: Network Connectivity
        echo '<div class="test-section info">';
        echo '<h3>5. Network Connectivity</h3>';
        
        if (extension_loaded('curl')) {
            // Test DNS resolution
            $ip = gethostbyname('writehuman.ai');
            if ($ip !== 'writehuman.ai') {
                echo '<p>✅ <strong>DNS Resolution:</strong> writehuman.ai → ' . $ip . '</p>';
            } else {
                echo '<p>❌ <strong>DNS Resolution:</strong> Failed to resolve writehuman.ai</p>';
            }
            
            // Test connection to WriteHuman.ai
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://writehuman.ai',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
            curl_close($ch);
            
            if ($error) {
                echo '<p>❌ <strong>WriteHuman.ai Connection:</strong> ' . htmlspecialchars($error) . '</p>';
            } else {
                echo '<p>✅ <strong>WriteHuman.ai Connection:</strong> HTTP ' . $httpCode . ' (' . round($totalTime * 1000, 2) . 'ms)</p>';
            }
        } else {
            echo '<p>❌ <strong>cURL Extension:</strong> Not available for network tests</p>';
        }
        echo '</div>';

        // Test 6: URL Rewriting
        echo '<div class="test-section info">';
        echo '<h3>6. URL Rewriting</h3>';
        
        if (file_exists('.htaccess')) {
            echo '<p>✅ <strong>.htaccess File:</strong> Present</p>';
            
            // Check if mod_rewrite is enabled (Apache only)
            if (function_exists('apache_get_modules')) {
                $modules = apache_get_modules();
                if (in_array('mod_rewrite', $modules)) {
                    echo '<p>✅ <strong>mod_rewrite:</strong> Enabled</p>';
                } else {
                    echo '<p>❌ <strong>mod_rewrite:</strong> Disabled</p>';
                }
            } else {
                echo '<p>⚠️ <strong>mod_rewrite:</strong> Unable to check (not Apache or function not available)</p>';
            }
        } else {
            echo '<p>❌ <strong>.htaccess File:</strong> Missing</p>';
        }
        echo '</div>';

        // Test 7: Common Local Server Issues
        echo '<div class="test-section warning">';
        echo '<h3>7. Common Local Server Issues</h3>';
        
        // Check if we're on localhost
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
            echo '<p>✅ <strong>Local Server:</strong> Running on localhost</p>';
        } else {
            echo '<p>⚠️ <strong>Local Server:</strong> Not running on localhost (' . $host . ')</p>';
        }
        
        // Check for common port issues
        if (strpos($host, ':8000') !== false) {
            echo '<p>✅ <strong>Port:</strong> Using port 8000 (common for local development)</p>';
        } elseif (strpos($host, ':') !== false) {
            echo '<p>⚠️ <strong>Port:</strong> Using custom port</p>';
        } else {
            echo '<p>⚠️ <strong>Port:</strong> Using default port (80/443)</p>';
        }
        
        // Check for SSL issues
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            echo '<p>✅ <strong>SSL:</strong> HTTPS enabled</p>';
        } else {
            echo '<p>⚠️ <strong>SSL:</strong> HTTP only (may cause mixed content issues)</p>';
        }
        echo '</div>';
        ?>

        <div class="solution">
            <h3>🔧 Quick Solutions</h3>
            <ol>
                <li><strong>If files are missing:</strong> Upload all files to your local server directory</li>
                <li><strong>If cURL is not available:</strong> Enable cURL extension in your PHP configuration</li>
                <li><strong>If cookies are invalid:</strong> Update cookie.json with fresh WriteHuman.ai cookies</li>
                <li><strong>If connection fails:</strong> Check your internet connection and firewall settings</li>
                <li><strong>If URL rewriting doesn't work:</strong> Enable mod_rewrite or use a different server</li>
                <li><strong>If you see 404 errors:</strong> Make sure all files are in the correct directory</li>
            </ol>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="launch.php" class="btn">🚀 Back to Launcher</a>
            <a href="test.php" class="btn">🧪 Test Connection</a>
            <a href="index.php" class="btn">🎯 Launch Tool</a>
            <button onclick="location.reload()" class="btn">🔄 Refresh Test</button>
        </div>
    </div>
</body>
</html> 