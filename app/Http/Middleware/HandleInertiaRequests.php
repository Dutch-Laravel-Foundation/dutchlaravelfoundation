<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Seo\SeoMetadata;
use Illuminate\Http\Request;
use Inertia\Middleware;

final class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $seoMetadata = resolve(SeoMetadata::class);
        $entry = $seoMetadata->currentEntry();

        return [
            ...parent::share($request),
            'app' => [
                'name' => config('app.name'),
                'locale' => config('app.locale'),
                'csrfToken' => csrf_token(),
                'captchaSiteKey' => config('captcha.sitekey'),
                'cspNonce' => app('csp-nonce'),
                'seo' => [
                    'title' => $seoMetadata->title($entry),
                    'description' => $seoMetadata->description($entry),
                    'keywords' => $seoMetadata->keywords($entry),
                    'canonicalUrl' => $seoMetadata->canonicalUrl($entry),
                    'openGraphType' => $seoMetadata->openGraphType($entry),
                    'socialImageUrl' => $seoMetadata->socialImageUrl($entry),
                    'jsonLd' => $seoMetadata->jsonLd($entry),
                ],
            ],
        ];
    }
}
