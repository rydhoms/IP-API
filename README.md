![License](https://img.shields.io/github/license/rydhoms/ip-api)
![Demo Live](https://badgen.net/badge/Demo/Website/green?icon=firefox)

# IP-API

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

# Installation

Follow these steps to download the latest release of the project and deploy it to your web server's public HTML directory.

## 1. Download the Latest Release

- Go to the [Releases](https://github.com/rydhoms/ip-api/releases) page of the repository.
- Download the ZIP file for the latest release.

## 2. Extract the Files

- Once the ZIP file is downloaded, extract its contents to a local directory on your computer.

## 3. Upload to Your Web Server

- **Access Your Server:**
   - Use an FTP client (e.g., FileZilla), SFTP, or your hosting control panel's file manager to connect to your web server.
- **Navigate to the Public HTML Directory:**
   - Locate your server's `public_html` (or equivalent web root) directory.
- **Upload Files:**
   - Upload all extracted files and folders from the release into the `public_html` directory.

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
Usage examples can be accessed at [https://ip.ridho.my.id](https://ip.ridho.my.id) to display the front-end page for IP checking and VPN/Proxy detection.

You can also use the API with various formats:
- **JSON Format**: [https://api.ridho.my.id/?format=json](https://api.ridho.my.id/?format=json)
- **Full Format**: [https://api.ridho.my.id/?format=full](https://api.ridho.my.id/?format=full)
- **Full JSON Format**: [https://api.ridho.my.id/?format=full-json](https://api.ridho.my.id/?format=full-json)

Other formats are also available as needed.

## IPv4 and IPv6 Detection
- IPv4 Only Detection: Set up a web server that has an IPv4 address. Create an A record (which points to an IPv4 address) for your domain. For example, if you use `api4.ridho.my.id`, users with both IPv4 and IPv6 will connect via IPv4. If a user only has IPv6, they won't be able to access the API.

- IPv6 Only Detection: Set up a web server that has an IPv6 address. Create an AAAA record (which points to an IPv6 address) for your domain. For example, if you use `api6.ridho.my.id`, users with both IPv4 and IPv6 will connect via IPv6. If a user only has IPv4, they won't be able to access the API.

- Both IPv4 and IPv6 (Random) Detection: You can combine both A and AAAA records on the same domain. For example, with `api.ridho.my.id` having both record types, a user with both IPv4 and IPv6 might connect using either protocol, depending on their primary network settings.

- If your server supports both IPv4 and IPv6, you only need one server. You can use different subdomains to control how users connect:

  - `api4.ridho.my.id`: This subdomain uses an A record, so users connect with IPv4 only.

  - `api6.ridho.my.id`: This subdomain uses an AAAA record, so users connect with IPv6 only.

  - `api.ridho.my.id`: This subdomain has both A and AAAA records, allowing connections via either IPv4 or IPv6.

## Front End
For the front end, you can use the [IP-INFO](https://github.com/rydhoms/IP-INFO) project which has a simple front end page to display the user's IP and user IP details.

## License
This project is open-source and free to use under the MIT License.
