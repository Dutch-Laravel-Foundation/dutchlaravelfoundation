<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Yaml\Yaml;
use Tests\TestCase;

class BestPracticesSyncCommandTest extends TestCase
{
    private string $sourcePath;

    private string $entriesPath;

    private string $taxonomyPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sourcePath = storage_path('framework/testing/best-practices-source');
        $this->entriesPath = storage_path('framework/testing/generated-best-practices/entries');
        $this->taxonomyPath = storage_path('framework/testing/generated-best-practices/categories');

        File::deleteDirectory($this->sourcePath);
        File::deleteDirectory($this->entriesPath);
        File::deleteDirectory($this->taxonomyPath);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->sourcePath);
        File::deleteDirectory($this->entriesPath);
        File::deleteDirectory($this->taxonomyPath);

        parent::tearDown();
    }

    public function testBestPracticesCollectionAndBlueprintAreConfigured(): void
    {
        $collection = $this->parseYaml(base_path('content/collections/best_practices.yaml'));
        $blueprint = $this->parseYaml(base_path('resources/blueprints/collections/best_practices/best_practices.yaml'));
        $taxonomy = $this->parseYaml(base_path('content/taxonomies/best_practice_categories.yaml'));
        $taxonomyBlueprint = $this->parseYaml(base_path('resources/blueprints/taxonomies/best_practice_categories/best_practice_categories.yaml'));
        $fields = $this->fieldsByHandle($blueprint);

        $this->assertSame('Best Practices', $collection['title'] ?? null);
        $this->assertSame('templates/best-practices/show', $collection['template'] ?? null);
        $this->assertSame('/best-practices/{slug}', $collection['route'] ?? null);
        $this->assertContains('best_practice_categories', $collection['taxonomies'] ?? []);

        foreach (['title', 'best_practice_categories', 'source_path', 'source_sha', 'github_url', 'related_files'] as $handle) {
            $this->assertArrayHasKey($handle, $fields);
        }

        $this->assertSame('Best Practice Categories', $taxonomy['title'] ?? null);
        $this->assertSame('Best Practice Category', $taxonomyBlueprint['title'] ?? null);
    }

    public function testCommandImportsLocalBestPracticesRepositoryIntoDeterministicStatamicContent(): void
    {
        $this->writeSourceFile('README.md', '# Dutch Laravel best practices');
        $this->writeSourceFile('application-structure/README.md', "# Application structure\n\nHow to organize apps.");
        $this->writeSourceFile('application-structure/controllers.md', "# Keep controllers thin\n\n<a name=\"introduction\"></a>\n## Introduction\n\nControllers should delegate work.\n\n## Examples\n\nKeep actions small.");
        $this->writeSourceFile('testing/pest.md', "# Test behavior\n\nUse focused tests for behavior.");

        $exitCode = Artisan::call('best-practices:sync', [
            'path' => $this->sourcePath,
            '--source-sha' => 'abc1234',
            '--github-base-url' => 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/abc1234',
            '--entries-path' => $this->entriesPath,
            '--taxonomy-path' => $this->taxonomyPath,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Imported 2 best practices across 2 categories.', Artisan::output());

        $entryPath = "{$this->entriesPath}/application-structure-controllers.md";
        $categoryPath = "{$this->taxonomyPath}/application-structure.yaml";

        $this->assertFileExists($entryPath);
        $this->assertFileExists($categoryPath);

        $entry = $this->parseFrontMatter($entryPath);
        $category = $this->parseYaml($categoryPath);

        $this->assertSame('best_practices', $entry['blueprint'] ?? null);
        $this->assertSame('Keep controllers thin', $entry['title'] ?? null);
        $this->assertSame('Controllers should delegate work.', $entry['summary'] ?? null);
        $this->assertSame([
            [
                'title' => 'Introduction',
                'anchor' => 'introduction',
            ],
            [
                'title' => 'Examples',
                'anchor' => 'examples',
            ],
        ], $entry['chapters'] ?? null);
        $this->assertSame(['application-structure'], $entry['best_practice_categories'] ?? null);
        $this->assertSame('application-structure', $entry['category_slug'] ?? null);
        $this->assertSame('Application structure', $entry['category_title'] ?? null);
        $this->assertSame('application-structure/controllers.md', $entry['source_path'] ?? null);
        $this->assertSame('abc1234', $entry['source_sha'] ?? null);
        $this->assertSame(
            'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/abc1234/application-structure/controllers.md',
            $entry['github_url'] ?? null,
        );
        $this->assertSame([], $entry['related_files'] ?? null);
        $this->assertStringContainsString('Controllers should delegate work.', $entry['content'] ?? '');
        $this->assertSame('Application structure', $category['title'] ?? null);
        $this->assertSame(1, $category['practice_count'] ?? null);
        $this->assertSame('application-structure/README.md', $category['source_path'] ?? null);

        $firstHash = hash_file('sha256', $entryPath);

        $this->assertSame(0, Artisan::call('best-practices:sync', [
            'path' => $this->sourcePath,
            '--source-sha' => 'abc1234',
            '--github-base-url' => 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/abc1234',
            '--entries-path' => $this->entriesPath,
            '--taxonomy-path' => $this->taxonomyPath,
        ]));

        $this->assertSame($firstHash, hash_file('sha256', $entryPath));
    }

    private function writeSourceFile(string $path, string $contents): void
    {
        File::ensureDirectoryExists(dirname("{$this->sourcePath}/{$path}"));
        File::put("{$this->sourcePath}/{$path}", $contents);
    }

    /**
     * @return array<string, mixed>
     */
    private function parseYaml(string $path): array
    {
        $this->assertFileExists($path);

        return Yaml::parseFile($path);
    }

    /**
     * @return array<string, mixed>
     */
    private function parseFrontMatter(string $path): array
    {
        $this->assertFileExists($path);
        $contents = (string) file_get_contents($path);

        $matched = preg_match('/^---\n(.*?)\n---\n(.*)$/s', $contents, $matches);

        $this->assertSame(1, $matched);

        return [
            ...Yaml::parse($matches[1]),
            'content' => $matches[2],
        ];
    }

    /**
     * @param array<string, mixed> $blueprint
     * @return array<string, array<string, mixed>>
     */
    private function fieldsByHandle(array $blueprint): array
    {
        $fields = [];

        foreach ($blueprint['tabs'] ?? [] as $tab) {
            foreach ($tab['sections'] ?? [] as $section) {
                foreach ($section['fields'] ?? [] as $field) {
                    if (! isset($field['handle'])) {
                        continue;
                    }

                    $fields[$field['handle']] = $field['field'] ?? [];
                }
            }
        }

        return $fields;
    }
}
