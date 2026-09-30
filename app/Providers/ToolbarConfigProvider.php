<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use NckRtl\Toolbar\Data\Layout\GroupConfig;
use NckRtl\Toolbar\Data\Layout\LayoutConfig;
use NckRtl\Toolbar\Data\Tools\AnnotationTool;
use NckRtl\Toolbar\Enums\Layout\Section;
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

        // The annotation tool ships in the unreleased toolbar; released versions do not have it.
        if (! class_exists(AnnotationTool::class)) {
            return;
        }

        $toolbar->config->layout(function (LayoutConfig $layout): void {
            $layout->addGroup(
                (new GroupConfig(priority: 20))
                    ->addTool(new AnnotationTool)
                    ->section(Section::RIGHT),
            );
        });
    }
}
