<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Batch;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Uri;
use Inertia\Support\Header;
use RuntimeException;
use SimpleXMLElement;
use Symfony\Component\Console\Helper\ProgressBar;
use Throwable;

final class WarmResponseCache extends Command
{
    private const MAX_SITEMAP_DEPTH = 5;

    protected $signature = 'responsecache:warm
        {--base-url= : Override the public application URL}
        {--concurrency= : Maximum number of simultaneous warming requests}';

    protected $description = 'Warm public document and Inertia response caches';

    public function handle(): int
    {
        try {
            $baseUrl = $this->baseUrl();
            $assetVersion = $this->assetVersion($baseUrl);
            $concurrency = $this->concurrency();
            $batchSize = $this->batchSize($concurrency);
            $urls = $this->urlsToWarm($baseUrl);
            $inertiaRequests = $this->canonicalInertiaRequests($urls, $assetVersion);

            $this->components->info(
                'Warming '.count($urls)." public URLs with a concurrency of {$concurrency} in batches of {$batchSize}.",
            );

            $progress = $this->output->createProgressBar(count($inertiaRequests));
            $progress->start();

            $paginations = $this->discoverPaginations(
                $inertiaRequests,
                $concurrency,
                $batchSize,
                $progress,
            );
            $remainingRequests = $this->remainingRequests($urls, $assetVersion, $paginations);

            $progress->setMaxSteps(count($inertiaRequests) + count($remainingRequests));
            $this->sendRequestChunks($remainingRequests, $concurrency, $batchSize, $progress);

            $progress->finish();
            $this->newLine(2);
            $this->components->info('Public response cache warmed.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    private function baseUrl(): string
    {
        $baseUrl = (string) ($this->option('base-url') ?: config('responsecache.warm.base_url'));
        $uri = Uri::of($baseUrl);

        if ($uri->scheme() === null || $uri->host() === null) {
            throw new RuntimeException('The response cache warmer requires an absolute base URL.');
        }

        return rtrim($baseUrl, '/');
    }

    private function concurrency(): int
    {
        $option = $this->option('concurrency');
        $concurrency = (int) ($option ?? config('responsecache.warm.concurrency', 10));

        if ($concurrency < 1 || $concurrency > 50) {
            throw new RuntimeException('Response cache warm concurrency must be between 1 and 50.');
        }

        return $concurrency;
    }

    private function batchSize(int $concurrency): int
    {
        $batchSize = (int) config('responsecache.warm.batch_size', 50);

        if ($batchSize < 1 || $batchSize > 500) {
            throw new RuntimeException('Response cache warm batch size must be between 1 and 500.');
        }

        return max($batchSize, $concurrency);
    }

    private function assetVersion(string $baseUrl): string
    {
        $version = resolve(HandleInertiaRequests::class)->version(Request::create($baseUrl));

        if (! is_string($version) || $version === '') {
            throw new RuntimeException('The Inertia asset version could not be determined. Build the frontend before warming.');
        }

        return $version;
    }

    /** @return list<string> */
    private function urlsToWarm(string $baseUrl): array
    {
        $sitemapPath = '/'.ltrim((string) config('responsecache.warm.sitemap_path', '/sitemap.xml'), '/');
        $sitemapUrl = "{$baseUrl}{$sitemapPath}";
        $visitedSitemaps = [];
        $urls = $this->readSitemap($sitemapUrl, Uri::of($baseUrl)->host(), $visitedSitemaps);

        foreach (config('responsecache.warm.additional_urls', []) as $url) {
            $urls[] = $this->absoluteUrl($baseUrl, (string) $url);
        }

        return collect($urls)
            ->filter(fn (string $url): bool => Uri::of($url)->host() === Uri::of($baseUrl)->host())
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, true>  $visitedSitemaps
     * @return list<string>
     */
    private function readSitemap(
        string $sitemapUrl,
        ?string $expectedHost,
        array &$visitedSitemaps,
        int $depth = 0,
    ): array {
        if (isset($visitedSitemaps[$sitemapUrl])) {
            return [];
        }

        if ($depth > self::MAX_SITEMAP_DEPTH) {
            throw new RuntimeException('The sitemap index nesting limit was exceeded.');
        }

        $visitedSitemaps[$sitemapUrl] = true;
        $response = $this->http()->accept('application/xml')->get($sitemapUrl)->throw();
        $xml = simplexml_load_string($response->body(), SimpleXMLElement::class, LIBXML_NONET);

        if (! $xml instanceof SimpleXMLElement) {
            throw new RuntimeException("Invalid sitemap XML returned by {$sitemapUrl}.");
        }

        $locations = collect($xml->xpath('//*[local-name()="loc"]') ?: [])
            ->map(static fn (SimpleXMLElement $location): string => trim((string) $location))
            ->filter(fn (string $url): bool => $url !== '' && Uri::of($url)->host() === $expectedHost)
            ->values();

        if ($xml->getName() !== 'sitemapindex') {
            return $locations->all();
        }

        return $locations
            ->flatMap(function (string $url) use ($expectedHost, &$visitedSitemaps, $depth): array {
                return $this->readSitemap(
                    $url,
                    $expectedHost,
                    $visitedSitemaps,
                    $depth + 1,
                );
            })
            ->values()
            ->all();
    }

    private function absoluteUrl(string $baseUrl, string $url): string
    {
        if (Uri::of($url)->host() !== null) {
            return $url;
        }

        return $baseUrl.'/'.ltrim($url, '/');
    }

    /**
     * @param  list<string>  $urls
     * @return array<string, array{url: string, headers: array<string, string>}>
     */
    private function canonicalInertiaRequests(array $urls, string $assetVersion): array
    {
        $requests = [];

        foreach ($urls as $index => $url) {
            $requests["inertia:{$index}"] = [
                'url' => $url,
                'headers' => $this->inertiaHeaders($assetVersion),
            ];
        }

        return $requests;
    }

    /**
     * @param  list<string>  $urls
     * @param  array<int, array{component: string, prop: string, last_page: int}>  $paginations
     * @return array<string, array{url: string, headers: array<string, string>}>
     */
    private function remainingRequests(
        array $urls,
        string $assetVersion,
        array $paginations,
    ): array {
        $requests = [];

        foreach ($urls as $index => $url) {
            $requests["document:{$index}"] = [
                'url' => $url,
                'headers' => ['Accept' => 'text/html'],
            ];

            $pagination = $paginations[$index] ?? null;

            if ($pagination === null) {
                continue;
            }

            for ($pageNumber = 1; $pageNumber <= $pagination['last_page']; $pageNumber++) {
                $pageUrl = (string) Uri::of($url)->withQuery(['page' => $pageNumber]);

                if ($pageNumber > 1) {
                    $requests["document:{$index}:{$pageNumber}"] = [
                        'url' => $pageUrl,
                        'headers' => ['Accept' => 'text/html'],
                    ];
                    $requests["inertia:{$index}:{$pageNumber}"] = [
                        'url' => $pageUrl,
                        'headers' => $this->inertiaHeaders($assetVersion),
                    ];
                    $requests["append:{$index}:{$pageNumber}"] = [
                        'url' => $pageUrl,
                        'headers' => $this->infiniteScrollHeaders($assetVersion, $pagination, 'append'),
                    ];
                }

                if ($pageNumber < $pagination['last_page']) {
                    $requests["prepend:{$index}:{$pageNumber}"] = [
                        'url' => $pageUrl,
                        'headers' => $this->infiniteScrollHeaders($assetVersion, $pagination, 'prepend'),
                    ];
                }
            }
        }

        return $requests;
    }

    /**
     * @param  array<string, array{url: string, headers: array<string, string>}>  $requests
     * @return array<int, array{component: string, prop: string, last_page: int}>
     */
    private function discoverPaginations(
        array $requests,
        int $concurrency,
        int $batchSize,
        ProgressBar $progress,
    ): array {
        $paginations = [];

        foreach (array_chunk($requests, $batchSize, true) as $requestChunk) {
            $responses = $this->sendRequests($requestChunk, $concurrency, $progress);

            foreach ($responses as $key => $response) {
                $page = $response->json();
                $pagination = is_array($page) ? $this->pagination($page) : null;

                if ($pagination === null) {
                    continue;
                }

                $index = (int) str($key)->after('inertia:')->toString();
                $paginations[$index] = $pagination;
            }
        }

        return $paginations;
    }

    /**
     * @param  array<string, mixed>  $page
     * @return array{component: string, prop: string, last_page: int}|null
     */
    private function pagination(array $page): ?array
    {
        $component = $page['component'] ?? null;
        $props = $page['props'] ?? null;

        if (! is_string($component) || ! is_array($props)) {
            return null;
        }

        foreach ($props as $prop => $value) {
            $lastPage = is_array($value) ? data_get($value, 'pagination.lastPage') : null;

            if (is_string($prop) && is_numeric($lastPage) && (int) $lastPage > 1) {
                return [
                    'component' => $component,
                    'prop' => $prop,
                    'last_page' => (int) $lastPage,
                ];
            }
        }

        return null;
    }

    /** @return array<string, string> */
    private function inertiaHeaders(string $assetVersion): array
    {
        return [
            'Accept' => 'text/html, application/xhtml+xml',
            'Content-Type' => 'application/json',
            Header::INERTIA => 'true',
            Header::VERSION => $assetVersion,
            'X-Requested-With' => 'XMLHttpRequest',
        ];
    }

    /**
     * @param  array{component: string, prop: string, last_page: int}  $pagination
     * @return array<string, string>
     */
    private function infiniteScrollHeaders(
        string $assetVersion,
        array $pagination,
        string $mergeIntent,
    ): array {
        return [
            ...$this->inertiaHeaders($assetVersion),
            Header::PARTIAL_COMPONENT => $pagination['component'],
            Header::PARTIAL_ONLY => $pagination['prop'],
            Header::INFINITE_SCROLL_MERGE_INTENT => $mergeIntent,
        ];
    }

    /**
     * @param  array<string, array{url: string, headers: array<string, string>}>  $requests
     * @return array<string, HttpResponse>
     */
    private function sendRequests(array $requests, int $concurrency, ProgressBar $progress): array
    {
        if ($requests === []) {
            return [];
        }

        $batch = Http::batch(function (Batch $batch) use ($requests): void {
            foreach ($requests as $key => $request) {
                $batch->as($key)
                    ->timeout($this->timeout())
                    ->retry(3, 250)
                    ->withHeaders($request['headers'])
                    ->get($request['url']);
            }
        });

        $batch->concurrency($concurrency)
            ->progress(static function () use ($progress): void {
                $progress->advance();
            })
            ->catch(static function () use ($progress): void {
                $progress->advance();
            });

        $results = $batch->send();
        $responses = [];

        foreach (array_keys($requests) as $key) {
            $result = $results[$key] ?? null;

            if (! $result instanceof HttpResponse) {
                throw new RuntimeException("Response cache warm request [{$key}] did not return a response.");
            }

            $result->throw();
            $responses[$key] = $result;
        }

        return $responses;
    }

    /**
     * @param  array<string, array{url: string, headers: array<string, string>}>  $requests
     */
    private function sendRequestChunks(
        array $requests,
        int $concurrency,
        int $batchSize,
        ProgressBar $progress,
    ): void {
        foreach (array_chunk($requests, $batchSize, true) as $requestChunk) {
            $this->sendRequests($requestChunk, $concurrency, $progress);
        }
    }

    private function http(): PendingRequest
    {
        return Http::timeout($this->timeout());
    }

    private function timeout(): int
    {
        return (int) config('responsecache.warm.timeout_in_seconds', 30);
    }
}
