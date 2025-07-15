<?php

$allowedDomain = "stealthwriter.toolsworlds.com";
define("LICENSE_START_DATE", "2025-06-03");
$expiryTimestamp = strtotime(LICENSE_START_DATE . " +1 year");
$currentHost = $_SERVER["HTTP_HOST"] ?? "";
$now = time();
if($expiryTimestamp < $now || strcasecmp($currentHost, $allowedDomain) !== 0) {
    header("Location: https://nomangraphics.org/?license=invalid");
    exit;
}
include "access.php";
define("COOKIE_FILE", __DIR__ . "/cookie.json");
define("WEBSITE_URL", getTargetUrl());
$css = file_get_contents(__DIR__ . "/css/styles.css");
if(!function_exists("getallheaders")) {
    function getallheaders()
    {
        $result = [];
        foreach ($_SERVER as $key => $value) {
            if(substr($key, 0, 5) == "HTTP_") {
                $key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
                $result[$key] = $value;
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}
function getTargetUrl()
{
    $jsonContent = file_get_contents(COOKIE_FILE);
    $dataFile = json_decode($jsonContent, true);
    $toolUID = "STEALTHWRITER_PROXY";
    return $dataFile[$toolUID]["proxy"]["targeturl"];
}
function initRequest($url)
{
    $response = makeRequest($url);
    $responseBody = $response["body"];
    $responseInfo = $response["responseInfo"];
    $contentType = isset($responseInfo["content_type"]) ? $responseInfo["content_type"] : "text/html";
    
    // Set appropriate headers
    if(stripos($contentType, "text/html") !== false) {
        header("Content-Type: text/html; charset=UTF-8");
    } elseif(stripos($contentType, "text/css") !== false) {
        header("Content-Type: text/css; charset=UTF-8");
    } elseif(stripos($contentType, "application/javascript") !== false) {
        header("Content-Type: application/javascript; charset=UTF-8");
    } else {
        header("Content-Type: " . $contentType);
    }
    
    // Add security headers
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: DENY");
    header("X-XSS-Protection: 1; mode=block");
    
    try {
        echo proxify($responseBody);
    } catch (Exception $e) {
        // Log error and return original content if proxify fails
        error_log("Proxify error: " . $e->getMessage());
        echo $responseBody;
    }
}

function makeRequest($url)
{
    $jsonContent = file_get_contents(COOKIE_FILE);
    $dataFile = json_decode($jsonContent, true);
    $toolUID = "STEALTHWRITER_PROXY";
    $proxyData = $dataFile[$toolUID]["proxy"];
    $cookieData = $dataFile[$toolUID]["cookie_data"];
    $cookieHeader = "";
    foreach ($cookieData as $key => $value) {
        $cookieHeader .= $key . "=" . $value . "; ";
    }
    $browserRequestHeaders = getallheaders();
    unset($browserRequestHeaders["Host"]);
    unset($browserRequestHeaders["Content-Length"]);
    unset($browserRequestHeaders["Accept-Encoding"]);
    unset($browserRequestHeaders["Pragma"]);
    unset($browserRequestHeaders["Connection"]);
    unset($browserRequestHeaders["Cookie"]);
    $agent = $proxyData["useragent"];
    $referer = WEBSITE_URL;
    $browserRequestHeaders["User-Agent"] = $agent;
    $browserRequestHeaders["Origin"] = WEBSITE_URL;
    $browserRequestHeaders["Referer"] = $referer;
    $browserRequestHeaders["Sec-Fetch-Site"] = "same-origin";
    $browserRequestHeaders["Cookie"] = $cookieHeader;
    $ch = curl_init();
    curl_setopt_array($ch, 
    [
        CURLOPT_URL => $url, 
        CURLOPT_RETURNTRANSFER => true, 
        CURLOPT_FOLLOWLOCATION => true, 
        CURLOPT_ENCODING => "", 
        CURLOPT_MAXREDIRS => 10, 
        CURLOPT_TIMEOUT => 3600, 
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, 
        CURLOPT_SSL_VERIFYPEER => false, 
        CURLOPT_USERAGENT => $agent, 
        CURLOPT_PROXY => $proxyData["ip"], 
        CURLOPT_PROXYPORT => $proxyData["port"], 
        CURLOPT_PROXYUSERPWD => $proxyData["username"] . ":" . $proxyData["password"], 
        CURLOPT_REFERER => $referer
    ]);
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "GET":
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            break;
        case "POST":
            $browserRequestHeaders["x-kl-ajax-request"] = "Ajax_Request";
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents("php://input"));
            break;
        case "PUT":
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_INFILE, fopen("php://input", "r"));
            break;
        case "OPTIONS":
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "OPTIONS");
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            break;
        }        
        $curlRequestHeaders = [];
        foreach ($browserRequestHeaders as $name => $value) {
            $curlRequestHeaders[] = $name . ": " . $value;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlRequestHeaders);
        $response = curl_exec($ch);
        $responseInfo = curl_getinfo($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $responseHeaders = substr($response, 0, $headerSize);
        curl_close($ch);
        return [
            "headers" => $responseHeaders, 
            "body" => $response, 
            "responseInfo" => $responseInfo
        ];
    
}

function proxify($result)
{
    global $css;
    $parse = parse_url(WEBSITE_URL);
    $host = $parse["host"];
    $proxyHost = $_SERVER["HTTP_HOST"];
    
    // Determine the correct protocol (https if the request is secure, http otherwise)
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    
    // Check if this is a JavaScript file - if so, only do minimal replacements
    $isJavaScript = (stripos($_SERVER['REQUEST_URI'], '.js') !== false) || 
                   (stripos($_SERVER['REQUEST_URI'], 'rocket-loader') !== false);

    // Match any subdomain of stealthwriter.ai
    $domainPattern = '(?:[a-z0-9-]+\.)*stealthwriter\.ai';

    // Replace absolute URLs (http(s)://*.stealthwriter.ai)
    $result = preg_replace_callback(
        '#(src|href|action)=([\'"])(https?:)?//'.$domainPattern.'(/[^\'"]*)#i',
        function ($matches) use ($proxyHost, $protocol) {
            return "{$matches[1]}={$matches[2]}{$protocol}://{$proxyHost}{$matches[4]}";
        },
        $result
    );

    // Protocol-relative URLs (//*.stealthwriter.ai)
    $result = preg_replace_callback(
        '#(src|href|action)=([\'"])//'.$domainPattern.'(/[^\'"]*)#i',
        function ($matches) use ($proxyHost, $protocol) {
            return "{$matches[1]}={$matches[2]}{$protocol}://{$proxyHost}{$matches[3]}";
        },
        $result
    );

    // Root-relative URLs
    $result = preg_replace_callback(
        '#(src|href|action)=([\'"])/([^\'"]*)#i',
        function ($matches) use ($proxyHost, $protocol) {
            return "{$matches[1]}={$matches[2]}{$protocol}://{$proxyHost}/{$matches[3]}";
        },
        $result
    );

    // For JavaScript files, only do minimal replacements to avoid breaking the code
    if ($isJavaScript) {
        // Only replace absolute URLs to stealthwriter.ai domains
        $result = preg_replace(
            '#https://(?:[a-z0-9-]+\.)*stealthwriter\.ai(/[^"\')\s;]*)#i',
            $protocol . '://' . $proxyHost . '$1',
            $result
        );
        return $result;
    }
    
    // For CSS files, only do URL replacements, don't inject our CSS
    $isCSS = (stripos($_SERVER['REQUEST_URI'], '.css') !== false);
    if ($isCSS) {
        // Only replace absolute URLs to stealthwriter.ai domains
        $result = preg_replace(
            '#https://(?:[a-z0-9-]+\.)*stealthwriter\.ai(/[^"\')\s;]*)#i',
            $protocol . '://' . $proxyHost . '$1',
            $result
        );
        return $result;
    }

    // Check if this is a login page and redirect to dashboard
    if (stripos($result, 'Login to your Account') !== false || 
        stripos($result, 'aMember Pro') !== false ||
        stripos($_SERVER['REQUEST_URI'], '/login') !== false ||
        stripos($_SERVER['REQUEST_URI'], '/auth') !== false ||
        $_SERVER['REQUEST_URI'] === '/' ||
        $_SERVER['REQUEST_URI'] === '/index.php') {
        
        // Redirect to dashboard
        header("Location: " . $protocol . "://" . $proxyHost . "/humanizer");
        exit;
    }

    // Inject CSS only (security headers are handled by .htaccess)
    $result = str_replace("</head>", "<style>" . $css . "</style></head>", $result);

    // Additional URL replacements for common patterns (more precise)
    // Only replace URLs in HTML attributes, not in JavaScript strings
    $result = preg_replace(
        '#(src|href|action)=([\'"])(https?:)?//(?:[a-z0-9-]+\.)*stealthwriter\.ai(/[^\'"]*)#i',
        '$1=$2' . $protocol . '://' . $proxyHost . '$4',
        $result
    );

    // Replace any remaining http:// references to the proxy domain
    $result = str_replace('http://' . $proxyHost, $protocol . '://' . $proxyHost, $result);

    // Your existing replacements
    $result = str_replace("/logouttt", "/", $result);
    $result = str_replace("/profileee", "/", $result);
    $result = str_replace("/billingggg", "/", $result);
    $result = str_replace("(Download temporarily restricted)", "try again", $result);

    // Replace API endpoints in JS code (more careful approach)
    // Only replace in specific contexts where we know it's safe
    $result = preg_replace(
        '#(fetch|axios\.get|axios\.post)\((["\'])https://(?:[a-z0-9-]+\.)*stealthwriter\.ai(/api/[^"\']*)#i',
        '$1($2' . $protocol . '://' . $proxyHost . '$3',
        $result
    );

    // Replace fetch("/api/...") and similar (only when it's clearly a fetch call)
    $result = preg_replace(
        '#fetch\((["\'])(/api/[^"\']*)#i',
        'fetch($1' . $protocol . '://' . $proxyHost . '$2',
        $result
    );

    $watermarkScript = '<script>
(function() {
  const sessionDuration = 30 * 60;
  let elapsedSeconds = 0;
  function formatTime(sec) {
    let h = Math.floor(sec / 3600);
    let m = Math.floor((sec % 3600) / 60);
    let s = sec % 60;
    return (
      (h < 10 ? "0" + h : h) + ":" +
      (m < 10 ? "0" + m : m) + ":" +
      (s < 10 ? "0" + s : s)
    );
  }
  function injectWatermark() {
    if (!document.getElementById("watermark")) {
      document.body.insertAdjacentHTML("beforeend", \'<div class="watermark-container" id="watermark"><h4>🚀 StealthWriter Tool</h4><p>Powered by toolbaazar.com</p><a href="https://whatsapp.com/" target="_blank">Join Our Channel For More Tools</a></div><div id="session-time">Session Time: 00:00:00 | Ends In: 00:30:00</div>\');
      // Timer
      const sessionTimeDiv = document.getElementById("session-time");
      let elapsed = elapsedSeconds;
      const timer = setInterval(() => {
        elapsed++;
        let remainingSeconds = sessionDuration - elapsed;
        if (remainingSeconds <= 0) {
          clearInterval(timer);
          sessionTimeDiv.textContent = "Session Ended - Refresh to Continue";
          return;
        }
        sessionTimeDiv.textContent =
          "Session Time: " + formatTime(elapsed) +
          " | Ends In: " + formatTime(remainingSeconds);
      }, 1000);
      // WhatsApp click
      const watermark = document.getElementById("watermark");
      const whatsappLink = "https://whatsapp.com/";
      watermark.addEventListener("click", () => {
        window.open(whatsappLink, "_blank");
      });
    }
  }
  // Observe DOM changes and always re-inject watermark
  const observer = new MutationObserver(injectWatermark);
  observer.observe(document.body, { childList: true, subtree: true });
  injectWatermark();
})();
</script>';

    // Inject watermark HTML and script before </body> or at the end if </body> is missing
    if (stripos($result, '</body>') !== false) {
        $result = str_ireplace('</body>', $watermarkScript . '</body>', $result);
    } else {
        $result .= $watermarkScript;
    }
    return $result;
}

?> 