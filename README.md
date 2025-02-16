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
1. Upload the `index.php` file to your web server's root directory.
2. If using Apache, ensure that `.htaccess` is configured correctly:
   ```apache
   RewriteEngine On
   RewriteRule ^$ index.php [L]
   ```
3. If using Nginx, modify the server block as follows:
   ```nginx
   server {
       listen 80;
       server_name example.com;
       root /var/www/html;
       index index.php;
       location / { try_files $uri /index.php; }
       location ~ \.php$ {
           include snippets/fastcgi-php.conf;
           fastcgi_pass unix:/run/php/php-fpm.sock;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
           include fastcgi_params;
       }
   }
   ```
4. Restart your server to apply changes.

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
| Full JSON | `https://example.com/?format=full-json` | ```json { "ip": "123.45.67.89", "country": "Indonesia", "region": "Jawa Tengah", "city": "Solo", "latitude": -7.5666, "longitude": 110.8167, "isp": "Telkom Indonesia" } ``` |

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

## License
This project is open-source and free to use under the MIT License.
