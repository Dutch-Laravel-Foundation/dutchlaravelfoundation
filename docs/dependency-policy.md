# Dependency policy

Applies to Composer and JavaScript dependencies, direct and transitive.

## Routine updates wait seven days

- Composer: `vet.json` sets `minimum-release-age` to 7 days. The Laravel Vet plugin (PHP 8.4+) then skips younger releases during `composer update`.
- npm: `.npmrc` sets `min-release-age=7` (npm 11.19+) and `ignore-scripts=true`.
- Bun: `bunfig.toml` sets `minimumReleaseAge` to 604800 seconds.
- `composer.lock` and `package-lock.json` are the source of truth. Do not add a Bun lockfile.
- Update one package at a time (`composer update vendor/package -W`, `npm install package@version`) and review the full lock diff.

## Security fixes skip the wait

Security updates are always allowed. The age hold never delays a fix for a known CVE.

- Dependabot security updates are on for the repository. For each advisory, Dependabot opens a pull request with the fixed version. It does not apply our age hold. Review and merge it like any other dependency change; CI runs the strict audit and the full test suite on it.
- To ship a fix before Dependabot does, update only the affected package and skip the hold for that one run: `composer update vendor/package --no-plugins` (check that the lock diff touches only that package), or `npm install package@version --min-release-age-exclude=package`. A newer lock installs normally with Vet enabled.
- In the PR, link the advisory (CVE, GHSA or Packagist) and name the fixed version. Do not add a permanent exclusion, and never set the age to zero.

## Audits

`composer audit:dependencies` runs `composer audit --locked` and `npm audit --package-lock-only`. CI runs it in the `audit` job. When a pull request changes `composer.json`, `composer.lock`, `package.json` or `package-lock.json`, any advisory fails the job. Otherwise an advisory only adds a warning, so a new advisory does not block unrelated work. Fix it in a separate pull request or escalate. Do not suppress it.

## Why Vet only holds back releases

Vet can also keep a hash inventory of every installed package and fail each install that does not match it. We do not use that part: it turns every routine bump into a manual trust-recording step. Vet stays for its Composer release-age hold. Add an inventory (`vendor/bin/vet --init`) later if the team wants per-package source review.
