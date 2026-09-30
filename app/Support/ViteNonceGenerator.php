<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Vite;
use Spatie\Csp\Nonce\NonceGenerator;

final class ViteNonceGenerator implements NonceGenerator
{
    public function generate(): string
    {
        // Hex, not base64: JSON escapes '/' as '\/', so a base64 nonce in cached Inertia
        // props was not found and replaced by the response cache.
        return Vite::useCspNonce(bin2hex(random_bytes(16)));
    }
}
