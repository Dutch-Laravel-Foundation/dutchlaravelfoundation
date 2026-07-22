<?php

declare(strict_types=1);

namespace Tests\Feature;

use DOMDocument;
use DOMNodeList;
use DOMXPath;
use Tests\TestCase;

final class MemberDetailPageTest extends TestCase
{
    public function testMemberWithInternshipsRendersItsDetailPage(): void
    {
        $this->get('/leden/besite')
            ->assertOk()
            ->assertSee('Beschikbare stages bij Besite', false)
            ->assertSee('logo-besite.svg', false);
    }

    public function testMemberDetailMarksMemberNavigationItemActive(): void
    {
        $response = $this->get('/leden/pionect');

        $response->assertOk();

        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML($response->getContent());
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($document);
        $desktopLink = $xpath->query('//nav[contains(concat(" ", normalize-space(@class), " "), " dlf-desktop-navigation ")]//a[@href="/leden" and contains(concat(" ", normalize-space(@class), " "), " dlf-nav-link--active ")]');
        $mobileLink = $xpath->query('//nav[contains(concat(" ", normalize-space(@class), " "), " dlf-mobile-navigation ")]//a[@href="/leden" and contains(concat(" ", normalize-space(@class), " "), " dlf-mobile-nav-link--active ")]');

        $this->assertInstanceOf(DOMNodeList::class, $desktopLink);
        $this->assertInstanceOf(DOMNodeList::class, $mobileLink);
        $this->assertCount(1, $desktopLink);
        $this->assertCount(1, $mobileLink);
    }
}
