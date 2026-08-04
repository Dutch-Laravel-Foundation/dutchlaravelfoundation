<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class EditorialPaginationTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_news_pagination_exposes_the_inertia_scroll_contract(): void
    {
        $response = $this->withHeaders($this->inertiaHeaders())->get('/nieuws?page=2');

        $response->assertOk();
        $response->assertHeader('X-Inertia', 'true');
        $response->assertJsonPath('component', 'Editorial/InsightsIndex');
        $response->assertJsonPath('props.editorial.pagination.currentPage', 2);
        $response->assertJsonPath('props.editorial.pagination.lastPage', 8);
        $response->assertJsonPath('props.editorial.pagination.hasMorePages', true);
        $response->assertJsonPath('mergeProps.0', 'editorial.items');
        $response->assertJsonPath('matchPropsOn.0', 'editorial.items.id');
        $response->assertJsonPath('scrollProps.editorial.pageName', 'page');
        $response->assertJsonPath('scrollProps.editorial.previousPage', 1);
        $response->assertJsonPath('scrollProps.editorial.currentPage', 2);
        $response->assertJsonPath('scrollProps.editorial.nextPage', 3);
    }

    public function test_podcast_pagination_uses_the_items_merge_key(): void
    {
        $response = $this->withHeaders($this->inertiaHeaders())->get('/podcast');

        $response->assertOk();
        $response->assertJsonPath('mergeProps.0', 'editorial.items');
        $response->assertJsonPath('matchPropsOn.0', 'editorial.items.id');
        $response->assertJsonPath('scrollProps.editorial.pageName', 'page');
        $response->assertJsonPath('scrollProps.editorial.currentPage', 1);
    }

    public function test_agenda_paginates_only_past_events_for_infinite_scrolling(): void
    {
        Carbon::setTestNow('2026-07-20 12:00:00');

        $response = $this->withHeaders($this->inertiaHeaders())->get('/agenda');

        $response->assertOk();
        $response->assertJsonCount(2, 'props.editorial.upcoming');
        $response->assertJsonCount(10, 'props.editorial.past');
        $response->assertJsonPath('props.editorial.pagination.perPage', 10);
        $response->assertJsonPath('mergeProps.0', 'editorial.past');
        $response->assertJsonPath('matchPropsOn.0', 'editorial.past.id');
        $response->assertJsonPath('scrollProps.editorial.pageName', 'page');
        $response->assertJsonPath('scrollProps.editorial.currentPage', 1);
    }

    public function test_editorial_indexes_render_infinite_scroll_with_accessible_loading_and_end_states(): void
    {
        $components = collect(File::allFiles(resource_path('js')))
            ->filter(fn (\SplFileInfo $file): bool => $file->getExtension() === 'tsx')
            ->filter(function (\SplFileInfo $file): bool {
                $source = file_get_contents($file->getPathname());

                return $source !== false && str_contains($source, '<InfiniteScroll');
            });

        $this->assertNotEmpty($components);

        foreach ($components as $component) {
            $source = file_get_contents($component->getPathname());

            $this->assertNotFalse($source);
            $this->assertSame(
                substr_count($source, '<InfiniteScroll'),
                substr_count($source, 'buffer={1200}'),
                $component->getPathname(),
            );
            $this->assertStringContainsString('data="editorial"', $source, $component->getPathname());
            $this->assertStringContainsString('role="status"', $source, $component->getPathname());
            $this->assertStringContainsString('aria-live="polite"', $source, $component->getPathname());
            $this->assertStringContainsString('hasMore', $source, $component->getPathname());
            $this->assertStringNotContainsString('<Pagination', $source, $component->getPathname());
        }

        $shellStyles = file_get_contents(resource_path('css/redesign-shell.css'));

        $this->assertNotFalse($shellStyles);
        $this->assertMatchesRegularExpression(
            '/\.dlf-footer\s*\{[^}]*overflow-anchor:\s*none;/s',
            $shellStyles,
        );

        $this->assertFileDoesNotExist(
            resource_path('js/components/editorial-react/Pagination.tsx'),
        );
    }

    /** @return array<string, string> */
    private function inertiaHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'X-Inertia' => 'true',
            'X-Inertia-Version' => hash_file('xxh128', public_path('build/manifest.json')),
        ];
    }
}
