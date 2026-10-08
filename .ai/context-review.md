# Agent-context audit

Audit target: [Je Laravel-project klaarmaken voor ontwikkelen met AI, part 1](https://dutchlaravelfoundation.nl/kennis/je-laravel-project-klaarmaken-voor-ai).
Published source: `content/collections/knowledge/2026-10-05.je-laravel-project-klaarmaken-voor-ai.md` (publication commit `2e94de9`). This review covers the context/knowledge subtask, not installation of every optional tool in the article.

## Context guidance (steps 1–7, 13–14)

| Published guidance | Repository decision and evidence |
| --- | --- |
| Boost guidelines, skills, MCP and saved choices | `boost.json` enables all three for Codex and Claude Code. Generated guidance in `AGENTS.md` is fenced and reproducible. `.codex/config.toml` connects local Boost and preserves the additional Toolbar MCP integration. |
| Select useful direct-dependency package knowledge | Selected Statamic CMS and Inertia guidelines, ResponseCache, MCP and Debugbar skills. These are all direct dependencies with `resources/boost` assets. No arbitrary remote skills were installed. |
| Slim always-loaded context | Project-authored instructions occupy only the short preamble of `AGENTS.md`; package-specific guidance is generated, not copied into that preamble. `config/boost.php` enables scoped rules, moving three framework rule groups into `.ai/rules/boost`. Keep the remaining defaults, especially Statamic's package-author guidance: do not discard useful CMS knowledge simply to reduce line count. No duplicative `.ai/guidelines` override was added. |
| One instruction file | Removed the `CLAUDE.md` symlink. Both agents target `AGENTS.md`; no second copy or import is required for current Claude Code. |
| Explain deliberate departures from Laravel | Recorded focused Statamic content, public DTO, DLF page-family, cache/routing and form-storage rules through Boost's RecordRule tool. `.ai/rules/index.md` maps scopes; rules are preserved across regeneration. This documents existing architecture, not a reason to refactor it toward Eloquent. |
| One discoverable skill directory | `.agents/skills` remains canonical. Claude's configured target and `.claude/skills` symlink use that same directory. Existing DLF skill, references, artwork/rail/divider contracts and audit script are preserved. Its previously unconditional reference to an unbundled browser skill now explicitly allows assigned browser tools and requires reporting missing access. |
| Custom skills survive updates | The established `building-dlf-pages` skill remains committed directly under `.agents/skills`. It is not in Boost's managed skill list and regeneration leaves it untouched. Moving it into `.ai/skills` solely to replace it with a link would add churn without eliminating duplication. New Boost-managed custom skills can use `.ai/skills`. |
| Safe shared settings, no secrets | Replaced Codex's unconditional full-access/never-ask defaults with workspace-write/on-request. Claude settings allow normal checks and deny `.env` reads. Local settings remain ignored; only shared settings and the skills link are unignored. Permissions are defense-in-depth, not a substitute for an isolated environment. |
| Share MCP configuration | `.mcp.example.json` is a credential-free, portable template for Claude/other clients; copy/merge it into local `.mcp.json`. The assigned environment owns and excludes `.mcp.json`, including its Orbit server, so this task deliberately does not replace, publish, or force-add that local file. Codex's tracked configuration works without this copy step. |
| Refresh after package updates | Composer's existing post-update commands are preserved and followed by `boost:update --no-discover --ansi`. After adding a direct dependency, run `php artisan boost:update --discover` interactively to review new package knowledge. |

## Practices deliberately retained beyond the examples

- The DLF design skill and its normative references are substantially more specific than generic Laravel/UI guidance; they remain on-demand rather than being flattened into AGENTS.md.
- The repository's explicit presentation-only exception forbids adding/changing automated tests for styling/copy/layout unless requested. Browser review and relevant checks remain required; behavior changes still need tests. This intentionally overrides the article's blanket UI browsertest advice.
- Existing Pest TIA shared-baseline workflow and README prerequisites remain intact. Do not replace these with a simplified local-only setup.
- Required checks remain `vendor/bin/pest --compact && bun run test && bun run typecheck`; build and formatting commands are listed separately. This subtask does not claim that Rector, architecture tests, mutation tests, stop hooks, PAO or Vet have been installed/configured. Those article sections concern separate checks/dependency/workflow work, not duplicated context to add here.
- Local URLs, isolated databases, browser tooling, and secret injection are assigned by the environment, not acquired by the agent. No live browser/topology was needed for this documentation/configuration task. Do not treat a permissive disposable harness as a safe shared default.

## Revalidation

1. Run `php artisan boost:update --no-discover --no-interaction`; check that project text, custom rules, and the DLF skill remain intact.
2. Run `php artisan boost:list-skills` for Boost-managed skills; separately confirm the committed `building-dlf-pages/SKILL.md` exists in `.agents/skills` (the Boost inventory does not list directly committed custom skills) and `.claude/skills` resolves there.
3. Run `vendor/bin/pint --dirty` and `vendor/bin/pest --compact && bun run test && bun run typecheck`.
4. In a fresh supported client session, verify loaded AGENTS.md, relevant scoped rules/skills, and Boost Application Info/Search Docs against the installed packages. Database inspection requires an assigned disposable environment; do not query a live database to prove MCP connectivity.

Executed in this workspace: Boost install and repeat update succeeded, eight managed skills were listed, the DLF skill remained intact, and the Claude skills link resolved correctly. Boost ApplicationInfo returned installed PHP 8.5 and Laravel 13.34.0 without querying application data. Pint, Composer validation, diff whitespace checks, and the required Pest/Bun/typecheck chain passed.

Reviewer: confirm this context baseline meets the published guidance within the subtask's scope, with the intentional departures above preserved. Full article-wide tooling completion belongs to the task group, not this audit alone.
