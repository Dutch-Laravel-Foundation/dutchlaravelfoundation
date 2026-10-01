<?php

declare(strict_types=1);
use App\Http\Controllers\ErrorPageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Pecotamic\Sitemap\Http\Controllers\SitemapController;
use Statamic\Http\Controllers\GlideController;

test('public frontend is owned by laravel and inertia', function () {
    expect(config('statamic.routes.enabled'))->toBeFalse();
    expect(Route::getRoutes()->getByName('statamic.site'))->toBeNull();

    $route = Route::getRoutes()->match(Request::create('/not-a-real-public-page'));

    expect($route->isFallback)->toBeTrue();
    expect($route->getActionName())->toBe(ErrorPageController::class);
    expect($route->gatherMiddleware())->toContain('inertia');
    expect($route->gatherMiddleware())->toContain('statamic.web');
});
test('public route contract is preserved', function () {
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

        expect($route)->not->toBeNull("Missing route [{$name}].");
        expect($route->uri())->toBe($uri);
        expect($route->gatherMiddleware())->toContain('inertia');
        expect($route->gatherMiddleware())->toContain('statamic.web');
    }
});
test('disabling statamic frontend routes preserves cms services', function () {
    expect(Route::getRoutes()->getByName('statamic.cp.index'))->not->toBeNull();
    expect(Route::getRoutes()->getByName('statamic.forms.submit'))->not->toBeNull();

    $glide = Route::getRoutes()->match(Request::create('/img/example.jpg'));

    expect($glide->getActionName())->toBe(GlideController::class.'@generateByPath');
});
test('sitemap remains available when statamic frontend routes are disabled', function () {
    $route = Route::getRoutes()->getByName('public.sitemap');

    expect($route)->not->toBeNull();
    expect($route->uri())->toBe('sitemap.xml');
    expect($route->getActionName())->toBe(SitemapController::class.'@show');

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml');
});
test('unknown public pages render the inertia error page', function () {
    $this->get('/not-a-real-public-page', [
        'X-Inertia' => 'true',
        'X-Inertia-Version' => hash_file('xxh128', public_path('build/manifest.json')),
    ])
        ->assertNotFound()
        ->assertJsonPath('component', 'Error')
        ->assertJsonPath('props.error.status', 404)
        ->assertJsonStructure(['props' => ['site']]);
});
