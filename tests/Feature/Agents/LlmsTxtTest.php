<?php

declare(strict_types=1);
it('llms txt returns markdown', function () {
    $response = $this->get('/llms.txt');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
});
it('llms txt contains title and preamble', function () {
    $body = $this->get('/llms.txt')->getContent();

    $this->assertStringContainsString('# Dutch Laravel Foundation', $body);
    $this->assertStringContainsString(config('dlf.llms.preamble'), $body);
});
it('llms txt lists core sections', function () {
    $body = $this->get('/llms.txt')->getContent();

    $this->assertStringContainsString('## Knowledge Base', $body);
    $this->assertStringContainsString('## Insights', $body);
    $this->assertStringContainsString('## Events', $body);
    $this->assertStringContainsString('## Internships', $body);
    $this->assertStringContainsString('## Markdown access', $body);
});
it('llms txt advertises markdown negotiation', function () {
    $body = $this->get('/llms.txt')->getContent();

    $this->assertStringContainsString('Accept: text/markdown', $body);
});
it('llms txt links to existing laravel page slug', function () {
    $body = $this->get('/llms.txt')->getContent();

    $this->assertStringContainsString('/wat-is-laravel.md', $body);
    $this->assertStringNotContainsString('/what-is-laravel.md', $body);
});
