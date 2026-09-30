<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class EditorialPagesTest extends TestCase
{
    #[Test]
    public function the_news_index_is_an_inertia_page_backed_by_editorial_dtos(): void
    {
        $response = $this->withHeaders($this->inertiaHeaders())->get('/nieuws');

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
    }

    #[Test]
    public function a_news_article_is_an_inertia_page_backed_by_an_editorial_dto(): void
    {
        $response = $this->withHeaders($this->inertiaHeaders())
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
    }

    #[Test]
    public function every_editorial_family_is_served_by_its_inertia_component(): void
    {
        $routes = [
            '/kennis' => 'Editorial/KnowledgeIndex',
            '/kennis/laravel-meer-dan-een-framework' => 'Editorial/KnowledgeShow',
            '/podcast' => 'Editorial/PodcastsIndex',
            '/podcast/gebruik-laravel-en-ai' => 'Editorial/PodcastsShow',
            '/agenda' => 'Editorial/EventsIndex',
            '/events/cxo-diner-2026' => 'Editorial/EventsShow',
        ];

        foreach ($routes as $uri => $component) {
            $response = $this->withHeaders($this->inertiaHeaders())->get($uri);

            $response->assertOk();
            $response->assertHeader('X-Inertia', 'true');
            $response->assertJsonPath('component', $component);
            $response->assertJsonStructure(['props' => ['editorial', 'site']]);
        }
    }

    /** @return array<string, string> */
    private function inertiaHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'X-Inertia' => 'true',
            'X-Inertia-Version' => hash_file('xxh128', public_path('build/manifest.json')),
        ];
    }
}
