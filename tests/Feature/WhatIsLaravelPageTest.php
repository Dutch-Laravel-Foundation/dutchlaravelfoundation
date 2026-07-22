<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class WhatIsLaravelPageTest extends TestCase
{
    public function testIntroLinksToTheLaravelWebsiteInANewTab(): void
    {
        $this->get('/wat-is-laravel')
            ->assertOk()
            ->assertSee(
                'href="https://laravel.com" target="_blank" rel="noopener noreferrer"',
                false,
            )
            ->assertSeeInOrder(
                [
                    'open source PHP framework',
                    'voor het bouwen van maatwerk webapplicaties. Denk aan interne tools en platforms met',
                    'miljoenen gebruikers.',
                ],
                false,
            );
    }
}
