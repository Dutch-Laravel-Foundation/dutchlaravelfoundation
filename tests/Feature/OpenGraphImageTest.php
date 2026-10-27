<?php

declare(strict_types=1);
use App\Services\Seo\SeoMetadata;
use Illuminate\Support\Facades\File;
use Statamic\Entries\Entry;
use Statamic\Facades\Entry as EntryRepository;
use Statamic\Structures\Page;

test('pages without a featured image use the default open graph image', function () {
    $response = $this->get('/');

    $response->assertOk();
    assertMetaContent($response->getContent(), 'property', 'og:image', config('app.url').'/og-image.jpg?v=4');
    assertMetaContent($response->getContent(), 'name', 'twitter:card', 'summary_large_image');
    assertMetaContent($response->getContent(), 'name', 'twitter:image', config('app.url').'/og-image.jpg?v=4');
});
test('default open graph image is a jpeg large enough for social cards', function () {
    [$width, $height, $type] = getimagesize(public_path('og-image.jpg'));

    expect($width)->toBeGreaterThanOrEqual(1200);
    expect($height)->toBeGreaterThanOrEqual(600);
    expect($type)->toBe(IMAGETYPE_JPEG);
    expect(filesize(public_path('og-image.jpg')))->toBeLessThan(5 * 1024 * 1024);
});
test('default open graph image uses baseline jpeg encoding', function () {
    $image = file_get_contents(public_path('og-image.jpg'));

    expect(str_contains($image, "\xFF\xC0"))->toBeTrue();
    expect(str_contains($image, "\xFF\xC2"))->toBeFalse();
});
test('articles with a featured image use a generated card as the open graph image', function (string $collection) {
    $entry = firstArticleWithFeaturedImage($collection);

    if ($entry === null) {
        $this->markTestSkipped("No published {$collection} entry with a featured image present");
    }

    $response = $this->get($entry->url());

    $response->assertOk();
    $image = generatedCardUrl($entry);

    expect($image)->toStartWith(config('app.url')."/og/{$entry->id()}.jpg?v=");
    assertMetaContent($response->getContent(), 'property', 'og:image', $image);
    assertMetaContent($response->getContent(), 'name', 'twitter:image', $image);
})->with(['knowledge', 'insights']);
test('pages without a featured image get a generated card with their title', function () {
    $response = $this->get('/lid-worden');

    $response->assertOk();
    expect(metaContent($response->getContent(), 'og:image'))
        ->toMatch('#^'.preg_quote(config('app.url'), '#').'/og/[0-9a-f-]{36}\.jpg\?v=[0-9a-f]{16}$#');
});
test('the generated card is a 1200x630 jpeg', function () {
    useTemporaryStorage();
    $entry = firstArticleWithFeaturedImage('knowledge');
    $url = generatedCardUrl($entry);

    $response = $this->get($url);

    $response->assertOk();
    $response->assertHeader('Content-Type', 'image/jpeg');
    expect($response->headers->get('Cache-Control'))->toContain('immutable');

    [$width, $height, $type] = getimagesize($response->baseResponse->getFile()->getPathname());

    expect([$width, $height, $type])->toBe([1200, 630, IMAGETYPE_JPEG]);
});
test('the generated card falls back to the group photo when an entry has no featured image', function () {
    useTemporaryStorage();
    $entry = EntryRepository::findByUri('/lid-worden');
    $entry = $entry instanceof Page ? $entry->entry() : $entry;

    $this->get("/og/{$entry->id()}.jpg")
        ->assertOk()
        ->assertHeader('Cache-Control', 'max-age=3600, public');
});
test('no card is served for unknown or unpublished entries', function () {
    useTemporaryStorage();
    $draft = EntryRepository::query()
        ->where('collection', 'knowledge')
        ->where('published', false)
        ->first();

    $this->get('/og/00000000-0000-4000-8000-000000000000.jpg')->assertNotFound();

    if ($draft !== null) {
        $this->get("/og/{$draft->id()}.jpg")->assertNotFound();
    }
});
test('podcast entries use their thumbnail as the social image', function () {
    $entry = firstPodcastWithThumbnail();

    if ($entry === null) {
        $this->markTestSkipped('No published podcast entry with a thumbnail URL present');
    }

    $response = $this->get($entry->url());
    $thumbnailUrl = $entry->get('thumbnail_url');

    $response->assertOk();
    assertMetaContent($response->getContent(), 'property', 'og:image', $thumbnailUrl);
    assertMetaContent($response->getContent(), 'name', 'twitter:image', $thumbnailUrl);
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
function assertMetaContent(string $html, string $attribute, string $name, string $content): void
{
    expect($html)->toMatch('/<meta\s+'.$attribute.'="'.preg_quote($name, '/').'"\s+content="'.preg_quote($content, '/').'"[^>]*>/');
}
function generatedCardUrl(Entry $entry): string
{
    $card = app(SeoMetadata::class)->openGraphCard($entry);

    return config('app.url')."/og/{$entry->id()}.jpg?v={$card->fingerprint()}";
}
function metaContent(string $html, string $property): ?string
{
    preg_match('/<meta\s+property="'.preg_quote($property, '/').'"\s+content="([^"]*)"/', $html, $matches);

    return isset($matches[1]) ? html_entity_decode($matches[1]) : null;
}
function useTemporaryStorage(): void
{
    $path = sys_get_temp_dir().'/dlf-og-'.bin2hex(random_bytes(4));
    app()->useStoragePath($path);
    test()->beforeApplicationDestroyed(fn () => File::deleteDirectory($path));
}
