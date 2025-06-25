<?php
// This is the main proxy script for stealthwriter.ai.

// --- Configuration Constants ---
// Define the path to the cookie/config file.
define("COOKIE_FILE", __DIR__ . "/cookie.json");

// Define the website URL from the cookie file. This is the target site we are proxying.
// This function must be defined before WEBSITE_URL is used.
function getTargetUrlFromCookie()
{
    // Read the content of the cookie.json file.
    $jsonContent = file_get_contents(COOKIE_FILE);
    // Decode the JSON content into a PHP associative array.
    $dataFile = json_decode($jsonContent, true);
    // Define the specific tool UID to access its configuration.
    $toolUID = "STEALTHWRITER_PROXY"; // Unique ID for this specific proxy setup
    // Return the target URL from the proxy configuration.
    return $dataFile[$toolUID]["proxy"]["targeturl"];
}

// Define the WEBSITE_URL constant by calling the function.
define("WEBSITE_URL", getTargetUrlFromCookie());

// Optional: Load custom CSS to inject into the proxied page.
// You can customize 'styles.css' in the same directory to add your own styling.
// For now, it's an empty string. You can fill this with actual CSS later if needed.
$css = ""; // file_get_contents(__DIR__ . "/css/styles.css"); // Uncomment and create css/styles.css if you want to inject custom CSS

// --- Utility Functions ---

// getallheaders() function polyfill for environments where it's not available (e.g., Nginx with PHP-FPM).
// This function retrieves all HTTP request headers sent by the client.
if (!function_exists("getallheaders")) {
    function getallheaders()
    {
        $result = [];
        // Iterate through server variables to find HTTP headers.
        foreach ($_SERVER as $key => $value) {
            // Check if the variable name starts with 'HTTP_'.
            if (substr($key, 0, 5) == "HTTP_") {
                // Convert 'HTTP_HEADER_NAME' to 'Header-Name' format.
                $key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
                $result[$key] = $value;
            } else {
                // Include other relevant server variables if they are not HTTP headers.
                // In many cases, we only care about HTTP headers for proxied requests.
                $result[$key] = $value;
            }
        }
        return $result;
    }
}

// --- Core Proxy Functions ---

/**
 * Initializes the proxy request by fetching content from the target URL
 * and sending it back to the client after modifications.
 *
 * @param string $url The full URL to fetch from the target website.
 */
function initRequest($url)
{
    // Make the actual request to the target URL using cURL.
    $response = makeRequest($url);
    $responseBody = $response["body"];
    $responseInfo = $response["responseInfo"];

    // Determine the content type from the response headers. Default to text/html.
    $contentType = isset($responseInfo["content_type"]) ? $responseInfo["content_type"] : "text/html";

    // Set the appropriate Content-Type header for the client's browser.
    if (stripos($contentType, "text/html") !== false) {
        header("Content-Type: text/html");
    } elseif (stripos($contentType, "text/css") !== false) {
        header("Content-Type: text/css");
    } else {
        // For other content types (e.g., images, JSON), send the original content type.
        header("Content-Type: " . $contentType);
    }

    // Process the response body (e.g., rewrite URLs, inject CSS) and output it.
    echo proxify($responseBody);
}

/**
 * Makes an HTTP request to the specified URL using cURL, acting as a proxy.
 * It forwards relevant headers and can use an upstream proxy.
 *
 * @param string $url The URL to request.
 * @return array An associative array containing 'headers', 'body', and 'responseInfo'.
 */
function makeRequest($url)
{
    // Load proxy and cookie data from the cookie.json file.
    $jsonContent = file_get_contents(COOKIE_FILE);
    $dataFile = json_decode($jsonContent, true);
    $toolUID = "STEALTHWRITER_PROXY"; // Ensure this matches the key in cookie.json
    $proxyData = $dataFile[$toolUID]["proxy"];
    $cookieData = $dataFile[$toolUID]["cookie_data"];

    // Build the Cookie header string from the cookie data.
    $cookieHeader = "";
    foreach ($cookieData as $key => $value) {
        $cookieHeader .= $key . "=" . $value . "; ";
    }

    // Get all headers from the incoming browser request.
    $browserRequestHeaders = getallheaders();

    // Unset headers that cURL manages automatically or should not be forwarded directly.
    unset($browserRequestHeaders["Host"]);           // cURL handles this based on CURLOPT_URL
    unset($browserRequestHeaders["Content-Length"]); // cURL sets this automatically for POST/PUT
    unset($browserRequestHeaders["Accept-Encoding"]); // Let cURL handle encoding, or set a specific one
    unset($browserRequestHeaders["Pragma"]);
    unset($browserRequestHeaders["Connection"]);     // cURL manages connection
    unset($browserRequestHeaders["Cookie"]);         // We'll set our own Cookie header

    // Set custom headers for the outgoing cURL request to mimic a browser.
    $agent = $proxyData["useragent"];
    $referer = WEBSITE_URL; // Set referrer to our proxy URL, not the original browser referrer
    $browserRequestHeaders["User-Agent"] = $agent;
    $browserRequestHeaders["Origin"] = WEBSITE_URL;
    $browserRequestHeaders["Referer"] = $referer;
    $browserRequestHeaders["Sec-Fetch-Site"] = "same-origin"; // Important for modern browser security checks
    $browserRequestHeaders["Cookie"] = $cookieHeader; // Use the cookies from cookie.json

    // Initialize cURL session.
    $ch = curl_init();

    // Set various cURL options.
    curl_setopt_array(
        $ch,
        [
            CURLOPT_URL => $url,                  // The URL to fetch
            CURLOPT_RETURNTRANSFER => true,       // Return the transfer as a string
            CURLOPT_FOLLOWLOCATION => true,       // Follow HTTP 3xx redirects
            CURLOPT_ENCODING => "",               // Handle all encodings
            CURLOPT_MAXREDIRS => 10,              // Maximum number of redirects
            CURLOPT_TIMEOUT => 3600,              // Max time in seconds for the request
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, // Use HTTP 1.1
            CURLOPT_SSL_VERIFYPEER => false,      // IMPORTANT: DO NOT USE IN PRODUCTION without proper CA certs.
                                                  // Disables SSL certificate verification, making it vulnerable to MITM attacks.
            CURLOPT_USERAGENT => $agent,          // Set the user agent
            CURLOPT_REFERER => $referer,          // Set the referrer

            // Proxy specific options, if 'ip' is provided in cookie.json
            CURLOPT_PROXY => !empty($proxyData["ip"]) ? $proxyData["ip"] : null,
            CURLOPT_PROXYPORT => !empty($proxyData["port"]) ? $proxyData["port"] : null,
            CURLOPT_PROXYUSERPWD => (!empty($proxyData["username"]) && !empty($proxyData["password"])) ? $proxyData["username"] . ":" . $proxyData["password"] : null,
        ]
    );

    // Handle different HTTP request methods (GET, POST, PUT, OPTIONS).
    switch ($_SERVER["REQUEST_METHOD"]) {
        case "GET":
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            break;
        case "POST":
            // For POST requests, add a custom header and send the raw POST data.
            $browserRequestHeaders["x-kl-ajax-request"] = "Ajax_Request"; // Example custom header
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents("php://input")); // Get raw POST data
            break;
        case "PUT":
            // For PUT requests, send the raw PUT data.
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_INFILE, fopen("php://input", "r")); // Read from input stream
            break;
        case "OPTIONS":
            // For OPTIONS requests (used for CORS pre-flight), set specific options.
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "OPTIONS");
            curl_setopt($ch, CURLOPT_VERBOSE, true); // For verbose output (debugging)
            curl_setopt($ch, CURLOPT_NOBODY, true); // Don't return the body for OPTIONS
            break;
    }

    // Convert the associative array of headers into an indexed array for cURL.
    $curlRequestHeaders = [];
    foreach ($browserRequestHeaders as $name => $value) {
        $curlRequestHeaders[] = $name . ": " . $value;
    }
    // Set the headers for the cURL request.
    curl_setopt($ch, CURLOPT_HTTPHEADER, $curlRequestHeaders);

    // Execute the cURL request.
    $response = curl_exec($ch);
    // Get information about the request (e.g., content type, status code).
    $responseInfo = curl_getinfo($ch);
    // Get the size of the header block.
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    // Extract only the response headers.
    $responseHeaders = substr($response, 0, $headerSize);
    // Close the cURL session.
    curl_close($ch);

    // Return the fetched content, headers, and response info.
    return [
        "headers" => $responseHeaders,
        "body" => $response,
        "responseInfo" => $responseInfo
    ];
}

/**
 * Modifies the fetched content (e.g., HTML, CSS, JS) to rewrite URLs
 * and inject custom CSS, ensuring links point back to the proxy.
 *
 * @param string $result The content fetched from the target website.
 * @return string The modified content.
 */
function proxify($result)
{
    global $css; // Access the custom CSS defined globally.

    // Parse the target website's URL to get its host.
    $parse = parse_url(WEBSITE_URL);
    $targetHost = $parse["host"];
    $currentProxyHost = $_SERVER["HTTP_HOST"];

    // Rewrite URLs in the content to point back to the proxy.
    // This replaces instances of the target domain with the proxy domain.
    // It handles both escaped slashes (e.g., for JSON/JS strings) and unescaped slashes.
    $result = str_replace(
        ["\\/" . $targetHost . "\\/", "/" . $targetHost],
        ["\\/" . $currentProxyHost . "\\/", "/" . $currentProxyHost],
        $result
    );

    // Optional: Inject custom CSS into the HTML <head> section.
    // This allows you to apply your own styles to the proxied content.
    if (!empty($css)) {
        $result = str_ireplace("</head>", "<style>" . $css . "</style></head>", $result);
    } else {
        // If no custom CSS is provided, you might still want to add a basic style block or nothing.
        // For stealthwriter.ai, we might need to add specific CSS later if it breaks.
    }

    // You can add more str_replace rules here if specific content needs modification.
    // For example, if stealthwriter.ai has a "Pro" button and you want to hide it:
    // $result = str_replace('<a href="/pro" class="button-pro">Pro Plan</a>', '', $result);

    return $result;
}

// --- Main execution block ---

// Construct the full URL for the request to the target website (stealthwriter.ai).
// This combines the base target URL with the specific path and query string from the browser.
$url = WEBSITE_URL . $_SERVER["REQUEST_URI"];

// Initialize and execute the proxy request.
initRequest($url);

// Output a newline for potential formatting, though not strictly necessary for web responses.
echo "\n";
?>
