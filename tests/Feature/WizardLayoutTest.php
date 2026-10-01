<?php

declare(strict_types=1);

test('navigation spacing belongs to the section instead of the button row', function () {
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
});
