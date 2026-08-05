<?php

declare(strict_types=1);

namespace App\ResponseCache;

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Support\Header;
use Spatie\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequests;
use Spatie\ResponseCache\Enums\HttpMethod;
use Symfony\Component\HttpFoundation\Response;

final class PublicResponseCacheProfile extends CacheAllSuccessfulGetRequests
{
    /** @var list<string> */
    private const EXCLUDED_PATHS = [
        'aanvraag',
        'aanvraag/*',
        'contact',
        'contact/*',
        'lid-worden',
        'lid-worden/*',
        'newsletter',
        'newsletter/*',
    ];

    /** @var list<string> */
    private const PRIVATE_QUERY_PARAMETERS = [
        'draft',
        'live_preview',
        'preview',
        'revision',
        'token',
    ];

    public function __construct(private readonly HandleInertiaRequests $inertia) {}

    public function shouldCacheRequest(Request $request): bool
    {
        if (! $request->isMethod(HttpMethod::Get->value)) {
            return false;
        }

        if (Auth::check() || $request->is(...self::EXCLUDED_PATHS)) {
            return false;
        }

        if ($request->ajax() && ! $request->headers->has(Header::INERTIA)) {
            return false;
        }

        if ($request->hasAny(self::PRIVATE_QUERY_PARAMETERS)) {
            return false;
        }

        if (
            $request->hasSession()
            && ($request->session()->has('errors') || $request->session()->has('_old_input'))
        ) {
            return false;
        }

        $requestedVersion = $request->header(Header::VERSION);

        if (
            $request->headers->has(Header::INERTIA)
            && $requestedVersion !== null
            && $requestedVersion !== $this->inertia->version($request)
        ) {
            return false;
        }

        return true;
    }

    public function shouldCacheResponse(Response $response): bool
    {
        return $response->isSuccessful() && $this->hasCacheableContentType($response);
    }

    public function useCacheNameSuffix(Request $request): string
    {
        return '';
    }
}
