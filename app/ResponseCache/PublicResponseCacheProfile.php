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

    /**
     * Query parameters that shape a cacheable page. Requests with any other parameter
     * (including utm_* and gclid) are rendered fresh and never stored, so visitors cannot
     * fill the cache with variants or leak their parameters into other visitors' pages.
     *
     * @var list<string>
     */
    private const CACHEABLE_QUERY_PARAMETERS = ['page', 'category'];

    private const MAX_CACHEABLE_PAGE = 1000;

    private const MAX_CATEGORY_LENGTH = 64;

    public function __construct(private readonly HandleInertiaRequests $inertia) {}

    /**
     * Pages also change without a save event: /agenda splits events on "today", and
     * scheduled entries appear when their date passes. Expire at the next full hour.
     */
    public function cacheLifetimeInSeconds(Request $request): int
    {
        $untilNextHour = (int) now()->diffInSeconds(now()->addHour()->startOfHour());

        return max(1, min(parent::cacheLifetimeInSeconds($request), $untilNextHour));
    }

    public function enabled(Request $request): bool
    {
        return parent::enabled($request) && $this->hasCacheableQuery($request);
    }

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

    private function hasCacheableQuery(Request $request): bool
    {
        foreach ($request->query() as $key => $value) {
            if (! in_array($key, self::CACHEABLE_QUERY_PARAMETERS, true) || ! is_string($value)) {
                return false;
            }
        }

        $page = $request->query('page');

        if ($page !== null && (! ctype_digit($page) || (int) $page < 1 || (int) $page > self::MAX_CACHEABLE_PAGE)) {
            return false;
        }

        $category = $request->query('category');

        return $category === null || mb_strlen($category) <= self::MAX_CATEGORY_LENGTH;
    }

    public function useCacheNameSuffix(Request $request): string
    {
        return '';
    }
}
