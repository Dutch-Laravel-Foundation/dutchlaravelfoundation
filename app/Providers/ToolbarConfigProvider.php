<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use NckRtl\Toolbar\Toolbar;

class ToolbarConfigProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! class_exists(Toolbar::class)) {
            return;
        }

        if (! $this->app->bound(Toolbar::class)) {
            return;
        }

        $toolbar = $this->app->make(Toolbar::class);
        $toolbar->config->primaryColor('#ff2d20', '#ffffff');
    }
}
