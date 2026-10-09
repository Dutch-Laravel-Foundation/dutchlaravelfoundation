# Dutch Laravel Foundation website

## Installation

Requirements: PHP 8.5 (with PCOV, SQLite and zip), Composer, Node 24 and Bun 1.4.

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
| `php artisan serve` and `bun run dev` | PHP server and Vite with SSR. Run each in its own terminal. |
| `composer test` | Pest with TIA. `composer test:full` runs every test. |
| `bun run test` / `bun run typecheck` | JavaScript tests and TypeScript. |
| `composer lint` / `composer analyse` | Pint and PHPStan (level 5, counted baseline). |
| `composer audit:dependencies` | Composer and npm advisory audits. |
| `composer check` | Pint, PHPStan, Bun tests, typecheck, build and the full Pest suite. Audits stay separate. |

Dependency updates follow the [dependency policy](docs/dependency-policy.md).

## Agent context

`AGENTS.md` (also `CLAUDE.md`) holds the agent instructions. The DLF page skill lives in `.agents/skills`. Boost provides MCP only (`boost.json` turns off generated guidelines and skills). Merge `.mcp.example.json` into a local `.mcp.json` for MCP clients other than Codex.

## LLM / Agent integration

This site exposes content to LLMs and agent frameworks:

- `robots.txt` — crawl rules for search engines and AI bots
- `Content-Signal` response header — Cloudflare content-use preferences
- `/llms.txt` and `/llms-full.txt` — index and full dump of curated content
- Markdown negotiation — append `.md` to any content URL, or send `Accept: text/markdown`

The llms.txt caches are invalidated automatically on Statamic `EntrySaved` / `EntryDeleted` events.

## TIA baselines

The `TIA baseline` workflow runs the full Pest suite with PCOV on every push
to `main`. Only successful `main` runs upload `pest-tia-baseline`; generated
cache files stay out of Git. Pull requests run the same checks without publishing.
Artifacts are retained for 90 days. Dispatch the workflow on `main` to refresh
an expired baseline.

Development machines, including disposable task-group VMs, need PHP 8.5 with
PCOV and an authenticated GitHub CLI with access to this repository and Actions
artifacts. Provision authentication through a scoped, short-lived credential;
do not bake credentials or project baselines into VM images.

Run `vendor/bin/pest --tia --baselined --refetch` after checkout/setup to fetch
the latest published baseline and execute affected tests. Subsequent Pest runs
automatically use TIA and shared baseline retrieval. `--baseline` prints the
local cache directory. `--no-tia` executes the full suite without TIA.

TIA checks the dependency/configuration fingerprint and PHP minor version before
reuse. An incompatible baseline can require a local recording run; a shared
baseline does not guarantee every first run will skip the full suite. Keep the
complete Git history available so TIA can compare against the recorded commit.

Local form submissions use `storage/forms -> form-submissions`. Production
deployment recreates that link to its shared forms directory. Test configuration
enables debug headers and disables the optional Laravel toolbar consistently
across CI and development machines.
