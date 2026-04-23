<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

class ListInternships extends ListCollectionTool
{
    protected string $name = 'list_internships';

    protected string $description = 'List open internship listings at Dutch Laravel Foundation member companies.';

    protected function collectionHandle(): string
    {
        return 'internships';
    }
}
