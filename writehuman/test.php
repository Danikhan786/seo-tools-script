<?php
// Simple connection test for WriterHuman tool
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connection Test - WriterHuman Tool</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
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
        .test-result {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            font-weight: bold;
        }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
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
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Connection Test</h1>
        
        <?php
        // Test 1: Basic PHP and cURL
        echo '<h3>1. PHP Environment</h3>';
        echo '<div class="test-result success">';
        echo '<strong>PHP Version:</strong> ' . phpversion() . '<br>';
        echo '<strong>cURL Extension:</strong> ' . (extension_loaded('curl') ? '✅ Available' : '❌ Not Available') . '<br>';
        echo '<strong>JSON Extension:</strong> ' . (extension_loaded('json') ? '✅ Available' : '❌ Not Available');
        echo '</div>';

        // Test 2: Cookie configuration
        echo '<h3>2. Cookie Configuration</h3>';
        if (file_exists('cookie.json')) {
            $jsonContent = file_get_contents('cookie.json');
            $dataFile = json_decode($jsonContent, true);
            
            if ($dataFile && isset($dataFile["WRITERHUMAN_PROXY"])) {
                $config = $dataFile["WRITERHUMAN_PROXY"];
                echo '<div class="test-result success">';
                echo '<strong>Target URL:</strong> ' . htmlspecialchars($config["proxy"]["targeturl"]) . '<br>';
                echo '<strong>Cookies:</strong> ' . count($config["cookie_data"]) . ' cookies configured';
                echo '</div>';
                
                // Test 3: Connection to WriteHuman.ai
                echo '<h3>3. WriteHuman.ai Connection</h3>';
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $config["proxy"]["targeturl"],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_USERAGENT => $config["proxy"]["useragent"],
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_HEADER => true,
                    CURLOPT_NOBODY => true
                ]);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
                curl_close($ch);
                
                if ($error) {
                    echo '<div class="test-result error">';
                    echo '❌ Connection Error: ' . htmlspecialchars($error);
                    echo '</div>';
                } else {
                    echo '<div class="test-result success">';
                    echo '✅ Connection successful<br>';
                    echo '<strong>HTTP Status:</strong> ' . $httpCode . '<br>';
                    echo '<strong>Response Time:</strong> ' . round($totalTime * 1000, 2) . 'ms';
                    echo '</div>';
                }
            } else {
                echo '<div class="test-result error">';
                echo '❌ Invalid cookie configuration';
                echo '</div>';
            }
        } else {
            echo '<div class="test-result error">';
            echo '❌ Cookie file not found';
            echo '</div>';
        }
        ?>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="launch.php" class="btn">🚀 Back to Launcher</a>
            <a href="index.php" class="btn">🎯 Launch Tool</a>
        </div>
    </div>
</body>
</html> 