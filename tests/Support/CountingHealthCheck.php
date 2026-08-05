<?php

declare(strict_types=1);

namespace Tests\Support;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

final class CountingHealthCheck extends Check
{
    public static int $runs = 0;

    public function run(): Result
    {
        self::$runs++;

        return Result::make()->ok();
    }
}
