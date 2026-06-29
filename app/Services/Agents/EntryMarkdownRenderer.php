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

        $videoUrl = $this->fieldAsText($entry->get('video_url'));
        if ($videoUrl !== '') {
            $lines[] = '**Video:** ' . $videoUrl;
        }

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
        if ($entry->collectionHandle() === 'podcasts') {
            return $this->podcastBody($entry);
        }

        foreach (['content', 'body', 'description', 'intro'] as $field) {
            $text = $this->fieldAsText($entry->get($field));

            if ($text !== '') {
                return $text;
            }
        }

        return '';
    }

    private function podcastBody(Entry $entry): string
    {
        $description = $this->fieldAsText($entry->get('description'));
        $transcript = $this->fieldAsText($entry->get('transcript'));
        $parts = [];

        if ($description !== '') {
            $parts[] = $description;
        }

        if ($transcript !== '') {
            $parts[] = "## Transcript\n\n{$transcript}";
        }

        return implode("\n\n", $parts);
    }

    private function fieldAsText(mixed $raw): string
    {
        if ($raw === null || $raw === '' || $raw === []) {
            return '';
        }

        if (is_array($raw)) {
            return trim($this->flattenBardNodes($raw));
        }

        return trim((string) $raw);
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
