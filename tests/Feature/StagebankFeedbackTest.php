<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class StagebankFeedbackTest extends TestCase
{
    public function testStagebankOverviewUsesUpdatedFilterHeading(): void
    {
        $response = $this->get('/stagebank');

        $response->assertOk();
        $response->assertSee('Wij helpen je zoeken!', false);
        $response->assertDontSee('Kunnen wij je helpen zoeken?', false);
    }

    public function testInternshipDetailUsesUpdatedApplyButtonLabel(): void
    {
        $response = $this->get('/stagebank/qlic');

        $response->assertOk();
        $response->assertSee('Bekijk stage vacatures', false);
        $response->assertDontSee('Solliciteren', false);
    }

    public function testInternshipDetailMergesCompanyInformationIntoTheHeader(): void
    {
        $response = $this->get('/stagebank/superscanner');

        $response->assertOk();
        $response->assertSee('href="https://superscanner.nl"', false);
        $response->assertSee('>Locatie<', false);
        $response->assertSee('>Website<', false);
        $response->assertSee('Stage contactpersoon', false);
        $response->assertDontSee('Stagebedrijf', false);
    }

    public function testInternshipTilesDoNotRenderDuplicateCompanyNameLine(): void
    {
        $template = file_get_contents(resource_path('views/templates/internships/index.antlers.html'));

        $this->assertNotFalse($template);
        $this->assertStringNotContainsString('x-text="item.member_title"', $template);
    }

    public function testStagebankOverviewRendersMemberLogos(): void
    {
        $this->get('/stagebank')
            ->assertOk()
            ->assertSee('data-logo="/assets/uploads/members/ux-logo.svg"', false);
    }

    public function testInternshipDetailRendersTheMemberLogo(): void
    {
        $this->get('/stagebank/ux')
            ->assertOk()
            ->assertSee('<img src="/assets/uploads/members/ux-logo.svg"', false);
    }
}
