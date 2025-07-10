<?php

$secret_key = "abubakkarmedia";
$toolData = getToolData();
$toolName = "WRITERHUMAN_PROXY";
if(!isset($toolData[$toolName])) {
    logmessage("Tool data for " . $toolName . " not found.");
    exit("Tool data for " . $toolName . " not found.");
}
$noAccessUrl = $toolData[$toolName]["secure"]["noaccessurl"];
$urlAfterAccess = $toolData[$toolName]["secure"]["urlafteraccess"];
$sessionTime = $toolData[$toolName]["secure"]["sessiontime"];
$token = $_GET["token"] ?? NULL;
$hmac = $_GET["hmac"] ?? NULL;
if(!preg_match("/^[a-zA-Z0-9]+\$/", $token) || !preg_match("/^[a-f0-9]{64}\$/", $hmac)) {
    logmessage("Invalid token or HMAC format.");
    header("Location: /sessionexpired.php");
    exit;
}
if($token && $hmac) {
    if(strlen($token) === 30 && hash_hmac("sha256", $token, $secret_key) === $hmac) {
        if(savetoken($token)) {
            setcookie("token", $token, time() + (int) $sessionTime, "/");
            logmessage("Token successfully saved and cookie set.");
        }
        logmessage("Redirecting to " . $urlAfterAccess . ".");
        header("Location: " . $urlAfterAccess);
        exit;
    }
    logmessage("Invalid token or HMAC.");
    header("Location: /sessionexpired.php");
    exit;
}
logmessage("Missing token or HMAC.");
header("Location: /sessionexpired.php");
exit;
function saveToken($token)
{
    $file = "token.php";
    $tokens = file_exists($file) ? explode(",", file_get_contents($file)) : [];
    if(!in_array($token, $tokens)) {
        $tokens[] = $token;
        file_put_contents($file, implode(",", $tokens));
        return true;
    }
    return false;
}
function getToolData()
{
    $file = "cookie.json";
    return file_exists($file) ? json_decode(file_get_contents($file), true) : [];
}
function logMessage($message)
{
    $file = "access_log.txt";
    $date = date("Y-m-d H:i:s");
    file_put_contents($file, "[" . $date . "] " . $message . "\n", FILE_APPEND);
}

?> 