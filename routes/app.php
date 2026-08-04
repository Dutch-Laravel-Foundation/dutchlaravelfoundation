<?php

declare(strict_types=1);

use App\Http\Controllers\AcquisitionPageController;
use App\Http\Controllers\CommunityPageController;
use App\Http\Controllers\EditorialPageController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\ReactPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', ReactPageController::class)->name('home');
Route::get('/contact', AcquisitionPageController::class)->name('contact');
Route::get('/lid-worden', AcquisitionPageController::class)->name('become-member');
Route::get('/aanvraag', AcquisitionPageController::class)->name('sales-funnel');
Route::get('/aanvraag/bedankt', AcquisitionPageController::class)->name('sales-funnel.thanks');
Route::get('/nieuws', [EditorialPageController::class, 'insightsIndex'])->name('insights.index');
Route::get('/nieuws/{slug}', [EditorialPageController::class, 'insightsShow'])->name('insights.show');
Route::get('/kennis', [EditorialPageController::class, 'knowledgeIndex'])->name('knowledge.index');
Route::get('/kennis/{slug}', [EditorialPageController::class, 'knowledgeShow'])->name('knowledge.show');
Route::get('/podcast', [EditorialPageController::class, 'podcastsIndex'])->name('podcasts.index');
Route::get('/podcast/{slug}', [EditorialPageController::class, 'podcastsShow'])->name('podcasts.show');
Route::get('/agenda', [EditorialPageController::class, 'eventsIndex'])->name('events.index');
Route::get('/events/{slug}', [EditorialPageController::class, 'eventsShow'])->name('events.show');

Route::get('/cases', [CommunityPageController::class, 'casesIndex'])->name('cases.index');
Route::get('/cases/{slug}', [CommunityPageController::class, 'casesShow'])->name('cases.show');
Route::get('/leden', [CommunityPageController::class, 'membersIndex'])->name('members.index');
Route::get('/leden/{slug}', [CommunityPageController::class, 'membersShow'])->name('members.show');
Route::get('/stagebank', [CommunityPageController::class, 'internshipsIndex'])->name('internships.index');
Route::get('/stagebank/{slug}', [CommunityPageController::class, 'internshipsShow'])->name('internships.show');
Route::get('/larabelles', [CommunityPageController::class, 'larabelles'])->name('larabelles');

Route::get('/{page}', PublicPageController::class)
    ->whereIn('page', [
        'aanbestedingen',
        'bedrijfsbezoek',
        'co-organised-meet-ups',
        'een-eigen-systeem-laten-bouwen-is-betaalbaarder-dan-je-denkt',
        'gastcolleges-voor-hbo-mbo',
        'hosting-hotline',
        'laravel-het-framework-dat-jouw-systeem-op-maat-tot-een-succes-maakt',
        'newsletter',
        'over-ons',
        'privacy-statement',
        'terms-and-conditions',
        'tips-voor-studenten',
        'wat-is-laravel',
        'werkgroepen',
    ])
    ->name('public-pages.show');
