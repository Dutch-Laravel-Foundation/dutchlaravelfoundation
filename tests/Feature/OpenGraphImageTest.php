<?php

declare(strict_types=1);
use Statamic\Entries\Entry;
use Statamic\Facades\Entry as EntryRepository;

it('pages without a featured image use the default open graph image', function () {
    $response = $this->get('/');

    $response->assertOk();
    $this->assertStringContainsString(
        '<meta property="og:image" content="'.config('app.url').'/og-image.jpg?v=4">',
        $response->getContent(),
    );
    $this->assertStringContainsString(
        '<meta name="twitter:card" content="summary_large_image">',
        $response->getContent(),
    );
    $this->assertStringContainsString(
        '<meta name="twitter:image" content="'.config('app.url').'/og-image.jpg?v=4">',
        $response->getContent(),
    );
});
it('default open graph image is a jpeg large enough for social cards', function () {
    [$width, $height, $type] = getimagesize(public_path('og-image.jpg'));

    expect($width)->toBeGreaterThanOrEqual(1200);
    expect($height)->toBeGreaterThanOrEqual(600);
    expect($type)->toBe(IMAGETYPE_JPEG);
    expect(filesize(public_path('og-image.jpg')))->toBeLessThan(5 * 1024 * 1024);
});
it('default open graph image uses baseline jpeg encoding', function () {
    $image = file_get_contents(public_path('og-image.jpg'));

    expect(str_contains($image, "\xFF\xC0"))->toBeTrue();
    expect(str_contains($image, "\xFF\xC2"))->toBeFalse();
});
it('knowledge articles use their featured image as the open graph image', function () {
    $entry = firstArticleWithFeaturedImage('knowledge');

    if ($entry === null) {
        $this->markTestSkipped('No published knowledge article with a featured image present');
    }

    $response = $this->get($entry->url());

    $response->assertOk();
    $this->assertStringContainsString(
        '<meta property="og:image" content="'.config('app.url').$entry->augmentedValue('featured_image')->value()->url().'">',
        $response->getContent(),
    );
    $this->assertStringContainsString(
        '<meta name="twitter:image" content="'.config('app.url').$entry->augmentedValue('featured_image')->value()->url().'">',
        $response->getContent(),
    );
});
it('news articles use their featured image as the open graph image', function () {
    $entry = firstArticleWithFeaturedImage('insights');

    if ($entry === null) {
        $this->markTestSkipped('No published news article with a featured image present');
    }

    $response = $this->get($entry->url());

    $response->assertOk();
    $this->assertStringContainsString(
        '<meta property="og:image" content="'.config('app.url').$entry->augmentedValue('featured_image')->value()->url().'">',
        $response->getContent(),
    );
    $this->assertStringContainsString(
        '<meta name="twitter:image" content="'.config('app.url').$entry->augmentedValue('featured_image')->value()->url().'">',
        $response->getContent(),
    );
});
it('podcast entries use their thumbnail as the social image', function () {
    $entry = firstPodcastWithThumbnail();

    if ($entry === null) {
        $this->markTestSkipped('No published podcast entry with a thumbnail URL present');
    }

    $response = $this->get($entry->url());
    $thumbnailUrl = $entry->get('thumbnail_url');

    $response->assertOk();
    $this->assertStringContainsString(
        '<meta property="og:image" content="'.$thumbnailUrl.'">',
        $response->getContent(),
    );
    $this->assertStringContainsString(
        '<meta name="twitter:image" content="'.$thumbnailUrl.'">',
        $response->getContent(),
    );
});
function firstArticleWithFeaturedImage(string $collection): ?Entry
{
    return EntryRepository::query()
        ->where('collection', $collection)
        ->where('published', true)
        ->get()
        ->first(fn (Entry $entry): bool => filled($entry->get('featured_image')));
}
function firstPodcastWithThumbnail(): ?Entry
{
    return EntryRepository::query()
        ->where('collection', 'podcasts')
        ->where('published', true)
        ->get()
        ->first(fn (Entry $entry): bool => filled($entry->get('thumbnail_url')));
}
