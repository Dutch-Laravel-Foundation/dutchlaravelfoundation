<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class WizardLayoutTest extends TestCase
{
    public function testNavigationSpacingBelongsToTheSectionInsteadOfTheButtonRow(): void
    {
        $template = file_get_contents(resource_path('views/partials/wizard/_sales-funnel.antlers.html'));

        $this->assertNotFalse($template);
        $this->assertStringContainsString(
            'class="dlf-wizard-navigation w-full py-14 sm:py-16 md:py-20"',
            $template,
        );
        $this->assertStringContainsString(
            'class="max-w-2xl mx-auto px-6 pb-0 sm:px-10 md:px-14 flex justify-between items-center"',
            $template,
        );
    }
}
