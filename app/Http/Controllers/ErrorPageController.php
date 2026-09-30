<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Content\SiteShell\SiteShellDataMapper;
use App\Content\SiteShell\SiteShellRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

final readonly class ErrorPageController
{
    public function __construct(
        private SiteShellRepository $siteShell,
        private SiteShellDataMapper $siteShellMapper,
    ) {}

    public function __invoke(Request $request): Response
    {
        return $this->render($request, Response::HTTP_NOT_FOUND);
    }

    public function render(Request $request, int $status): Response
    {
        return Inertia::render('Error', [
            'error' => ['status' => $status],
            'site' => $this->siteShellMapper->map(
                $this->siteShell->fetch(),
                $request->getRequestUri(),
            ),
        ])
            ->toResponse($request)
            ->setStatusCode($status);
    }
}
