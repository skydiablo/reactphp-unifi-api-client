# ReactPHP Unifi API Client

Ein ReactPHP-basierter Client für die Ubiquiti Unifi Controller API.

## Installation

```bash
composer require skydiablo/reactphp-unifi-api-client
```

## Verwendung

```php
<?php

use React\EventLoop\Loop;
use SkyDiablo\UnifiApiClient\UnifiClient;
use SkyDiablo\UnifiApiClient\Services\BasicService;
use SkyDiablo\UnifiApiClient\Services\Site;
use SkyDiablo\UnifiApiClient\Services\Device;

// Client erstellen
$client = new UnifiClient(
    'https://unifi.example.com:8443',
    'username',
    'password'
);

// Services erstellen
$basicService = new BasicService($client);
$siteService = new Site($client);
$deviceService = new Device($client);

// Controller-Informationen abrufen
$basicService->getInfo()->then(function (array $info) {
    echo "Controller Version: " . $info['version'] . "\n";
});

// Standorte abrufen
$siteService->getSites()->then(function (array $sites) {
    foreach ($sites as $site) {
        echo "Site: " . $site['name'] . " (" . $site['desc'] . ")\n";
    }
});

// Geräte abrufen
$deviceService->getDeviceBasics()->then(function (array $devices) {
    foreach ($devices as $device) {
        echo "Device: " . ($device['name'] ?? 'Unnamed') . " (" . $device['mac'] . ")\n";
    }
});

// Ausloggen, wenn fertig
$client->logout()->then(function () {
    echo "Logged out\n";
    Loop::stop();
});

// Event-Loop starten
Loop::run();
```

## Verfügbare Services

### BasicService

- `getInfo()`: Ruft Informationen über den Controller ab

### Site

- `getSites()`: Ruft alle verfügbaren Standorte ab

### Device

- `getDeviceBasics(string $site = 'default')`: Ruft Basisinformationen über Geräte an einem Standort ab
- `getDevicesV2(string $site = 'default', bool $separateUnmanaged = false, bool $includeTrafficUsage = false)`: Ruft detaillierte Geräteinformationen ab (v2 API)

## Tests

Das Projekt enthält eine umfangreiche Test-Suite mit Unit-Tests und Integrationstests. Weitere Informationen finden Sie in der [Test-Dokumentation](tests/README.md).

## Lizenz

MIT 