<?php
// Simple WriterHuman.ai Proxy for Local Server
define("COOKIE_FILE", __DIR__ . "/cookie.json");
define("WEBSITE_URL", "https://writehuman.ai/");

// Load CSS with error handling
$css = '';
if (file_exists(__DIR__ . "/css/styles.css")) {
    $css = file_get_contents(__DIR__ . "/css/styles.css");
}

if (!function_exists("getallheaders")) {
    function getallheaders() {
        $result = [];
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) == "HTTP_") {
                $key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
                $result[$key] = $value;
            }
        }
        return $result;
    }
}

function makeRequest($url) {
    // Check if cookie file exists
    if (!file_exists(COOKIE_FILE)) {
        header("Location: error.php?title=Cookie File Missing&error=Cookie file not found. Please check your configuration.");
        exit;
    }
    
    $jsonContent = file_get_contents(COOKIE_FILE);
    $dataFile = json_decode($jsonContent, true);
    
    if (!$dataFile || !isset($dataFile["WRITERHUMAN_PROXY"])) {
        header("Location: error.php?title=Invalid Configuration&error=Invalid cookie configuration. Please check your cookie.json file.");
        exit;
    }
    
    $cookieData = $dataFile["WRITERHUMAN_PROXY"]["cookie_data"];
    
    // Build cookie header
    $cookieHeader = '';
    foreach ($cookieData as $name => $value) {
        $cookieHeader .= "$name=$value; ";
    }
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/127.0.0.0 Safari/537.36',
        CURLOPT_REFERER => WEBSITE_URL,
        CURLOPT_COOKIE => trim($cookieHeader),
        CURLOPT_HTTPHEADER => [
            'Origin: ' . WEBSITE_URL,
            'Sec-Fetch-Site: same-origin'
        ]
    ]);
    
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents("php://input"));
    }
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $responseInfo = curl_getinfo($ch);
    curl_close($ch);
    
    if ($error) {
        header("Location: error.php?title=Connection Error&error=" . urlencode("Connection error: " . $error));
        exit;
    }
    
    return [
        "body" => $response,
        "responseInfo" => $responseInfo
    ];
}

function proxify($result) {
    global $css;
    $proxyHost = $_SERVER["HTTP_HOST"];
    $domainPattern = '(?:[a-z0-9-]+\.)*writehuman\.ai';

    // Keep CDN resources pointing to original sources
    $result = preg_replace(
        '#(src|href)=([\'"])http://' . preg_quote($proxyHost) . '/([a-z0-9-]+\.cdn\.bubble\.io/[^\'"]*)#i',
        '$1=$2https://$3',
        $result
    );

    // Keep package resources pointing to original WriteHuman.ai
    $result = preg_replace(
        '#(src|href)=([\'"])http://' . preg_quote($proxyHost) . '(/package/[^\'"]*)#i',
        '$1=$2https://writehuman.ai$3',
        $result
    );

    // Keep API resources pointing to original WriteHuman.ai
    $result = preg_replace(
        '#(src|href)=([\'"])http://' . preg_quote($proxyHost) . '(/api/[^\'"]*)#i',
        '$1=$2https://writehuman.ai$3',
        $result
    );

    // Replace absolute URLs for main site pages only
    $result = preg_replace_callback(
        '#(src|href|action)=([\'"])(https?:)?//'.$domainPattern.'(/[^\'"]*)#i',
        function ($matches) use ($proxyHost) {
            $path = $matches[4];
            // Don't proxy CDN, package, or API resources
            if (strpos($path, '/cdn.') !== false || strpos($path, '/package/') === 0 || strpos($path, '/api/') === 0) {
                return $matches[0]; // Keep original
            }
            return "{$matches[1]}={$matches[2]}http://{$proxyHost}{$path}";
        },
        $result
    );

    // Replace protocol-relative URLs for main site pages only
    $result = preg_replace_callback(
        '#(src|href|action)=([\'"])//'.$domainPattern.'(/[^\'"]*)#i',
        function ($matches) use ($proxyHost) {
            $path = $matches[3];
            // Don't proxy CDN, package, or API resources
            if (strpos($path, '/cdn.') !== false || strpos($path, '/package/') === 0 || strpos($path, '/api/') === 0) {
                return $matches[0]; // Keep original
            }
            return "{$matches[1]}={$matches[2]}http://{$proxyHost}{$path}";
        },
        $result
    );

    // Replace root-relative URLs (but not for CDN/package/api)
    $result = preg_replace_callback(
        '#(src|href|action)=([\'"])/([^\'"]*)#i',
        function ($matches) use ($proxyHost) {
            $path = $matches[3];
            // Don't proxy CDN, package, or API resources
            if (strpos($path, 'cdn.') !== false || strpos($path, 'package/') === 0 || strpos($path, 'api/') === 0) {
                return "{$matches[1]}={$matches[2]}https://writehuman.ai/{$path}";
            }
            return "{$matches[1]}={$matches[2]}http://{$proxyHost}/{$path}";
        },
        $result
    );

    // Fix JavaScript fetch calls to API
    $result = preg_replace(
        '#fetch\((["\'])http://' . preg_quote($proxyHost) . '(/api/[^"\']*)#i',
        'fetch($1https://writehuman.ai$2',
        $result
    );

    // Fix JavaScript XMLHttpRequest calls
    $result = preg_replace(
        '#open\((["\'])(GET|POST|PUT|DELETE)(["\']),(["\'])http://' . preg_quote($proxyHost) . '(/api/[^"\']*)#i',
        'open($1$2$3,$4https://writehuman.ai$5',
        $result
    );

    // Inject CSS if available
    if (!empty($css)) {
        $result = str_replace("</head>", "<style>" . $css . "</style></head>", $result);
    }

    // Inject JavaScript fixes
    $jsFix = file_get_contents(__DIR__ . "/js-fix.js");
    if ($jsFix) {
        $result = str_replace("</head>", "<script>" . $jsFix . "</script></head>", $result);
    }

    // Add watermark
    $watermarkHtml = <<<HTML
<div class="watermark-container" id="watermark">
    <h4>WriterHuman Tool</h4>
    <p>Powered by Local Server</p>
</div>
HTML;

    $watermarkScript = <<<HTML
<script>
(function() {
    function injectWatermark() {
        if (!document.getElementById('watermark')) {
            document.body.insertAdjacentHTML('beforeend', `$watermarkHtml`);
        }
    }
    
    const observer = new MutationObserver(injectWatermark);
    observer.observe(document.body, { childList: true, subtree: true });
    injectWatermark();
})();
</script>
HTML;

    if (stripos($result, '</body>') !== false) {
        $result = str_ireplace('</body>', $watermarkScript . '</body>', $result);
    } else {
        $result .= $watermarkScript;
    }
    
    return $result;
}

// Main execution
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

// Handle special cases for WriteHuman.ai resources
if (strpos($requestUri, '/package/') === 0 || strpos($requestUri, '/api/') === 0) {
    // These are internal WriteHuman.ai resources, proxy them directly
    $targetUrl = WEBSITE_URL . ltrim($requestUri, '/');
    $response = makeRequest($targetUrl);
    $contentType = $response['responseInfo']['content_type'] ?? 'application/octet-stream';
    header('Content-Type: ' . $contentType);
    echo $response['body'];
    exit;
}

// Handle CDN resources - redirect to original
if (strpos($requestUri, '/cdn.') !== false || preg_match('/\/[a-z0-9-]+\.cdn\.bubble\.io\//', $requestUri)) {
    $targetUrl = 'https://writehuman.ai' . $requestUri;
    header('Location: ' . $targetUrl);
    exit;
}

// Main proxy logic for HTML pages
$targetUrl = WEBSITE_URL . ltrim($requestUri, '/');
$response = makeRequest($targetUrl);
$contentType = $response['responseInfo']['content_type'] ?? 'text/html';

if (stripos($contentType, 'text/html') !== false) {
    header('Content-Type: text/html');
    echo proxify($response['body']);
} else {
    header('Content-Type: ' . $contentType);
    echo $response['body'];
}
exit;
