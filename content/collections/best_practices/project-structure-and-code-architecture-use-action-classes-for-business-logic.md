---
id: c6cd596d-fae0-5df3-b191-d9b3ab6750d1
blueprint: best_practices
title: 'Gebruik Action-classes voor businesslogica'
title_nl: 'Gebruik Action-classes voor businesslogica'
title_en: 'Use Action Classes for Business Logic'
summary_nl: 'Kapsel herbruikbare businessoperaties in met één doel gerichte action-classes met expliciete afhankelijkheden.'
summary_en: 'Encapsulate reusable business operations in single-purpose action classes with explicit dependencies.'
chapters_nl:
  - title: Beschrijving
    anchor: beschrijving
  - title: 'Aanbevolen situatie'
    anchor: aanbevolen-situatie
  - title: 'Menselijke begeleiding'
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

  Kapsel herbruikbare businessoperaties in met één doel gerichte action-classes met expliciete afhankelijkheden.

  <a name="recommended-situation"></a>
  ## Aanbevolen situatie

  Gebruik dit wanneer businesslogica omvangrijk is, hergebruikt wordt over meerdere ingangen, of afhankelijk is van externe services.

  <a name="human-guidance"></a>
  ## Menselijke begeleiding

  Action-classes zijn aanroepbare classes met één doel die één afzonderlijke businessoperatie inkapselen. In combinatie met constructor dependency injection en het programmeren tegen interfaces op systeemgrenzen houden ze controllers dun, businesslogica testbaar en externe afhankelijkheden uitwisselbaar.

  <a name="why"></a>
  ### Waarom

  - **Single responsibility**: Elke action-class doet één ding goed, waardoor businesslogica eenvoudig te vinden, te testen en aan te passen is
  - **Herbruikbaarheid**: Dezelfde action kan worden aangeroepen vanuit controllers, commands, jobs en andere actions
  - **Testbaarheid**: Constructor injection maakt afhankelijkheden expliciet en eenvoudig te mocken
  - **Uitwisselbaarheid**: Programmeren tegen interfaces op systeemgrenzen (payment gateways, notificatiekanalen, externe API's) maakt het mogelijk implementaties te vervangen zonder de businesslogica te wijzigen

  <a name="suitable-for"></a>
  ### Geschikt voor

  - Businessoperaties die vanuit meerdere plekken worden aangeroepen (controllers, commands, jobs)
  - Operaties met externe afhankelijkheden die in tests mockbaar moeten zijn
  - Complexe operaties die controllers of jobs te groot zouden maken
  - Applicaties met meerdere integratiepunten (payment providers, verzendservices, enz.)

  <a name="less-suitable"></a>
  ### Minder geschikt

  - Eenvoudige CRUD-operaties die maar op één plek worden gebruikt
  - Operaties waarbij één Eloquent-aanroep volstaat
  - Prototyping of wegwerpcode waar de overhead niet gerechtvaardigd is

  <a name="examples"></a>
  ### Voorbeelden

  #### Action-class met één doel

  ```php
  class CreateOrderAction
  {
      public function __construct(private InventoryService $inventory) {}

      public function execute(array $data): Order
      {
          $order = Order::create($data);
          $this->inventory->reserve($order);

          return $order;
      }
  }
  ```

  #### Gebruik dependency injection

  Gebruik altijd constructor injection. Vermijd `app()` of `resolve()` binnen classes:

  ```php
  // Slecht: service locator-patroon
  class OrderController extends Controller
  {
      public function store(StoreOrderRequest $request)
      {
          $service = app(OrderService::class);

          return $service->create($request->validated());
      }
  }

  // Goed: constructor injection
  class OrderController extends Controller
  {
      public function __construct(private OrderService $service) {}

      public function store(StoreOrderRequest $request)
      {
          return $this->service->create($request->validated());
      }
  }
  ```

  #### Programmeer tegen interfaces op systeemgrenzen

  Steun op contracts voor externe integraties om testbaarheid en uitwisselbaarheid mogelijk te maken:

  ```php
  // Slecht: concrete afhankelijkheid
  class OrderService
  {
      public function __construct(private StripeGateway $gateway) {}
  }

  // Goed: interface-afhankelijkheid
  interface PaymentGateway
  {
      public function charge(int $amount, string $customerId): PaymentResult;
  }

  class OrderService
  {
      public function __construct(private PaymentGateway $gateway) {}
  }
  ```

  Bind in een service provider:

  ```php
  $this->app->bind(PaymentGateway::class, StripeGateway::class);
  ```

  <a name="more-info"></a>
  ### Meer informatie

  - [Laravel Service Container Documentation](https://laravel.com/docs/container)
  - [Laravel Service Providers Documentation](https://laravel.com/docs/providers)
  - [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)

  <a name="boost-guideline"></a>
  ## Boost Guideline

  ```md
  ---
  title: Use Action Classes for Business Logic
  description: Encapsulate reusable business operations in single-purpose action classes with explicit dependencies.
  recommended_situation: Use when business logic is substantial, reused across entry points, or depends on external services.
  ---

  - Extract substantial business operations into focused action classes instead of embedding them in controllers, commands, or jobs.
  - Give each action one clear responsibility and inject its collaborators through the constructor.
  - Reuse actions across entry points when the same operation is triggered from HTTP, CLI, queues, or events.
  - Depend on interfaces at external system boundaries so business logic remains testable and implementations stay swappable.
  ```
content_en: |-
  <a name="description"></a>
  ## Description

  Encapsulate reusable business operations in single-purpose action classes with explicit dependencies.

  <a name="recommended-situation"></a>
  ## Recommended Situation

  Use when business logic is substantial, reused across entry points, or depends on external services.

  <a name="human-guidance"></a>
  ## Human Guidance

  Action classes are single-purpose, invokable classes that encapsulate one discrete business operation. Combined with constructor dependency injection and coding to interfaces at system boundaries, they keep controllers thin, business logic testable, and external dependencies swappable.

  <a name="why"></a>
  ### Why

  - **Single responsibility**: Each action class does one thing well, making it easy to find, test, and modify business logic
  - **Reusability**: The same action can be called from controllers, commands, jobs, and other actions
  - **Testability**: Constructor injection makes dependencies explicit and easy to mock
  - **Swappability**: Coding to interfaces at system boundaries (payment gateways, notification channels, external APIs) allows swapping implementations without changing business logic

  <a name="suitable-for"></a>
  ### Suitable For

  - Business operations that are called from multiple places (controllers, commands, jobs)
  - Operations with external dependencies that should be mockable in tests
  - Complex operations that would make controllers or jobs too large
  - Applications with multiple integration points (payment providers, shipping services, etc.)

  <a name="less-suitable"></a>
  ### Less Suitable

  - Simple CRUD operations that are only used in one place
  - Operations where a single Eloquent call suffices
  - Prototyping or throwaway code where the overhead isn't justified

  <a name="examples"></a>
  ### Examples

  #### Single-Purpose Action Class

  ```php
  class CreateOrderAction
  {
      public function __construct(private InventoryService $inventory) {}

      public function execute(array $data): Order
      {
          $order = Order::create($data);
          $this->inventory->reserve($order);

          return $order;
      }
  }
  ```

  #### Use Dependency Injection

  Always use constructor injection. Avoid `app()` or `resolve()` inside classes:

  ```php
  // Bad: service locator pattern
  class OrderController extends Controller
  {
      public function store(StoreOrderRequest $request)
      {
          $service = app(OrderService::class);

          return $service->create($request->validated());
      }
  }

  // Good: constructor injection
  class OrderController extends Controller
  {
      public function __construct(private OrderService $service) {}

      public function store(StoreOrderRequest $request)
      {
          return $this->service->create($request->validated());
      }
  }
  ```

  #### Code to Interfaces at System Boundaries

  Depend on contracts for external integrations to enable testability and swappability:

  ```php
  // Bad: concrete dependency
  class OrderService
  {
      public function __construct(private StripeGateway $gateway) {}
  }

  // Good: interface dependency
  interface PaymentGateway
  {
      public function charge(int $amount, string $customerId): PaymentResult;
  }

  class OrderService
  {
      public function __construct(private PaymentGateway $gateway) {}
  }
  ```

  Bind in a service provider:

  ```php
  $this->app->bind(PaymentGateway::class, StripeGateway::class);
  ```

  <a name="more-info"></a>
  ### More Info

  - [Laravel Service Container Documentation](https://laravel.com/docs/container)
  - [Laravel Service Providers Documentation](https://laravel.com/docs/providers)
  - [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)

  <a name="boost-guideline"></a>
  ## Boost Guideline

  ```md
  ---
  title: Use Action Classes for Business Logic
  description: Encapsulate reusable business operations in single-purpose action classes with explicit dependencies.
  recommended_situation: Use when business logic is substantial, reused across entry points, or depends on external services.
  ---

  - Extract substantial business operations into focused action classes instead of embedding them in controllers, commands, or jobs.
  - Give each action one clear responsibility and inject its collaborators through the constructor.
  - Reuse actions across entry points when the same operation is triggered from HTTP, CLI, queues, or events.
  - Depend on interfaces at external system boundaries so business logic remains testable and implementations stay swappable.
  ```
best_practice_categories:
  - project-structure-and-code-architecture
category_slug: project-structure-and-code-architecture
category_title: 'Projectstructuur en architectuur'
category_title_en: 'Project Structure and Code Architecture'
source_path: project-structure-and-code-architecture/use-action-classes-for-business-logic/BEST_PRACTICE.md
source_sha: c7034be11f69954eac43e50486b6e5bebde98c46
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/project-structure-and-code-architecture/use-action-classes-for-business-logic/BEST_PRACTICE.md'
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

  - Full best practice: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/project-structure-and-code-architecture/use-action-classes-for-business-logic/BEST_PRACTICE.md
  - Dutch translation: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/project-structure-and-code-architecture/use-action-classes-for-business-logic/translations/nl.md

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
skill_source_path: project-structure-and-code-architecture/use-action-classes-for-business-logic/skill/SKILL.md
skill_github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/project-structure-and-code-architecture/use-action-classes-for-business-logic/skill/SKILL.md'
skill_references: []
---
