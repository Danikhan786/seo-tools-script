<?php
session_start();

// Function to get data from cookie.json
function getDataFromJson() {
    $jsonFilePath = 'cookie.json';
    if (!file_exists($jsonFilePath)) {
        error_log("cookie.json file does not exist");
        return null;
    }
    
    $jsonData = file_get_contents($jsonFilePath);
    if ($jsonData === false) {
        error_log("Failed to read cookie.json");
        return null;
    }

    $data = json_decode($jsonData, true);
    if ($data === null) {
        error_log("Failed to decode JSON data: " . json_last_error_msg());
    }
    return $data;
}

// Function to update .htaccess file with blocked IPs
function updateHtaccess($blockedIps) {
    $htaccessFile = '.htaccess';
    $htaccessContent = file_get_contents($htaccessFile);
    if ($htaccessContent === false) {
        error_log("Failed to read .htaccess file");
        return false;
    }

    // Prepare the new deny lines
    $blockedIpsArray = array_map('trim', explode(',', $blockedIps));
    $denyLines = '';
    foreach ($blockedIpsArray as $ip) {
        if (!empty($ip)) {
            $denyLines .= "deny from " . $ip . "\n";
        }
    }

    // Replace the placeholder section with new deny lines
    $newHtaccessContent = preg_replace(
        '/# BLOCKED IPS START.*# BLOCKED IPS END/s',
        "# BLOCKED IPS START\n$denyLines# BLOCKED IPS END",
        $htaccessContent
    );

    // Write the updated content back to .htaccess
    if (file_put_contents($htaccessFile, $newHtaccessContent) === false) {
        error_log("Failed to write to .htaccess file");
        return false;
    }

    return true;
}

// Function to update cookie.json file with all cookies and proxy information
function updateDataJsonFile($cookiesData, $ip, $port, $username, $password, $userAgent, $targetUrl, $baseUrl, $noAccessUrl, $urlAfterAccess, $blockIps, $downloadLimit, $productsId, $pageName, $sessionTime) {
    $cookies = json_decode($cookiesData, true);
    $filteredCookies = array();

    // Include all cookies without filtering
    foreach ($cookies as $cookie) {
        $filteredCookies[$cookie['name']] = $cookie['value'];
    }

    // Remove slashes from URLs and user agent
    $targetUrl = stripslashes($targetUrl);
    $baseUrl = stripslashes($baseUrl);
    $userAgent = stripslashes($userAgent);
    $noAccessUrl = stripslashes($noAccessUrl);
    $urlAfterAccess = stripslashes($urlAfterAccess);

    $data = array(
        "WRITERHUMAN_PROXY" => array(
            "tool_name" => "WRITERHUMAN_PROXY",
            "cookie_data" => $filteredCookies,
            "proxy" => array(
                "ip" => $ip,
                "port" => $port,
                "username" => $username,
                "password" => $password,
                "useragent" => $userAgent,
                "targeturl" => $targetUrl,
                "baseurl" => $baseUrl
            ),
            "secure" => array(
                "noaccessurl" => $noAccessUrl,
                "urlafteraccess" => $urlAfterAccess,
                "blockips" => $blockIps,
                "downloadlimit" => $downloadLimit,
                "productsid" => $productsId,
                "pagename" => $pageName,
                "sessiontime" => $sessionTime
            )
        )
    );

    // Encode URLs and user agent before writing to JSON
    $data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    
    file_put_contents('cookie.json', $data);

    // Update .htaccess file with the blocked IPs
    if (!updateHtaccess($blockIps)) {
        die('Failed to update .htaccess file');
    }
}

// Function to save unfiltered cookies to unfiltercookies.json file
function saveUnfilteredCookies($cookiesData) {
    file_put_contents('unfiltercookies.json', $cookiesData);
}

// Function to get current data from cookie.json file
function getCurrentDataJson() {
    $dataJson = file_get_contents('cookie.json');
    return json_decode($dataJson, true);
}

// Function to get list of saved cookie files
function getCookieFiles() {
    $files = glob('cookies/*.json');
    return array_map('basename', $files);
}

// Function to get the content of a cookie file
function getCookieFileContent($filename) {
    $filepath = 'cookies/' . $filename;
    if (file_exists($filepath)) {
        return file_get_contents($filepath);
    }
    return '';
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['target_url'])) {
        $targetUrl = $_POST['target_url'];
        $baseUrl = $_POST['base_url'];
        $cookiesData = $_POST['cookies_data'];
        $ip = $_POST['ip'];
        $port = $_POST['port'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $userAgent = $_POST['user_agent'];
        $noAccessUrl = $_POST['noaccess_url'];
        $urlAfterAccess = $_POST['urlafteraccess'];
        $blockIps = $_POST['block_ips'];
        $downloadLimit = $_POST['download_limit'];
        $productsId = $_POST['products_id'];
        $pageName = $_POST['page_name'];
        $sessionTime = $_POST['sessiontime'];

        updateDataJsonFile($cookiesData, $ip, $port, $username, $password, $userAgent, $targetUrl, $baseUrl, $noAccessUrl, $urlAfterAccess, $blockIps, $downloadLimit, $productsId, $pageName, $sessionTime);
        saveUnfilteredCookies($cookiesData);

        echo '<script>';
        echo 'document.getElementById("notification").style.display = "block";';
        echo 'setTimeout(function() { document.getElementById("notification").style.display = "none"; }, 3000);';
        echo '</script>';
    } elseif (isset($_POST['save_cookies'])) {
        $buttonName = $_POST['button_name'];
        $cookiesData = $_POST['popup_cookies_data'];
        if (!file_exists('cookies')) {
            mkdir('cookies', 0777, true);
        }
        file_put_contents('cookies/' . $buttonName . '.json', $cookiesData);
    } elseif (isset($_POST['edit_cookies'])) {
        $buttonName = $_POST['button_name'];
        $cookiesData = $_POST['popup_cookies_data'];
        file_put_contents('cookies/' . $buttonName . '.json', $cookiesData);
    } elseif (isset($_POST['delete_cookies'])) {
        $buttonName = $_POST['button_name'];
        unlink('cookies/' . $buttonName . '.json');
    } elseif (isset($_POST['get_cookie_file'])) {
        $buttonName = $_POST['button_name'];
        echo getCookieFileContent($buttonName . '.json');
        exit;
    }
}

$currentDataJson = getCurrentDataJson();
$cookieFiles = getCookieFiles();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WriteHuman Tool Update</title>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
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
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="number"], textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        textarea {
            height: 100px;
            resize: vertical;
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
        .notification {
            display: none;
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .cookie-files {
            margin-top: 30px;
        }
        .cookie-file {
            background: #f8f9fa;
            padding: 10px;
            margin: 5px 0;
            border-radius: 4px;
            cursor: pointer;
        }
        .cookie-file:hover {
            background: #e9ecef;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>WriteHuman Tool Configuration</h1>
        
        <div id="notification" class="notification">
            Configuration updated successfully!
        </div>

        <form method="POST">
            <div class="form-group">
                <label for="target_url">Target URL:</label>
                <input type="text" id="target_url" name="target_url" value="<?php echo isset($currentDataJson['WRITERHUMAN_PROXY']['proxy']['targeturl']) ? htmlspecialchars($currentDataJson['WRITERHUMAN_PROXY']['proxy']['targeturl']) : 'https://writehuman.ai'; ?>" required>
            </div>

            <div class="form-group">
                <label for="base_url">Base URL:</label>
                <input type="text" id="base_url" name="base_url" value="<?php echo isset($currentDataJson['WRITERHUMAN_PROXY']['proxy']['baseurl']) ? htmlspecialchars($currentDataJson['WRITERHUMAN_PROXY']['proxy']['baseurl']) : 'https://writehuman.ai'; ?>" required>
            </div>

            <div class="form-group">
                <label for="cookies_data">Cookies Data (JSON):</label>
                <textarea id="cookies_data" name="cookies_data" placeholder="Paste your cookies JSON data here..." required></textarea>
            </div>

            <div class="form-group">
                <label for="ip">Proxy IP:</label>
                <input type="text" id="ip" name="ip" value="<?php echo isset($currentDataJson['WRITERHUMAN_PROXY']['proxy']['ip']) ? htmlspecialchars($currentDataJson['WRITERHUMAN_PROXY']['proxy']['ip']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="port">Proxy Port:</label>
                <input type="number" id="port" name="port" value="<?php echo isset($currentDataJson['WRITERHUMAN_PROXY']['proxy']['port']) ? htmlspecialchars($currentDataJson['WRITERHUMAN_PROXY']['proxy']['port']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="username">Proxy Username:</label>
                <input type="text" id="username" name="username" value="<?php echo isset($currentDataJson['WRITERHUMAN_PROXY']['proxy']['username']) ? htmlspecialchars($currentDataJson['WRITERHUMAN_PROXY']['proxy']['username']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Proxy Password:</label>
                <input type="text" id="password" name="password" value="<?php echo isset($currentDataJson['WRITERHUMAN_PROXY']['proxy']['password']) ? htmlspecialchars($currentDataJson['WRITERHUMAN_PROXY']['proxy']['password']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="user_agent">User Agent:</label>
                <input type="text" id="user_agent" name="user_agent" value="<?php echo isset($currentDataJson['WRITERHUMAN_PROXY']['proxy']['useragent']) ? htmlspecialchars($currentDataJson['WRITERHUMAN_PROXY']['proxy']['useragent']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="noaccess_url">No Access URL:</label>
                <input type="text" id="noaccess_url" name="noaccess_url" value="<?php echo isset($currentDataJson['WRITERHUMAN_PROXY']['secure']['noaccessurl']) ? htmlspecialchars($currentDataJson['WRITERHUMAN_PROXY']['secure']['noaccessurl']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="urlafteraccess">URL After Access:</label>
                <input type="text" id="urlafteraccess" name="urlafteraccess" value="<?php echo isset($currentDataJson['WRITERHUMAN_PROXY']['secure']['urlafteraccess']) ? htmlspecialchars($currentDataJson['WRITERHUMAN_PROXY']['secure']['urlafteraccess']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="block_ips">Block IPs (comma-separated):</label>
                <input type="text" id="block_ips" name="block_ips" value="<?php echo isset($currentDataJson['WRITERHUMAN_PROXY']['secure']['blockips']) ? htmlspecialchars($currentDataJson['WRITERHUMAN_PROXY']['secure']['blockips']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="download_limit">Download Limit:</label>
                <input type="number" id="download_limit" name="download_limit" value="<?php echo isset($currentDataJson['WRITERHUMAN_PROXY']['secure']['downloadlimit']) ? htmlspecialchars($currentDataJson['WRITERHUMAN_PROXY']['secure']['downloadlimit']) : '100'; ?>">
            </div>

            <div class="form-group">
                <label for="products_id">Products ID:</label>
                <input type="text" id="products_id" name="products_id" value="<?php echo isset($currentDataJson['WRITERHUMAN_PROXY']['secure']['productsid']) ? htmlspecialchars($currentDataJson['WRITERHUMAN_PROXY']['secure']['productsid']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="page_name">Page Name:</label>
                <input type="text" id="page_name" name="page_name" value="<?php echo isset($currentDataJson['WRITERHUMAN_PROXY']['secure']['pagename']) ? htmlspecialchars($currentDataJson['WRITERHUMAN_PROXY']['secure']['pagename']) : 'writehuman'; ?>">
            </div>

            <div class="form-group">
                <label for="sessiontime">Session Time (seconds):</label>
                <input type="number" id="sessiontime" name="sessiontime" value="<?php echo isset($currentDataJson['WRITERHUMAN_PROXY']['secure']['sessiontime']) ? htmlspecialchars($currentDataJson['WRITERHUMAN_PROXY']['secure']['sessiontime']) : '1800'; ?>">
            </div>

            <button type="submit">Update Configuration</button>
        </form>

        <div class="cookie-files">
            <h3>Saved Cookie Files</h3>
            <?php foreach ($cookieFiles as $file): ?>
                <div class="cookie-file" onclick="loadCookieFile('<?php echo $file; ?>')">
                    <?php echo htmlspecialchars($file); ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function loadCookieFile(filename) {
            $.post('', {get_cookie_file: true, button_name: filename.replace('.json', '')}, function(data) {
                $('#cookies_data').val(data);
            });
        }
    </script>
</body>
</html> 