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

    public function testInternshipTilesDoNotRenderDuplicateCompanyNameLine(): void
    {
        $template = file_get_contents(resource_path('views/templates/internships/index.antlers.html'));

        $this->assertNotFalse($template);
        $this->assertStringNotContainsString('x-text="item.member_title"', $template);
    }
}
