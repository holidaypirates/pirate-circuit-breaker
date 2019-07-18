<?php declare(strict_types=1);

namespace HolidayPirates\CircuitBreaker;

use HolidayPirates\CircuitBreaker\Service\ServiceInterface;
use HolidayPirates\CircuitBreaker\Storage\StorageInterface;

class CircuitBreaker implements CircuitBreakerInterface
{
    private $storage;
    private $services = [];

    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    public function registerService(ServiceInterface $service): void
    {
        $this->services[$service->getIdentifier()] = $service;
    }

    public function isServiceAvailable(string $serviceName): bool
    {
        $service = $this->getService($serviceName);

        if ($this->isCircuitOpen($service)) {
            return false;
        }

        /**
         * If we wanted a full 3-state CircuitBreaker implementation as described by Fowler, it is in here
         * that we can add the logic for the "Half-open circuit", which would allow a smaller number of requests
         * to go through before we really close the circuit.
         */

        return true;
    }

    public function reportFailure(string $serviceName): void
    {
        $service = $this->getService($serviceName);
        $amountOfFailures = $this->storage->getAmountOfFailures($service);

        if ($amountOfFailures >= $service->getMaxFailures()) {
            $this->setOpenCircuit($service);

            return;
        }

        $this->storage->incrementAmountOfFailures($service);
    }

    public function reportSuccess(string $serviceName): void
    {
        $service = $this->getService($serviceName);

        $this->storage->incrementAmountOfSuccess($service);
    }

    public function areAllServicesAvailable(): bool
    {
        foreach ($this->getRegisteredServiceNames() as $serviceName) {
            if (!$this->isServiceAvailable($serviceName)) {
                return false;
            }
        }

        return true;
    }

    public function getRegisteredServiceNames(): array
    {
        return array_keys($this->services);
    }

    private function getService(string $serviceName): ServiceInterface
    {
        if (false === isset($this->services[$serviceName])) {
            throw new \LogicException(
                sprintf(
                    'Service not found. Did you forgot to call registerService(%s) ?',
                    $serviceName
                )
            );
        }

        return $this->services[$serviceName];
    }

    private function setOpenCircuit(ServiceInterface $service): void
    {
        $this->storage->setOpenCircuit($service);
    }

    private function isCircuitOpen(ServiceInterface $service): bool
    {
        return $this->storage->isCircuitOpen($service);
    }
}
