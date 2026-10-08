# Laravel Boost Guidelines

## Foundational Context

This is a Laravel application on PHP 8.5. Use the APIs that match the installed major version of each package. Check a PHP package with `composer show <vendor/package>` and JS packages in `package.json`.

## Skills Activation

Domain skills live in `**/skills/**`. Activate the relevant skill when you work in its domain.

## Conventions

- Follow the existing conventions. Check sibling files for structure, approach, and naming.
- Use descriptive names, such as `isRegisteredForDiscounts`, not `discount()`.
- Reuse existing components before you write a new one.
- Prove behavior with tests, not with verification scripts or tinker.
- Keep the directory structure. Do not add base folders or change dependencies without approval.
- If a frontend change does not show, run `bun run build`.
- Be concise in replies.
