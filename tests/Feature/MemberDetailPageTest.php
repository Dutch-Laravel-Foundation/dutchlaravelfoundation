<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class MemberDetailPageTest extends TestCase
{
    public function testMemberWithInternshipsRendersItsDetailPage(): void
    {
        $this->get('/leden/besite')
            ->assertOk()
            ->assertSee('Beschikbare stages bij Besite', false)
            ->assertSee('logo-besite.svg', false);
    }
}
