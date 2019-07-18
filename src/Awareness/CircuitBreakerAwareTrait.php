<?php declare(strict_types=1);

namespace HolidayPirates\CircuitBreaker\Awareness;

use HolidayPirates\CircuitBreaker\CircuitBreakerInterface;
use HolidayPirates\CircuitBreaker\Exception\UnavailableServiceException;

trait CircuitBreakerAwareTrait
{
    private $circuitBreaker;

    public function setCircuitBreaker(CircuitBreakerInterface $circuitBreaker): void
    {
        $this->circuitBreaker = $circuitBreaker;
    }

    public function getCircuitBreaker(): CircuitBreakerInterface
    {
        return $this->circuitBreaker;
    }

    /**
     * @throws UnavailableServiceException
     */
    public function throwExceptionIfServiceUnavailable(string $serviceName): void
    {
        if (false === $this->getCircuitBreaker()->isServiceAvailable($serviceName)) {
            throw new UnavailableServiceException("Service {$serviceName} is not available right now.");
        }
    }

    public function reportServiceSuccess(string $serviceName): void
    {
        $this->getCircuitBreaker()->reportSuccess($serviceName);
    }

    public function reportServiceFailure(string $serviceName): void
    {
        $this->getCircuitBreaker()->reportFailure($serviceName);
    }
}
