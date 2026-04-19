<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Support\EntryFormatter;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Statamic\Facades\Search;

class SearchContent extends Tool
{
    protected string $name = 'search_content';

    protected string $description = 'Full-text search across Dutch Laravel Foundation content (insights, knowledge, events, internships, cases). Returns a ranked list of matching items with title, URL, and excerpt.';

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'query' => 'required|string|min:2|max:200',
            'collections' => 'nullable|array',
            'collections.*' => 'in:insights,knowledge,events,internships,cases',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $limit = (int) ($validated['limit'] ?? 10);

        $results = Search::index('default')->search($validated['query'])->get();
        $formatter = new EntryFormatter();

        $items = collect($results)
            ->map(fn ($hit) => $hit->getSearchable())
            ->filter(function ($entry) use ($validated) {
                if (! $entry || ! method_exists($entry, 'collectionHandle')) {
                    return false;
                }
                if (empty($validated['collections'])) {
                    return true;
                }
                return in_array($entry->collectionHandle(), $validated['collections'], true);
            })
            ->take($limit)
            ->map(fn ($entry) => $formatter->listItem($entry))
            ->values()
            ->all();

        return Response::json($items);
    }
}
