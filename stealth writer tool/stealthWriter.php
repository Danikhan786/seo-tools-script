<?php

$allowedDomain = "localhost:";
define("LICENSE_START_DATE", value: "2025-06-03");
$expiryTimestamp = strtotime(LICENSE_START_DATE . " +1 year");
$currentHost = $_SERVER["HTTP_HOST"] ?? "";
$now = time();

include "access.php";
define("COOKIE_FILE", __DIR__ . "/cookie.json");
define("WEBSITE_URL", getTargetUrl());
$css = file_get_contents(__DIR__ . "/css/styles.css");
if (!function_exists("getallheaders")) {
    function getallheaders()
    {
        $result = [];
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) == "HTTP_") {
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
    $toolUID = "STEALTHWRITER_PROXY"; // <-- changed here
    return $dataFile[$toolUID]["proxy"]["targeturl"];
}
function initRequest($url)
{
    $response = makeRequest($url);
    $responseBody = $response["body"];
    $responseInfo = $response["responseInfo"];
    $contentType = isset($responseInfo["content_type"]) ? $responseInfo["content_type"] : "text/html";
    if (stripos($contentType, "text/html") !== false) {
        header("Content-Type: text/html");
    } elseif (stripos($contentType, "text/css") !== false) {
        header("Content-Type: text/css");
    } else {
        header("Content-Type: " . $contentType);
    }
    echo proxify($responseBody);
}
function makeRequest($url)
{
    $jsonContent = file_get_contents(COOKIE_FILE);
    $dataFile = json_decode($jsonContent, true);
    $toolUID = "STEALTHWRITER_PROXY"; // <-- changed here
    $proxyData = $dataFile[$toolUID]["proxy"];
    $cookieData = $dataFile[$toolUID]["cookie_data"];
    // Build the Cookie header
    $cookieHeader = '';
    foreach ($cookieData as $name => $value) {
        $cookieHeader .= "$name=$value; ";
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
    $browserRequestHeaders["Cookie"] = trim($cookieHeader);
    $ch = curl_init();
    curl_setopt_array(
        $ch,
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
        ]
    );
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
    curl_setopt($ch, CURLOPT_COOKIE, $cookieHeader);
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

    // Match any subdomain of stealthwriter.ai
    $domainPattern = '(?:[a-z0-9-]+\.)*stealthwriter\.ai';

    // Replace absolute URLs (http(s)://*.stealthwriter.ai)
    $result = preg_replace_callback(
        '#(src|href|action)=([\'"])(https?:)?//'.$domainPattern.'(/[^\'"]*)#i',
        function ($matches) use ($proxyHost) {
            return "{$matches[1]}={$matches[2]}http://{$proxyHost}{$matches[4]}";
        },
        $result
    );

    // Protocol-relative URLs (//*.stealthwriter.ai)
    $result = preg_replace_callback(
        '#(src|href|action)=([\'"])//'.$domainPattern.'(/[^\'"]*)#i',
        function ($matches) use ($proxyHost) {
            return "{$matches[1]}={$matches[2]}http://{$proxyHost}{$matches[3]}";
        },
        $result
    );

    // Root-relative URLs
    $result = preg_replace_callback(
        '#(src|href|action)=([\'"])/([^\'"]*)#i',
        function ($matches) use ($proxyHost) {
            return "{$matches[1]}={$matches[2]}http://{$proxyHost}/{$matches[3]}";
        },
        $result
    );

    // Inject your CSS
    $result = str_replace("</head>", "<style>" . $css . "</style></head>", $result);

    // Your existing replacements
    $result = str_replace("/logouttt", "/", $result);
    $result = str_replace("/profileee", "/", $result);
    $result = str_replace("/billingggg", "/", $result);
    $result = str_replace("(Download temporarily restricted)", "try again", $result);

    // Replace API endpoints in JS code
    $result = preg_replace(
        '#(["\'])https://(?:[a-z0-9-]+\.)*stealthwriter\.ai(/api/[^"\']*)#i',
        '$1http://' . $proxyHost . '$2',
        $result
    );

    // Replace all API endpoints in JS code (fetch, axios, etc.)
    $result = preg_replace(
        '#(https?:)?//(?:[a-z0-9-]+\.)*stealthwriter\.ai(/api/[^"\')\s]*)#i',
        'http://' . $proxyHost . '$2',
        $result
    );

    // Replace fetch("/api/...") and similar
    $result = preg_replace(
        '#fetch\((["\'])(/api/[^"\']*)#i',
        'fetch($1http://' . $proxyHost . '$2',
        $result
    );

    return $result;
}

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

// Build the target URL for any request
$targetBase = rtrim(WEBSITE_URL, '/');
$targetUrl = $targetBase . $requestUri;

// Only proxify HTML, return raw for assets
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
