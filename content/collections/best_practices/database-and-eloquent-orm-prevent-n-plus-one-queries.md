---
id: a626898f-5101-508d-ac3f-48f5f2aeb4fb
blueprint: best_practices
title: 'Voorkom N+1 Queries'
title_nl: 'Voorkom N+1 Queries'
title_en: 'Prevent N+1 Queries'
summary_nl: 'Voorkom lazy loading van Eloquent-relaties in loops door gerelateerde data en counts vooraf eager te laden.'
summary_en: 'Prevent Eloquent relationship lazy loading in loops by eager loading related data and counts up front.'
chapters_nl:
  - title: Omschrijving
    anchor: omschrijving
  - title: 'Aanbevolen Situatie'
    anchor: aanbevolen-situatie
  - title: 'Menselijke Begeleiding'
    anchor: menselijke-begeleiding
  - title: 'Boost Guideline'
    anchor: boost-guideline
chapters_en:
  - title: Description
    anchor: description
  - title: 'Recommended Situation'
    anchor: recommended-situation
  - title: 'Human Guidance'
    anchor: human-guidance
  - title: 'Boost Guideline'
    anchor: boost-guideline
content_nl: |-
  <a name="description"></a>
  ## Omschrijving

  Voorkom lazy loading van Eloquent-relaties in loops door gerelateerde data en counts vooraf eager te laden.

  <a name="recommended-situation"></a>
  ## Aanbevolen Situatie

  Gebruik dit wanneer code collecties van modellen weergeeft of verwerkt en gerelateerde Eloquent-data nodig heeft.

  <a name="human-guidance"></a>
  ## Menselijke Begeleiding

  N+1 query-problemen behoren tot de meest voorkomende performanceproblemen in Laravel-applicaties. Ze ontstaan wanneer code een collectie modellen laadt en vervolgens op elk model afzonderlijk een relatie aanspreekt, wat resulteert in één query voor de collectie plus één query per model. Laravel biedt verschillende tools om dit te voorkomen, waaronder eager loading met `with()`, `preventLazyLoading()`, `withCount()` en het selectief laden van kolommen.

  <a name="why"></a>
  ### Waarom

  - **Drastisch minder queries**: Eager loading met `with()` reduceert N+1 queries tot slechts 2 queries in totaal, ongeacht de grootte van de collectie
  - **Vroegtijdige detectie**: Het inschakelen van `preventLazyLoading()` tijdens ontwikkeling vangt N+1 problemen op voordat ze in productie belanden
  - **Efficiënt tellen**: Het gebruik van `withCount()` voorkomt dat volledige relaties worden geladen alleen maar om ze te tellen
  - **Lager geheugengebruik**: Door alleen de benodigde kolommen te selecteren voorkom je dat grote tekst- of JSON-velden onnodig worden geladen

  <a name="suitable-for"></a>
  ### Geschikt Voor

  - Elke applicatie die relaties laadt op collecties van modellen
  - Views of API-responses die gerelateerde modeldata weergeven
  - Applicaties met groeiende datasets waarbij het aantal queries van belang is
  - Projecten met meerdere ontwikkelaars waar N+1 problemen makkelijk worden geïntroduceerd

  <a name="less-suitable"></a>
  ### Minder Geschikt

  - Eenvoudige applicaties met minimale relaties
  - Opzoekacties op een enkel model waarbij slechts één gerelateerde query wordt uitgevoerd

  <a name="examples"></a>
  ### Voorbeelden

  #### Laad Relaties Altijd Eager

  ```php
  // Slecht: N+1 — voert 1 + N queries uit
  $posts = Post::all();
  foreach ($posts as $post) {
      echo $post->author->name;
  }

  // Goed: 2 queries in totaal
  $posts = Post::with('author')->get();
  foreach ($posts as $post) {
      echo $post->author->name;
  }
  ```

  Beperk eager loads zodat ze alleen de benodigde kolommen selecteren (voeg altijd de foreign key toe):

  ```php
  $users = User::with(['posts' => function ($query) {
      $query->select('id', 'user_id', 'title')
            ->where('published', true)
            ->latest()
            ->limit(10);
  }])->get();
  ```

  #### Voorkom Lazy Loading Tijdens Ontwikkeling

  Schakel dit in binnen `AppServiceProvider::boot()` om N+1 problemen tijdens ontwikkeling op te vangen:

  ```php
  public function boot(): void
  {
      Model::preventLazyLoading(! app()->isProduction());
  }
  ```

  Dit gooit een `LazyLoadingViolationException` wanneer een relatie wordt aangesproken zonder eager te zijn geladen.

  #### Gebruik `withCount()` voor het Tellen van Relaties

  ```php
  // Slecht: laadt volledige collecties alleen maar om ze te tellen
  $posts = Post::all();
  foreach ($posts as $post) {
      echo $post->comments->count();
  }

  // Goed: voegt een comments_count-attribuut toe via een enkele subquery
  $posts = Post::withCount('comments')->get();
  foreach ($posts as $post) {
      echo $post->comments_count;
  }
  ```

  #### Selecteer Alleen de Benodigde Kolommen

  ```php
  // Slecht: SELECT * op beide tabellen
  $posts = Post::with('author')->get();

  // Goed: haalt alleen op wat je nodig hebt
  $posts = Post::select('id', 'title', 'user_id', 'created_at')
      ->with(['author:id,name,avatar'])
      ->get();
  ```

  Wanneer je kolommen selecteert op eager-geladen relaties, voeg dan altijd de foreign key-kolom toe, anders matcht de relatie niet.

  <a name="more-info"></a>
  ### Meer Informatie

  - [Laravel Eager Loading Documentatie](https://laravel.com/docs/eloquent-relationships#eager-loading)
  - [Laravel `preventLazyLoading` Documentatie](https://laravel.com/docs/eloquent-relationships#preventing-lazy-loading)
  - [Laravel Counting Related Models](https://laravel.com/docs/eloquent-relationships#counting-related-models)
  - [Gebruik Chunking voor Grote Datasets](../../use-chunking-for-large-datasets/translations/nl.md) — voor het efficiënt verwerken van grote collecties
  - [Gebruik Eloquent Scopes en Casts](../../use-eloquent-scopes-and-casts/translations/nl.md) — voor herbruikbare query-constraints
  - [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)

  <a name="boost-guideline"></a>
  ## Boost Guideline

  ```md
  ---
  title: Prevent N+1 Queries
  description: Prevent Eloquent relationship lazy loading in loops by eager loading related data and counts up front.
  recommended_situation: Use when code renders or processes collections of models and needs related Eloquent data.
  ---

  - Eager load every relationship that will be accessed inside loops, collections, API resources, or views.
  - Use `withCount()` or aggregate queries when only counts or summary values are needed.
  - Select only the columns you need on the root query and eager-loaded relationships, including required foreign keys.
  - Enable `Model::preventLazyLoading(! app()->isProduction())` in development to catch regressions early.
  ```
content_en: |-
  <a name="description"></a>
  ## Description

  Prevent Eloquent relationship lazy loading in loops by eager loading related data and counts up front.

  <a name="recommended-situation"></a>
  ## Recommended Situation

  Use when code renders or processes collections of models and needs related Eloquent data.

  <a name="human-guidance"></a>
  ## Human Guidance

  N+1 query problems are one of the most common performance issues in Laravel applications. They occur when code loads a collection of models and then accesses a relationship on each model individually, resulting in one query for the collection plus one query per model. Laravel provides several tools to prevent this, including eager loading with `with()`, `preventLazyLoading()`, `withCount()`, and selective column loading.

  <a name="why"></a>
  ### Why

  - **Dramatically fewer queries**: Eager loading with `with()` reduces N+1 queries down to just 2 queries total, regardless of collection size
  - **Early detection**: Enabling `preventLazyLoading()` in development catches N+1 issues before they reach production
  - **Efficient counting**: Using `withCount()` avoids loading entire relationships just to count them
  - **Reduced memory usage**: Selecting only needed columns avoids loading large text or JSON fields unnecessarily

  <a name="suitable-for"></a>
  ### Suitable For

  - Any application that loads relationships on collections of models
  - Views or API responses that display related model data
  - Applications with growing datasets where query count matters
  - Projects with multiple developers where N+1 issues are easily introduced

  <a name="less-suitable"></a>
  ### Less Suitable

  - Simple applications with minimal relationships
  - Single-model lookups where only one related query is executed

  <a name="examples"></a>
  ### Examples

  #### Always Eager Load Relationships

  ```php
  // Bad: N+1 — executes 1 + N queries
  $posts = Post::all();
  foreach ($posts as $post) {
      echo $post->author->name;
  }

  // Good: 2 queries total
  $posts = Post::with('author')->get();
  foreach ($posts as $post) {
      echo $post->author->name;
  }
  ```

  Constrain eager loads to select only needed columns (always include the foreign key):

  ```php
  $users = User::with(['posts' => function ($query) {
      $query->select('id', 'user_id', 'title')
            ->where('published', true)
            ->latest()
            ->limit(10);
  }])->get();
  ```

  #### Prevent Lazy Loading in Development

  Enable this in `AppServiceProvider::boot()` to catch N+1 issues during development:

  ```php
  public function boot(): void
  {
      Model::preventLazyLoading(! app()->isProduction());
  }
  ```

  This throws a `LazyLoadingViolationException` when a relationship is accessed without being eager-loaded.

  #### Use `withCount()` for Counting Relations

  ```php
  // Bad: loads entire collections just to count them
  $posts = Post::all();
  foreach ($posts as $post) {
      echo $post->comments->count();
  }

  // Good: adds a comments_count attribute via a single subquery
  $posts = Post::withCount('comments')->get();
  foreach ($posts as $post) {
      echo $post->comments_count;
  }
  ```

  #### Select Only Needed Columns

  ```php
  // Bad: SELECT * on both tables
  $posts = Post::with('author')->get();

  // Good: only fetches what you need
  $posts = Post::select('id', 'title', 'user_id', 'created_at')
      ->with(['author:id,name,avatar'])
      ->get();
  ```

  When selecting columns on eager-loaded relationships, always include the foreign key column or the relationship won't match.

  <a name="more-info"></a>
  ### More Info

  - [Laravel Eager Loading Documentation](https://laravel.com/docs/eloquent-relationships#eager-loading)
  - [Laravel `preventLazyLoading` Documentation](https://laravel.com/docs/eloquent-relationships#preventing-lazy-loading)
  - [Laravel Counting Related Models](https://laravel.com/docs/eloquent-relationships#counting-related-models)
  - [Use Chunking for Large Datasets](../use-chunking-for-large-datasets/BEST_PRACTICE.md) — for processing large collections efficiently
  - [Use Eloquent Scopes and Casts](../use-eloquent-scopes-and-casts/BEST_PRACTICE.md) — for reusable query constraints
  - [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)

  <a name="boost-guideline"></a>
  ## Boost Guideline

  ```md
  ---
  title: Prevent N+1 Queries
  description: Prevent Eloquent relationship lazy loading in loops by eager loading related data and counts up front.
  recommended_situation: Use when code renders or processes collections of models and needs related Eloquent data.
  ---

  - Eager load every relationship that will be accessed inside loops, collections, API resources, or views.
  - Use `withCount()` or aggregate queries when only counts or summary values are needed.
  - Select only the columns you need on the root query and eager-loaded relationships, including required foreign keys.
  - Enable `Model::preventLazyLoading(! app()->isProduction())` in development to catch regressions early.
  ```
best_practice_categories:
  - database-and-eloquent-orm
category_slug: database-and-eloquent-orm
category_title: 'Database en Eloquent ORM'
category_title_en: 'Database & Eloquent ORM'
source_path: database-and-eloquent-orm/prevent-n-plus-one-queries/BEST_PRACTICE.md
source_sha: c7034be11f69954eac43e50486b6e5bebde98c46
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/database-and-eloquent-orm/prevent-n-plus-one-queries/BEST_PRACTICE.md'
has_skill: true
skill_content: |-
  Use this skill when a Laravel task touches this best practice. It is self-contained so it can be installed independently by Laravel Boost or another agent-skill system.

  ## Core Guidance



  ## Why It Matters

  - Apply the best practice consistently and keep the implementation focused.

  ## Apply When

  - Laravel work that directly overlaps with this practice.

  ## Be Careful When

  - Tasks outside this practice; use a more specific skill instead.

  ## Canonical Source

  - Full best practice: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/database-and-eloquent-orm/prevent-n-plus-one-queries/BEST_PRACTICE.md
  - Dutch translation: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/database-and-eloquent-orm/prevent-n-plus-one-queries/translations/nl.md

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
skill_source_path: database-and-eloquent-orm/prevent-n-plus-one-queries/skill/SKILL.md
skill_github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/database-and-eloquent-orm/prevent-n-plus-one-queries/skill/SKILL.md'
skill_references: []
---
