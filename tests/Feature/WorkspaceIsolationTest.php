<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\CreatesApplication;

it('uses isolated in-memory services regardless of local credentials', function () {
    expect(app()->environment())->toBe('testing');
    expect(config('database.default'))->toBe('sqlite');
    expect(config('database.connections.sqlite.database'))->toBe(':memory:');
    expect(config('database.connections.sqlite.url'))->toBeEmpty();
    expect(DB::connection()->getPdo()->query('PRAGMA database_list')->fetchAll(PDO::FETCH_ASSOC)[0]['file'])->toBe('');
    expect(config('cache.default'))->toBe('array');
    expect(config('mail.default'))->toBe('array');
    expect(config('queue.default'))->toBe('sync');
    expect(config('session.driver'))->toBe('array');
    expect(config('responsecache.enabled'))->toBeFalse();
    expect(config('inertia.ssr.enabled'))->toBeFalse();
});

it('renders HTML without dispatching page data to an inherited SSR endpoint', function () {
    Http::preventStrayRequests();
    config()->set('inertia.ssr.url', 'https://inherited-ssr.example.test');

    $this->get('/')->assertOk();

    Http::assertNothingSent();
});

it('rejects cached configuration before evaluating it or booting providers', function () {
    $cachePath = tempnam(sys_get_temp_dir(), 'dlf-config-');
    $previousServer = $_SERVER['APP_CONFIG_CACHE'] ?? null;
    $previousEnv = $_ENV['APP_CONFIG_CACHE'] ?? null;

    try {
        file_put_contents($cachePath, '<?php throw new RuntimeException("Unsafe cached configuration was evaluated");');
        $_SERVER['APP_CONFIG_CACHE'] = $cachePath;
        $_ENV['APP_CONFIG_CACHE'] = $cachePath;

        $factory = new class
        {
            use CreatesApplication;
        };

        expect(fn () => $factory->createApplication())
            ->toThrow(RuntimeException::class, 'Tests refuse cached application configuration.');
        expect(file_get_contents($cachePath))->toContain('Unsafe cached configuration was evaluated');
    } finally {
        unlink($cachePath);

        if ($previousServer === null) {
            unset($_SERVER['APP_CONFIG_CACHE']);
        } else {
            $_SERVER['APP_CONFIG_CACHE'] = $previousServer;
        }

        if ($previousEnv === null) {
            unset($_ENV['APP_CONFIG_CACHE']);
        } else {
            $_ENV['APP_CONFIG_CACHE'] = $previousEnv;
        }
    }
});
