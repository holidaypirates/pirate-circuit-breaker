<?php declare(strict_types=1);

namespace HolidayPirates\Tests\Integration\CircuitBreaker\Storage\Adapter;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Exception;
use HolidayPirates\CircuitBreaker\Service\DummyService;
use HolidayPirates\CircuitBreaker\Storage\Adapter\Exception\StorageAdapterException;
use HolidayPirates\CircuitBreaker\Storage\Adapter\SimpleCacheAdapter;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\SimpleCache\CacheException;
use Psr\SimpleCache\CacheInterface;
use Throwable;

class SimpleCacheAdapterTest extends TestCase
{
    public function testFailureHandlers(): void
    {
        $cache = new ArrayCachePool();
        $adapter = new SimpleCacheAdapter($cache);
        $service = new DummyService(1, 1);

        TestCase::assertEquals(0, $adapter->getAmountOfFailures($service));

        $adapter->incrementAmountOfFailures($service);

        TestCase::assertEquals(1, $adapter->getAmountOfFailures($service));
    }

    public function testSuccessHandlers(): void
    {
        $cache = new ArrayCachePool();
        $adapter = new SimpleCacheAdapter($cache);
        $service = new DummyService(1, 1);

        $adapter->incrementAmountOfFailures($service);
        $adapter->incrementAmountOfFailures($service);
        $adapter->incrementAmountOfFailures($service);

        TestCase::assertEquals(3, $adapter->getAmountOfFailures($service));

        $adapter->incrementAmountOfSuccess($service);
        $adapter->incrementAmountOfSuccess($service);
        $adapter->incrementAmountOfSuccess($service);

        /**
         * Each success operation reported should decrement the amount of failures to control the threshold
         */
        TestCase::assertEquals(0, $adapter->getAmountOfFailures($service));
    }

    public function testCircuitHandling(): void
    {
        $cache = new ArrayCachePool();
        $adapter = new SimpleCacheAdapter($cache);
        $service = new DummyService(1, 1);

        TestCase::assertFalse($adapter->isCircuitOpen($service));

        $adapter->setOpenCircuit($service);

        TestCase::assertTrue($adapter->isCircuitOpen($service));
    }

    public function testExceptionThrowingGet(): void
    {
        $cache = $this->prophesize(CacheInterface::class);
        $cache->get(Argument::any(), Argument::any())->willThrow($this->getMockCacheException());

        $adapter = new SimpleCacheAdapter($cache->reveal());
        $service = new DummyService(1, 2);

        $this->expectException(StorageAdapterException::class);

        $adapter->incrementAmountOfFailures($service);
    }

    public function testExceptionThrowingSet(): void
    {
        $cache = $this->prophesize(CacheInterface::class);
        $cache->set(Argument::any(), Argument::any(), Argument::any())->willThrow($this->getMockCacheException());

        $adapter = new SimpleCacheAdapter($cache->reveal());
        $service = new DummyService(1, 2);

        $this->expectException(StorageAdapterException::class);

        $adapter->setOpenCircuit($service);
    }

    public function getMockCacheException(): CacheException
    {
        return new class extends Exception implements CacheException
        {
        };
    }
}
