<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class WizardLayoutTest extends TestCase
{
    public function test_navigation_spacing_belongs_to_the_section_instead_of_the_button_row(): void
    {
        $component = file_get_contents(resource_path('js/components/forms-react/SalesFunnelWizard.tsx'));

        $this->assertNotFalse($component);
        $this->assertStringContainsString(
            'className="dlf-wizard-navigation',
            $component,
        );
        $this->assertStringContainsString(
            'max-w-2xl mx-auto px-6 pb-0 sm:px-10 md:px-14',
            $component,
        );
    }
}
