<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Support\EntryFormatter;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Statamic\Facades\Entry;

class ListInsights extends Tool
{
    protected string $name = 'list_insights';

    protected string $description = 'List published Dutch Laravel Foundation insights (blog articles), newest first. Each item includes title, URL, excerpt, and publish date.';

    public function handle(Request $request): Response
    {
        $limit = min((int) $request->integer('limit', 10), 50);
        $page  = max((int) $request->integer('page', 1), 1);
        $since = $request->date('since');

        $query = Entry::query()
            ->where('collection', 'insights')
            ->where('published', true)
            ->orderBy('date', 'desc');

        if ($since !== null) {
            $query->where('date', '>=', $since);
        }

        $entries = $query->forPage($page, $limit)->get();
        $formatter = new EntryFormatter();

        $items = $entries->map(fn ($e) => $formatter->listItem($e))->all();

        return Response::json($items);
    }
}
