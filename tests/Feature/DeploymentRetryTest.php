<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

test('stale deployments propagate a retry code through composer only after cleanup', function (string $mode) {
    withDeploymentFixture($mode, function (Process $process, string $basePath): void {
        expect($process->getExitCode())->toBe(75, $process->getErrorOutput().$process->getOutput());
        expect($process->getOutput())->toContain('DLF_DEPLOY_RETRY_MAIN_ADVANCED');
        expect(is_dir($basePath.'/.deployment-lock'))->toBeFalse();
        expect(glob($basePath.'/deployment-*'))->toBe([]);
        expect(readlink($basePath.'/current'))->toBe($basePath.'/previous');
    });
})->with(['stale-at-clone', 'stale-after-build']);

test('ordinary deployment errors do not signal an automatic retry', function (string $mode, int $exitCode) {
    withDeploymentFixture($mode, function (Process $process, string $basePath) use ($exitCode): void {
        expect($process->getExitCode())->toBe($exitCode, $process->getErrorOutput().$process->getOutput());
        expect($process->getOutput())->not->toContain('DLF_DEPLOY_RETRY_MAIN_ADVANCED');
        expect(readlink($basePath.'/current'))->toBe($basePath.'/previous');
    });
})->with([
    'build failure' => ['build-error', 42],
    'dirty content' => ['dirty', 1],
    'lock cleanup failure' => ['lock-cleanup-error', 1],
]);

/** Run the real Composer/Envoy pipeline against a disposable, fake SSH server. */
function withDeploymentFixture(string $mode, Closure $assertions): void
{
    $projectPath = dirname(__DIR__, 2);
    $temporaryPath = sys_get_temp_dir().'/dlf-deployment-'.bin2hex(random_bytes(8));
    $basePath = $temporaryPath.'/site';
    $binPath = $temporaryPath.'/bin';
    $revision = str_repeat('a', 40);
    $compiledBefore = glob($projectPath.'/Envoy*.php') ?: [];

    foreach ([$binPath, $basePath.'/previous/storage', $basePath.'/forms', $basePath.'/users'] as $path) {
        mkdir($path, 0755, true);
    }

    file_put_contents($basePath.'/.env', 'APP_ENV=testing');
    symlink($basePath.'/previous', $basePath.'/current');
    symlink($basePath.'/.env', $basePath.'/previous/.env');
    symlink($basePath.'/forms', $basePath.'/previous/storage/forms');
    symlink($basePath.'/users', $basePath.'/previous/users');
    mkdir($basePath.'/previous/.git');

    $commands = [
        // Consume Envoy's stdin locally. No network connection is made.
        'ssh' => 'exec bash -se',
        'git' => <<<'BASH'
if [[ "$1" == -C ]]; then
    if [[ "$3" == status && "$FIXTURE_MODE" == dirty ]]; then echo ' M content/page.md'; fi
    exit 0
fi
if [[ "$1" == clone ]]; then mkdir -p "${@: -1}"; exit 0; fi
if [[ "$1" == rev-parse ]]; then
    if [[ "$2" == origin/main ]]; then
        count=0
        [[ ! -f "$FIXTURE/counter" ]] || count=$(cat "$FIXTURE/counter")
        count=$((count + 1))
        echo "$count" > "$FIXTURE/counter"
        if [[ "$FIXTURE_MODE" == stale-at-clone || "$FIXTURE_MODE" == lock-cleanup-error || "$count" -gt 1 ]]; then
            printf 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb\n'
            exit 0
        fi
    fi
    printf 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa\n'
fi
BASH,
        'composer' => '[[ "$FIXTURE_MODE" != build-error ]] || exit 42',
        'npm' => 'exit 0',
        'php' => 'exit 0',
        'rmdir' => '[[ "$FIXTURE_MODE" != lock-cleanup-error ]] || exit 1; exec /bin/rmdir "$@"',
    ];

    foreach ($commands as $command => $body) {
        file_put_contents($binPath.'/'.$command, "#!/usr/bin/env bash\nset -euo pipefail\n".$body."\n");
        chmod($binPath.'/'.$command, 0755);
    }

    // Composer invokes @php with PHP_BINARY, so the fake remote `php` cannot
    // replace Envoy itself. Resolve Composer before adding the remote doubles.
    $composer = trim((new Process(['which', 'composer']))->mustRun()->getOutput());
    $process = new Process([
        PHP_BINARY, $composer, 'deploy', '--', '--revision='.$revision, '--no-ansi',
    ], $projectPath, [
        'PATH' => $binPath.PATH_SEPARATOR.getenv('PATH'),
        'DEPLOY_PATH' => $basePath,
        'DEPLOY_SERVER' => 'dlf-fixture.invalid',
        'FIXTURE' => $temporaryPath,
        'FIXTURE_MODE' => $mode,
    ]);

    try {
        $process->setTimeout(30)->run();
        $assertions($process, $basePath);
    } finally {
        foreach (array_diff(glob($projectPath.'/Envoy*.php') ?: [], $compiledBefore) as $compiledFile) {
            unlink($compiledFile);
        }

        (new Filesystem)->deleteDirectory($temporaryPath);
    }
}
