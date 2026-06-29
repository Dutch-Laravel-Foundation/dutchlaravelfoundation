<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\Agents\EntryMarkdownRenderer;
use Statamic\Contracts\Entries\Entry;
use Symfony\Component\Yaml\Yaml;
use Tests\TestCase;

class PodcastsCollectionTest extends TestCase
{
    public function testPodcastsCollectionDefinesPluralHandleAndSingularPublicRoutes(): void
    {
        $collection = $this->parseYaml(base_path('content/collections/podcasts.yaml'));

        $this->assertSame('Podcasts', $collection['title'] ?? null);
        $this->assertSame('templates/podcasts/show', $collection['template'] ?? null);
        $this->assertSame('layout', $collection['layout'] ?? null);
        $this->assertSame('/podcast/{slug}', $collection['route'] ?? null);
        $this->assertTrue($collection['date'] ?? false);
        $this->assertSame('desc', $collection['sort_dir'] ?? null);
    }

    public function testPodcastBlueprintRequiresPublishingFields(): void
    {
        $blueprint = $this->parseYaml(base_path('resources/blueprints/collections/podcasts/podcasts.yaml'));
        $fields = $this->fieldsByHandle($blueprint);

        foreach (['title', 'summary', 'description', 'video_url', 'spotify_url', 'thumbnail_url', 'transcript', 'date', 'published_at', 'slug'] as $handle) {
            $this->assertArrayHasKey($handle, $fields);
        }

        $this->assertSame('text', $fields['title']['type'] ?? null);
        $this->assertSame('textarea', $fields['summary']['type'] ?? null);
        $this->assertSame('markdown', $fields['description']['type'] ?? null);
        $this->assertSame('video', $fields['video_url']['type'] ?? null);
        $this->assertSame('text', $fields['spotify_url']['type'] ?? null);
        $this->assertSame('text', $fields['thumbnail_url']['type'] ?? null);
        $this->assertSame('markdown', $fields['transcript']['type'] ?? null);

        foreach (['title', 'summary', 'description', 'video_url', 'spotify_url', 'thumbnail_url', 'transcript', 'date', 'published_at'] as $handle) {
            $this->assertContains('required', $fields[$handle]['validate'] ?? []);
        }

        $this->assertContains('url', $fields['spotify_url']['validate'] ?? []);
        $this->assertTrue($fields['published_at']['time_enabled'] ?? false);
    }

    public function testPodcastsIndexPageRenders(): void
    {
        $response = $this->get('/podcast');

        $response->assertOk();
        $response->assertSee('Podcast', false);
        $response->assertSee('Bekijk talks, interviews en andere video\'s ook direct via onze kanalen.', false);
    }

    public function testPodcastOverviewPageAndNavigationUseSingularLabel(): void
    {
        $page = $this->parseFrontMatter(base_path('content/collections/pages/podcast.md'));
        $navigation = $this->parseYaml(base_path('content/trees/navigation/main.yaml'));

        $this->assertSame('Podcast', $page['title'] ?? null);
        $this->assertSame('templates/podcasts/index', $page['template'] ?? null);
        $this->assertSame('Podcast', data_get($page, 'content.0.content.0.text'));
        $this->assertSame(
            'Podcast',
            data_get($navigation, 'tree.3.children.1.title'),
        );
        $this->assertSame(
            '2b67a6f6-e3cf-4f4e-bdf9-f4bafc8cc0f9',
            data_get($navigation, 'tree.3.children.1.entry'),
        );
    }

    public function testPodcastTemplatesExposeVideoDescriptionAndTranscript(): void
    {
        $showTemplate = file_get_contents(resource_path('views/templates/podcasts/show.antlers.html'));
        $indexTemplate = file_get_contents(resource_path('views/templates/podcasts/index.antlers.html'));

        $this->assertNotFalse($showTemplate);
        $this->assertNotFalse($indexTemplate);
        $this->assertStringContainsString('video_url', $showTemplate);
        $this->assertStringContainsString('spotify_url', $showTemplate);
        $this->assertStringContainsString('partial:buttons/youtube', $showTemplate);
        $this->assertStringContainsString('partial:buttons/spotify', $showTemplate);
        $this->assertStringContainsString('partial:buttons/youtube', $indexTemplate);
        $this->assertStringContainsString('partial:buttons/spotify', $indexTemplate);
        $this->assertStringContainsString('https://www.youtube.com/@DutchLaravelFoundation', $indexTemplate);
        $this->assertStringContainsString('https://open.spotify.com/show/28cbLx8VKFE0j3xdbRhxsO', $indexTemplate);
        $this->assertStringContainsString('aspect-video mb-6 lg:mb-8', $showTemplate);
        $this->assertStringContainsString('thumbnail_url', $indexTemplate);
        $this->assertStringContainsString('summary', $indexTemplate);
        $this->assertStringContainsString('description', $showTemplate);
        $this->assertStringContainsString('transcript', $showTemplate);
        $this->assertStringContainsString('role="tablist"', $showTemplate);
        $this->assertStringContainsString('Samenvatting', $showTemplate);
        $this->assertStringContainsString("activeTab === 'transcript'", $showTemplate);
        $this->assertStringContainsString("activeTab === 'description'", $showTemplate);
        $this->assertStringContainsString('collection:podcasts', $indexTemplate);
        $this->assertStringContainsString('sort="published_at:desc"', $indexTemplate);
        $this->assertStringContainsString('block-image-left-large', $indexTemplate);
    }

    public function testPodcastEntriesCanBeServedAsMarkdown(): void
    {
        $middleware = file_get_contents(app_path('Http/Middleware/ServeMarkdown.php'));
        $llmsController = file_get_contents(app_path('Http/Controllers/Agents/LlmsController.php'));
        $llmsIndex = file_get_contents(resource_path('views/agents/llms.blade.php'));

        $this->assertNotFalse($middleware);
        $this->assertNotFalse($llmsController);
        $this->assertNotFalse($llmsIndex);
        $this->assertStringContainsString("'/podcast/'", $middleware);
        $this->assertStringContainsString("'podcasts'", $middleware);
        $this->assertStringContainsString('podcastItems', $llmsController);
        $this->assertStringContainsString('## Podcasts', $llmsIndex);
    }

    public function testImportedPodcastEntriesContainVideoUrlsAndTranscripts(): void
    {
        $paths = glob(base_path('content/collections/podcasts/*.md'));

        $this->assertCount(19, $paths);

        foreach ($paths as $path) {
            $entry = $this->parseFrontMatter($path);

            $this->assertNotEmpty($entry['id'] ?? null);
            $this->assertSame('podcasts', $entry['blueprint'] ?? null);
            $this->assertNotEmpty($entry['title'] ?? null);
            $this->assertNotEmpty($entry['summary'] ?? null);
            $this->assertNotEmpty($entry['description'] ?? null);
            $this->assertMatchesRegularExpression(
                '/^https:\/\/www\.youtube\.com\/watch\?v=[A-Za-z0-9_-]+$/',
                $entry['video_url'] ?? '',
            );
            $this->assertMatchesRegularExpression(
                '/^https:\/\/open\.spotify\.com\/(?:episode\/[A-Za-z0-9]+|show\/28cbLx8VKFE0j3xdbRhxsO)$/',
                $entry['spotify_url'] ?? '',
            );
            $this->assertMatchesRegularExpression(
                '/^https:\/\/i\.ytimg\.com\/vi\/[A-Za-z0-9_-]+\/maxresdefault\.jpg$/',
                $entry['thumbnail_url'] ?? '',
            );
            $this->assertLessThanOrEqual(2, $this->sentenceCount($entry['summary']));
            $this->assertNotEmpty($entry['transcript'] ?? null);
            $this->assertMatchesRegularExpression(
                '/^\d{4}-\d{2}-\d{2}$/',
                $entry['date'] ?? '',
            );
            $this->assertMatchesRegularExpression(
                '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
                $entry['published_at'] ?? '',
            );
        }
    }

    public function testPodcastEntriesUsePublicationTimesForSameDayOrdering(): void
    {
        $paths = glob(base_path('content/collections/podcasts/*.md'));

        $entries = array_map(function (string $path): array {
            $entry = $this->parseFrontMatter($path);

            return [
                'file' => basename($path),
                'published_at' => $entry['published_at'],
            ];
        }, $paths);

        usort($entries, fn (array $left, array $right): int => strcmp($right['published_at'], $left['published_at']));

        $marchSecondEntries = array_values(array_filter(
            $entries,
            fn (array $entry): bool => str_starts_with($entry['published_at'], '2026-03-02 '),
        ));

        $this->assertSame([
            '2026-03-02.andreas-braun-mongodb-on-rethinking-databases.md',
            '2026-03-02.nativephp-with-shane-rosenthal.md',
            '2026-03-02.junior-adwise-on-laravel-statamic-side-projects.md',
            '2026-03-02.mattias-geniar-oh-dear-on-monitoring-uptime-ai-monitoring.md',
            '2026-03-02.laracon-eu-2026-kickoff-first-impressions-talks-what-we-re-excited-about.md',
            '2026-03-02.pete-heslop-ceo-of-steadfast-collective-and-official-laravel-partner.md',
        ], array_column($marchSecondEntries, 'file'));
    }

    public function testPodcastSummariesAndDescriptionsUseDutchCopy(): void
    {
        $paths = glob(base_path('content/collections/podcasts/*.md'));
        $englishImportPhrases = [
            '\bIn this (?:episode|interview)\b',
            '\bRecorded live\b',
            '\bLive from\b',
            '\bWe (?:talk|discuss|dive)\b',
            '\bThis episode\b',
            '\bSteve works\b',
            '\bTaylor Otwell on AI\b',
            '\bfirst impressions\b',
            '\bwhat we.?re excited\b',
            '\bAI-mindset shifts\b',
            '\bside projects\b',
            '\bmanaging director\b',
            '\bofficial Laravel Partner\b',
            '\bprove their Laravel expertise\b',
        ];

        foreach ($paths as $path) {
            $entry = $this->parseFrontMatter($path);

            foreach (['summary', 'description'] as $field) {
                $this->assertDoesNotMatchRegularExpression(
                    '/'.implode('|', $englishImportPhrases).'/i',
                    $entry[$field] ?? '',
                    basename($path).': '.$field,
                );
            }
        }

        $steve = $this->parseFrontMatter(base_path(
            'content/collections/podcasts/2026-03-04.steve-mcdougall-laravel-certification-explained-why-developers-and-employers-should-care.md',
        ));

        $this->assertStringContainsString(
            'Live vanaf Laracon EU 2026 spreken we met Steve McDougall',
            $steve['summary'],
        );
        $this->assertStringContainsString(
            'Steve werkt aan het certificeringsinitiatief',
            $steve['description'],
        );
    }

    public function testPodcastMarkdownIncludesVideoUrlAndTranscript(): void
    {
        $entry = new class implements Entry {
            /** @var array<string, string|null> */
            private array $data = [
                'title' => 'Under the Hood of Shift',
                'excerpt' => null,
                'meta_description' => null,
                'video_url' => 'https://www.youtube.com/watch?v=example',
                'spotify_url' => 'https://open.spotify.com/episode/example',
                'description' => 'A conversation about automating Laravel upgrades.',
                'transcript' => 'Welcome to the Dutch Laravel Foundation podcast transcript.',
                'tags' => null,
            ];

            public function get(string $key): ?string
            {
                return $this->data[$key] ?? null;
            }

            public function collectionHandle(): string
            {
                return 'podcasts';
            }

            public function date(): null
            {
                return null;
            }

            public function absoluteUrl(): string
            {
                return 'https://example.com/podcast/under-the-hood-of-shift';
            }
        };

        $markdown = app(EntryMarkdownRenderer::class)->render($entry);

        $this->assertStringContainsString('**Video:** https://www.youtube.com/watch?v=example', $markdown);
        $this->assertStringContainsString('**Spotify:** https://open.spotify.com/episode/example', $markdown);
        $this->assertStringContainsString('A conversation about automating Laravel upgrades.', $markdown);
        $this->assertStringContainsString('## Transcript', $markdown);
        $this->assertStringContainsString('Welcome to the Dutch Laravel Foundation podcast transcript.', $markdown);
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

        $contents = file_get_contents($path);
        $this->assertIsString($contents);
        $this->assertSame(1, preg_match('/^---\R(.*?)\R---/s', $contents, $matches));

        return Yaml::parse($matches[1]);
    }

    /**
     * @param array<string, mixed> $node
     * @return array<string, array<string, mixed>>
     */
    private function fieldsByHandle(array $node): array
    {
        $fields = [];

        foreach ($node as $key => $value) {
            if ($key === 'handle' && is_string($value) && isset($node['field']) && is_array($node['field'])) {
                $fields[$value] = $node['field'];
            }

            if (is_array($value)) {
                $fields = array_merge($fields, $this->fieldsByHandle($value));
            }
        }

        return $fields;
    }

    private function sentenceCount(string $text): int
    {
        preg_match_all('/[.!?](?:\s|$)/', $text, $matches);

        return max(1, count($matches[0]));
    }
}
