<?php
// Fix Console Errors Script for StealthWriter Proxy
// This script helps reduce console errors by blocking problematic third-party scripts

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['fix_errors'])) {
    // Read current cookie.json
    $cookieFile = 'cookie.json';
    $currentData = json_decode(file_get_contents($cookieFile), true);
    
    // Add error fixing configuration
    $currentData['STEALTHWRITER_PROXY']['error_fixes'] = [
        'block_gtm' => true,
        'block_facebook' => true,
        'block_cookiebot' => true,
        'block_fonts' => false,
        'block_analytics' => true
    ];
    
    // Save back to file
    file_put_contents($cookieFile, json_encode($currentData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin: 20px;'>✅ Console error fixes applied successfully!</div>";
}

// Get current configuration
$currentData = json_decode(file_get_contents('cookie.json'), true);
$errorFixes = $currentData['STEALTHWRITER_PROXY']['error_fixes'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Console Errors - StealthWriter</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .error-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .error-info h3 {
            color: #856404;
            margin-top: 0;
        }
        .error-list {
            list-style: none;
            padding: 0;
        }
        .error-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .error-list li:last-child {
            border-bottom: none;
        }
        .error-type {
            font-weight: bold;
            color: #dc3545;
        }
        .error-description {
            color: #666;
            font-size: 14px;
        }
        .fix-option {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .fix-option label {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        .fix-option input[type="checkbox"] {
            margin-right: 10px;
        }
        button {
            background-color: #007cba;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        button:hover {
            background-color: #005a87;
        }
        .status {
            background: #e7f3ff;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Fix Console Errors - StealthWriter</h1>
        
        <div class="error-info">
            <h3>🔍 Current Console Errors:</h3>
            <ul class="error-list">
                <li>
                    <span class="error-type">Google Tag Manager Warning:</span>
                    <div class="error-description">Preloaded resource not used within a few seconds</div>
                </li>
                <li>
                    <span class="error-type">Font Loading Error:</span>
                    <div class="error-description">Failed to decode downloaded font</div>
                </li>
                <li>
                    <span class="error-type">Facebook Tracking Error:</span>
                    <div class="error-description">Failed to load resource: net::ERR_BLOCKED_BY_CLIENT</div>
                </li>
                <li>
                    <span class="error-type">Cookiebot Error:</span>
                    <div class="error-description">Domain not authorized to show cookie banner</div>
                </li>
            </ul>
        </div>
        
        <div class="status">
            <strong>Current Status:</strong> 
            <?php if (empty($errorFixes)): ?>
                <span style="color: #dc3545;">No error fixes applied</span>
            <?php else: ?>
                <span style="color: #28a745;">Error fixes are active</span>
            <?php endif; ?>
        </div>
        
        <form method="POST">
            <h3>🛠️ Select Error Fixes:</h3>
            
            <div class="fix-option">
                <label>
                    <input type="checkbox" name="block_gtm" value="1" <?php echo ($errorFixes['block_gtm'] ?? false) ? 'checked' : ''; ?>>
                    <strong>Block Google Tag Manager</strong>
                    <div style="font-size: 12px; color: #666; margin-left: 25px;">Prevents GTM preload warnings</div>
                </label>
            </div>
            
            <div class="fix-option">
                <label>
                    <input type="checkbox" name="block_facebook" value="1" <?php echo ($errorFixes['block_facebook'] ?? false) ? 'checked' : ''; ?>>
                    <strong>Block Facebook Tracking</strong>
                    <div style="font-size: 12px; color: #666; margin-left: 25px;">Prevents Facebook pixel errors</div>
                </label>
            </div>
            
            <div class="fix-option">
                <label>
                    <input type="checkbox" name="block_cookiebot" value="1" <?php echo ($errorFixes['block_cookiebot'] ?? false) ? 'checked' : ''; ?>>
                    <strong>Block Cookiebot</strong>
                    <div style="font-size: 12px; color: #666; margin-left: 25px;">Prevents cookie banner authorization errors</div>
                </label>
            </div>
            
            <div class="fix-option">
                <label>
                    <input type="checkbox" name="block_analytics" value="1" <?php echo ($errorFixes['block_analytics'] ?? false) ? 'checked' : ''; ?>>
                    <strong>Block Analytics Scripts</strong>
                    <div style="font-size: 12px; color: #666; margin-left: 25px;">Blocks various tracking and analytics scripts</div>
                </label>
            </div>
            
            <div class="fix-option">
                <label>
                    <input type="checkbox" name="block_fonts" value="1" <?php echo ($errorFixes['block_fonts'] ?? false) ? 'checked' : ''; ?>>
                    <strong>Block External Fonts</strong>
                    <div style="font-size: 12px; color: #666; margin-left: 25px;">Prevents font loading errors (may affect appearance)</div>
                </label>
            </div>
            
            <button type="submit" name="fix_errors">Apply Error Fixes</button>
            <a href="lifetimeupdatescript.php"><button type="button">Back to Configuration</button></a>
        </form>
        
        <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 4px;">
            <h4>💡 Additional Tips:</h4>
            <ul>
                <li>Most of these errors are harmless and don't affect functionality</li>
                <li>Facebook tracking errors are often caused by ad blockers</li>
                <li>Font errors can be ignored unless you notice display issues</li>
                <li>Cookiebot errors are related to GDPR compliance and can be safely blocked</li>
            </ul>
        </div>
    </div>
</body>
</html> 