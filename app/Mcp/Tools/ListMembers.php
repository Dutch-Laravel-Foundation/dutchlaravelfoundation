<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

class ListMembers extends ListCollectionTool
{
    protected string $name = 'list_members';

    protected string $description = 'List member companies of the Dutch Laravel Foundation.';

    protected function collectionHandle(): string
    {
        return 'members';
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
