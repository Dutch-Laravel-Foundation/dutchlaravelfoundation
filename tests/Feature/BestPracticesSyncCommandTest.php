<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\File;
use Symfony\Component\Yaml\Yaml;
use Tests\TestCase;

final class BestPracticesSyncCommandTest extends TestCase
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
        File::deleteDirectory(dirname($this->entriesPath));
    }

    protected function tearDown(): void
    {
        Date::setTestNow();
        File::deleteDirectory($this->sourcePath);
        File::deleteDirectory(dirname($this->entriesPath));

        parent::tearDown();
    }

    public function testItImportsNestedMultilingualPracticesAndSkills(): void
    {
        Date::setTestNow('2026-07-22 10:15:00');

        $this->writeSourceFile('routing/README.md', "# Routing\n");
        $this->writeSourceFile('documentation/README.md', "# Documentation\n");
        $this->writeSourceFile(
            'routing/use-form-requests/BEST_PRACTICE.md',
            "# Use Form Requests\n\n## Introduction\n\nKeep validation out of controllers.\n\n## Why\n\nIt keeps controllers focused.\n",
        );
        $this->writeSourceFile(
            'routing/use-form-requests/translations/nl.md',
            "# Gebruik Form Requests\n\n## Introductie\n\nHoud validatie uit controllers.\n\n## Waarom\n\nZo blijven controllers overzichtelijk.\n",
        );
        $this->writeSourceFile(
            'routing/use-form-requests/skill/SKILL.md',
            "---\nname: dlf-use-form-requests\ndescription: Apply Form Request guidance.\n---\n\n# Use Form Requests skill\n\nInspect controller validation.\n",
        );
        $this->writeSourceFile(
            'routing/use-form-requests/skill/references/checklist.md',
            "# Review checklist\n\n- Uses a Form Request.\n",
        );
        $this->writeSourceFile(
            'routing/use-form-requests/skill/references/.gitkeep',
            '',
        );

        $exitCode = Artisan::call('best-practices:sync', [
            'path' => $this->sourcePath,
            '--source-sha' => 'abc1234',
            '--github-base-url' => 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/abc1234',
            '--entries-path' => $this->entriesPath,
            '--taxonomy-path' => $this->taxonomyPath,
        ]);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString(
            'Imported 1 best practice across 2 categories.',
            Artisan::output(),
        );

        $entry = $this->parseFrontMatter("{$this->entriesPath}/routing-use-form-requests.md");
        $routing = $this->parseYaml("{$this->taxonomyPath}/routing.yaml");
        $documentation = $this->parseYaml("{$this->taxonomyPath}/documentation.yaml");

        $this->assertSame('best_practices', $entry['blueprint']);
        $this->assertSame('Gebruik Form Requests', $entry['title']);
        $this->assertSame('Gebruik Form Requests', $entry['title_nl']);
        $this->assertSame('Use Form Requests', $entry['title_en']);
        $this->assertSame('Houd validatie uit controllers.', $entry['summary_nl']);
        $this->assertSame('Keep validation out of controllers.', $entry['summary_en']);
        $this->assertSame(['routing'], $entry['best_practice_categories']);
        $this->assertSame('routing', $entry['category_slug']);
        $this->assertSame('Routing', $entry['category_title']);
        $this->assertSame('routing/use-form-requests/BEST_PRACTICE.md', $entry['source_path']);
        $this->assertSame('abc1234', $entry['source_sha']);
        $this->assertSame(Date::now()->timestamp, $entry['synced_at']);
        $this->assertSame(
            'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/abc1234/routing/use-form-requests/BEST_PRACTICE.md',
            $entry['github_url'],
        );
        $this->assertTrue($entry['has_skill']);
        $this->assertSame(
            'routing/use-form-requests/skill/SKILL.md',
            $entry['skill_source_path'],
        );
        $this->assertSame(
            'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/abc1234/routing/use-form-requests/skill/SKILL.md',
            $entry['skill_github_url'],
        );
        $this->assertStringNotContainsString('name: dlf-use-form-requests', $entry['skill_content']);
        $this->assertStringContainsString('Inspect controller validation.', $entry['skill_content']);
        $this->assertSame([
            [
                'path' => 'routing/use-form-requests/skill/references/checklist.md',
                'title' => 'Review checklist',
                'content' => "# Review checklist\n\n- Uses a Form Request.",
            ],
        ], $entry['skill_references']);
        $this->assertStringContainsString('Houd validatie uit controllers.', $entry['content_nl']);
        $this->assertStringContainsString('Keep validation out of controllers.', $entry['content_en']);
        $this->assertSame([
            ['title' => 'Introductie', 'anchor' => 'introductie'],
            ['title' => 'Waarom', 'anchor' => 'waarom'],
        ], $entry['chapters_nl']);
        $this->assertSame(1, $routing['practice_count']);
        $this->assertSame(0, $documentation['practice_count']);
    }

    public function testCollectionAndBlueprintExposeImportedFields(): void
    {
        $collection = $this->parseYaml(base_path('content/collections/best_practices.yaml'));
        $blueprint = $this->parseYaml(
            base_path('resources/blueprints/collections/best_practices/best_practices.yaml'),
        );
        $taxonomy = $this->parseYaml(
            base_path('content/taxonomies/best_practice_categories.yaml'),
        );
        $fields = $this->fieldsByHandle($blueprint);

        $this->assertSame('Best practices', $collection['title']);
        $this->assertSame('templates/best-practices/show', $collection['template']);
        $this->assertSame('/best-practices/{slug}', $collection['route']);
        $this->assertContains('best_practice_categories', $collection['taxonomies']);
        $this->assertSame('Best practice categories', $taxonomy['title']);

        foreach ([
            'title_nl',
            'title_en',
            'summary_nl',
            'summary_en',
            'content_nl',
            'content_en',
            'chapters_nl',
            'chapters_en',
            'has_skill',
            'skill_content',
            'skill_references',
            'source_sha',
            'synced_at',
        ] as $handle) {
            $this->assertArrayHasKey($handle, $fields);
        }
    }

    public function testImportIsIdempotentAndRemovesStaleFiles(): void
    {
        Date::setTestNow('2026-07-22 10:15:00');

        $this->writeSourceFile('testing/README.md', "# Testing\n");
        $this->writeSourceFile(
            'testing/use-pest/BEST_PRACTICE.md',
            "# Use Pest\n\n## Introduction\n\nWrite focused tests.\n",
        );
        File::ensureDirectoryExists($this->entriesPath);
        File::ensureDirectoryExists($this->taxonomyPath);
        File::put("{$this->entriesPath}/stale.md", "---\ntitle: Stale\n---\n");
        File::put("{$this->taxonomyPath}/stale.yaml", "title: Stale\n");

        $arguments = [
            'path' => $this->sourcePath,
            '--source-sha' => 'abc1234',
            '--entries-path' => $this->entriesPath,
            '--taxonomy-path' => $this->taxonomyPath,
        ];

        $this->assertSame(0, Artisan::call('best-practices:sync', $arguments));
        $entryPath = "{$this->entriesPath}/testing-use-pest.md";
        $firstHash = hash_file('sha256', $entryPath);
        $entry = $this->parseFrontMatter($entryPath);
        $firstSyncedAt = Date::now()->timestamp;

        $this->assertFalse($entry['has_skill']);
        $this->assertNull($entry['title_nl']);
        $this->assertNull($entry['content_nl']);
        $this->assertSame($firstSyncedAt, $entry['synced_at']);
        $this->assertFileDoesNotExist("{$this->entriesPath}/stale.md");
        $this->assertFileDoesNotExist("{$this->taxonomyPath}/stale.yaml");

        Date::setTestNow('2026-07-27 10:15:00');

        $this->assertSame(0, Artisan::call('best-practices:sync', $arguments));
        $this->assertSame($firstHash, hash_file('sha256', $entryPath));
        $this->assertSame($firstSyncedAt, $this->parseFrontMatter($entryPath)['synced_at']);
        $this->assertStringContainsString(
            'Changed files: 0; deleted stale files: 0.',
            Artisan::output(),
        );

        $this->writeSourceFile(
            'testing/use-pest/BEST_PRACTICE.md',
            "# Use Pest\n\n## Introduction\n\nWrite focused and maintainable tests.\n",
        );

        $this->assertSame(0, Artisan::call('best-practices:sync', $arguments));
        $this->assertSame(
            Date::now()->timestamp,
            $this->parseFrontMatter($entryPath)['synced_at'],
        );
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

        $matched = preg_match('/^---\n(.*?)\n---\n$/s', $contents, $matches);

        $this->assertSame(1, $matched);

        return Yaml::parse($matches[1]);
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
