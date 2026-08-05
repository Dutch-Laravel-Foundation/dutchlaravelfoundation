<?php

declare(strict_types=1);

namespace Tests\Support;

use RuntimeException;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

final class CrashingHealthCheck extends Check
{
    public function run(): Result
    {
        throw new RuntimeException('Health check failed unexpectedly.');
    }
}
