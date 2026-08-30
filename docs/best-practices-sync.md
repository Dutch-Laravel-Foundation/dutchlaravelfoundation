# Best Practices Sync

The website imports `Dutch-Laravel-Foundation/best-practices` as generated Statamic content with:

```bash
php artisan best-practices:sync ../best-practices
```

The website workflow `.github/workflows/sync-best-practices.yml` can be started manually or through `repository_dispatch` with event type `best-practices-updated`.

The source repository can dispatch to this website after pushes to `main` with a workflow like this:

```yaml
name: Notify Website

on:
  push:
    branches:
      - main

jobs:
  dispatch:
    runs-on: ubuntu-latest
    steps:
      - name: Dispatch website sync
        uses: peter-evans/repository-dispatch@v3
        with:
          token: ${{ secrets.DLF_WEBSITE_DISPATCH_TOKEN }}
          repository: Dutch-Laravel-Foundation/dutchlaravelfoundation
          event-type: best-practices-updated
          client-payload: '{"sha":"${{ github.sha }}","ref":"${{ github.ref_name }}"}'
```

`DLF_WEBSITE_DISPATCH_TOKEN` must be a fine-grained personal access token or GitHub App token available in the `best-practices` repository. It needs access to `Dutch-Laravel-Foundation/dutchlaravelfoundation` with `Contents: read and write`, which is the GitHub permission required to create a `repository_dispatch` event. Store it as a repository secret in the source `best-practices` repository.

If `best-practices` is private, the website repository also needs `BEST_PRACTICES_REPO_TOKEN` with `Contents: read` access to `Dutch-Laravel-Foundation/best-practices` so the sync workflow can check it out.
