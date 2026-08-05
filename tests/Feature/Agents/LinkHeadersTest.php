<?php

declare(strict_types=1);
it('homepage advertises llms txt', function () {
    $response = $this->get('/');
    $link = $response->headers->get('Link');

    expect($link)->not->toBeNull();
    $this->assertStringContainsString('rel="llms-txt"', $link);
    $this->assertStringContainsString('/llms.txt', $link);
});
it('homepage advertises sitemap', function () {
    $link = $this->get('/')->headers->get('Link');

    $this->assertStringContainsString('rel="sitemap"', (string) $link);
    $this->assertStringContainsString('/sitemap.xml', (string) $link);
});
it('responses declare content signals', function () {
    $this->get('/')->assertHeader(
        'Content-Signal',
        'search=yes, ai-train=no, ai-input=yes',
    );
});
it('link headers only appear on html responses', function () {
    $link = $this->get('/robots.txt')->headers->get('Link');

    expect($link)->toBeNull();
});
