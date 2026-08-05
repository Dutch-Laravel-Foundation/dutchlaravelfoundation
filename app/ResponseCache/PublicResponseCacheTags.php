<?php

declare(strict_types=1);

namespace App\ResponseCache;

use Illuminate\Http\Request;
use Statamic\Entries\Entry;

final class PublicResponseCacheTags
{
    public const string SITE_SHELL = 'site-shell';

    /** @var array<string, string> */
    private const array OVERVIEW_ROUTES = [
        'app.insights.index' => 'insights',
        'app.knowledge.index' => 'knowledge',
        'app.podcasts.index' => 'podcasts',
        'app.events.index' => 'events',
        'app.cases.index' => 'cases',
        'app.members.index' => 'members',
        'app.internships.index' => 'internships',
    ];

    /** @var array<string, list<string>> */
    private const array ROUTE_DEPENDENCIES = [
        'app.home' => ['insights', 'knowledge', 'partners', 'clients'],
        'app.knowledge.show' => ['authors'],
        'app.members.show' => ['internships', 'cases'],
        'app.public-pages.show' => ['members', 'board', 'partners', 'cases'],
    ];

    /** @var list<string> */
    private const array ENTRY_ROUTES = [
        'app.home',
        'app.contact',
        'app.become-member',
        'app.sales-funnel',
        'app.sales-funnel.thanks',
        'app.insights.show',
        'app.knowledge.show',
        'app.podcasts.show',
        'app.events.show',
        'app.cases.show',
        'app.members.show',
        'app.internships.show',
        'app.larabelles',
        'app.public-pages.show',
    ];

    /** @return list<string> */
    public function forRequest(Request $request): array
    {
        $routeName = $request->route()->getName();
        $tags = [self::SITE_SHELL];

        if (is_string($routeName) && in_array($routeName, self::ENTRY_ROUTES, true)) {
            $tags[] = $this->entryTag($request->getPathInfo());
        }

        if (is_string($routeName) && isset(self::OVERVIEW_ROUTES[$routeName])) {
            $tags[] = $this->overviewTag(self::OVERVIEW_ROUTES[$routeName]);
        }

        foreach (self::ROUTE_DEPENDENCIES[$routeName] ?? [] as $collection) {
            $tags[] = $this->overviewTag($collection);
        }

        return array_values(array_unique($tags));
    }

    public function entryTag(string $uri): string
    {
        $normalizedUri = '/'.ltrim($uri, '/');

        return "entry:{$normalizedUri}";
    }

    public function overviewTag(string $collection): string
    {
        return "overview:{$collection}";
    }

    /** @return list<string> */
    public function forEntry(Entry $entry): array
    {
        $collection = $entry->collectionHandle();

        if (! is_string($collection) || $collection === '') {
            return [];
        }

        $tags = [];
        $uri = $entry->uri();

        if (is_string($uri) && $uri !== '') {
            $tags[] = $this->entryTag($uri);

            $originalSlug = $entry->getOriginal('slug');
            $currentSlug = $entry->slug();

            if (
                is_string($originalSlug)
                && $originalSlug !== ''
                && is_string($currentSlug)
                && $originalSlug !== $currentSlug
            ) {
                $tags[] = $this->entryTag($this->replaceLastPathSegment($uri, $originalSlug));
            }
        }

        $tags[] = $this->overviewTag($collection);

        if (in_array($collection, ['members', 'socials', 'cta'], true)) {
            $tags[] = self::SITE_SHELL;
        }

        if ($collection === 'pages' && $entry->isDirty(['title', 'slug'])) {
            $tags[] = self::SITE_SHELL;
        }

        return array_values(array_unique($tags));
    }

    private function replaceLastPathSegment(string $uri, string $slug): string
    {
        $segments = explode('/', trim($uri, '/'));
        $segments[array_key_last($segments)] = $slug;

        return '/'.implode('/', $segments);
    }
}
