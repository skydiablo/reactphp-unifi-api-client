# ReactPHP Unifi API Client

A ReactPHP-based client for the Ubiquiti Unifi Controller API. This library enables asynchronous communication with Unifi Controllers using ReactPHP.

## Requirements

- PHP 8.1 or higher
- ReactPHP HTTP Client

## Installation

```bash
composer require skydiablo/reactphp-unifi-api-client
```

## Usage

```php
<?php

use React\EventLoop\Loop;
use SkyDiablo\UnifiApiClient\UnifiClient;
use SkyDiablo\UnifiApiClient\Services\BasicService;
use SkyDiablo\UnifiApiClient\Services\Site;
use SkyDiablo\UnifiApiClient\Services\Device;

// Create client
$client = new UnifiClient(
    'https://unifi.example.com:8443',
    'username',
    'password'
);

// Create services
$basicService = new BasicService($client);
$siteService = new Site($client);
$deviceService = new Device($client);

// Get controller information
$basicService->getInfo()->then(function (array $info) {
    echo "Controller Version: " . $info['version'] . "\n";
});

// Get sites
$siteService->getSites()->then(function (array $sites) {
    foreach ($sites as $site) {
        echo "Site: " . $site['name'] . " (" . $site['desc'] . ")\n";
    }
});

// Get devices
$deviceService->getDeviceBasics()->then(function (array $devices) {
    foreach ($devices as $device) {
        echo "Device: " . ($device['name'] ?? 'Unnamed') . " (" . $device['mac'] . ")\n";
    }
});

// Logout when finished
$client->logout()->then(function () {
    echo "Logged out\n";
    Loop::stop();
});

// Start event loop
Loop::run();
```

## Available Services

### BasicService

- `getInfo()`: Retrieves information about the controller

### Site

- `getSites()`: Retrieves all available sites

### Device

- `getDeviceBasics(string $site = 'default')`: Retrieves basic information about devices at a site
- `getDevicesV2(string $site = 'default', bool $separateUnmanaged = false, bool $includeTrafficUsage = false)`: Retrieves detailed device information (v2 API)

## Error Handling

All API requests return a Promise object. Use the `catch` method to handle errors:

```php
$client->login()->then(
    function () {
        echo "Login successful\n";
    },
    function (\Exception $e) {
        echo "Login failed: " . $e->getMessage() . "\n";
    }
);
```

## Tests

The project includes a comprehensive test suite with unit tests and integration tests. For more information, see the [Test Documentation](tests/README.md).

## Contributing

Contributions are welcome! Please ensure your changes pass the tests and add new tests as needed.

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

MIT 