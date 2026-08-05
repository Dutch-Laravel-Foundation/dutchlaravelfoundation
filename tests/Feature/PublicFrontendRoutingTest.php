<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\ErrorPageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Pecotamic\Sitemap\Http\Controllers\SitemapController;
use Statamic\Http\Controllers\GlideController;
use Tests\TestCase;

final class PublicFrontendRoutingTest extends TestCase
{
    public function test_public_frontend_is_owned_by_laravel_and_inertia(): void
    {
        $this->assertFalse(config('statamic.routes.enabled'));
        $this->assertNull(Route::getRoutes()->getByName('statamic.site'));

        $route = Route::getRoutes()->match(Request::create('/not-a-real-public-page'));

        $this->assertTrue($route->isFallback);
        $this->assertSame(ErrorPageController::class, $route->getActionName());
        $this->assertContains('inertia', $route->gatherMiddleware());
        $this->assertContains('statamic.web', $route->gatherMiddleware());
    }

    public function test_public_route_contract_is_preserved(): void
    {
        $routes = [
            'app.home' => '/',
            'app.contact' => 'contact',
            'app.become-member' => 'lid-worden',
            'app.sales-funnel' => 'aanvraag',
            'app.sales-funnel.thanks' => 'aanvraag/bedankt',
            'app.insights.index' => 'nieuws',
            'app.insights.show' => 'nieuws/{slug}',
            'app.knowledge.index' => 'kennis',
            'app.knowledge.show' => 'kennis/{slug}',
            'app.podcasts.index' => 'podcast',
            'app.podcasts.show' => 'podcast/{slug}',
            'app.events.index' => 'agenda',
            'app.events.show' => 'events/{slug}',
            'app.cases.index' => 'cases',
            'app.cases.show' => 'cases/{slug}',
            'app.members.index' => 'leden',
            'app.members.show' => 'leden/{slug}',
            'app.internships.index' => 'stagebank',
            'app.internships.show' => 'stagebank/{slug}',
            'app.larabelles' => 'larabelles',
            'app.public-pages.show' => '{page}',
        ];

        foreach ($routes as $name => $uri) {
            $route = Route::getRoutes()->getByName($name);

            $this->assertNotNull($route, "Missing route [{$name}].");
            $this->assertSame($uri, $route->uri());
            $this->assertContains('inertia', $route->gatherMiddleware());
            $this->assertContains('statamic.web', $route->gatherMiddleware());
        }
    }

    public function test_disabling_statamic_frontend_routes_preserves_cms_services(): void
    {
        $this->assertNotNull(Route::getRoutes()->getByName('statamic.cp.index'));
        $this->assertNotNull(Route::getRoutes()->getByName('statamic.forms.submit'));

        $glide = Route::getRoutes()->match(Request::create('/img/example.jpg'));

        $this->assertSame(GlideController::class.'@generateByPath', $glide->getActionName());
    }

    public function test_sitemap_remains_available_when_statamic_frontend_routes_are_disabled(): void
    {
        $route = Route::getRoutes()->getByName('public.sitemap');

        $this->assertNotNull($route);
        $this->assertSame('sitemap.xml', $route->uri());
        $this->assertSame(SitemapController::class.'@show', $route->getActionName());

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml');
    }

    public function test_unknown_public_pages_render_the_inertia_error_page(): void
    {
        $this->get('/not-a-real-public-page', [
            'X-Inertia' => 'true',
            'X-Inertia-Version' => hash_file('xxh128', public_path('build/manifest.json')),
        ])
            ->assertNotFound()
            ->assertJsonPath('component', 'Error')
            ->assertJsonPath('props.error.status', 404)
            ->assertJsonStructure(['props' => ['site']]);
    }
}
