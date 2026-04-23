<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

class ListBoard extends ListCollectionTool
{
    protected string $name = 'list_board';

    protected string $description = 'List board members of the Dutch Laravel Foundation.';

    protected function collectionHandle(): string
    {
        return 'board';
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
