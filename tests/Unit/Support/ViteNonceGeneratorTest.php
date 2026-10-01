<?php

declare(strict_types=1);
use App\Support\ViteNonceGenerator;
use Illuminate\Support\Facades\Vite;

test('nonces contain no characters that json escapes', function () {
    $generator = new ViteNonceGenerator;

    for ($i = 0; $i < 50; $i++) {
        $nonce = $generator->generate();

        expect($nonce)->toMatch('/\A[0-9a-f]{32}\z/');
        expect(trim(json_encode($nonce), '"'))->toBe($nonce);
    }

    expect(Vite::cspNonce())->toBe($nonce);
});
