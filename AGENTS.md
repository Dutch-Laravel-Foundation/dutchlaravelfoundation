# Dutch Laravel Foundation

The DLF website. Statamic CMS (flat-file content) with a React 19 / Inertia v3 public site and SSR. PHP 8.5, Laravel 13, Tailwind v4. Composer and npm lockfiles; Bun runs scripts and tests.

## Read first

- `.ai/rules/index.md`, then every rule whose paths match your change.
- For public frontend work: `.agents/skills/building-dlf-pages/SKILL.md`. Keep its page-family and divider contracts.
- Use the Boost `search-docs` MCP tool before you rely on an unfamiliar Laravel, Inertia or Statamic API.

## Architecture

- This is not an Eloquent CMS. Content lives in `content/` (collections, globals, navigation, trees). Blueprints in `resources/blueprints` and fieldsets in `resources/fieldsets` define its schema. Users live in `users/`.
- `app/Content` reads Statamic entries and maps them to Spatie Data DTOs in `app/Data`. Generated TypeScript types live in `resources/js/generated`; regenerate them with `bun run types`, never by hand.
- Inertia pages live in `resources/js/pages`. The Control Panel (`/cp`) is Statamic's own Vue app.
- Public responses use Spatie Response Cache. Statamic static caching is off on purpose (see `.ai/rules/routes.md`).
- `/llms.txt`, `/llms-full.txt` and `.md` URLs serve content to agents (`app/Http/Controllers/Agents`).
- Use `php please` for Statamic commands and `php artisan make:...` for Laravel classes. Pass `--no-interaction`.

## Commands

| Intent | Command |
| --- | --- |
| Install | `composer install`, `npm ci` |
| Build client + SSR | `bun run build` |
| Tests (TIA) | `vendor/bin/pest --compact` (add a path or `--filter`) |
| Handoff check | `vendor/bin/pest --compact && bun run test && bun run typecheck` |
| Full gate | `composer check` (Pint, PHPStan, Bun tests, typecheck, build, full Pest) |
| Format PHP | `vendor/bin/pint --dirty` |
| Dependency audits | `composer audit:dependencies` |
| Local dev server | `APP_PORT=8000 VITE_PORT=5173 composer dev` |

Feature tests need a build (`public/build/manifest.json`). PHPStan uses a counted baseline in `phpstan-baseline.neon`; fix new errors, do not add them to the baseline. TIA setup is in `README.md`.

## Boundaries

- Orbit and other managed environments already serve the app at `APP_URL`. Do not start `composer dev` or a second server there. Use that URL for browser review.
- Tests run on in-memory SQLite and array drivers (`phpunit.xml`). Never point tests or scripts at production data, real form submissions or shared storage.
- Never commit credentials, `.env`, local agent settings, test baselines or form submissions.
- Do not deploy. Do not change dependencies without approval. Dependency changes follow `docs/dependency-policy.md`.
- Run the handoff check yourself before you finish. Run Pint on changed PHP files.

## UI and Layout Changes

Purely presentational UI changes must not result in new or modified automated
tests unless the user explicitly requests tests. This exception overrides any
general TDD instruction for changes limited to copy, styling, colors, spacing,
typography, responsive layout, or visual arrangement.

Continue to validate UI changes in the browser and run relevant formatting,
linting, and build checks. If a UI task changes application behavior—such as
forms, navigation, permissions, state, data handling, or interactions—use the
normal testing guidance for that behavioral change.
