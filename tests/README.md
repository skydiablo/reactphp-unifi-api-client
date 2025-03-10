# Tests for ReactPHP Unifi API Client

This test suite contains unit tests and integration tests for the ReactPHP Unifi API Client.

## Requirements

- PHP 8.1 or higher
- Composer
- PHPUnit 10.5 or higher
- Mockery 1.6 or higher
- Xdebug (for code coverage reports)

## Installation

```bash
composer install
```

## Running Tests

### Unit Tests

Unit tests can be run without a connection to a Unifi controller:

```bash
./vendor/bin/phpunit --testsuite Unit
```

### Integration Tests

Integration tests require a connection to a real Unifi controller. You need to set the following environment variables:

```bash
export UNIFI_HOST="https://unifi.example.com:8443"
export UNIFI_USERNAME="admin"
export UNIFI_PASSWORD="password"
```

Then you can run the integration tests:

```bash
./vendor/bin/phpunit --testsuite Integration
```

### Running All Tests

To run all tests:

```bash
./vendor/bin/phpunit
```

## Code Coverage

To generate a code coverage report:

```bash
XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-html coverage
```

The report will be created in the `coverage` directory.

## Test Structure

- `Unit/`: Contains unit tests that mock external dependencies
  - `Services/`: Tests for individual service classes
  - `UnifiClientTest.php`: Tests for the main client class
- `Integration/`: Contains tests that interact with a real Unifi controller
  - `Services/`: Integration tests for service classes
  - `UnifiClientTest.php`: Integration tests for the client

## Notes

- Integration tests are skipped if the required environment variables are not set
- Tests use Mockery for mocking and PHPUnit for assertions
- The test suite is configured in the `phpunit.xml` file in the project root 