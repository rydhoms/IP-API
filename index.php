<?php
// index.php or api.php to respond api request
// Function to get the user's IP address
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP);
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ipList[0]); // Get the first IP (front-most)
        return filter_var($ip, FILTER_VALIDATE_IP);
    } else {
        return filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
    }
}

// Function to get geo-location data based on IP
function getGeoLocation($ip) {
    $api_url = "http://ip-api.com/json/{$ip}?fields=status,message,country,regionName,city,lat,lon,isp";
    
    if (function_exists('file_get_contents')) {
        $response = @file_get_contents($api_url);
    } else {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        curl_close($ch);
    }
    
    return $response ? json_decode($response, true) : null;
}

// Get format parameter from the URL
$format = isset($_GET['format']) ? strtolower($_GET['format']) : 'text';
$callback = isset($_GET['callback']) ? $_GET['callback'] : null;
$user_ip = getUserIP();

// Fetch geo-location data
$geoData = getGeoLocation($user_ip);

// Function to get IP and geolocation in text format
function getUserGeoText($geoData) {
    return "IP Address: " . getUserIP() . "\n" .
           "Country: " . ($geoData['country'] ?? 'N/A') . "\n" .
           "Region: " . ($geoData['regionName'] ?? 'N/A') . "\n" .
           "City: " . ($geoData['city'] ?? 'N/A') . "\n" .
           "Latitude: " . ($geoData['lat'] ?? 'N/A') . "\n" .
           "Longitude: " . ($geoData['lon'] ?? 'N/A') . "\n" .
           "ISP: " . ($geoData['isp'] ?? 'N/A');
}

// Function to get IP and geolocation in JSON format
function getUserGeoJson($geoData) {
    return json_encode([
        "ip" => getUserIP(),
        "country" => $geoData['country'] ?? 'N/A',
        "region" => $geoData['regionName'] ?? 'N/A',
        "city" => $geoData['city'] ?? 'N/A',
        "latitude" => $geoData['lat'] ?? 'N/A',
        "longitude" => $geoData['lon'] ?? 'N/A',
        "isp" => $geoData['isp'] ?? 'N/A'
    ]);
}

// Set headers based on format
switch ($format) {
    case 'json':
        header('Content-Type: application/json');
        echo json_encode(["ip" => $user_ip]);
        break;
    
    case 'jsonp':
        if (!empty($callback) && preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $callback)) {
            header('Content-Type: application/javascript');
            echo $callback . '(' . json_encode(["ip" => $user_ip]) . ');';
        } else {
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
}
?>
