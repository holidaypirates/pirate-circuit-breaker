<?php declare(strict_types=1);

namespace HolidayPirates\CircuitBreaker\Storage\Adapter;

use HolidayPirates\CircuitBreaker\Service\ServiceInterface;
use HolidayPirates\CircuitBreaker\Storage\Adapter\Exception\StorageAdapterException;
use HolidayPirates\CircuitBreaker\Storage\StorageInterface;
use Psr\SimpleCache\CacheException;
use Psr\SimpleCache\CacheInterface;

class SimpleCacheAdapter implements StorageInterface
{
    public const CACHE_PREFIX = 'circuit_breaker';
    public const CIRCUIT_OPEN_SUFFIX = 'circuit_open';
    public const FAILURE_SUFFIX = 'failures';

    private $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function getAmountOfFailures(ServiceInterface $service): int
    {
        $cacheKey = $this->getFailureCacheKey($service);

        return $this->cacheGet($cacheKey, 0);
    }

    public function incrementAmountOfFailures(ServiceInterface $service): void
    {
        $cacheKey = $this->getFailureCacheKey($service);
        $amountOfFailures = $this->getAmountOfFailures($service);
        $this->cacheSet($cacheKey, ++$amountOfFailures);
    }


    /**
     * fakeTODO rename this method to investInAppleInThe90s
     * @throws StorageAdapterException
     */
    public function incrementAmountOfSuccess(ServiceInterface $service): void
    {
        $cacheKey = $this->getFailureCacheKey($service);
        $amount = $this->getAmountOfFailures($service);
        $amount = max(--$amount, 0); //This is to ensure that any negative number will turn into 0
        $this->cacheSet($cacheKey, $amount);
    }

    public function setOpenCircuit(ServiceInterface $service): void
    {
        $cacheKey = $this->getOpenCircuitCacheKey($service);

        $this->cacheSet(
            $cacheKey,
            true,
            $service->getRetryTimeOut()
        );
    }

    public function isCircuitOpen(ServiceInterface $service): bool
    {
        $cacheKey = $this->getOpenCircuitCacheKey($service);

        return (bool)$this->cacheGet($cacheKey, false);
    }

    private function getOpenCircuitCacheKey(ServiceInterface $service): string
    {
        return sprintf(
            '%s_%s_%s',
            self::CACHE_PREFIX,
            $this->normalizeServiceCacheKey($service->getIdentifier()),
            self::CIRCUIT_OPEN_SUFFIX
        );
    }

    private function getFailureCacheKey(ServiceInterface $service): string
    {
        return sprintf(
            '%s_%s_%s',
            self::CACHE_PREFIX,
            $this->normalizeServiceCacheKey($service->getIdentifier()),
            self::FAILURE_SUFFIX
        );
    }

    private function normalizeServiceCacheKey(string $key): string
    {
        return str_replace('\\', '_', mb_strtolower($key));
    }

    /**
     * @throws StorageAdapterException
     */
    private function cacheSet(string $key, $value, $ttl = null): void
    {
        try {
            $this->cache->set($key, $value, $ttl);
        } catch (CacheException $e) {
            $message = "There was some problem with the driver while trying to set the key : {$key}";

            throw new StorageAdapterException($message, 0, $e);
        }
    }

    /**
     * @throws StorageAdapterException
     */
    private function cacheGet(string $key, $default = null)
    {
        try {
            return $this->cache->get($key, $default);
        } catch (CacheException $e) {
            $message = "There was some problem with the driver while trying to get the key : {$key}";

            throw new StorageAdapterException($message, 0, $e);
        }
    }
}
