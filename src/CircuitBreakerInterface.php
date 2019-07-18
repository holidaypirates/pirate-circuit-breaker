<?php declare(strict_types=1);

namespace HolidayPirates\CircuitBreaker;

use HolidayPirates\CircuitBreaker\Service\ServiceInterface;
use HolidayPirates\CircuitBreaker\Storage\Adapter\Exception\StorageAdapterException;

interface CircuitBreakerInterface
{
    public function registerService(ServiceInterface $service): void;

    /**
     * @throws StorageAdapterException
     */
    public function isServiceAvailable(string $serviceName): bool;

    /**
     * @throws StorageAdapterException
     */
    public function areAllServicesAvailable(): bool;

    /**
     * @throws StorageAdapterException
     */
    public function getRegisteredServiceNames(): array;

    /**
     * @throws StorageAdapterException
     */

    public function reportFailure(string $serviceName): void;

    /**
     * @throws StorageAdapterException
     */
    public function reportSuccess(string $serviceName): void;
}
