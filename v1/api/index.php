<?php
// /v1/api/index.php

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set secure headers for public API access
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Content-Security-Policy: default-src 'none'; script-src 'self'; style-src 'self'; img-src 'self' data:; frame-ancestors 'none';");
header("Referrer-Policy: no-referrer-when-downgrade");
header("Permissions-Policy: geolocation=(), microphone=(), camera=(), usb=()");

// Enforce HTTPS (improved check for proxies)
$isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
           (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
if (!$isHttps) {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
    exit();
}

// Health check endpoint
if ($_SERVER['REQUEST_URI'] === '/health') {
    header('Content-Type: application/json');
    echo json_encode(["status" => "ok", "timestamp" => date("Y-m-d H:i:s")]);
    exit();
}

// Function to get the user's IP address
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP);
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ipList[0]);
        return filter_var($ip, FILTER_VALIDATE_IP);
    } else {
        return filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
    }
}

// Rate limiting with file locking to prevent race conditions
function isRateLimited($ip) {
    $rateLimitFile = __DIR__ . "/cache/rate_limit_{$ip}.json";
    $rateLimitTime = 60; // 1 minute
    $maxRequests = 100; // Max requests per minute

    if (file_exists($rateLimitFile)) {
        $fp = fopen($rateLimitFile, 'r+');
        if (flock($fp, LOCK_EX)) { // Exclusive lock
            $data = json_decode(file_get_contents($rateLimitFile), true);
            if ($data['count'] >= $maxRequests && (time() - $data['timestamp']) < $rateLimitTime) {
                flock($fp, LOCK_UN); // Release lock
                fclose($fp);
                return true; // Rate limit exceeded
            }
            // Update rate limit data
            $data = [
                'timestamp' => time(),
                'count' => ($data['count'] ?? 0) + 1
            ];
            file_put_contents($rateLimitFile, json_encode($data));
            flock($fp, LOCK_UN); // Release lock
        }
        fclose($fp);
    } else {
        // Create new rate limit file
        $data = [
            'timestamp' => time(),
            'count' => 1
        ];
        file_put_contents($rateLimitFile, json_encode($data));
    }

    return false;
}

$user_ip = getUserIP();
if (isRateLimited($user_ip)) {
    http_response_code(429); // Too Many Requests
    die("Rate limit exceeded. Please try again later.");
}

// Ensure required directories exist with secure permissions
function ensureDirectories() {
    $dirs = [__DIR__ . "/cache", __DIR__ . "/logs"];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true); // Secure permissions
        }
    }
}

// Function to write logs in JSON format with file locking
function writeLog($message, $type = "access") {
    ensureDirectories();
    $logFile = __DIR__ . "/logs/{$type}.json";

    $fp = fopen($logFile, 'c+');
    if (flock($fp, LOCK_EX)) { // Exclusive lock
        $logData = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : [];
        $logData[] = ["timestamp" => date("Y-m-d H:i:s"), "message" => $message];
        file_put_contents($logFile, json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        flock($fp, LOCK_UN); // Release lock
    }
    fclose($fp);

    manageLogSize($logFile);
}

// Function to log errors
function logError($errorMessage) {
    writeLog($errorMessage, "error");
}

// Function to limit log file size (max 5MB) with file locking
function manageLogSize($file) {
    $maxSize = 5 * 1024 * 1024; // 5MB
    if (file_exists($file) && filesize($file) > $maxSize) {
        $backupFile = $file . ".old";
        $fp = fopen($file, 'r+');
        if (flock($fp, LOCK_EX)) { // Exclusive lock
            @rename($file, $backupFile);
            file_put_contents($file, json_encode([["timestamp" => date("Y-m-d H:i:s"), "message" => "Log truncated due to size limit"]], JSON_PRETTY_PRINT));
            flock($fp, LOCK_UN); // Release lock
        }
        fclose($fp);
    }
}

// Function to delete old cache files
function clearOldCache() {
    $cacheDir = __DIR__ . "/cache";
    $cacheLifetime = 3600; // 1 hour

    if (!is_dir($cacheDir)) return;

    foreach (glob($cacheDir . "/*.json") as $file) {
        if (filemtime($file) < (time() - $cacheLifetime)) {
            @unlink($file);
        }
    }
}

// Function to fetch data using cURL or fallback to file_get_contents
function fetchUrl($url, $headers = []) {
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); // Timeout 5 seconds
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge([
            'User-Agent: MyPublicAPI/1.0'
        ], $headers));
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) {
            throw new Exception("cURL error: " . curl_error($ch));
        }

        return $response;
    }

    if (ini_get('allow_url_fopen')) {
        $context = stream_context_create([
            'http' => [
                'timeout' => 5, // Timeout 5 seconds
                'header' => array_merge([
                    "User-Agent: MyPublicAPI/1.0"
                ], $headers)
            ]
        ]);
        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            throw new Exception("file_get_contents error: Unable to fetch data from $url");
        }

        return $response;
    }

    throw new Exception("Neither cURL nor file_get_contents is available.");
}

// Function to detect proxy/VPN using IPHub (API key from environment)
function detectProxyVPN($ip) {

    // Include config.php from the root directory
    require_once __DIR__ . '/../../config.php';

    // Retrieve the API key
    $apiKey = $config['IPHUB_API_KEY'];

    if (!$apiKey) {
        throw new Exception("IPHub API key not found.");
    }

    $apiUrl = "http://v2.api.iphub.info/ip/{$ip}";

    try {
        $response = fetchUrl($apiUrl, [
            'X-Key: ' . $apiKey
        ]);

        $data = json_decode($response, true);

        if (isset($data['block'])) {
            switch ($data['block']) {
                case 0:
                    return "No proxy or VPN detected.";
                case 1:
                    return "Proxy or VPN detected (residential proxy).";
                case 2:
                    return "Proxy or VPN detected (non-residential proxy, hosting provider, or data center).";
                default:
                    return "Unknown proxy/VPN status.";
            }
        } else {
            return "Unable to detect proxy/VPN status.";
        }
    } catch (Exception $e) {
        logError("Proxy/VPN detection failed: " . $e->getMessage());
        return "Proxy/VPN detection failed.";
    }
}

// Function to fetch geolocation data from multiple APIs with caching
function getGeoLocation($ip) {
    ensureDirectories();
    clearOldCache();
    $cacheFile = __DIR__ . "/cache/geo_{$ip}.json";
    $cacheTime = 600; // 10 minutes

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
        return json_decode(file_get_contents($cacheFile), true);
    }

    // List of APIs (primary and fallback)
    $apiList = [
        "http://ip-api.com/json/{$ip}?fields=status,message,country,regionName,city,lat,lon,isp",
        "https://ipwhois.app/json/{$ip}",
        "https://ipinfo.io/{$ip}/json?token=" . getenv('IPINFO_API_KEY') // Use environment variable
    ];

    foreach ($apiList as $api_url) {
        try {
            $response = fetchUrl($api_url);
            $geoData = json_decode($response, true);
            if (isset($geoData['country'])) {
                file_put_contents($cacheFile, json_encode($geoData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                return $geoData;
            }
        } catch (Exception $e) {
            logError($e->getMessage());
        }
    }

    logError("All geolocation APIs failed for IP: $ip");
    return [
        "error" => "Unable to fetch geolocation data",
        "country" => "N/A",
        "regionName" => "N/A",
        "city" => "N/A",
        "lat" => "N/A",
        "lon" => "N/A",
        "isp" => "N/A"
    ];
}

// Get parameters from URL with validation
$allowedFormats = ['text', 'json', 'jsonp', 'xml', 'csv', 'html', 'full', 'full-json'];
$format = isset($_GET['format']) && in_array(strtolower($_GET['format']), $allowedFormats) ? strtolower($_GET['format']) : 'text';
$callback = isset($_GET['callback']) && preg_match('/^[a-zA-Z_][a-zA-Z0-9_.]*$/', $_GET['callback']) ? $_GET['callback'] : null;

$geoData = getGeoLocation($user_ip);

// Function to format geolocation in text
function getUserGeoText($geoData) {
    $proxyVpnStatus = detectProxyVPN(getUserIP());

    return "IP Address: " . getUserIP() . "\n" .
           "Country: " . ($geoData['country'] ?? 'N/A') . "\n" .
           "Region: " . ($geoData['regionName'] ?? 'N/A') . "\n" .
           "City: " . ($geoData['city'] ?? 'N/A') . "\n" .
           "Latitude: " . ($geoData['lat'] ?? 'N/A') . "\n" .
           "Longitude: " . ($geoData['lon'] ?? 'N/A') . "\n" .
           "ISP: " . ($geoData['isp'] ?? 'N/A') . "\n" .
           "Proxy/VPN Status: " . $proxyVpnStatus;
}

// Function to format geolocation in JSON
function getUserGeoJson($geoData) {
    $proxyVpnStatus = detectProxyVPN(getUserIP());

    return json_encode([
        "ip" => getUserIP(),
        "country" => $geoData['country'] ?? 'N/A',
        "region" => $geoData['regionName'] ?? 'N/A',
        "city" => $geoData['city'] ?? 'N/A',
        "latitude" => $geoData['lat'] ?? 'N/A',
        "longitude" => $geoData['lon'] ?? 'N/A',
        "isp" => $geoData['isp'] ?? 'N/A',
        "proxy_vpn_status" => $proxyVpnStatus
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

// Logging request (anonymize IP for privacy)
writeLog("IP: " . md5($user_ip) . " - Format: $format");

// Output based on requested format
switch ($format) {
    case 'json':
        header('Content-Type: application/json');
        echo json_encode(["ip" => $user_ip], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        break;

    case 'jsonp':
        if (!empty($callback)) {
            header('Content-Type: application/javascript');
            echo $callback . '(' . json_encode(["ip" => $user_ip], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
        } else {
            logError("Invalid JSONP callback provided by IP: " . md5($user_ip));
            http_response_code(400); // Bad Request
            echo "Invalid or missing callback parameter for JSONP";
        }
        break;

    case 'xml':
        header('Content-Type: application/xml');
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
        echo "<response><ip>$user_ip</ip></response>";
        break;

    case 'csv':
        header('Content-Type: text/csv');
        echo "ip\n$user_ip";
        break;

    case 'html':
        header('Content-Type: text/html; charset=UTF-8');
        echo "<html><head><title>Your IP Address</title></head><body><p>Your IP Address: $user_ip</p></body></html>";
        break;

    case 'full':
        header('Content-Type: text/plain');
        echo getUserGeoText($geoData);
        break;

    case 'full-json':
        header('Content-Type: application/json');
        echo getUserGeoJson($geoData);
        break;

    default:
        header('Content-Type: text/plain');
        echo $user_ip;
        echo "\n";
}
?>