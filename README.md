# Public IP API

This is a simple PHP-based public API to retrieve a user's IP address in various formats. The API supports JSON, JSONP, XML, CSV, HTML, and plain text responses.

## Features
- Supports multiple response formats: `json`, `jsonp`, `xml`, `csv`, `html`, and `text`.
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
| CSV | `https://example.com/?format=csv` | `ip
123.45.67.89` |
| HTML | `https://example.com/?format=html` | `<html><body><p>Your IP Address: 123.45.67.89</p></body></html>` |

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
