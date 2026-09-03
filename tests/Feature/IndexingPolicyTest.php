<?php

declare(strict_types=1);

it('marks confirmation pages as noindex while allowing links to be followed', function () {
    foreach (['/newsletter', '/aanvraag/bedankt'] as $path) {
        $this->get($path)
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, follow">', false);
    }
});

it('does not add noindex metadata to normal public pages', function () {
    $this->get('/contact')
        ->assertOk()
        ->assertDontSee('name="robots"', false);
});

it('excludes non-indexable pages from the sitemap', function () {
    $response = $this->get('/sitemap.xml');

    $response
        ->assertOk()
        ->assertDontSee('/newsletter')
        ->assertDontSee('/aanvraag/bedankt')
        ->assertDontSee('/terms-and-conditions')
        ->assertSee('/contact');
});

it('does not serve the unpublished empty terms page', function () {
    $this->get('/terms-and-conditions')->assertNotFound();
});
