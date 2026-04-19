<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Agents\EntryMarkdownRenderer;
use Closure;
use Illuminate\Http\Request;
use Statamic\Facades\Entry;
use Symfony\Component\HttpFoundation\Response;

class ServeMarkdown
{
    /**
     * Route prefixes whose entries may be served as markdown.
     * Maps URL prefix → Statamic collection handle.
     */
    private const WHITELIST = [
        '/nieuws/'    => 'insights',
        '/kennis/'    => 'knowledge',
        '/events/'    => 'events',
        '/stagebank/' => 'internships',
        '/cases/'     => 'cases',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $path = '/' . ltrim($request->path(), '/');
        $wantsMarkdown = false;

        if (str_ends_with($path, '.md')) {
            $wantsMarkdown = true;
            $path = substr($path, 0, -3);
        } elseif ($this->prefersMarkdown($request)) {
            $wantsMarkdown = true;
        }

        if (! $wantsMarkdown || ! $this->inWhitelist($path)) {
            return $next($request);
        }

        $entry = Entry::findByUri($path);
        if ($entry === null || ! $entry->published()) {
            return $next($request);
        }

        $markdown = app(EntryMarkdownRenderer::class)->render($entry);

        return response($markdown, 200, [
            'Content-Type'  => 'text/markdown; charset=UTF-8',
            'Vary'          => 'Accept',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }

    private function prefersMarkdown(Request $request): bool
    {
        $accept = (string) $request->header('Accept', '');
        if ($accept === '' || $accept === '*/*') {
            return false;
        }

        // Simple preference: markdown explicit AND no higher-priority html
        $hasMarkdown = str_contains($accept, 'text/markdown');
        $hasHtml     = str_contains($accept, 'text/html');

        return $hasMarkdown && ! $hasHtml;
    }

    private function inWhitelist(string $path): bool
    {
        foreach (array_keys(self::WHITELIST) as $prefix) {
            if (str_starts_with($path, $prefix) && strlen($path) > strlen($prefix)) {
                return true;
            }
        }

        // Top-level pages (single-segment or parent_uri/slug) from the 'pages' collection.
        if ($path === '/' || $path === '') {
            return false;
        }

        $slug = ltrim($path, '/');
        $denylist = (array) config('dlf.markdown_negotiation.pages_denylist', []);

        return ! in_array($slug, $denylist, true);
    }
}
