<?php

declare(strict_types=1);
use App\Services\Agents\EntryMarkdownRenderer;
use Statamic\Contracts\Entries\Entry;

test('podcasts collection defines plural handle and singular public routes', function () {
    $collection = parseYaml(base_path('content/collections/podcasts.yaml'));

    expect($collection['title'] ?? null)->toBe('Podcasts');
    expect($collection['template'] ?? null)->toBe('templates/podcasts/show');
    expect($collection['layout'] ?? null)->toBe('layout');
    expect($collection['route'] ?? null)->toBe('/podcast/{slug}');
    expect($collection['date'] ?? false)->toBeTrue();
    expect($collection['sort_dir'] ?? null)->toBe('desc');
});
test('podcast blueprint requires publishing fields', function () {
    $blueprint = parseYaml(base_path('resources/blueprints/collections/podcasts/podcasts.yaml'));
    $fields = fieldsByHandle($blueprint);

    foreach (['title', 'summary', 'description', 'video_url', 'spotify_url', 'thumbnail_url', 'transcript', 'date', 'published_at', 'slug', 'call_to_action'] as $handle) {
        expect($fields)->toHaveKey($handle);
    }

    expect($fields['title']['type'] ?? null)->toBe('text');
    expect($fields['summary']['type'] ?? null)->toBe('textarea');
    expect($fields['description']['type'] ?? null)->toBe('markdown');
    expect($fields['video_url']['type'] ?? null)->toBe('video');
    expect($fields['spotify_url']['type'] ?? null)->toBe('text');
    expect($fields['thumbnail_url']['type'] ?? null)->toBe('text');
    expect($fields['transcript']['type'] ?? null)->toBe('markdown');
    expect($fields['call_to_action']['type'] ?? null)->toBe('entries');
    expect($fields['call_to_action']['collections'] ?? null)->toBe(['cta']);

    foreach (['title', 'summary', 'description', 'video_url', 'spotify_url', 'thumbnail_url', 'transcript', 'date', 'published_at'] as $handle) {
        expect($fields[$handle]['validate'] ?? [])->toContain('required');
    }

    expect($fields['spotify_url']['validate'] ?? [])->toContain('url');
    expect($fields['published_at']['time_enabled'] ?? false)->toBeTrue();
});
test('podcasts index page renders', function () {
    $response = $this->withHeaders(inertiaHeaders())->get('/podcast');

    $response->assertOk();
    $response->assertJsonPath('component', 'Editorial/PodcastsIndex');
    $response->assertJsonPath('props.page.title', 'Podcast');
    $response->assertJsonPath(
        'props.page.seo.description',
        'Luister naar gesprekken met developers, ondernemers en communityleden over Laravel, technologie, loopbanen en de Nederlandse community.',
    );
    $response->assertJsonPath(
        'props.page.footerCta.id',
        'ee5d33de-9a24-4860-92dd-3503740b62af',
    );
    $response->assertJsonCount(10, 'props.editorial.items');
});
test('podcast overview page and navigation use singular label', function () {
    $page = parseFrontMatter(base_path('content/collections/pages/podcast.md'));
    $navigation = parseYaml(base_path('content/trees/navigation/main.yaml'));

    expect($page['title'] ?? null)->toBe('Podcast');
    expect($page['template'] ?? null)->toBe('templates/podcasts/index');
    expect(data_get($page, 'content.0.content.0.text'))->toBe('Podcast');
    expect(data_get($navigation, 'tree.3.children.1.title'))->toBe('Podcast');
    expect(data_get($navigation, 'tree.3.children.1.entry'))->toBe('2b67a6f6-e3cf-4f4e-bdf9-f4bafc8cc0f9');
});
test('podcast react components expose video description and transcript', function () {
    $showPage = file_get_contents(resource_path('js/pages/Editorial/PodcastsShow.tsx'));
    $indexPage = file_get_contents(resource_path('js/pages/Editorial/PodcastsIndex.tsx'));
    $controls = file_get_contents(resource_path('js/components/editorial-react/PodcastControls.tsx'));

    $this->assertNotFalse($showPage);
    $this->assertNotFalse($indexPage);
    $this->assertNotFalse($controls);
    $this->assertStringContainsString('editorial.videoUrl', $showPage);
    $this->assertStringContainsString('editorial.spotifyUrl', $showPage);
    $this->assertStringContainsString('episode.thumbnailUrl', $indexPage);
    $this->assertStringContainsString('episode.summary', $indexPage);
    $this->assertStringContainsString('descriptionHtml', $controls);
    $this->assertStringContainsString('transcriptHtml', $controls);
    $this->assertStringContainsString('<Tabs.List', $controls);
    $this->assertStringContainsString('Samenvatting', $controls);
    $this->assertStringContainsString('value="transcript"', $controls);
    $this->assertStringContainsString('value="description"', $controls);
});
test('podcast entries use the homepage call to action banner', function () {
    $paths = glob(base_path('content/collections/podcasts/*.md'));

    foreach ($paths as $path) {
        $entry = parseFrontMatter($path);

        expect($entry['call_to_action'] ?? null)->toBe('ee5d33de-9a24-4860-92dd-3503740b62af', basename($path).' must use the homepage CTA banner.');
    }
});
test('podcast entries can be served as markdown', function () {
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
});
test('imported podcast entries contain video urls and transcripts', function () {
    $paths = glob(base_path('content/collections/podcasts/*.md'));

    expect($paths)->toHaveCount(19);

    foreach ($paths as $path) {
        $entry = parseFrontMatter($path);

        expect($entry['id'] ?? null)->not->toBeEmpty();
        expect($entry['blueprint'] ?? null)->toBe('podcasts');
        expect($entry['title'] ?? null)->not->toBeEmpty();
        expect($entry['summary'] ?? null)->not->toBeEmpty();
        expect($entry['description'] ?? null)->not->toBeEmpty();
        expect($entry['video_url'] ?? '')->toMatch('/^https:\/\/www\.youtube\.com\/watch\?v=[A-Za-z0-9_-]+$/');
        expect($entry['spotify_url'] ?? '')->toMatch('/^https:\/\/open\.spotify\.com\/(?:episode\/[A-Za-z0-9]+|show\/28cbLx8VKFE0j3xdbRhxsO)$/');
        expect($entry['thumbnail_url'] ?? '')->toMatch('/^https:\/\/i\.ytimg\.com\/vi\/[A-Za-z0-9_-]+\/maxresdefault\.jpg$/');
        expect(sentenceCount($entry['summary']))->toBeLessThanOrEqual(2);
        expect($entry['transcript'] ?? null)->not->toBeEmpty();
        expect($entry['date'] ?? '')->toMatch('/^\d{4}-\d{2}-\d{2}$/');
        expect($entry['published_at'] ?? '')->toMatch('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/');
    }
});
test('podcast entries use publication times for same day ordering', function () {
    $paths = glob(base_path('content/collections/podcasts/*.md'));

    $entries = array_map(function (string $path): array {
        $entry = parseFrontMatter($path);

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

    expect(array_column($marchSecondEntries, 'file'))->toBe([
        '2026-03-02.andreas-braun-mongodb-on-rethinking-databases.md',
        '2026-03-02.nativephp-with-shane-rosenthal.md',
        '2026-03-02.junior-adwise-on-laravel-statamic-side-projects.md',
        '2026-03-02.mattias-geniar-oh-dear-on-monitoring-uptime-ai-monitoring.md',
        '2026-03-02.laracon-eu-2026-kickoff-first-impressions-talks-what-we-re-excited-about.md',
        '2026-03-02.pete-heslop-ceo-of-steadfast-collective-and-official-laravel-partner.md',
    ]);
});
test('podcast summaries and descriptions use dutch copy', function () {
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
        $entry = parseFrontMatter($path);

        foreach (['summary', 'description'] as $field) {
            $this->assertDoesNotMatchRegularExpression(
                '/'.implode('|', $englishImportPhrases).'/i',
                $entry[$field] ?? '',
                basename($path).': '.$field,
            );
        }
    }

    $steve = parseFrontMatter(base_path(
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
});
test('podcast markdown includes video url and transcript', function () {
    $entry = new class implements Entry
    {
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
});
/**
 * @return array<string, mixed>
 */
/**
 * @return array<string, mixed>
 */
/**
 * @param  array<string, mixed>  $node
 * @return array<string, array<string, mixed>>
 */
function sentenceCount(string $text): int
{
    preg_match_all('/[.!?](?:\s|$)/', $text, $matches);

    return max(1, count($matches[0]));
}
