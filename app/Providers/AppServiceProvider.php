<?php

declare(strict_types=1);

namespace App\Providers;

use App\Health\Checks\MailTransportCheck;
use App\Http\Controllers\Agents\LlmsController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Facades\Health;
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
        Health::checks([
            EnvironmentCheck::new()
                ->expectEnvironment('production')
                ->name('ApplicationBoot')
                ->label('Application'),
            DatabaseCheck::new()
                ->name('DatabaseConnection')
                ->label('Database'),
            CacheCheck::new()
                ->name('Cache')
                ->label('Cache'),
            MailTransportCheck::new()
                ->name('MailTransport')
                ->label('Outbound mail'),
            UsedDiskSpaceCheck::new()
                ->name('UsedDiskSpace')
                ->label('Disk space'),
        ]);

        // Statamic::script('app', 'cp');
        // Statamic::style('app', 'cp');

        $invalidate = function ($event): void {
            $handle = $event->entry->collectionHandle();
            if (in_array($handle, ['insights', 'knowledge', 'events', 'internships'], true)) {
                Cache::forget(LlmsController::CACHE_KEY_INDEX);
                Cache::forget(LlmsController::CACHE_KEY_FULL);
            }
        };

        Event::listen(EntrySaved::class, $invalidate);
        Event::listen(EntryDeleted::class, $invalidate);
    }
}
