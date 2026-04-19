<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

class ListPartners extends ListCollectionTool
{
    protected string $name = 'list_partners';

    protected string $description = 'List partner organizations of the Dutch Laravel Foundation.';

    protected function collectionHandle(): string
    {
        return 'partners';
    }

    protected function sortField(): string
    {
        return 'title';
    }

    protected function sortDirection(): string
    {
        return 'asc';
    }
}
