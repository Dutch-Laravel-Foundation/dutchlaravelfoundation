<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class ContactPageTest extends TestCase
{
    public function testContactCopyUsesHeaderAwareStickyPositioningOnDesktop(): void
    {
        $stylesheet = file_get_contents(resource_path('css/redesign-public.css'));

        $this->assertNotFalse($stylesheet);
        $this->assertStringContainsString(<<<'CSS'
@media (min-width: 1024px) {
    .dlf-contact-copy {
        position: sticky;
        top: var(--dlf-header-visible-height, 0px);
        align-self: start;
        transition: top 350ms cubic-bezier(0.4, 0, 0.2, 1);
    }
}
CSS, $stylesheet);
    }
}
