<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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
