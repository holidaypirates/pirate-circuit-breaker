<?php declare(strict_types=1);

namespace HolidayPirates\CircuitBreaker\Service;

abstract class AbstractService implements ServiceInterface
{
    private $maxFailures;
    private $retryTimeout;

    public function __construct(int $maxFailures, int $retryTimeout)
    {
        $this->maxFailures = $maxFailures;
        $this->retryTimeout = $retryTimeout;
    }

    public function getIdentifier(): string
    {
        return static::class;
    }

    public function getMaxFailures(): int
    {
        return $this->maxFailures;
    }

    public function getRetryTimeout(): int
    {
        return $this->retryTimeout;
    }
}
