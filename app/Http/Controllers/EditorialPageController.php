<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Content\Editorial\EditorialDataMapper;
use App\Content\Editorial\EditorialRepository;
use App\Content\Mappers\HomePageDataMapper;
use App\Content\Repositories\PageRepository;
use App\Content\SiteShell\SiteShellDataMapper;
use App\Content\SiteShell\SiteShellRepository;
use App\Data\Editorial\ArticleIndexData;
use App\Data\Editorial\EventIndexData;
use App\Data\Editorial\PaginationData;
use App\Data\Editorial\PodcastIndexData;
use App\Data\Pages\HomePageData;
use App\Data\SiteShell\SiteShellData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Inertia\ScrollMetadata;

final readonly class EditorialPageController
{
    public function __construct(
        private EditorialRepository $editorial,
        private EditorialDataMapper $editorialMapper,
        private PageRepository $pages,
        private HomePageDataMapper $pageMapper,
        private SiteShellRepository $siteShell,
        private SiteShellDataMapper $siteShellMapper,
    ) {}

    public function insightsIndex(Request $request): Response
    {
        return Inertia::render('Editorial/InsightsIndex', [
            'page' => fn (): HomePageData => $this->page('/nieuws'),
            'editorial' => Inertia::scroll(
                fn (): ArticleIndexData => $this->editorialMapper->mapArticleIndex(
                    $this->editorial->paginateInsights($this->pageNumber($request), $this->category($request)),
                ),
                wrapper: 'items',
                metadata: fn (ArticleIndexData $data): ScrollMetadata => $this->scrollMetadata($data->pagination),
            )->matchOn('items.id'),
            'site' => fn (): SiteShellData => $this->site($request),
            'category' => $this->category($request),
        ]);
    }

    public function insightsShow(Request $request): Response
    {
        $article = $this->editorialMapper->mapInsight(
            $this->editorial->findInsightByUri($this->uri($request)),
        );

        abort_if($article === null, 404);

        return Inertia::render('Editorial/InsightsShow', [
            'editorial' => $article,
            'site' => $this->site($request),
        ]);
    }

    public function knowledgeIndex(Request $request): Response
    {
        return Inertia::render('Editorial/KnowledgeIndex', [
            'page' => fn (): HomePageData => $this->page('/kennis'),
            'editorial' => Inertia::scroll(
                fn (): ArticleIndexData => $this->editorialMapper->mapArticleIndex(
                    $this->editorial->paginateKnowledge($this->pageNumber($request), $this->category($request)),
                ),
                wrapper: 'items',
                metadata: fn (ArticleIndexData $data): ScrollMetadata => $this->scrollMetadata($data->pagination),
            )->matchOn('items.id'),
            'site' => fn (): SiteShellData => $this->site($request),
            'category' => $this->category($request),
        ]);
    }

    public function knowledgeShow(Request $request): Response
    {
        $article = $this->editorialMapper->mapKnowledge(
            $this->editorial->findKnowledgeByUri($this->uri($request)),
        );

        abort_if($article === null, 404);

        return Inertia::render('Editorial/KnowledgeShow', [
            'editorial' => $article,
            'site' => $this->site($request),
        ]);
    }

    public function podcastsIndex(Request $request): Response
    {
        return Inertia::render('Editorial/PodcastsIndex', [
            'page' => fn (): HomePageData => $this->page('/podcast'),
            'editorial' => Inertia::scroll(
                fn (): PodcastIndexData => $this->editorialMapper->mapPodcastIndex(
                    $this->editorial->paginatePodcasts($this->pageNumber($request)),
                ),
                wrapper: 'items',
                metadata: fn (PodcastIndexData $data): ScrollMetadata => $this->scrollMetadata($data->pagination),
            )->matchOn('items.id'),
            'site' => fn (): SiteShellData => $this->site($request),
        ]);
    }

    public function podcastsShow(Request $request): Response
    {
        $podcast = $this->editorialMapper->mapPodcast(
            $this->editorial->findPodcastByUri($this->uri($request)),
        );

        abort_if($podcast === null, 404);

        return Inertia::render('Editorial/PodcastsShow', [
            'editorial' => $podcast,
            'site' => $this->site($request),
        ]);
    }

    public function eventsIndex(Request $request): Response
    {
        return Inertia::render('Editorial/EventsIndex', [
            'page' => fn (): HomePageData => $this->page('/agenda'),
            'editorial' => Inertia::scroll(
                fn (): EventIndexData => $this->editorialMapper->mapEventIndex(
                    $this->editorial->paginateEvents($this->pageNumber($request)),
                ),
                wrapper: 'past',
                metadata: fn (EventIndexData $data): ScrollMetadata => $this->scrollMetadata($data->pagination),
            )->matchOn('past.id'),
            'site' => fn (): SiteShellData => $this->site($request),
        ]);
    }

    public function eventsShow(Request $request): Response
    {
        $event = $this->editorialMapper->mapEvent(
            $this->editorial->findEventByUri($this->uri($request)),
        );

        abort_if($event === null, 404);

        return Inertia::render('Editorial/EventsShow', [
            'editorial' => $event,
            'site' => $this->site($request),
        ]);
    }

    private function page(string $uri): HomePageData
    {
        $entry = $this->pages->findByUri($uri);

        if ($entry === null) {
            abort(404);
        }

        return $this->pageMapper->map(['entry' => $entry]);
    }

    private function site(Request $request): SiteShellData
    {
        return $this->siteShellMapper->map(
            $this->siteShell->fetch(),
            $request->getRequestUri(),
        );
    }

    private function pageNumber(Request $request): int
    {
        return max(1, $request->integer('page', 1));
    }

    private function scrollMetadata(PaginationData $pagination): ScrollMetadata
    {
        return new ScrollMetadata(
            pageName: 'page',
            previousPage: $pagination->currentPage > 1 ? $pagination->currentPage - 1 : null,
            nextPage: $pagination->hasMorePages ? $pagination->currentPage + 1 : null,
            currentPage: $pagination->currentPage,
        );
    }

    private function category(Request $request): ?string
    {
        $category = $request->query('category');

        return is_string($category) && $category !== '' ? $category : null;
    }

    private function uri(Request $request): string
    {
        return '/'.ltrim($request->path(), '/');
    }
}
