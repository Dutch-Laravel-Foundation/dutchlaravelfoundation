<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Vite;
use Spatie\Csp\Nonce\NonceGenerator;

final class ViteNonceGenerator implements NonceGenerator
{
    public function generate(): string
    {
        return Vite::useCspNonce(base64_encode(random_bytes(16)));
    }
}
