<?php

declare(strict_types=1);

namespace App\Providers;

use App\Content\Community\CommunityRepository;
use App\Content\Community\StatamicCommunityRepository;
use App\Content\Editorial\EditorialRepository;
use App\Content\Editorial\StatamicEditorialRepository;
use App\Content\Forms\FormsRepository;
use App\Content\Forms\StatamicFormsRepository;
use App\Content\Graphql\GraphqlClient;
use App\Content\Graphql\StatamicGraphqlClient;
use App\Content\Home\HomeRepository;
use App\Content\Home\StatamicHomeRepository;
use App\Content\PublicPages\PublicPageRepository;
use App\Content\PublicPages\StatamicPublicPageRepository;
use App\Content\Repositories\PageRepository;
use App\Content\Repositories\StatamicPageRepository;
use App\Content\SiteShell\SiteShellRepository;
use App\Content\SiteShell\StatamicSiteShellRepository;
use App\Health\Checks\MailTransportCheck;
use App\Http\Controllers\Agents\LlmsController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Facades\Health;
use Statamic\Events\EntryDeleted;
use Statamic\Events\EntrySaved;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CommunityRepository::class, StatamicCommunityRepository::class);
        $this->app->bind(EditorialRepository::class, StatamicEditorialRepository::class);
        $this->app->bind(FormsRepository::class, StatamicFormsRepository::class);
        $this->app->bind(GraphqlClient::class, StatamicGraphqlClient::class);
        $this->app->bind(HomeRepository::class, StatamicHomeRepository::class);
        $this->app->bind(PageRepository::class, StatamicPageRepository::class);
        $this->app->bind(PublicPageRepository::class, StatamicPublicPageRepository::class);
        $this->app->bind(SiteShellRepository::class, StatamicSiteShellRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $controlPanelRoute = trim((string) config('statamic.cp.route', 'cp'), '/');

        Inertia::withoutSsr([
            $controlPanelRoute,
            "{$controlPanelRoute}/*",
        ]);

        Health::checks([
            EnvironmentCheck::new()
                ->expectEnvironment('production')
                ->name('ApplicationBoot')
                ->label('Application'),
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
