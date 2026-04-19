<?php

declare(strict_types=1);

namespace Tests\Feature\Agents\Mcp;

use App\Mcp\Support\EntryFormatter;
use Statamic\Facades\Entry;
use Tests\TestCase;

class EntryFormatterTest extends TestCase
{
    public function testFormatsListItemFromInsightEntry(): void
    {
        $entry = Entry::query()
            ->where('collection', 'insights')
            ->where('published', true)
            ->first();

        if ($entry === null) {
            $this->markTestSkipped('No published insights');
        }

        $item = (new EntryFormatter())->listItem($entry);

        $this->assertSame((string) $entry->get('title'), $item['title']);
        $this->assertSame('insights', $item['collection']);
        $this->assertSame((string) $entry->absoluteUrl(), $item['url']);
        $this->assertArrayHasKey('excerpt', $item);
        $this->assertArrayHasKey('published_at', $item);
    }
}
