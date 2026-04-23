<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Support\EntryFormatter;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Statamic\Facades\Entry;

class ListKnowledge extends Tool
{
    protected string $name = 'list_knowledge';

    protected string $description = 'List entries from the Dutch Laravel Foundation knowledge base (guides, how-to articles, reference docs).';

    public function handle(Request $request): Response
    {
        $limit = min((int) $request->integer('limit', 10), 50);
        $page  = max((int) $request->integer('page', 1), 1);

        $entries = Entry::query()
            ->where('collection', 'knowledge')
            ->where('published', true)
            ->orderBy('date', 'desc')
            ->forPage($page, $limit)
            ->get();

        $formatter = new EntryFormatter();

        return Response::json($entries->map(fn ($e) => $formatter->listItem($e))->all());
    }
}
