<?php

declare(strict_types=1);
test('the news index is an inertia page backed by editorial dtos', function () {
    $response = $this->withHeaders(inertiaHeaders())->get('/nieuws');

    $response->assertOk();
    $response->assertHeader('X-Inertia', 'true');
    $response->assertJsonPath('component', 'Editorial/InsightsIndex');
    $response->assertJsonPath('props.page.slug', 'nieuws');
    $response->assertJsonStructure([
        'props' => [
            'editorial' => [
                'items' => [['id', 'title', 'slug', 'category', 'date', 'featuredImage']],
                'pagination' => ['total', 'perPage', 'currentPage', 'lastPage'],
            ],
            'site' => ['organization', 'navigation', 'footer'],
        ],
    ]);
});
test('a news article is an inertia page backed by an editorial dto', function () {
    $response = $this->withHeaders(inertiaHeaders())
        ->get('/nieuws/winstgevers-eerlijke-marketing-slimme-techniek-en-0-bullshit');

    $response->assertOk();
    $response->assertHeader('X-Inertia', 'true');
    $response->assertJsonPath('component', 'Editorial/InsightsShow');
    $response->assertJsonPath(
        'props.editorial.slug',
        'winstgevers-eerlijke-marketing-slimme-techniek-en-0-bullshit',
    );
    $response->assertJsonStructure([
        'props' => [
            'editorial' => ['id', 'title', 'content', 'seo'],
            'site' => ['organization', 'navigation', 'footer'],
        ],
    ]);
});
test('every editorial family is served by its inertia component', function () {
    $routes = [
        '/kennis' => 'Editorial/KnowledgeIndex',
        '/kennis/laravel-meer-dan-een-framework' => 'Editorial/KnowledgeShow',
        '/podcast' => 'Editorial/PodcastsIndex',
        '/podcast/gebruik-laravel-en-ai' => 'Editorial/PodcastsShow',
        '/agenda' => 'Editorial/EventsIndex',
        '/events/cxo-diner-2026' => 'Editorial/EventsShow',
    ];

    foreach ($routes as $uri => $component) {
        $response = $this->withHeaders(inertiaHeaders())->get($uri);

        $response->assertOk();
        $response->assertHeader('X-Inertia', 'true');
        $response->assertJsonPath('component', $component);
        $response->assertJsonStructure(['props' => ['editorial', 'site']]);
    }
});
/** @return array<string, string> */
