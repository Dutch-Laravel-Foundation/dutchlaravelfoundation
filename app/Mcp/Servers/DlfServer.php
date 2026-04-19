<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;

class DlfServer extends Server
{
    protected string $name = 'Dutch Laravel Foundation';

    protected string $version = '1.0.0';

    protected string $instructions = 'Read-only MCP server for the Dutch Laravel Foundation. Exposes foundation content: insights, knowledge base, events, internships, cases, members, board, partners. Content is in Dutch and English. When quoting, cite the URL from each item as the source.';

    /** @var array<int, class-string> */
    protected array $tools = [
        \App\Mcp\Tools\ListInsights::class,
        \App\Mcp\Tools\GetInsight::class,
        \App\Mcp\Tools\ListKnowledge::class,
        \App\Mcp\Tools\GetKnowledgeArticle::class,
        \App\Mcp\Tools\ListEvents::class,
        \App\Mcp\Tools\ListInternships::class,
        \App\Mcp\Tools\ListCases::class,
        \App\Mcp\Tools\ListMembers::class,
        \App\Mcp\Tools\ListBoard::class,
        \App\Mcp\Tools\ListPartners::class,
    ];
}
