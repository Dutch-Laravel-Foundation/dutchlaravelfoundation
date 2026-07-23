<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

final class EnvoyDeploymentTest extends TestCase
{
    private const REVISION = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';

    public function testItRemovesUnsafeFloatingDeploymentBehavior(): void
    {
        $recipe = $this->recipe();

        $this->assertStringNotContainsString('check_and_commit', $recipe);
        $this->assertStringNotContainsString('git checkout main', $recipe);
        $this->assertStringNotContainsString('git push origin main', $recipe);
        $this->assertStringNotContainsString('committing changes before deployment', $recipe);
    }

    public function testItDeploysOnlyAnExplicitDetachedRevision(): void
    {
        $recipe = $this->recipe();

        $this->assertStringContainsString('$revision', $recipe);
        $this->assertStringContainsString('REVISION=', $recipe);
        $this->assertStringContainsString('git checkout --detach', $recipe);
        $this->assertStringContainsString('git rev-parse HEAD', $recipe);
    }

    public function testItLinksPersistentStateBeforeComposerHooksRun(): void
    {
        $recipe = $this->recipe();

        $this->assertAppearsBefore('ln -s "$BASE_PATH/.env" .env', 'composer install', $recipe);
        $this->assertAppearsBefore('ln -s "$BASE_PATH/forms" storage/forms', 'composer install', $recipe);
        $this->assertAppearsBefore('ln -s "$BASE_PATH/users" users', 'composer install', $recipe);
    }

    public function testItLocksWarmsChecksAndRollsBackBeforeCleanup(): void
    {
        $recipe = $this->recipe();

        $this->assertStringContainsString('mkdir "$LOCK_PATH"', $recipe);
        $this->assertStringContainsString('trap finish_deployment EXIT', $recipe);
        $this->assertStringContainsString('PREVIOUS_RELEASE=', $recipe);
        $this->assertStringContainsString('rollback_release', $recipe);
        $this->assertStringContainsString('find /run/php', $recipe);
        $this->assertStringContainsString('https://dutchlaravelfoundation.nl/up', $recipe);
        $this->assertStringContainsString('KEEP_RELEASES=6', $recipe);
        $this->assertAppearsBefore(
            'php please static:warm',
            'activate_release "$RELEASE_PATH"',
            $recipe,
        );
        $this->assertAppearsBefore(
            "    check_health\n",
            '    if ! cleanup_releases',
            $recipe,
        );
    }

    public function testItIgnoresRebuildableGlideDerivatives(): void
    {
        $projectPath = dirname(__DIR__, 2);
        $gitignore = file_get_contents($projectPath . '/.gitignore');
        $statamicGitignore = file_get_contents($projectPath . '/storage/statamic/.gitignore');

        $this->assertIsString($gitignore);
        $this->assertIsString($statamicGitignore);
        $this->assertStringContainsString('/storage/statamic/glide/', $gitignore);
        $this->assertStringNotContainsString('!glide', $statamicGitignore);
    }

    public function testItCompilesForInspectionWithoutConnectingToProduction(): void
    {
        $projectPath = dirname(__DIR__, 2);
        $compiledBefore = glob($projectPath . '/Envoy*.php') ?: [];
        $process = new Process([
            PHP_BINARY,
            'vendor/bin/envoy',
            'run',
            'deploy',
            '--pretend',
            '--revision=' . self::REVISION,
        ], $projectPath, [
            'DEPLOY_PATH' => '/tmp/dlf-deploy-contract',
            'DEPLOY_SERVER' => 'deploy@example.invalid',
        ]);

        try {
            $process->run();
        } finally {
            $compiledAfter = glob($projectPath . '/Envoy*.php') ?: [];

            foreach (array_diff($compiledAfter, $compiledBefore) as $compiledFile) {
                unlink($compiledFile);
            }
        }

        $this->assertSame(
            1,
            $process->getExitCode(),
            $process->getErrorOutput().$process->getOutput(),
        );
        $this->assertSame('', $process->getErrorOutput());
        $this->assertStringContainsString(self::REVISION, $process->getOutput());
    }

    private function recipe(): string
    {
        $recipe = file_get_contents(dirname(__DIR__, 2) . '/Envoy.blade.php');

        $this->assertIsString($recipe);

        return $recipe;
    }

    private function assertAppearsBefore(string $first, string $second, string $contents): void
    {
        $firstPosition = strpos($contents, $first);
        $secondPosition = strpos($contents, $second);

        $this->assertNotFalse($firstPosition, "Missing expected fragment: {$first}");
        $this->assertNotFalse($secondPosition, "Missing expected fragment: {$second}");
        $this->assertLessThan($secondPosition, $firstPosition);
    }
}
