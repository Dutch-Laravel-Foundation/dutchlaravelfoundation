# Production deployment

The `Deploy production` workflow calls the existing `composer deploy` / Envoy
recipe after a pull request is merged into `main`. Closing an unmerged PR or
pushing directly to `main` (including Statamic content commits) does not deploy.

**Deployment is disabled by default.** The job requires the repository variable
`PRODUCTION_DEPLOY_ENABLED` to equal the exact string `true`. A missing variable,
`false`, or any other value skips the job, including manual runs. Leave it unset
until the SSH setup is complete and the owner confirms deployment can be enabled.
Merging this PR alone does not enable deployment.

## GitHub configuration

Create an environment named `production` under **Settings → Environments**.
Restrict its deployment branches to `main`. Required reviewers are optional;
leave them off if merged PRs should deploy without an additional approval.

Add these **environment secrets**, not configuration variables. Do not paste
their values into a PR, issue, workflow file, or repository documentation.

| Secret | Value |
| --- | --- |
| `DEPLOY_HOST` | VPS hostname or IP that the GitHub-hosted runner can reach. |
| `DEPLOY_USER` | SSH account that can run the existing deployment recipe. |
| `DEPLOY_PORT` | SSH port, including `22` when using the default. |
| `DEPLOY_PATH` | Absolute existing deployment base directory containing `current`, `.env`, `forms`, and `users`. This is not the `current` directory itself. |
| `DEPLOY_SSH_PRIVATE_KEY` | Complete dedicated private key, including its opening and closing lines. |
| `DEPLOY_SSH_KNOWN_HOSTS` | Verified OpenSSH known-hosts entry for `DEPLOY_HOST` and `DEPLOY_PORT`. |

The workflow writes a private SSH configuration using the fixed alias
`dlf-production`. Host, account, port, path, key, and known-hosts values all come
from secrets. GitHub masks recognized secret values in logs; transformed values
are not guaranteed to be masked. The workflow does not print the SSH config or
upload deployment logs as artifacts. Keep debug tracing (`set -x`) disabled.

Actions are pinned to full commit SHAs and the token has only `contents: read`.
The privileged `pull_request_target` job checks out `main`, never a PR head or a
fork. Protect changes to `.github/`, `Envoy.blade.php`, and Composer dependencies
with review. Anyone who can change code executed by this job can use its secrets.
Require passing PR checks before merge; deployment is not a substitute for CI.

## VPS configuration

There are two separate SSH connections:

1. **GitHub Actions → VPS:** the new key described below.
2. **VPS → GitHub:** the existing repository deploy key used to clone/fetch code
   and, where required, push Statamic edits. Keep that key on the VPS. A deploy
   key listed in GitHub does not itself grant access to the VPS.

Generate a dedicated Ed25519 key on a trusted machine. The private key must be
unencrypted for unattended use; limit this key to this deployment account.

```sh
ssh-keygen -t ed25519 -C 'dlf-github-actions' -f ./dlf-actions -N ''
```

Add the contents of `dlf-actions.pub` to the deployment account's
`~/.ssh/authorized_keys` on the VPS, prefixed with `restrict`:

```text
restrict ssh-ed25519 <public-key-data> dlf-github-actions
```

Keep `~/.ssh` mode `700` and `authorized_keys` mode `600`, owned by that account.
Store the private file as `DEPLOY_SSH_PRIVATE_KEY`; do not copy it onto the VPS
or commit it. Keep or remove the local copy according to your key-management
policy after configuring the secret.

Use the existing deployment account or an account with the equivalent site-only
permissions. It must write releases and the `current` symlink, read the shared
environment file, manage the site's PM2 process, and access the site's PHP-FPM
socket for `cachetool`. It needs the same PHP, Composer, Node/npm, Git, curl,
PM2, and cachetool setup as a successful manual deployment. It does not need
unrestricted sudo. The key's `restrict` option disables forwarding and PTYs but
still allows shell commands; it is not a deployment-only forced command.

Verify the server's SSH host key through an already trusted connection or the
VPS console. For example, read the public host key and its fingerprint there:

```sh
cat /etc/ssh/ssh_host_ed25519_key.pub
ssh-keygen -lf /etc/ssh/ssh_host_ed25519_key.pub
```

Format the known-hosts secret as `HOST ssh-ed25519 PUBLIC_KEY_DATA` for port 22,
or `[HOST]:PORT ssh-ed25519 PUBLIC_KEY_DATA` for a non-default port. Substitute
the exact host used in `DEPLOY_HOST`. You may use `ssh-keyscan` to obtain an entry,
but verify its fingerprint against the trusted connection before using it. The
workflow uses `StrictHostKeyChecking yes`; it never accepts a new key blindly.

The VPS must be reachable from the GitHub-hosted runner. An SSH alias, VPN, or
jump-host configuration on a developer's Mac is not available in Actions. This
workflow supports a directly reachable SSH endpoint. If production remains
VPN-only, keep deployment disabled until an explicit runner-to-VPN/jump-host
connection is added; do not simply copy the Mac's alias into `DEPLOY_HOST`.

Verify the VPS can fetch this repository without agent forwarding. Run a
read-only `git ls-remote` as the deployment account using the existing VPS key:

```sh
git ls-remote git@github.com:Dutch-Laravel-Foundation/dutchlaravelfoundation.git refs/heads/main
```

The workflow never forwards its SSH agent. The existing production layout and
manual deployment must work before enabling this workflow. It does not provision
the VPS, migrate an old layout, or install server dependencies.

## Revisions, content, and retries

Each attempt fetches current `main` and installs deployment dependencies from a
fresh checkout at that exact revision. That includes Statamic commits made after
the PR was opened. The selected revision must contain the merged PR commit;
rewriting `main` to exclude it stops the deployment.

Envoy retains its checks for dirty or unpushed production edits and for `main`
advancing before activation. The two stale-revision checks exit with code `75`.
Only after removing the unused candidate release and successfully releasing the
deployment lock do they emit `DLF_DEPLOY_RETRY_MAIN_ADVANCED`. The driver requires
both that exit code and the marker to retry, up to **three total attempts**, with
ten seconds between attempts. Each retry fetches a new revision and recipe.

SSH, dependency, build, health-check, dirty-working-tree, unpushed-edit, and lock
failures are not retried automatically. Existing rollback behavior remains in
place. Avoid editing content during the final release switch; the deployment
lock serializes deployments, not Statamic editors or queued content writes.
Continuous commits can exhaust the retry limit; investigate and rerun later.

GitHub serializes runs in the `production-deployment` concurrency group without
canceling the active run. GitHub may replace an older pending run with a newer
one; the next run still deploys latest `main`, not an outdated queued revision.

## Enable and verify

1. Merge the PR while `PRODUCTION_DEPLOY_ENABLED` is unset or `false`.
2. Configure the VPS access, environment restrictions, and all six secrets.
3. After the owner confirms setup is complete, create the **repository-level**
   Actions variable `PRODUCTION_DEPLOY_ENABLED` with value `true` under
   **Settings → Secrets and variables → Actions → Variables**. This flag is
   intentionally not an environment variable: the job checks it before starting.
4. In **Actions → Deploy production → Run workflow**, select `main` and run it.
   This is a real production deployment, not a dry run. Check its result and the
   site. Subsequent PR merges deploy automatically; direct pushes do not.

To pause deployment, set the repository variable to `false`. That prevents new
jobs from starting but does not stop a deployment already running. Missing
secrets after enabling fail the job with the missing secret's name, not its value.
Rotate access by replacing the private-key secret and the matching authorized
public key together.
