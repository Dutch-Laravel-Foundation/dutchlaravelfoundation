<?php

declare(strict_types=1);

namespace Tests\Feature\Agents;

use App\Services\Agents\EntryMarkdownRenderer;
use Statamic\Facades\Entry;
use Tests\TestCase;

class EntryMarkdownRendererTest extends TestCase
{
    public function testRendersKnownInsightEntry(): void
    {
        $entry = Entry::query()
            ->where('collection', 'insights')
            ->where('published', true)
            ->first();

        if ($entry === null) {
            $this->markTestSkipped('No published insight entries present');
        }

        $renderer = app(EntryMarkdownRenderer::class);
        $markdown = $renderer->render($entry);

        $this->assertStringContainsString('# ' . $entry->get('title'), $markdown);
        $this->assertStringContainsString('**Type:** insights', $markdown);
        $this->assertStringContainsString('**URL:** ' . $entry->absoluteUrl(), $markdown);
    }

    public function testRendersKnowledgeEntry(): void
    {
        $entry = Entry::query()
            ->where('collection', 'knowledge')
            ->where('published', true)
            ->first();

        if ($entry === null) {
            $this->markTestSkipped('No published knowledge entries present');
        }

        $markdown = app(EntryMarkdownRenderer::class)->render($entry);

        $this->assertStringContainsString('# ' . $entry->get('title'), $markdown);
        $this->assertStringContainsString('**Type:** knowledge', $markdown);
    }
}
