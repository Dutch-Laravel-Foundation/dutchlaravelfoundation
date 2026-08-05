<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Inertia\Support\Header;

it('warms documents, Inertia visits, and infinite scroll responses from the sitemap', function (): void {
    config([
        'responsecache.warm.base_url' => 'https://example.test',
        'responsecache.warm.batch_size' => 4,
        'responsecache.warm.concurrency' => 3,
        'responsecache.warm.sitemap_path' => '/sitemap.xml',
        'responsecache.warm.timeout_in_seconds' => 5,
        'responsecache.warm.additional_urls' => [],
    ]);

    Http::fake(function (Request $request) {
        if ($request->url() === 'https://example.test/sitemap.xml') {
            return Http::response(<<<'XML'
                <?xml version="1.0" encoding="UTF-8"?>
                <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
                    <url><loc>https://example.test/</loc></url>
                    <url><loc>https://example.test/nieuws</loc></url>
                    <url><loc>https://external.test/ignored</loc></url>
                </urlset>
                XML, 200, ['Content-Type' => 'application/xml']);
        }

        if (! $request->hasHeader(Header::INERTIA)) {
            return Http::response('<html>Rendered document</html>', 200, ['Content-Type' => 'text/html']);
        }

        if ($request->url() === 'https://example.test/nieuws') {
            return Http::response([
                'component' => 'Editorial/InsightsIndex',
                'props' => [
                    'editorial' => [
                        'items' => [],
                        'pagination' => ['lastPage' => 2],
                    ],
                ],
            ]);
        }

        return Http::response([
            'component' => 'Editorial/InsightsIndex',
            'props' => ['editorial' => ['items' => []]],
        ]);
    });

    expect(Artisan::call('responsecache:warm'))->toBe(0);
    expect(Artisan::output())->toContain('concurrency of 3 in batches of 4');

    Http::assertSentCount(9);
    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://example.test/nieuws?page=2'
        && $request->hasHeader(Header::PARTIAL_COMPONENT)
        && $request->header(Header::PARTIAL_COMPONENT)[0] === 'Editorial/InsightsIndex'
        && $request->header(Header::PARTIAL_ONLY)[0] === 'editorial'
        && $request->header(Header::INFINITE_SCROLL_MERGE_INTENT)[0] === 'append');
    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://example.test/nieuws?page=1'
        && $request->hasHeader(Header::PARTIAL_COMPONENT)
        && $request->header(Header::PARTIAL_COMPONENT)[0] === 'Editorial/InsightsIndex'
        && $request->header(Header::PARTIAL_ONLY)[0] === 'editorial'
        && $request->header(Header::INFINITE_SCROLL_MERGE_INTENT)[0] === 'prepend');
    Http::assertNotSent(fn (Request $request): bool => str_starts_with($request->url(), 'https://external.test'));
});

it('only visits each nested sitemap once', function (): void {
    config([
        'responsecache.warm.base_url' => 'https://example.test',
        'responsecache.warm.batch_size' => 4,
        'responsecache.warm.concurrency' => 3,
        'responsecache.warm.sitemap_path' => '/sitemap.xml',
        'responsecache.warm.timeout_in_seconds' => 5,
        'responsecache.warm.additional_urls' => [],
    ]);

    Http::fake(function (Request $request) {
        if ($request->url() === 'https://example.test/sitemap.xml') {
            return Http::response(<<<'XML'
                <?xml version="1.0" encoding="UTF-8"?>
                <sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
                    <sitemap><loc>https://example.test/nested.xml</loc></sitemap>
                    <sitemap><loc>https://example.test/nested.xml</loc></sitemap>
                </sitemapindex>
                XML, 200, ['Content-Type' => 'application/xml']);
        }

        if ($request->url() === 'https://example.test/nested.xml') {
            return Http::response(<<<'XML'
                <?xml version="1.0" encoding="UTF-8"?>
                <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
                    <url><loc>https://example.test/</loc></url>
                </urlset>
                XML, 200, ['Content-Type' => 'application/xml']);
        }

        if ($request->hasHeader(Header::INERTIA)) {
            return Http::response([
                'component' => 'Home',
                'props' => [],
            ]);
        }

        return Http::response('<html>Rendered document</html>', 200, ['Content-Type' => 'text/html']);
    });

    expect(Artisan::call('responsecache:warm'))->toBe(0);

    Http::assertSentCount(4);
    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://example.test/nested.xml');
});

it('rejects an unsafe concurrency value before sending requests', function (): void {
    config([
        'responsecache.warm.base_url' => 'https://example.test',
        'responsecache.warm.concurrency' => 0,
    ]);

    Http::fake();

    expect(Artisan::call('responsecache:warm'))->toBe(1)
        ->and(Artisan::output())->toContain('between 1 and 50');

    Http::assertNothingSent();
});
