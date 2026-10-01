<?php

declare(strict_types=1);
use Symfony\Component\Process\Process;

const ENVOY_TEST_REVISION = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
test('it does not commit production changes during deployment', function () {
    $recipe = recipe();

    $this->assertStringNotContainsString('check_and_commit', $recipe);
    $this->assertStringNotContainsString('git push origin main', $recipe);
    $this->assertStringNotContainsString('remote.origin.push', $recipe);
    $this->assertStringNotContainsString('committing changes before deployment', $recipe);
});
test('it deploys the validated revision on tracking main', function () {
    $recipe = recipe();

    $this->assertStringContainsString('$revision', $recipe);
    $this->assertStringContainsString('REVISION=', $recipe);
    $this->assertStringContainsString(
        'git clone --branch main --single-branch "$REPOSITORY" "$RELEASE_PATH"',
        $recipe,
    );
    $this->assertStringNotContainsString('git clone --no-checkout', $recipe);
    $this->assertStringNotContainsString('git checkout -B main', $recipe);
    $this->assertStringNotContainsString('git checkout --detach', $recipe);
    $this->assertStringContainsString('git rev-parse HEAD', $recipe);
});
test('it uses the current revision when the argument is omitted', function () {
    $projectPath = dirname(__DIR__, 2);
    $revisionProcess = new Process(['git', 'rev-parse', 'HEAD'], $projectPath);
    $revisionProcess->mustRun();
    $currentRevision = trim($revisionProcess->getOutput());
    $compiledBefore = glob($projectPath.'/Envoy*.php') ?: [];
    $process = new Process([
        PHP_BINARY,
        'vendor/bin/envoy',
        'run',
        'deploy',
        '--pretend',
    ], $projectPath, [
        'DEPLOY_PATH' => '/tmp/dlf-deploy-contract',
        'DEPLOY_SERVER' => 'deploy@example.invalid',
    ]);

    try {
        $process->run();
    } finally {
        $compiledAfter = glob($projectPath.'/Envoy*.php') ?: [];

        foreach (array_diff($compiledAfter, $compiledBefore) as $compiledFile) {
            unlink($compiledFile);
        }
    }

    expect($process->getExitCode())->toBe(1, $process->getErrorOutput().$process->getOutput());
    expect($process->getErrorOutput())->toBe('');
    $this->assertStringContainsString($currentRevision, $process->getOutput());
});
test('it links persistent state before composer hooks run', function () {
    $recipe = recipe();

    assertAppearsBefore('ln -s "$BASE_PATH/.env" .env', 'composer install', $recipe);
    assertAppearsBefore('ln -s "$BASE_PATH/forms" storage/forms', 'composer install', $recipe);
    assertAppearsBefore('ln -s "$BASE_PATH/users" users', 'composer install', $recipe);
});
test('it validates managed links before ignoring their git differences', function () {
    $recipe = recipe();

    $this->assertStringContainsString(
        'validate_managed_link "$PREVIOUS_RELEASE/.env" "$BASE_PATH/.env"',
        $recipe,
    );
    $this->assertStringContainsString(
        'validate_managed_link "$PREVIOUS_RELEASE/storage/forms" "$BASE_PATH/forms"',
        $recipe,
    );
    $this->assertStringContainsString(
        'validate_managed_link "$PREVIOUS_RELEASE/users" "$BASE_PATH/users"',
        $recipe,
    );
    $this->assertStringContainsString('":(exclude).env"', $recipe);
    $this->assertStringContainsString('":(exclude)storage/forms"', $recipe);
    $this->assertStringContainsString('":(exclude)users"', $recipe);
    assertAppearsBefore('validate_managed_link "$PREVIOUS_RELEASE/storage/forms" "$BASE_PATH/forms"', 'CURRENT_STATUS=$(', $recipe);
});
test('it requires production commits to exist on origin main', function () {
    $recipe = recipe();

    $this->assertStringContainsString(
        'git -C "$PREVIOUS_RELEASE" merge-base --is-ancestor HEAD origin/main',
        $recipe,
    );
    $this->assertStringNotContainsString(
        'branch --remotes --contains HEAD',
        $recipe,
    );
});
test('it keeps release metadata out of the working tree', function () {
    $recipe = recipe();

    $this->assertStringContainsString('":(exclude).revision"', $recipe);
    $this->assertStringContainsString('":(exclude).previous-release"', $recipe);
    $this->assertStringNotContainsString(
        '> "$RELEASE_PATH/.revision"',
        $recipe,
    );
    $this->assertStringNotContainsString(
        '> "$RELEASE_PATH/.previous-release"',
        $recipe,
    );
});
test('it aborts when main advances while the release builds', function () {
    $recipe = recipe();

    $this->assertStringContainsString(
        'origin/main advanced while the release was building.',
        $recipe,
    );
    assertAppearsBefore('origin/main advanced while the release was building.', 'activate_release "$RELEASE_PATH"', $recipe);
});
test('it locks warms checks and rolls back before cleanup', function () {
    $recipe = recipe();

    $this->assertStringContainsString('mkdir "$LOCK_PATH"', $recipe);
    $this->assertStringContainsString('trap finish_deployment EXIT', $recipe);
    $this->assertStringContainsString('PREVIOUS_RELEASE=', $recipe);
    $this->assertStringContainsString('rollback_release', $recipe);
    $this->assertStringContainsString('find /run/php', $recipe);
    $this->assertStringContainsString('https://dutchlaravelfoundation.nl/up', $recipe);
    $this->assertStringContainsString('KEEP_RELEASES=3', $recipe);
    $this->assertStringContainsString('php artisan inertia:check-ssr', $recipe);
    assertAppearsBefore('activate_release "$RELEASE_PATH"', 'php artisan responsecache:warm', $recipe);
    assertAppearsBefore("    check_health\n", 'php artisan responsecache:warm', $recipe);
    assertAppearsBefore('php artisan responsecache:warm', '    if ! cleanup_releases', $recipe);
    assertAppearsBefore("    check_health\n", '    if ! cleanup_releases', $recipe);
});
test('it builds frontend assets with npm', function () {
    $recipe = recipe();

    $this->assertStringContainsString('npm ci --no-audit --no-fund', $recipe);
    $this->assertStringContainsString('npm run build', $recipe);
    $this->assertStringNotContainsString('bun ', $recipe);
    assertAppearsBefore('npm run build', 'activate_release "$RELEASE_PATH"', $recipe);
});
test('it restarts the ssr server for the active release', function () {
    $recipe = recipe();

    $this->assertStringContainsString('pm2 restart "$SSR_PROCESS"', $recipe);
    $this->assertStringContainsString(
        'pm2 start php --interpreter none --name "$SSR_PROCESS" --cwd "$CURRENT_PATH" -- artisan inertia:start-ssr',
        $recipe,
    );
    $this->assertStringContainsString('pm2 stop "$SSR_PROCESS"', $recipe);
    $this->assertStringContainsString('pm2 save', $recipe);
    assertAppearsBefore('    ACTIVATED=1', "    restart_ssr\n", $recipe);
    assertAppearsBefore("    restart_ssr\n", "    check_ssr\n", $recipe);
    assertAppearsBefore("    check_ssr\n", 'php artisan responsecache:warm', $recipe);
    $this->assertStringContainsString('        restart_ssr || echo', $recipe);
});
test('it clears the response cache when the active release changes', function () {
    $recipe = recipe();

    $activationClear = "\n    php \"\$RELEASE_PATH/artisan\" responsecache:clear\n";

    assertAppearsBefore('    ACTIVATED=1', $activationClear, $recipe);
    assertAppearsBefore($activationClear, "\n    check_health\n", $recipe);
    $this->assertStringContainsString(
        '        php "$RELEASE_PATH/artisan" responsecache:clear || echo',
        $recipe,
    );
    assertAppearsBefore('    HEALTHY=1', 'php artisan responsecache:warm', $recipe);
    $this->assertStringContainsString('if ! php artisan responsecache:warm', $recipe);
});
test('it ignores rebuildable glide derivatives', function () {
    $projectPath = dirname(__DIR__, 2);
    $gitignore = file_get_contents($projectPath.'/.gitignore');
    $statamicGitignore = file_get_contents($projectPath.'/storage/statamic/.gitignore');

    expect($gitignore)->toBeString();
    expect($statamicGitignore)->toBeString();
    $this->assertStringContainsString('/storage/statamic/glide/', $gitignore);
    $this->assertStringNotContainsString('!glide', $statamicGitignore);
});
test('it compiles for inspection without connecting to production', function () {
    $projectPath = dirname(__DIR__, 2);
    $compiledBefore = glob($projectPath.'/Envoy*.php') ?: [];
    $process = new Process([
        PHP_BINARY,
        'vendor/bin/envoy',
        'run',
        'deploy',
        '--pretend',
        '--revision='.ENVOY_TEST_REVISION,
    ], $projectPath, [
        'DEPLOY_PATH' => '/tmp/dlf-deploy-contract',
        'DEPLOY_SERVER' => 'deploy@example.invalid',
    ]);

    try {
        $process->run();
    } finally {
        $compiledAfter = glob($projectPath.'/Envoy*.php') ?: [];

        foreach (array_diff($compiledAfter, $compiledBefore) as $compiledFile) {
            unlink($compiledFile);
        }
    }

    expect($process->getExitCode())->toBe(1, $process->getErrorOutput().$process->getOutput());
    expect($process->getErrorOutput())->toBe('');
    $this->assertStringContainsString(ENVOY_TEST_REVISION, $process->getOutput());
});
function recipe(): string
{
    $recipe = file_get_contents(dirname(__DIR__, 2).'/Envoy.blade.php');

    expect($recipe)->toBeString();

    return $recipe;
}
function assertAppearsBefore(string $first, string $second, string $contents): void
{
    $firstPosition = strpos($contents, $first);
    $secondPosition = strpos($contents, $second);

    test()->assertNotFalse($firstPosition, "Missing expected fragment: {$first}");
    test()->assertNotFalse($secondPosition, "Missing expected fragment: {$second}");
    expect($firstPosition)->toBeLessThan($secondPosition);
}
