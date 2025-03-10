# Tests für ReactPHP Unifi API Client

Diese Test-Suite enthält Unit-Tests und Integrationstests für den ReactPHP Unifi API Client.

## Voraussetzungen

- PHP 8.1 oder höher
- Composer

## Installation

```bash
composer install
```

## Unit-Tests ausführen

Unit-Tests können ohne Verbindung zu einem Unifi-Controller ausgeführt werden:

```bash
./vendor/bin/phpunit --testsuite Unit
```

## Integrationstests ausführen

Integrationstests erfordern eine Verbindung zu einem echten Unifi-Controller. Sie müssen die folgenden Umgebungsvariablen setzen:

```bash
export UNIFI_HOST="https://unifi.example.com:8443"
export UNIFI_USERNAME="admin"
export UNIFI_PASSWORD="password"
```

Dann können Sie die Integrationstests ausführen:

```bash
./vendor/bin/phpunit --testsuite Integration
```

## Alle Tests ausführen

Um alle Tests auszuführen:

```bash
./vendor/bin/phpunit
```

## Code-Abdeckung

Um einen Code-Abdeckungsbericht zu generieren:

```bash
XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-html coverage
```

Der Bericht wird im Verzeichnis `coverage` erstellt.

## Hinweise

- Die Integrationstests werden übersprungen, wenn die erforderlichen Umgebungsvariablen nicht gesetzt sind.
- Die Tests verwenden Mockery für Mocking und PHPUnit für Assertions.
- Die Tests sind in Unit-Tests und Integrationstests unterteilt. 