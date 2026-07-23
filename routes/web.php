<?php

use App\Http\Controllers\Agents\LlmsController;
use App\Http\Controllers\Agents\RobotsController;
use App\Http\Controllers\SecurityTxtController;
use App\Http\Middleware\EnsureOhDearHealthEndpointIsProduction;
use Illuminate\Support\Facades\Route;
use Spatie\Health\Http\Controllers\HealthCheckJsonResultsController;
use Spatie\Health\Http\Middleware\RequiresSecret;

Route::middleware([
    EnsureOhDearHealthEndpointIsProduction::class,
    RequiresSecret::class,
])
    ->get('/oh-dear-health-check-results', HealthCheckJsonResultsController::class)
    ->name('oh-dear-health-check-results');

Route::get('/robots.txt', RobotsController::class);
Route::get('/.well-known/security.txt', SecurityTxtController::class);
Route::get('/llms.txt', [LlmsController::class, 'index']);
Route::get('/llms-full.txt', [LlmsController::class, 'full']);

Route::permanentRedirect('/leden/avocado-media', '/leden');
Route::permanentRedirect(
    '/cases/mobiele-app-api-en-adminpanel-als-mvp-voor-toetsing-onder-duizenden-reizigers',
    '/nieuws/showcase-ov-chipkaart-app'
);

// General redirects
Route::permanentRedirect('/about-us', '/over-ons');
Route::permanentRedirect('/become-member', '/lid-worden');
Route::permanentRedirect('/about-laravel', '/what-is-laravel');

// Knowledge redirects
Route::permanentRedirect('/knowledge/{slug?}', '/kennis/{slug?}');

// Member redirects
Route::permanentRedirect('/our-members/{slug?}', '/leden/{slug?}');

// Insight redirects
Route::permanentRedirect('/news/{slug?}', '/nieuws/{slug?}');
Route::permanentRedirect('/insights/{slug?}', '/nieuws/{slug?}');

// Agenda/event redirects
Route::permanentRedirect('/calendar/laravel-directors-dinner', '/events/laravel-directors-dinner');
Route::permanentRedirect('/calendar/{slug?}', '/agenda/{slug?}');

// Cases redirects
Route::permanentRedirect('/showcases/{slug?}', '/cases/{slug?}');
