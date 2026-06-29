<?php

declare(strict_types=1);

namespace App\Http\Controllers\Agents;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /cp/',
            'Disallow: /statamic/',
            'Disallow: /!/',
            '',
            '# AI crawlers — explicitly allowed for public content',
        ];

        foreach ([
            'GPTBot', 'OAI-SearchBot', 'ChatGPT-User',
            'ClaudeBot', 'Claude-User', 'Claude-SearchBot',
            'PerplexityBot', 'Perplexity-User',
            'Google-Extended', 'Applebot-Extended',
            'CCBot', 'Bytespider',
        ] as $bot) {
            $lines[] = 'User-agent: ' . $bot;
        }

        $lines[] = 'Allow: /';
        $lines[] = '';
        $lines[] = '# Cloudflare Content Signals (AI policy declaration)';
        $lines[] = 'Content-Signal: search=yes, ai-train=no, ai-input=yes';
        $lines[] = '';
        $lines[] = 'Sitemap: ' . rtrim(config('app.url'), '/') . '/sitemap.xml';

        return response(implode("\n", $lines) . "\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
