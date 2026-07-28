<?php

declare(strict_types=1);

namespace Tests\Feature;

use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

final class CanonicalHostTest extends TestCase
{
    public function testWwwRequestsRedirectPermanentlyToTheCanonicalHost(): void
    {
        config(['app.url' => 'https://dutchlaravelfoundation.nl']);

        $this->get('https://www.dutchlaravelfoundation.nl/lid-worden?source=test')
            ->assertStatus(Response::HTTP_PERMANENTLY_REDIRECT)
            ->assertRedirect('https://dutchlaravelfoundation.nl/lid-worden?source=test');
    }
}
