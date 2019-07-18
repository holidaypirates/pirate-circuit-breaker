<?php declare(strict_types=1);

namespace HolidayPirates\Tests\Unit\CircuitBreaker\Awareness;

use HolidayPirates\CircuitBreaker\Awareness\CircuitBreakerAwareTrait;
use HolidayPirates\CircuitBreaker\CircuitBreakerInterface;
use HolidayPirates\CircuitBreaker\Exception\UnavailableServiceException;
use PHPUnit\Framework\TestCase;

class CircuitBreakerAwareTraitTest extends TestCase
{
    public function testCircuitBreakerHolding(): void
    {
        $traitedClass = new class
        {
            use CircuitBreakerAwareTrait;
        };

        $circuitBreakerMock = $this->prophesize(CircuitBreakerInterface::class)->reveal();
        $traitedClass->setCircuitBreaker($circuitBreakerMock);

        TestCase::assertSame($circuitBreakerMock, $traitedClass->getCircuitBreaker());

    }

    public function testExceptionThrowing(): void
    {
        $traitedClass = new class
        {
            use CircuitBreakerAwareTrait;
        };

        $fakeServiceName = 'fake-service';

        $circuitBreakerMock = $this->prophesize(CircuitBreakerInterface::class);
        $circuitBreakerMock->isServiceAvailable($fakeServiceName)->willReturn(false);

        $traitedClass->setCircuitBreaker($circuitBreakerMock->reveal());

        $this->expectException(UnavailableServiceException::class);

        $traitedClass->throwExceptionIfServiceUnavailable($fakeServiceName);
    }

    public function testReporting(): void
    {
        $traitedClass = new class
        {
            use CircuitBreakerAwareTrait;
        };

        $fakeServiceName = 'fake-service';

        $circuitBreakerMock = $this->prophesize(CircuitBreakerInterface::class);
        $circuitBreakerMock->reportSuccess($fakeServiceName)->will(function ($args) use ($fakeServiceName) {
            TestCase::assertEquals($fakeServiceName, $args[0]);
        });
        $circuitBreakerMock->reportFailure($fakeServiceName)->will(function ($args) use ($fakeServiceName) {
            TestCase::assertEquals($fakeServiceName, $args[0]);
        });

        $circuitBreakerMock->isServiceAvailable($fakeServiceName)->willReturn(true);

        $traitedClass->setCircuitBreaker($circuitBreakerMock->reveal());

        $traitedClass->reportServiceSuccess($fakeServiceName);
        $traitedClass->reportServiceFailure($fakeServiceName);
        $traitedClass->throwExceptionIfServiceUnavailable($fakeServiceName);
    }
}
