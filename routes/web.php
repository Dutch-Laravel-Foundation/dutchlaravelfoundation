<?php

use Illuminate\Support\Facades\Route;

Route::permanentRedirect('/leden/avocado-media', '/leden/clarity');

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
Route::permanentRedirect('/calendar/{slug?}', '/agenda/{slug?}');

// Cases redirects
Route::permanentRedirect('/showcases/{slug?}', '/cases/{slug?}');