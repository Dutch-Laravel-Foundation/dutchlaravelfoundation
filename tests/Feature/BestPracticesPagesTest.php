<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class BestPracticesPagesTest extends TestCase
{
    public function testOverviewShowsImportedPracticesAndCategoryFilters(): void
    {
        $this->get('/best-practices')
            ->assertOk()
            ->assertSee('23 best practices')
            ->assertSee('Categorieën')
            ->assertSee('Routing')
            ->assertSee('Gebruik Form Request-classes')
            ->assertSee('/best-practices/routing-use-form-request-classes', false)
            ->assertSee('href="https://github.com/Dutch-Laravel-Foundation/best-practices"', false)
            ->assertSee('aria-label="Bekijk de best practices op GitHub"', false)
            ->assertSee('data-best-practice-filter', false);
    }

    public function testOverviewShowsTheBestPracticesMemberBanner(): void
    {
        $this->get('/best-practices')
            ->assertOk()
            ->assertSee('Vind een Laravel-partij die best practices toepast')
            ->assertSee('Bekijk onze leden')
            ->assertSee('href="/leden"', false);
    }

    public function testDetailShowsTheBestPracticesMemberBanner(): void
    {
        $this->get('/best-practices/routing-use-form-request-classes')
            ->assertOk()
            ->assertSee('Vind een Laravel-partij die best practices toepast')
            ->assertSee('Bekijk onze leden')
            ->assertSee('href="/leden"', false);
    }

    public function testDetailOnlyDisplaysTheDutchBestPractice(): void
    {
        $url = '/best-practices/routing-use-form-request-classes';

        foreach ([$url, $url.'?lang=en'] as $detailUrl) {
            $this->get($detailUrl)
                ->assertOk()
                ->assertSee('Gebruik Form Request-classes')
                ->assertSee('Menselijke begeleiding')
                ->assertDontSee('<h1>Use Form Request Classes</h1>', false)
                ->assertDontSee('Human Guidance')
                ->assertDontSee('class="best-practice-detail__language-sidebar"', false)
                ->assertDontSee('class="best-practice-language__flag"', false);
        }
    }

    public function testDetailDisplaysItsSkillAtTheBottom(): void
    {
        $url = '/best-practices/routing-use-form-request-classes';

        $this->get($url)
            ->assertOk()
            ->assertSee('id="best-practice-skill"', false)
            ->assertSee('data-code-copy', false)
            ->assertSee('class="language-markdown"', false)
            ->assertSee('## Core Guidance')
            ->assertDontSee('<h2>Core Guidance</h2>', false)
            ->assertSee('Bekijk op GitHub')
            ->assertDontSee('?view=skill');
    }

    public function testDetailLinksToTheDutchMarkdownTranslation(): void
    {
        $this->get('/best-practices/routing-use-form-request-classes')
            ->assertOk()
            ->assertSee('Help ons deze best practice in het Nederlands te verbeteren')
            ->assertSee(
                'href="https://github.com/Dutch-Laravel-Foundation/best-practices/edit/main/routing/use-form-request-classes/translations/nl.md"',
                false,
            );
    }
}
