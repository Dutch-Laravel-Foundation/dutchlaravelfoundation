---
id: cdff398d-702b-5993-a498-5bd4b3c08918
blueprint: best_practices
title: 'Effectieve migrations schrijven'
title_nl: 'Effectieve migrations schrijven'
title_en: 'Write Effective Migrations'
summary_nl: 'Migrations vormen de versiebeheer voor je databaseschema. Goed geschreven migrations zijn gericht, omkeerbaar en bevatten vanaf het begin de juiste indexering. Omdat migrations bevroren momentopnames zijn, vragen ze om speciale discipline —...'
summary_en: 'Migrations are the version control for your database schema. Well-written migrations are focused, reversible, and include proper indexing from the start. Since migrations are frozen snapshots in time, they require special discipline — once...'
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
  - title: 'Meer Info'
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

  Migrations vormen de versiebeheer voor je databaseschema. Goed geschreven migrations zijn gericht, omkeerbaar en bevatten vanaf het begin de juiste indexering. Omdat migrations bevroren momentopnames zijn, vragen ze om speciale discipline — zodra ze naar productie zijn uitgerold, mogen ze nooit meer worden gewijzigd.

  <a name="why"></a>
  ## Waarom

  - **Consistentie**: Het gebruik van `constrained()` voor foreign keys zorgt voor automatische naamgeving en referentiële integriteit
  - **Veiligheid**: Uitgerolde migrations nooit wijzigen voorkomt inconsistente databasetoestanden tussen omgevingen
  - **Performance**: Indexes toevoegen in de migration in plaats van achteraf voorkomt vergeten performance-optimalisaties
  - **Omkeerbaarheid**: Het schrijven van `down()`-methodes maakt veilige rollbacks mogelijk tijdens mislukte deployments en in CI-pipelines
  - **Duidelijkheid**: Eén verantwoordelijkheid per migration maakt het eenvoudig om te herkennen wat er wanneer is veranderd

  <a name="suitable-for"></a>
  ## Geschikt Voor

  - Alle Laravel-applicaties die migrations gebruiken
  - Teams met meerdere developers die aan hetzelfde databaseschema werken
  - Projecten met CI/CD-pipelines die migrations uitvoeren

  <a name="less-suitable"></a>
  ## Minder Geschikt

  - N.v.t. — deze praktijken gelden voor elk project dat Laravel-migrations gebruikt

  <a name="examples"></a>
  ## Voorbeelden

  ### Gebruik `constrained()` voor Foreign Keys

  ```php
  $table->foreignId('user_id')->constrained()->cascadeOnDelete();

  // Non-standard names
  $table->foreignId('author_id')->constrained('users');
  ```

  ### Wijzig Uitgerolde Migrations Nooit

  ```php
  // Bad: editing a migration that already ran in production
  // 2024_01_01_create_posts_table.php
  $table->string('slug')->unique(); // added after deployment

  // Good: new migration to alter the table
  // 2024_03_15_add_slug_to_posts_table.php
  Schema::table('posts', function (Blueprint $table) {
      $table->string('slug')->unique()->after('title');
  });
  ```

  ### Voeg Indexes toe in de Migration

  ```php
  // Bad: no indexes on frequently queried columns
  Schema::create('orders', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained();
      $table->string('status');
      $table->timestamps();
  });

  // Good: indexes added from the start
  Schema::create('orders', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->index();
      $table->string('status')->index();
      $table->timestamp('shipped_at')->nullable()->index();
      $table->timestamps();
  });
  ```

  ### Spiegel Column-Defaults in Model `$attributes`

  Wanneer een column een database-default heeft, spiegel deze dan in het model zodat nieuwe instances de juiste waarden hebben vóór het opslaan:

  ```php
  // Migration
  $table->string('status')->default('pending');

  // Model
  protected $attributes = [
      'status' => 'pending',
  ];
  ```

  ### Schrijf Omkeerbare `down()`-Methodes

  ```php
  public function down(): void
  {
      Schema::table('posts', function (Blueprint $table) {
          $table->dropColumn('slug');
      });
  }
  ```

  Voor bewust onomkeerbare migrations laat je een duidelijke comment achter en vereis je in plaats daarvan een corrigerende voorwaartse migration.

  ### Houd Migrations Gericht

  Meng nooit DDL (schemawijzigingen) en DML (datamanipulatie) in één migration:

  ```php
  // Bad: partial failure creates unrecoverable state
  public function up(): void
  {
      Schema::create('settings', function (Blueprint $table) { /* ... */ });
      DB::table('settings')->insert(['key' => 'version', 'value' => '1.0']);
  }

  // Good: separate migrations
  // Migration 1: create_settings_table
  Schema::create('settings', function (Blueprint $table) { /* ... */ });

  // Migration 2: seed_default_settings
  DB::table('settings')->insert(['key' => 'version', 'value' => '1.0']);
  ```

  <a name="more-info"></a>
  ## Meer Info

  - [Laravel Migrations Documentation](https://laravel.com/docs/migrations)
  - [Avoid Eloquent Models in Migrations](../../avoid-eloquent-models-in-migrations/translations/nl.md) — gebruik de Query Builder in plaats van Eloquent in migrations
  - [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)
content_en: |-
  <a name="introduction"></a>
  ## Introduction

  Migrations are the version control for your database schema. Well-written migrations are focused, reversible, and include proper indexing from the start. Since migrations are frozen snapshots in time, they require special discipline — once deployed to production, they should never be modified.

  <a name="why"></a>
  ## Why

  - **Consistency**: Using `constrained()` for foreign keys ensures automatic naming and referential integrity
  - **Safety**: Never modifying deployed migrations prevents inconsistent database states across environments
  - **Performance**: Adding indexes in the migration rather than as an afterthought avoids forgotten performance optimizations
  - **Reversibility**: Writing `down()` methods allows safe rollbacks during failed deployments and in CI pipelines
  - **Clarity**: Keeping one concern per migration makes it easy to identify what changed and when

  <a name="suitable-for"></a>
  ## Suitable For

  - All Laravel applications using migrations
  - Teams with multiple developers working on the same database schema
  - Projects with CI/CD pipelines that run migrations

  <a name="less-suitable"></a>
  ## Less Suitable

  - N/A — these practices apply to any project using Laravel migrations

  <a name="examples"></a>
  ## Examples

  ### Use `constrained()` for Foreign Keys

  ```php
  $table->foreignId('user_id')->constrained()->cascadeOnDelete();

  // Non-standard names
  $table->foreignId('author_id')->constrained('users');
  ```

  ### Never Modify Deployed Migrations

  ```php
  // Bad: editing a migration that already ran in production
  // 2024_01_01_create_posts_table.php
  $table->string('slug')->unique(); // added after deployment

  // Good: new migration to alter the table
  // 2024_03_15_add_slug_to_posts_table.php
  Schema::table('posts', function (Blueprint $table) {
      $table->string('slug')->unique()->after('title');
  });
  ```

  ### Add Indexes in the Migration

  ```php
  // Bad: no indexes on frequently queried columns
  Schema::create('orders', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained();
      $table->string('status');
      $table->timestamps();
  });

  // Good: indexes added from the start
  Schema::create('orders', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->index();
      $table->string('status')->index();
      $table->timestamp('shipped_at')->nullable()->index();
      $table->timestamps();
  });
  ```

  ### Mirror Column Defaults in Model `$attributes`

  When a column has a database default, mirror it in the model so new instances have correct values before saving:

  ```php
  // Migration
  $table->string('status')->default('pending');

  // Model
  protected $attributes = [
      'status' => 'pending',
  ];
  ```

  ### Write Reversible `down()` Methods

  ```php
  public function down(): void
  {
      Schema::table('posts', function (Blueprint $table) {
          $table->dropColumn('slug');
      });
  }
  ```

  For intentionally irreversible migrations, leave a clear comment and require a forward-fix migration instead.

  ### Keep Migrations Focused

  Never mix DDL (schema changes) and DML (data manipulation) in a single migration:

  ```php
  // Bad: partial failure creates unrecoverable state
  public function up(): void
  {
      Schema::create('settings', function (Blueprint $table) { /* ... */ });
      DB::table('settings')->insert(['key' => 'version', 'value' => '1.0']);
  }

  // Good: separate migrations
  // Migration 1: create_settings_table
  Schema::create('settings', function (Blueprint $table) { /* ... */ });

  // Migration 2: seed_default_settings
  DB::table('settings')->insert(['key' => 'version', 'value' => '1.0']);
  ```

  <a name="more-info"></a>
  ## More Info

  - [Laravel Migrations Documentation](https://laravel.com/docs/migrations)
  - [Avoid Eloquent Models in Migrations](../avoid-eloquent-models-in-migrations/BEST_PRACTICE.md) — use Query Builder instead of Eloquent in migrations
  - [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)
best_practice_categories:
  - database-and-eloquent-orm
category_slug: database-and-eloquent-orm
category_title: 'Database en Eloquent ORM'
category_title_en: 'Database & Eloquent ORM'
source_path: database-and-eloquent-orm/write-effective-migrations/BEST_PRACTICE.md
source_sha: c7034be11f69954eac43e50486b6e5bebde98c46
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/database-and-eloquent-orm/write-effective-migrations/BEST_PRACTICE.md'
has_skill: true
skill_content: |-
  Use this skill when a Laravel task touches this best practice. It is self-contained so it can be installed independently by Laravel Boost or another agent-skill system.

  ## Core Guidance

  Migrations are the version control for your database schema. Well-written migrations are focused, reversible, and include proper indexing from the start. Since migrations are frozen snapshots in time, they require special discipline — once deployed to production, they should never be modified.

  ## Why It Matters

  - **Consistency**: Using `constrained()` for foreign keys ensures automatic naming and referential integrity
  - **Safety**: Never modifying deployed migrations prevents inconsistent database states across environments
  - **Performance**: Adding indexes in the migration rather than as an afterthought avoids forgotten performance optimizations
  - **Reversibility**: Writing `down()` methods allows safe rollbacks during failed deployments and in CI pipelines
  - **Clarity**: Keeping one concern per migration makes it easy to identify what changed and when

  ## Apply When

  - All Laravel applications using migrations
  - Teams with multiple developers working on the same database schema
  - Projects with CI/CD pipelines that run migrations

  ## Be Careful When

  - N/A — these practices apply to any project using Laravel migrations

  ## Canonical Source

  - Full best practice: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/database-and-eloquent-orm/write-effective-migrations/BEST_PRACTICE.md
  - Dutch translation: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/database-and-eloquent-orm/write-effective-migrations/translations/nl.md

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
skill_source_path: database-and-eloquent-orm/write-effective-migrations/skill/SKILL.md
skill_github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/database-and-eloquent-orm/write-effective-migrations/skill/SKILL.md'
skill_references: []
---
