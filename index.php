<?php
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

// Get format parameter from the URL
$format = isset($_GET['format']) ? strtolower($_GET['format']) : 'text';
$callback = isset($_GET['callback']) ? $_GET['callback'] : null;
$user_ip = getUserIP();

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

    default:
        header('Content-Type: text/plain');
        echo $user_ip;
}
?>
