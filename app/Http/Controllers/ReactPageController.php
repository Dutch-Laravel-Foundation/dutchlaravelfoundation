<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Content\Home\HomeDataMapper;
use App\Content\Home\HomeRepository;
use App\Content\Mappers\HomePageDataMapper;
use App\Content\Repositories\PageRepository;
use App\Content\SiteShell\SiteShellDataMapper;
use App\Content\SiteShell\SiteShellRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ReactPageController
{
    public function __invoke(
        Request $request,
        PageRepository $pages,
        HomeRepository $home,
        SiteShellRepository $siteShell,
        HomePageDataMapper $mapper,
        HomeDataMapper $homeMapper,
        SiteShellDataMapper $siteShellMapper,
    ): Response {
        $entry = $pages->findByUri('/'.ltrim($request->path(), '/'));

        if ($entry === null) {
            abort(404);
        }

        return Inertia::render('Home', [
            'page' => $mapper->map(['entry' => $entry]),
            'home' => $homeMapper->map($home->get()),
            'site' => $siteShellMapper->map($siteShell->fetch(), $request->getRequestUri()),
        ]);
    }
}
