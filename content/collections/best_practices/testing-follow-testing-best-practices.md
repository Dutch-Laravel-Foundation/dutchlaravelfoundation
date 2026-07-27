---
id: f3c8de7d-02ae-5629-a2f2-47d02fa4f892
blueprint: best_practices
title: 'Volg Best Practices voor Testen'
title_nl: 'Volg Best Practices voor Testen'
title_en: 'Follow Testing Best Practices'
summary_nl: 'Gebruik de test-helpers en -patronen van Laravel die suites sneller, duidelijker en minder foutgevoelig maken.'
summary_en: 'Use Laravel’s testing helpers and patterns that make suites faster, clearer, and less error-prone.'
chapters_nl:
  - title: Beschrijving
    anchor: beschrijving
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
  ## Beschrijving

  Gebruik de test-helpers en -patronen van Laravel die suites sneller, duidelijker en minder foutgevoelig maken.

  <a name="recommended-situation"></a>
  ## Aanbevolen Situatie

  Gebruik dit bij het schrijven of onderhouden van tests in een Laravel-applicatie.

  <a name="human-guidance"></a>
  ## Menselijke Begeleiding

  Naast het kiezen van een testframework (zie [Gebruik PHPUnit of Pest](../../use-phpunit-or-pest-for-testing/translations/nl.md)) zijn er verschillende Laravel-specifieke testpatronen die de snelheid, betrouwbaarheid en expressiviteit van tests verbeteren. Denk aan het gebruik van `LazilyRefreshDatabase` voor performance, model-assertions voor duidelijkheid, factory states voor leesbaarheid, en het correct ordenen van fakes om te voorkomen dat model events breken.

  <a name="why"></a>
  ### Waarom

  - **Snellere testsuites**: `LazilyRefreshDatabase` draait migrations alleen wanneer het schema verandert, wat grote suites aanzienlijk versnelt
  - **Expressieve assertions**: `assertModelExists()` is type-veiliger en levert duidelijkere foutmeldingen op dan een kale `assertDatabaseHas()`
  - **Zelfdocumenterende tests**: Benoemde factory states zoals `->unverified()` communiceren de bedoeling beter dan handmatige attribuut-overrides
  - **Correct gedrag**: `Event::fake()` na de factory-setup aanroepen voorkomt dat model events die factories nodig hebben stilzwijgend breken

  <a name="suitable-for"></a>
  ### Geschikt Voor

  - Alle Laravel-applicaties met testsuites
  - Teams die hun CI/CD-pipelines willen versnellen
  - Projecten met complexe factory-relaties
  - Applicaties met event-gedreven of queue-gebaseerd gedrag dat getest moet worden

  <a name="less-suitable"></a>
  ### Minder Geschikt

  - Zeer kleine testsuites waar snelheidsoptimalisaties niet merkbaar zijn
  - Projecten die de ingebouwde testfuncties van Laravel niet gebruiken

  <a name="examples"></a>
  ### Voorbeelden

  #### Gebruik `LazilyRefreshDatabase`

  ```php
  // Slower: runs all migrations every test run
  use Illuminate\Foundation\Testing\RefreshDatabase;

  // Faster: only migrates when schema changes
  use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
  ```

  #### Gebruik Model-assertions

  ```php
  // Bad: raw database assertion
  $this->assertDatabaseHas('users', ['id' => $user->id]);

  // Good: more expressive and type-safe
  $this->assertModelExists($user);
  ```

  #### Gebruik Factory States en Sequences

  Benoemde states maken tests zelfdocumenterend:

  ```php
  // Bad: manual attribute override
  User::factory()->create(['email_verified_at' => null]);

  // Good: named state communicates intent
  User::factory()->unverified()->create();
  ```

  #### Roep `Event::fake()` Aan na de Factory-setup

  Model-factories vertrouwen op model events (bijv. `creating` om UUID's te genereren). Als je `Event::fake()` vóór de factory-aanroepen plaatst, worden die events onderdrukt en ontstaan er kapotte modellen:

  ```php
  // Bad: Event::fake() prevents factory model events
  Event::fake();
  $user = User::factory()->create();

  // Good: create models first, then fake events
  $user = User::factory()->create();
  Event::fake();
  ```

  #### Gebruik `Exceptions::fake()` voor Exception-assertions

  Gebruik in plaats van `withoutExceptionHandling()` liever `Exceptions::fake()` om te controleren dat de juiste exception werd gerapporteerd terwijl het request normaal wordt voltooid.

  #### Gebruik `recycle()` om Relatie-instanties te Delen

  Zonder `recycle()` maken geneste factories afzonderlijke instanties van dezelfde conceptuele entiteit:

  ```php
  Ticket::factory()
      ->recycle(Airline::factory()->create())
      ->create();
  ```

  <a name="more-info"></a>
  ### Meer Info

  - [Laravel Testing Documentation](https://laravel.com/docs/testing)
  - [Laravel Database Testing Documentation](https://laravel.com/docs/database-testing)
  - [Laravel Mocking Documentation](https://laravel.com/docs/mocking)
  - [Gebruik PHPUnit of Pest voor Testen](../../use-phpunit-or-pest-for-testing/translations/nl.md)
  - [Houd Vast aan Eén Testframework](../../stick-to-one-testing-framework/translations/nl.md)
  - [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)

  <a name="boost-guideline"></a>
  ## Boost Guideline

  ```md
  ---
  title: Follow Testing Best Practices
  description: Use Laravel’s testing helpers and patterns that make suites faster, clearer, and less error-prone.
  recommended_situation: Use when writing or maintaining tests in a Laravel application.
  ---

  - Prefer Laravel-native testing helpers that improve speed and clarity, including `LazilyRefreshDatabase`, model assertions, and named factory states.
  - Structure test setup so fakes do not accidentally disable model events or other behavior required to build valid test data.
  - Use expressive assertions and realistic factories to verify behavior instead of low-level or incidental implementation details.
  - Reach for Laravel testing utilities such as `Exceptions::fake()` and `recycle()` when they make intent or correctness clearer.
  ```
content_en: |-
  <a name="description"></a>
  ## Description

  Use Laravel’s testing helpers and patterns that make suites faster, clearer, and less error-prone.

  <a name="recommended-situation"></a>
  ## Recommended Situation

  Use when writing or maintaining tests in a Laravel application.

  <a name="human-guidance"></a>
  ## Human Guidance

  Beyond choosing a testing framework (see [Use PHPUnit or Pest](../use-phpunit-or-pest-for-testing/BEST_PRACTICE.md)), there are several Laravel-specific testing patterns that improve test speed, reliability, and expressiveness. These include using `LazilyRefreshDatabase` for performance, model assertions for clarity, factory states for readability, and correctly ordering fakes to avoid breaking model events.

  <a name="why"></a>
  ### Why

  - **Faster test suites**: `LazilyRefreshDatabase` only runs migrations when the schema changes, significantly speeding up large suites
  - **Expressive assertions**: `assertModelExists()` is more type-safe and produces clearer failure messages than raw `assertDatabaseHas()`
  - **Self-documenting tests**: Named factory states like `->unverified()` communicate intent better than manual attribute overrides
  - **Correct behavior**: Calling `Event::fake()` after factory setup prevents silently breaking model events that factories depend on

  <a name="suitable-for"></a>
  ### Suitable For

  - All Laravel applications with test suites
  - Teams looking to speed up CI/CD pipelines
  - Projects with complex factory relationships
  - Applications with event-driven or queued behavior that needs testing

  <a name="less-suitable"></a>
  ### Less Suitable

  - Very small test suites where speed optimizations aren't noticeable
  - Projects not using Laravel's built-in testing features

  <a name="examples"></a>
  ### Examples

  #### Use `LazilyRefreshDatabase`

  ```php
  // Slower: runs all migrations every test run
  use Illuminate\Foundation\Testing\RefreshDatabase;

  // Faster: only migrates when schema changes
  use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
  ```

  #### Use Model Assertions

  ```php
  // Bad: raw database assertion
  $this->assertDatabaseHas('users', ['id' => $user->id]);

  // Good: more expressive and type-safe
  $this->assertModelExists($user);
  ```

  #### Use Factory States and Sequences

  Named states make tests self-documenting:

  ```php
  // Bad: manual attribute override
  User::factory()->create(['email_verified_at' => null]);

  // Good: named state communicates intent
  User::factory()->unverified()->create();
  ```

  #### Call `Event::fake()` After Factory Setup

  Model factories rely on model events (e.g., `creating` to generate UUIDs). Calling `Event::fake()` before factory calls silences those events, producing broken models:

  ```php
  // Bad: Event::fake() prevents factory model events
  Event::fake();
  $user = User::factory()->create();

  // Good: create models first, then fake events
  $user = User::factory()->create();
  Event::fake();
  ```

  #### Use `Exceptions::fake()` for Exception Assertions

  Instead of `withoutExceptionHandling()`, use `Exceptions::fake()` to assert the correct exception was reported while the request completes normally.

  #### Use `recycle()` to Share Relationship Instances

  Without `recycle()`, nested factories create separate instances of the same conceptual entity:

  ```php
  Ticket::factory()
      ->recycle(Airline::factory()->create())
      ->create();
  ```

  <a name="more-info"></a>
  ### More Info

  - [Laravel Testing Documentation](https://laravel.com/docs/testing)
  - [Laravel Database Testing Documentation](https://laravel.com/docs/database-testing)
  - [Laravel Mocking Documentation](https://laravel.com/docs/mocking)
  - [Use PHPUnit or Pest for Testing](../use-phpunit-or-pest-for-testing/BEST_PRACTICE.md)
  - [Stick to One Testing Framework](../stick-to-one-testing-framework/BEST_PRACTICE.md)
  - [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)

  <a name="boost-guideline"></a>
  ## Boost Guideline

  ```md
  ---
  title: Follow Testing Best Practices
  description: Use Laravel’s testing helpers and patterns that make suites faster, clearer, and less error-prone.
  recommended_situation: Use when writing or maintaining tests in a Laravel application.
  ---

  - Prefer Laravel-native testing helpers that improve speed and clarity, including `LazilyRefreshDatabase`, model assertions, and named factory states.
  - Structure test setup so fakes do not accidentally disable model events or other behavior required to build valid test data.
  - Use expressive assertions and realistic factories to verify behavior instead of low-level or incidental implementation details.
  - Reach for Laravel testing utilities such as `Exceptions::fake()` and `recycle()` when they make intent or correctness clearer.
  ```
best_practice_categories:
  - testing
category_slug: testing
category_title: Testen
category_title_en: Testing
source_path: testing/follow-testing-best-practices/BEST_PRACTICE.md
source_sha: c7034be11f69954eac43e50486b6e5bebde98c46
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/testing/follow-testing-best-practices/BEST_PRACTICE.md'
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

  - Full best practice: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/testing/follow-testing-best-practices/BEST_PRACTICE.md
  - Dutch translation: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/testing/follow-testing-best-practices/translations/nl.md

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
skill_source_path: testing/follow-testing-best-practices/skill/SKILL.md
skill_github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/testing/follow-testing-best-practices/skill/SKILL.md'
skill_references: []
---
