<?php declare(strict_types=1);

namespace HolidayPirates\Tests\Integration\CircuitBreaker;

use Cache\Adapter\Redis\RedisCachePool;
use HolidayPirates\CircuitBreaker\CircuitBreaker;
use HolidayPirates\CircuitBreaker\CircuitBreakerInterface;
use HolidayPirates\CircuitBreaker\Service\DummyService;
use HolidayPirates\CircuitBreaker\Storage\Adapter\SimpleCacheAdapter;
use HolidayPirates\CircuitBreaker\Storage\StorageInterface;
use LogicException;
use PHPUnit\Framework\TestCase;
use Redis;

class CircuitBreakerTest extends TestCase
{
    public function testServiceAvailabilityInASimpleScenario(): void
    {
        $circuitBreaker = $this->buildCircuitBreaker();
        $dummyService = new DummyService(2, 60);
        $circuitBreaker->registerService($dummyService);

        TestCase::assertTrue($circuitBreaker->isServiceAvailable(DummyService::class));

        $circuitBreaker->reportFailure(DummyService::class);
        $circuitBreaker->reportFailure(DummyService::class);

        TestCase::assertTrue($circuitBreaker->isServiceAvailable(DummyService::class));

        $circuitBreaker->reportFailure(DummyService::class);

        TestCase::assertFalse($circuitBreaker->isServiceAvailable(DummyService::class));
    }

    public function testServiceAvailabilityInaComplexScenario(): void
    {
        $circuitBreaker = $this->buildCircuitBreaker();
        $dummyService = new DummyService(2, 2);
        $circuitBreaker->registerService($dummyService);

        $circuitBreaker->reportFailure(DummyService::class);
        $circuitBreaker->reportFailure(DummyService::class);
        TestCase::assertTrue($circuitBreaker->isServiceAvailable(DummyService::class));

        $circuitBreaker->reportSuccess(DummyService::class);
        $circuitBreaker->reportSuccess(DummyService::class);
        TestCase::assertTrue($circuitBreaker->isServiceAvailable(DummyService::class));

        $circuitBreaker->reportFailure(DummyService::class);
        $circuitBreaker->reportFailure(DummyService::class);
        TestCase::assertTrue($circuitBreaker->isServiceAvailable(DummyService::class));

    }

    public function testThrowsExceptionWhenServiceIsNotRegistered(): void
    {
        $this->expectException(LogicException::class);
        $circuitBreaker = $this->buildCircuitBreaker();
        $circuitBreaker->isServiceAvailable(DummyService::class);
    }

    public function testGetRegisteredServices(): void
    {
        $circuitBreaker = $this->buildCircuitBreaker();

        TestCase::assertEquals([], $circuitBreaker->getRegisteredServiceNames());

        $circuitBreaker->registerService(new DummyService(0, 0));

        TestCase::assertEquals([DummyService::class], $circuitBreaker->getRegisteredServiceNames());
    }

    public function testServicesAvailable()
    {
        $circuitBreaker = $this->buildCircuitBreaker();
        $circuitBreaker->registerService(new DummyService(1,60));

        TestCase::assertTrue($circuitBreaker->areAllServicesAvailable());

        $circuitBreaker->reportFailure(DummyService::class);
        $circuitBreaker->reportFailure(DummyService::class);

        TestCase::assertFalse($circuitBreaker->areAllServicesAvailable());
    }

    private function getStorage(): StorageInterface
    {
        $client = new Redis();
        $client->connect('redis');
        $pool = new RedisCachePool($client);
        $pool->clear();

        return new SimpleCacheAdapter($pool);
    }

    private function buildCircuitBreaker(): CircuitBreakerInterface
    {
        $storage = $this->getStorage();

        return new CircuitBreaker($storage);
    }
}
