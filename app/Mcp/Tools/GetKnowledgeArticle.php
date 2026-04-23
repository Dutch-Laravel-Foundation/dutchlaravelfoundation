<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Services\Agents\EntryMarkdownRenderer;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Statamic\Facades\Entry;

class GetKnowledgeArticle extends Tool
{
    protected string $name = 'get_knowledge_article';

    protected string $description = 'Fetch the full content of a single knowledge base article as markdown, given its slug.';

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:255',
        ]);

        $entry = Entry::query()
            ->where('collection', 'knowledge')
            ->where('slug', $validated['slug'])
            ->where('published', true)
            ->first();

        if ($entry === null) {
            return Response::error("No published knowledge article found with slug: {$validated['slug']}");
        }

        return Response::text(app(EntryMarkdownRenderer::class)->render($entry));
    }
}
