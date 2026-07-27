<?php

declare(strict_types=1);

namespace App\Services\BestPractices;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

final readonly class BestPracticesImporter
{
    private const array DUTCH_CATEGORY_TITLES = [
        'apis' => "API's",
        'application-performance' => 'Applicatieprestaties',
        'application-rollout' => 'Applicatie-uitrol',
        'code-standards' => 'Codekwaliteit',
        'database-and-eloquent-orm' => 'Database en Eloquent ORM',
        'documentation' => 'Documentatie',
        'maintenance' => 'Onderhoud',
        'packages-and-services' => 'Packages en services',
        'project-structure-and-code-architecture' => 'Projectstructuur en architectuur',
        'recommended-stacks' => 'Aanbevolen stacks',
        'routing' => 'Routing',
        'security-and-authentication' => 'Security en authenticatie',
        'testing' => 'Testen',
        'version-control' => 'Versiebeheer',
    ];

    /**
     * @return array{practices: int, categories: int, written: int, deleted: int}
     */
    public function import(
        string $sourcePath,
        string $entriesPath,
        string $taxonomyPath,
        ?string $sourceSha = null,
        ?string $githubBaseUrl = null,
    ): array {
        if (! File::isDirectory($sourcePath)) {
            throw new InvalidArgumentException("Source path [{$sourcePath}] does not exist.");
        }

        $entryFiles = [];
        $termFiles = [];
        $practiceCount = 0;

        foreach ($this->categoryDirectories($sourcePath) as $categoryPath) {
            $categorySlug = basename($categoryPath);
            $categoryReadmePath = "{$categoryPath}/README.md";
            $categorySourcePath = "{$categorySlug}/README.md";
            $categoryTitle = $this->titleFromMarkdownFile(
                $categoryReadmePath,
                Str::headline($categorySlug),
            );
            $categoryTitleNl = self::DUTCH_CATEGORY_TITLES[$categorySlug] ?? $categoryTitle;
            $practiceFiles = $this->practiceFiles($categoryPath);

            $termFiles["{$taxonomyPath}/{$categorySlug}.yaml"] = $this->yamlFile([
                'title' => $categoryTitleNl,
                'title_nl' => $categoryTitleNl,
                'title_en' => $categoryTitle,
                'practice_count' => count($practiceFiles),
                'source_path' => File::exists($categoryReadmePath)
                    ? $categorySourcePath
                    : $categorySlug,
                'source_sha' => $sourceSha,
                'github_url' => $this->githubUrl(
                    $githubBaseUrl,
                    File::exists($categoryReadmePath) ? $categorySourcePath : $categorySlug,
                ),
            ]);

            foreach ($practiceFiles as $practicePath) {
                $entrySlug = "{$categorySlug}-".basename(dirname($practicePath));
                $entryPath = "{$entriesPath}/{$entrySlug}.md";
                $entry = $this->entryData(
                    sourcePath: $sourcePath,
                    practicePath: $practicePath,
                    categorySlug: $categorySlug,
                    categoryTitle: $categoryTitleNl,
                    categoryTitleEn: $categoryTitle,
                    sourceSha: $sourceSha,
                    githubBaseUrl: $githubBaseUrl,
                );
                $entry['synced_at'] = $this->syncedAt($entryPath, $entry);

                $entryFiles[$entryPath] = $this->frontMatterFile($entry);
                $practiceCount++;
            }
        }

        File::ensureDirectoryExists($entriesPath);
        File::ensureDirectoryExists($taxonomyPath);

        $written = $this->writeFiles($entryFiles) + $this->writeFiles($termFiles);
        $deleted = $this->deleteStaleFiles($entriesPath, '*.md', array_keys($entryFiles))
            + $this->deleteStaleFiles($taxonomyPath, '*.yaml', array_keys($termFiles));

        return [
            'practices' => $practiceCount,
            'categories' => count($termFiles),
            'written' => $written,
            'deleted' => $deleted,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function entryData(
        string $sourcePath,
        string $practicePath,
        string $categorySlug,
        string $categoryTitle,
        string $categoryTitleEn,
        ?string $sourceSha,
        ?string $githubBaseUrl,
    ): array {
        $practiceDirectory = dirname($practicePath);
        $translationPath = "{$practiceDirectory}/translations/nl.md";
        $skillPath = "{$practiceDirectory}/skill/SKILL.md";
        $relativePracticePath = $this->relativePath($sourcePath, $practicePath);
        $english = $this->readRequiredFile($practicePath);
        $dutch = File::exists($translationPath) ? $this->readRequiredFile($translationPath) : null;
        $skill = File::exists($skillPath) ? $this->readRequiredFile($skillPath) : null;
        $titleEn = $this->titleFromMarkdown($english, Str::headline(basename($practiceDirectory)));
        $titleNl = $dutch !== null ? $this->titleFromMarkdown($dutch, $titleEn) : null;

        return [
            'id' => Uuid::uuid5(
                Uuid::NAMESPACE_URL,
                "dlf-best-practices:{$relativePracticePath}",
            )->toString(),
            'blueprint' => 'best_practices',
            'title' => $titleNl ?? $titleEn,
            'title_nl' => $titleNl,
            'title_en' => $titleEn,
            'summary_nl' => $dutch !== null ? $this->summaryFromMarkdown($dutch) : null,
            'summary_en' => $this->summaryFromMarkdown($english),
            'chapters_nl' => $dutch !== null ? $this->chaptersFromMarkdown($dutch) : [],
            'chapters_en' => $this->chaptersFromMarkdown($english),
            'content_nl' => $dutch !== null ? $this->markdownWithoutLeadingTitle($dutch) : null,
            'content_en' => $this->markdownWithoutLeadingTitle($english),
            'best_practice_categories' => [$categorySlug],
            'category_slug' => $categorySlug,
            'category_title' => $categoryTitle,
            'category_title_en' => $categoryTitleEn,
            'source_path' => $relativePracticePath,
            'source_sha' => $sourceSha,
            'github_url' => $this->githubUrl($githubBaseUrl, $relativePracticePath),
            'has_skill' => $skill !== null,
            'skill_content' => $skill !== null
                ? $this->markdownWithoutLeadingTitle($this->markdownWithoutFrontMatter($skill))
                : null,
            'skill_source_path' => $skill !== null
                ? $this->relativePath($sourcePath, $skillPath)
                : null,
            'skill_github_url' => $skill !== null
                ? $this->githubUrl($githubBaseUrl, $this->relativePath($sourcePath, $skillPath))
                : null,
            'skill_references' => $this->skillReferences(
                $sourcePath,
                "{$practiceDirectory}/skill/references",
            ),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function categoryDirectories(string $sourcePath): array
    {
        $directories = array_filter(
            File::directories($sourcePath),
            fn (string $path): bool => ! str_starts_with(basename($path), '.')
                && File::exists("{$path}/README.md"),
        );

        sort($directories);

        return array_values($directories);
    }

    /**
     * @return array<int, string>
     */
    private function practiceFiles(string $categoryPath): array
    {
        $files = array_filter(
            File::directories($categoryPath),
            fn (string $path): bool => File::exists("{$path}/BEST_PRACTICE.md"),
        );
        $paths = array_map(
            fn (string $path): string => "{$path}/BEST_PRACTICE.md",
            $files,
        );

        sort($paths);

        return array_values($paths);
    }

    /**
     * @return array<int, array{path: string, title: string, content: string}>
     */
    private function skillReferences(string $sourcePath, string $referencesPath): array
    {
        if (! File::isDirectory($referencesPath)) {
            return [];
        }

        $finder = Finder::create()
            ->files()
            ->in($referencesPath)
            ->name('*.md')
            ->sortByName();
        $references = [];

        foreach ($finder as $file) {
            $contents = $this->readRequiredFile($file->getPathname());
            $references[] = [
                'path' => $this->relativePath($sourcePath, $file->getPathname()),
                'title' => $this->titleFromMarkdown(
                    $contents,
                    Str::headline($file->getBasename('.md')),
                ),
                'content' => rtrim($contents),
            ];
        }

        return $references;
    }

    private function readRequiredFile(string $path): string
    {
        $contents = File::get($path);

        if (! is_string($contents)) {
            throw new InvalidArgumentException("Unable to read required source file [{$path}].");
        }

        return preg_replace('/[ \t]+$/m', '', $contents) ?? $contents;
    }

    private function titleFromMarkdownFile(string $path, string $fallback): string
    {
        if (! File::exists($path)) {
            return $fallback;
        }

        return $this->titleFromMarkdown($this->readRequiredFile($path), $fallback);
    }

    private function titleFromMarkdown(string $markdown, string $fallback): string
    {
        if (preg_match('/^#\s+(.+)$/m', $markdown, $matches) !== 1) {
            return $fallback;
        }

        return trim($matches[1]);
    }

    private function markdownWithoutLeadingTitle(string $markdown): string
    {
        $markdown = preg_replace('/\A#\s+.+\R{1,2}/', '', $markdown) ?? $markdown;

        return rtrim($markdown);
    }

    private function markdownWithoutFrontMatter(string $markdown): string
    {
        $markdown = preg_replace('/\A---\R.*?\R---\R*/s', '', $markdown) ?? $markdown;

        return rtrim($markdown);
    }

    private function summaryFromMarkdown(string $markdown): string
    {
        $markdown = $this->markdownWithoutFrontMatter($markdown);
        $markdown = preg_replace('/\A#\s+.+\R{1,2}/', '', $markdown) ?? $markdown;
        $markdown = preg_replace('/<a\s+name="[^"]+"><\/a>\s*/', '', $markdown) ?? $markdown;
        $markdown = preg_replace('/```.*?```/s', '', $markdown) ?? $markdown;
        $blocks = preg_split('/\R{2,}/', trim($markdown)) ?: [];

        foreach ($blocks as $block) {
            $block = trim($block);

            if (
                $block === ''
                || str_starts_with($block, '#')
                || str_starts_with($block, '- ')
            ) {
                continue;
            }

            $block = preg_replace('/\*\*(.*?)\*\*/', '$1', $block) ?? $block;
            $block = preg_replace('/`([^`]+)`/', '$1', $block) ?? $block;
            $block = preg_replace('/\[(.*?)\]\((.*?)\)/', '$1', $block) ?? $block;

            return Str::limit(trim($block), 240);
        }

        return '';
    }

    /**
     * @return array<int, array{title: string, anchor: string}>
     */
    private function chaptersFromMarkdown(string $markdown): array
    {
        preg_match_all('/^##\s+(.+)$/m', $markdown, $matches);

        return array_map(
            fn (string $title): array => [
                'title' => trim($title),
                'anchor' => Str::slug($title),
            ],
            $matches[1],
        );
    }

    private function githubUrl(?string $githubBaseUrl, string $sourcePath): ?string
    {
        if ($githubBaseUrl === null || $githubBaseUrl === '') {
            return null;
        }

        $encodedPath = implode(
            '/',
            array_map(rawurlencode(...), explode('/', str_replace('\\', '/', $sourcePath))),
        );

        return rtrim($githubBaseUrl, '/')."/{$encodedPath}";
    }

    private function relativePath(string $sourcePath, string $path): string
    {
        return ltrim(
            str_replace(
                '\\',
                '/',
                Str::after($path, rtrim($sourcePath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR),
            ),
            '/',
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function frontMatterFile(array $data): string
    {
        return "---\n".rtrim($this->dumpYaml($data))."\n---\n";
    }

    /**
     * @param array<string, mixed> $data
     */
    private function yamlFile(array $data): string
    {
        return $this->dumpYaml($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function dumpYaml(array $data): string
    {
        return Yaml::dump(
            $data,
            6,
            2,
            Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK | Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE,
        );
    }

    /**
     * @param array<string, mixed> $entry
     */
    private function syncedAt(string $entryPath, array $entry): int
    {
        $existingEntry = $this->frontMatterData($entryPath);
        $existingSyncedAt = $existingEntry['synced_at'] ?? null;

        unset($existingEntry['synced_at']);

        if (is_int($existingSyncedAt) && $existingEntry === $entry) {
            return $existingSyncedAt;
        }

        return Date::now()->timestamp;
    }

    /**
     * @return array<string, mixed>
     */
    private function frontMatterData(string $path): array
    {
        if (! File::exists($path)) {
            return [];
        }

        $contents = File::get($path);

        if (
            ! is_string($contents)
            || preg_match('/^---\n(.*?)\n---\n$/s', $contents, $matches) !== 1
        ) {
            return [];
        }

        $data = Yaml::parse($matches[1]);

        return is_array($data) ? $data : [];
    }

    /**
     * @param array<string, string> $files
     */
    private function writeFiles(array $files): int
    {
        $written = 0;

        foreach ($files as $path => $contents) {
            File::ensureDirectoryExists(dirname($path));

            if (File::exists($path) && File::get($path) === $contents) {
                continue;
            }

            File::put($path, $contents);
            $written++;
        }

        return $written;
    }

    /**
     * @param array<int, string> $expectedPaths
     */
    private function deleteStaleFiles(string $directory, string $pattern, array $expectedPaths): int
    {
        $deleted = 0;
        $expected = array_flip($expectedPaths);

        foreach (glob("{$directory}/{$pattern}") ?: [] as $path) {
            if (isset($expected[$path])) {
                continue;
            }

            File::delete($path);
            $deleted++;
        }

        return $deleted;
    }
}
