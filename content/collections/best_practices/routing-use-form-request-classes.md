---
id: 8a260ed2-3a4c-502f-baa4-72ff92b1be57
blueprint: best_practices
title: 'Gebruik Form Request-classes'
title_nl: 'Gebruik Form Request-classes'
title_en: 'Use Form Request Classes'
summary_nl: 'Verplaats de validatie en autorisatie van requests naar toegewijde Form Request-classes in plaats van naar controllers.'
summary_en: 'Move request validation and authorization into dedicated Form Request classes instead of controllers.'
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

  Verplaats de validatie en autorisatie van requests naar toegewijde Form Request-classes in plaats van naar controllers.

  <a name="recommended-situation"></a>
  ## Aanbevolen situatie

  Gebruik dit voor controller-acties en endpoints die niet-triviale gebruikersinvoer accepteren.

  <a name="human-guidance"></a>
  ## Menselijke begeleiding

  Laravel Form Request-classes halen validatie- en autorisatielogica uit controllers en plaatsen deze in toegewijde classes. Door een Form Request te type-hinten in een controllermethode wordt automatisch de validatie en autorisatie uitgevoerd voordat de methode draait. Zo blijven controllers dun en is de validatielogica herbruikbaar.

  <a name="why"></a>
  ### Waarom

  - **Scheiding van verantwoordelijkheden**: Validatielogica leeft in een eigen class en vervuilt de controllermethoden niet
  - **Herbruikbaarheid**: Dezelfde Form Request kan in meerdere controllers of acties worden gebruikt
  - **Automatische uitvoering**: Het type-hinten van de Form Request voert validatie en autorisatie automatisch uit — geen handmatige `validate()`-aanroep nodig
  - **Veiligheid**: Het gebruik van `$request->validated()` zorgt ervoor dat alleen gevalideerde data aan mass operations wordt doorgegeven, waardoor niet-gevalideerde velden niet kunnen doorlekken

  <a name="suitable-for"></a>
  ### Geschikt voor

  - Elke controllermethode die gebruikersinvoer accepteert
  - Formulieren met meerdere validatieregels
  - Endpoints waar autorisatie en validatie nauw met elkaar samenhangen
  - API's waar consistente validatie-foutmeldingen belangrijk zijn

  <a name="less-suitable"></a>
  ### Minder geschikt

  - Extreem eenvoudige endpoints met één of twee triviale validatieregels
  - Closure-gebaseerde routes in prototyping- of testscenario's

  <a name="examples"></a>
  ### Voorbeelden

  #### Haal validatie naar Form Requests

  ```php
  // Bad: inline validation in controllers
  public function store(Request $request)
  {
      $request->validate([
          'title' => 'required|max:255',
          'body' => 'required',
      ]);
  }

  // Good: dedicated Form Request class
  public function store(StorePostRequest $request)
  {
      Post::create($request->validated());
  }
  ```

  #### Gebruik altijd `validated()`

  Gebruik nooit `$request->all()` voor mass operations:

  ```php
  // Bad: includes unvalidated fields
  Post::create($request->all());

  // Good: only validated data
  Post::create($request->validated());
  ```

  #### Geef de voorkeur aan array-notatie voor regels

  Array-syntax is beter leesbaar en combineert netjes met `Rule::`-objecten. Geef er de voorkeur aan in nieuwe code, maar sluit aan bij de bestaande conventie:

  ```php
  // Preferred for new code
  'email' => ['required', 'email', Rule::unique('users')],

  // Follow existing convention if the project uses string notation
  'email' => 'required|email|unique:users',
  ```

  #### Gebruik `Rule::when()` voor voorwaardelijke validatie

  ```php
  'company_name' => [
      Rule::when($this->account_type === 'business', ['required', 'string', 'max:255']),
  ],
  ```

  #### Gebruik de `after()`-methode voor custom validatie

  Gebruik `after()` in plaats van `withValidator()` voor custom validatielogica die van meerdere velden afhangt:

  ```php
  public function after(): array
  {
      return [
          function (Validator $validator) {
              if ($this->quantity > Product::find($this->product_id)?->stock) {
                  $validator->errors()->add('quantity', 'Not enough stock.');
              }
          },
      ];
  }
  ```

  <a name="more-info"></a>
  ### Meer info

  - [Laravel Form Request Validation Documentation](https://laravel.com/docs/validation#form-request-validation)
  - [Laravel Validation Rules Documentation](https://laravel.com/docs/validation#available-validation-rules)
  - [Use Route Model Binding](../../use-route-model-binding/translations/nl.md) — voor automatische model-resolutie in controllers
  - [Use Action Classes for Business Logic](../../../project-structure-and-code-architecture/use-action-classes-for-business-logic/translations/nl.md) — voor het uithalen van businesslogica uit controllers
  - [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)

  <a name="boost-guideline"></a>
  ## Boost-richtlijn

  ```md
  ---
  title: Use Form Request Classes
  description: Move request validation and authorization into dedicated Form Request classes instead of controllers.
  recommended_situation: Use for controller actions and endpoints that accept non-trivial user input.
  ---

  - Create Form Request classes for request validation and authorization instead of validating inline in controllers.
  - Type-hint the Form Request on controller actions so Laravel runs authorization and validation automatically.
  - Pass `$request->validated()` downstream for mass assignment, actions, or services; do not use `$request->all()`.
  - Prefer readable rule definitions and keep conditional or cross-field validation inside the Form Request.
  ```
content_en: |-
  <a name="description"></a>
  ## Description

  Move request validation and authorization into dedicated Form Request classes instead of controllers.

  <a name="recommended-situation"></a>
  ## Recommended Situation

  Use for controller actions and endpoints that accept non-trivial user input.

  <a name="human-guidance"></a>
  ## Human Guidance

  Laravel Form Request classes extract validation and authorization logic from controllers into dedicated classes. Type-hinting a Form Request in a controller method triggers automatic validation and authorization before the method executes. This keeps controllers thin and validation logic reusable.

  <a name="why"></a>
  ### Why

  - **Separation of concerns**: Validation logic lives in its own class, not cluttering controller methods
  - **Reusability**: The same Form Request can be used across multiple controllers or actions
  - **Automatic execution**: Type-hinting the Form Request triggers validation and authorization automatically — no manual `validate()` call needed
  - **Safety**: Using `$request->validated()` ensures only validated data is passed to mass operations, preventing unvalidated fields from leaking through

  <a name="suitable-for"></a>
  ### Suitable For

  - Any controller method that accepts user input
  - Forms with multiple validation rules
  - Endpoints where authorization and validation are closely related
  - APIs where consistent validation error responses matter

  <a name="less-suitable"></a>
  ### Less Suitable

  - Extremely simple endpoints with one or two trivial validation rules
  - Closure-based routes in prototyping or testing scenarios

  <a name="examples"></a>
  ### Examples

  #### Extract Validation into Form Requests

  ```php
  // Bad: inline validation in controllers
  public function store(Request $request)
  {
      $request->validate([
          'title' => 'required|max:255',
          'body' => 'required',
      ]);
  }

  // Good: dedicated Form Request class
  public function store(StorePostRequest $request)
  {
      Post::create($request->validated());
  }
  ```

  #### Always Use `validated()`

  Never use `$request->all()` for mass operations:

  ```php
  // Bad: includes unvalidated fields
  Post::create($request->all());

  // Good: only validated data
  Post::create($request->validated());
  ```

  #### Prefer Array Notation for Rules

  Array syntax is more readable and composes cleanly with `Rule::` objects. Prefer it in new code, but match existing convention:

  ```php
  // Preferred for new code
  'email' => ['required', 'email', Rule::unique('users')],

  // Follow existing convention if the project uses string notation
  'email' => 'required|email|unique:users',
  ```

  #### Use `Rule::when()` for Conditional Validation

  ```php
  'company_name' => [
      Rule::when($this->account_type === 'business', ['required', 'string', 'max:255']),
  ],
  ```

  #### Use the `after()` Method for Custom Validation

  Use `after()` instead of `withValidator()` for custom validation logic that depends on multiple fields:

  ```php
  public function after(): array
  {
      return [
          function (Validator $validator) {
              if ($this->quantity > Product::find($this->product_id)?->stock) {
                  $validator->errors()->add('quantity', 'Not enough stock.');
              }
          },
      ];
  }
  ```

  <a name="more-info"></a>
  ### More Info

  - [Laravel Form Request Validation Documentation](https://laravel.com/docs/validation#form-request-validation)
  - [Laravel Validation Rules Documentation](https://laravel.com/docs/validation#available-validation-rules)
  - [Use Route Model Binding](../use-route-model-binding/BEST_PRACTICE.md) — for automatic model resolution in controllers
  - [Use Action Classes for Business Logic](../../project-structure-and-code-architecture/use-action-classes-for-business-logic/BEST_PRACTICE.md) — for extracting business logic from controllers
  - [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)

  <a name="boost-guideline"></a>
  ## Boost Guideline

  ```md
  ---
  title: Use Form Request Classes
  description: Move request validation and authorization into dedicated Form Request classes instead of controllers.
  recommended_situation: Use for controller actions and endpoints that accept non-trivial user input.
  ---

  - Create Form Request classes for request validation and authorization instead of validating inline in controllers.
  - Type-hint the Form Request on controller actions so Laravel runs authorization and validation automatically.
  - Pass `$request->validated()` downstream for mass assignment, actions, or services; do not use `$request->all()`.
  - Prefer readable rule definitions and keep conditional or cross-field validation inside the Form Request.
  ```
best_practice_categories:
  - routing
category_slug: routing
category_title: Routing
category_title_en: Routing
source_path: routing/use-form-request-classes/BEST_PRACTICE.md
source_sha: c7034be11f69954eac43e50486b6e5bebde98c46
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/routing/use-form-request-classes/BEST_PRACTICE.md'
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

  - Full best practice: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/routing/use-form-request-classes/BEST_PRACTICE.md
  - Dutch translation: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/routing/use-form-request-classes/translations/nl.md

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
skill_source_path: routing/use-form-request-classes/skill/SKILL.md
skill_github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/routing/use-form-request-classes/skill/SKILL.md'
skill_references: []
synced_at: 1785159222
---
