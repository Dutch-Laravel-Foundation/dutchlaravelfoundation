---
id: 3cd031de-3526-5620-80bf-b7d7e53e7184
blueprint: best_practices
title: 'Gebruik route model binding'
title_nl: 'Gebruik route model binding'
title_en: 'Use Route Model Binding'
summary_nl: 'Laravels impliciete route model binding resolveert Eloquent-modellen automatisch vanuit route-parameters, waardoor handmatige findOrFail()-aanroepen overbodig worden. In combinatie met scoped bindings voor geneste resources en resource cont...'
summary_en: "Laravel's implicit route model binding automatically resolves Eloquent models from route parameters, eliminating manual findOrFail() calls. Combined with scoped bindings for nested resources and resource controllers, this keeps routing code..."
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

  Laravels impliciete route model binding resolveert Eloquent-modellen automatisch vanuit route-parameters, waardoor handmatige `findOrFail()`-aanroepen overbodig worden. In combinatie met scoped bindings voor geneste resources en resource controllers houdt dit routingcode beknopt, consistent en minder foutgevoelig.

  <a name="why"></a>
  ## Waarom

  - **Minder boilerplate**: Geen handmatige `findOrFail()`- of `find()`-aanroepen nodig, Laravel resolveert het model automatisch
  - **Automatische 404-afhandeling**: Als het model niet gevonden wordt, geeft Laravel een 404-response terug zonder extra code
  - **Afdwingen van parent-child-relaties**: Scoped bindings zorgen ervoor dat geneste resources daadwerkelijk bij hun parent horen, wat ongeautoriseerde toegang voorkomt
  - **RESTful consistentie**: Resource controllers dwingen standaard CRUD-naamgevingsconventies af in de hele applicatie

  <a name="suitable-for"></a>
  ## Geschikt voor

  - Elke route die op een specifieke model-instantie werkt
  - Geneste resource-routes (bijv. `/users/{user}/posts/{post}`)
  - RESTful API's en CRUD-controllers
  - Applicaties waar consistente URL-patronen de developer experience verbeteren

  <a name="less-suitable"></a>
  ## Minder geschikt voor

  - Routes die aangepaste resolutielogica nodig hebben die verder gaat dan eenvoudige key-lookups
  - Endpoints die niet op specifieke model-instanties werken
  - Legacy-routes met niet-standaard parameternaamgeving

  <a name="examples"></a>
  ## Voorbeelden

  ### Impliciete route model binding

  ```php
  // Slecht: handmatige resolutie
  public function show(int $id)
  {
      $post = Post::findOrFail($id);
  }

  // Goed: automatische resolutie met type-hinting
  public function show(Post $post)
  {
      return view('posts.show', ['post' => $post]);
  }
  ```

  ### Scoped bindings voor geneste resources

  Dwing parent-child-relaties automatisch af:

  ```php
  Route::get('/users/{user}/posts/{post}', function (User $user, Post $post) {
      // $post wordt automatisch gescoped op $user
  })->scopeBindings();
  ```

  ### Gebruik resource controllers

  ```php
  Route::resource('posts', PostController::class);
  Route::apiResource('api/posts', Api\PostController::class);
  ```

  <a name="more-info"></a>
  ## Meer info

  - [Laravel Route Model Binding Documentatie](https://laravel.com/docs/routing#route-model-binding)
  - [Laravel Resource Controllers Documentatie](https://laravel.com/docs/controllers#resource-controllers)
  - [Gebruik Action Classes voor Bedrijfslogica](../../../project-structure-and-code-architecture/use-action-classes-for-business-logic/translations/nl.md)
  - [Gebruik Form Request Classes](../../use-form-request-classes/translations/nl.md)
  - [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)
content_en: |-
  <a name="introduction"></a>
  ## Introduction

  Laravel's implicit route model binding automatically resolves Eloquent models from route parameters, eliminating manual `findOrFail()` calls. Combined with scoped bindings for nested resources and resource controllers, this keeps routing code concise, consistent, and less error-prone.

  <a name="why"></a>
  ## Why

  - **Less boilerplate**: No need for manual `findOrFail()` or `find()` calls, Laravel resolves the model automatically
  - **Automatic 404 handling**: If the model isn't found, Laravel returns a 404 response without any extra code
  - **Parent-child enforcement**: Scoped bindings ensure nested resources actually belong to their parent, preventing unauthorized access
  - **RESTful consistency**: Resource controllers enforce standard CRUD naming conventions across the application

  <a name="suitable-for"></a>
  ## Suitable For

  - Any route that operates on a specific model instance
  - Nested resource routes (e.g., `/users/{user}/posts/{post}`)
  - RESTful APIs and CRUD controllers
  - Applications where consistent URL patterns improve developer experience

  <a name="less-suitable"></a>
  ## Less Suitable

  - Routes that need custom resolution logic beyond simple key lookups
  - Endpoints that don't operate on specific model instances
  - Legacy routes with non-standard parameter naming

  <a name="examples"></a>
  ## Examples

  ### Implicit Route Model Binding

  ```php
  // Bad: manual resolution
  public function show(int $id)
  {
      $post = Post::findOrFail($id);
  }

  // Good: automatic resolution with type-hinting
  public function show(Post $post)
  {
      return view('posts.show', ['post' => $post]);
  }
  ```

  ### Scoped Bindings for Nested Resources

  Enforce parent-child relationships automatically:

  ```php
  Route::get('/users/{user}/posts/{post}', function (User $user, Post $post) {
      // $post is automatically scoped to $user
  })->scopeBindings();
  ```

  ### Use Resource Controllers

  ```php
  Route::resource('posts', PostController::class);
  Route::apiResource('api/posts', Api\PostController::class);
  ```

  <a name="more-info"></a>
  ## More Info

  - [Laravel Route Model Binding Documentation](https://laravel.com/docs/routing#route-model-binding)
  - [Laravel Resource Controllers Documentation](https://laravel.com/docs/controllers#resource-controllers)
  - [Use Action Classes for Business Logic](../../project-structure-and-code-architecture/use-action-classes-for-business-logic/BEST_PRACTICE.md), keep controllers thin by extracting logic to action classes
  - [Use Form Request Classes](../use-form-request-classes/BEST_PRACTICE.md), extract validation from controllers into Form Requests
  - [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)
best_practice_categories:
  - routing
category_slug: routing
category_title: Routing
category_title_en: Routing
source_path: routing/use-route-model-binding/BEST_PRACTICE.md
source_sha: c7034be11f69954eac43e50486b6e5bebde98c46
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/routing/use-route-model-binding/BEST_PRACTICE.md'
has_skill: true
skill_content: |-
  Use this skill when a Laravel task touches this best practice. It is self-contained so it can be installed independently by Laravel Boost or another agent-skill system.

  ## Core Guidance

  Laravel's implicit route model binding automatically resolves Eloquent models from route parameters, eliminating manual `findOrFail()` calls. Combined with scoped bindings for nested resources and resource controllers, this keeps routing code concise, consistent, and less error-prone.

  ## Why It Matters

  - **Less boilerplate**: No need for manual `findOrFail()` or `find()` calls, Laravel resolves the model automatically
  - **Automatic 404 handling**: If the model isn't found, Laravel returns a 404 response without any extra code
  - **Parent-child enforcement**: Scoped bindings ensure nested resources actually belong to their parent, preventing unauthorized access
  - **RESTful consistency**: Resource controllers enforce standard CRUD naming conventions across the application

  ## Apply When

  - Any route that operates on a specific model instance
  - Nested resource routes (e.g., `/users/{user}/posts/{post}`)
  - RESTful APIs and CRUD controllers
  - Applications where consistent URL patterns improve developer experience

  ## Be Careful When

  - Routes that need custom resolution logic beyond simple key lookups
  - Endpoints that don't operate on specific model instances
  - Legacy routes with non-standard parameter naming

  ## Canonical Source

  - Full best practice: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/routing/use-route-model-binding/BEST_PRACTICE.md
  - Dutch translation: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/routing/use-route-model-binding/translations/nl.md

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
skill_source_path: routing/use-route-model-binding/skill/SKILL.md
skill_github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/routing/use-route-model-binding/skill/SKILL.md'
skill_references: []
synced_at: 1785231871
---
