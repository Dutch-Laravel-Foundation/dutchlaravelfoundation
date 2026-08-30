<?php

declare(strict_types=1);

namespace App\Services\BestPractices;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Yaml\Yaml;

final class BestPracticesImporter
{
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
            throw new \InvalidArgumentException("Source path [{$sourcePath}] does not exist.");
        }

        File::ensureDirectoryExists($entriesPath);
        File::ensureDirectoryExists($taxonomyPath);

        $written = 0;
        $expectedEntries = [];
        $expectedTerms = [];
        $practiceCount = 0;
        $categoryCount = 0;

        foreach ($this->categoryDirectories($sourcePath) as $categoryPath) {
            $categorySlug = basename($categoryPath);
            $practiceFiles = $this->practiceFiles($categoryPath);

            if ($practiceFiles === []) {
                continue;
            }

            $categoryReadmePath = "{$categoryPath}/README.md";
            $categorySourcePath = "{$categorySlug}/README.md";
            $categoryTitle = $this->titleFromMarkdownFile($categoryReadmePath, Str::headline($categorySlug));

            $expectedTerms[] = $termPath = "{$taxonomyPath}/{$categorySlug}.yaml";
            $written += $this->writeIfChanged($termPath, $this->termContents(
                title: $categoryTitle,
                sourcePath: File::exists($categoryReadmePath) ? $categorySourcePath : $categorySlug,
                sourceSha: $sourceSha,
                githubUrl: $this->githubUrl($githubBaseUrl, File::exists($categoryReadmePath) ? $categorySourcePath : $categorySlug),
                practiceCount: count($practiceFiles),
            ));
            $categoryCount++;

            foreach ($practiceFiles as $practicePath) {
                $relativePath = $this->relativePath($sourcePath, $practicePath);
                $entrySlug = Str::slug(str_replace(['/', '.md'], ['-', ''], $relativePath));

                $markdown = $this->markdownWithoutLeadingTitle((string) File::get($practicePath));

                $expectedEntries[] = $entryPath = "{$entriesPath}/{$entrySlug}.md";
                $written += $this->writeIfChanged($entryPath, $this->entryContents(
                    sourcePath: $relativePath,
                    title: $this->titleFromMarkdownFile($practicePath, Str::headline(pathinfo($practicePath, PATHINFO_FILENAME))),
                    categorySlug: $categorySlug,
                    categoryTitle: $categoryTitle,
                    markdown: $markdown,
                    sourceSha: $sourceSha,
                    githubUrl: $this->githubUrl($githubBaseUrl, $relativePath),
                ));
                $practiceCount++;
            }
        }

        $deleted = $this->deleteStaleFiles($entriesPath, '*.md', $expectedEntries)
            + $this->deleteStaleFiles($taxonomyPath, '*.yaml', $expectedTerms);

        return [
            'practices' => $practiceCount,
            'categories' => $categoryCount,
            'written' => $written,
            'deleted' => $deleted,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function categoryDirectories(string $sourcePath): array
    {
        $directories = array_filter(File::directories($sourcePath), fn (string $path): bool => ! str_starts_with(basename($path), '.'));

        sort($directories);

        return array_values($directories);
    }

    /**
     * @return array<int, string>
     */
    private function practiceFiles(string $categoryPath): array
    {
        $files = array_filter(File::files($categoryPath), function (\SplFileInfo $file): bool {
            return $file->getExtension() === 'md'
                && strtolower($file->getFilename()) !== 'readme.md';
        });

        $paths = array_map(fn (\SplFileInfo $file): string => $file->getPathname(), $files);
        sort($paths);

        return array_values($paths);
    }

    private function termContents(string $title, string $sourcePath, ?string $sourceSha, ?string $githubUrl, int $practiceCount): string
    {
        return $this->yamlFile([
            'title' => $title,
            'practice_count' => $practiceCount,
            'source_path' => $sourcePath,
            'source_sha' => $sourceSha,
            'github_url' => $githubUrl,
        ]);
    }

    private function entryContents(
        string $sourcePath,
        string $title,
        string $categorySlug,
        string $categoryTitle,
        string $markdown,
        ?string $sourceSha,
        ?string $githubUrl,
    ): string {
        return "---\n".$this->dumpYaml([
            'id' => Uuid::uuid5(Uuid::NAMESPACE_URL, "dlf-best-practices:{$sourcePath}")->toString(),
            'blueprint' => 'best_practices',
            'title' => $title,
            'summary' => $this->summaryFromMarkdown($markdown),
            'chapters' => $this->chaptersFromMarkdown($markdown),
            'best_practice_categories' => [$categorySlug],
            'category_slug' => $categorySlug,
            'category_title' => $categoryTitle,
            'source_path' => $sourcePath,
            'source_sha' => $sourceSha,
            'github_url' => $githubUrl,
            'boost_skill_path' => null,
            'related_files' => [],
        ])."---\n{$markdown}\n";
    }

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
            4,
            2,
            Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK | Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE,
        );
    }

    private function titleFromMarkdownFile(string $path, string $fallback): string
    {
        if (! File::exists($path)) {
            return $fallback;
        }

        if (preg_match('/^#\s+(.+)$/m', (string) File::get($path), $matches) !== 1) {
            return $fallback;
        }

        return trim($matches[1]);
    }

    private function markdownWithoutLeadingTitle(string $markdown): string
    {
        $markdown = preg_replace('/\A#\s+.+\R{1,2}/', '', $markdown) ?? $markdown;

        return rtrim($markdown);
    }

    private function summaryFromMarkdown(string $markdown): string
    {
        $markdown = preg_replace('/<a\s+name="[^"]+"><\/a>\s*/', '', $markdown) ?? $markdown;
        $markdown = preg_replace('/```.*?```/s', '', $markdown) ?? $markdown;
        $blocks = preg_split('/\R{2,}/', trim($markdown)) ?: [];

        foreach ($blocks as $block) {
            $block = trim($block);

            if ($block === '' || str_starts_with($block, '#') || str_starts_with($block, '- ')) {
                continue;
            }

            $block = preg_replace('/\*\*(.*?)\*\*/', '$1', $block) ?? $block;
            $block = preg_replace('/`([^`]+)`/', '$1', $block) ?? $block;
            $block = preg_replace('/\[(.*?)\]\((.*?)\)/', '$1', $block) ?? $block;
            $block = trim($block);

            return Str::limit($block, 240);
        }

        return '';
    }

    /**
     * @return array<int, array{title: string, anchor: string}>
     */
    private function chaptersFromMarkdown(string $markdown): array
    {
        preg_match_all('/^##\s+(.+)$/m', $markdown, $matches);

        return array_map(fn (string $title): array => [
            'title' => trim($title),
            'anchor' => Str::slug($title),
        ], $matches[1]);
    }

    private function githubUrl(?string $githubBaseUrl, string $sourcePath): ?string
    {
        if (! $githubBaseUrl) {
            return null;
        }

        return rtrim($githubBaseUrl, '/').'/'.str_replace('%2F', '/', rawurlencode($sourcePath));
    }

    private function relativePath(string $sourcePath, string $path): string
    {
        return ltrim(str_replace('\\', '/', Str::after($path, rtrim($sourcePath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR)), '/');
    }

    /**
     * @param array<int, string> $expectedPaths
     */
    private function deleteStaleFiles(string $directory, string $pattern, array $expectedPaths): int
    {
        $deleted = 0;
        $expectedPaths = array_flip($expectedPaths);

        foreach (glob("{$directory}/{$pattern}") ?: [] as $path) {
            if (isset($expectedPaths[$path])) {
                continue;
            }

            File::delete($path);
            $deleted++;
        }

        return $deleted;
    }

    private function writeIfChanged(string $path, string $contents): int
    {
        File::ensureDirectoryExists(dirname($path));

        if (File::exists($path) && File::get($path) === $contents) {
            return 0;
        }

        File::put($path, $contents);

        return 1;
    }
}
