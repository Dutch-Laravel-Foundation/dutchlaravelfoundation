<?php

declare(strict_types=1);

namespace App\Content\Graphql;

interface GraphqlClient
{
    /**
     * @param  array<string, mixed>  $variables
     * @return array<string, mixed>
     */
    public function query(string $document, array $variables = []): array;
}
