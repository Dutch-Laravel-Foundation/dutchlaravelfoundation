# DLF dependency trust and update policy

This policy applies to **all Composer and JavaScript dependencies**, including
transitive and development packages, Composer plugins, and install scripts.
An advisory audit is not a source-code trust review; both are required.

## Routine changes: wait seven days

- Composer: `laravel/vet` **0.2.1** is pinned and explicitly allowed as a plugin.
  Its published requirements are PHP `^8.4` and Composer plugin API `^2.0`;
  it works on the project's PHP 8.5 and requires DOM, mbstring, OpenSSL, Phar,
  tokenizer and zip. Do not substitute an incompatible Vet release or ignore
  platform requirements. `vet.json` sets `minimum-release-age` to **7 days**.
  Vet filters update candidates and rejects untrusted or too-recent installed
  packages. Vet itself is exempt from its own age filter: manually verify its
  publication date and source before changing the exact pin.
- Bun 1.4.2: `bunfig.toml` sets `minimumReleaseAge = 604800` (**seconds**),
  with no exclusions. Bun holds newly resolved direct and transitive releases.
- npm **11.19.0+**: `.npmrc` sets `min-release-age=7` (**days**) and disables
  install scripts. CI selects 11.19.0 explicitly. Older npm versions may silently
  ignore this setting and must not be used to resolve updates.
- `composer.lock` and `package-lock.json` are authoritative. Use targeted
  `composer update vendor/package --with-dependencies --prefer-dist` and
  `npm install package@version --package-lock-only --ignore-scripts`. Review the
  entire lock delta. Do not introduce a competing Bun lockfile. Bun remains the
  test/build runner; exploratory Bun resolution must not replace the npm lock.
- Composer defaults to `preferred-install=dist`; keep installs and updates in
  dist mode, including local review and CI. Vet's inventory fingerprints the
  installed files, so source checkouts (including `.git` metadata) do not match
  reviewed dist archives. Do not override this with `--prefer-source`, record
  checkout hashes, or reinitialize trust to hide a mismatch. If Composer falls
  back to source, restore the reviewed dist installation before rerunning Vet;
  leave trust checks fail-closed and review any actual archive changes.
- Age settings are not a retroactive JS lockfile audit. Review publication times
  of every changed JS version (for example `npm view package time --json`).
  Verify unknown/missing timestamps, dev branches, git/path dependencies and
  Composer locked/fixed candidates manually; never treat missing dates as safe.
  Routine changes need a verifiable release timestamp at least seven days old.

## Explicit trust review

Before accepting any change, record in the PR the package/version, release date,
upstream release and source links, maintainer/provenance changes, and an assessment
of the complete source delta. Inspect new transitive packages, network/filesystem
access, obfuscated/bundled artifacts, lifecycle scripts and Composer plugins.
Do not automatically enable plugins, add `trustedDependencies`, permit install
scripts or accept every package just to make a check pass. A reviewer must approve
trust independently; an agent's PASS or a clean vulnerability audit is insufficient.

Use `vendor/bin/vet -v` (or `vendor/bin/vet vendor/package`) to inspect PHP changes,
then interactively record only reviewed packages. Commit the corresponding
`vet.json` entries with the lock change. CI runs Vet non-interactively and fails
on unknown/changed bytes. Never run `--fresh` or `--init` to bless an update.
Opaque artifacts and warnings require explicit reviewer assessment.

The initial `vet.json` is a **bootstrap inventory of the existing lock**, not a
claim that every historical source file has been manually reviewed. It establishes
hashes for future deltas. Vet was added without upgrading unrelated packages;
`nckrtl/laravel-toolbar` was rolled back from 0.3.8 (published 2026-10-03) to
0.3.6 (2026-09-15) because the initial age gate rejected 0.3.8. Reviewer acceptance
of this bootstrap and the toolbar rollback, including its opaque built JS assets,
is required. Do not keep a permanent exception for the newer toolbar.

## Urgent CVE fixes: narrow temporary bypass

A security fix must not wait seven days when delay leaves DLF exposed. The PR must
include the CVE/GHSA/Packagist advisory URL and ID, affected installed version,
fixed version and upstream patch/release evidence, exposure/urgency rationale,
reviewer/owner, exact exception packages and versions, and an expiry/removal date
(no later than the fixed release's seven-day birthday).

1. Independently verify the advisory and patch. Determine the smallest targeted
   direct/transitive update; do not run blanket update or `audit fix --force`.
2. Temporarily add **exact package names, no globs** to Vet's
   `minimum-release-age-exclude`, Bun's `minimumReleaseAgeExcludes`, or use npm's
   `--min-release-age-exclude=package` for this one resolution. Pin the intended
   fixed version during resolution; name-only exclusions otherwise admit other
   versions. Never set the global age to zero, turn off Vet/plugins, or add a
   permanent advisory ignore. Extend the exception only with renewed evidence.
3. Complete the same explicit trust review, including the patch and every
   transitive delta; record reviewed Vet hashes. Restore constraints if temporarily
   pinned, without re-resolving unrelated dependencies.
4. Run all audits and full checks below. Commit a tracked Vet age exception only
   if needed for its installed-version gate, with the evidence/expiry in the PR.
   Remove it and rerun gates once the release is seven days old. Bun/npm resolution
   exceptions should be removed immediately after producing the fixed lock.
5. An unrelated unresolved advisory fails the gate: fix it or escalate to the
   security owner. No silent broad suppression or skipped checks.

## Required checks and evidence

```sh
composer audit --locked --no-interaction
vendor/bin/vet --no-interaction
npm audit --package-lock-only --ignore-scripts
composer quality
vendor/bin/pest --compact && bun run test && bun run typecheck
```

`composer audit:dependencies` groups the first three checks and is included in
`composer quality` and CI. Audits include dev/transitive dependencies and fail on
any advisory; don't downgrade them to production-only or high-severity-only.
`bun audit` can additionally inspect the JS graph, but npm's authoritative lock
audit is the required gate. `composer quality` also runs Pint, PHPStan, **full Pest
with `--no-tia`**, JS tests, typecheck and client/SSR builds. Attach actual output
and platform versions to dependency PRs; disclose missing access rather than
claiming skipped checks passed.

References: [Vet installation and review](https://github.com/laravel/vet/tree/v0.2.1),
[Bun release age](https://bun.sh/docs/pm/cli/install),
[npm install configuration](https://docs.npmjs.com/cli/v11/commands/npm-install).
