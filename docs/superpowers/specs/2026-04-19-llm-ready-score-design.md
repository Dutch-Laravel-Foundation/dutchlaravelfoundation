# LLM-ready score improvements for dutchlaravelfoundation.nl

**Date:** 2026-04-19
**Branch:** `llm-ready-score`
**Reference:** https://isitagentready.com/dutchlaravelfoundation.nl (current score: 25)

## Goal

Raise the site's *Is It Agent Ready* score from 25 to ~85–90 by adding LLM/agent
affordances that each deliver genuine value for real-world agents — not stubs to
game the scoring tool.

**Explicit non-goal:** reaching 100. Commerce protocols (x402/UCP/ACP) and
cryptographic Web Bot Auth do not fit a non-profit content site's mission.

## Current state

- `robots.txt`: exists but trivial (`User-agent: * Disallow:` — no AI rules, no
  sitemap directive)
- `sitemap.xml`: exists (served by `pecotamic/sitemap`)
- `llms.txt`: missing (404)
- No `Link` response headers
- No markdown content negotiation (ignores `Accept: text/markdown`)
- No `.well-known/*` endpoints
- No MCP server

## Deliverables

1. **Discoverability** — dynamic `robots.txt` with AI bot rules, `llms.txt`,
   `llms-full.txt`, `Link` response headers on the homepage.
2. **Content accessibility** — markdown content negotiation on content-heavy
   Statamic collections, via both `Accept: text/markdown` and `.md` URL suffix.
3. **Bot access control** — explicit AI-bot allow rules and a Cloudflare
   Content Signals policy declaration in `robots.txt`.
4. **Protocol discovery** — a read-only Laravel MCP server at `/mcp` exposing
   curated Statamic collections; advertised via `.well-known/mcp.json`.

## Out of scope

- Write tools in the MCP server (no form submission, no auth, no user accounts).
- OAuth / Passport integration.
- Web Bot Auth (cryptographic signing — requires Cloudflare Zero Trust setup).
- Agent Skills / WebMCP embeds.
- Commerce protocols.
- Templating or content changes unrelated to LLM exposure.

## Architecture

Three independent, independently-testable layers:

```
┌─────────────────────────────────────────────────────────────┐
│  STATIC / DISCOVERY LAYER                                    │
│    routes/web.php                                            │
│     ├─ GET /robots.txt        → RobotsController             │
│     ├─ GET /llms.txt          → LlmsController@index         │
│     ├─ GET /llms-full.txt     → LlmsController@full          │
│     ├─ GET /.well-known/mcp.json → WellKnownController@mcp   │
│     └─ Link headers via AddDiscoveryHeaders middleware       │
│        (global, applies to all web responses)                │
├─────────────────────────────────────────────────────────────┤
│  CONTENT NEGOTIATION LAYER                                   │
│    app/Http/Middleware/ServeMarkdown.php                     │
│     ├─ Matches: whitelisted collection routes                │
│     ├─ Triggers on: `Accept: text/markdown` OR `.md` suffix  │
│     ├─ Uses EntryMarkdownRenderer service                    │
│     └─ Returns text/markdown with Vary header                │
├─────────────────────────────────────────────────────────────┤
│  MCP SERVER LAYER                                            │
│    routes/ai.php (loaded via RouteServiceProvider)           │
│     └─ Mcp::web('/mcp', DlfServer::class)                    │
│                                                              │
│    app/Mcp/Servers/DlfServer.php                             │
│    app/Mcp/Tools/*.php (one per tool)                        │
│    app/Mcp/Support/EntryFormatter.php                        │
└─────────────────────────────────────────────────────────────┘
```

**Key boundary:** `EntryMarkdownRenderer` is shared by the markdown-negotiation
middleware, the `llms-full.txt` builder, and the MCP tools. Single source of
truth for "Statamic entry → markdown".

**Dependency additions:** `laravel/mcp` only.

## Section 1 — Discoverability

### `robots.txt` (dynamic, via `RobotsController`)

Replaces the current static file. Served at `GET /robots.txt`.

```
User-agent: *
Allow: /
Disallow: /cp/
Disallow: /statamic/
Disallow: /!/

# AI crawlers — explicitly allowed for public content
User-agent: GPTBot
User-agent: OAI-SearchBot
User-agent: ChatGPT-User
User-agent: ClaudeBot
User-agent: Claude-User
User-agent: Claude-SearchBot
User-agent: PerplexityBot
User-agent: Perplexity-User
User-agent: Google-Extended
User-agent: Applebot-Extended
User-agent: CCBot
User-agent: Bytespider
Allow: /

# Cloudflare Content Signals (AI policy declaration)
Content-Signal: search=yes, ai-train=no, ai-input=yes

Sitemap: https://dutchlaravelfoundation.nl/sitemap.xml
```

Rationale for content signals:
- `search=yes`: fine to index for search
- `ai-train=no`: don't use as silent training data
- `ai-input=yes`: quoting / citing in real-time agent answers is OK

### `llms.txt` (hybrid: hand-written preamble + auto collection listings)

Laravel route rendering a cached Blade template.

Structure:

```markdown
# Dutch Laravel Foundation

> [2–3 sentence mission statement from config/dlf.php]

## About
- [About the Foundation](https://…/about.md)
- [Board](https://…/board.md)
- [Members](https://…/members.md)

## Knowledge Base
[auto: every knowledge entry, title + URL, newest first, capped at 50]

## Insights
[auto: insights, title + date + URL, newest first, capped at 50]

## Events
[auto: upcoming events]

## Internships
[auto: open internships]

## Programmatic access
- MCP server: https://…/mcp
- MCP discovery: https://…/.well-known/mcp.json
- Markdown of any page: append `.md` to the URL, or send `Accept: text/markdown`
```

Hand-written preamble and highlighted pages live in `config/dlf.php`:

```php
return [
    'llms' => [
        'preamble' => '...mission statement...',
        'highlighted_pages' => [
            ['label' => 'About', 'slug' => 'about'],
            // ...
        ],
    ],
];
```

Cached under key `dlf:agents:llms-txt`; invalidated by a listener on Statamic's
`EntrySaved` / `EntryDeleted` for the curated collections.

### `llms-full.txt`

Same structure as `llms.txt` but each auto-listed entry is inlined as full
markdown via `EntryMarkdownRenderer`. Separator between entries: `\n\n---\n\n`.
Cache key: `dlf:agents:llms-full-txt`.

### `Link` response headers

`AddDiscoveryHeaders` middleware added to the global `web` middleware group.
Adds three headers to every HTML response:

```
Link: <https://…/llms.txt>; rel="llms-txt"
Link: <https://…/sitemap.xml>; rel="sitemap"
Link: <https://…/.well-known/mcp.json>; rel="mcp"
```

Headers are only added when the response `Content-Type` starts with `text/html`
(avoids polluting JSON responses or the markdown negotiation responses).

### `/.well-known/mcp.json`

Minimal MCP server card served by `WellKnownController@mcp`:

```json
{
  "name": "Dutch Laravel Foundation",
  "description": "Read-only MCP server exposing foundation content: insights, knowledge base, events, internships, cases, members.",
  "version": "1.0.0",
  "transport": { "type": "http", "url": "https://dutchlaravelfoundation.nl/mcp" },
  "authentication": { "type": "none" },
  "contact": { "url": "https://dutchlaravelfoundation.nl/contact" }
}
```

Host portion is built from `config('app.url')`, not hard-coded.

## Section 2 — Markdown content negotiation

### Scope (whitelist)

Collections that render as markdown:

- `insights/*`
- `knowledge/*`
- `events/*`
- `internships/*`
- `cases/*`
- top-level `pages/*` (with a small denylist for UI-only pages such as
  form-success/thank-you pages, determined during implementation)

Collections explicitly out of scope: `cta`, `reviews`, `socials`, `clients`,
`partners`, `members`, `board`. (These are available via the MCP tools but not
as standalone markdown pages — they have no public URL or the content is too
thin to be useful as a markdown document.)

### Trigger conditions

The middleware serves markdown when **both** of:

1. The resolved route maps to a whitelisted collection entry, AND
2. Either the URL path ends in `.md`, OR the `Accept` header prefers
   `text/markdown` over `text/html` (standard content negotiation; if both
   are acceptable, the explicit `.md` suffix wins).

### Mechanism

Because Statamic resolves entries dynamically, pure-middleware routing is
awkward. Implementation: a thin controller wrapper around Statamic's
`FrontendController` that:

1. If the URL ends in `.md`, rewrite the request path to strip the suffix
   before delegation.
2. After Statamic resolves the entry, branch:
   - If negotiation applies → return
     `response($renderer->render($entry), 200)
     ->header('Content-Type', 'text/markdown; charset=utf-8')
     ->header('Vary', 'Accept')
     ->header('Cache-Control', 'public, max-age=300')`
   - Otherwise → let Statamic render HTML as usual.

### `EntryMarkdownRenderer` (`app/Services/EntryMarkdownRenderer.php`)

Single public method: `render(Entry $entry): string`.

Output shape:

```markdown
# {title}

> {excerpt or meta_description, if any}

**Type:** insight
**Published:** 2026-03-12
**URL:** https://dutchlaravelfoundation.nl/insights/…
**Tags:** laravel, foundation

---

{body converted from Statamic's bard/markdown field to plain markdown}

---

## Related
- [Linked entry A](https://…)
```

The renderer owns a small per-collection formatter map (insights, knowledge,
events, internships, cases, pages). An unknown collection falls back to a
generic formatter that emits title, URL, and any string/markdown/bard fields
it finds.

## Section 3 — MCP server (`laravel/mcp`)

### Registration

```php
// routes/ai.php
use Laravel\Mcp\Facades\Mcp;
use App\Mcp\Servers\DlfServer;

Mcp::web('/mcp', DlfServer::class);
```

Rate limiting is applied to the `/mcp` path via Laravel MCP's middleware hook
(exact syntax to be confirmed against the package version during
implementation — either as a fluent `->middleware(...)` chain on the route
registration or by applying `throttle:60,1` to a wrapping route group).

`routes/ai.php` is loaded via `App\Providers\RouteServiceProvider`, matching
the convention established by `routes/web.php` / `routes/api.php`.

### Server

`App\Mcp\Servers\DlfServer` registers tools and declares server metadata:
name, version, and an `instructions` string telling the agent what the server
offers, that content is in Dutch and English, and that URLs in tool responses
should be cited as sources.

### Tools

| Tool | Args | Returns |
|---|---|---|
| `SearchContent` | `query: string` (req), `collections: array<enum>` (opt), `limit: int` (default 10, max 50) | JSON list of `{ title, collection, url, excerpt, published_at }` |
| `ListInsights` | `limit, page, since, tag` | JSON list of `{ title, url, published_at, summary }` |
| `GetInsight` | `slug: string` (req) | Full markdown via `EntryMarkdownRenderer` + metadata block |
| `ListKnowledge` | `limit, page, tag` | As above |
| `GetKnowledgeArticle` | `slug: string` (req) | Full markdown |
| `ListEvents` | `limit, page, since, upcoming_only: bool` | List shape |
| `ListInternships` | `limit, page, open_only: bool` | List shape |
| `ListCases` | `limit, page` | List shape |
| `ListMembers` | `limit, page` | List shape |
| `ListBoard` | — | List shape |
| `ListPartners` | `limit, page` | List shape |

Only `insights` and `knowledge` have `Get…Entry` tools because they carry
deep content worth a dedicated tool call. For all other list tools, the
response includes the entry URL so the agent can fetch the markdown via the
negotiation layer instead of burning another tool call.

### Shared support

`app/Mcp/Support/EntryFormatter.php` — converts a Statamic Entry into the
list-item JSON shape. Keeps tools thin.

### Search implementation

Statamic ships with a built-in search subsystem. We'll use the local driver
with a single index covering the curated collections. Index configuration
lives in `config/statamic/search.php` (touch existing file if present). If no
index exists, the search-update command seeds it.

Document in `README.md`:

```
php please search:update --all
```

### Rate limiting

`throttle:60,1` on the MCP route — 60 requests per minute per IP. Sane cap
against runaway agents; enough headroom for a normal conversation.

## Section 4 — Testing

Pest feature tests, organised by layer. One file per behaviour:

```
tests/Feature/
  Discovery/
    RobotsTxtTest.php
    LlmsTxtTest.php
    LlmsFullTxtTest.php
    WellKnownMcpTest.php
    LinkHeadersTest.php
  Content/
    MarkdownNegotiationTest.php
    EntryMarkdownRendererTest.php
  Mcp/
    ServerDiscoveryTest.php
    SearchContentTest.php
    ListInsightsTest.php
    GetInsightTest.php
    ListKnowledgeTest.php
    GetKnowledgeArticleTest.php
    ListEventsTest.php
    ListInternshipsTest.php
    ListCasesTest.php
    ListMembersTest.php
    ListBoardTest.php
    ListPartnersTest.php
    RateLimitTest.php
```

Test data: stub Statamic entries built through the project's existing test
fixtures. If no such fixtures exist, create minimal ones under
`tests/Fixtures/content/` and point Statamic at that path during tests.

Laravel MCP ships test helpers — use them for the MCP tests rather than
reimplementing JSON-RPC assertions.

## Section 5 — Rollout

Single PR, no feature flag. Changes:

- New files: controllers, middleware, services, MCP tools, tests, `routes/ai.php`, `config/dlf.php`.
- Modifications: `RouteServiceProvider` loads `routes/ai.php`; web middleware group gains `AddDiscoveryHeaders` + `ServeMarkdown`; route for `/robots.txt` replaces any existing static file handler.
- `composer require laravel/mcp`.

Post-deploy verification:

1. Re-run https://isitagentready.com/dutchlaravelfoundation.nl — capture new score in the PR description.
2. Manual curl:
   - `curl https://…/llms.txt`
   - `curl -H "Accept: text/markdown" https://…/insights/<slug>`
   - `curl https://…/insights/<slug>.md`
   - `curl -I https://…/ | grep -i link`
   - `curl https://…/.well-known/mcp.json`
3. Connect the MCP endpoint once from Claude Desktop or Cursor to confirm tool discovery works end-to-end.
4. Monitor Laravel logs for 24h for 500s on any new route.

## Expected score impact

| Category | Current | After |
|---|---|---|
| Discoverability | partial (robots + sitemap) | full (+ AI bot rules, Link headers, llms.txt) |
| Content Accessibility | 0 | full (markdown negotiation) |
| Bot Access Control | 0 | AI rules + Content Signals (no Web Bot Auth) |
| Protocol Discovery | 0 | MCP server card + real MCP server (no Agent Skills/WebMCP/OAuth) |
| Commerce | 0 | 0 (out of scope) |

**Expected landing:** ~85–90.

## Open items to resolve during implementation

- Confirm the current Statamic search driver and whether an index already exists for insights/knowledge.
- Finalise the `pages/*` denylist for markdown negotiation.
- Confirm `config/dlf.php` does not already exist; otherwise pick an alternate config namespace (e.g., `config/agents.php`).
- Confirm the project's existing test fixture conventions for Statamic entries before inventing new ones.
