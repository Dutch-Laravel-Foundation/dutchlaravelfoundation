<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Content\PublicPages\PublicPageDataMapper;
use App\Content\PublicPages\PublicPageRepository;
use App\Content\SiteShell\SiteShellDataMapper;
use App\Content\SiteShell\SiteShellRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final readonly class PublicPageController
{
    private const array COMPONENTS = [
        'templates/default' => 'PublicPages/Default',
        'templates/about-us/index' => 'PublicPages/About',
        'templates/what-is-laravel/index' => 'PublicPages/WhatIsLaravel',
        'templates/landings-page/general' => 'PublicPages/GeneralLanding',
        'templates/landings-page/general2' => 'PublicPages/FrameworkLanding',
        'templates/landings-page/aanbesteding' => 'PublicPages/TenderLanding',
        'templates/privacy-statement/index' => 'PublicPages/PrivacyStatement',
        'templates/newsletter/index' => 'PublicPages/Newsletter',
    ];

    public function __invoke(
        Request $request,
        PublicPageRepository $pages,
        PublicPageDataMapper $mapper,
        SiteShellRepository $siteShell,
        SiteShellDataMapper $siteShellMapper,
    ): Response {
        $page = $mapper->map($pages->findByUri('/'.ltrim($request->path(), '/')));

        if ($page === null) {
            abort(404);
        }

        $component = self::COMPONENTS[$page->template] ?? null;

        if ($component === null) {
            abort(404);
        }

        return Inertia::render($component, [
            'page' => $page,
            'site' => $siteShellMapper->map($siteShell->fetch(), $request->getRequestUri()),
        ]);
    }
}
