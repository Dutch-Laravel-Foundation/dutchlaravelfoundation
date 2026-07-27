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
            ->assertSee('href="'.$url.'?lang=en"', false);
    }

    public function testDetailExposesItsSkillAsASeparateView(): void
    {
        $url = '/best-practices/routing-use-form-request-classes';

        $this->get($url)
            ->assertOk()
            ->assertSee('Skill')
            ->assertSee('href="'.$url.'?view=skill"', false);
    }
}
