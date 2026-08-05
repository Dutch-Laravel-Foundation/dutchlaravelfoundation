<?php

declare(strict_types=1);

namespace App\ResponseCache;

use Illuminate\Http\Request;
use Inertia\Support\Header;
use Spatie\ResponseCache\Hasher\DefaultHasher;

final class InertiaRequestHasher extends DefaultHasher
{
    /** @var list<string> */
    private const RESPONSE_SHAPING_HEADERS = [
        Header::INERTIA,
        Header::VERSION,
        Header::PARTIAL_COMPONENT,
        Header::PARTIAL_ONLY,
        Header::PARTIAL_EXCEPT,
        Header::RESET,
        Header::INFINITE_SCROLL_MERGE_INTENT,
        Header::EXCEPT_ONCE_PROPS,
        Header::ERROR_BAG,
    ];

    public function getHashFor(Request $request): string
    {
        $responseShape = collect(self::RESPONSE_SHAPING_HEADERS)
            ->mapWithKeys(fn (string $header): array => [
                $header => $request->header($header),
            ])
            ->all();

        return hash('xxh128', parent::getHashFor($request).json_encode($responseShape, JSON_THROW_ON_ERROR));
    }
}
