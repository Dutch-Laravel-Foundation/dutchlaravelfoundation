---
id: e84e8688-f715-5984-8c4f-9fdd601b161a
blueprint: best_practices
title: 'Ga correct om met exceptions'
title_nl: 'Ga correct om met exceptions'
title_en: 'Handle Exceptions Properly'
summary_nl: 'Laravel biedt flexibele exception handling via twee benaderingen: het co-loceren van gedrag op exception-classes of het centraliseren ervan in bootstrap/app.php. Beide benaderingen werken, de sleutel is er één kiezen en die consistent toepa...'
summary_en: 'Laravel provides flexible exception handling through two approaches: co-locating behavior on exception classes or centralizing it in bootstrap/app.php. Either approach works, the key is picking one and applying it consistently. Beyond that,...'
chapters_nl:
  - title: Introductie
    anchor: introductie
  - title: Waarom
    anchor: waarom
  - title: 'Geschikt voor'
    anchor: geschikt-voor
  - title: 'Minder geschikt voor'
    anchor: minder-geschikt-voor
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

  Laravel biedt flexibele exception handling via twee benaderingen: het co-loceren van gedrag op exception-classes of het centraliseren ervan in `bootstrap/app.php`. Beide benaderingen werken, de sleutel is er één kiezen en die consistent toepassen. Daarnaast biedt Laravel tools om ruis te onderdrukken (throttling, deduplicatie), correcte API-foutformaten af te dwingen en gestructureerde context aan exceptions te koppelen.

  <a name="why"></a>
  ## Waarom

  - **Consistente afhandeling**: Het kiezen van één benadering (co-located of gecentraliseerd) voorkomt verspreid, moeilijk te vinden exception-gedrag
  - **Minder ruis**: Het throttlen van exceptions met een hoog volume en het dedupliceren van reports beschermt log sinks en budgetten voor error tracking
  - **Correcte API-responses**: Het expliciet declareren van JSON-rendering voor API-routes voorkomt dat HTML-foutpagina's naar API-clients worden gestuurd
  - **Beter debuggen**: Gestructureerde context op exception-classes levert rijke metadata in logregels op zonder handmatige logging

  <a name="suitable-for"></a>
  ## Geschikt voor

  - Applicaties met custom exception-typen
  - API's die consistente JSON-foutresponses nodig hebben
  - Applicaties die integreren met error tracking-services (Sentry, Flare, Bugsnag)
  - Applicaties met veel verkeer waar het aantal errors de logging kan overweldigen

  <a name="less-suitable"></a>
  ## Minder geschikt voor

  - Eenvoudige applicaties waar de standaard exception handling voldoende is
  - Prototypes in een vroeg stadium waar gestructureerde error handling overhead toevoegt

  <a name="examples"></a>
  ## Voorbeelden

  ### Kies één benadering en wees consistent

  **Co-locatie op de exception-class**, houdt gedrag naast de definitie:

  ```php
  class InvalidOrderException extends Exception
  {
      public function report(): void { /* custom reporting */ }

      public function render(Request $request): Response
      {
          return response()->view('errors.invalid-order', status: 422);
      }
  }
  ```

  **Gecentraliseerd in `bootstrap/app.php`**, alle exception handling op één plek:

  ```php
  ->withExceptions(function (Exceptions $exceptions) {
      $exceptions->render(function (InvalidOrderException $e, Request $request) {
          return response()->view('errors.invalid-order', status: 422);
      });
  })
  ```

  Bekijk de bestaande codebase en volg het patroon dat al is vastgelegd.

  ### Gebruik `ShouldntReport` voor exceptions die nooit gelogd mogen worden

  ```php
  class PodcastProcessingException extends Exception implements ShouldntReport {}
  ```

  ### Forceer JSON-foutrendering voor API-routes

  Laravel detecteert `Accept: application/json` automatisch, maar API-clients stellen deze mogelijk niet in:

  ```php
  $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
      return $request->is('api/*') || $request->expectsJson();
  });
  ```

  ### Voeg gestructureerde context toe aan exceptions

  ```php
  class InvalidOrderException extends Exception
  {
      public function context(): array
      {
          return ['order_id' => $this->orderId];
      }
  }
  ```

  Laravel neemt deze data automatisch op in de logregel.

  ### Throttle exceptions met een hoog volume

  Eén falende integratie kan error tracking overspoelen. Gebruik `throttle()` om per exception-type te rate-limiten.

  ### Schakel `dontReportDuplicates()` in

  Voorkomt dat dezelfde exception-instantie meerdere keren wordt gelogd wanneer `report($e)` in meerdere catch-blocks wordt aangeroepen.

  <a name="more-info"></a>
  ## Meer info

  - [Laravel Error Handling Documentatie](https://laravel.com/docs/errors)
  - [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)
content_en: |-
  <a name="introduction"></a>
  ## Introduction

  Laravel provides flexible exception handling through two approaches: co-locating behavior on exception classes or centralizing it in `bootstrap/app.php`. Either approach works, the key is picking one and applying it consistently. Beyond that, Laravel offers tools for suppressing noise (throttling, deduplication), forcing correct API error formats, and attaching structured context to exceptions.

  <a name="why"></a>
  ## Why

  - **Consistent handling**: Choosing one approach (co-located or centralized) prevents scattered, hard-to-find exception behavior
  - **Reduced noise**: Throttling high-volume exceptions and deduplicating reports protects log sinks and error tracking budgets
  - **Correct API responses**: Explicitly declaring JSON rendering for API routes prevents HTML error pages from being sent to API clients
  - **Better debugging**: Structured context on exception classes provides rich metadata in log entries without manual logging

  <a name="suitable-for"></a>
  ## Suitable For

  - Applications with custom exception types
  - APIs that need consistent JSON error responses
  - Applications integrating with error tracking services (Sentry, Flare, Bugsnag)
  - High-traffic applications where error volume can overwhelm logging

  <a name="less-suitable"></a>
  ## Less Suitable

  - Simple applications where default exception handling is sufficient
  - Early-stage prototypes where structured error handling adds overhead

  <a name="examples"></a>
  ## Examples

  ### Choose One Approach and Be Consistent

  **Co-location on the exception class**, keeps behavior alongside the definition:

  ```php
  class InvalidOrderException extends Exception
  {
      public function report(): void { /* custom reporting */ }

      public function render(Request $request): Response
      {
          return response()->view('errors.invalid-order', status: 422);
      }
  }
  ```

  **Centralized in `bootstrap/app.php`**, all exception handling in one place:

  ```php
  ->withExceptions(function (Exceptions $exceptions) {
      $exceptions->render(function (InvalidOrderException $e, Request $request) {
          return response()->view('errors.invalid-order', status: 422);
      });
  })
  ```

  Check the existing codebase and follow whichever pattern is already established.

  ### Use `ShouldntReport` for Exceptions That Should Never Log

  ```php
  class PodcastProcessingException extends Exception implements ShouldntReport {}
  ```

  ### Force JSON Error Rendering for API Routes

  Laravel auto-detects `Accept: application/json` but API clients may not set it:

  ```php
  $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
      return $request->is('api/*') || $request->expectsJson();
  });
  ```

  ### Add Structured Context to Exceptions

  ```php
  class InvalidOrderException extends Exception
  {
      public function context(): array
      {
          return ['order_id' => $this->orderId];
      }
  }
  ```

  Laravel automatically includes this data in the log entry.

  ### Throttle High-Volume Exceptions

  A single failing integration can flood error tracking. Use `throttle()` to rate-limit per exception type.

  ### Enable `dontReportDuplicates()`

  Prevents the same exception instance from being logged multiple times when `report($e)` is called in multiple catch blocks.

  <a name="more-info"></a>
  ## More Info

  - [Laravel Error Handling Documentation](https://laravel.com/docs/errors)
  - [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)
best_practice_categories:
  - maintenance
category_slug: maintenance
category_title: Onderhoud
category_title_en: Maintenance
source_path: maintenance/handle-exceptions-properly/BEST_PRACTICE.md
source_sha: c7034be11f69954eac43e50486b6e5bebde98c46
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/maintenance/handle-exceptions-properly/BEST_PRACTICE.md'
has_skill: true
skill_content: |-
  Use this skill when a Laravel task touches this best practice. It is self-contained so it can be installed independently by Laravel Boost or another agent-skill system.

  ## Core Guidance

  Laravel provides flexible exception handling through two approaches: co-locating behavior on exception classes or centralizing it in `bootstrap/app.php`. Either approach works, the key is picking one and applying it consistently. Beyond that, Laravel offers tools for suppressing noise (throttling, deduplication), forcing correct API error formats, and attaching structured context to exceptions.

  ## Why It Matters

  - **Consistent handling**: Choosing one approach (co-located or centralized) prevents scattered, hard-to-find exception behavior
  - **Reduced noise**: Throttling high-volume exceptions and deduplicating reports protects log sinks and error tracking budgets
  - **Correct API responses**: Explicitly declaring JSON rendering for API routes prevents HTML error pages from being sent to API clients
  - **Better debugging**: Structured context on exception classes provides rich metadata in log entries without manual logging

  ## Apply When

  - Applications with custom exception types
  - APIs that need consistent JSON error responses
  - Applications integrating with error tracking services (Sentry, Flare, Bugsnag)
  - High-traffic applications where error volume can overwhelm logging

  ## Be Careful When

  - Simple applications where default exception handling is sufficient
  - Early-stage prototypes where structured error handling adds overhead

  ## Canonical Source

  - Full best practice: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/maintenance/handle-exceptions-properly/BEST_PRACTICE.md
  - Dutch translation: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/maintenance/handle-exceptions-properly/translations/nl.md

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
skill_source_path: maintenance/handle-exceptions-properly/skill/SKILL.md
skill_github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/maintenance/handle-exceptions-properly/skill/SKILL.md'
skill_references: []
synced_at: 1785231871
---
