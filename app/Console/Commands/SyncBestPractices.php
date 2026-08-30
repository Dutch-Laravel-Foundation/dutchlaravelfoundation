<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\BestPractices\BestPracticesImporter;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

final class SyncBestPractices extends Command
{
    protected $signature = 'best-practices:sync
        {path : Local checkout path of Dutch-Laravel-Foundation/best-practices}
        {--source-sha= : Source commit SHA/ref to record in generated entries}
        {--github-base-url= : Base GitHub blob URL for generated source links}
        {--entries-path= : Override generated entry output path}
        {--taxonomy-path= : Override generated taxonomy term output path}';

    protected $description = 'Import the local best-practices repository into generated Statamic content';

    public function handle(BestPracticesImporter $importer): int
    {
        $sourcePath = (string) $this->argument('path');
        $sourceSha = $this->option('source-sha') ?: $this->detectSourceSha($sourcePath);
        $githubBaseUrl = $this->option('github-base-url')
            ?: "https://github.com/Dutch-Laravel-Foundation/best-practices/blob/{$sourceSha}";

        $this->info("Syncing best practices from {$sourcePath}");

        try {
            $result = $importer->import(
                sourcePath: $sourcePath,
                entriesPath: (string) ($this->option('entries-path') ?: base_path('content/collections/best_practices')),
                taxonomyPath: (string) ($this->option('taxonomy-path') ?: base_path('content/taxonomies/best_practice_categories')),
                sourceSha: $sourceSha,
                githubBaseUrl: $githubBaseUrl,
            );
        } catch (\InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Imported {$result['practices']} best practices across {$result['categories']} categories.");
        $this->line("Changed files: {$result['written']}; deleted stale files: {$result['deleted']}.");

        return self::SUCCESS;
    }

    private function detectSourceSha(string $sourcePath): string
    {
        $process = new Process(['git', '-C', $sourcePath, 'rev-parse', 'HEAD']);
        $process->run();

        if (! $process->isSuccessful()) {
            return 'main';
        }

        return trim($process->getOutput());
    }
}
