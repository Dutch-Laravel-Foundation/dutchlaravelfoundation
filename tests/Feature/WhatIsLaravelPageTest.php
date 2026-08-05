<?php

declare(strict_types=1);

it('intro links to the laravel website in a new tab', function () {
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
});
