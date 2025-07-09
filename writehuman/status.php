<?php
// Simple status checker
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Check - WriterHuman Tool</title>
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
        .status-item {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            font-weight: bold;
        }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .warning { background: #fff3cd; color: #856404; }
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
        <h1>🔍 Status Check</h1>
        
        <?php
        // Check PHP
        echo '<div class="status-item success">';
        echo '✅ PHP Version: ' . phpversion();
        echo '</div>';
        
        // Check cURL
        if (extension_loaded('curl')) {
            echo '<div class="status-item success">';
            echo '✅ cURL Extension: Available';
            echo '</div>';
        } else {
            echo '<div class="status-item error">';
            echo '❌ cURL Extension: Not Available';
            echo '</div>';
        }
        
        // Check JSON
        if (extension_loaded('json')) {
            echo '<div class="status-item success">';
            echo '✅ JSON Extension: Available';
            echo '</div>';
        } else {
            echo '<div class="status-item error">';
            echo '❌ JSON Extension: Not Available';
            echo '</div>';
        }
        
        // Check files
        $files = [
            'index.php' => 'Main entry point',
            'writerHuman.php' => 'Core proxy logic',
            'cookie.json' => 'Configuration file',
            'css/styles.css' => 'CSS styling'
        ];
        
        foreach ($files as $file => $description) {
            if (file_exists($file)) {
                echo '<div class="status-item success">';
                echo '✅ ' . $description . ' (' . $file . '): Found';
                echo '</div>';
            } else {
                echo '<div class="status-item error">';
                echo '❌ ' . $description . ' (' . $file . '): Missing';
                echo '</div>';
            }
        }
        
        // Check cookie configuration
        if (file_exists('cookie.json')) {
            $jsonContent = file_get_contents('cookie.json');
            $dataFile = json_decode($jsonContent, true);
            
            if ($dataFile && isset($dataFile["WRITERHUMAN_PROXY"])) {
                $cookieCount = count($dataFile["WRITERHUMAN_PROXY"]["cookie_data"]);
                echo '<div class="status-item success">';
                echo '✅ Cookie Configuration: Valid (' . $cookieCount . ' cookies)';
                echo '</div>';
            } else {
                echo '<div class="status-item error">';
                echo '❌ Cookie Configuration: Invalid structure';
                echo '</div>';
            }
        }
        
        // Test connection
        if (extension_loaded('curl')) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://writehuman.ai',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                echo '<div class="status-item error">';
                echo '❌ WriteHuman.ai Connection: ' . htmlspecialchars($error);
                echo '</div>';
            } else {
                echo '<div class="status-item success">';
                echo '✅ WriteHuman.ai Connection: HTTP ' . $httpCode;
                echo '</div>';
            }
        }
        ?>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="launch.php" class="btn">🚀 Back to Launcher</a>
            <a href="test.php" class="btn">🧪 Test Connection</a>
            <a href="index.php" class="btn">🎯 Launch Tool</a>
        </div>
    </div>
</body>
</html> 