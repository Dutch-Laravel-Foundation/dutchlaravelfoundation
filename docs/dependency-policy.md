# Dependency policy

Applies to Composer and JavaScript dependencies, direct and transitive.

## Routine updates wait seven days

- Composer: `vet.json` sets `minimum-release-age` to 7 days. The Laravel Vet plugin (PHP 8.4+) then skips younger releases during `composer update`.
- npm: `.npmrc` sets `min-release-age=7` (npm 11.19+) and `ignore-scripts=true`.
- Bun: `bunfig.toml` sets `minimumReleaseAge` to 604800 seconds.
- `composer.lock` and `package-lock.json` are the source of truth. Do not add a Bun lockfile.
- Update one package at a time (`composer update vendor/package -W`, `npm install package@version`) and review the full lock diff.

## Security fixes skip the wait

A fix for a known CVE ships as soon as it is reviewed. In the PR, link the advisory (CVE, GHSA or Packagist) and name the fixed version. Bypass the age hold for that one package only: `minimum-release-age-exclude` in `vet.json`, `minimumReleaseAgeExcludes` in `bunfig.toml`, or `npm install --min-release-age-exclude=package`. Remove the exclusion in the same PR or once the release is seven days old. Never set the age to zero.

## Audits

`composer audit:dependencies` runs `composer audit --locked` and `npm audit --package-lock-only`. CI runs it in the `quality` job. Any advisory fails the job: fix it or escalate. Do not suppress it.

## Why Vet only holds back releases

Vet can also keep a hash inventory of every installed package and fail each install that does not match it. We do not use that part: it turns every routine bump into a manual trust-recording step. Vet stays for its Composer release-age hold. Add an inventory (`vendor/bin/vet --init`) later if the team wants per-package source review.
