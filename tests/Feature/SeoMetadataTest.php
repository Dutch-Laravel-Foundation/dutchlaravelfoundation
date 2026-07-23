<?php

declare(strict_types=1);

namespace Tests\Feature;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Testing\TestResponse;
use JsonException;
use Tests\TestCase;

class SeoMetadataTest extends TestCase
{
    public function testHomepageHasCanonicalMetadataAndOrganizationStructuredData(): void
    {
        $response = $this->get('/?campaign=test');

        $response->assertOk();

        $xpath = $this->xpath($response);
        $canonicalUrl = rtrim(config('app.url'), '/') . '/';

        $this->assertSame($canonicalUrl, $this->attribute($xpath, '//link[@rel="canonical"]', 'href'));
        $this->assertSame($canonicalUrl, $this->attribute($xpath, '//meta[@property="og:url"]', 'content'));
        $this->assertSame(
            'Dutch Laravel Foundation | Laravel-community Nederland',
            $this->text($xpath, '//title'),
        );
        $this->assertStringStartsWith(
            'De Dutch Laravel Foundation stimuleert',
            $this->attribute($xpath, '//meta[@name="description"]', 'content'),
        );

        $graph = $this->jsonLdGraph($xpath);
        $organization = $this->graphNode($graph, 'Organization');

        $this->assertSame('Dutch Laravel Foundation', $organization['name']);
        $this->assertSame($canonicalUrl . '#organization', $organization['@id']);
        $this->assertSame('info@dutchlaravelfoundation.nl', $organization['email']);
        $this->assertSame('Zoetermeer', $organization['address']['addressLocality']);
    }

    public function testKnowledgeArticleUsesItsIntroductionAndAuthorInStructuredData(): void
    {
        $response = $this->get('/kennis/het-belang-van-toegankelijke-websites');

        $response->assertOk();

        $xpath = $this->xpath($response);
        $description = $this->attribute($xpath, '//meta[@name="description"]', 'content');

        $this->assertStringStartsWith('We willen in ons vakgebied', $description);
        $this->assertNotSame('De kennis- en brancheorganisatie voor Laravel developers', $description);
        $this->assertSame('article', $this->attribute($xpath, '//meta[@property="og:type"]', 'content'));

        $article = $this->graphNode($this->jsonLdGraph($xpath), 'Article');

        $this->assertSame('Het belang van toegankelijke websites', $article['headline']);
        $this->assertSame('Person', $article['author'][0]['@type']);
        $this->assertNotEmpty($article['author'][0]['name']);
        $this->assertSame(
            rtrim(config('app.url'), '/') . '/#organization',
            $article['publisher']['@id'],
        );
    }

    public function testNewsPodcastAndCasePagesExposeCollectionSpecificStructuredData(): void
    {
        $pages = [
            '/nieuws/van-der-arend-automatisering-korte-lijnen-laravel-als-vaste-basis' => 'NewsArticle',
            '/podcast/20-jaar-laravel-carriere-pixel-industries-tot-zig-dennis-koster-dutch-laravel-foundation' => 'PodcastEpisode',
            '/cases/dropday' => 'CreativeWork',
        ];

        foreach ($pages as $path => $expectedType) {
            $response = $this->get($path);

            $response->assertOk();

            $xpath = $this->xpath($response);
            $canonicalUrl = rtrim(config('app.url'), '/') . $path;

            $this->assertSame(
                $canonicalUrl,
                $this->attribute($xpath, '//link[@rel="canonical"]', 'href'),
                "Canonical URL mismatch for [{$path}].",
            );
            $this->assertNotEmpty(
                $this->graphNode($this->jsonLdGraph($xpath), $expectedType),
                "Missing {$expectedType} schema for [{$path}].",
            );
        }
    }

    public function testAnExplicitBrandedTitleIsNotSuffixedWithTheSiteNameAgain(): void
    {
        $response = $this->get(
            '/podcast/20-jaar-laravel-carriere-pixel-industries-tot-zig-dennis-koster-dutch-laravel-foundation',
        );

        $response->assertOk();

        $title = $this->text($this->xpath($response), '//title');

        $this->assertSame(1, substr_count($title, 'Dutch Laravel Foundation'));
    }

    public function testAnExplicitUnbrandedTitleReceivesTheSiteName(): void
    {
        $response = $this->get('/kennis/razendsnelle-php-tooling-met-mago');

        $response->assertOk();

        $this->assertSame(
            'Razendsnelle PHP tooling met Mago | Dutch Laravel Foundation',
            $this->text($this->xpath($response), '//title'),
        );
    }

    public function testEditorialBodySectionsStartAtH2(): void
    {
        $response = $this->get('/nieuws/wij-stellen-voor-kobalt-digital');

        $response->assertOk();

        $xpath = $this->xpath($response);
        $heading = $xpath->query('//article//*[self::h2 or self::h3][1]')->item(0);

        $this->assertInstanceOf(DOMElement::class, $heading);
        $this->assertSame('h2', $heading->tagName);
    }

    public function testEventBodySectionsStartAtH2(): void
    {
        $response = $this->get('/events/dutch-laravel-foundation-meetup');

        $response->assertOk();

        $xpath = $this->xpath($response);
        $headings = $xpath->query(
            '//*[@id="main-content"]//*[self::h1 or self::h2 or self::h3]',
        );

        $this->assertGreaterThanOrEqual(2, $headings->count());
        $this->assertSame('h1', $headings->item(0)->nodeName);
        $this->assertSame('h2', $headings->item(1)->nodeName);
    }

    public function testCoreLandingPagesHaveSpecificDescriptions(): void
    {
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

            $description = $this->attribute(
                $this->xpath($response),
                '//meta[@name="description"]',
                'content',
            );

            $this->assertStringStartsWith(
                $expectedStart,
                $description,
                "Unexpected description for [{$path}].",
            );
        }
    }

    public function testMemberAndInternshipDescriptionsUseTheirOwnContent(): void
    {
        $pages = [
            '/leden/goedemiddag' => 'Bij Goedemiddag! draait het niet alleen om techniek.',
            '/stagebank/qlic' => 'Als backend stagiair ga je aan de slag met Laravel',
        ];

        foreach ($pages as $path => $expectedStart) {
            $response = $this->get($path);

            $response->assertOk();

            $this->assertStringStartsWith(
                $expectedStart,
                $this->attribute(
                    $this->xpath($response),
                    '//meta[@name="description"]',
                    'content',
                ),
            );
        }
    }

    public function testMemberAndInternshipPagesHaveDistinctTitles(): void
    {
        $memberResponse = $this->get('/leden/qlic');
        $internshipResponse = $this->get('/stagebank/qlic');

        $memberResponse->assertOk();
        $internshipResponse->assertOk();

        $this->assertSame(
            'Qlic | Dutch Laravel Foundation',
            $this->text($this->xpath($memberResponse), '//title'),
        );
        $this->assertSame(
            'Laravel-stage bij Qlic | Dutch Laravel Foundation',
            $this->text($this->xpath($internshipResponse), '//title'),
        );
    }

    public function testSharedFooterCallToActionUsesAnH2Heading(): void
    {
        $response = $this->get('/');

        $response->assertOk();

        $node = $this->xpath($response)->query('//*[@id="footer-cta-title"]')->item(0);

        $this->assertInstanceOf(DOMElement::class, $node);
        $this->assertSame('h2', $node->tagName);
    }

    private function xpath(TestResponse $response): DOMXPath
    {
        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML($response->getContent());
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($document);
    }

    private function attribute(DOMXPath $xpath, string $query, string $attribute): string
    {
        $node = $xpath->query($query)->item(0);

        $this->assertInstanceOf(DOMElement::class, $node, "No element found for [{$query}].");

        return $node->getAttribute($attribute);
    }

    private function text(DOMXPath $xpath, string $query): string
    {
        $node = $xpath->query($query)->item(0);

        $this->assertNotNull($node, "No element found for [{$query}].");

        return trim($node->textContent);
    }

    /**
     * @return array<int, array<string, mixed>>
     *
     * @throws JsonException
     */
    private function jsonLdGraph(DOMXPath $xpath): array
    {
        $json = $this->text($xpath, '//script[@type="application/ld+json"]');
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame('https://schema.org', $data['@context']);
        $this->assertIsArray($data['@graph']);

        return $data['@graph'];
    }

    /**
     * @param  array<int, array<string, mixed>>  $graph
     * @return array<string, mixed>
     */
    private function graphNode(array $graph, string $type): array
    {
        $node = collect($graph)->firstWhere('@type', $type);

        $this->assertIsArray($node, "No JSON-LD node with type [{$type}] found.");

        return $node;
    }
}
