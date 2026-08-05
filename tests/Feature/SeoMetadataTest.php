<?php

declare(strict_types=1);
use Illuminate\Testing\TestResponse;

it('homepage has canonical metadata and organization structured data', function () {
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
it('knowledge article uses its introduction and author in structured data', function () {
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
it('news podcast and case pages expose collection specific structured data', function () {
    $pages = [
        '/nieuws/van-der-arend-automatisering-korte-lijnen-laravel-als-vaste-basis' => 'NewsArticle',
        '/podcast/20-jaar-laravel-carriere-pixel-industries-tot-zig-dennis-koster-dutch-laravel-foundation' => 'PodcastEpisode',
        '/cases/dropday' => 'CreativeWork',
    ];

    foreach ($pages as $path => $expectedType) {
        $response = $this->get($path);

        $response->assertOk();

        $xpath = xpath($response);
        $canonicalUrl = rtrim(config('app.url'), '/').$path;

        expect(attribute($xpath, '//link[@rel="canonical"]', 'href'))->toBe($canonicalUrl, "Canonical URL mismatch for [{$path}].");
        expect(graphNode(jsonLdGraph($xpath), $expectedType))->not->toBeEmpty("Missing {$expectedType} schema for [{$path}].");
    }
});
it('an explicit branded title is not suffixed with the site name again', function () {
    $response = $this->get(
        '/podcast/20-jaar-laravel-carriere-pixel-industries-tot-zig-dennis-koster-dutch-laravel-foundation',
    );

    $response->assertOk();

    $title = text(xpath($response), '//title');

    expect(substr_count($title, 'Dutch Laravel Foundation'))->toBe(1);
});
it('an explicit unbranded title receives the site name', function () {
    $response = $this->get('/kennis/razendsnelle-php-tooling-met-mago');

    $response->assertOk();

    expect(text(xpath($response), '//title'))->toBe('Razendsnelle PHP tooling met Mago | Dutch Laravel Foundation');
});
it('editorial body sections start at h2', function () {
    $response = $this->get('/nieuws/wij-stellen-voor-kobalt-digital');

    $response->assertOk();

    $xpath = xpath($response);
    $heading = $xpath->query('//article//*[self::h2 or self::h3][1]')->item(0);

    expect($heading)->toBeInstanceOf(DOMElement::class);
    expect($heading->tagName)->toBe('h2');
});
it('event body sections start at h2', function () {
    $response = $this->get('/events/dutch-laravel-foundation-meetup');

    $response->assertOk();

    $xpath = xpath($response);
    $headings = $xpath->query(
        '//*[@id="main-content"]//*[self::h1 or self::h2 or self::h3]',
    );

    expect($headings->count())->toBeGreaterThanOrEqual(2);
    expect($headings->item(0)->nodeName)->toBe('h1');
    expect($headings->item(1)->nodeName)->toBe('h2');
});
it('core landing pages have specific descriptions', function () {
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
        $response = $this->get($path);

        $response->assertOk();

        $description = attribute(xpath($response), '//meta[@name="description"]', 'content');

        expect($description)->toStartWith($expectedStart, "Unexpected description for [{$path}].");
    }
});
it('member and internship descriptions use their own content', function () {
    $pages = [
        '/leden/goedemiddag' => 'Bij Goedemiddag! draait het niet alleen om techniek.',
        '/stagebank/qlic' => 'Als backend stagiair ga je aan de slag met Laravel',
    ];

    foreach ($pages as $path => $expectedStart) {
        $response = $this->get($path);

        $response->assertOk();

        expect(attribute(xpath($response), '//meta[@name="description"]', 'content'))->toStartWith($expectedStart);
    }
});
it('member and internship pages have distinct titles', function () {
    $memberResponse = $this->get('/leden/qlic');
    $internshipResponse = $this->get('/stagebank/qlic');

    $memberResponse->assertOk();
    $internshipResponse->assertOk();

    expect(text(xpath($memberResponse), '//title'))->toBe('Qlic | Dutch Laravel Foundation');
    expect(text(xpath($internshipResponse), '//title'))->toBe('Laravel-stage bij Qlic | Dutch Laravel Foundation');
});
it('shared footer call to action uses an h2 heading', function () {
    $response = $this->get('/');

    $response->assertOk();

    $node = xpath($response)->query('//*[@id="footer-cta-title"]')->item(0);

    expect($node)->toBeInstanceOf(DOMElement::class);
    expect($node->tagName)->toBe('h2');
});
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
