<?php
// Simple launcher for WriterHuman tool
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WriterHuman Tool Launcher</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: white;
        }
        .container {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #fff;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        .status {
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-weight: bold;
        }
        .success { background: rgba(40, 167, 69, 0.2); border: 1px solid #28a745; }
        .error { background: rgba(220, 53, 69, 0.2); border: 1px solid #dc3545; }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 10px;
            background: linear-gradient(45deg, #007cba, #005a87);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        .btn.success {
            background: linear-gradient(45deg, #28a745, #20c997);
        }
        .info-box {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 WriterHuman Tool</h1>
        
        <?php
        // Check if required files exist
        $requiredFiles = ['cookie.json', 'writerHuman.php', 'index.php'];
        $missingFiles = [];
        
        foreach ($requiredFiles as $file) {
            if (!file_exists($file)) {
                $missingFiles[] = $file;
            }
        }
        
        if (!empty($missingFiles)) {
            echo '<div class="status error">';
            echo '❌ Missing required files: ' . implode(', ', $missingFiles);
            echo '</div>';
        } else {
            echo '<div class="status success">';
            echo '✅ All required files are present';
            echo '</div>';
        }
        
        // Check cookie configuration
        if (file_exists('cookie.json')) {
            $jsonContent = file_get_contents('cookie.json');
            $dataFile = json_decode($jsonContent, true);
            
            if ($dataFile && isset($dataFile["WRITERHUMAN_PROXY"])) {
                $config = $dataFile["WRITERHUMAN_PROXY"];
                echo '<div class="info-box">';
                echo '<h3>📋 Configuration Status</h3>';
                echo '<p><strong>Target URL:</strong> ' . htmlspecialchars($config["proxy"]["targeturl"]) . '</p>';
                echo '<p><strong>Cookies:</strong> ' . count($config["cookie_data"]) . ' cookies configured</p>';
                echo '</div>';
                
                // Quick connection test
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $config["proxy"]["targeturl"],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_TIMEOUT => 5,
                    CURLOPT_USERAGENT => $config["proxy"]["useragent"],
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_HEADER => true,
                    CURLOPT_NOBODY => true
                ]);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);
                curl_close($ch);
                
                if ($error) {
                    echo '<div class="status error">';
                    echo '❌ Connection Error: ' . htmlspecialchars($error);
                    echo '</div>';
                } else {
                    if ($httpCode == 200) {
                        echo '<div class="status success">';
                        echo '✅ Connection test successful (HTTP ' . $httpCode . ')';
                        echo '</div>';
                    } else {
                        echo '<div class="status error">';
                        echo '⚠️ Connection established but HTTP status is ' . $httpCode;
                        echo '</div>';
                    }
                }
            } else {
                echo '<div class="status error">';
                echo '❌ Invalid cookie configuration';
                echo '</div>';
            }
        }
        ?>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="btn success">
                🚀 Launch WriterHuman Tool
            </a>
            <a href="test.php" class="btn">
                🧪 Test Connection
            </a>
            <a href="status.php" class="btn">
                🔍 Status Check
            </a>
        </div>
        
        <div class="info-box">
            <h3>💡 How to Use</h3>
            <ol>
                <li>Click "Launch WriterHuman Tool" to start the proxy</li>
                <li>The tool will load WriteHuman.ai through your local server</li>
                <li>All features will work as if you're using the original site</li>
                <li>Your session cookies are automatically included</li>
            </ol>
        </div>
    </div>
</body>
</html> 