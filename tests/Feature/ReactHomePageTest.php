<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ReactHomePageTest extends TestCase
{
    #[Test]
    public function the_homepage_is_rendered_from_a_graphql_mapped_dto(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'X-Inertia' => 'true',
            'X-Inertia-Version' => hash_file('xxh128', public_path('build/manifest.json')),
        ])->get('/');

        $response->assertOk();
        $response->assertHeader('X-Inertia', 'true');
        $response->assertJsonPath('component', 'Home');
        $response->assertJsonStructure(['props' => ['app' => ['name', 'locale', 'csrfToken']]]);
        $response->assertJsonPath('props.page.id', 'home');
        $response->assertJsonPath('props.page.uri', '/');
        $response->assertJsonPath(
            'props.page.headerTitle',
            'De kennis- en brancheorganisatie voor Laravel developers',
        );
        $response->assertJsonPath('props.page.menuTheme', 'red');
        $response->assertJsonPath(
            'props.page.seo.title',
            'Dutch Laravel Foundation | Laravel-community Nederland',
        );
        $response->assertJsonStructure([
            'props' => [
                'home' => [
                    'latestInsight' => ['id', 'title', 'slug', 'url', 'introduction', 'featuredImage'],
                    'latestKnowledge' => ['id', 'title', 'slug', 'url', 'introduction', 'featuredImage'],
                    'highlightedInsight' => ['id', 'title', 'slug', 'url', 'introduction', 'featuredImage'],
                    'partners',
                    'clients',
                ],
            ],
        ]);
        $response->assertJsonCount(15, 'props.home.clients');
        $response->assertJsonPath('props.site.organization.site.name', 'Dutch Laravel Foundation');
        $response->assertJsonPath('props.site.organization.title', 'Dutch Laravel Foundation');
        $response->assertJsonPath('props.site.navigation.main.0.title', 'Laravel');
        $response->assertJsonPath('props.site.newsletter.handle', 'newsletter');
    }
}
