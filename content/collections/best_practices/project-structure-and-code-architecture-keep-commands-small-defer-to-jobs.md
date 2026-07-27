---
id: b30ea772-a967-52e7-abfb-37dc05f8d477
blueprint: best_practices
title: 'Houd Commands Klein en Verplaats Business Logic naar Jobs'
title_nl: 'Houd Commands Klein en Verplaats Business Logic naar Jobs'
title_en: 'Keep Commands Small and Defer Business Logic to Jobs'
summary_nl: 'Laravel console commands moeten lichtgewicht blijven en gericht zijn op het afhandelen van command-line input/output, terwijl complexe business logic naar queued jobs verplaatst moet worden. Dit architectuurpatroon scheidt de verantwoordeli...'
summary_en: 'Laravel console commands should be kept lightweight and focused on handling command-line input/output, while complex business logic should be deferred to queued jobs. This architectural pattern separates concerns between the command layer (...'
chapters_nl:
  - title: Introductie
    anchor: introductie
  - title: Waarom
    anchor: waarom
  - title: 'Geschikt Voor'
    anchor: geschikt-voor
  - title: 'Minder Geschikt'
    anchor: minder-geschikt
  - title: Implementatie
    anchor: implementatie
  - title: 'Veelvoorkomende Valkuilen'
    anchor: veelvoorkomende-valkuilen
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
  - title: Implementation
    anchor: implementation
  - title: 'Common Pitfalls'
    anchor: common-pitfalls
  - title: 'More Info'
    anchor: more-info
content_nl: |-
  <a name="introduction"></a>
  ## Introductie

  Laravel console commands moeten lichtgewicht blijven en gericht zijn op het afhandelen van command-line input/output, terwijl complexe business logic naar queued jobs verplaatst moet worden. Dit architectuurpatroon scheidt de verantwoordelijkheden tussen de command-laag (verantwoordelijk voor CLI-interactie) en de business logic-laag (afgehandeld door jobs), wat resulteert in beter onderhoudbare en schaalbare applicaties.

  <a name="why"></a>
  ## Waarom

  - **Betere Performance**: Commands die zware business logic synchroon uitvoeren kunnen timeouts veroorzaken of andere processen blokkeren. Jobs maken asynchrone verwerking mogelijk, wat de uitvoering van commands drastisch versnelt.

  - **Verbeterde Herbruikbaarheid**: Wanneer business logic in jobs wordt ondergebracht in plaats van in commands, wordt het eenvoudig voor controllers, andere jobs en commands om dezelfde logic te gebruiken.

  - **Schaalbaarheid**: Jobs kunnen verdeeld worden over meerdere workers en queues, wat betere benutting van resources en de mogelijkheid om verwerking op basis van vraag te schalen mogelijk maakt.

  - **Verbeterde Foutafhandeling**: Jobs bieden robuuste mechanismen voor foutafhandeling, waaronder automatische retry-mogelijkheden met configureerbare backoff-strategieën, afhandeling van mislukte jobs en uitgebreide monitoring.

  - **Efficiëntie van de Scheduler**: Wanneer meerdere geplande commands tegelijkertijd draaien, voorkomen lichtgewicht commands die snel jobs dispatchen dat de scheduler geblokkeerd raakt, zodat alle geplande taken op tijd draaien.

  - **Beter Testen**: Business logic in jobs kan onafhankelijk van command-line-aspecten getest worden, wat leidt tot meer gerichte en onderhoudbare tests.

  <a name="suitable-for"></a>
  ## Geschikt Voor

  - **Geplande Commands**: Taken die op een schema draaien en dataverwerking, imports, exports of andere tijdrovende operaties uitvoeren
  - **Langlopende Operaties**: Commands die grote datasets verwerken, meerdere API-calls maken of complexe berekeningen uitvoeren
  - **Operaties die Retry-Logic Vereisen**: Taken die kunnen mislukken door externe afhankelijkheden (API's, third-party services)
  - **Batchverwerking**: Commands die meerdere items moeten verwerken waarbij elk item onafhankelijk verwerkt kan worden
  - **Resource-Intensieve Taken**: Operaties die significant geheugen of CPU-resources verbruiken

  <a name="less-suitable"></a>
  ## Minder Geschikt

  - **Interactieve Commands**: Commands die tijdens de uitvoering gebruikersinvoer of realtime feedback vereisen
  - **Eenvoudige Database Queries**: Snelle operaties zoals het legen van de cache of eenvoudige database-opschoningen die binnen enkele seconden klaar zijn
  - **Development/Debugging Commands**: Eenmalige commands die gebruikt worden voor debugging- of ontwikkeldoeleinden
  - **Commands die Directe Resultaten Vereisen**: Operaties waarbij het command moet wachten op het resultaat en dit direct moet weergeven

  <a name="implementation"></a>
  ## Implementatie

  ### Basispatroon

  **Command (Klein en Gericht):**
  ```php
  namespace App\Console\Commands;

  use App\Jobs\ProcessDataJob;
  use Illuminate\Console\Command;

  class ProcessDataCommand extends Command
  {
      protected $signature = 'data:process {type}';
      protected $description = 'Queue data processing job';

      public function handle()
      {
          $type = $this->argument('type');

          // Minimal logic - just dispatch the job
          ProcessDataJob::dispatch($type);

          $this->info("Data processing job queued for type: {$type}");

          return 0;
      }
  }
  ```

  **Job (Bevat Business Logic):**
  ```php
  namespace App\Jobs;

  use App\Services\DataProcessor;
  use Illuminate\Bus\Queueable;
  use Illuminate\Contracts\Queue\ShouldQueue;
  use Illuminate\Foundation\Bus\Dispatchable;
  use Illuminate\Queue\InteractsWithQueue;
  use Illuminate\Queue\SerializesModels;

  class ProcessDataJob implements ShouldQueue
  {
      use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

      public $tries = 3;
      public $timeout = 120;

      public function __construct(
          private string $type
      ) {}

      public function handle(DataProcessor $processor)
      {
          // All the heavy business logic goes here
          $processor->processType($this->type);
      }

      public function failed(\Throwable $exception)
      {
          // Handle job failure
          \Log::error('Data processing failed', [
              'type' => $this->type,
              'error' => $exception->getMessage()
          ]);
      }
  }
  ```

  ### Geplande Commands met Jobs

  ```php
  // In app/Console/Kernel.php
  protected function schedule(Schedule $schedule)
  {
      // Good: Command quickly dispatches job
      $schedule->command('data:process daily')
          ->daily()
          ->withoutOverlapping();

      // Better: Direct job scheduling for simple cases
      $schedule->job(new ProcessDataJob('hourly'))
          ->hourly();
  }
  ```

  ### Dubbele Jobs Voorkomen

  Gebruik voor geplande commands unieke jobs om overlap te voorkomen:

  ```php
  use Illuminate\Contracts\Queue\ShouldBeUnique;

  class ScheduledImportJob implements ShouldQueue, ShouldBeUnique
  {
      public function uniqueId(): string
      {
          return 'scheduled-import-' . $this->importType;
      }

      public function uniqueFor(): int
      {
          return 3600; // 1 hour
      }
  }
  ```

  <a name="common-pitfalls"></a>
  ## Veelvoorkomende Valkuilen

  ### De Sync Queue Driver Gebruiken
  De belangrijkste valkuil is het gebruik van de standaard `sync`-driver, die jobs synchroon uitvoert en zo het hele doel van dit patroon tenietdoet:

  ```php
  // BAD: Job blocks the command with sync driver
  QUEUE_CONNECTION=sync

  // GOOD: Job runs asynchronously
  QUEUE_CONNECTION=redis  # or database, sqs, beanstalkd
  ```

  Bij gebruik van de `sync`-driver:
  - Jobs worden direct uitgevoerd in hetzelfde proces als het command
  - Commands blokkeren totdat de job voltooid is
  - Er zijn geen retry-mogelijkheden beschikbaar
  - Mislukte jobs worden niet bijgehouden
  - Je verliest alle voordelen van het verplaatsen naar jobs

  Zorg er altijd voor dat je queue-connection geconfigureerd is voor asynchrone verwerking wanneer je dit patroon implementeert.

  <a name="more-info"></a>
  ## Meer Info

  - [Laravel Queue Documentation](https://laravel.com/docs/queues)
  - [Laravel Task Scheduling Documentation](https://laravel.com/docs/scheduling)
  - [Laravel Console Commands Documentation](https://laravel.com/docs/artisan)
  - [Supervisor Configuration for Laravel](https://laravel.com/docs/queues#supervisor-configuration)
  - [Laravel Horizon for Queue Monitoring](https://laravel.com/docs/horizon)
  - [Laravel Queue Workers Documentation](https://laravel.com/docs/queues#running-the-queue-worker)
  - [Configure Queued Jobs Properly](../../configure-queued-jobs-properly/translations/nl.md) — voor geavanceerde jobconfiguratie zoals backoff, rate limiting en batching
content_en: |-
  <a name="introduction"></a>
  ## Introduction

  Laravel console commands should be kept lightweight and focused on handling command-line input/output, while complex business logic should be deferred to queued jobs. This architectural pattern separates concerns between the command layer (responsible for CLI interaction) and the business logic layer (handled by jobs), resulting in more maintainable and scalable applications.

  <a name="why"></a>
  ## Why

  - **Better Performance**: Commands that execute heavy business logic synchronously can cause timeouts or block other processes. Jobs allow asynchronous processing, drastically speeding up command execution.

  - **Improved Reusability**: When business logic is encapsulated in jobs rather than commands, it becomes easy for controllers, other jobs, and commands to use the same logic.

  - **Scalability**: Jobs can be distributed across multiple workers and queues, allowing better resource utilization and the ability to scale processing based on demand.

  - **Enhanced Error Handling**: Jobs provide robust error handling mechanisms including automatic retry capabilities with configurable backoff strategies, failed job handling, and comprehensive monitoring.

  - **Scheduler Efficiency**: When multiple scheduled commands run simultaneously, lightweight commands that quickly dispatch jobs prevent blocking the scheduler, ensuring all scheduled tasks run on time.

  - **Better Testing**: Business logic in jobs can be tested independently from command-line concerns, leading to more focused and maintainable tests.

  <a name="suitable-for"></a>
  ## Suitable For

  - **Scheduled Commands**: Tasks that run on a schedule and perform data processing, imports, exports, or other time-consuming operations
  - **Long-Running Operations**: Commands that process large datasets, make multiple API calls, or perform complex calculations
  - **Operations Requiring Retry Logic**: Tasks that might fail due to external dependencies (APIs, third-party services)
  - **Batch Processing**: Commands that need to process multiple items where each item could be processed independently
  - **Resource-Intensive Tasks**: Operations that consume significant memory or CPU resources

  <a name="less-suitable"></a>
  ## Less Suitable

  - **Interactive Commands**: Commands that require user input or real-time feedback during execution
  - **Simple Database Queries**: Quick operations like cache clearing or simple database cleanups that complete in seconds
  - **Development/Debugging Commands**: One-off commands used for debugging or development purposes
  - **Commands Requiring Immediate Results**: Operations where the command must wait for and display the result immediately

  <a name="implementation"></a>
  ## Implementation

  ### Basic Pattern

  **Command (Small and Focused):**
  ```php
  namespace App\Console\Commands;

  use App\Jobs\ProcessDataJob;
  use Illuminate\Console\Command;

  class ProcessDataCommand extends Command
  {
      protected $signature = 'data:process {type}';
      protected $description = 'Queue data processing job';

      public function handle()
      {
          $type = $this->argument('type');

          // Minimal logic - just dispatch the job
          ProcessDataJob::dispatch($type);

          $this->info("Data processing job queued for type: {$type}");

          return 0;
      }
  }
  ```

  **Job (Contains Business Logic):**
  ```php
  namespace App\Jobs;

  use App\Services\DataProcessor;
  use Illuminate\Bus\Queueable;
  use Illuminate\Contracts\Queue\ShouldQueue;
  use Illuminate\Foundation\Bus\Dispatchable;
  use Illuminate\Queue\InteractsWithQueue;
  use Illuminate\Queue\SerializesModels;

  class ProcessDataJob implements ShouldQueue
  {
      use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

      public $tries = 3;
      public $timeout = 120;

      public function __construct(
          private string $type
      ) {}

      public function handle(DataProcessor $processor)
      {
          // All the heavy business logic goes here
          $processor->processType($this->type);
      }

      public function failed(\Throwable $exception)
      {
          // Handle job failure
          \Log::error('Data processing failed', [
              'type' => $this->type,
              'error' => $exception->getMessage()
          ]);
      }
  }
  ```

  ### Scheduled Commands with Jobs

  ```php
  // In app/Console/Kernel.php
  protected function schedule(Schedule $schedule)
  {
      // Good: Command quickly dispatches job
      $schedule->command('data:process daily')
          ->daily()
          ->withoutOverlapping();

      // Better: Direct job scheduling for simple cases
      $schedule->job(new ProcessDataJob('hourly'))
          ->hourly();
  }
  ```

  ### Preventing Duplicate Jobs

  For scheduled commands, use unique jobs to prevent overlap:

  ```php
  use Illuminate\Contracts\Queue\ShouldBeUnique;

  class ScheduledImportJob implements ShouldQueue, ShouldBeUnique
  {
      public function uniqueId(): string
      {
          return 'scheduled-import-' . $this->importType;
      }

      public function uniqueFor(): int
      {
          return 3600; // 1 hour
      }
  }
  ```

  <a name="common-pitfalls"></a>
  ## Common Pitfalls

  ### Using Sync Queue Driver
  The most important pitfall is using the default `sync` driver, which executes jobs synchronously and defeats the entire purpose of this pattern:

  ```php
  // BAD: Job blocks the command with sync driver
  QUEUE_CONNECTION=sync

  // GOOD: Job runs asynchronously
  QUEUE_CONNECTION=redis  # or database, sqs, beanstalkd
  ```

  When using the `sync` driver:
  - Jobs execute immediately in the same process as the command
  - Commands will block until the job completes
  - No retry capabilities are available
  - Failed jobs aren't tracked
  - You lose all benefits of deferring to jobs

  Always ensure your queue connection is configured for asynchronous processing when implementing this pattern.

  <a name="more-info"></a>
  ## More Info

  - [Laravel Queue Documentation](https://laravel.com/docs/queues)
  - [Laravel Task Scheduling Documentation](https://laravel.com/docs/scheduling)
  - [Laravel Console Commands Documentation](https://laravel.com/docs/artisan)
  - [Supervisor Configuration for Laravel](https://laravel.com/docs/queues#supervisor-configuration)
  - [Laravel Horizon for Queue Monitoring](https://laravel.com/docs/horizon)
  - [Laravel Queue Workers Documentation](https://laravel.com/docs/queues#running-the-queue-worker)
  - [Configure Queued Jobs Properly](../configure-queued-jobs-properly/BEST_PRACTICE.md) — for advanced job configuration like backoff, rate limiting, and batching
best_practice_categories:
  - project-structure-and-code-architecture
category_slug: project-structure-and-code-architecture
category_title: 'Projectstructuur en architectuur'
category_title_en: 'Project Structure and Code Architecture'
source_path: project-structure-and-code-architecture/keep-commands-small-defer-to-jobs/BEST_PRACTICE.md
source_sha: c7034be11f69954eac43e50486b6e5bebde98c46
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/project-structure-and-code-architecture/keep-commands-small-defer-to-jobs/BEST_PRACTICE.md'
has_skill: true
skill_content: |-
  Use this skill when a Laravel task touches this best practice. It is self-contained so it can be installed independently by Laravel Boost or another agent-skill system.

  ## Core Guidance

  Laravel console commands should be kept lightweight and focused on handling command-line input/output, while complex business logic should be deferred to queued jobs. This architectural pattern separates concerns between the command layer (responsible for CLI interaction) and the business logic layer (handled by jobs), resulting in more maintainable and scalable applications.

  ## Why It Matters

  - **Better Performance**: Commands that execute heavy business logic synchronously can cause timeouts or block other processes. Jobs allow asynchronous processing, drastically speeding up command execution.
  - **Improved Reusability**: When business logic is encapsulated in jobs rather than commands, it becomes easy for controllers, other jobs, and commands to use the same logic.
  - **Scalability**: Jobs can be distributed across multiple workers and queues, allowing better resource utilization and the ability to scale processing based on demand.
  - **Enhanced Error Handling**: Jobs provide robust error handling mechanisms including automatic retry capabilities with configurable backoff strategies, failed job handling, and comprehensive monitoring.
  - **Scheduler Efficiency**: When multiple scheduled commands run simultaneously, lightweight commands that quickly dispatch jobs prevent blocking the scheduler, ensuring all scheduled tasks run on time.
  - **Better Testing**: Business logic in jobs can be tested independently from command-line concerns, leading to more focused and maintainable tests.

  ## Apply When

  - **Scheduled Commands**: Tasks that run on a schedule and perform data processing, imports, exports, or other time-consuming operations
  - **Long-Running Operations**: Commands that process large datasets, make multiple API calls, or perform complex calculations
  - **Operations Requiring Retry Logic**: Tasks that might fail due to external dependencies (APIs, third-party services)
  - **Batch Processing**: Commands that need to process multiple items where each item could be processed independently
  - **Resource-Intensive Tasks**: Operations that consume significant memory or CPU resources

  ## Be Careful When

  - **Interactive Commands**: Commands that require user input or real-time feedback during execution
  - **Simple Database Queries**: Quick operations like cache clearing or simple database cleanups that complete in seconds
  - **Development/Debugging Commands**: One-off commands used for debugging or development purposes
  - **Commands Requiring Immediate Results**: Operations where the command must wait for and display the result immediately

  ## Canonical Source

  - Full best practice: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/project-structure-and-code-architecture/keep-commands-small-defer-to-jobs/BEST_PRACTICE.md
  - Dutch translation: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/project-structure-and-code-architecture/keep-commands-small-defer-to-jobs/translations/nl.md

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
skill_source_path: project-structure-and-code-architecture/keep-commands-small-defer-to-jobs/skill/SKILL.md
skill_github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/project-structure-and-code-architecture/keep-commands-small-defer-to-jobs/skill/SKILL.md'
skill_references: []
---
