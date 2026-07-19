<?php

declare(strict_types=1);

namespace Tests\Feature;

use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use Tests\TestCase;

class EditorialPaginationTest extends TestCase
{
    public function testNewsPaginationUsesCompactThreeColumnNavigation(): void
    {
        $response = $this->get('/nieuws?page=2');

        $response->assertOk();

        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML($response->getContent());
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($document);
        $navigation = $xpath->query('//nav[contains(concat(" ", normalize-space(@class), " "), " editorial-pagination ")]');
        $newer = $xpath->query('//nav[contains(concat(" ", normalize-space(@class), " "), " editorial-pagination ")]/a[contains(concat(" ", normalize-space(@class), " "), " editorial-pagination__link--newer ")]');
        $status = $xpath->query('//nav[contains(concat(" ", normalize-space(@class), " "), " editorial-pagination ")]/span[contains(concat(" ", normalize-space(@class), " "), " editorial-pagination__status ")]');
        $older = $xpath->query('//nav[contains(concat(" ", normalize-space(@class), " "), " editorial-pagination ")]/a[contains(concat(" ", normalize-space(@class), " "), " editorial-pagination__link--older ")]');

        $this->assertInstanceOf(DOMNodeList::class, $navigation);
        $this->assertInstanceOf(DOMNodeList::class, $newer);
        $this->assertInstanceOf(DOMNodeList::class, $status);
        $this->assertInstanceOf(DOMNodeList::class, $older);
        $this->assertCount(1, $navigation);
        $this->assertCount(1, $newer);
        $this->assertCount(1, $status);
        $this->assertCount(1, $older);

        $newerLink = $newer->item(0);
        $pageStatus = $status->item(0);
        $olderLink = $older->item(0);

        $this->assertInstanceOf(DOMElement::class, $newerLink);
        $this->assertInstanceOf(DOMElement::class, $pageStatus);
        $this->assertInstanceOf(DOMElement::class, $olderLink);
        $this->assertSame('← Nieuwer', trim($newerLink->textContent));
        $this->assertSame('2 / 8', trim($pageStatus->textContent));
        $this->assertSame('Ouder →', trim($olderLink->textContent));
        $this->assertStringEndsWith('/nieuws?page=1', $newerLink->getAttribute('href'));
        $this->assertStringEndsWith('/nieuws?page=3', $olderLink->getAttribute('href'));
    }
}
