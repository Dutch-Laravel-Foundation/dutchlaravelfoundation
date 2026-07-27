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

    public function testOverviewShowsTheBecomeMemberBanner(): void
    {
        $this->get('/best-practices')
            ->assertOk()
            ->assertSee('Word ook lid van de Dutch Laravel Foundation')
            ->assertSee('href="/lid-worden"', false);
    }

    public function testDetailDefaultsToDutchAndExposesEnglishOption(): void
    {
        $url = '/best-practices/routing-use-form-request-classes';

        $this->get($url)
            ->assertOk()
            ->assertSee('Gebruik Form Request-classes')
            ->assertSee('Menselijke begeleiding')
            ->assertDontSee('Human Guidance')
            ->assertSee('href="'.$url.'?lang=en"', false)
            ->assertSee('class="best-practice-detail__language-sidebar"', false)
            ->assertSee('class="best-practice-language__flag"', false);
    }

    public function testDetailDisplaysItsSkillAtTheBottom(): void
    {
        $url = '/best-practices/routing-use-form-request-classes';

        $this->get($url)
            ->assertOk()
            ->assertSee('id="best-practice-skill"', false)
            ->assertSee('Laravel Boost skill')
            ->assertSee('Core Guidance')
            ->assertSee('Bekijk SKILL.md op GitHub')
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
