# LLM-ready score improvements — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Raise dutchlaravelfoundation.nl's *Is It Agent Ready* score from 25 to ~85–90 by adding a robust discoverability layer, markdown content negotiation, and a read-only Laravel MCP server that exposes curated Statamic collections.

**Architecture:** Three independent layers behind one PR. (1) A discoverability layer: dynamic robots.txt, llms.txt/llms-full.txt, /.well-known/mcp.json, and Link response headers. (2) A content-negotiation layer: a middleware + controller wrapper that serves markdown for whitelisted Statamic entries when requested via `Accept: text/markdown` or `.md` suffix, backed by a shared `EntryMarkdownRenderer` service. (3) An MCP server using the first-party `laravel/mcp` package, mounted at `/mcp`, registering read-only tools that list and fetch curated Statamic collections.

**Tech Stack:** Laravel 12, Statamic 6, PHP 8.4, `laravel/mcp` (new), PHPUnit (project uses PHPUnit classes, NOT Pest).

**Spec:** `docs/superpowers/specs/2026-04-19-llm-ready-score-design.md`

**Conventions observed in this repo (follow them):**
- Tests: classic PHPUnit classes (`class FooTest extends Tests\TestCase { public function testBar(): void { ... } }`). `declare(strict_types=1);` at the top of every new PHP file.
- Middleware registration: `app/Http/Kernel.php` (this project still uses the Kernel-style, not `bootstrap/app.php` fluent registration).
- Routing: route files wired in `app/Providers/RouteServiceProvider.php`.
- Statamic collection routes (actual canonical URLs):
  - `insights` → `/nieuws/{slug}`
  - `knowledge` → `/kennis/{slug}`
  - `events` → `/events/{slug}`
  - `internships` → `/stagebank/{slug}`
  - `cases` → `/cases/{slug}`
  - `pages` → `{parent_uri}/{slug}`
- Existing tests hit live Statamic content (see `tests/Feature/StagebankFeedbackTest.php`). No fixture scaffolding needed — use real entries or assert shape only.
- An existing static `public/robots.txt` must be deleted so the Laravel route wins.

---

## Phase 0 — Dependency install

### Task 0.1: Install `laravel/mcp`

**Files:**
- Modify: `composer.json`, `composer.lock`

- [ ] **Step 1: Install the package**

```bash
composer require laravel/mcp
```

Expected: package installed, `routes/ai.php` auto-registration (if any) verified on next `php artisan route:list`.

- [ ] **Step 2: Verify install**

```bash
php artisan route:list --except-vendor=0 2>&1 | head -20
composer show laravel/mcp
```

Expected: `laravel/mcp` present with a valid version. No errors.

- [ ] **Step 3: Commit**

```bash
git add composer.json composer.lock
git commit -m "chore: install laravel/mcp package"
```

---

## Phase 1 — Discoverability layer

### Task 1.1: Remove static robots.txt

**Files:**
- Delete: `public/robots.txt`

- [ ] **Step 1: Delete the static file**

```bash
git rm public/robots.txt
```

- [ ] **Step 2: Verify nothing else references it**

```bash
# Run grep to confirm no references
```

Use the Grep tool with pattern `robots\.txt` across the repo. Expected: only mentions in documentation, not in code.

- [ ] **Step 3: Commit**

```bash
git commit -m "chore: remove static robots.txt (replaced by dynamic controller in next commit)"
```

### Task 1.2: RobotsController with AI-bot rules + content signals

**Files:**
- Create: `app/Http/Controllers/Agents/RobotsController.php`
- Modify: `routes/web.php` — add the route
- Test: `tests/Feature/Agents/RobotsTxtTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Agents;

use Tests\TestCase;

class RobotsTxtTest extends TestCase
{
    public function testRobotsTxtIsServed(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function testRobotsTxtAllowsGeneralCrawlers(): void
    {
        $body = $this->get('/robots.txt')->getContent();

        $this->assertStringContainsString('User-agent: *', $body);
        $this->assertStringContainsString('Allow: /', $body);
    }

    public function testRobotsTxtDisallowsStatamicAdmin(): void
    {
        $body = $this->get('/robots.txt')->getContent();

        $this->assertStringContainsString('Disallow: /cp/', $body);
        $this->assertStringContainsString('Disallow: /statamic/', $body);
    }

    public function testRobotsTxtExplicitlyAllowsAiBots(): void
    {
        $body = $this->get('/robots.txt')->getContent();

        foreach ([
            'GPTBot', 'OAI-SearchBot', 'ChatGPT-User',
            'ClaudeBot', 'Claude-User', 'Claude-SearchBot',
            'PerplexityBot', 'Perplexity-User',
            'Google-Extended', 'Applebot-Extended',
            'CCBot', 'Bytespider',
        ] as $bot) {
            $this->assertStringContainsString('User-agent: ' . $bot, $body);
        }
    }

    public function testRobotsTxtDeclaresContentSignals(): void
    {
        $body = $this->get('/robots.txt')->getContent();

        $this->assertStringContainsString('Content-Signal: search=yes, ai-train=no, ai-input=yes', $body);
    }

    public function testRobotsTxtIncludesSitemap(): void
    {
        $body = $this->get('/robots.txt')->getContent();

        $this->assertMatchesRegularExpression('#^Sitemap: https?://.+/sitemap\.xml$#m', $body);
    }
}
```

- [ ] **Step 2: Run it — expect failure**

```bash
./vendor/bin/phpunit --filter RobotsTxtTest
```

Expected: FAIL (route or controller not found, static file no longer there).

- [ ] **Step 3: Implement the controller**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Agents;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /cp/',
            'Disallow: /statamic/',
            'Disallow: /!/',
            '',
            '# AI crawlers — explicitly allowed for public content',
        ];

        foreach ([
            'GPTBot', 'OAI-SearchBot', 'ChatGPT-User',
            'ClaudeBot', 'Claude-User', 'Claude-SearchBot',
            'PerplexityBot', 'Perplexity-User',
            'Google-Extended', 'Applebot-Extended',
            'CCBot', 'Bytespider',
        ] as $bot) {
            $lines[] = 'User-agent: ' . $bot;
        }

        $lines[] = 'Allow: /';
        $lines[] = '';
        $lines[] = '# Cloudflare Content Signals (AI policy declaration)';
        $lines[] = 'Content-Signal: search=yes, ai-train=no, ai-input=yes';
        $lines[] = '';
        $lines[] = 'Sitemap: ' . rtrim(config('app.url'), '/') . '/sitemap.xml';

        return response(implode("\n", $lines) . "\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
```

- [ ] **Step 4: Register the route**

In `routes/web.php`, prepend (above the redirect block):

```php
use App\Http\Controllers\Agents\RobotsController;

Route::get('/robots.txt', RobotsController::class);
```

- [ ] **Step 5: Run the tests — expect pass**

```bash
./vendor/bin/phpunit --filter RobotsTxtTest
```

Expected: all 6 tests green.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Agents/RobotsController.php routes/web.php tests/Feature/Agents/RobotsTxtTest.php
git commit -m "feat: dynamic robots.txt with AI bot rules and content signals"
```

### Task 1.3: `/.well-known/mcp.json`

**Files:**
- Create: `app/Http/Controllers/Agents/WellKnownController.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Agents/WellKnownMcpTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Agents;

use Tests\TestCase;

class WellKnownMcpTest extends TestCase
{
    public function testWellKnownMcpReturnsValidCard(): void
    {
        $response = $this->get('/.well-known/mcp.json');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');

        $data = $response->json();
        $this->assertSame('Dutch Laravel Foundation', $data['name']);
        $this->assertArrayHasKey('description', $data);
        $this->assertSame('1.0.0', $data['version']);
        $this->assertSame('http', $data['transport']['type']);
        $this->assertStringEndsWith('/mcp', $data['transport']['url']);
        $this->assertSame('none', $data['authentication']['type']);
        $this->assertArrayHasKey('contact', $data);
    }
}
```

- [ ] **Step 2: Run — expect fail**

```bash
./vendor/bin/phpunit --filter WellKnownMcpTest
```

Expected: 404.

- [ ] **Step 3: Implement the controller**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Agents;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class WellKnownController extends Controller
{
    public function mcp(): JsonResponse
    {
        $base = rtrim(config('app.url'), '/');

        return response()->json([
            'name' => 'Dutch Laravel Foundation',
            'description' => 'Read-only MCP server exposing foundation content: insights, knowledge base, events, internships, cases, members.',
            'version' => '1.0.0',
            'transport' => [
                'type' => 'http',
                'url' => $base . '/mcp',
            ],
            'authentication' => [
                'type' => 'none',
            ],
            'contact' => [
                'url' => $base . '/contact',
            ],
        ]);
    }
}
```

- [ ] **Step 4: Register the route**

In `routes/web.php`:

```php
use App\Http\Controllers\Agents\WellKnownController;

Route::get('/.well-known/mcp.json', [WellKnownController::class, 'mcp']);
```

- [ ] **Step 5: Run — expect pass**

```bash
./vendor/bin/phpunit --filter WellKnownMcpTest
```

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Agents/WellKnownController.php routes/web.php tests/Feature/Agents/WellKnownMcpTest.php
git commit -m "feat: add /.well-known/mcp.json discovery endpoint"
```

### Task 1.4: `config/dlf.php` with llms.txt preamble

**Files:**
- Create: `config/dlf.php`
- Modify: `.env.example` (optional — add nothing new, config reads `app.url`)

- [ ] **Step 1: Create the config file**

```php
<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | llms.txt configuration
    |--------------------------------------------------------------------------
    |
    | Controls generation of /llms.txt and /llms-full.txt. The preamble is
    | hand-written prose describing the foundation; `highlighted_pages` is a
    | curated list rendered above the auto-generated collection listings.
    |
    */

    'llms' => [
        'preamble' => 'The Dutch Laravel Foundation (Stichting Dutch Laravel Foundation) is a non-profit that promotes the adoption and professional use of the Laravel PHP framework in the Netherlands. We connect members, host events, share knowledge, and support students via internship placements.',

        'highlighted_pages' => [
            ['label' => 'Over ons', 'slug' => 'over-ons'],
            ['label' => 'Lid worden', 'slug' => 'lid-worden'],
            ['label' => 'What is Laravel', 'slug' => 'what-is-laravel'],
        ],

        // Max entries per auto section in llms.txt
        'max_entries_per_section' => 50,
    ],

];
```

- [ ] **Step 2: Smoke-test the config loads**

```bash
php artisan tinker --execute="echo config('dlf.llms.preamble');"
```

Expected: the preamble string is printed.

- [ ] **Step 3: Commit**

```bash
git add config/dlf.php
git commit -m "feat: add config/dlf.php with llms.txt preamble and highlighted pages"
```

### Task 1.5: `/llms.txt` endpoint

**Files:**
- Create: `app/Http/Controllers/Agents/LlmsController.php`
- Create: `resources/views/agents/llms.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Agents/LlmsTxtTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Agents;

use Tests\TestCase;

class LlmsTxtTest extends TestCase
{
    public function testLlmsTxtReturnsMarkdown(): void
    {
        $response = $this->get('/llms.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
    }

    public function testLlmsTxtContainsTitleAndPreamble(): void
    {
        $body = $this->get('/llms.txt')->getContent();

        $this->assertStringContainsString('# Dutch Laravel Foundation', $body);
        $this->assertStringContainsString(config('dlf.llms.preamble'), $body);
    }

    public function testLlmsTxtListsCoreSections(): void
    {
        $body = $this->get('/llms.txt')->getContent();

        $this->assertStringContainsString('## Knowledge Base', $body);
        $this->assertStringContainsString('## Insights', $body);
        $this->assertStringContainsString('## Events', $body);
        $this->assertStringContainsString('## Internships', $body);
        $this->assertStringContainsString('## Programmatic access', $body);
    }

    public function testLlmsTxtAdvertisesMcpAndMarkdownNegotiation(): void
    {
        $body = $this->get('/llms.txt')->getContent();

        $this->assertStringContainsString('/mcp', $body);
        $this->assertStringContainsString('/.well-known/mcp.json', $body);
        $this->assertStringContainsString('Accept: text/markdown', $body);
    }
}
```

- [ ] **Step 2: Run — expect fail**

```bash
./vendor/bin/phpunit --filter LlmsTxtTest
```

- [ ] **Step 3: Create the controller**

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Agents;

use App\Http\Controllers\Controller;
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

    private function markdownResponse(string $body): Response
    {
        return response($body, 200, [
            'Content-Type'  => 'text/markdown; charset=UTF-8',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }
}
```

- [ ] **Step 4: Create the Blade view**

```blade
{{-- resources/views/agents/llms.blade.php --}}
# Dutch Laravel Foundation

> {{ $preamble }}

## About
@foreach ($highlighted as $page)
- [{{ $page['label'] }}]({{ $base }}/{{ ltrim($page['slug'], '/') }}.md)
@endforeach

## Knowledge Base
@foreach ($knowledgeItems as $item)
- [{{ $item['title'] }}]({{ $item['url'] }}.md)
@endforeach

## Insights
@foreach ($insightsItems as $item)
- [{{ $item['title'] }}]({{ $item['url'] }}.md){!! $item['date'] ? ' — ' . $item['date'] : '' !!}
@endforeach

## Events
@foreach ($eventsItems as $item)
- [{{ $item['title'] }}]({{ $item['url'] }}.md){!! $item['date'] ? ' — ' . $item['date'] : '' !!}
@endforeach

## Internships
@foreach ($internshipItems as $item)
- [{{ $item['title'] }}]({{ $item['url'] }}.md)
@endforeach

## Programmatic access
- MCP server: {{ $base }}/mcp
- MCP discovery: {{ $base }}/.well-known/mcp.json
- Markdown of any content page: append `.md` to the URL, or send `Accept: text/markdown`
- Full dump of content: {{ $base }}/llms-full.txt
```

- [ ] **Step 5: Register the route**

In `routes/web.php`:

```php
use App\Http\Controllers\Agents\LlmsController;

Route::get('/llms.txt', [LlmsController::class, 'index']);
```

- [ ] **Step 6: Run — expect pass**

```bash
./vendor/bin/phpunit --filter LlmsTxtTest
```

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/Agents/LlmsController.php resources/views/agents/llms.blade.php routes/web.php tests/Feature/Agents/LlmsTxtTest.php
git commit -m "feat: add /llms.txt endpoint"
```

### Task 1.6: `EntryMarkdownRenderer` service (needed for llms-full + later markdown negotiation + MCP)

**Files:**
- Create: `app/Services/Agents/EntryMarkdownRenderer.php`
- Test: `tests/Feature/Agents/EntryMarkdownRendererTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Agents;

use App\Services\Agents\EntryMarkdownRenderer;
use Statamic\Facades\Entry;
use Tests\TestCase;

class EntryMarkdownRendererTest extends TestCase
{
    public function testRendersKnownInsightEntry(): void
    {
        $entry = Entry::query()
            ->where('collection', 'insights')
            ->where('published', true)
            ->first();

        if ($entry === null) {
            $this->markTestSkipped('No published insight entries present');
        }

        $renderer = app(EntryMarkdownRenderer::class);
        $markdown = $renderer->render($entry);

        $this->assertStringContainsString('# ' . $entry->get('title'), $markdown);
        $this->assertStringContainsString('**Type:** insights', $markdown);
        $this->assertStringContainsString('**URL:** ' . $entry->absoluteUrl(), $markdown);
    }

    public function testRendersKnowledgeEntry(): void
    {
        $entry = Entry::query()
            ->where('collection', 'knowledge')
            ->where('published', true)
            ->first();

        if ($entry === null) {
            $this->markTestSkipped('No published knowledge entries present');
        }

        $markdown = app(EntryMarkdownRenderer::class)->render($entry);

        $this->assertStringContainsString('# ' . $entry->get('title'), $markdown);
        $this->assertStringContainsString('**Type:** knowledge', $markdown);
    }
}
```

- [ ] **Step 2: Run — expect fail**

```bash
./vendor/bin/phpunit --filter EntryMarkdownRendererTest
```

- [ ] **Step 3: Implement the renderer**

```php
<?php

declare(strict_types=1);

namespace App\Services\Agents;

use Statamic\Contracts\Entries\Entry;
use Statamic\Fieldtypes\Bard\Augmentor as BardAugmentor;

class EntryMarkdownRenderer
{
    public function render(Entry $entry): string
    {
        $lines = [];

        $lines[] = '# ' . $entry->get('title');
        $lines[] = '';

        $excerpt = (string) ($entry->get('excerpt') ?? $entry->get('meta_description') ?? '');
        if ($excerpt !== '') {
            $lines[] = '> ' . $excerpt;
            $lines[] = '';
        }

        $lines[] = '**Type:** ' . $entry->collectionHandle();

        if ($entry->date()) {
            $lines[] = '**Published:** ' . $entry->date()->format('Y-m-d');
        }

        $lines[] = '**URL:** ' . $entry->absoluteUrl();

        $tags = $entry->get('tags');
        if (is_array($tags) && $tags !== []) {
            $lines[] = '**Tags:** ' . implode(', ', array_map('strval', $tags));
        }

        $lines[] = '';
        $lines[] = '---';
        $lines[] = '';
        $lines[] = $this->body($entry);

        return implode("\n", $lines) . "\n";
    }

    private function body(Entry $entry): string
    {
        foreach (['content', 'body', 'description', 'intro'] as $field) {
            $raw = $entry->get($field);

            if ($raw === null || $raw === '' || $raw === []) {
                continue;
            }

            if (is_array($raw)) {
                // Bard field: array of nodes. Render to HTML, then strip tags to plain text.
                $html = (new BardAugmentor($entry->blueprint()->field($field)->fieldtype()))->augment($raw);
                return trim(strip_tags(is_string($html) ? $html : (string) $html));
            }

            return trim((string) $raw);
        }

        return '';
    }
}
```

- [ ] **Step 4: Run — expect pass**

```bash
./vendor/bin/phpunit --filter EntryMarkdownRendererTest
```

- [ ] **Step 5: Commit**

```bash
git add app/Services/Agents/EntryMarkdownRenderer.php tests/Feature/Agents/EntryMarkdownRendererTest.php
git commit -m "feat: add EntryMarkdownRenderer service for converting Statamic entries to markdown"
```

### Task 1.7: `/llms-full.txt` endpoint

**Files:**
- Modify: `app/Http/Controllers/Agents/LlmsController.php` — add `full()` method
- Create: `resources/views/agents/llms-full.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Agents/LlmsFullTxtTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Agents;

use Tests\TestCase;

class LlmsFullTxtTest extends TestCase
{
    public function testLlmsFullTxtReturnsMarkdown(): void
    {
        $response = $this->get('/llms-full.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
    }

    public function testLlmsFullTxtIncludesInlinedEntries(): void
    {
        $body = $this->get('/llms-full.txt')->getContent();

        $this->assertStringContainsString('# Dutch Laravel Foundation', $body);
        // Inlined entries are separated by --- blocks
        $this->assertGreaterThanOrEqual(2, substr_count($body, "\n---\n"));
    }
}
```

- [ ] **Step 2: Run — expect fail**

```bash
./vendor/bin/phpunit --filter LlmsFullTxtTest
```

- [ ] **Step 3: Add `full()` to `LlmsController`**

Inside `LlmsController`, add:

```php
public function full(): Response
{
    $body = Cache::remember(self::CACHE_KEY_FULL, self::CACHE_TTL, fn () => $this->renderFull());

    return $this->markdownResponse($body);
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
 * @return \Illuminate\Support\Collection<int, \Statamic\Contracts\Entries\Entry>
 */
private function entriesFor(string $handle, int $limit): \Illuminate\Support\Collection
{
    return \Statamic\Facades\Entry::query()
        ->where('collection', $handle)
        ->where('published', true)
        ->orderBy('date', 'desc')
        ->limit($limit)
        ->get();
}
```

Add the import at the top of the file:

```php
use App\Services\Agents\EntryMarkdownRenderer;
```

- [ ] **Step 4: Create the Blade view**

```blade
{{-- resources/views/agents/llms-full.blade.php --}}
# Dutch Laravel Foundation

> {{ $preamble }}

This file contains full content of curated collections. For a lighter index with links only, see [{{ $base }}/llms.txt]({{ $base }}/llms.txt).

@foreach ($sections as $sectionTitle => $items)

---

# {{ $sectionTitle }}

@foreach ($items as $entry)

---

{!! $renderer->render($entry) !!}
@endforeach
@endforeach
```

- [ ] **Step 5: Register the route**

In `routes/web.php`, add:

```php
Route::get('/llms-full.txt', [LlmsController::class, 'full']);
```

- [ ] **Step 6: Run — expect pass**

```bash
./vendor/bin/phpunit --filter LlmsFullTxtTest
```

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/Agents/LlmsController.php resources/views/agents/llms-full.blade.php routes/web.php tests/Feature/Agents/LlmsFullTxtTest.php
git commit -m "feat: add /llms-full.txt endpoint with inlined entry content"
```

### Task 1.8: Cache invalidation on Statamic entry save

**Files:**
- Modify: `app/Providers/AppServiceProvider.php`

- [ ] **Step 1: Add event listeners in `AppServiceProvider::boot`**

Insert into the `boot()` method:

```php
use App\Http\Controllers\Agents\LlmsController;
use Illuminate\Support\Facades\Cache;
use Statamic\Events\EntrySaved;
use Statamic\Events\EntryDeleted;

// ...

protected function flushAgentCaches(): void
{
    Cache::forget(LlmsController::CACHE_KEY_INDEX);
    Cache::forget(LlmsController::CACHE_KEY_FULL);
}

public function boot(): void
{
    // existing boot logic...

    $invalidate = function ($event) {
        $handle = $event->entry->collectionHandle();
        if (in_array($handle, ['insights', 'knowledge', 'events', 'internships', 'cases', 'pages', 'members', 'board', 'partners'], true)) {
            Cache::forget(LlmsController::CACHE_KEY_INDEX);
            Cache::forget(LlmsController::CACHE_KEY_FULL);
        }
    };

    \Illuminate\Support\Facades\Event::listen(EntrySaved::class, $invalidate);
    \Illuminate\Support\Facades\Event::listen(EntryDeleted::class, $invalidate);
}
```

(Preserve any existing `boot()` content — read the current file first, merge.)

- [ ] **Step 2: Smoke test manually in tinker**

```bash
php artisan tinker --execute="Cache::put('dlf:agents:llms-txt', 'test', 60); event(new Statamic\Events\EntrySaved(Statamic\Facades\Entry::query()->where('collection','insights')->first())); echo Cache::has('dlf:agents:llms-txt') ? 'FAIL' : 'OK';"
```

Expected: `OK`. (Skip if no insights entries exist.)

- [ ] **Step 3: Commit**

```bash
git add app/Providers/AppServiceProvider.php
git commit -m "feat: invalidate llms.txt and llms-full.txt caches on Statamic entry save/delete"
```

### Task 1.9: `AddDiscoveryHeaders` middleware (Link headers)

**Files:**
- Create: `app/Http/Middleware/AddDiscoveryHeaders.php`
- Modify: `app/Http/Kernel.php` — add to `web` middleware group
- Test: `tests/Feature/Agents/LinkHeadersTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Agents;

use Tests\TestCase;

class LinkHeadersTest extends TestCase
{
    public function testHomepageAdvertisesLlmsTxt(): void
    {
        $response = $this->get('/');
        $link = $response->headers->get('Link');

        $this->assertNotNull($link);
        $this->assertStringContainsString('rel="llms-txt"', $link);
        $this->assertStringContainsString('/llms.txt', $link);
    }

    public function testHomepageAdvertisesSitemap(): void
    {
        $link = $this->get('/')->headers->get('Link');

        $this->assertStringContainsString('rel="sitemap"', (string) $link);
        $this->assertStringContainsString('/sitemap.xml', (string) $link);
    }

    public function testHomepageAdvertisesMcpDiscovery(): void
    {
        $link = $this->get('/')->headers->get('Link');

        $this->assertStringContainsString('rel="mcp"', (string) $link);
        $this->assertStringContainsString('/.well-known/mcp.json', (string) $link);
    }

    public function testLinkHeadersOnlyAppearOnHtmlResponses(): void
    {
        $link = $this->get('/robots.txt')->headers->get('Link');

        $this->assertNull($link);
    }
}
```

- [ ] **Step 2: Run — expect fail**

```bash
./vendor/bin/phpunit --filter LinkHeadersTest
```

- [ ] **Step 3: Create the middleware**

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddDiscoveryHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $contentType = (string) $response->headers->get('Content-Type', '');

        if (! str_starts_with($contentType, 'text/html')) {
            return $response;
        }

        $base = rtrim(config('app.url'), '/');

        $response->headers->set('Link', implode(', ', [
            '<' . $base . '/llms.txt>; rel="llms-txt"',
            '<' . $base . '/sitemap.xml>; rel="sitemap"',
            '<' . $base . '/.well-known/mcp.json>; rel="mcp"',
        ]));

        return $response;
    }
}
```

- [ ] **Step 4: Register in the `web` middleware group**

In `app/Http/Kernel.php`, append to the `'web'` array:

```php
\App\Http\Middleware\AddDiscoveryHeaders::class,
```

- [ ] **Step 5: Run — expect pass**

```bash
./vendor/bin/phpunit --filter LinkHeadersTest
```

- [ ] **Step 6: Commit**

```bash
git add app/Http/Middleware/AddDiscoveryHeaders.php app/Http/Kernel.php tests/Feature/Agents/LinkHeadersTest.php
git commit -m "feat: add Link response headers advertising llms.txt, sitemap, and MCP discovery"
```

---

## Phase 2 — Markdown content negotiation

### Task 2.1: `ServeMarkdown` middleware with `.md` suffix + Accept header support

**Files:**
- Create: `app/Http/Middleware/ServeMarkdown.php`
- Modify: `app/Http/Kernel.php` — append to `web` group (AFTER `SubstituteBindings` but before any Statamic-specific middleware)
- Test: `tests/Feature/Agents/MarkdownNegotiationTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Agents;

use Statamic\Facades\Entry;
use Tests\TestCase;

class MarkdownNegotiationTest extends TestCase
{
    private function firstInsightSlug(): ?string
    {
        $entry = Entry::query()
            ->where('collection', 'insights')
            ->where('published', true)
            ->first();

        return $entry?->slug();
    }

    public function testMdSuffixReturnsMarkdown(): void
    {
        $slug = $this->firstInsightSlug();
        if ($slug === null) {
            $this->markTestSkipped('No published insights');
        }

        $response = $this->get('/nieuws/' . $slug . '.md');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
        $response->assertHeader('Vary', 'Accept');
        $this->assertStringStartsWith('# ', $response->getContent());
    }

    public function testAcceptHeaderReturnsMarkdown(): void
    {
        $slug = $this->firstInsightSlug();
        if ($slug === null) {
            $this->markTestSkipped('No published insights');
        }

        $response = $this->get('/nieuws/' . $slug, ['Accept' => 'text/markdown']);

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
        $response->assertHeader('Vary', 'Accept');
    }

    public function testHtmlStillServedWhenAcceptIsDefault(): void
    {
        $slug = $this->firstInsightSlug();
        if ($slug === null) {
            $this->markTestSkipped('No published insights');
        }

        $response = $this->get('/nieuws/' . $slug);

        $response->assertOk();
        $this->assertStringContainsString('text/html', (string) $response->headers->get('Content-Type'));
    }

    public function testNonWhitelistedPathIgnoresMarkdownNegotiation(): void
    {
        // Homepage is not in the whitelist — Accept header should be ignored.
        $response = $this->get('/', ['Accept' => 'text/markdown']);

        $this->assertStringContainsString('text/html', (string) $response->headers->get('Content-Type'));
    }
}
```

- [ ] **Step 2: Run — expect fail**

```bash
./vendor/bin/phpunit --filter MarkdownNegotiationTest
```

- [ ] **Step 3: Implement the middleware**

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Agents\EntryMarkdownRenderer;
use Closure;
use Illuminate\Http\Request;
use Statamic\Facades\Entry;
use Symfony\Component\HttpFoundation\Response;

class ServeMarkdown
{
    /**
     * Route prefixes whose entries may be served as markdown.
     * Maps URL prefix → Statamic collection handle.
     */
    private const WHITELIST = [
        '/nieuws/'     => 'insights',
        '/kennis/'     => 'knowledge',
        '/events/'     => 'events',
        '/stagebank/'  => 'internships',
        '/cases/'      => 'cases',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $path = '/' . ltrim($request->path(), '/');
        $wantsMarkdown = false;
        $originalPath = $path;

        if (str_ends_with($path, '.md')) {
            $wantsMarkdown = true;
            $path = substr($path, 0, -3);
        } elseif ($this->prefersMarkdown($request)) {
            $wantsMarkdown = true;
        }

        if (! $wantsMarkdown || ! $this->inWhitelist($path)) {
            return $next($request);
        }

        $entry = Entry::findByUri($path);
        if ($entry === null || ! $entry->published()) {
            return $next($request);
        }

        $markdown = app(EntryMarkdownRenderer::class)->render($entry);

        return response($markdown, 200, [
            'Content-Type'  => 'text/markdown; charset=UTF-8',
            'Vary'          => 'Accept',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }

    private function prefersMarkdown(Request $request): bool
    {
        $accept = (string) $request->header('Accept', '');
        if ($accept === '' || $accept === '*/*') {
            return false;
        }

        // Simple preference: markdown explicit AND no higher-priority html
        $hasMarkdown = str_contains($accept, 'text/markdown');
        $hasHtml     = str_contains($accept, 'text/html');

        return $hasMarkdown && ! $hasHtml;
    }

    private function inWhitelist(string $path): bool
    {
        foreach (array_keys(self::WHITELIST) as $prefix) {
            if (str_starts_with($path, $prefix) && strlen($path) > strlen($prefix)) {
                return true;
            }
        }
        return false;
    }
}
```

- [ ] **Step 4: Register in the `web` middleware group**

In `app/Http/Kernel.php`'s `'web'` array (append at end, after `AddDiscoveryHeaders`):

```php
\App\Http\Middleware\ServeMarkdown::class,
```

- [ ] **Step 5: Run — expect pass**

```bash
./vendor/bin/phpunit --filter MarkdownNegotiationTest
```

- [ ] **Step 6: Commit**

```bash
git add app/Http/Middleware/ServeMarkdown.php app/Http/Kernel.php tests/Feature/Agents/MarkdownNegotiationTest.php
git commit -m "feat: markdown content negotiation for insights/knowledge/events/internships/cases"
```

### Task 2.2: Add `pages/*` support with denylist

**Files:**
- Modify: `app/Http/Middleware/ServeMarkdown.php`
- Modify: `config/dlf.php` — add `markdown_negotiation.pages_denylist`
- Modify: `tests/Feature/Agents/MarkdownNegotiationTest.php`

- [ ] **Step 1: Add a denylist test**

Append to the test class:

```php
public function testPagesSupportMarkdownNegotiation(): void
{
    // Use the 'over-ons' page which exists in the redirect table.
    $response = $this->get('/over-ons.md');

    if ($response->status() === 404) {
        $this->markTestSkipped('over-ons page missing in this environment');
    }

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
}

public function testDenylistedPagesFallThroughToHtml(): void
{
    config()->set('dlf.markdown_negotiation.pages_denylist', ['bedankt', 'thank-you']);

    // A denylisted path should NOT be served as markdown even with .md
    $response = $this->get('/bedankt.md');

    // Route doesn't exist for .md on denylisted pages; should 404 since we don't serve markdown.
    $this->assertContains($response->status(), [404, 405]);
}
```

- [ ] **Step 2: Extend the middleware**

Update `handle()` in `ServeMarkdown` — after the whitelist check, before the entry lookup, add a `pages` branch. Modify `inWhitelist()` to return `true` for non-collection pages that are not denylisted, and adjust the entry resolution:

```php
private function inWhitelist(string $path): bool
{
    foreach (array_keys(self::WHITELIST) as $prefix) {
        if (str_starts_with($path, $prefix) && strlen($path) > strlen($prefix)) {
            return true;
        }
    }

    // Top-level pages (single-segment or parent_uri/slug) from the 'pages' collection.
    if ($path === '/' || $path === '') {
        return false;
    }

    $slug = ltrim($path, '/');
    $denylist = (array) config('dlf.markdown_negotiation.pages_denylist', []);

    return ! in_array($slug, $denylist, true);
}
```

- [ ] **Step 3: Add config**

Append to `config/dlf.php`:

```php
'markdown_negotiation' => [
    'pages_denylist' => [
        'bedankt',
        'thank-you',
    ],
],
```

- [ ] **Step 4: Run tests — expect pass**

```bash
./vendor/bin/phpunit --filter MarkdownNegotiationTest
```

- [ ] **Step 5: Commit**

```bash
git add app/Http/Middleware/ServeMarkdown.php config/dlf.php tests/Feature/Agents/MarkdownNegotiationTest.php
git commit -m "feat: extend markdown negotiation to top-level pages with denylist"
```

---

## Phase 3 — MCP server

### Task 3.1: Wire `routes/ai.php` and add a minimal `DlfServer`

**Files:**
- Create: `routes/ai.php`
- Modify: `app/Providers/RouteServiceProvider.php`
- Create: `app/Mcp/Servers/DlfServer.php`
- Test: `tests/Feature/Agents/Mcp/ServerDiscoveryTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Agents\Mcp;

use Tests\TestCase;

class ServerDiscoveryTest extends TestCase
{
    public function testMcpEndpointReturnsToolsList(): void
    {
        $response = $this->postJson('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/list',
            'params' => [],
        ]);

        $response->assertOk();
        $data = $response->json();

        $this->assertSame('2.0', $data['jsonrpc']);
        $this->assertArrayHasKey('tools', $data['result']);
        $this->assertIsArray($data['result']['tools']);
        // No tools registered yet — tighter assertion added in Task 3.9.
    }
}
```

(If the first `tools/list` attempt in execution reveals the Laravel MCP package uses different JSON-RPC framing, adjust the assertions — but the response must include a tool list reachable at `/mcp`.)

- [ ] **Step 2: Run — expect fail**

```bash
./vendor/bin/phpunit --filter ServerDiscoveryTest
```

- [ ] **Step 3: Create `routes/ai.php`**

```php
<?php

declare(strict_types=1);

use App\Mcp\Servers\DlfServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp', DlfServer::class);
```

- [ ] **Step 4: Register the route file**

In `app/Providers/RouteServiceProvider.php`'s `boot()`, inside `$this->routes(function () { ... })`, append after the `web` group registration:

```php
Route::middleware(['web', 'throttle:60,1'])
    ->group(base_path('routes/ai.php'));
```

- [ ] **Step 5: Create the server class**

```php
<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;

class DlfServer extends Server
{
    public string $serverName = 'Dutch Laravel Foundation';
    public string $serverVersion = '1.0.0';
    public string $instructions = 'Read-only MCP server for the Dutch Laravel Foundation. Exposes foundation content: insights, knowledge base, events, internships, cases, members, board, partners. Content is in Dutch and English. When quoting, cite the URL from each item as the source.';

    /** @var array<int, class-string> */
    public array $tools = [
        // Tools registered in later tasks
    ];
}
```

(If the Laravel MCP package version uses a different property or method name for registering tools, adjust accordingly — check `vendor/laravel/mcp/src/Server.php`. The test in Step 1 will guide you: it just needs `tools/list` to work.)

- [ ] **Step 6: Run — expect pass (empty tool list is fine)**

```bash
./vendor/bin/phpunit --filter ServerDiscoveryTest
```

The test only asserts the response shape, not the content of the tools list. Tighter assertions are added in Task 3.9 once all tools are registered.

- [ ] **Step 7: Commit**

```bash
git add routes/ai.php app/Providers/RouteServiceProvider.php app/Mcp/Servers/DlfServer.php tests/Feature/Agents/Mcp/ServerDiscoveryTest.php
git commit -m "feat: mount Laravel MCP server at /mcp with throttle:60,1"
```

### Task 3.2: `EntryFormatter` shared support class

**Files:**
- Create: `app/Mcp/Support/EntryFormatter.php`
- Test: `tests/Feature/Agents/Mcp/EntryFormatterTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Agents\Mcp;

use App\Mcp\Support\EntryFormatter;
use Statamic\Facades\Entry;
use Tests\TestCase;

class EntryFormatterTest extends TestCase
{
    public function testFormatsListItemFromInsightEntry(): void
    {
        $entry = Entry::query()
            ->where('collection', 'insights')
            ->where('published', true)
            ->first();

        if ($entry === null) {
            $this->markTestSkipped('No published insights');
        }

        $item = (new EntryFormatter())->listItem($entry);

        $this->assertSame((string) $entry->get('title'), $item['title']);
        $this->assertSame('insights', $item['collection']);
        $this->assertSame((string) $entry->absoluteUrl(), $item['url']);
        $this->assertArrayHasKey('excerpt', $item);
        $this->assertArrayHasKey('published_at', $item);
    }
}
```

- [ ] **Step 2: Run — expect fail**

```bash
./vendor/bin/phpunit --filter EntryFormatterTest
```

- [ ] **Step 3: Implement the formatter**

```php
<?php

declare(strict_types=1);

namespace App\Mcp\Support;

use Statamic\Contracts\Entries\Entry;

class EntryFormatter
{
    /**
     * @return array{title: string, collection: string, url: string, excerpt: string|null, published_at: string|null}
     */
    public function listItem(Entry $entry): array
    {
        $excerpt = $entry->get('excerpt') ?? $entry->get('meta_description') ?? null;

        return [
            'title'        => (string) $entry->get('title'),
            'collection'   => $entry->collectionHandle(),
            'url'          => (string) $entry->absoluteUrl(),
            'excerpt'      => $excerpt === null ? null : (string) $excerpt,
            'published_at' => $entry->date()?->format('Y-m-d'),
        ];
    }
}
```

- [ ] **Step 4: Run — expect pass**

```bash
./vendor/bin/phpunit --filter EntryFormatterTest
```

- [ ] **Step 5: Commit**

```bash
git add app/Mcp/Support/EntryFormatter.php tests/Feature/Agents/Mcp/EntryFormatterTest.php
git commit -m "feat: add EntryFormatter shared support for MCP tools"
```

### Task 3.3: `ListInsights` tool

**Files:**
- Create: `app/Mcp/Tools/ListInsights.php`
- Modify: `app/Mcp/Servers/DlfServer.php` — add to `$tools`
- Test: `tests/Feature/Agents/Mcp/ListInsightsTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Agents\Mcp;

use Tests\TestCase;

class ListInsightsTest extends TestCase
{
    public function testListsInsightsAsJsonArray(): void
    {
        $response = $this->postJson('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_insights',
                'arguments' => ['limit' => 5],
            ],
        ]);

        $response->assertOk();
        $data = $response->json();

        $this->assertArrayHasKey('content', $data['result']);
        $text = $data['result']['content'][0]['text'];
        $decoded = json_decode($text, true);
        $this->assertIsArray($decoded);

        if ($decoded !== []) {
            $this->assertArrayHasKey('title', $decoded[0]);
            $this->assertArrayHasKey('url', $decoded[0]);
            $this->assertArrayHasKey('collection', $decoded[0]);
            $this->assertSame('insights', $decoded[0]['collection']);
        }

        $this->assertLessThanOrEqual(5, count($decoded));
    }
}
```

- [ ] **Step 2: Run — expect fail**

```bash
./vendor/bin/phpunit --filter ListInsightsTest
```

- [ ] **Step 3: Implement the tool**

```php
<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Support\EntryFormatter;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Statamic\Facades\Entry;

class ListInsights extends Tool
{
    public string $name = 'list_insights';
    public string $description = 'List published Dutch Laravel Foundation insights (blog articles), newest first. Each item includes title, URL, excerpt, and publish date.';

    public function handle(Request $request): Response
    {
        $limit = min((int) $request->integer('limit', 10), 50);
        $page  = max((int) $request->integer('page', 1), 1);
        $since = $request->date('since');

        $query = Entry::query()
            ->where('collection', 'insights')
            ->where('published', true)
            ->orderBy('date', 'desc');

        if ($since !== null) {
            $query->where('date', '>=', $since);
        }

        $entries = $query->forPage($page, $limit)->get();
        $formatter = new EntryFormatter();

        $items = $entries->map(fn ($e) => $formatter->listItem($e))->all();

        return Response::json($items);
    }
}
```

- [ ] **Step 4: Register in `DlfServer::$tools`**

```php
public array $tools = [
    \App\Mcp\Tools\ListInsights::class,
];
```

- [ ] **Step 5: Run — expect pass**

```bash
./vendor/bin/phpunit --filter ListInsightsTest
```

- [ ] **Step 6: Commit**

```bash
git add app/Mcp/Tools/ListInsights.php app/Mcp/Servers/DlfServer.php tests/Feature/Agents/Mcp/ListInsightsTest.php
git commit -m "feat: add list_insights MCP tool"
```

### Task 3.4: `GetInsight` tool (full content for a slug)

**Files:**
- Create: `app/Mcp/Tools/GetInsight.php`
- Modify: `app/Mcp/Servers/DlfServer.php`
- Test: `tests/Feature/Agents/Mcp/GetInsightTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Agents\Mcp;

use Statamic\Facades\Entry;
use Tests\TestCase;

class GetInsightTest extends TestCase
{
    public function testReturnsFullMarkdownForKnownSlug(): void
    {
        $entry = Entry::query()
            ->where('collection', 'insights')
            ->where('published', true)
            ->first();

        if ($entry === null) {
            $this->markTestSkipped('No insights');
        }

        $response = $this->postJson('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => [
                'name' => 'get_insight',
                'arguments' => ['slug' => $entry->slug()],
            ],
        ]);

        $response->assertOk();
        $text = $response->json('result.content.0.text');

        $this->assertStringContainsString('# ' . $entry->get('title'), $text);
        $this->assertStringContainsString('**URL:**', $text);
    }

    public function testUnknownSlugReturnsError(): void
    {
        $response = $this->postJson('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => [
                'name' => 'get_insight',
                'arguments' => ['slug' => 'definitely-does-not-exist-12345'],
            ],
        ]);

        $response->assertOk();
        $this->assertTrue($response->json('result.isError') ?? false);
    }
}
```

- [ ] **Step 2: Run — expect fail**

```bash
./vendor/bin/phpunit --filter GetInsightTest
```

- [ ] **Step 3: Implement**

```php
<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Services\Agents\EntryMarkdownRenderer;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Statamic\Facades\Entry;

class GetInsight extends Tool
{
    public string $name = 'get_insight';
    public string $description = 'Fetch the full content of a single insight article as markdown, given its slug.';

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:255',
        ]);

        $entry = Entry::query()
            ->where('collection', 'insights')
            ->where('slug', $validated['slug'])
            ->where('published', true)
            ->first();

        if ($entry === null) {
            return Response::error("No published insight found with slug: {$validated['slug']}");
        }

        return Response::text(app(EntryMarkdownRenderer::class)->render($entry));
    }
}
```

- [ ] **Step 4: Register in `DlfServer::$tools`**

Append `\App\Mcp\Tools\GetInsight::class` to the array.

- [ ] **Step 5: Run — expect pass**

```bash
./vendor/bin/phpunit --filter GetInsightTest
```

- [ ] **Step 6: Commit**

```bash
git add app/Mcp/Tools/GetInsight.php app/Mcp/Servers/DlfServer.php tests/Feature/Agents/Mcp/GetInsightTest.php
git commit -m "feat: add get_insight MCP tool"
```

### Task 3.5: `ListKnowledge` + `GetKnowledgeArticle` tools

**Files:**
- Create: `app/Mcp/Tools/ListKnowledge.php`
- Create: `app/Mcp/Tools/GetKnowledgeArticle.php`
- Modify: `app/Mcp/Servers/DlfServer.php`
- Test: `tests/Feature/Agents/Mcp/KnowledgeToolsTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Agents\Mcp;

use Statamic\Facades\Entry;
use Tests\TestCase;

class KnowledgeToolsTest extends TestCase
{
    public function testListKnowledgeReturnsJsonArray(): void
    {
        $response = $this->postJson('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => [
                'name' => 'list_knowledge',
                'arguments' => ['limit' => 5],
            ],
        ]);

        $response->assertOk();
        $items = json_decode($response->json('result.content.0.text'), true);

        $this->assertIsArray($items);
        if ($items !== []) {
            $this->assertSame('knowledge', $items[0]['collection']);
        }
    }

    public function testGetKnowledgeArticleReturnsMarkdown(): void
    {
        $entry = Entry::query()
            ->where('collection', 'knowledge')
            ->where('published', true)
            ->first();

        if ($entry === null) {
            $this->markTestSkipped('No knowledge entries');
        }

        $response = $this->postJson('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => [
                'name' => 'get_knowledge_article',
                'arguments' => ['slug' => $entry->slug()],
            ],
        ]);

        $response->assertOk();
        $text = $response->json('result.content.0.text');
        $this->assertStringContainsString('# ' . $entry->get('title'), $text);
    }
}
```

- [ ] **Step 2: Run — expect fail**

```bash
./vendor/bin/phpunit --filter KnowledgeToolsTest
```

- [ ] **Step 3: Implement `ListKnowledge`**

```php
<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Support\EntryFormatter;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Statamic\Facades\Entry;

class ListKnowledge extends Tool
{
    public string $name = 'list_knowledge';
    public string $description = 'List entries from the Dutch Laravel Foundation knowledge base (guides, how-to articles, reference docs).';

    public function handle(Request $request): Response
    {
        $limit = min((int) $request->integer('limit', 10), 50);
        $page  = max((int) $request->integer('page', 1), 1);

        $entries = Entry::query()
            ->where('collection', 'knowledge')
            ->where('published', true)
            ->orderBy('date', 'desc')
            ->forPage($page, $limit)
            ->get();

        $formatter = new EntryFormatter();

        return Response::json($entries->map(fn ($e) => $formatter->listItem($e))->all());
    }
}
```

- [ ] **Step 4: Implement `GetKnowledgeArticle`**

```php
<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Services\Agents\EntryMarkdownRenderer;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Statamic\Facades\Entry;

class GetKnowledgeArticle extends Tool
{
    public string $name = 'get_knowledge_article';
    public string $description = 'Fetch the full content of a single knowledge base article as markdown, given its slug.';

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:255',
        ]);

        $entry = Entry::query()
            ->where('collection', 'knowledge')
            ->where('slug', $validated['slug'])
            ->where('published', true)
            ->first();

        if ($entry === null) {
            return Response::error("No published knowledge article found with slug: {$validated['slug']}");
        }

        return Response::text(app(EntryMarkdownRenderer::class)->render($entry));
    }
}
```

- [ ] **Step 5: Register both in `DlfServer::$tools`**

Append `\App\Mcp\Tools\ListKnowledge::class` and `\App\Mcp\Tools\GetKnowledgeArticle::class`.

- [ ] **Step 6: Run — expect pass**

```bash
./vendor/bin/phpunit --filter KnowledgeToolsTest
```

- [ ] **Step 7: Commit**

```bash
git add app/Mcp/Tools/ListKnowledge.php app/Mcp/Tools/GetKnowledgeArticle.php app/Mcp/Servers/DlfServer.php tests/Feature/Agents/Mcp/KnowledgeToolsTest.php
git commit -m "feat: add list_knowledge and get_knowledge_article MCP tools"
```

### Task 3.6: List tools for the remaining collections (events, internships, cases, members, board, partners)

**Files:**
- Create: `app/Mcp/Tools/ListEvents.php`
- Create: `app/Mcp/Tools/ListInternships.php`
- Create: `app/Mcp/Tools/ListCases.php`
- Create: `app/Mcp/Tools/ListMembers.php`
- Create: `app/Mcp/Tools/ListBoard.php`
- Create: `app/Mcp/Tools/ListPartners.php`
- Modify: `app/Mcp/Servers/DlfServer.php`
- Test: `tests/Feature/Agents/Mcp/ListCollectionToolsTest.php`

- [ ] **Step 1: Write the failing test covering all six tools**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Agents\Mcp;

use Tests\TestCase;

class ListCollectionToolsTest extends TestCase
{
    /**
     * @return array<int, array{0: string, 1: string}>
     */
    public static function toolCases(): array
    {
        return [
            ['list_events', 'events'],
            ['list_internships', 'internships'],
            ['list_cases', 'cases'],
            ['list_members', 'members'],
            ['list_board', 'board'],
            ['list_partners', 'partners'],
        ];
    }

    /**
     * @dataProvider toolCases
     */
    public function testToolReturnsJsonArrayForCollection(string $toolName, string $expectedCollection): void
    {
        $response = $this->postJson('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => [
                'name' => $toolName,
                'arguments' => ['limit' => 5],
            ],
        ]);

        $response->assertOk();
        $items = json_decode((string) $response->json('result.content.0.text'), true);

        $this->assertIsArray($items, "tool {$toolName} should return a JSON array");

        if ($items !== []) {
            $this->assertSame($expectedCollection, $items[0]['collection']);
            $this->assertArrayHasKey('title', $items[0]);
            $this->assertArrayHasKey('url', $items[0]);
        }
    }
}
```

- [ ] **Step 2: Run — expect fail**

```bash
./vendor/bin/phpunit --filter ListCollectionToolsTest
```

- [ ] **Step 3: Create a shared abstract `ListCollectionTool`**

Reduce duplication: `app/Mcp/Tools/ListCollectionTool.php`:

```php
<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Support\EntryFormatter;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Statamic\Facades\Entry;

abstract class ListCollectionTool extends Tool
{
    abstract protected function collectionHandle(): string;

    protected function sortField(): string
    {
        return 'date';
    }

    protected function sortDirection(): string
    {
        return 'desc';
    }

    public function handle(Request $request): Response
    {
        $limit = min((int) $request->integer('limit', 10), 50);
        $page  = max((int) $request->integer('page', 1), 1);

        $entries = Entry::query()
            ->where('collection', $this->collectionHandle())
            ->where('published', true)
            ->orderBy($this->sortField(), $this->sortDirection())
            ->forPage($page, $limit)
            ->get();

        $formatter = new EntryFormatter();

        return Response::json($entries->map(fn ($e) => $formatter->listItem($e))->all());
    }
}
```

- [ ] **Step 4: Create the six concrete tools**

Each follows the same pattern. Example for `ListEvents`:

```php
<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

class ListEvents extends ListCollectionTool
{
    public string $name = 'list_events';
    public string $description = 'List Dutch Laravel Foundation events (meetups, conferences, workshops). Default order: upcoming first.';

    protected function collectionHandle(): string { return 'events'; }
    protected function sortField(): string { return 'date'; }
    protected function sortDirection(): string { return 'asc'; }
}
```

Apply the same pattern for the other five:

```php
class ListInternships extends ListCollectionTool
{
    public string $name = 'list_internships';
    public string $description = 'List open internship listings at Dutch Laravel Foundation member companies.';
    protected function collectionHandle(): string { return 'internships'; }
}

class ListCases extends ListCollectionTool
{
    public string $name = 'list_cases';
    public string $description = 'List case studies from Dutch Laravel Foundation member companies.';
    protected function collectionHandle(): string { return 'cases'; }
    protected function sortDirection(): string { return 'asc'; }
}

class ListMembers extends ListCollectionTool
{
    public string $name = 'list_members';
    public string $description = 'List member companies of the Dutch Laravel Foundation.';
    protected function collectionHandle(): string { return 'members'; }
    protected function sortField(): string { return 'title'; }
    protected function sortDirection(): string { return 'asc'; }
}

class ListBoard extends ListCollectionTool
{
    public string $name = 'list_board';
    public string $description = 'List board members of the Dutch Laravel Foundation.';
    protected function collectionHandle(): string { return 'board'; }
    protected function sortField(): string { return 'title'; }
    protected function sortDirection(): string { return 'asc'; }
}

class ListPartners extends ListCollectionTool
{
    public string $name = 'list_partners';
    public string $description = 'List partner organizations of the Dutch Laravel Foundation.';
    protected function collectionHandle(): string { return 'partners'; }
    protected function sortField(): string { return 'title'; }
    protected function sortDirection(): string { return 'asc'; }
}
```

Each goes in its own file (`app/Mcp/Tools/ListInternships.php`, etc.) with the correct namespace declaration. The `<?php declare(strict_types=1); namespace App\Mcp\Tools;` preamble is the same for all.

- [ ] **Step 5: Register all six in `DlfServer::$tools`**

```php
public array $tools = [
    \App\Mcp\Tools\ListInsights::class,
    \App\Mcp\Tools\GetInsight::class,
    \App\Mcp\Tools\ListKnowledge::class,
    \App\Mcp\Tools\GetKnowledgeArticle::class,
    \App\Mcp\Tools\ListEvents::class,
    \App\Mcp\Tools\ListInternships::class,
    \App\Mcp\Tools\ListCases::class,
    \App\Mcp\Tools\ListMembers::class,
    \App\Mcp\Tools\ListBoard::class,
    \App\Mcp\Tools\ListPartners::class,
];
```

- [ ] **Step 6: Run — expect pass**

```bash
./vendor/bin/phpunit --filter ListCollectionToolsTest
```

- [ ] **Step 7: Commit**

```bash
git add app/Mcp/Tools/ListCollectionTool.php app/Mcp/Tools/ListEvents.php app/Mcp/Tools/ListInternships.php app/Mcp/Tools/ListCases.php app/Mcp/Tools/ListMembers.php app/Mcp/Tools/ListBoard.php app/Mcp/Tools/ListPartners.php app/Mcp/Servers/DlfServer.php tests/Feature/Agents/Mcp/ListCollectionToolsTest.php
git commit -m "feat: add MCP list tools for events, internships, cases, members, board, partners"
```

### Task 3.7: `SearchContent` tool (cross-collection full-text search)

**Files:**
- Create: `app/Mcp/Tools/SearchContent.php`
- Modify: `app/Mcp/Servers/DlfServer.php`
- Test: `tests/Feature/Agents/Mcp/SearchContentTest.php`

- [ ] **Step 1: Verify Statamic search is available**

```bash
php artisan statamic:search:update --all 2>&1 | tail -10
```

If the command fails with "no index", configure a default local index. Check `config/statamic/search.php` for existing `default` index config. If the default exists, proceed. If not, add a local driver config in `config/statamic/search.php` covering the curated collections:

```php
'indexes' => [
    'default' => [
        'driver' => 'local',
        'searchables' => ['collection:insights', 'collection:knowledge', 'collection:events', 'collection:internships', 'collection:cases'],
        'fields' => ['title', 'excerpt', 'content'],
    ],
],
```

Re-run `php artisan statamic:search:update --all` — expected: no errors.

- [ ] **Step 2: Write the failing test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Agents\Mcp;

use Tests\TestCase;

class SearchContentTest extends TestCase
{
    public function testSearchReturnsJsonArray(): void
    {
        $response = $this->postJson('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => [
                'name' => 'search_content',
                'arguments' => ['query' => 'laravel', 'limit' => 5],
            ],
        ]);

        $response->assertOk();
        $items = json_decode((string) $response->json('result.content.0.text'), true);
        $this->assertIsArray($items);

        if ($items !== []) {
            $this->assertArrayHasKey('title', $items[0]);
            $this->assertArrayHasKey('url', $items[0]);
            $this->assertArrayHasKey('collection', $items[0]);
        }
    }

    public function testSearchRequiresQuery(): void
    {
        $response = $this->postJson('/mcp', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => [
                'name' => 'search_content',
                'arguments' => [],
            ],
        ]);

        $response->assertOk();
        $this->assertTrue($response->json('result.isError') ?? false);
    }
}
```

- [ ] **Step 3: Run — expect fail**

```bash
./vendor/bin/phpunit --filter SearchContentTest
```

- [ ] **Step 4: Implement the tool**

```php
<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Support\EntryFormatter;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Statamic\Facades\Search;

class SearchContent extends Tool
{
    public string $name = 'search_content';
    public string $description = 'Full-text search across Dutch Laravel Foundation content (insights, knowledge, events, internships, cases). Returns a ranked list of matching items with title, URL, and excerpt.';

    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'query' => 'required|string|min:2|max:200',
            'collections' => 'nullable|array',
            'collections.*' => 'in:insights,knowledge,events,internships,cases',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $limit = (int) ($validated['limit'] ?? 10);

        $results = Search::index('default')->search($validated['query']);
        $formatter = new EntryFormatter();

        $items = collect($results)
            ->map(fn ($hit) => $hit->getSearchable())
            ->filter(function ($entry) use ($validated) {
                if (! $entry || ! method_exists($entry, 'collectionHandle')) {
                    return false;
                }
                if (empty($validated['collections'])) {
                    return true;
                }
                return in_array($entry->collectionHandle(), $validated['collections'], true);
            })
            ->take($limit)
            ->map(fn ($entry) => $formatter->listItem($entry))
            ->values()
            ->all();

        return Response::json($items);
    }
}
```

- [ ] **Step 5: Register in `DlfServer::$tools`**

Append `\App\Mcp\Tools\SearchContent::class`.

- [ ] **Step 6: Run — expect pass**

```bash
./vendor/bin/phpunit --filter SearchContentTest
```

- [ ] **Step 7: Commit**

```bash
git add app/Mcp/Tools/SearchContent.php app/Mcp/Servers/DlfServer.php tests/Feature/Agents/Mcp/SearchContentTest.php config/statamic/search.php
git commit -m "feat: add search_content MCP tool backed by Statamic search"
```

### Task 3.8: Rate limit verification

**Files:**
- Test: `tests/Feature/Agents/Mcp/RateLimitTest.php`

- [ ] **Step 1: Write the test**

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Agents\Mcp;

use Tests\TestCase;

class RateLimitTest extends TestCase
{
    public function testMcpEndpointEnforcesRateLimit(): void
    {
        // 60 allowed, 61st should 429
        for ($i = 0; $i < 60; $i++) {
            $r = $this->postJson('/mcp', [
                'jsonrpc' => '2.0', 'id' => $i, 'method' => 'tools/list', 'params' => [],
            ]);
            $this->assertSame(200, $r->status(), "request {$i} expected 200");
        }

        $over = $this->postJson('/mcp', [
            'jsonrpc' => '2.0', 'id' => 61, 'method' => 'tools/list', 'params' => [],
        ]);

        $this->assertSame(429, $over->status());
    }
}
```

- [ ] **Step 2: Run — expect pass**

```bash
./vendor/bin/phpunit --filter RateLimitTest
```

(If the test is flaky due to cached limiter state between tests, isolate with `RateLimiter::clear('mcp')` or similar in a `setUp()` call — consult `vendor/laravel/framework/src/Illuminate/Routing/Middleware/ThrottleRequests.php` for the key format.)

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/Agents/Mcp/RateLimitTest.php
git commit -m "test: verify /mcp endpoint rate limit (60/min)"
```

### Task 3.9: Tighten `ServerDiscoveryTest` now that tools exist

**Files:**
- Modify: `tests/Feature/Agents/Mcp/ServerDiscoveryTest.php`

- [ ] **Step 1: Tighten assertions**

Replace the `assertIsArray` / `assertNotEmpty` check with explicit tool-name checks:

```php
public function testMcpEndpointListsAllExpectedTools(): void
{
    $response = $this->postJson('/mcp', [
        'jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/list', 'params' => [],
    ]);

    $tools = collect($response->json('result.tools'))->pluck('name')->all();

    foreach ([
        'search_content',
        'list_insights', 'get_insight',
        'list_knowledge', 'get_knowledge_article',
        'list_events', 'list_internships', 'list_cases',
        'list_members', 'list_board', 'list_partners',
    ] as $expected) {
        $this->assertContains($expected, $tools, "missing tool: {$expected}");
    }
}
```

- [ ] **Step 2: Run — expect pass**

```bash
./vendor/bin/phpunit --filter ServerDiscoveryTest
```

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/Agents/Mcp/ServerDiscoveryTest.php
git commit -m "test: assert all expected MCP tools are discoverable"
```

---

## Phase 4 — Full test run and README note

### Task 4.1: Run the entire test suite

- [ ] **Step 1: Run everything**

```bash
./vendor/bin/phpunit
```

Expected: all green. Fix any regressions before continuing.

- [ ] **Step 2: If any tests fail that are unrelated to this work, document them separately** — do not silently fix. Commit only intentional changes.

### Task 4.2: README note on MCP and search index

**Files:**
- Modify: `README.md`

- [ ] **Step 1: Append a section**

```markdown
## LLM / Agent integration

This site exposes content to LLMs and agent frameworks:

- `robots.txt` — AI bot rules and Cloudflare Content Signals
- `/llms.txt` and `/llms-full.txt` — index and full dump of curated content
- `/.well-known/mcp.json` — MCP server discovery card
- Markdown negotiation — append `.md` to any content URL, or send `Accept: text/markdown`
- MCP server — `/mcp` (rate limited to 60 req/min per IP)

After adding or editing insights/knowledge/events/internships/cases content, rebuild the search index used by the `search_content` MCP tool:

\`\`\`
php please search:update --all
\`\`\`

The llms.txt caches are invalidated automatically on Statamic `EntrySaved` / `EntryDeleted` events.
```

- [ ] **Step 2: Commit**

```bash
git add README.md
git commit -m "docs: document LLM/agent integration surface"
```

### Task 4.3: Manual verification checklist (post-deploy)

After merging and deploying, run this smoke test:

- [ ] `curl -s https://dutchlaravelfoundation.nl/robots.txt` — contains `ClaudeBot`, `GPTBot`, `Content-Signal`, `Sitemap:`
- [ ] `curl -s https://dutchlaravelfoundation.nl/llms.txt | head -30` — markdown with preamble + sections
- [ ] `curl -s https://dutchlaravelfoundation.nl/llms-full.txt | wc -l` — substantially longer than llms.txt
- [ ] `curl -sI https://dutchlaravelfoundation.nl/ | grep -i link` — three Link headers
- [ ] `curl -s https://dutchlaravelfoundation.nl/.well-known/mcp.json | jq .` — valid JSON with transport URL
- [ ] `curl -s -H "Accept: text/markdown" https://dutchlaravelfoundation.nl/nieuws/<any-slug> | head` — markdown output
- [ ] `curl -s https://dutchlaravelfoundation.nl/nieuws/<any-slug>.md | head` — markdown output
- [ ] `curl -s -X POST https://dutchlaravelfoundation.nl/mcp -H "Content-Type: application/json" -d '{"jsonrpc":"2.0","id":1,"method":"tools/list","params":{}}' | jq '.result.tools | length'` — returns 11
- [ ] Connect from Claude Desktop (`claude_desktop_config.json` with `"dlf": { "command": "npx", "args": ["-y", "mcp-remote", "https://dutchlaravelfoundation.nl/mcp"] }`) and verify tools appear
- [ ] Re-run https://isitagentready.com/dutchlaravelfoundation.nl — record the new score in the PR description.
