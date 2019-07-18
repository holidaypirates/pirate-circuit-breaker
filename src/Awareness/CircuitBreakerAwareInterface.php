<?php declare(strict_types=1);

namespace HolidayPirates\CircuitBreaker\Awareness;

use HolidayPirates\CircuitBreaker\CircuitBreakerInterface;

interface CircuitBreakerAwareInterface
{
    public function setCircuitBreaker(CircuitBreakerInterface $circuitBreaker): void;
}
