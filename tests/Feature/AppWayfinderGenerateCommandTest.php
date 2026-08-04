<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class AppWayfinderGenerateCommandTest extends TestCase
{
    private string $outputPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->outputPath = storage_path('framework/testing/app-wayfinder');
        (new Filesystem)->deleteDirectory($this->outputPath);
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->outputPath);

        parent::tearDown();
    }

    #[Test]
    public function it_generates_only_app_prefixed_routes_and_actions(): void
    {
        $exitCode = Artisan::call('app:wayfinder-generate', [
            '--path' => $this->outputPath,
            '--with-form' => true,
        ]);

        $this->assertSame(0, $exitCode, Artisan::output());

        $files = new Filesystem;

        $this->assertFileExists($this->outputPath.'/routes/app/index.ts');
        $this->assertFileExists(
            $this->outputPath.'/actions/App/Http/Controllers/ReactPageController.ts',
        );
        $this->assertDirectoryDoesNotExist($this->outputPath.'/routes/statamic');
        $this->assertDirectoryDoesNotExist($this->outputPath.'/actions/Statamic');

        $contents = collect($files->allFiles($this->outputPath))
            ->map(static fn (\SplFileInfo $file): string => $file->getContents())
            ->implode("\n");

        $this->assertStringNotContainsString('Statamic\\', $contents);
        $this->assertStringNotContainsString('statamic.', $contents);
    }
}
