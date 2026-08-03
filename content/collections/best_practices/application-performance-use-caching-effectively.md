---
id: 6ef0bf80-e5de-545f-bccd-5309dd333f70
blueprint: best_practices
title: 'Gebruik caching effectief'
title_nl: 'Gebruik caching effectief'
title_en: 'Use Caching Effectively'
summary_nl: 'De cachinglaag van Laravel biedt meerdere patronen die verder gaan dan eenvoudige get/put-operaties. Het correct gebruiken van Cache::remember(), Cache::flexible(), Cache::memo(), once(), cache tags en failover stores kan de prestaties van...'
summary_en: "Laravel's caching layer provides several patterns beyond simple get/put operations. Using Cache::remember(), Cache::flexible(), Cache::memo(), once(), cache tags, and failover stores properly can dramatically improve application performance..."
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

  De cachinglaag van Laravel biedt meerdere patronen die verder gaan dan eenvoudige get/put-operaties. Het correct gebruiken van `Cache::remember()`, `Cache::flexible()`, `Cache::memo()`, `once()`, cache tags en failover stores kan de prestaties van je applicatie drastisch verbeteren, terwijl je veelvoorkomende valkuilen zoals race conditions en verouderde data vermijdt.

  <a name="why"></a>
  ## Waarom

  - **Minder boilerplate**: `Cache::remember()` vervangt handmatige get/check/put-patronen door één atomaire operatie
  - **Betere gebruikerservaring**: `Cache::flexible()` serveert verouderde data terwijl deze op de achtergrond wordt ververst, zodat geen enkele gebruiker de trage route hoeft te nemen
  - **Minder round-trips**: `Cache::memo()` en `once()` elimineren overbodige cache- of berekeningsaanroepen binnen één request
  - **Schone invalidatie**: Cache tags maken het mogelijk om gerelateerde groepen entries atomair te legen
  - **Veerkracht**: Failover cache stores houden de applicatie draaiende wanneer de primaire cache uitvalt

  <a name="suitable-for"></a>
  ## Geschikt voor

  - Applicaties met kostbare database queries of API calls die niet realtime hoeven te zijn
  - Endpoints met veel verkeer waar cache stampedes een zorg zijn
  - Applicaties met gerelateerde data die samen geïnvalideerd moet worden
  - Productieomgevingen waar de betrouwbaarheid van de cache store van belang is

  <a name="less-suitable"></a>
  ## Minder geschikt voor

  - Data die altijd actueel moet zijn (financiële transacties, realtime voorraad)
  - Ontwikkelomgevingen waar caching bugs verhult
  - Eenvoudige applicaties met snelle queries en weinig verkeer

  <a name="examples"></a>
  ## Voorbeelden

  ### Gebruik `Cache::remember()` in plaats van handmatige get/put

  ```php
  // Slecht: race condition, boilerplate
  $val = Cache::get('stats');
  if (! $val) {
      $val = $this->computeStats();
      Cache::put('stats', $val, 60);
  }

  // Goed: atomair patroon
  $val = Cache::remember('stats', 60, fn () => $this->computeStats());
  ```

  ### Gebruik `Cache::flexible()` voor stale-while-revalidate

  Bij keys met veel verkeer krijgt er altijd één gebruiker een traag antwoord wanneer de cache verloopt. `flexible()` serveert licht verouderde data terwijl deze op de achtergrond wordt ververst:

  ```php
  // Slecht: één ongelukkige gebruiker wacht op de herberekening
  Cache::remember('users', 300, fn () => User::all());

  // Goed: vers gedurende 5 min, verouderd-maar-geserveerd tot 10 min, ververst via deferred functie
  Cache::flexible('users', [300, 600], fn () => User::all());
  ```

  ### Gebruik `Cache::memo()` om overbodige hits te vermijden

  Als dezelfde cache key meerdere keren per request wordt gelezen, slaat `memo()` de opgeloste waarde in het geheugen op:

  ```php
  Cache::memo()->get('settings'); // 5 aanroepen = 1 Redis round-trip in plaats van 5
  ```

  ### Gebruik cache tags om gerelateerde groepen te invalideren

  ```php
  Cache::tags(['user-1'])->flush();
  ```

  > **Let op:** Tags werken alleen met `redis`, `memcached`, `dynamodb`, niet met `file` of `database`.

  ### Gebruik `once()` voor memoization per request

  `once()` memoiseert de retourwaarde van een functie voor de levensduur van het object, puur in-memory, zonder de cache store aan te spreken:

  ```php
  public function roles(): Collection
  {
      return once(fn () => $this->loadRoles());
  }
  ```

  ### Gebruik `Cache::add()` voor atomaire conditionele writes

  ```php
  // Slecht: race condition tussen check en write
  if (! Cache::has('lock')) {
      Cache::put('lock', true, 10);
  }

  // Goed: atomair, schrijft alleen als de key nog niet bestaat
  Cache::add('lock', true, 10);
  ```

  ### Configureer failover cache stores in productie

  ```php
  // config/cache.php
  'failover' => [
      'driver' => 'failover',
      'stores' => ['redis', 'database'],
  ],
  ```

  <a name="more-info"></a>
  ## Meer info

  - [Laravel Cache Documentatie](https://laravel.com/docs/cache)
  - [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)
content_en: |-
  <a name="introduction"></a>
  ## Introduction

  Laravel's caching layer provides several patterns beyond simple get/put operations. Using `Cache::remember()`, `Cache::flexible()`, `Cache::memo()`, `once()`, cache tags, and failover stores properly can dramatically improve application performance while avoiding common pitfalls like race conditions and stale data.

  <a name="why"></a>
  ## Why

  - **Reduced boilerplate**: `Cache::remember()` replaces manual get/check/put patterns with an atomic operation
  - **Better user experience**: `Cache::flexible()` serves stale data while refreshing in the background, so no user takes the slow-path hit
  - **Fewer round-trips**: `Cache::memo()` and `once()` eliminate redundant cache or computation calls within a single request
  - **Clean invalidation**: Cache tags allow flushing related groups of entries atomically
  - **Resilience**: Failover cache stores keep the application running when the primary cache goes down

  <a name="suitable-for"></a>
  ## Suitable For

  - Applications with expensive database queries or API calls that don't need to be real-time
  - High-traffic endpoints where cache stampedes are a concern
  - Applications with related data that needs to be invalidated together
  - Production environments where cache store reliability matters

  <a name="less-suitable"></a>
  ## Less Suitable

  - Data that must always be fresh (financial transactions, real-time inventory)
  - Development environments where caching obscures bugs
  - Simple applications with fast queries and low traffic

  <a name="examples"></a>
  ## Examples

  ### Use `Cache::remember()` Instead of Manual Get/Put

  ```php
  // Bad: race condition, boilerplate
  $val = Cache::get('stats');
  if (! $val) {
      $val = $this->computeStats();
      Cache::put('stats', $val, 60);
  }

  // Good: atomic pattern
  $val = Cache::remember('stats', 60, fn () => $this->computeStats());
  ```

  ### Use `Cache::flexible()` for Stale-While-Revalidate

  On high-traffic keys, one user always gets a slow response when the cache expires. `flexible()` serves slightly stale data while refreshing in the background:

  ```php
  // Bad: one unlucky user waits for recomputation
  Cache::remember('users', 300, fn () => User::all());

  // Good: fresh for 5 min, stale-but-served up to 10 min, refreshes via deferred function
  Cache::flexible('users', [300, 600], fn () => User::all());
  ```

  ### Use `Cache::memo()` to Avoid Redundant Hits

  If the same cache key is read multiple times per request, `memo()` stores the resolved value in memory:

  ```php
  Cache::memo()->get('settings'); // 5 calls = 1 Redis round-trip instead of 5
  ```

  ### Use Cache Tags to Invalidate Related Groups

  ```php
  Cache::tags(['user-1'])->flush();
  ```

  > **Note:** Tags only work with `redis`, `memcached`, `dynamodb`, not `file` or `database`.

  ### Use `once()` for Per-Request Memoization

  `once()` memoizes a function's return value for the lifetime of the object, pure in-memory, no cache store hit:

  ```php
  public function roles(): Collection
  {
      return once(fn () => $this->loadRoles());
  }
  ```

  ### Use `Cache::add()` for Atomic Conditional Writes

  ```php
  // Bad: race condition between check and write
  if (! Cache::has('lock')) {
      Cache::put('lock', true, 10);
  }

  // Good: atomic, only writes if key doesn't exist
  Cache::add('lock', true, 10);
  ```

  ### Configure Failover Cache Stores in Production

  ```php
  // config/cache.php
  'failover' => [
      'driver' => 'failover',
      'stores' => ['redis', 'database'],
  ],
  ```

  <a name="more-info"></a>
  ## More Info

  - [Laravel Cache Documentation](https://laravel.com/docs/cache)
  - [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)
best_practice_categories:
  - application-performance
category_slug: application-performance
category_title: Applicatieprestaties
category_title_en: 'Application Performance'
source_path: application-performance/use-caching-effectively/BEST_PRACTICE.md
source_sha: c7034be11f69954eac43e50486b6e5bebde98c46
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/application-performance/use-caching-effectively/BEST_PRACTICE.md'
has_skill: true
skill_content: |-
  Use this skill when a Laravel task touches this best practice. It is self-contained so it can be installed independently by Laravel Boost or another agent-skill system.

  ## Core Guidance

  Laravel's caching layer provides several patterns beyond simple get/put operations. Using `Cache::remember()`, `Cache::flexible()`, `Cache::memo()`, `once()`, cache tags, and failover stores properly can dramatically improve application performance while avoiding common pitfalls like race conditions and stale data.

  ## Why It Matters

  - **Reduced boilerplate**: `Cache::remember()` replaces manual get/check/put patterns with an atomic operation
  - **Better user experience**: `Cache::flexible()` serves stale data while refreshing in the background, so no user takes the slow-path hit
  - **Fewer round-trips**: `Cache::memo()` and `once()` eliminate redundant cache or computation calls within a single request
  - **Clean invalidation**: Cache tags allow flushing related groups of entries atomically
  - **Resilience**: Failover cache stores keep the application running when the primary cache goes down

  ## Apply When

  - Applications with expensive database queries or API calls that don't need to be real-time
  - High-traffic endpoints where cache stampedes are a concern
  - Applications with related data that needs to be invalidated together
  - Production environments where cache store reliability matters

  ## Be Careful When

  - Data that must always be fresh (financial transactions, real-time inventory)
  - Development environments where caching obscures bugs
  - Simple applications with fast queries and low traffic

  ## Canonical Source

  - Full best practice: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/application-performance/use-caching-effectively/BEST_PRACTICE.md
  - Dutch translation: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/application-performance/use-caching-effectively/translations/nl.md

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
skill_source_path: application-performance/use-caching-effectively/skill/SKILL.md
skill_github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/application-performance/use-caching-effectively/skill/SKILL.md'
skill_references: []
synced_at: 1785231871
---
