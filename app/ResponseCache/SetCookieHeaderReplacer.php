<?php

declare(strict_types=1);

namespace App\ResponseCache;

use Spatie\ResponseCache\Replacers\Replacer;
use Symfony\Component\HttpFoundation\Response;

final class SetCookieHeaderReplacer implements Replacer
{
    public function prepareResponseToCache(Response $response): void
    {
        $response->headers->remove('Set-Cookie');
    }

    public function replaceInCachedResponse(Response $response): void {}
}
