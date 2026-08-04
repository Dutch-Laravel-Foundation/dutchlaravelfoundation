<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;

final class GenerateAppWayfinder extends Command
{
    protected $signature = 'app:wayfinder-generate
        {--path= : The output root passed to Wayfinder}
        {--skip-actions : Do not generate controller actions}
        {--skip-routes : Do not generate named routes}
        {--with-form : Generate form variants}';

    protected $description = 'Generate Wayfinder types for app-owned Inertia routes only.';

    public function handle(Router $router): int
    {
        $originalRoutes = $router->getRoutes();
        $appRoutes = new RouteCollection;
        $restoredRoutes = $originalRoutes instanceof RouteCollection
            ? $originalRoutes
            : new RouteCollection;

        foreach ($originalRoutes->getRoutes() as $route) {
            if (str_starts_with((string) $route->getName(), 'app.')) {
                $appRoutes->add($route);
            }

            if ($restoredRoutes !== $originalRoutes) {
                $restoredRoutes->add($route);
            }
        }

        $router->setRoutes($appRoutes);

        try {
            $arguments = array_filter([
                '--path' => $this->option('path'),
                '--skip-actions' => (bool) $this->option('skip-actions'),
                '--skip-routes' => (bool) $this->option('skip-routes'),
                '--with-form' => (bool) $this->option('with-form'),
            ], static fn (mixed $value): bool => $value !== null && $value !== false);

            return $this->call('wayfinder:generate', $arguments);
        } finally {
            $router->setRoutes($restoredRoutes);
        }
    }
}
