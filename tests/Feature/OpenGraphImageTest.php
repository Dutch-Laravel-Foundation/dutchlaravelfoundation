<?php

declare(strict_types=1);

namespace Tests\Feature;

use Statamic\Entries\Entry;
use Statamic\Facades\Entry as EntryRepository;
use Tests\TestCase;

class OpenGraphImageTest extends TestCase
{
    public function test_pages_without_a_featured_image_use_the_default_open_graph_image(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $this->assertMetaContent($response->getContent(), 'property', 'og:image', config('app.url').'/og-image.jpg?v=4');
        $this->assertMetaContent($response->getContent(), 'name', 'twitter:card', 'summary_large_image');
        $this->assertMetaContent($response->getContent(), 'name', 'twitter:image', config('app.url').'/og-image.jpg?v=4');
    }

    public function test_default_open_graph_image_is_a_jpeg_large_enough_for_social_cards(): void
    {
        [$width, $height, $type] = getimagesize(public_path('og-image.jpg'));

        $this->assertGreaterThanOrEqual(1200, $width);
        $this->assertGreaterThanOrEqual(600, $height);
        $this->assertSame(IMAGETYPE_JPEG, $type);
        $this->assertLessThan(5 * 1024 * 1024, filesize(public_path('og-image.jpg')));
    }

    public function test_default_open_graph_image_uses_baseline_jpeg_encoding(): void
    {
        $image = file_get_contents(public_path('og-image.jpg'));

        $this->assertTrue(str_contains($image, "\xFF\xC0"));
        $this->assertFalse(str_contains($image, "\xFF\xC2"));
    }

    public function test_knowledge_articles_use_their_featured_image_as_the_open_graph_image(): void
    {
        $entry = $this->firstArticleWithFeaturedImage('knowledge');

        if ($entry === null) {
            $this->markTestSkipped('No published knowledge article with a featured image present');
        }

        $response = $this->get($entry->url());

        $response->assertOk();
        $image = config('app.url').$entry->augmentedValue('featured_image')->value()->url();

        $this->assertMetaContent($response->getContent(), 'property', 'og:image', $image);
        $this->assertMetaContent($response->getContent(), 'name', 'twitter:image', $image);
    }

    public function test_news_articles_use_their_featured_image_as_the_open_graph_image(): void
    {
        $entry = $this->firstArticleWithFeaturedImage('insights');

        if ($entry === null) {
            $this->markTestSkipped('No published news article with a featured image present');
        }

        $response = $this->get($entry->url());

        $response->assertOk();
        $image = config('app.url').$entry->augmentedValue('featured_image')->value()->url();

        $this->assertMetaContent($response->getContent(), 'property', 'og:image', $image);
        $this->assertMetaContent($response->getContent(), 'name', 'twitter:image', $image);
    }

    public function test_podcast_entries_use_their_thumbnail_as_the_social_image(): void
    {
        $entry = $this->firstPodcastWithThumbnail();

        if ($entry === null) {
            $this->markTestSkipped('No published podcast entry with a thumbnail URL present');
        }

        $response = $this->get($entry->url());
        $thumbnailUrl = $entry->get('thumbnail_url');

        $response->assertOk();
        $this->assertMetaContent($response->getContent(), 'property', 'og:image', $thumbnailUrl);
        $this->assertMetaContent($response->getContent(), 'name', 'twitter:image', $thumbnailUrl);
    }

    private function firstArticleWithFeaturedImage(string $collection): ?Entry
    {
        return EntryRepository::query()
            ->where('collection', $collection)
            ->where('published', true)
            ->get()
            ->first(fn (Entry $entry): bool => filled($entry->get('featured_image')));
    }

    private function firstPodcastWithThumbnail(): ?Entry
    {
        return EntryRepository::query()
            ->where('collection', 'podcasts')
            ->where('published', true)
            ->get()
            ->first(fn (Entry $entry): bool => filled($entry->get('thumbnail_url')));
    }

    private function assertMetaContent(
        string $html,
        string $attribute,
        string $name,
        string $content,
    ): void {
        $this->assertMatchesRegularExpression(
            '/<meta\s+'.$attribute.'="'.preg_quote($name, '/').'"\s+content="'.preg_quote($content, '/').'"[^>]*>/',
            $html,
        );
    }
}
