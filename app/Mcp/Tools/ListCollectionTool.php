<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Support\EntryFormatter;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Statamic\Facades\Entry;

abstract class ListCollectionTool extends Tool
{
    abstract protected function collectionHandle(): string;

    protected function sortField(): string
    {
        return 'date';
    }

    protected function sortDirection(): string
    {
        return 'desc';
    }

    public function handle(Request $request): Response
    {
        $limit = min((int) $request->integer('limit', 10), 50);
        $page  = max((int) $request->integer('page', 1), 1);

        $entries = Entry::query()
            ->where('collection', $this->collectionHandle())
            ->where('published', true)
            ->orderBy($this->sortField(), $this->sortDirection())
            ->forPage($page, $limit)
            ->get();

        $formatter = new EntryFormatter();

        return Response::json($entries->map(fn ($e) => $formatter->listItem($e))->all());
    }
}
