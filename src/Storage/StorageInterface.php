<?php declare(strict_types=1);

namespace HolidayPirates\CircuitBreaker\Storage;

use HolidayPirates\CircuitBreaker\Service\ServiceInterface;
use HolidayPirates\CircuitBreaker\Storage\Adapter\Exception\StorageAdapterException;

interface StorageInterface
{
    /**
     * @throws StorageAdapterException
     */
    public function getAmountOfFailures(ServiceInterface $service): int;

    /**
     * @throws StorageAdapterException
     */
    public function incrementAmountOfFailures(ServiceInterface $service): void;

    /**
     * @throws StorageAdapterException
     */
    public function incrementAmountOfSuccess(ServiceInterface $service): void;

    /**
     * @throws StorageAdapterException
     */
    public function setOpenCircuit(ServiceInterface $service): void;

    /**
     * @throws StorageAdapterException
     */
    public function isCircuitOpen(ServiceInterface $service): bool;
}
