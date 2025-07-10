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
        "ABUBAKKARAHMAD" => array(
            "tool_name" => "ABUBAKKARAHMAD",
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
    <title>Update</title>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #fcf0f3;
        }
        
        h1 {
            color: #fff;
            background-color: #f20f4b;
            padding: 10px;
            border-radius: 0px;
            width: 1030px;
            margin-left: -41px;
            box-shadow: 0 0 17px 2px rgb(229 54 111 / 87%);
            padding-left: 37px;
        }
        
        form {
            margin-top: 20px;
        }
        
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            font-size: 16px;
            border: 1px solid #f20f4b;
            border-radius: 5px;
            box-shadow: 0 0 17px 2px rgb(229 54 111 / 25%);
        }
        
        input[type="submit"] {
            background-color: #f20f4b;
            color: #fff;
            border: none;
            padding: 12px;
            margin-bottom: 5px;
            margin-top: 20px;
            margin-right: 20px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
            font-size: 25px;
            font-weight: bold;
            text-align: left;
            position: relative;
            padding-left: 10px;
            box-shadow: 0 0 17px 2px rgb(229 54 111 / 87%);
        }
        
        input[type="submit"]:hover {
            background-color: #b1002b;
            color: #fff;
        }
        
        .notification {
            background-color: #008000;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            color: white;
            padding: 15px;
            margin-top: 10px;
            display: <?php echo isset($notification) ? 'inline-block' : 'none'; ?>;
        }
        
        label {
            font-size: 16px;
            font-weight: bold;
            color: white;
            background-color: #f20f4b;
            padding: 5px;
            border: 2px solid #f20f4b;
            border-radius: 5px;
            display: inline-block;
            margin-bottom: 1px;
            margin-top: 30px;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            font-size: 16px;
            border: 1px solid #f20f4b;
            border-radius: 5px;
            box-shadow: 0 0 17px 2px rgb(229 54 111 / 25%);
        }
        
        #clear-cookies-button {
            background-color: #f20f4b;
            color: #fff;
            border: none;
            font-weight: bold;
            padding: 10px 15px;
            margin-top: 10px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        #clear-cookies-button:hover {
            background-color: #b1002b;
            color: #fff;
        }
        
        .dropdown {
            cursor: pointer;
            padding: 10px;
            border: 1px solid #f20f4b;
            border-radius: 5px;
            margin-top: 20px;
            background-color: #f20f4b;
            color: #fff;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .dropdown:hover {
            background-color: #b1002b;
        }
        
        .dropdown-content {
            display: none;
            margin-top: 10px;
        }
    
        .menu-container {
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100px;
            margin-bottom: 50px;
        }

        .navtabs {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 10px 20px;
            position: relative;
        }

        button#user-data-link,
        button#update-tools-link  {
            background-color: #f20f4b;
            color: #fff;
            border: none;
            padding: 10px;
            margin-bottom: 5px;
            margin-top: 10px;
            margin-right: 20px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
            font-size: 25px;
            font-weight: bold;
            text-align: left;
            position: relative;
            padding-left: 10px;
            box-shadow: 0 0 17px 2px rgb(229 54 111 / 87%);
        }

        .cookies-container {
            margin-top: 20px;
        }

        .cookie-button {
            background-color: #f20f4b;
            color: #fff;
            border: none;
            padding: 10px 10px;
            margin-bottom: 5px;
            margin-right: 10px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
            font-size: 16px;
            font-weight: bold;
        }

        .cookie-button:hover {
            background-color: #b1002b;
        }

        .edit-icon {
            cursor: pointer;
            margin-left: 20px;
            color: #fcf0f3;
        }

        .edit-icon:hover {
            color: #fcf0f3;
        }
        
        button {
    background-color: #f20f4b;
    color: #fff;
    border: none;
    padding: 10px 10px;
    margin-bottom: 5px;
    margin-right: 10px;
    cursor: pointer;
    border-radius: 5px;
    transition: background-color 0.3s;
    font-size: 16px;
    font-weight: bold;
}

.ui-dialog-titlebar.ui-corner-all.ui-widget-header.ui-helper-clearfix.ui-draggable-handle {
    font-size: 16px;
    font-weight: bold;
    color: white;
    background-color: #f20f4b;
    border: 2px solid #f20f4b;
    border-radius: 5px;
}

button.ui-button.ui-corner-all.ui-widget {
    background-color: #f20f4b;
    color: #fff;
    border: none;
    padding: 10px 10px;
    margin-bottom: 5px;
    margin-right: 10px;
    cursor: pointer;
    border-radius: 5px;
    transition: background-color 0.3s;
    font-size: 16px;
    font-weight: bold;
}
    </style>
</head>
<body>
    
    <?php
    $domainName = $_SERVER['HTTP_HOST']; // Fetch the domain name
    echo "<h1>Update - " . htmlspecialchars($domainName) . "</h1>";
    ?>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <div class="cookies-container">
            <h3>Here you can add cookies files with different cookies stored</h3>
            <button type="button" class="cookie-button" onclick="openAddCookiePopup()">Add Cookies Button</button><br>
            <h3>Here is list of cookies button click and add that cookies and on edit icon you can edit it</h3>
            <?php
            foreach ($cookieFiles as $file) {
                $buttonName = basename($file, '.json');
                echo "<button type='button' class='cookie-button' onclick='loadCookieFile(\"$buttonName\")'>$buttonName <span class='edit-icon' onclick='editCookieFile(\"$buttonName\")'>|| &#9998;</span></button>";
            }
            ?>
        </div>
        
        <label for="target_url">Target URL:</label>
        <input type="text" id="target_url" name="target_url" value="<?php echo isset($currentDataJson['ABUBAKKARAHMAD']['proxy']['targeturl']) ? $currentDataJson['ABUBAKKARAHMAD']['proxy']['targeturl'] : ''; ?>"><br>
        <label for="base_url">Base URL:</label>
        <input type="text" id="base_url" name="base_url" value="<?php echo isset($currentDataJson['ABUBAKKARAHMAD']['proxy']['baseurl']) ? $currentDataJson['ABUBAKKARAHMAD']['proxy']['baseurl'] : ''; ?>"><br>
        <label for="cookies_data">Cookies Data:</label>
        <textarea id="cookies_data" rows="4" name="cookies_data" placeholder="Enter JSON formatted cookies here" required><?php echo htmlspecialchars(file_get_contents('unfiltercookies.json')); ?></textarea><br><br>
        <button id="clear-cookies-button" type="button">Clear Cookies</button><br>
        
        <div class="dropdown" onclick="toggleDropdown('proxy-section')">Proxy Section</div>
        <div id="proxy-section" class="dropdown-content">
            <label for="ip">IP:</label>
            <input type="text" id="ip" name="ip" value="<?php echo isset($currentDataJson['ABUBAKKARAHMAD']['proxy']['ip']) ? $currentDataJson['ABUBAKKARAHMAD']['proxy']['ip'] : ''; ?>"><br>
            <label for="port">Port:</label>
            <input type="text" id="port" name="port" value="<?php echo isset($currentDataJson['ABUBAKKARAHMAD']['proxy']['port']) ? $currentDataJson['ABUBAKKARAHMAD']['proxy']['port'] : ''; ?>"><br>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo isset($currentDataJson['ABUBAKKARAHMAD']['proxy']['username']) ? $currentDataJson['ABUBAKKARAHMAD']['proxy']['username'] : ''; ?>"><br>
            <label for="password">Password:</label>
            <input type="text" id="password" name="password" value="<?php echo isset($currentDataJson['ABUBAKKARAHMAD']['proxy']['password']) ? $currentDataJson['ABUBAKKARAHMAD']['proxy']['password'] : ''; ?>"><br>
            <label for="user_agent">User Agent:</label>
            <input type="text" id="user_agent" name="user_agent" value="<?php echo isset($currentDataJson['ABUBAKKARAHMAD']['proxy']['useragent']) ? $currentDataJson['ABUBAKKARAHMAD']['proxy']['useragent'] : ''; ?>"><br>
        </div>
        
        <div class="dropdown" onclick="toggleDropdown('secure-section')">Secure Section</div>
        <div id="secure-section" class="dropdown-content">
            <label for="noaccess_url">No Access URL:</label>
            <input type="text" id="noaccess_url" name="noaccess_url" value="<?php echo isset($currentDataJson['ABUBAKKARAHMAD']['secure']['noaccessurl']) ? $currentDataJson['ABUBAKKARAHMAD']['secure']['noaccessurl'] : ''; ?>"><br>
            <label for="urlafteraccess">URL After Access:</label>
            <input type="text" id="urlafteraccess" name="urlafteraccess" value="<?php echo isset($currentDataJson['ABUBAKKARAHMAD']['secure']['urlafteraccess']) ? $currentDataJson['ABUBAKKARAHMAD']['secure']['urlafteraccess'] : ''; ?>"><br>
            <label for="block_ips">Block IPs:</label>
            <input type="text" id="block_ips" name="block_ips" value="<?php echo isset($currentDataJson['ABUBAKKARAHMAD']['secure']['blockips']) ? $currentDataJson['ABUBAKKARAHMAD']['secure']['blockips'] : ''; ?>"><br>
            <label for="download_limit">Download Limit:</label>
            <input type="text" id="download_limit" name="download_limit" value="<?php echo isset($currentDataJson['ABUBAKKARAHMAD']['secure']['downloadlimit']) ? $currentDataJson['ABUBAKKARAHMAD']['secure']['downloadlimit'] : ''; ?>"><br>
             <label for="sessiontime">Session Time add time in seconds:</label>
<input type="text" id="sessiontime" name="sessiontime" value="<?php echo isset($currentDataJson['ABUBAKKARAHMAD']['secure']['sessiontime']) ? $currentDataJson['ABUBAKKARAHMAD']['secure']['sessiontime'] : ''; ?>"><br>
            <label for="products_id">Products ID:</label>
            <input type="text" id="products_id" name="products_id" value="<?php echo isset($currentDataJson['ABUBAKKARAHMAD']['secure']['productsid']) ? $currentDataJson['ABUBAKKARAHMAD']['secure']['productsid'] : ''; ?>"><br>
            <label for="page_name">Page Name:</label>
            <input type="text" id="page_name" name="page_name" value="<?php echo isset($currentDataJson['ABUBAKKARAHMAD']['secure']['pagename']) ? $currentDataJson['ABUBAKKARAHMAD']['secure']['pagename'] : ''; ?>"><br>
           

        </div>
        
        <div id="notification" class="notification">Data updated successfully</div>
        <input type="submit" value="Save">
    </form>

    <!-- Popup for adding/editing cookies -->
    <div id="cookie-popup" title="Add/Edit Cookies" style="display:none;">
        <form id="cookie-popup-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="button_name">Button Name:</label>
            <input type="text" id="button_name" name="button_name" required><br>
            <label for="popup_cookies_data">Cookies Data:</label>
            <textarea id="popup_cookies_data" name="popup_cookies_data" rows="4" required></textarea><br>
            <input type="hidden" name="save_cookies" value="1">
            <button type="submit">Save</button>
        </form>
        <button id="delete-cookie-button" style="display:none;" onclick="deleteCookieFile()">Delete</button>
    </div>

    <script>
        // Function to toggle the visibility of dropdown content
        function toggleDropdown(id) {
            var section = document.getElementById(id);
            if (section.style.display === "none" || section.style.display === "") {
                section.style.display = "block";
            } else {
                section.style.display = "none";
            }
        }
        
        // Add an event listener to the "Clear Cookies" button
        const clearCookiesButton = document.getElementById('clear-cookies-button');
        const cookiesDataTextarea = document.getElementById('cookies_data');

        clearCookiesButton.addEventListener('click', function() {
            cookiesDataTextarea.value = '';
        });

        // JavaScript for showing notification
        document.addEventListener('DOMContentLoaded', function() {
            var notification = document.getElementById("notification");
            if (notification) {
                notification.style.display = "none";
            }
        });

        document.addEventListener('submit', function() {
            var notification = document.getElementById("notification");
            if (notification) {
                notification.style.display = "block";
                setTimeout(function() {
                    notification.style.display = "none";
                }, 3000);
            }
        });

        // Function to open the add/edit cookie popup
        function openAddCookiePopup() {
            document.getElementById('button_name').value = '';
            document.getElementById('popup_cookies_data').value = '';
            document.getElementById('cookie-popup-form').elements['save_cookies'].value = 1;
            document.getElementById('delete-cookie-button').style.display = 'none';
            $('#cookie-popup').dialog('open');
        }

        function editCookieFile(buttonName) {
            const popupForm = document.getElementById('cookie-popup-form');
            const cookieData = loadCookieFileContent(buttonName);
            document.getElementById('button_name').value = buttonName;
            document.getElementById('popup_cookies_data').value = cookieData;
            popupForm.elements['save_cookies'].value = 0;
            document.getElementById('delete-cookie-button').style.display = 'inline';
            $('#cookie-popup').dialog('open');
        }

        function loadCookieFile(buttonName) {
            const cookieData = loadCookieFileContent(buttonName);
            document.getElementById('cookies_data').value = cookieData;
        }

        function loadCookieFileContent(buttonName) {
            let cookieData = '';
            $.ajax({
                url: '<?php echo $_SERVER["PHP_SELF"]; ?>',
                type: 'POST',
                async: false,
                data: { get_cookie_file: 1, button_name: buttonName },
                success: function(data) {
                    cookieData = data;
                }
            });
            return cookieData;
        }

        function deleteCookieFile() {
            const buttonName = document.getElementById('button_name').value;
            $.ajax({
                url: '<?php echo $_SERVER["PHP_SELF"]; ?>',
                type: 'POST',
                async: false,
                data: { delete_cookies: 1, button_name: buttonName },
                success: function() {
                    location.reload();
                }
            });
        }

        $(function() {
            $('#cookie-popup').dialog({
                autoOpen: false,
                modal: true,
                width: 400,
                buttons: {
                    "Save": function() {
                        $('#cookie-popup-form').submit();
                    }
                }
            });
        });
    </script>
</body>
</html>
