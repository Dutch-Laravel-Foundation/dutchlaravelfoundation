# Dutch Laravel Foundation website

## Installation

Requirements: PHP 8.5 (with SQLite and zip), Composer, Node 24 and Bun 1.4.

```sh
composer install
npm ci
cp .env.example .env
mkdir -p users storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs
touch database/database.sqlite
php artisan key:generate
bun run build
```

The example environment uses SQLite, the log mailer and no response cache, so it needs no Redis or real credentials.

## Commands

| Command | Purpose |
| --- | --- |
| `APP_PORT=8000 VITE_PORT=5173 composer dev` | PHP server and Vite with SSR. Export both ports. `INERTIA_SSR_URL` defaults to `VITE_PORT + 1`. |
| `composer test` | Full Pest suite. Append a path or `--filter` for a targeted run. |
| `composer test -- --parallel --processes=4` | Full Pest suite with four workers, as used in CI. |
| `bun run test` / `bun run typecheck` | JavaScript tests and TypeScript. |
| `composer lint` / `composer analyse` | Pint and PHPStan (level 5, counted baseline). |
| `composer audit:dependencies` | Composer and npm advisory audits. |
| `composer check` | Pint, PHPStan, Bun tests, typecheck, build and the full Pest suite. Audits stay separate. |

Dependency updates follow the [dependency policy](docs/dependency-policy.md).

## Agent context

`AGENTS.md` (also `CLAUDE.md`) holds the agent instructions. Scoped rules live in `.ai/rules`, the DLF page skill in `.agents/skills`. Boost provides MCP only (`boost.json` turns off generated guidelines and skills). Merge `.mcp.example.json` into a local `.mcp.json` for MCP clients other than Codex.

## LLM / Agent integration

This site exposes content to LLMs and agent frameworks:

- `robots.txt` — crawl rules for search engines and AI bots
- `Content-Signal` response header — Cloudflare content-use preferences
- `/llms.txt` and `/llms-full.txt` — index and full dump of curated content
- Markdown negotiation — append `.md` to any content URL, or send `Accept: text/markdown`

The llms.txt caches are invalidated automatically on Statamic `EntrySaved` / `EntryDeleted` events.

## Testing

The `Tests` workflow runs the full Pest suite on pull requests and pushes to
`main`, alongside quality checks and dependency audits. It uses four Pest workers
to match the four CPUs on GitHub's standard public Linux runners. No coverage
extension, baseline artifacts or authenticated GitHub CLI is needed for tests.

Build assets before running feature tests. Do not rebuild assets while tests are
running: Inertia tests use the build manifest to identify the current asset version.

Local form submissions use `storage/forms -> form-submissions`. Production
deployment recreates that link to its shared forms directory. Test configuration
enables debug headers consistently across CI and development machines.
