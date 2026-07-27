---
id: 7cba1a23-2b45-582b-831e-ef6ef4b55813
blueprint: best_practices
title: 'Voorkom Veelvoorkomende Kwetsbaarheden'
title_nl: 'Voorkom Veelvoorkomende Kwetsbaarheden'
title_en: 'Prevent Common Vulnerabilities'
summary_nl: 'Laravel biedt ingebouwde bescherming tegen de meest voorkomende kwetsbaarheden in webapplicaties, maar deze moeten correct worden gebruikt. Dit behandelt essentiële beveiligingspraktijken zoals bescherming tegen mass assignment, het voorkom...'
summary_en: 'Laravel provides built-in protections against the most common web application vulnerabilities, but they need to be used correctly. This covers essential security practices including mass assignment protection, SQL injection prevention, XSS...'
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

  Laravel biedt ingebouwde bescherming tegen de meest voorkomende kwetsbaarheden in webapplicaties, maar deze moeten correct worden gebruikt. Dit behandelt essentiële beveiligingspraktijken zoals bescherming tegen mass assignment, het voorkomen van SQL-injectie, XSS-escaping, CSRF-bescherming, validatie van bestandsuploads, rate limiting en het versleutelen van gevoelige databasevelden. Voor autorisatiepatronen, zie [Gebruik Policies en Gates voor Autorisatie](../../../project-structure-and-code-architecture/use-policies-and-gates-for-authorization/translations/nl.md).

  <a name="why"></a>
  ## Waarom

  - **Defense in depth**: Elke praktijk pakt een andere aanvalsvector aan — samen dekken ze de OWASP Top 10-risico's die relevant zijn voor Laravel-applicaties
  - **Framework-ondersteuning**: Laravel biedt alle tools al; je hoeft ze alleen consistent te gebruiken
  - **Gegevensbescherming**: Het versleutelen van gevoelige velden en het buiten de code houden van secrets beschermt tegen datalekken
  - **Beschikbaarheid**: Rate limiting voorkomt brute-force-aanvallen en misbruik van authenticatie- en API-endpoints

  <a name="suitable-for"></a>
  ## Geschikt Voor

  - Alle Laravel-applicaties, ongeacht de omvang
  - Applicaties die gebruikersinvoer, authenticatie of bestandsuploads verwerken
  - Applicaties die gevoelige gegevens opslaan (API-sleutels, tokens, persoonlijke informatie)

  <a name="less-suitable"></a>
  ## Minder Geschikt

  - N.v.t. — deze praktijken zijn van toepassing op elke Laravel-applicatie

  <a name="examples"></a>
  ## Voorbeelden

  ### Bescherming tegen Mass Assignment

  Elk model moet `$fillable` (whitelist) of `$guarded` (blacklist) definiëren:

  ```php
  // Slecht: alle velden zijn mass assignable
  class User extends Model
  {
      protected $guarded = [];
  }

  // Goed: expliciete whitelist
  class User extends Model
  {
      protected $fillable = [
          'name',
          'email',
          'password',
      ];
  }
  ```

  Gebruik nooit `$guarded = []` op models die gebruikersinvoer accepteren.

  ### Voorkom SQL-injectie

  Gebruik altijd parameter binding. Interpoleer nooit gebruikersinvoer in queries:

  ```php
  // Slecht: kwetsbaarheid voor SQL-injectie
  DB::select("SELECT * FROM users WHERE name = '{$request->name}'");

  // Goed: parameter binding
  User::where('name', $request->name)->get();

  // Goed: raw expressies met bindings
  User::whereRaw('LOWER(name) = ?', [strtolower($request->name)])->get();
  ```

  ### Escape Output om XSS te Voorkomen

  Gebruik `{{ }}` voor HTML-escaping. Gebruik `{!! !!}` alleen voor vertrouwde, vooraf gesaniteerde content:

  ```blade
  {{-- Slecht: niet-geëscapete gebruikerscontent --}}
  {!! $user->bio !!}

  {{-- Goed: automatisch geëscaped --}}
  {{ $user->bio }}
  ```

  ### CSRF-bescherming

  Voeg `@csrf` toe aan alle POST/PUT/DELETE Blade-formulieren:

  ```blade
  <form method="POST" action="/posts">
      @csrf
      <input type="text" name="title">
  </form>
  ```

  ### Rate Limiting voor Auth- en API-routes

  ```php
  RateLimiter::for('login', function (Request $request) {
      return Limit::perMinute(5)->by($request->ip());
  });

  Route::post('/login', LoginController::class)->middleware('throttle:login');
  ```

  ### Valideer Bestandsuploads

  Valideer MIME-type, extensie en grootte. Vertrouw nooit door de client aangeleverde bestandsnamen:

  ```php
  public function rules(): array
  {
      return [
          'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
      ];
  }
  ```

  Sla op met gegenereerde bestandsnamen:

  ```php
  $path = $request->file('avatar')->store('avatars', 'public');
  ```

  ### Versleutel Gevoelige Databasevelden

  Gebruik de `encrypted`-cast voor API-sleutels en tokens, en markeer het attribuut als `hidden`:

  ```php
  class Integration extends Model
  {
      protected $hidden = ['api_key', 'api_secret'];

      protected function casts(): array
      {
          return [
              'api_key' => 'encrypted',
              'api_secret' => 'encrypted',
          ];
      }
  }
  ```

  ### Controleer Dependencies

  Voer `composer audit` periodiek uit en automatiseer het in CI:

  ```bash
  composer audit
  ```

  <a name="more-info"></a>
  ## Meer Info

  - [Laravel Security-documentatie](https://laravel.com/docs/security)
  - [Laravel CSRF-bescherming](https://laravel.com/docs/csrf)
  - [Laravel Rate Limiting](https://laravel.com/docs/rate-limiting)
  - [Laravel Encryption-documentatie](https://laravel.com/docs/encryption)
  - [Gebruik Policies en Gates voor Autorisatie](../../../project-structure-and-code-architecture/use-policies-and-gates-for-authorization/translations/nl.md) — voor autorisatiepatronen
  - [Implementeer Content Security Policy (CSP)](../../implement-content-security-policy/translations/nl.md) — voor aanvullende XSS-bescherming via security headers
  - [Gebruik Configuratie op de Juiste Manier](../../../project-structure-and-code-architecture/use-configuration-properly/translations/nl.md) — voor het buiten de code houden van secrets
  - [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)
content_en: |-
  <a name="introduction"></a>
  ## Introduction

  Laravel provides built-in protections against the most common web application vulnerabilities, but they need to be used correctly. This covers essential security practices including mass assignment protection, SQL injection prevention, XSS escaping, CSRF protection, file upload validation, rate limiting, and encrypting sensitive database fields. For authorization patterns, see [Use Policies and Gates for Authorization](../../project-structure-and-code-architecture/use-policies-and-gates-for-authorization/BEST_PRACTICE.md).

  <a name="why"></a>
  ## Why

  - **Defense in depth**: Each practice addresses a different attack vector — together they cover the OWASP Top 10 risks relevant to Laravel applications
  - **Framework support**: Laravel already provides all the tools; you just need to use them consistently
  - **Data protection**: Encrypting sensitive fields and keeping secrets out of code protects against data breaches
  - **Availability**: Rate limiting prevents brute-force attacks and abuse of authentication and API endpoints

  <a name="suitable-for"></a>
  ## Suitable For

  - All Laravel applications, regardless of size
  - Applications handling user input, authentication, or file uploads
  - Applications storing sensitive data (API keys, tokens, personal information)

  <a name="less-suitable"></a>
  ## Less Suitable

  - N/A — these practices apply to every Laravel application

  <a name="examples"></a>
  ## Examples

  ### Mass Assignment Protection

  Every model must define `$fillable` (whitelist) or `$guarded` (blacklist):

  ```php
  // Bad: all fields are mass assignable
  class User extends Model
  {
      protected $guarded = [];
  }

  // Good: explicit whitelist
  class User extends Model
  {
      protected $fillable = [
          'name',
          'email',
          'password',
      ];
  }
  ```

  Never use `$guarded = []` on models that accept user input.

  ### Prevent SQL Injection

  Always use parameter binding. Never interpolate user input into queries:

  ```php
  // Bad: SQL injection vulnerability
  DB::select("SELECT * FROM users WHERE name = '{$request->name}'");

  // Good: parameter binding
  User::where('name', $request->name)->get();

  // Good: raw expressions with bindings
  User::whereRaw('LOWER(name) = ?', [strtolower($request->name)])->get();
  ```

  ### Escape Output to Prevent XSS

  Use `{{ }}` for HTML escaping. Only use `{!! !!}` for trusted, pre-sanitized content:

  ```blade
  {{-- Bad: unescaped user content --}}
  {!! $user->bio !!}

  {{-- Good: auto-escaped --}}
  {{ $user->bio }}
  ```

  ### CSRF Protection

  Include `@csrf` in all POST/PUT/DELETE Blade forms:

  ```blade
  <form method="POST" action="/posts">
      @csrf
      <input type="text" name="title">
  </form>
  ```

  ### Rate Limit Auth and API Routes

  ```php
  RateLimiter::for('login', function (Request $request) {
      return Limit::perMinute(5)->by($request->ip());
  });

  Route::post('/login', LoginController::class)->middleware('throttle:login');
  ```

  ### Validate File Uploads

  Validate MIME type, extension, and size. Never trust client-provided filenames:

  ```php
  public function rules(): array
  {
      return [
          'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
      ];
  }
  ```

  Store with generated filenames:

  ```php
  $path = $request->file('avatar')->store('avatars', 'public');
  ```

  ### Encrypt Sensitive Database Fields

  Use `encrypted` cast for API keys and tokens, and mark the attribute as `hidden`:

  ```php
  class Integration extends Model
  {
      protected $hidden = ['api_key', 'api_secret'];

      protected function casts(): array
      {
          return [
              'api_key' => 'encrypted',
              'api_secret' => 'encrypted',
          ];
      }
  }
  ```

  ### Audit Dependencies

  Run `composer audit` periodically and automate it in CI:

  ```bash
  composer audit
  ```

  <a name="more-info"></a>
  ## More Info

  - [Laravel Security Documentation](https://laravel.com/docs/security)
  - [Laravel CSRF Protection](https://laravel.com/docs/csrf)
  - [Laravel Rate Limiting](https://laravel.com/docs/rate-limiting)
  - [Laravel Encryption Documentation](https://laravel.com/docs/encryption)
  - [Use Policies and Gates for Authorization](../../project-structure-and-code-architecture/use-policies-and-gates-for-authorization/BEST_PRACTICE.md) — for authorization patterns
  - [Implement Content Security Policy (CSP)](../implement-content-security-policy/BEST_PRACTICE.md) — for additional XSS protection via security headers
  - [Use Configuration Properly](../../project-structure-and-code-architecture/use-configuration-properly/BEST_PRACTICE.md) — for keeping secrets out of code
  - [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)
best_practice_categories:
  - security-and-authentication
category_slug: security-and-authentication
category_title: 'Security en authenticatie'
category_title_en: 'Security & Authentication'
source_path: security-and-authentication/prevent-common-vulnerabilities/BEST_PRACTICE.md
source_sha: c7034be11f69954eac43e50486b6e5bebde98c46
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/security-and-authentication/prevent-common-vulnerabilities/BEST_PRACTICE.md'
has_skill: true
skill_content: |-
  Use this skill when a Laravel task touches this best practice. It is self-contained so it can be installed independently by Laravel Boost or another agent-skill system.

  ## Core Guidance

  Laravel provides built-in protections against the most common web application vulnerabilities, but they need to be used correctly. This covers essential security practices including mass assignment protection, SQL injection prevention, XSS escaping, CSRF protection, file upload validation, rate limiting, and encrypting sensitive database fields. For authorization patterns, see Use Policies and Gates for Authorization.

  ## Why It Matters

  - **Defense in depth**: Each practice addresses a different attack vector — together they cover the OWASP Top 10 risks relevant to Laravel applications
  - **Framework support**: Laravel already provides all the tools; you just need to use them consistently
  - **Data protection**: Encrypting sensitive fields and keeping secrets out of code protects against data breaches
  - **Availability**: Rate limiting prevents brute-force attacks and abuse of authentication and API endpoints

  ## Apply When

  - All Laravel applications, regardless of size
  - Applications handling user input, authentication, or file uploads
  - Applications storing sensitive data (API keys, tokens, personal information)

  ## Be Careful When

  - N/A — these practices apply to every Laravel application

  ## Canonical Source

  - Full best practice: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/security-and-authentication/prevent-common-vulnerabilities/BEST_PRACTICE.md
  - Dutch translation: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/security-and-authentication/prevent-common-vulnerabilities/translations/nl.md

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
skill_source_path: security-and-authentication/prevent-common-vulnerabilities/skill/SKILL.md
skill_github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/security-and-authentication/prevent-common-vulnerabilities/skill/SKILL.md'
skill_references: []
---
