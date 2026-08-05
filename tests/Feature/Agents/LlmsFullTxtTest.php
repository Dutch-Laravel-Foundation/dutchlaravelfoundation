<?php

declare(strict_types=1);
it('llms full txt returns markdown', function () {
    $response = $this->get('/llms-full.txt');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
});
it('llms full txt includes inlined entries', function () {
    $body = $this->get('/llms-full.txt')->getContent();

    $this->assertStringContainsString('# Dutch Laravel Foundation', $body);

    // Inlined entries are separated by --- blocks
    expect(substr_count($body, "\n---\n"))->toBeGreaterThanOrEqual(2);
});
