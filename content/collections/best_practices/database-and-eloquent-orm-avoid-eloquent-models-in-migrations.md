---
id: 808c049a-f9d5-5e1e-adef-b9bbd88c2209
blueprint: best_practices
title: 'Vermijd het gebruik van Eloquent-models in migraties'
title_nl: 'Vermijd het gebruik van Eloquent-models in migraties'
title_en: 'Avoid Using Eloquent Models in Migrations'
summary_nl: 'Database-migraties zouden ruwe database-queries of Laravels Query Builder (DB-facade) moeten gebruiken in plaats van Eloquent-models. Hoewel het handig kan lijken om models te gebruiken voor datamanipulatie tijdens migraties, kan deze werkw...'
summary_en: "Database migrations should use raw database queries or Laravel's Query Builder (DB facade) instead of Eloquent models. While it may seem convenient to use models for data manipulation during migrations, this practice can lead to broken migr..."
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

  Database-migraties zouden ruwe database-queries of Laravels Query Builder (DB-facade) moeten gebruiken in plaats van Eloquent-models. Hoewel het handig kan lijken om models te gebruiken voor datamanipulatie tijdens migraties, kan deze werkwijze na verloop van tijd leiden tot kapotte migraties en onvoorspelbaar gedrag.

  <a name="why"></a>
  ## Waarom

  - **Models evolueren, migraties niet** - Wanneer je een Eloquent-model wijzigt (velden toevoegen, casting aanpassen, relaties wijzigen), weerspiegelt het model alleen de huidige staat, niet de historische staat. Een migratie die dat model gebruikt, kan mislukken wanneer deze op een schone database wordt uitgevoerd.
  - **Schema-mismatch tijdens uitvoering** - Eloquent verwacht dat het databaseschema overeenkomt met de modeldefinitie. Tijdens migraties is het schema in beweging, wat tot fouten kan leiden wanneer een model verwijst naar velden of relaties die nog niet bestaan.
  - **Onvoorspelbare migratievolgorde** - Als migratie 1 een model gebruikt dat wijzigingen uit migratie 2 weerspiegelt, breekt migratie 1 omdat migratie 2 nog niet is uitgevoerd.
  - **Scheiding van verantwoordelijkheden** - Migraties zijn specifiek ontworpen voor databasestructuur, niet voor objectgestuurde datamanipulatie. Het gebruik van de DB-facade behoudt deze scheiding.
  - **Stabiliteit op lange termijn** - Migraties moeten voor onbepaalde tijd reproduceerbaar zijn. Het gebruik van models breekt deze garantie zodra het model verandert.

  <a name="suitable-for"></a>
  ## Geschikt Voor

  - **Alle migraties** - Gebruik Query Builder of ruwe SQL voor elke datamanipulatie in migraties
  - **Data seeden tijdens migraties** - Gebruik de DB-facade in plaats van models om initiële data te vullen
  - **Schemawijzigingen** - Gebruik altijd de Schema Builder voor structurele wijzigingen
  - **Verse installaties** - Zorgt ervoor dat migraties correct worden uitgevoerd in nieuwe omgevingen, ongeacht modelwijzigingen

  <a name="less-suitable"></a>
  ## Minder Geschikt

  - **Database seeders** - Seeders zijn bedoeld voor testdata en kunnen veilig Eloquent-models gebruiken, omdat ze apart worden uitgevoerd en geen deel uitmaken van de migratiegeschiedenis
  - **Eenmalige scripts** - Voor scripts die niet opnieuw worden uitgevoerd, kan het gebruik van models acceptabel zijn (hoewel nog steeds niet aanbevolen)

  <a name="examples"></a>
  ## Voorbeelden

  **❌ Slecht - Een Eloquent-model gebruiken:**
  ```php
  use App\Models\Post;

  public function up()
  {
      Schema::table('posts', function (Blueprint $table) {
          $table->boolean('is_published')->default(false);
      });

      // This will break if the Post model changes
      Post::query()->update(['is_published' => true]);
  }
  ```

  **✅ Goed - De Query Builder gebruiken:**
  ```php
  use Illuminate\Support\Facades\DB;

  public function up()
  {
      Schema::table('posts', function (Blueprint $table) {
          $table->boolean('is_published')->default(false);
      });

      // This will always work
      DB::table('posts')->update(['is_published' => true]);
  }
  ```

  **✅ Goed - Ruwe SQL gebruiken:**
  ```php
  use Illuminate\Support\Facades\DB;

  public function up()
  {
      Schema::table('posts', function (Blueprint $table) {
          $table->boolean('is_published')->default(false);
      });

      // Raw SQL is also reliable
      DB::statement('UPDATE posts SET is_published = true');
  }
  ```

  <a name="more-info"></a>
  ## Meer Info

  - [Laravel Migrations Documentation](https://laravel.com/docs/migrations)
  - [Laravel Query Builder Documentation](https://laravel.com/docs/queries)
  - [Laravel Database Seeding Documentation](https://laravel.com/docs/seeding)
  - [Write Effective Migrations](../../write-effective-migrations/translations/nl.md) — voor algemene best practices voor migraties zoals indexering, omkeerbaarheid en gerichte migraties
content_en: |-
  <a name="introduction"></a>
  ## Introduction

  Database migrations should use raw database queries or Laravel's Query Builder (DB facade) instead of Eloquent models. While it may seem convenient to use models for data manipulation during migrations, this practice can lead to broken migrations and unpredictable behavior over time.

  <a name="why"></a>
  ## Why

  - **Models evolve, migrations don't** - When you change an Eloquent model (add fields, change casting, modify relationships), the model only reflects the current state, not its historical state. A migration using that model may fail when run on a fresh database.
  - **Schema mismatch during execution** - Eloquent expects the database schema to match the model definition. During migrations, the schema is in flux, which can cause failures when a model references fields or relationships that don't exist yet.
  - **Unpredictable migration order** - If Migration 1 uses a model that reflects changes from Migration 2, Migration 1 will break because Migration 2 hasn't run yet.
  - **Separation of concerns** - Migrations are specifically designed for database structure, not object-driven data manipulation. Using the DB facade maintains this separation.
  - **Long-term stability** - Migrations need to be reproducible indefinitely. Using models breaks this guarantee as soon as the model changes.

  <a name="suitable-for"></a>
  ## Suitable For

  - **All migrations** - Use Query Builder or raw SQL for any data manipulation in migrations
  - **Data seeding during migrations** - Use DB facade instead of models to populate initial data
  - **Schema modifications** - Always use Schema Builder for structural changes
  - **Fresh installations** - Ensures migrations run correctly on new environments regardless of model changes

  <a name="less-suitable"></a>
  ## Less Suitable

  - **Database seeders** - Seeders are meant for test data and can safely use Eloquent models since they're run separately and aren't part of the migration history
  - **One-time scripts** - For scripts that won't be run again, using models may be acceptable (though still not recommended)

  <a name="examples"></a>
  ## Examples

  **❌ Bad - Using Eloquent Model:**
  ```php
  use App\Models\Post;

  public function up()
  {
      Schema::table('posts', function (Blueprint $table) {
          $table->boolean('is_published')->default(false);
      });

      // This will break if the Post model changes
      Post::query()->update(['is_published' => true]);
  }
  ```

  **✅ Good - Using Query Builder:**
  ```php
  use Illuminate\Support\Facades\DB;

  public function up()
  {
      Schema::table('posts', function (Blueprint $table) {
          $table->boolean('is_published')->default(false);
      });

      // This will always work
      DB::table('posts')->update(['is_published' => true]);
  }
  ```

  **✅ Good - Using Raw SQL:**
  ```php
  use Illuminate\Support\Facades\DB;

  public function up()
  {
      Schema::table('posts', function (Blueprint $table) {
          $table->boolean('is_published')->default(false);
      });

      // Raw SQL is also reliable
      DB::statement('UPDATE posts SET is_published = true');
  }
  ```

  <a name="more-info"></a>
  ## More Info

  - [Laravel Migrations Documentation](https://laravel.com/docs/migrations)
  - [Laravel Query Builder Documentation](https://laravel.com/docs/queries)
  - [Laravel Database Seeding Documentation](https://laravel.com/docs/seeding)
  - [Write Effective Migrations](../write-effective-migrations/BEST_PRACTICE.md) — for general migration best practices like indexing, reversibility, and focused migrations
best_practice_categories:
  - database-and-eloquent-orm
category_slug: database-and-eloquent-orm
category_title: 'Database en Eloquent ORM'
category_title_en: 'Database & Eloquent ORM'
source_path: database-and-eloquent-orm/avoid-eloquent-models-in-migrations/BEST_PRACTICE.md
source_sha: c7034be11f69954eac43e50486b6e5bebde98c46
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/database-and-eloquent-orm/avoid-eloquent-models-in-migrations/BEST_PRACTICE.md'
has_skill: true
skill_content: |-
  Use this skill when a Laravel task touches this best practice. It is self-contained so it can be installed independently by Laravel Boost or another agent-skill system.

  ## Core Guidance

  Database migrations should use raw database queries or Laravel's Query Builder (DB facade) instead of Eloquent models. While it may seem convenient to use models for data manipulation during migrations, this practice can lead to broken migrations and unpredictable behavior over time.

  ## Why It Matters

  - **Models evolve, migrations don't** - When you change an Eloquent model (add fields, change casting, modify relationships), the model only reflects the current state, not its historical state. A migration using that model may fail when run on a fresh database.
  - **Schema mismatch during execution** - Eloquent expects the database schema to match the model definition. During migrations, the schema is in flux, which can cause failures when a model references fields or relationships that don't exist yet.
  - **Unpredictable migration order** - If Migration 1 uses a model that reflects changes from Migration 2, Migration 1 will break because Migration 2 hasn't run yet.
  - **Separation of concerns** - Migrations are specifically designed for database structure, not object-driven data manipulation. Using the DB facade maintains this separation.
  - **Long-term stability** - Migrations need to be reproducible indefinitely. Using models breaks this guarantee as soon as the model changes.

  ## Apply When

  - **All migrations** - Use Query Builder or raw SQL for any data manipulation in migrations
  - **Data seeding during migrations** - Use DB facade instead of models to populate initial data
  - **Schema modifications** - Always use Schema Builder for structural changes
  - **Fresh installations** - Ensures migrations run correctly on new environments regardless of model changes

  ## Be Careful When

  - **Database seeders** - Seeders are meant for test data and can safely use Eloquent models since they're run separately and aren't part of the migration history
  - **One-time scripts** - For scripts that won't be run again, using models may be acceptable (though still not recommended)

  ## Canonical Source

  - Full best practice: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/database-and-eloquent-orm/avoid-eloquent-models-in-migrations/BEST_PRACTICE.md
  - Dutch translation: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/database-and-eloquent-orm/avoid-eloquent-models-in-migrations/translations/nl.md

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
skill_source_path: database-and-eloquent-orm/avoid-eloquent-models-in-migrations/skill/SKILL.md
skill_github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/database-and-eloquent-orm/avoid-eloquent-models-in-migrations/skill/SKILL.md'
skill_references: []
---
