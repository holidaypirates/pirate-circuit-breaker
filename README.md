# pirate-circuit-breaker

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/f6ff3265503746ab824e9e2df84570f6)](https://app.codacy.com/app/ricardofiorani/pirate-circuit-breaker?utm_source=github.com&utm_medium=referral&utm_content=holidaypirates/pirate-circuit-breaker&utm_campaign=Badge_Grade_Dashboard)

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

This is an Implementation of the 2-state (Open and Closed) CircuitBreaker pattern that we use at HolidayPirates.  
Unlike the [3-state CircuitBreaker proposed by Fowler](https://martinfowler.com/bliki/CircuitBreaker.html), this implementation has only two states, "Open" and "Closed".  

## Install

Via Composer

```bash
$ composer require holidaypirates/pirate-circuit-breaker
```

## Requirements
- PHP 7.3
- An implementation of the `\Psr\SimpleCache\CacheInterface` to store the services failures and circuit state OR your own storage implementation of `\HolidayPirates\CircuitBreaker\Storage\StorageInterface`
- For development only : Docker and Docker-Compose
## Usage

```php
<?php declare(strict_types=1);

use HolidayPirates\CircuitBreaker\CircuitBreaker;
use HolidayPirates\CircuitBreaker\Service\DummyService;
use HolidayPirates\CircuitBreaker\Storage\Adapter\SimpleCacheAdapter;

// Setup:
$pool = new YourCachePool(); // Any implementation of \Psr\SimpleCache\CacheInterface
$storageAdapter = new SimpleCacheAdapter($pool);
$circuitBreaker = new CircuitBreaker($storageAdapter);

$service = new DummyService(5, 60); //After 5 failed attempts it will wait 60 seconds before allowing more requests.

$circuitBreaker->registerService($service);

// Usage:
$dummyApiClient = new DummyApiClient(); // This will be any service you want to protect with the CB

if (false == $circuitBreaker->isServiceAvailable(DummyService::class)) {
    throw new \Exception('Service unavailable');
}

try {
    $response = $dummyApiClient->sendRequest();
    $circuitBreaker->reportSuccess(DummyService::class);
} catch (Exception $exception) {
    $circuitBreaker->reportFailure(DummyService::class);
       
    throw new \Exception('Service unavailable',0, $exception);
}

```
> Please note that `HolidayPirates\CircuitBreaker\Service\DummyService` is just an implementation of `\HolidayPirates\CircuitBreaker\Service\ServiceInterface`.  
> You must create your own implementations of `\HolidayPirates\CircuitBreaker\Service\ServiceInterface` for each service that you want the CircuitBreaker to operate in.  

For more examples of usage please see `\HolidayPirates\Tests\Integration\CircuitBreaker\CircuitBreakerTest`
## Testing

```bash
$ docker-compose run php vendor/bin/phpunit
```

## Credits

- [Ricardo Fiorani][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/holidaypirates/pirate-circuit-breaker.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/holidaypirates/pirate-circuit-breaker/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/holidaypirates/pirate-circuit-breaker.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/holidaypirates/pirate-circuit-breaker.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/holidaypirates/pirate-circuit-breaker.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/holidaypirates/pirate-circuit-breaker
[link-travis]: https://travis-ci.org/holidaypirates/pirate-circuit-breaker
[link-scrutinizer]: https://scrutinizer-ci.com/g/holidaypirates/pirate-circuit-breaker/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/holidaypirates/pirate-circuit-breaker
[link-downloads]: https://packagist.org/packages/holidaypirates/pirate-circuit-breaker
[link-author]: https://github.com/ricardofiorani
[link-contributors]: ../../contributors
