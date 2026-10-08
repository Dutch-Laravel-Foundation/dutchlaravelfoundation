# Dutch Laravel Foundation website

## Installation and workspace checks

Use PHP 8.5 (with PCOV, SQLite and zip), Composer, Node 24, npm 11.19.0+
and Bun 1.4.2. Follow [the dependency trust and update policy](docs/dependency-policy.md)
for seven-day release holds, advisory audits, Vet review and urgent CVE exceptions.
Install locked dependencies with `composer install --prefer-dist --no-interaction`
and `npm ci --no-audit --no-fund`. For a **fresh, disposable workspace only**:

```sh
cp .env.example .env # never overwrite an environment-provided .env
mkdir -p users storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs
touch database/database.sqlite
php artisan key:generate --no-interaction
bun run build
```

Do not copy production `.env`, users, databases or form submissions. Example
configuration contains no credentials: SQLite stays inside the workspace, mail
uses the log driver, queues run synchronously and response caching is disabled
(no Redis required). Tracked `storage/forms` must resolve to the workspace-local
`storage/form-submissions`, never a production shared directory. Dependencies,
local credentials, submissions, SQLite files and Pest/TIA caches remain untracked.

`composer quality` is the canonical non-mutating gate: Pint, Larastan level 5,
the **full** Pest suite (including architecture and isolation contracts), Bun
tests, TypeScript and client/SSR builds. `composer test` retains the fast local
TIA path; `composer test:full` bypasses selection. CI runs the same PHP/JS gates
and build before recording its full TIA baseline. Static analysis starts with a
counted, file/message-specific inventory of 29 existing diagnostics in
`phpstan-baseline.neon`; new diagnostics fail and stale ignores fail. Do not
regenerate that inventory to hide regressions. Analysis uses PHPStan's debug
execution mode (no result-cache reuse or parallel workers) with CLI OPcache off
to avoid a reproduced PHP 8.5 heap corruption during cached analysis; all rules
and the counted diagnostic inventory still apply. No test gate was removed. PHPUnit forces SSR off as well as in-memory services;
the test application factory rejects cached configuration before bootstrap so
cached production settings cannot bypass isolation. A disposable sentinel cache
regression verifies refusal without evaluating the cache or booting providers.

### One development start path

The canonical developer entry point is `composer dev`: it runs the loopback PHP
server and Vite (including Inertia's integrated SSR). The launcher propagates
the first process's exit status and stops/reaps its sibling; regression tests
exercise both failure directions using disposable command doubles, not servers. Supply **environment-assigned** `APP_URL`, `APP_PORT`,
`VITE_PORT` and `INERTIA_SSR_URL`; it refuses to start without them and Vite
uses strict port binding (no fallback to an unassigned port). A Project workspace that already manages its
server must use that managed process instead, not launch this command alongside
it. Never invent a port, acquire a topology, or start a competing server.

### Workspace/browser review evidence

For an assigned fresh Project workspace, confirm its `.env` uses only disposable
resources, run `composer quality`, then review the assigned `APP_URL` in a real
browser: homepage, editorial/detail navigation, mobile layout, contact and sales
funnel using disposable submissions; inspect console/network failures and verify
submissions land only in local storage. Existing HTTP and JS tests are not a
substitute for that browser review.

For Orbit subtask #1087, **fresh-workspace and browser proof were not executed**:
no assigned APP_URL or managed start path was supplied. Orbit refused topology
acquisition because Project VM groups use their own Project workspace. No server
was started and no topology was acquired. Deterministic checks were executed in
the existing workspace; browser confirmation remains explicitly unavailable,
not claimed as passing.

## Development agent context

`AGENTS.md` is the shared instruction file. Boost choices live in `boost.json`;
refresh generated guidelines and skills with `php artisan boost:update --no-discover`.
Load `.ai/rules/index.md` for scoped project rules and `.agents/skills` for on-demand
knowledge. Claude Code uses the same skills through `.claude/skills`.

Codex MCP configuration is tracked in `.codex/config.toml`. For Claude Code or
another MCP client, merge `.mcp.example.json` into your environment's local
`.mcp.json`; preserve any environment-provided servers and never commit credentials.
See [.ai/context-review.md](.ai/context-review.md) for the published article audit,
intentional differences, and revalidation procedure.

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
