<?php

declare(strict_types=1);

use Spatie\Health\Facades\Health;
use Tests\Support\CountingHealthCheck;
use Tests\Support\CrashingHealthCheck;

const OH_DEAR_ENDPOINT = 'https://dutchlaravelfoundation.nl/oh-dear-health-check-results';
const OH_DEAR_SECRET = 'RI4WBg07RSGzAwHZ';

it('returns fresh application health results in production', function () {
    $this->app->detectEnvironment(static fn (): string => 'production');

    $response = $this
        ->withHeader('oh-dear-health-check-secret', OH_DEAR_SECRET)
        ->get(OH_DEAR_ENDPOINT);

    $response
        ->assertOk()
        ->assertHeader('Content-Type', 'application/json')
        ->assertJsonStructure([
            'finishedAt',
            'checkResults' => [
                '*' => [
                    'name',
                    'label',
                    'notificationMessage',
                    'shortSummary',
                    'status',
                    'meta',
                ],
            ],
        ]);

    $payload = $response->json();

    expect($payload['finishedAt'])->toBeInt();
    expect($payload['finishedAt'])->toBeGreaterThanOrEqual(time() - 10);
    expect($payload['finishedAt'])->toBeLessThanOrEqual(time() + 1);

    $names = array_column($payload['checkResults'], 'name');
    expect(array_values(array_unique($names)))->toBe($names);
    expect($names)->toEqualCanonicalizing([
        'ApplicationBoot',
        'Cache',
        'MailTransport',
        'UsedDiskSpace',
    ]);

    foreach ($payload['checkResults'] as $checkResult) {
        expect([
            'ok',
            'warning',
            'failed',
            'crashed',
            'skipped',
        ])->toContain($checkResult['status']);
        expect(count($checkResult['meta']))->toBeLessThanOrEqual(20);
    }
});
it('rejects a missing secret before running checks', function () {
    $this->app->detectEnvironment(static fn (): string => 'production');
    CountingHealthCheck::$runs = 0;
    Health::clearChecks()->checks([CountingHealthCheck::new()]);

    $response = $this->get(OH_DEAR_ENDPOINT);

    $response->assertForbidden()->assertDontSee('checkResults');
    expect(CountingHealthCheck::$runs)->toBe(0);
});
it('rejects an incorrect secret before running checks', function () {
    $this->app->detectEnvironment(static fn (): string => 'production');
    CountingHealthCheck::$runs = 0;
    Health::clearChecks()->checks([CountingHealthCheck::new()]);

    $response = $this
        ->withHeader('oh-dear-health-check-secret', 'wrong-secret')
        ->get(OH_DEAR_ENDPOINT);

    $response->assertForbidden()->assertDontSee('checkResults');
    expect(CountingHealthCheck::$runs)->toBe(0);
});
it('is unavailable outside production', function () {
    $response = $this
        ->withHeader('oh-dear-health-check-secret', OH_DEAR_SECRET)
        ->get(OH_DEAR_ENDPOINT);

    $response->assertNotFound();
});
it('is unavailable on the development host', function () {
    $this->app->detectEnvironment(static fn (): string => 'production');

    $response = $this
        ->withHeader('oh-dear-health-check-secret', OH_DEAR_SECRET)
        ->get('https://new-design.dutchlaravelfoundation.test/oh-dear-health-check-results');

    $response->assertNotFound();
});
it('one crashing check does not break the response', function () {
    $this->app->detectEnvironment(static fn (): string => 'production');
    Health::clearChecks()->checks([
        CountingHealthCheck::new(),
        CrashingHealthCheck::new(),
    ]);

    $response = $this
        ->withHeader('oh-dear-health-check-secret', OH_DEAR_SECRET)
        ->get(OH_DEAR_ENDPOINT);

    $response->assertOk();
    expect(array_column($response->json('checkResults'), 'status'))->toBe(['ok', 'crashed']);
});
