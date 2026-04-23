<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

class ListCases extends ListCollectionTool
{
    protected string $name = 'list_cases';

    protected string $description = 'List case studies from Dutch Laravel Foundation member companies.';

    protected function collectionHandle(): string
    {
        return 'cases';
    }

    protected function sortDirection(): string
    {
        return 'asc';
    }
}
