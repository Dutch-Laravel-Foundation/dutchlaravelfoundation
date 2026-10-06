# Dutch Laravel Foundation website

## Installation

...

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
