<?php

declare(strict_types=1);

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

uses(TestCase::class);

function seoHeadXpath(TestResponse $response): DOMXPath
{
    $document = new DOMDocument;
    $previous = libxml_use_internal_errors(true);
    $document->loadHTML($response->getContent());
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    return new DOMXPath($document);
}

function seoHeadAttribute(DOMXPath $xpath, string $query, string $attribute): string
{
    $node = $xpath->query($query)->item(0);

    expect($node)->toBeInstanceOf(DOMElement::class);

    return $node->getAttribute($attribute);
}

describe('SEO head consistency', function (): void {
    it('renders one authoritative set of SEO tags on every page family', function (string $path): void {
        $response = $this->get($path);

        $response->assertOk();

        $xpath = seoHeadXpath($response);
        $uniqueTags = [
            '//title',
            '//meta[@name="description"]',
            '//meta[@name="keywords"]',
            '//link[@rel="canonical"]',
            '//meta[@property="og:title"]',
            '//meta[@property="og:type"]',
            '//meta[@property="og:url"]',
            '//meta[@property="og:description"]',
            '//meta[@property="og:image"]',
            '//meta[@name="twitter:card"]',
            '//meta[@name="twitter:title"]',
            '//meta[@name="twitter:description"]',
            '//meta[@name="twitter:image"]',
            '//script[@type="application/ld+json"]',
            '//link[@rel="apple-touch-icon"]',
            '//link[@rel="manifest"]',
            '//link[@rel="mask-icon"]',
        ];

        foreach ($uniqueTags as $query) {
            expect($xpath->query($query))->toHaveCount(1, "Unexpected tag count for [{$query}] on [{$path}].");
        }
    })->with([
        'home' => '/',
        'news index' => '/nieuws',
        'news detail' => '/nieuws/wij-stellen-voor-kobalt-digital',
        'knowledge index' => '/kennis',
        'knowledge detail' => '/kennis/het-belang-van-toegankelijke-websites',
        'podcast index' => '/podcast',
        'podcast detail' => '/podcast/gebruik-laravel-en-ai',
        'agenda' => '/agenda',
        'event detail' => '/events/dutch-laravel-foundation-meetup',
        'cases index' => '/cases',
        'case detail' => '/cases/dropday',
        'members index' => '/leden',
        'member detail' => '/leden/qlic',
        'internships index' => '/stagebank',
        'internship detail' => '/stagebank/qlic',
        'public page' => '/over-ons',
        'contact form' => '/contact',
        'membership form' => '/lid-worden',
        'sales funnel' => '/aanvraag',
        'sales funnel thanks' => '/aanvraag/bedankt',
    ]);

    it('keeps title, description, canonical and social metadata aligned', function (string $path): void {
        $response = $this->get($path);

        $response->assertOk();

        $xpath = seoHeadXpath($response);
        $title = trim((string) $xpath->query('//title')->item(0)?->textContent);
        $description = seoHeadAttribute($xpath, '//meta[@name="description"]', 'content');
        $canonical = seoHeadAttribute($xpath, '//link[@rel="canonical"]', 'href');
        $image = seoHeadAttribute($xpath, '//meta[@property="og:image"]', 'content');

        expect($title)->not->toBeEmpty()
            ->and(seoHeadAttribute($xpath, '//meta[@property="og:title"]', 'content'))->toBe($title)
            ->and(seoHeadAttribute($xpath, '//meta[@name="twitter:title"]', 'content'))->toBe($title)
            ->and(seoHeadAttribute($xpath, '//meta[@property="og:description"]', 'content'))->toBe($description)
            ->and(seoHeadAttribute($xpath, '//meta[@name="twitter:description"]', 'content'))->toBe($description)
            ->and(seoHeadAttribute($xpath, '//meta[@property="og:url"]', 'content'))->toBe($canonical)
            ->and(seoHeadAttribute($xpath, '//meta[@name="twitter:image"]', 'content'))->toBe($image);
    })->with([
        'home' => '/',
        'news' => '/nieuws',
        'knowledge article' => '/kennis/het-belang-van-toegankelijke-websites',
        'podcast' => '/podcast/gebruik-laravel-en-ai',
        'event' => '/events/dutch-laravel-foundation-meetup',
        'case' => '/cases/dropday',
        'member' => '/leden/qlic',
        'contact' => '/contact',
        'membership' => '/lid-worden',
        'sales funnel' => '/aanvraag',
        'thanks' => '/aanvraag/bedankt',
    ]);

    it('matches the Antlers social-image rules for non-editorial featured images', function (string $path): void {
        $response = $this->get($path);

        $response->assertOk();

        $xpath = seoHeadXpath($response);
        $defaultImage = rtrim((string) config('app.url'), '/').'/og-image.jpg?v=4';

        expect(seoHeadAttribute($xpath, '//meta[@property="og:image"]', 'content'))->toBe($defaultImage)
            ->and(seoHeadAttribute($xpath, '//meta[@name="twitter:image"]', 'content'))->toBe($defaultImage);
    })->with([
        'case detail' => '/cases/dropday',
        'event detail' => '/events/dutch-laravel-foundation-meetup',
    ]);
});
