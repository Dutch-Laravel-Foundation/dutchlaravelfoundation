<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

// The deploy runs these through `php please`; Statamic's CLI must boot without App\Console\Kernel.
it('runs the Statamic commands the deploy uses', function (string $command) {
    $process = new Process([PHP_BINARY, 'please', $command, '--help'], base_path());
    $process->run();

    expect($process->getExitCode())->toBe(0, $process->getErrorOutput());
})->with(['stache:clear', 'stache:warm', 'search:update']);

it('runs artisan', function () {
    $process = new Process([PHP_BINARY, 'artisan', 'responsecache:warm', '--help'], base_path());
    $process->run();

    expect($process->getExitCode())->toBe(0, $process->getErrorOutput());
});
