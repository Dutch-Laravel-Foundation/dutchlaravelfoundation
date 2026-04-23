<?php

declare(strict_types=1);

namespace App\Services\Agents;

use Statamic\Contracts\Entries\Entry;

class EntryMarkdownRenderer
{
    public function render(Entry $entry): string
    {
        $lines = [];

        $lines[] = '# ' . $entry->get('title');
        $lines[] = '';

        $excerpt = (string) ($entry->get('excerpt') ?? $entry->get('meta_description') ?? '');
        if ($excerpt !== '') {
            $lines[] = '> ' . $excerpt;
            $lines[] = '';
        }

        $lines[] = '**Type:** ' . $entry->collectionHandle();

        if ($entry->date()) {
            $lines[] = '**Published:** ' . $entry->date()->format('Y-m-d');
        }

        $lines[] = '**URL:** ' . $entry->absoluteUrl();

        $tags = $entry->get('tags');
        if (is_array($tags) && $tags !== []) {
            $lines[] = '**Tags:** ' . implode(', ', array_map('strval', $tags));
        }

        $lines[] = '';
        $lines[] = '---';
        $lines[] = '';
        $lines[] = $this->body($entry);

        return implode("\n", $lines) . "\n";
    }

    private function body(Entry $entry): string
    {
        foreach (['content', 'body', 'description', 'intro'] as $field) {
            $raw = $entry->get($field);

            if ($raw === null || $raw === '' || $raw === []) {
                continue;
            }

            if (is_array($raw)) {
                // Bard field: array of ProseMirror nodes. Use flattenBardNodes for reliable
                // text extraction that works regardless of whether sets are configured.
                return trim($this->flattenBardNodes($raw));
            }

            return trim((string) $raw);
        }

        return '';
    }

    /**
     * Recursively extract text from a Bard/ProseMirror node array.
     *
     * @param array<mixed> $nodes
     */
    private function flattenBardNodes(array $nodes): string
    {
        $text = '';
        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }
            if (isset($node['text']) && is_string($node['text'])) {
                $text .= $node['text'] . ' ';
            }
            if (isset($node['content']) && is_array($node['content'])) {
                $text .= $this->flattenBardNodes($node['content']);
            }
        }
        return $text;
    }
}
