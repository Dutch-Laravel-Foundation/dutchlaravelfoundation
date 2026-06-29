<?php

declare(strict_types=1);

namespace App\Http\Controllers\Agents;

use App\Http\Controllers\Controller;
use App\Services\Agents\EntryMarkdownRenderer;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Statamic\Facades\Entry;

class LlmsController extends Controller
{
    public const CACHE_KEY_INDEX = 'dlf:agents:llms-txt';
    public const CACHE_KEY_FULL  = 'dlf:agents:llms-full-txt';
    private const CACHE_TTL = 3600;

    public function index(): Response
    {
        $body = Cache::remember(self::CACHE_KEY_INDEX, self::CACHE_TTL, fn () => $this->renderIndex());

        return $this->markdownResponse($body);
    }

    public function full(): Response
    {
        $body = Cache::remember(self::CACHE_KEY_FULL, self::CACHE_TTL, fn () => $this->renderFull());

        return $this->markdownResponse($body);
    }

    private function renderIndex(): string
    {
        $limit = (int) config('dlf.llms.max_entries_per_section', 50);
        $base  = rtrim(config('app.url'), '/');

        return view('agents.llms', [
            'base'            => $base,
            'preamble'        => config('dlf.llms.preamble'),
            'highlighted'     => config('dlf.llms.highlighted_pages', []),
            'knowledgeItems'  => $this->publishedEntries('knowledge', $limit),
            'insightsItems'   => $this->publishedEntries('insights', $limit),
            'eventsItems'     => $this->publishedEntries('events', $limit),
            'internshipItems' => $this->publishedEntries('internships', $limit),
        ])->render();
    }

    private function renderFull(): string
    {
        $limit    = (int) config('dlf.llms.max_entries_per_section', 50);
        $renderer = app(EntryMarkdownRenderer::class);

        return view('agents.llms-full', [
            'base'     => rtrim(config('app.url'), '/'),
            'preamble' => config('dlf.llms.preamble'),
            'sections' => [
                'Knowledge Base' => $this->entriesFor('knowledge', $limit),
                'Insights'       => $this->entriesFor('insights', $limit),
                'Events'         => $this->entriesFor('events', $limit),
                'Internships'    => $this->entriesFor('internships', $limit),
            ],
            'renderer' => $renderer,
        ])->render();
    }

    /**
     * @return array<int, array{title: string, url: string, date: ?string}>
     */
    private function publishedEntries(string $handle, int $limit): array
    {
        return Entry::query()
            ->where('collection', $handle)
            ->where('published', true)
            ->orderBy('date', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn ($entry) => [
                'title' => (string) $entry->get('title'),
                'url'   => (string) $entry->absoluteUrl(),
                'date'  => $entry->date()?->format('Y-m-d'),
            ])
            ->all();
    }

    /**
     * @return \Illuminate\Support\Collection<int, \Statamic\Contracts\Entries\Entry>
     */
    private function entriesFor(string $handle, int $limit): \Illuminate\Support\Collection
    {
        return Entry::query()
            ->where('collection', $handle)
            ->where('published', true)
            ->orderBy('date', 'desc')
            ->limit($limit)
            ->get();
    }

    private function markdownResponse(string $body): Response
    {
        return response($body, 200, [
            'Content-Type'  => 'text/markdown; charset=UTF-8',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
}
