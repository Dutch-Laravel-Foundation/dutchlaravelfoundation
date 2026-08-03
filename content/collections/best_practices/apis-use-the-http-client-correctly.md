---
id: 13e2ac3c-3ec9-516f-8fee-4e7d37d6e1ae
blueprint: best_practices
title: 'Gebruik de HTTP Client op de juiste manier'
title_nl: 'Gebruik de HTTP Client op de juiste manier'
title_en: 'Use the HTTP Client Correctly'
summary_nl: "De HTTP Client van Laravel (gebouwd op Guzzle) biedt een vloeiende interface voor het maken van HTTP-requests naar externe API's. Correct gebruik betekent het instellen van expliciete timeouts, het implementeren van retry met backoff, het c..."
summary_en: "Laravel's HTTP Client (built on Guzzle) provides a fluent interface for making HTTP requests to external APIs. Using it correctly means setting explicit timeouts, implementing retry with backoff, handling errors properly, using request pool..."
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
  - title: 'Meer informatie'
    anchor: meer-informatie
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

  De HTTP Client van Laravel (gebouwd op Guzzle) biedt een vloeiende interface voor het maken van HTTP-requests naar externe API's. Correct gebruik betekent het instellen van expliciete timeouts, het implementeren van retry met backoff, het correct afhandelen van fouten, het gebruik van request pooling voor gelijktijdige calls en het faken van requests in tests.

  <a name="why"></a>
  ## Waarom

  - **Snel falen**: Expliciete timeouts voorkomen dat requests 30+ seconden blijven hangen op API's die niet reageren
  - **Weerbaarheid**: Retry met exponentiële backoff vangt tijdelijke fouten netjes op zonder externe services te overbelasten
  - **Correctheid**: De HTTP Client gooit standaard geen exception bij 4xx/5xx, fouten moeten expliciet worden afgehandeld om te voorkomen dat foutresponsbodies stilzwijgend als data worden gebruikt
  - **Prestaties**: `Http::pool()` voert onafhankelijke requests gelijktijdig uit, waardoor sequentiële wachttijden verdwijnen
  - **Betrouwbaarheid van tests**: `Http::fake()` met `preventStrayRequests()` zorgt ervoor dat tests nooit echte API's raken en vangt niet-gemockte calls op

  <a name="suitable-for"></a>
  ## Geschikt voor

  - Elke applicatie die communiceert met externe API's
  - Services die integreren met betaalproviders, e-mailservices of externe databronnen
  - Applicaties waar betrouwbaarheid en prestaties van API's van belang zijn

  <a name="less-suitable"></a>
  ## Minder geschikt voor

  - Interne service-naar-service communicatie waarvoor een dedicated client library wordt aangeboden
  - Eenvoudige bestandsdownloads of eenmalige scripts waar robuustheid niet cruciaal is

  <a name="examples"></a>
  ## Voorbeelden

  ### Stel altijd expliciete timeouts in

  ```php
  // Slecht: standaard timeout van 30 seconden
  $response = Http::get('https://api.example.com/users');

  // Goed: expliciete timeouts
  $response = Http::timeout(5)
      ->connectTimeout(3)
      ->get('https://api.example.com/users');
  ```

  Definieer voor service-specifieke clients de timeouts in een macro:

  ```php
  Http::macro('github', function () {
      return Http::baseUrl('https://api.github.com')
          ->timeout(10)
          ->connectTimeout(3)
          ->withToken(config('services.github.token'));
  });

  $response = Http::github()->get('/repos/laravel/framework');
  ```

  ### Gebruik retry met backoff voor externe API's

  ```php
  // Slecht: geen retry bij tijdelijke fout
  $response = Http::post('https://api.stripe.com/v1/charges', $data);

  // Goed: exponentiële backoff
  $response = Http::retry([100, 500, 1000])
      ->timeout(10)
      ->post('https://api.stripe.com/v1/charges', $data);
  ```

  ### Handel fouten expliciet af

  ```php
  // Slecht: zou een foutresponsbody als data kunnen gebruiken
  $response = Http::get('https://api.example.com/users/1');
  $user = $response->json();

  // Goed: gooi een exception bij falen
  $response = Http::timeout(5)
      ->get('https://api.example.com/users/1')
      ->throw();

  $user = $response->json();

  // Goed: gracieuze degradatie
  $response = Http::get('https://api.example.com/users/1');

  if ($response->successful()) {
      return $response->json();
  }

  if ($response->notFound()) {
      return null;
  }

  $response->throw();
  ```

  ### Gebruik request pooling voor gelijktijdige requests

  ```php
  // Slecht: sequentiële requests
  $users = Http::get('https://api.example.com/users')->json();
  $posts = Http::get('https://api.example.com/posts')->json();

  // Goed: gelijktijdige requests
  use Illuminate\Http\Client\Pool;

  $responses = Http::pool(fn (Pool $pool) => [
      $pool->as('users')->get('https://api.example.com/users'),
      $pool->as('posts')->get('https://api.example.com/posts'),
  ]);

  $users = $responses['users']->json();
  $posts = $responses['posts']->json();
  ```

  ### Fake HTTP-calls in tests

  ```php
  it('syncs user from API', function () {
      Http::preventStrayRequests();

      Http::fake([
          'api.example.com/users/1' => Http::response([
              'name' => 'John Doe',
              'email' => 'john@example.com',
          ]),
      ]);

      $service = new UserSyncService;
      $service->sync(1);

      Http::assertSent(function (Request $request) {
          return $request->url() === 'https://api.example.com/users/1';
      });
  });
  ```

  <a name="more-info"></a>
  ## Meer informatie

  - [Laravel HTTP Client Documentatie](https://laravel.com/docs/http-client)
  - [Laravel HTTP Client Testing Documentatie](https://laravel.com/docs/http-client#testing)
  - [Volg Testing Best Practices](../../../testing/follow-testing-best-practices/translations/nl.md)
  - [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)
content_en: |-
  <a name="introduction"></a>
  ## Introduction

  Laravel's HTTP Client (built on Guzzle) provides a fluent interface for making HTTP requests to external APIs. Using it correctly means setting explicit timeouts, implementing retry with backoff, handling errors properly, using request pooling for concurrent calls, and faking requests in tests.

  <a name="why"></a>
  ## Why

  - **Fail fast**: Explicit timeouts prevent requests from hanging for 30+ seconds on unresponsive APIs
  - **Resilience**: Retry with exponential backoff handles transient failures gracefully without overwhelming external services
  - **Correctness**: The HTTP Client does not throw on 4xx/5xx by default, errors must be handled explicitly to avoid silently using error response bodies as data
  - **Performance**: `Http::pool()` runs independent requests concurrently, eliminating sequential wait times
  - **Test reliability**: `Http::fake()` with `preventStrayRequests()` ensures tests never hit real APIs and catches unmocked calls

  <a name="suitable-for"></a>
  ## Suitable For

  - Any application that communicates with external APIs
  - Services that integrate with payment providers, email services, or third-party data sources
  - Applications where API reliability and performance matter

  <a name="less-suitable"></a>
  ## Less Suitable

  - Internal service-to-service communication where a dedicated client library is provided
  - Simple file downloads or one-off scripts where robustness isn't critical

  <a name="examples"></a>
  ## Examples

  ### Always Set Explicit Timeouts

  ```php
  // Bad: default 30-second timeout
  $response = Http::get('https://api.example.com/users');

  // Good: explicit timeouts
  $response = Http::timeout(5)
      ->connectTimeout(3)
      ->get('https://api.example.com/users');
  ```

  For service-specific clients, define timeouts in a macro:

  ```php
  Http::macro('github', function () {
      return Http::baseUrl('https://api.github.com')
          ->timeout(10)
          ->connectTimeout(3)
          ->withToken(config('services.github.token'));
  });

  $response = Http::github()->get('/repos/laravel/framework');
  ```

  ### Use Retry with Backoff for External APIs

  ```php
  // Bad: no retry on transient failure
  $response = Http::post('https://api.stripe.com/v1/charges', $data);

  // Good: exponential backoff
  $response = Http::retry([100, 500, 1000])
      ->timeout(10)
      ->post('https://api.stripe.com/v1/charges', $data);
  ```

  ### Handle Errors Explicitly

  ```php
  // Bad: could be using an error response body as data
  $response = Http::get('https://api.example.com/users/1');
  $user = $response->json();

  // Good: throw on failure
  $response = Http::timeout(5)
      ->get('https://api.example.com/users/1')
      ->throw();

  $user = $response->json();

  // Good: graceful degradation
  $response = Http::get('https://api.example.com/users/1');

  if ($response->successful()) {
      return $response->json();
  }

  if ($response->notFound()) {
      return null;
  }

  $response->throw();
  ```

  ### Use Request Pooling for Concurrent Requests

  ```php
  // Bad: sequential requests
  $users = Http::get('https://api.example.com/users')->json();
  $posts = Http::get('https://api.example.com/posts')->json();

  // Good: concurrent requests
  use Illuminate\Http\Client\Pool;

  $responses = Http::pool(fn (Pool $pool) => [
      $pool->as('users')->get('https://api.example.com/users'),
      $pool->as('posts')->get('https://api.example.com/posts'),
  ]);

  $users = $responses['users']->json();
  $posts = $responses['posts']->json();
  ```

  ### Fake HTTP Calls in Tests

  ```php
  it('syncs user from API', function () {
      Http::preventStrayRequests();

      Http::fake([
          'api.example.com/users/1' => Http::response([
              'name' => 'John Doe',
              'email' => 'john@example.com',
          ]),
      ]);

      $service = new UserSyncService;
      $service->sync(1);

      Http::assertSent(function (Request $request) {
          return $request->url() === 'https://api.example.com/users/1';
      });
  });
  ```

  <a name="more-info"></a>
  ## More Info

  - [Laravel HTTP Client Documentation](https://laravel.com/docs/http-client)
  - [Laravel HTTP Client Testing Documentation](https://laravel.com/docs/http-client#testing)
  - [Follow Testing Best Practices](../../testing/follow-testing-best-practices/BEST_PRACTICE.md), for general testing patterns
  - [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)
best_practice_categories:
  - apis
category_slug: apis
category_title: "API's"
category_title_en: APIs
source_path: apis/use-the-http-client-correctly/BEST_PRACTICE.md
source_sha: c7034be11f69954eac43e50486b6e5bebde98c46
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/apis/use-the-http-client-correctly/BEST_PRACTICE.md'
has_skill: true
skill_content: |-
  Use this skill when a Laravel task touches this best practice. It is self-contained so it can be installed independently by Laravel Boost or another agent-skill system.

  ## Core Guidance

  Laravel's HTTP Client (built on Guzzle) provides a fluent interface for making HTTP requests to external APIs. Using it correctly means setting explicit timeouts, implementing retry with backoff, handling errors properly, using request pooling for concurrent calls, and faking requests in tests.

  ## Why It Matters

  - **Fail fast**: Explicit timeouts prevent requests from hanging for 30+ seconds on unresponsive APIs
  - **Resilience**: Retry with exponential backoff handles transient failures gracefully without overwhelming external services
  - **Correctness**: The HTTP Client does not throw on 4xx/5xx by default, errors must be handled explicitly to avoid silently using error response bodies as data
  - **Performance**: `Http::pool()` runs independent requests concurrently, eliminating sequential wait times
  - **Test reliability**: `Http::fake()` with `preventStrayRequests()` ensures tests never hit real APIs and catches unmocked calls

  ## Apply When

  - Any application that communicates with external APIs
  - Services that integrate with payment providers, email services, or third-party data sources
  - Applications where API reliability and performance matter

  ## Be Careful When

  - Internal service-to-service communication where a dedicated client library is provided
  - Simple file downloads or one-off scripts where robustness isn't critical

  ## Canonical Source

  - Full best practice: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/apis/use-the-http-client-correctly/BEST_PRACTICE.md
  - Dutch translation: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/apis/use-the-http-client-correctly/translations/nl.md

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
skill_source_path: apis/use-the-http-client-correctly/skill/SKILL.md
skill_github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/apis/use-the-http-client-correctly/skill/SKILL.md'
skill_references: []
synced_at: 1785231871
---
