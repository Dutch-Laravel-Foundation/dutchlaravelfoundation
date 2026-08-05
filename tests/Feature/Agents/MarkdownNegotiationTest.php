<?php

declare(strict_types=1);
use Statamic\Facades\Entry;

function firstInsightSlug(): ?string
{
    $entry = Entry::query()
        ->where('collection', 'insights')
        ->where('published', true)
        ->first();

    return $entry?->slug();
}
it('md suffix returns markdown', function () {
    $slug = firstInsightSlug();
    if ($slug === null) {
        $this->markTestSkipped('No published insights');
    }

    $response = $this->get('/nieuws/'.$slug.'.md');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
    $response->assertHeader('Vary', 'Accept');
    expect($response->getContent())->toStartWith('# ');
});
it('accept header returns markdown', function () {
    $slug = firstInsightSlug();
    if ($slug === null) {
        $this->markTestSkipped('No published insights');
    }

    $response = $this->get('/nieuws/'.$slug, ['Accept' => 'text/markdown']);

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
    $response->assertHeader('Vary', 'Accept');
});
it('html still served when accept is default', function () {
    $slug = firstInsightSlug();
    if ($slug === null) {
        $this->markTestSkipped('No published insights');
    }

    $response = $this->get('/nieuws/'.$slug);

    // The middleware must NOT return text/markdown — it should pass through to Statamic.
    // In the test environment Statamic may throw a 500 (missing Vite build), so we only
    // assert the Content-Type is not text/markdown, not that the status is 200.
    $this->assertStringNotContainsString(
        'text/markdown',
        (string) $response->headers->get('Content-Type', '')
    );
});
it('non whitelisted path ignores markdown negotiation', function () {
    // Homepage is not in the whitelist — Accept header should be ignored.
    $response = $this->get('/', ['Accept' => 'text/markdown']);

    $this->assertStringContainsString('text/html', (string) $response->headers->get('Content-Type'));
});
it('pages support markdown negotiation', function () {
    // Use the 'over-ons' page which exists in the redirect table.
    $response = $this->get('/over-ons.md');

    if ($response->status() === 404) {
        $this->markTestSkipped('over-ons page missing in this environment');
    }

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
});
it('denylisted pages fall through to html', function () {
    config()->set('dlf.markdown_negotiation.pages_denylist', ['bedankt', 'thank-you']);

    // A denylisted path should NOT be served as markdown even with .md
    $response = $this->get('/bedankt.md');

    // The middleware must NOT return text/markdown for a denylisted page.
    $this->assertStringNotContainsString(
        'text/markdown',
        (string) $response->headers->get('Content-Type', '')
    );

    // Response should be a non-200 status (404 in prod; 500 in test env due to missing Vite build).
    $this->assertNotSame(200, $response->status());
});
