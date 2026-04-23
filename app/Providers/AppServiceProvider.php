<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Controllers\Agents\LlmsController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Statamic\Events\EntryDeleted;
use Statamic\Events\EntrySaved;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Statamic::script('app', 'cp');
        // Statamic::style('app', 'cp');

        $invalidate = function ($event): void {
            $handle = $event->entry->collectionHandle();
            if (in_array($handle, ['insights', 'knowledge', 'events', 'internships', 'cases', 'pages', 'members', 'board', 'partners'], true)) {
                Cache::forget(LlmsController::CACHE_KEY_INDEX);
                Cache::forget(LlmsController::CACHE_KEY_FULL);
            }
        };

        Event::listen(EntrySaved::class, $invalidate);
        Event::listen(EntryDeleted::class, $invalidate);
    }
}
