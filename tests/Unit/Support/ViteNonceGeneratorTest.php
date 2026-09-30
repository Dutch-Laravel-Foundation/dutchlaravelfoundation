<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\ViteNonceGenerator;
use Illuminate\Support\Facades\Vite;
use Tests\TestCase;

final class ViteNonceGeneratorTest extends TestCase
{
    public function test_nonces_contain_no_characters_that_json_escapes(): void
    {
        $generator = new ViteNonceGenerator;

        for ($i = 0; $i < 50; $i++) {
            $nonce = $generator->generate();

            $this->assertMatchesRegularExpression('/\A[0-9a-f]{32}\z/', $nonce);
            $this->assertSame($nonce, trim(json_encode($nonce), '"'));
        }

        $this->assertSame($nonce, Vite::cspNonce());
    }
}
