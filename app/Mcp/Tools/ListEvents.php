<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

class ListEvents extends ListCollectionTool
{
    protected string $name = 'list_events';

    protected string $description = 'List Dutch Laravel Foundation events (meetups, conferences, workshops). Default order: upcoming first.';

    protected function collectionHandle(): string
    {
        return 'events';
    }

    protected function sortField(): string
    {
        return 'date';
    }

    protected function sortDirection(): string
    {
        return 'asc';
    }
}
