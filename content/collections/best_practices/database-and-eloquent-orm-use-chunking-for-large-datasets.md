---
id: f98718e0-c3ab-59da-9a37-489837b76d48
blueprint: best_practices
title: 'Gebruik Chunking voor Grote Datasets'
title_nl: 'Gebruik Chunking voor Grote Datasets'
title_en: 'Use Chunking for Large Datasets'
summary_nl: 'Duizenden records tegelijk in het geheugen laden kan leiden tot geheugenuitputting en trage responstijden. Laravel biedt verschillende chunking- en lazy collection-strategieën — chunk(), chunkById(), cursor(), lazy() en lazyById() — die elk...'
summary_en: 'Loading thousands of records into memory at once can cause memory exhaustion and slow response times. Laravel provides several chunking and lazy collection strategies — chunk(), chunkById(), cursor(), lazy(), and lazyById() — each suited to...'
chapters_nl:
  - title: Introductie
    anchor: introductie
  - title: Waarom
    anchor: waarom
  - title: 'Geschikt Voor'
    anchor: geschikt-voor
  - title: 'Minder Geschikt'
    anchor: minder-geschikt
  - title: Voorbeelden
    anchor: voorbeelden
  - title: 'Meer Informatie'
    anchor: meer-informatie
chapters_en:
  - title: Introduction
    anchor: introduction
  - title: Why
    anchor: why
  - title: 'Suitable For'
    anchor: suitable-for
  - title: 'Less Suitable'
    anchor: less-suitable
  - title: Examples
    anchor: examples
  - title: 'More Info'
    anchor: more-info
content_nl: |-
  <a name="introduction"></a>
  ## Introductie

  Duizenden records tegelijk in het geheugen laden kan leiden tot geheugenuitputting en trage responstijden. Laravel biedt verschillende chunking- en lazy collection-strategieën — `chunk()`, `chunkById()`, `cursor()`, `lazy()` en `lazyById()` — die elk geschikt zijn voor andere scenario's, afhankelijk van of je relaties nodig hebt, records aan het wijzigen bent, of geheugenefficiëntie prioriteit geeft.

  <a name="why"></a>
  ## Waarom

  - **Voorkomt geheugenuitputting**: Records in kleinere batches verwerken houdt het geheugengebruik voorspelbaar
  - **Veilig tijdens mutaties**: `chunkById()` en `lazyById()` gebruiken `id > last_id` in plaats van `OFFSET`, wat overgeslagen of gedupliceerde records voorkomt wanneer je data wijzigt tijdens het itereren
  - **Geheugenefficiënt lezen**: `cursor()` houdt via een PHP-generator slechts één model tegelijk in het geheugen
  - **Ondersteuning voor relaties**: `lazy()` ondersteunt eager loading terwijl er toch gechunkt wordt, in tegenstelling tot `cursor()`

  <a name="suitable-for"></a>
  ## Geschikt Voor

  - Batchverwerking van grote datasets (imports, exports, notificaties)
  - Geplande commands die veel records verwerken
  - Rapporten of datatransformaties op grote tabellen
  - Elke operatie die over meer dan een paar honderd records itereert

  <a name="less-suitable"></a>
  ## Minder Geschikt

  - Kleine datasets waarbij het prima is om alle records tegelijk te laden
  - Queries die van nature een beperkt aantal records teruggeven

  <a name="examples"></a>
  ## Voorbeelden

  ### Basis Chunking

  ```php
  // Bad: loads everything into memory
  $users = User::all();
  foreach ($users as $user) {
      $user->notify(new WeeklyDigest);
  }

  // Good: processes in batches of 200
  User::where('subscribed', true)->chunk(200, function ($users) {
      foreach ($users as $user) {
          $user->notify(new WeeklyDigest);
      }
  });
  ```

  ### Gebruik `chunkById()` Wanneer je Records Wijzigt

  Standaard `chunk()` gebruikt `OFFSET`, wat verschuift wanneer rijen veranderen. `chunkById()` gebruikt `id > last_id`, wat veilig is tegen mutatie:

  ```php
  User::where('active', false)->chunkById(200, function ($users) {
      $users->each->delete();
  });
  ```

  ### Kiezen Tussen `cursor()` en `lazy()`

  - `cursor()` — één model tegelijk in het geheugen, maar kan geen relaties eager-loaden (risico op N+1)
  - `lazy()` — gechunkte paginatie die een platte `LazyCollection` teruggeeft, ondersteunt eager loading

  ```php
  // Good: attribute-only work, maximum memory efficiency
  foreach (User::where('active', true)->cursor() as $user) {
      ProcessUser::dispatch($user->id);
  }

  // Good: when you need relationships
  foreach (User::with('roles')->lazy() as $user) {
      echo $user->roles->count();
  }
  ```

  ### Gebruik `lazyById()` Wanneer je Wijzigt Tijdens het Itereren

  `lazy()` gebruikt offset-paginatie — het wijzigen van records tijdens het itereren kan ze overslaan of dubbel verwerken. `lazyById()` gebruikt `id > last_id`, veilig tegen mutatie:

  ```php
  User::where('needs_update', true)->lazyById()->each(function ($user) {
      $user->update(['needs_update' => false]);
  });
  ```

  <a name="more-info"></a>
  ## Meer Informatie

  - [Laravel Chunking Results Documentation](https://laravel.com/docs/eloquent#chunking-results)
  - [Laravel Lazy Collections Documentation](https://laravel.com/docs/eloquent#lazy-collection)
  - [Prevent N+1 Queries](../../prevent-n-plus-one-queries/translations/nl.md) — voor eager loading en query-optimalisatie
  - [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)
content_en: |-
  <a name="introduction"></a>
  ## Introduction

  Loading thousands of records into memory at once can cause memory exhaustion and slow response times. Laravel provides several chunking and lazy collection strategies — `chunk()`, `chunkById()`, `cursor()`, `lazy()`, and `lazyById()` — each suited to different scenarios depending on whether you need relationships, are modifying records, or prioritize memory efficiency.

  <a name="why"></a>
  ## Why

  - **Prevents memory exhaustion**: Processing records in smaller batches keeps memory usage predictable
  - **Safe during mutations**: `chunkById()` and `lazyById()` use `id > last_id` instead of `OFFSET`, preventing skipped or duplicated records when modifying data during iteration
  - **Memory-efficient reads**: `cursor()` holds only one model in memory at a time via a PHP generator
  - **Relationship support**: `lazy()` supports eager loading while still chunking, unlike `cursor()`

  <a name="suitable-for"></a>
  ## Suitable For

  - Batch processing large datasets (imports, exports, notifications)
  - Scheduled commands processing many records
  - Reports or data transformations on large tables
  - Any operation iterating over more than a few hundred records

  <a name="less-suitable"></a>
  ## Less Suitable

  - Small datasets where loading all records at once is fine
  - Queries that return a limited number of records by design

  <a name="examples"></a>
  ## Examples

  ### Basic Chunking

  ```php
  // Bad: loads everything into memory
  $users = User::all();
  foreach ($users as $user) {
      $user->notify(new WeeklyDigest);
  }

  // Good: processes in batches of 200
  User::where('subscribed', true)->chunk(200, function ($users) {
      foreach ($users as $user) {
          $user->notify(new WeeklyDigest);
      }
  });
  ```

  ### Use `chunkById()` When Modifying Records

  Standard `chunk()` uses `OFFSET` which shifts when rows change. `chunkById()` uses `id > last_id`, which is safe against mutation:

  ```php
  User::where('active', false)->chunkById(200, function ($users) {
      $users->each->delete();
  });
  ```

  ### Choosing Between `cursor()` and `lazy()`

  - `cursor()` — one model in memory at a time, but cannot eager-load relationships (N+1 risk)
  - `lazy()` — chunked pagination returning a flat `LazyCollection`, supports eager loading

  ```php
  // Good: attribute-only work, maximum memory efficiency
  foreach (User::where('active', true)->cursor() as $user) {
      ProcessUser::dispatch($user->id);
  }

  // Good: when you need relationships
  foreach (User::with('roles')->lazy() as $user) {
      echo $user->roles->count();
  }
  ```

  ### Use `lazyById()` When Updating During Iteration

  `lazy()` uses offset pagination — updating records during iteration can skip or double-process. `lazyById()` uses `id > last_id`, safe against mutation:

  ```php
  User::where('needs_update', true)->lazyById()->each(function ($user) {
      $user->update(['needs_update' => false]);
  });
  ```

  <a name="more-info"></a>
  ## More Info

  - [Laravel Chunking Results Documentation](https://laravel.com/docs/eloquent#chunking-results)
  - [Laravel Lazy Collections Documentation](https://laravel.com/docs/eloquent#lazy-collection)
  - [Prevent N+1 Queries](../prevent-n-plus-one-queries/BEST_PRACTICE.md) — for eager loading and query optimization
  - [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)
best_practice_categories:
  - database-and-eloquent-orm
category_slug: database-and-eloquent-orm
category_title: 'Database en Eloquent ORM'
category_title_en: 'Database & Eloquent ORM'
source_path: database-and-eloquent-orm/use-chunking-for-large-datasets/BEST_PRACTICE.md
source_sha: c7034be11f69954eac43e50486b6e5bebde98c46
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/database-and-eloquent-orm/use-chunking-for-large-datasets/BEST_PRACTICE.md'
has_skill: true
skill_content: |-
  Use this skill when a Laravel task touches this best practice. It is self-contained so it can be installed independently by Laravel Boost or another agent-skill system.

  ## Core Guidance

  Loading thousands of records into memory at once can cause memory exhaustion and slow response times. Laravel provides several chunking and lazy collection strategies — `chunk()`, `chunkById()`, `cursor()`, `lazy()`, and `lazyById()` — each suited to different scenarios depending on whether you need relationships, are modifying records, or prioritize memory efficiency.

  ## Why It Matters

  - **Prevents memory exhaustion**: Processing records in smaller batches keeps memory usage predictable
  - **Safe during mutations**: `chunkById()` and `lazyById()` use `id > last_id` instead of `OFFSET`, preventing skipped or duplicated records when modifying data during iteration
  - **Memory-efficient reads**: `cursor()` holds only one model in memory at a time via a PHP generator
  - **Relationship support**: `lazy()` supports eager loading while still chunking, unlike `cursor()`

  ## Apply When

  - Batch processing large datasets (imports, exports, notifications)
  - Scheduled commands processing many records
  - Reports or data transformations on large tables
  - Any operation iterating over more than a few hundred records

  ## Be Careful When

  - Small datasets where loading all records at once is fine
  - Queries that return a limited number of records by design

  ## Canonical Source

  - Full best practice: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/database-and-eloquent-orm/use-chunking-for-large-datasets/BEST_PRACTICE.md
  - Dutch translation: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/database-and-eloquent-orm/use-chunking-for-large-datasets/translations/nl.md

  ## Workflow

  1. Inspect the user's Laravel code before recommending changes.
  2. Identify the narrow rule from this best practice that applies to the task.
  3. Prefer Laravel's built-in conventions and documented APIs over custom abstractions.
  4. Keep examples focused on this practice; reference other skills or practices when the task crosses boundaries.
  5. Verify code changes with the project's available tests, linters, static analysis, or framework checks.

  ## Review Checklist

  - The recommendation is Laravel-specific and grounded in this practice.
  - Code examples use realistic Laravel file names, class names, and method names.
  - The advice avoids mixing unrelated architecture, deployment, security, or testing topics.
  - Related practices are mentioned when useful, but not re-explained in full.
  - Dutch output, when requested, keeps framework and API names intact.
skill_source_path: database-and-eloquent-orm/use-chunking-for-large-datasets/skill/SKILL.md
skill_github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/database-and-eloquent-orm/use-chunking-for-large-datasets/skill/SKILL.md'
skill_references: []
---
