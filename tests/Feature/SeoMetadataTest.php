<?php

declare(strict_types=1);
use App\Services\Seo\SeoMetadata;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;

test('draft entries do not provide seo metadata', function () {
    $seo = resolve(SeoMetadata::class);

    $this->app->instance('request', Request::create('/leden/mollie'));
    expect($seo->currentEntry())->toBeNull();

    $this->app->instance('request', Request::create('/leden/emble'));
    expect($seo->currentEntry()?->slug())->toBe('emble');
});
test('homepage has canonical metadata and organization structured data', function () {
    $response = $this->get('/?campaign=test');

    $response->assertOk();

    $xpath = xpath($response);
    $canonicalUrl = rtrim(config('app.url'), '/').'/';

    expect(attribute($xpath, '//link[@rel="canonical"]', 'href'))->toBe($canonicalUrl);
    expect(attribute($xpath, '//meta[@property="og:url"]', 'content'))->toBe($canonicalUrl);
    expect(text($xpath, '//title'))->toBe('Dutch Laravel Foundation | Laravel-community Nederland');
    expect(attribute($xpath, '//meta[@name="description"]', 'content'))->toStartWith('De Dutch Laravel Foundation stimuleert');

    $graph = jsonLdGraph($xpath);
    $organization = graphNode($graph, 'Organization');

    expect($organization['name'])->toBe('Dutch Laravel Foundation');
    expect($organization['@id'])->toBe($canonicalUrl.'#organization');
    expect($organization['email'])->toBe('info@dutchlaravelfoundation.nl');
    expect($organization['address']['addressLocality'])->toBe('Zoetermeer');
});
test('paginated indexes have self referencing canonical metadata', function () {
    foreach (['/kennis', '/nieuws', '/podcast'] as $path) {
        $response = $this->get("{$path}?page=2&utm_source=test");

        $response->assertOk();

        $xpath = xpath($response);
        $canonicalUrl = rtrim(config('app.url'), '/')."{$path}?page=2";

        expect(attribute($xpath, '//link[@rel="canonical"]', 'href'))->toBe($canonicalUrl);
        expect(attribute($xpath, '//meta[@property="og:url"]', 'content'))->toBe($canonicalUrl);
    }
});
test('filtered indexes canonicalize to their unfiltered listing', function () {
    $response = $this->get('/kennis?category=Tooling&page=2');

    $response->assertOk();

    $xpath = xpath($response);
    $canonicalUrl = rtrim(config('app.url'), '/').'/kennis';

    expect(attribute($xpath, '//link[@rel="canonical"]', 'href'))->toBe($canonicalUrl);
    expect(attribute($xpath, '//meta[@property="og:url"]', 'content'))->toBe($canonicalUrl);
});
test('knowledge article uses its introduction and author in structured data', function () {
    $response = $this->get('/kennis/het-belang-van-toegankelijke-websites');

    $response->assertOk();

    $xpath = xpath($response);
    $description = attribute($xpath, '//meta[@name="description"]', 'content');

    expect($description)->toStartWith('We willen in ons vakgebied');
    $this->assertNotSame('De kennis- en brancheorganisatie voor Laravel developers', $description);
    expect(attribute($xpath, '//meta[@property="og:type"]', 'content'))->toBe('article');

    $article = graphNode(jsonLdGraph($xpath), 'Article');

    expect($article['headline'])->toBe('Het belang van toegankelijke websites');
    expect($article['author'][0]['@type'])->toBe('Person');
    expect($article['author'][0]['name'])->not->toBeEmpty();
    expect($article['publisher']['@id'])->toBe(rtrim(config('app.url'), '/').'/#organization');
});
test('news podcast and case pages expose collection specific structured data', function () {
    $pages = [
        '/nieuws/van-der-arend-automatisering-korte-lijnen-laravel-als-vaste-basis' => 'NewsArticle',
        '/podcast/20-jaar-laravel-carriere-pixel-industries-tot-zig-dennis-koster-dutch-laravel-foundation' => 'PodcastEpisode',
        '/cases/dropday' => 'CreativeWork',
    ];

    foreach ($pages as $path => $expectedType) {
        $response = getWithFreshRequestScope($path);

        $response->assertOk();

        $xpath = xpath($response);
        $canonicalUrl = rtrim(config('app.url'), '/').$path;

        expect(attribute($xpath, '//link[@rel="canonical"]', 'href'))->toBe($canonicalUrl, "Canonical URL mismatch for [{$path}].");
        expect(graphNode(jsonLdGraph($xpath), $expectedType))->not->toBeEmpty("Missing {$expectedType} schema for [{$path}].");
    }
});
test('an explicit branded title is not suffixed with the site name again', function () {
    $response = $this->get(
        '/podcast/20-jaar-laravel-carriere-pixel-industries-tot-zig-dennis-koster-dutch-laravel-foundation',
    );

    $response->assertOk();

    $title = text(xpath($response), '//title');

    expect(substr_count($title, 'Dutch Laravel Foundation'))->toBe(1);
});
test('an explicit unbranded title receives the site name', function () {
    $response = $this->get('/kennis/razendsnelle-php-tooling-met-mago');

    $response->assertOk();

    expect(text(xpath($response), '//title'))->toBe('Razendsnelle PHP tooling met Mago | Dutch Laravel Foundation');
});
test('editorial body sections start at h2', function () {
    $response = $this->withHeaders(inertiaHeaders())
        ->get('/nieuws/wij-stellen-voor-kobalt-digital');

    $response->assertOk();

    $blocks = $response->json('props.editorial.content');

    expect($blocks)->toBeArray();
    expect(firstHeadingFromBlocks($blocks))->toStartWith('<h2');
});
test('event body sections start at h2', function () {
    $response = $this->withHeaders(inertiaHeaders())
        ->get('/events/dutch-laravel-foundation-meetup');

    $response->assertOk();

    $blocks = $response->json('props.editorial.content');

    expect($blocks)->toBeArray();
    expect(firstHeadingFromBlocks($blocks))->toStartWith('<h2');
});
test('core landing pages have specific descriptions', function () {
    $pages = [
        '/wat-is-laravel' => 'Laravel is een populair open-source PHP-framework',
        '/leden' => 'Vind ervaren Nederlandse Laravel-bureaus',
        '/lid-worden' => 'Word lid van de Dutch Laravel Foundation',
        '/over-ons' => 'Maak kennis met de Dutch Laravel Foundation',
        '/stagebank' => 'Vind een Laravel-stage bij aangesloten organisaties',
        '/cases' => 'Bekijk cases van Nederlandse organisaties',
        '/kennis' => 'Lees praktische artikelen over Laravel',
        '/nieuws' => 'Blijf op de hoogte van nieuws',
        '/podcast' => 'Luister naar gesprekken met developers',
        '/agenda' => 'Bekijk aankomende Laravel-meetups',
    ];

    foreach ($pages as $path => $expectedStart) {
        $response = getWithFreshRequestScope($path);

        $response->assertOk();

        $description = attribute(xpath($response), '//meta[@name="description"]', 'content');

        expect($description)->toStartWith($expectedStart, "Unexpected description for [{$path}].");
    }
});
test('member and internship descriptions use their own content', function () {
    $pages = [
        '/leden/goedemiddag' => 'Bij Goedemiddag! draait het niet alleen om techniek.',
        '/stagebank/qlic' => 'Als backend stagiair ga je aan de slag met Laravel',
    ];

    foreach ($pages as $path => $expectedStart) {
        $response = getWithFreshRequestScope($path);

        $response->assertOk();

        expect(attribute(xpath($response), '//meta[@name="description"]', 'content'))->toStartWith($expectedStart);
    }
});
test('member and internship pages have distinct titles', function () {
    $memberResponse = getWithFreshRequestScope('/leden/qlic');
    $internshipResponse = getWithFreshRequestScope('/stagebank/qlic');

    $memberResponse->assertOk();
    $internshipResponse->assertOk();

    expect(text(xpath($memberResponse), '//title'))->toBe('Qlic | Dutch Laravel Foundation');
    expect(text(xpath($internshipResponse), '//title'))->toBe('Laravel-stage bij Qlic | Dutch Laravel Foundation');
});
test('shared footer call to action uses an h2 heading', function () {
    $response = $this->get('/');

    $response->assertOk();

    $footerCta = file_get_contents(resource_path('js/components/site/FooterCta.tsx'));

    $this->assertNotFalse($footerCta);
    $this->assertStringContainsString('<h2 id="footer-cta-title">', $footerCta);
});
/** @return array<string, string> */
function getWithFreshRequestScope(string $uri): TestResponse
{
    app()->forgetScopedInstances();

    return test()->get($uri);
}
/** @param array<int, mixed> $blocks */
function firstHeadingFromBlocks(array $blocks): string
{
    foreach ($blocks as $block) {
        if (! is_array($block) || ! is_string($block['html'] ?? null)) {
            continue;
        }

        if (preg_match('/<h[1-6]\b[^>]*>/', $block['html'], $matches) === 1) {
            return $matches[0];
        }
    }

    test()->fail('No heading found in the Inertia editorial content DTO.');
}
function xpath(TestResponse $response): DOMXPath
{
    $document = new DOMDocument;
    $previous = libxml_use_internal_errors(true);
    $document->loadHTML($response->getContent());
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    return new DOMXPath($document);
}
function attribute(DOMXPath $xpath, string $query, string $attribute): string
{
    $node = $xpath->query($query)->item(0);

    expect($node)->toBeInstanceOf(DOMElement::class, "No element found for [{$query}].");

    return $node->getAttribute($attribute);
}
function text(DOMXPath $xpath, string $query): string
{
    $node = $xpath->query($query)->item(0);

    expect($node)->not->toBeNull("No element found for [{$query}].");

    return trim($node->textContent);
}
/**
 * @return array<int, array<string, mixed>>
 *
 * @throws JsonException
 */
function jsonLdGraph(DOMXPath $xpath): array
{
    $json = text($xpath, '//script[@type="application/ld+json"]');
    $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

    expect($data['@context'])->toBe('https://schema.org');
    expect($data['@graph'])->toBeArray();

    return $data['@graph'];
}
/**
 * @param  array<int, array<string, mixed>>  $graph
 * @return array<string, mixed>
 */
function graphNode(array $graph, string $type): array
{
    $node = collect($graph)->firstWhere('@type', $type);

    expect($node)->toBeArray("No JSON-LD node with type [{$type}] found.");

    return $node;
}
