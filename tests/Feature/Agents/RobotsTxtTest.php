<?php

declare(strict_types=1);
it('robots txt is served', function () {
    $response = $this->get('/robots.txt');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
});
it('robots txt allows general crawlers', function () {
    $body = $this->get('/robots.txt')->getContent();

    $this->assertStringContainsString('User-agent: *', $body);
    $this->assertStringContainsString('Allow: /', $body);
});
it('robots txt disallows statamic admin', function () {
    $body = $this->get('/robots.txt')->getContent();

    $this->assertStringContainsString('Disallow: /cp/', $body);
    $this->assertStringContainsString('Disallow: /statamic/', $body);
});
it('robots txt explicitly allows ai bots', function () {
    $body = $this->get('/robots.txt')->getContent();

    foreach ([
        'GPTBot', 'OAI-SearchBot', 'ChatGPT-User',
        'ClaudeBot', 'Claude-User', 'Claude-SearchBot',
        'PerplexityBot', 'Perplexity-User',
        'Google-Extended', 'Applebot-Extended',
        'CCBot', 'Bytespider',
    ] as $bot) {
        $this->assertStringContainsString('User-agent: '.$bot, $body);
    }
});
it('robots txt only contains supported directives', function () {
    $body = $this->get('/robots.txt')->getContent();

    $this->assertStringNotContainsString('Content-Signal:', $body);
});
it('robots txt includes sitemap', function () {
    $body = $this->get('/robots.txt')->getContent();

    expect($body)->toMatch('#^Sitemap: https?://.+/sitemap\.xml$#m');
});
