<?php

declare(strict_types=1);

namespace Tests\Feature;

use Statamic\Entries\Entry;
use Statamic\Facades\Entry as EntryRepository;
use Tests\TestCase;

class OpenGraphImageTest extends TestCase
{
    public function testPagesWithoutAFeaturedImageUseTheDefaultOpenGraphImage(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $this->assertStringContainsString(
            '<meta property="og:image" content="' . config('app.url') . '/og-image.png">',
            $response->getContent(),
        );
        $this->assertStringContainsString(
            '<meta name="twitter:card" content="summary_large_image">',
            $response->getContent(),
        );
        $this->assertStringContainsString(
            '<meta name="twitter:image" content="' . config('app.url') . '/og-image.png">',
            $response->getContent(),
        );
    }

    public function testKnowledgeArticlesUseTheirFeaturedImageAsTheOpenGraphImage(): void
    {
        $entry = $this->firstArticleWithFeaturedImage('knowledge');

        if ($entry === null) {
            $this->markTestSkipped('No published knowledge article with a featured image present');
        }

        $response = $this->get($entry->url());

        $response->assertOk();
        $this->assertStringContainsString(
            '<meta property="og:image" content="' . config('app.url') . $entry->augmentedValue('featured_image')->value()->url() . '">',
            $response->getContent(),
        );
        $this->assertStringContainsString(
            '<meta name="twitter:image" content="' . config('app.url') . $entry->augmentedValue('featured_image')->value()->url() . '">',
            $response->getContent(),
        );
    }

    public function testNewsArticlesUseTheirFeaturedImageAsTheOpenGraphImage(): void
    {
        $entry = $this->firstArticleWithFeaturedImage('insights');

        if ($entry === null) {
            $this->markTestSkipped('No published news article with a featured image present');
        }

        $response = $this->get($entry->url());

        $response->assertOk();
        $this->assertStringContainsString(
            '<meta property="og:image" content="' . config('app.url') . $entry->augmentedValue('featured_image')->value()->url() . '">',
            $response->getContent(),
        );
        $this->assertStringContainsString(
            '<meta name="twitter:image" content="' . config('app.url') . $entry->augmentedValue('featured_image')->value()->url() . '">',
            $response->getContent(),
        );
    }

    private function firstArticleWithFeaturedImage(string $collection): ?Entry
    {
        return EntryRepository::query()
            ->where('collection', $collection)
            ->where('published', true)
            ->get()
            ->first(fn (Entry $entry): bool => filled($entry->get('featured_image')));
    }
}
