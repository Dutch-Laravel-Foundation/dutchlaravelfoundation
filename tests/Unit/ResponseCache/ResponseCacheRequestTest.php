<?php

declare(strict_types=1);

use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Support\Header;
use Spatie\ResponseCache\CacheProfiles\CacheProfile;
use Spatie\ResponseCache\Hasher\RequestHasher;
use Symfony\Component\HttpFoundation\Response;

describe('public response cache profile', function (): void {
    it('caches public document and Inertia GET requests', function (): void {
        $profile = resolve(CacheProfile::class);

        expect($profile->shouldCacheRequest(requestFor('/nieuws')))->toBeTrue()
            ->and($profile->shouldCacheRequest(requestFor('/nieuws', [
                Header::INERTIA => 'true',
                'X-Requested-With' => 'XMLHttpRequest',
            ])))->toBeTrue();
    });

    it('does not cache unrelated Ajax requests', function (): void {
        $profile = resolve(CacheProfile::class);

        expect($profile->shouldCacheRequest(requestFor('/api/status', [
            'X-Requested-With' => 'XMLHttpRequest',
        ])))->toBeFalse();
    });

    it('does not cache authenticated or stateful requests', function (): void {
        $profile = resolve(CacheProfile::class);

        Auth::setUser(new GenericUser(['id' => 42]));

        expect($profile->shouldCacheRequest(requestFor('/nieuws')))->toBeFalse();

        Auth::forgetUser();

        expect($profile->shouldCacheRequest(requestFor('/contact')))->toBeFalse()
            ->and($profile->shouldCacheRequest(requestFor('/nieuws', [
                Header::INERTIA => 'true',
                Header::VERSION => 'outdated-assets',
            ])))->toBeFalse();
    });

    it('does not cache responses while validation input is flashed', function (): void {
        $profile = resolve(CacheProfile::class);
        $request = requestFor('/nieuws');
        $session = app('session')->driver();

        $session->put('_old_input', ['email' => 'invalid']);
        $request->setLaravelSession($session);

        expect($profile->shouldCacheRequest($request))->toBeFalse();

        $session->forget('_old_input');
    });

    it('only caches successful textual responses', function (): void {
        $profile = resolve(CacheProfile::class);

        expect($profile->shouldCacheResponse(new Response('<html></html>', 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ])))->toBeTrue()
            ->and($profile->shouldCacheResponse(new Response('{}', 200, [
                'Content-Type' => 'application/json',
            ])))->toBeTrue()
            ->and($profile->shouldCacheResponse(new Response('', 302, [
                'Location' => '/nieuws',
            ])))->toBeFalse()
            ->and($profile->shouldCacheResponse(new Response('Not found', 404, [
                'Content-Type' => 'text/html',
            ])))->toBeFalse();
    });
});

describe('Inertia response cache hashing', function (): void {
    it('separates documents, full Inertia responses, partial reloads, and scroll directions', function (): void {
        $hasher = resolve(RequestHasher::class);
        $document = requestFor('/nieuws?page=2');
        $inertia = requestFor('/nieuws?page=2', inertiaHeaders());
        $partial = requestFor('/nieuws?page=2', [
            ...inertiaHeaders(),
            Header::PARTIAL_COMPONENT => 'Editorial/InsightsIndex',
            Header::PARTIAL_ONLY => 'editorial',
            Header::INFINITE_SCROLL_MERGE_INTENT => 'append',
        ]);
        $prepend = requestFor('/nieuws?page=2', [
            ...inertiaHeaders(),
            Header::PARTIAL_COMPONENT => 'Editorial/InsightsIndex',
            Header::PARTIAL_ONLY => 'editorial',
            Header::INFINITE_SCROLL_MERGE_INTENT => 'prepend',
        ]);

        expect($hasher->getHashFor($document))->not->toBe($hasher->getHashFor($inertia))
            ->and($hasher->getHashFor($inertia))->not->toBe($hasher->getHashFor($partial))
            ->and($hasher->getHashFor($partial))->not->toBe($hasher->getHashFor($prepend));
    });

    it('shares a key between an Inertia prefetch and the eventual visit', function (): void {
        $hasher = resolve(RequestHasher::class);
        $visit = requestFor('/nieuws?category=Bestuur', inertiaHeaders());
        $prefetch = requestFor('/nieuws?category=Bestuur', [
            ...inertiaHeaders(),
            'Purpose' => 'prefetch',
        ]);

        expect($hasher->getHashFor($prefetch))->toBe($hasher->getHashFor($visit));
    });
});

/** @param array<string, string> $headers */
function requestFor(string $uri, array $headers = []): Request
{
    $server = collect($headers)
        ->mapWithKeys(fn (string $value, string $name): array => [
            'HTTP_'.strtoupper(str_replace('-', '_', $name)) => $value,
        ])
        ->all();

    return Request::create($uri, 'GET', server: $server);
}

/** @return array<string, string> */
function inertiaHeaders(): array
{
    Inertia::version('current-assets');

    return [
        Header::INERTIA => 'true',
        Header::VERSION => 'current-assets',
        'X-Requested-With' => 'XMLHttpRequest',
    ];
}
