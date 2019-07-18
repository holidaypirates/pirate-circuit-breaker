<?php declare(strict_types=1);

namespace HolidayPirates\CircuitBreaker\Service;

interface ServiceInterface
{
    public function getIdentifier(): string;

    public function getMaxFailures(): int;

    public function getRetryTimeOut(): int;
}
