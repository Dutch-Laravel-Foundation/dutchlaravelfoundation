<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Content\Community\CommunityDataMapper;
use App\Content\Community\CommunityRepository;
use App\Content\SiteShell\SiteShellDataMapper;
use App\Content\SiteShell\SiteShellRepository;
use App\Data\SiteShell\NavigationData;
use App\Data\SiteShell\NavigationItemData;
use App\Data\SiteShell\SiteShellData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final readonly class CommunityPageController
{
    public function __construct(
        private CommunityRepository $community,
        private CommunityDataMapper $mapper,
        private SiteShellRepository $siteShell,
        private SiteShellDataMapper $siteShellMapper,
    ) {}

    public function casesIndex(Request $request): Response
    {
        return $this->render($request, 'Community/CasesIndex', $this->mapper->mapCaseIndex($this->community->caseIndex()));
    }

    public function casesShow(Request $request): Response
    {
        $case = $this->mapper->mapCase($this->community->findCaseByUri($this->uri($request)));

        abort_if($case === null, 404);

        return $this->render($request, 'Community/CasesShow', $case);
    }

    public function membersIndex(Request $request): Response
    {
        return $this->render($request, 'Community/MembersIndex', $this->mapper->mapMemberIndex($this->community->memberIndex()));
    }

    public function membersShow(Request $request): Response
    {
        $member = $this->mapper->mapMember($this->community->findMemberByUri($this->uri($request)));

        abort_if($member === null, 404);

        return $this->render($request, 'Community/MembersShow', $member, '/leden');
    }

    public function internshipsIndex(Request $request): Response
    {
        return $this->render($request, 'Community/InternshipsIndex', $this->mapper->mapInternshipIndex($this->community->internshipIndex()));
    }

    public function internshipsShow(Request $request): Response
    {
        $internship = $this->mapper->mapInternship($this->community->findInternshipByUri($this->uri($request)));

        abort_if($internship === null, 404);

        return $this->render($request, 'Community/InternshipsShow', $internship);
    }

    public function larabelles(Request $request): Response
    {
        $larabelles = $this->mapper->mapLarabelles($this->community->findLarabellesByUri());

        abort_if($larabelles === null, 404);

        return $this->render($request, 'Community/Larabelles', $larabelles);
    }

    private function render(
        Request $request,
        string $component,
        mixed $community,
        ?string $activeSectionUrl = null,
    ): Response {
        return Inertia::render($component, [
            'community' => $community,
            'site' => $this->site($request, $activeSectionUrl),
        ]);
    }

    private function site(Request $request, ?string $activeSectionUrl): SiteShellData
    {
        $site = $this->siteShellMapper->map($this->siteShell->fetch(), $request->getRequestUri());

        if ($activeSectionUrl === null) {
            return $site;
        }

        $main = array_map(
            static fn (NavigationItemData $item): NavigationItemData => new NavigationItemData(
                id: $item->id,
                title: $item->title,
                slug: $item->slug,
                url: $item->url,
                permalink: $item->permalink,
                isCurrent: $item->isCurrent,
                isAncestor: $item->isAncestor || $item->url === $activeSectionUrl,
                children: $item->children,
            ),
            $site->navigation->main,
        );

        return new SiteShellData(
            organization: $site->organization,
            seo: $site->seo,
            openGraph: $site->openGraph,
            navigation: new NavigationData($main, $site->navigation->legal),
            footer: $site->footer,
            defaultCta: $site->defaultCta,
            newsletter: $site->newsletter,
        );
    }

    private function uri(Request $request): string
    {
        return '/'.ltrim($request->path(), '/');
    }
}
