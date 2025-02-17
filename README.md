![License](https://img.shields.io/github/license/rydhoms/ip-api)
![Demo Live](https://badgen.net/badge/Demo/Website/green?icon=firefox)

# Public IP API

This is a simple PHP-based public API to retrieve a user's IP address in various formats. The API supports JSON, JSONP, XML, CSV, HTML, and plain text responses.

## Requirements
- PHP 7.4 or later
- Web server (Apache, Nginx, or similar)
- Internet access for fetching geolocation data (`full` and `full-json` formats)
- Optional: cURL enabled (for additional integrations)

## Features
- Supports multiple response formats: `json`, `jsonp`, `xml`, `csv`, `html`, `text`, `full`, and `full-json`.
- **Format `full` and `full-json` include geolocation data obtained from the free IP geolocation service [ip-api.com](https://ip-api.com/).**
- **Geolocation data has limitations due to the free tier of `ip-api.com` (limited requests per minute).**
- Can be accessed directly via the root domain.
- Simple and lightweight implementation.

## Installation
1. Download source
2. Upload the all files to to your web server's root directory
3. Edit `config.php` and replace the key from IPHub service to get VPN/Proxy detection, and the key from IPInfo service for backup IP geolocation detection.
4. Access your website via domain root like `https://example.com` and access api via `https://example.com/?format=json` or  `https://example.com/v1/api?format=json`


## Usage
| Format  | URL Example | Response |
|---------|------------|----------|
| Plain Text (Default) | `https://example.com/` | `123.45.67.89` |
| JSON | `https://example.com/?format=json` | `{ "ip": "123.45.67.89" }` |
| JSONP | `https://example.com/?format=jsonp&callback=myFunction` | `myFunction({ "ip": "123.45.67.89" });` |
| XML | `https://example.com/?format=xml` | `<?xml version="1.0" encoding="UTF-8"?><response><ip>123.45.67.89</ip></response>` |
| CSV | `https://example.com/?format=csv` | `ip,123.45.67.89` |
| HTML | `https://example.com/?format=html` | `<html><body><p>Your IP Address: 123.45.67.89</p></body></html>` |
| Full (IP + Geolocation) | `https://example.com/?format=full` | ``` IP Address: 123.45.67.89 Country: Indonesia Region: Jawa Tengah City: Solo Latitude: -7.5666 Longitude: 110.8167 ISP: Telkom Indonesia ``` |
| Full JSON | `https://example.com/?format=full-json` | ``` { "ip": "123.45.67.89", "country": "Indonesia", "region": "Jawa Tengah", "city": "Solo", "latitude": -7.5666, "longitude": 110.8167, "isp": "Telkom Indonesia" } ``` |

🔹 **Note:**  
- **The `full` and `full-json` formats use geolocation data from [ip-api.com](https://ip-api.com/), a free IP geolocation service.**  
- **The free tier of `ip-api.com` has request limits (up to 45 requests per minute). If the limit is exceeded, the API may return an error or limited data.**  
- For higher request limits, consider using their **pro** version.  

## Code Examples

### Bash (cURL)
```sh
curl -s https://example.com/
```

### Bash (wget)
```sh
wget -qO- https://example.com/
```

### PHP
```php
<?php
$ip = file_get_contents("https://example.com/?format=json");
echo json_decode($ip, true)["ip"];
?>
```

### JavaScript (Browser)
```javascript
fetch('https://example.com/?format=json')
    .then(response => response.json())
    .then(data => console.log(data.ip));
```

### Python
```python
import requests
response = requests.get("https://example.com/?format=json")
print(response.json()["ip"])
```

### Node.js
```javascript
const https = require('https');
https.get('https://example.com/?format=json', (res) => {
    let data = '';
    res.on('data', chunk => { data += chunk; });
    res.on('end', () => { console.log(JSON.parse(data).ip); });
});
```

## Demo Website
Usage examples can be accessed at [https://ip.ridho.id](https://ip.ridho.id) to display the front-end page for IP checking and VPN/Proxy detection.

You can also use the API with various formats:
- **JSON Format**: [https://ip.ridho.id/?format=json](https://ip.ridho.id/?format=json)
- **Full Format**: [https://ip.ridho.id/?format=full](https://ip.ridho.id/?format=full)
- **Full JSON Format**: [https://ip.ridho.id/?format=full-json](https://ip.ridho.id/?format=full-json)

Other formats are also available as needed.

## License
This project is open-source and free to use under the MIT License.
