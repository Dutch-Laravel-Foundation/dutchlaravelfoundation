<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;

final class SecurityTxtController extends Controller
{
    private const CANONICAL = 'https://dutchlaravelfoundation.nl/.well-known/security.txt';

    public function __invoke(): Response
    {
        $expires = now()->utc()->addMonths(6)->format('Y-m-d\TH:i:s\Z');

        $content = implode("\n", [
            'Contact: mailto:info@dutchlaravelfoundation.nl',
            "Expires: {$expires}",
            'Canonical: '.self::CANONICAL,
            'Preferred-Languages: nl, en',
            '',
        ]);

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
