<?php

declare(strict_types=1);

namespace App\Mcp\Support;

use Statamic\Contracts\Entries\Entry;

class EntryFormatter
{
    /**
     * @return array{title: string, collection: string, url: string, excerpt: string|null, published_at: string|null}
     */
    public function listItem(Entry $entry): array
    {
        $excerpt = $entry->get('excerpt') ?? $entry->get('meta_description') ?? null;

        return [
            'title'        => (string) $entry->get('title'),
            'collection'   => $entry->collectionHandle(),
            'url'          => (string) $entry->absoluteUrl(),
            'excerpt'      => $excerpt === null ? null : (string) $excerpt,
            'published_at' => $entry->date()?->format('Y-m-d'),
        ];
    }
}
