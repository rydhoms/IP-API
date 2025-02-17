<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IP Geolocation & Proxy/VPN Detection</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            color: #2c3e50;
        }

        .data-item {
            margin: 1rem 0;
            font-size: 1.1rem;
        }

        .data-item strong {
            color: #2980b9;
        }

        .loading {
            font-size: 1.2rem;
            color: #7f8c8d;
        }

        .error {
            color: #e74c3c;
            font-size: 1.2rem;
        }

        .refresh-button {
            margin-top: 1.5rem;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            color: #fff;
            background-color: #3498db;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .refresh-button:hover {
            background-color: #2980b9;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>IP Geolocation & Proxy/VPN Detection</h1>
        <div id="data" class="loading">Loading...</div>
        <button class="refresh-button" onclick="fetchData()">Refresh Data</button>
    </div>

    <script>
        // Function to fetch data from the API
        async function fetchData() {
            const dataElement = document.getElementById('data');
            dataElement.innerHTML = '<div class="loading">Loading...</div>';

            try {
                const response = await fetch('v1/api?format=full-json');
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const data = await response.json();
                displayData(data);
            } catch (error) {
                dataElement.innerHTML = `<div class="error">Error: ${error.message}</div>`;
            }
        }

        // Function to display the fetched data
        function displayData(data) {
            const dataElement = document.getElementById('data');
            dataElement.innerHTML = `
                <div class="data-item"><strong>IP Address:</strong> ${data.ip}</div>
                <div class="data-item"><strong>Country:</strong> ${data.country}</div>
                <div class="data-item"><strong>Region:</strong> ${data.region}</div>
                <div class="data-item"><strong>City:</strong> ${data.city}</div>
                <div class="data-item"><strong>Latitude:</strong> ${data.latitude}</div>
                <div class="data-item"><strong>Longitude:</strong> ${data.longitude}</div>
                <div class="data-item"><strong>ISP:</strong> ${data.isp}</div>
                <div class="data-item"><strong>Proxy/VPN Status:</strong> ${data.proxy_vpn_status}</div>
            `;
        }

        // Fetch data when the page loads
        fetchData();
    </script>
</body>

</html>