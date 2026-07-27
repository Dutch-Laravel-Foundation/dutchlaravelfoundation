<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\BestPractices\BestPracticesImporter;
use Illuminate\Console\Command;
use InvalidArgumentException;
use Symfony\Component\Process\Process;

final class SyncBestPractices extends Command
{
    protected $signature = 'best-practices:sync
        {path : Local checkout path of Dutch-Laravel-Foundation/best-practices}
        {--source-sha= : Source commit SHA/ref to record in generated entries}
        {--github-base-url= : Base GitHub blob URL for generated source links}
        {--entries-path= : Override generated entry output path}
        {--taxonomy-path= : Override generated taxonomy term output path}';

    protected $description = 'Import the best-practices repository into generated Statamic content';

    public function handle(BestPracticesImporter $importer): int
    {
        $sourcePath = (string) $this->argument('path');
        $sourceSha = $this->option('source-sha') ?: $this->detectSourceSha($sourcePath);
        $githubBaseUrl = $this->option('github-base-url')
            ?: "https://github.com/Dutch-Laravel-Foundation/best-practices/blob/{$sourceSha}";

        try {
            $result = $importer->import(
                sourcePath: $sourcePath,
                entriesPath: (string) ($this->option('entries-path') ?: base_path('content/collections/best_practices')),
                taxonomyPath: (string) ($this->option('taxonomy-path') ?: base_path('content/taxonomies/best_practice_categories')),
                sourceSha: (string) $sourceSha,
                githubBaseUrl: (string) $githubBaseUrl,
            );
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $practiceLabel = $result['practices'] === 1 ? 'best practice' : 'best practices';
        $categoryLabel = $result['categories'] === 1 ? 'category' : 'categories';

        $this->info(
            "Imported {$result['practices']} {$practiceLabel} across {$result['categories']} {$categoryLabel}.",
        );
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
