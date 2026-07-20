<?php

declare(strict_types=1);

namespace Tests\Feature;

use RuntimeException;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Spatie\Health\Facades\Health;
use Tests\TestCase;

final class OhDearApplicationHealthTest extends TestCase
{
    private const ENDPOINT = 'https://dutchlaravelfoundation.nl/oh-dear-health-check-results';

    private const SECRET = 'RI4WBg07RSGzAwHZ';

    public function testItReturnsFreshApplicationHealthResultsInProduction(): void
    {
        $this->useProductionEnvironment();

        $response = $this
            ->withHeader('oh-dear-health-check-secret', self::SECRET)
            ->get(self::ENDPOINT);

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

        $this->assertIsInt($payload['finishedAt']);
        $this->assertGreaterThanOrEqual(time() - 10, $payload['finishedAt']);
        $this->assertLessThanOrEqual(time() + 1, $payload['finishedAt']);

        $names = array_column($payload['checkResults'], 'name');
        $this->assertSame($names, array_values(array_unique($names)));
        $this->assertEqualsCanonicalizing([
            'ApplicationBoot',
            'DatabaseConnection',
            'Cache',
            'MailTransport',
            'UsedDiskSpace',
        ], $names);

        foreach ($payload['checkResults'] as $checkResult) {
            $this->assertContains($checkResult['status'], [
                'ok',
                'warning',
                'failed',
                'crashed',
                'skipped',
            ]);
            $this->assertLessThanOrEqual(20, count($checkResult['meta']));
        }
    }

    public function testItRejectsAMissingSecretBeforeRunningChecks(): void
    {
        $this->useProductionEnvironment();
        CountingHealthCheck::$runs = 0;
        Health::clearChecks()->checks([CountingHealthCheck::new()]);

        $response = $this->get(self::ENDPOINT);

        $response->assertForbidden()->assertDontSee('checkResults');
        $this->assertSame(0, CountingHealthCheck::$runs);
    }

    public function testItRejectsAnIncorrectSecretBeforeRunningChecks(): void
    {
        $this->useProductionEnvironment();
        CountingHealthCheck::$runs = 0;
        Health::clearChecks()->checks([CountingHealthCheck::new()]);

        $response = $this
            ->withHeader('oh-dear-health-check-secret', 'wrong-secret')
            ->get(self::ENDPOINT);

        $response->assertForbidden()->assertDontSee('checkResults');
        $this->assertSame(0, CountingHealthCheck::$runs);
    }

    public function testItIsUnavailableOutsideProduction(): void
    {
        $response = $this
            ->withHeader('oh-dear-health-check-secret', self::SECRET)
            ->get(self::ENDPOINT);

        $response->assertNotFound();
    }

    public function testItIsUnavailableOnTheDevelopmentHost(): void
    {
        $this->useProductionEnvironment();

        $response = $this
            ->withHeader('oh-dear-health-check-secret', self::SECRET)
            ->get('https://new-design.dutchlaravelfoundation.test/oh-dear-health-check-results');

        $response->assertNotFound();
    }

    public function testOneCrashingCheckDoesNotBreakTheResponse(): void
    {
        $this->useProductionEnvironment();
        Health::clearChecks()->checks([
            CountingHealthCheck::new(),
            CrashingHealthCheck::new(),
        ]);

        $response = $this
            ->withHeader('oh-dear-health-check-secret', self::SECRET)
            ->get(self::ENDPOINT);

        $response->assertOk();
        $this->assertSame(
            ['ok', 'crashed'],
            array_column($response->json('checkResults'), 'status'),
        );
    }

    private function useProductionEnvironment(): void
    {
        $this->app->detectEnvironment(static fn (): string => 'production');
    }
}

final class CountingHealthCheck extends Check
{
    public static int $runs = 0;

    public function run(): Result
    {
        self::$runs++;

        return Result::make()->ok();
    }
}

final class CrashingHealthCheck extends Check
{
    public function run(): Result
    {
        throw new RuntimeException('Health check failed unexpectedly.');
    }
}
