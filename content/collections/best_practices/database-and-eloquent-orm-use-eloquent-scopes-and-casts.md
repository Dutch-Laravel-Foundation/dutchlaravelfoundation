---
id: 6dbb433c-925e-5b26-a068-19240d35bdd1
blueprint: best_practices
title: 'Gebruik Eloquent scopes en casts'
title_nl: 'Gebruik Eloquent scopes en casts'
title_en: 'Use Eloquent Scopes and Casts'
summary_nl: 'Eloquent biedt local scopes voor herbruikbare query-constraints en attribute casts voor automatische typeconversie. Door deze functies te gebruiken houd je querylogica DRY, zorg je voor consistente datatypes en maak je code expressiever. In...'
summary_en: 'Eloquent provides local scopes for reusable query constraints and attribute casts for automatic type conversion. Using these features keeps query logic DRY, ensures consistent data types, and makes code more expressive. Combined with helper...'
chapters_nl:
  - title: Introductie
    anchor: introductie
  - title: Waarom
    anchor: waarom
  - title: 'Geschikt voor'
    anchor: geschikt-voor
  - title: 'Minder geschikt'
    anchor: minder-geschikt
  - title: Voorbeelden
    anchor: voorbeelden
  - title: 'Meer info'
    anchor: meer-info
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

  Eloquent biedt local scopes voor herbruikbare query-constraints en attribute casts voor automatische typeconversie. Door deze functies te gebruiken houd je querylogica DRY, zorg je voor consistente datatypes en maak je code expressiever. In combinatie met helpers zoals `whereBelongsTo()` maken ze Eloquent-queries overzichtelijker en minder foutgevoelig.

  <a name="why"></a>
  ## Waarom

  - **DRY queries**: Local scopes halen herbruikbare query-constraints eruit, waardoor gedupliceerde `where`-clausules door de hele codebase worden voorkomen
  - **Typeveiligheid**: Attribute casts converteren databasewaarden automatisch naar de juiste PHP-types (booleans, arrays, datums, decimalen)
  - **Overzichtelijkere queries**: `whereBelongsTo()` maakt hardgecodeerde foreign key-verwijzingen overbodig, waardoor relatiequeries beter leesbaar worden
  - **Betere templates**: Door datumkolommen te casten kun je Carbon-methoden direct in Blade-templates gebruiken in plaats van strings handmatig te parsen

  <a name="suitable-for"></a>
  ## Geschikt voor

  - Elk model met herhaalde querypatronen (actieve gebruikers, gepubliceerde posts, enz.)
  - Modellen met JSON-, boolean-, decimal- of datumkolommen
  - Applicaties die Blade of API-responses gebruiken die datums formatteren

  <a name="less-suitable"></a>
  ## Minder geschikt

  - Eenmalige queries die nergens worden hergebruikt
  - Global scopes moeten spaarzaam worden gebruikt — geef voor de meeste filterbehoeften de voorkeur aan local scopes

  <a name="examples"></a>
  ## Voorbeelden

  ### Local scopes

  ```php
  // Bad: duplicated query logic
  $active = User::where('verified', true)->whereNotNull('activated_at')->get();
  $articles = Article::whereHas('user', function ($q) {
      $q->where('verified', true)->whereNotNull('activated_at');
  })->get();

  // Good: reusable local scope
  public function scopeActive(Builder $query): Builder
  {
      return $query->where('verified', true)->whereNotNull('activated_at');
  }

  $active = User::active()->get();
  $articles = Article::whereHas('user', fn ($q) => $q->active())->get();
  ```

  ### Global scopes — spaarzaam gebruiken

  Global scopes passen elke query op het model stilzwijgend aan, wat debuggen bemoeilijkt. Reserveer ze voor werkelijk universele constraints zoals soft deletes of multi-tenancy. Geef voor al het andere de voorkeur aan local scopes.

  ### Attribute casts

  Gebruik de `casts()`-methode voor automatische typeconversie:

  ```php
  protected function casts(): array
  {
      return [
          'is_active' => 'boolean',
          'metadata' => 'array',
          'total' => 'decimal:2',
      ];
  }
  ```

  ### Cast datumkolommen correct

  ```php
  // Bad: manual date parsing in templates
  {{ Carbon::createFromFormat('Y-d-m H-i', $order->ordered_at)->toDateString() }}

  // Good: cast in the model
  protected function casts(): array
  {
      return [
          'ordered_at' => 'datetime',
      ];
  }

  // Then use directly in Blade
  {{ $order->ordered_at->toDateString() }}
  {{ $order->ordered_at->format('m-d') }}
  ```

  ### Gebruik `whereBelongsTo()`

  ```php
  // Bad: hardcoded foreign key
  Post::where('user_id', $user->id)->get();

  // Good: cleaner and relationship-aware
  Post::whereBelongsTo($user)->get();
  Post::whereBelongsTo($user, 'author')->get();
  ```

  <a name="more-info"></a>
  ## Meer info

  - [Laravel Local Scopes Documentation](https://laravel.com/docs/eloquent#local-scopes)
  - [Laravel Attribute Casting Documentation](https://laravel.com/docs/eloquent-mutators#attribute-casting)
  - [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)
content_en: |-
  <a name="introduction"></a>
  ## Introduction

  Eloquent provides local scopes for reusable query constraints and attribute casts for automatic type conversion. Using these features keeps query logic DRY, ensures consistent data types, and makes code more expressive. Combined with helpers like `whereBelongsTo()`, they make Eloquent queries cleaner and less error-prone.

  <a name="why"></a>
  ## Why

  - **DRY queries**: Local scopes extract reusable query constraints, eliminating duplicated `where` clauses across the codebase
  - **Type safety**: Attribute casts automatically convert database values to the correct PHP types (booleans, arrays, dates, decimals)
  - **Cleaner queries**: `whereBelongsTo()` eliminates hardcoded foreign key references, making relationship queries more readable
  - **Better templates**: Casting date columns means you can use Carbon methods directly in Blade templates instead of manually parsing strings

  <a name="suitable-for"></a>
  ## Suitable For

  - Any model with repeated query patterns (active users, published posts, etc.)
  - Models with JSON, boolean, decimal, or date columns
  - Applications using Blade or API responses that format dates

  <a name="less-suitable"></a>
  ## Less Suitable

  - One-off queries that aren't reused anywhere
  - Global scopes should be used sparingly — prefer local scopes for most filtering needs

  <a name="examples"></a>
  ## Examples

  ### Local Scopes

  ```php
  // Bad: duplicated query logic
  $active = User::where('verified', true)->whereNotNull('activated_at')->get();
  $articles = Article::whereHas('user', function ($q) {
      $q->where('verified', true)->whereNotNull('activated_at');
  })->get();

  // Good: reusable local scope
  public function scopeActive(Builder $query): Builder
  {
      return $query->where('verified', true)->whereNotNull('activated_at');
  }

  $active = User::active()->get();
  $articles = Article::whereHas('user', fn ($q) => $q->active())->get();
  ```

  ### Global Scopes — Use Sparingly

  Global scopes silently modify every query on the model, making debugging difficult. Reserve them for truly universal constraints like soft deletes or multi-tenancy. Prefer local scopes for everything else.

  ### Attribute Casts

  Use the `casts()` method for automatic type conversion:

  ```php
  protected function casts(): array
  {
      return [
          'is_active' => 'boolean',
          'metadata' => 'array',
          'total' => 'decimal:2',
      ];
  }
  ```

  ### Cast Date Columns Properly

  ```php
  // Bad: manual date parsing in templates
  {{ Carbon::createFromFormat('Y-d-m H-i', $order->ordered_at)->toDateString() }}

  // Good: cast in the model
  protected function casts(): array
  {
      return [
          'ordered_at' => 'datetime',
      ];
  }

  // Then use directly in Blade
  {{ $order->ordered_at->toDateString() }}
  {{ $order->ordered_at->format('m-d') }}
  ```

  ### Use `whereBelongsTo()`

  ```php
  // Bad: hardcoded foreign key
  Post::where('user_id', $user->id)->get();

  // Good: cleaner and relationship-aware
  Post::whereBelongsTo($user)->get();
  Post::whereBelongsTo($user, 'author')->get();
  ```

  <a name="more-info"></a>
  ## More Info

  - [Laravel Local Scopes Documentation](https://laravel.com/docs/eloquent#local-scopes)
  - [Laravel Attribute Casting Documentation](https://laravel.com/docs/eloquent-mutators#attribute-casting)
  - [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)
best_practice_categories:
  - database-and-eloquent-orm
category_slug: database-and-eloquent-orm
category_title: 'Database en Eloquent ORM'
category_title_en: 'Database & Eloquent ORM'
source_path: database-and-eloquent-orm/use-eloquent-scopes-and-casts/BEST_PRACTICE.md
source_sha: c7034be11f69954eac43e50486b6e5bebde98c46
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/database-and-eloquent-orm/use-eloquent-scopes-and-casts/BEST_PRACTICE.md'
has_skill: true
skill_content: |-
  Use this skill when a Laravel task touches this best practice. It is self-contained so it can be installed independently by Laravel Boost or another agent-skill system.

  ## Core Guidance

  Eloquent provides local scopes for reusable query constraints and attribute casts for automatic type conversion. Using these features keeps query logic DRY, ensures consistent data types, and makes code more expressive. Combined with helpers like `whereBelongsTo()`, they make Eloquent queries cleaner and less error-prone.

  ## Why It Matters

  - **DRY queries**: Local scopes extract reusable query constraints, eliminating duplicated `where` clauses across the codebase
  - **Type safety**: Attribute casts automatically convert database values to the correct PHP types (booleans, arrays, dates, decimals)
  - **Cleaner queries**: `whereBelongsTo()` eliminates hardcoded foreign key references, making relationship queries more readable
  - **Better templates**: Casting date columns means you can use Carbon methods directly in Blade templates instead of manually parsing strings

  ## Apply When

  - Any model with repeated query patterns (active users, published posts, etc.)
  - Models with JSON, boolean, decimal, or date columns
  - Applications using Blade or API responses that format dates

  ## Be Careful When

  - One-off queries that aren't reused anywhere
  - Global scopes should be used sparingly — prefer local scopes for most filtering needs

  ## Canonical Source

  - Full best practice: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/database-and-eloquent-orm/use-eloquent-scopes-and-casts/BEST_PRACTICE.md
  - Dutch translation: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/database-and-eloquent-orm/use-eloquent-scopes-and-casts/translations/nl.md

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
skill_source_path: database-and-eloquent-orm/use-eloquent-scopes-and-casts/skill/SKILL.md
skill_github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/database-and-eloquent-orm/use-eloquent-scopes-and-casts/skill/SKILL.md'
skill_references: []
synced_at: 1785159222
---
