<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StealthWriter.ai Proxy - Launcher</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 2.5em;
        }
        .description {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            background: linear-gradient(135deg, #ff5722, #ff7043);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: bold;
            font-size: 18px;
            margin: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(255, 87, 34, 0.3);
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 87, 34, 0.4);
        }
        .btn-secondary {
            background: linear-gradient(135deg, #2196F3, #42A5F5);
            box-shadow: 0 5px 15px rgba(33, 150, 243, 0.3);
        }
        .btn-secondary:hover {
            box-shadow: 0 8px 25px rgba(33, 150, 243, 0.4);
        }
        .status {
            margin-top: 20px;
            padding: 15px;
            background: #e8f5e8;
            border-radius: 10px;
            color: #2e7d32;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 StealthWriter.ai Proxy</h1>
        <p class="description">
            Access StealthWriter.ai through this secure proxy with your authenticated session. 
            All features including the Humanizer tool are available.
        </p>
        
        <a href="index.php" class="btn">Launch StealthWriter Proxy</a>
        <a href="test.php" class="btn btn-secondary">Test Configuration</a>
        
        <div class="status">
            ✅ Proxy Ready - Authentication Configured
        </div>
    </div>
</body>
</html> 