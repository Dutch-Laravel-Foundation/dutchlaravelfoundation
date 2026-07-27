---
id: 17329f8b-6cae-56d6-9322-ec108ace38d5
blueprint: best_practices
title: 'Wachtrij-jobs correct configureren'
title_nl: 'Wachtrij-jobs correct configureren'
title_en: 'Configure Queued Jobs Properly'
summary_nl: 'Configureer Laravel queue-jobs met veilige timeouts, retries, uniciteit en foutafhandeling voor betrouwbaarheid in productie.'
summary_en: 'Configure Laravel queued jobs with safe timeouts, retries, uniqueness, and failure handling for production reliability.'
chapters_nl:
  - title: Beschrijving
    anchor: beschrijving
  - title: 'Aanbevolen situatie'
    anchor: aanbevolen-situatie
  - title: 'Menselijke begeleiding'
    anchor: menselijke-begeleiding
  - title: Boost-richtlijn
    anchor: boost-richtlijn
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

  Configureer Laravel queue-jobs met veilige timeouts, retries, uniciteit en foutafhandeling voor betrouwbaarheid in productie.

  <a name="recommended-situation"></a>
  ## Aanbevolen situatie

  Gebruik dit wanneer queue-jobs buiten de `sync`-driver draaien of belangrijk extern of gebruikersgericht werk uitvoeren.

  <a name="human-guidance"></a>
  ## Menselijke begeleiding

  Naast het uitbesteden van businesslogica aan jobs (zie [Houd commands klein en besteed uit aan jobs](../../keep-commands-small-defer-to-jobs/translations/nl.md)), moeten de jobs zelf goed geconfigureerd worden om betrouwbaar te zijn in productie. Dit omvat het instellen van correcte timeout- en retry-waarden, het toepassen van exponential backoff, het voorkomen van dubbele uitvoering, het expliciet afhandelen van fouten en het rate limiten van externe API-aanroepen.

  <a name="why"></a>
  ### Waarom

  - **Voorkomt dubbele uitvoering**: Wanneer `retry_after` korter is dan `timeout`, verdeelt de queue-worker de job opnieuw terwijl deze nog draait
  - **Beschermt externe services**: Exponential backoff en rate limiting voorkomen dat falende API's worden overspoeld
  - **Expliciete foutafhandeling**: Het implementeren van `failed()` zorgt ervoor dat fouten worden afgehandeld in plaats van stilzwijgend genegeerd
  - **Gecontroleerde concurrency**: `ShouldBeUnique` en `WithoutOverlapping` voorkomen dubbele en gelijktijdige verwerking van dezelfde data

  <a name="suitable-for"></a>
  ### Geschikt voor

  - Applicaties die queue-jobs in productie gebruiken
  - Jobs die externe API's aanroepen of kritieke data verwerken
  - Queue-deployments met meerdere workers of meerdere servers
  - Jobs die gebruikersgerichte operaties verwerken waarbij duplicaten of fouten zichtbaar zijn

  <a name="less-suitable"></a>
  ### Minder geschikt

  - Jobs die de `sync`-queue-driver gebruiken (alleen development/testing)
  - Eenvoudige fire-and-forget-jobs waarbij fouten acceptabel zijn

  <a name="examples"></a>
  ### Voorbeelden

  #### Stel `retry_after` hoger in dan `timeout`

  ```php
  class ProcessReport implements ShouldQueue
  {
      public $timeout = 120;
  }

  // config/queue.php — retry_after must be longer than any job timeout
  // retry_after: 180 ← safely longer
  ```

  #### Gebruik exponential backoff

  ```php
  class SyncWithStripe implements ShouldQueue
  {
      public $tries = 3;
      public $backoff = [1, 5, 10]; // seconds between retries
  }
  ```

  #### Voorkom dubbele jobverwerking

  ```php
  class GenerateInvoice implements ShouldQueue, ShouldBeUnique
  {
      public function uniqueId(): string
      {
          return $this->order->id;
      }

      public $uniqueFor = 3600;
  }
  ```

  #### Implementeer altijd `failed()`

  ```php
  public function failed(?Throwable $exception): void
  {
      $this->podcast->update(['status' => 'failed']);
      Log::error('Processing failed', [
          'id' => $this->podcast->id,
          'error' => $exception->getMessage(),
      ]);
  }
  ```

  #### Rate limit externe API-aanroepen

  ```php
  public function middleware(): array
  {
      return [new RateLimited('external-api')];
  }
  ```

  #### Batch gerelateerde jobs

  ```php
  Bus::batch([
      new ImportCsvChunk($chunk1),
      new ImportCsvChunk($chunk2),
  ])
  ->then(fn (Batch $batch) => Notification::send($user, new ImportComplete))
  ->catch(fn (Batch $batch, Throwable $e) => Log::error('Batch failed'))
  ->dispatch();
  ```

  #### Gebruik `WithoutOverlapping` voor concurrency-controle

  ```php
  public function middleware(): array
  {
      return [
          (new WithoutOverlapping($this->product->id))
              ->releaseAfter(60)
              ->expireAfter(180),
      ];
  }
  ```

  Gebruik `releaseAfter()` om te bepalen hoe lang overlappende jobs moeten wachten voordat ze terug naar de queue worden vrijgegeven, en `expireAfter()` om ervoor te zorgen dat de lock uiteindelijk verloopt als de worker crasht of de job onverwacht een timeout krijgt.

  <a name="more-info"></a>
  ### Meer info

  - [Laravel Queue Documentation](https://laravel.com/docs/queues)
  - [Laravel Job Middleware Documentation](https://laravel.com/docs/queues#job-middleware)
  - [Laravel Horizon Documentation](https://laravel.com/docs/horizon)
  - [Houd commands klein en besteed uit aan jobs](../../keep-commands-small-defer-to-jobs/translations/nl.md)
  - [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)

  <a name="boost-guideline"></a>
  ## Boost-richtlijn

  ```md
  ---
  title: Configure Queued Jobs Properly
  description: Configure Laravel queued jobs with safe timeouts, retries, uniqueness, and failure handling for production reliability.
  recommended_situation: Use when queued jobs run outside the `sync` driver or handle important external or user-facing work.
  ---

  - Set job `timeout`, `tries`, and queue `retry_after` coherently so long-running jobs are not retried while still executing.
  - Use exponential backoff, rate limiting middleware, and explicit uniqueness or overlap controls for jobs that hit external services or shared resources.
  - Implement `failed()` handling for jobs where failure state, cleanup, or observability matters.
  - Use batching and queue middleware deliberately when coordinating related jobs or controlling concurrency.
  ```
content_en: |-
  <a name="description"></a>
  ## Description

  Configure Laravel queued jobs with safe timeouts, retries, uniqueness, and failure handling for production reliability.

  <a name="recommended-situation"></a>
  ## Recommended Situation

  Use when queued jobs run outside the `sync` driver or handle important external or user-facing work.

  <a name="human-guidance"></a>
  ## Human Guidance

  Beyond deferring business logic to jobs (see [Keep Commands Small and Defer to Jobs](../keep-commands-small-defer-to-jobs/BEST_PRACTICE.md)), jobs themselves need proper configuration to be reliable in production. This includes setting correct timeout and retry values, implementing exponential backoff, preventing duplicate execution, handling failures explicitly, and rate limiting external API calls.

  <a name="why"></a>
  ### Why

  - **Prevents duplicate execution**: When `retry_after` is shorter than `timeout`, the queue worker re-dispatches the job while it's still running
  - **Protects external services**: Exponential backoff and rate limiting prevent hammering failing APIs
  - **Explicit failure handling**: Implementing `failed()` ensures errors are handled rather than silently ignored
  - **Controlled concurrency**: `ShouldBeUnique` and `WithoutOverlapping` prevent duplicate and concurrent processing of the same data

  <a name="suitable-for"></a>
  ### Suitable For

  - Applications using queued jobs in production
  - Jobs that call external APIs or process critical data
  - Multi-worker or multi-server queue deployments
  - Jobs processing user-facing operations where duplicates or failures are visible

  <a name="less-suitable"></a>
  ### Less Suitable

  - Jobs using the `sync` queue driver (development/testing only)
  - Simple fire-and-forget jobs where failures are acceptable

  <a name="examples"></a>
  ### Examples

  #### Set `retry_after` Greater Than `timeout`

  ```php
  class ProcessReport implements ShouldQueue
  {
      public $timeout = 120;
  }

  // config/queue.php — retry_after must be longer than any job timeout
  // retry_after: 180 ← safely longer
  ```

  #### Use Exponential Backoff

  ```php
  class SyncWithStripe implements ShouldQueue
  {
      public $tries = 3;
      public $backoff = [1, 5, 10]; // seconds between retries
  }
  ```

  #### Prevent Duplicate Job Processing

  ```php
  class GenerateInvoice implements ShouldQueue, ShouldBeUnique
  {
      public function uniqueId(): string
      {
          return $this->order->id;
      }

      public $uniqueFor = 3600;
  }
  ```

  #### Always Implement `failed()`

  ```php
  public function failed(?Throwable $exception): void
  {
      $this->podcast->update(['status' => 'failed']);
      Log::error('Processing failed', [
          'id' => $this->podcast->id,
          'error' => $exception->getMessage(),
      ]);
  }
  ```

  #### Rate Limit External API Calls

  ```php
  public function middleware(): array
  {
      return [new RateLimited('external-api')];
  }
  ```

  #### Batch Related Jobs

  ```php
  Bus::batch([
      new ImportCsvChunk($chunk1),
      new ImportCsvChunk($chunk2),
  ])
  ->then(fn (Batch $batch) => Notification::send($user, new ImportComplete))
  ->catch(fn (Batch $batch, Throwable $e) => Log::error('Batch failed'))
  ->dispatch();
  ```

  #### Use `WithoutOverlapping` for Concurrency Control

  ```php
  public function middleware(): array
  {
      return [
          (new WithoutOverlapping($this->product->id))
              ->releaseAfter(60)
              ->expireAfter(180),
      ];
  }
  ```

  Use `releaseAfter()` to decide how long overlapping jobs should wait before being released back to the queue, and `expireAfter()` to ensure the lock eventually expires if the worker crashes or the job times out unexpectedly.

  <a name="more-info"></a>
  ### More Info

  - [Laravel Queue Documentation](https://laravel.com/docs/queues)
  - [Laravel Job Middleware Documentation](https://laravel.com/docs/queues#job-middleware)
  - [Laravel Horizon Documentation](https://laravel.com/docs/horizon)
  - [Keep Commands Small and Defer to Jobs](../keep-commands-small-defer-to-jobs/BEST_PRACTICE.md)
  - [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)

  <a name="boost-guideline"></a>
  ## Boost Guideline

  ```md
  ---
  title: Configure Queued Jobs Properly
  description: Configure Laravel queued jobs with safe timeouts, retries, uniqueness, and failure handling for production reliability.
  recommended_situation: Use when queued jobs run outside the `sync` driver or handle important external or user-facing work.
  ---

  - Set job `timeout`, `tries`, and queue `retry_after` coherently so long-running jobs are not retried while still executing.
  - Use exponential backoff, rate limiting middleware, and explicit uniqueness or overlap controls for jobs that hit external services or shared resources.
  - Implement `failed()` handling for jobs where failure state, cleanup, or observability matters.
  - Use batching and queue middleware deliberately when coordinating related jobs or controlling concurrency.
  ```
best_practice_categories:
  - project-structure-and-code-architecture
category_slug: project-structure-and-code-architecture
category_title: 'Projectstructuur en architectuur'
category_title_en: 'Project Structure and Code Architecture'
source_path: project-structure-and-code-architecture/configure-queued-jobs-properly/BEST_PRACTICE.md
source_sha: c7034be11f69954eac43e50486b6e5bebde98c46
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/project-structure-and-code-architecture/configure-queued-jobs-properly/BEST_PRACTICE.md'
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

  - Full best practice: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/project-structure-and-code-architecture/configure-queued-jobs-properly/BEST_PRACTICE.md
  - Dutch translation: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/project-structure-and-code-architecture/configure-queued-jobs-properly/translations/nl.md

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
skill_source_path: project-structure-and-code-architecture/configure-queued-jobs-properly/skill/SKILL.md
skill_github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/project-structure-and-code-architecture/configure-queued-jobs-properly/skill/SKILL.md'
skill_references: []
---
