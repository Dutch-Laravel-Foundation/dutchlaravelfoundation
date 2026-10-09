# Dutch Laravel Foundation

The DLF website. Statamic CMS (flat-file content) with a React 19 / Inertia v3 public site and SSR. PHP 8.5, Laravel 13, Tailwind v4. Composer and npm lockfiles; Bun runs scripts and tests.

- For public frontend work, read `.agents/skills/building-dlf-pages/SKILL.md` first. Keep its page-family and divider contracts.
- Use the Boost `search-docs` MCP tool before you rely on an unfamiliar Laravel, Inertia or Statamic API.

## Traps

- This is not an Eloquent CMS. Content lives in `content/`, with its schema in `resources/blueprints` and `resources/fieldsets`. `app/Content` repositories read Statamic entries and map them to Spatie Data DTOs in `app/Data`. Extend the matching repository and mapper. Do not add Eloquent models or hardcode content in React.
- GraphQL field selections must match the entry blueprint. When you change authored fields, check the blueprint and the existing content.
- TypeScript types in `resources/js/generated` are generated. Run `bun run types` after a DTO change. Never edit them by hand.
- Forms and submissions belong to Statamic. `storage/forms` links to the local `storage/form-submissions`. Tests use only disposable submissions.
- Statamic static caching is removed from `statamic.web` in `AppServiceProvider` on purpose, so it cannot bypass the response cache. Do not restore it. Use the existing invalidation listener when authored content changes.
- The persistent layout owns the navigation, footer and footer CTA. Do not add a second page shell.
- Use `php please` for Statamic commands and `php artisan make:...` for Laravel classes. Pass `--no-interaction`.

## Commands

| Intent | Command |
| --- | --- |
| Build client + SSR | `bun run build` |
| Tests (TIA) | `vendor/bin/pest --compact` (add a path or `--filter`) |
| Handoff check | `vendor/bin/pest --compact && bun run test && bun run typecheck` |
| Full gate | `composer check` (Pint, PHPStan, Bun tests, typecheck, build, full Pest) |
| Format PHP | `vendor/bin/pint --dirty` |

Feature tests need a build (`public/build/manifest.json`). Fix new PHPStan errors; do not add them to `phpstan-baseline.neon`.

## Boundaries

- Orbit and other managed environments already serve the app at `APP_URL`. Use that URL for browser review. Do not start a second server.
- Never point tests or scripts at production data, real form submissions or shared storage.
- Never commit credentials, `.env`, local agent settings, test baselines or form submissions.
- Do not deploy. Do not change dependencies without approval.
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
