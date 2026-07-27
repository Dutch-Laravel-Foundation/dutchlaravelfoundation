---
id: 06243faf-5cb4-56ed-8747-cfb3652c44cc
blueprint: best_practices
title: 'Configuratie op de juiste manier gebruiken'
title_nl: 'Configuratie op de juiste manier gebruiken'
title_en: 'Use Configuration Properly'
summary_nl: 'Het configuratiesysteem van Laravel is ontworpen met een duidelijke regel: environment-variabelen zouden alleen in config-bestanden benaderd moeten worden, en applicatiecode zou altijd config() moeten gebruiken. Dit patroon zorgt ervoor dat...'
summary_en: "Laravel's configuration system is designed with a clear rule: environment variables should only be accessed in config files, and application code should always use config(). This pattern ensures that configuration caching works correctly an..."
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

  Het configuratiesysteem van Laravel is ontworpen met een duidelijke regel: environment-variabelen zouden alleen in config-bestanden benaderd moeten worden, en applicatiecode zou altijd `config()` moeten gebruiken. Dit patroon zorgt ervoor dat het cachen van configuratie correct werkt en dat environment-checks betrouwbaar zijn. Daarnaast verbetert het gebruik van constanten en taalbestanden in plaats van hardcoded strings de onderhoudbaarheid.

  <a name="why"></a>
  ## Waarom

  - **Compatibiliteit met caching**: Directe `env()`-aanroepen geven `null` terug wanneer de config gecachet is — het gebruik van `config()` werkt altijd correct
  - **Gecentraliseerde instellingen**: Alle configuratie staat in config-bestanden, waardoor het eenvoudig is om te vinden en te controleren wat de applicatie gebruikt
  - **Betrouwbare environment-checks**: `App::environment()` en `app()->isProduction()` werken ongeacht het cachen van configuratie, in tegenstelling tot `env('APP_ENV')`
  - **Veiligheid bij refactoren**: Het gebruik van class-constanten in plaats van magische strings voor model-states en -types maakt refactoren in de IDE mogelijk en elimineert bugs door typefouten

  <a name="suitable-for"></a>
  ## Geschikt Voor

  - Alle Laravel-applicaties
  - Applicaties die uitgerold worden met `config:cache` (oftewel de meeste productieomgevingen)
  - Projecten met meerdere omgevingen (local, staging, production)

  <a name="less-suitable"></a>
  ## Minder Geschikt

  - N.v.t. — deze praktijken zijn van toepassing op elke Laravel-applicatie

  <a name="examples"></a>
  ## Voorbeelden

  ### `env()` Alleen in Config-bestanden

  ```php
  // Slecht: geeft null terug wanneer de config gecachet is
  $key = env('API_KEY');

  // Goed: definieer in config, gebruik via config()
  // config/services.php
  'key' => env('API_KEY'),

  // Applicatiecode
  $key = config('services.key');
  ```

  ### Gebruik `App::environment()` voor Environment-checks

  ```php
  // Slecht: gaat stuk met config-caching
  if (env('APP_ENV') === 'production') {

  // Goed: altijd betrouwbaar
  if (app()->isProduction()) {
  // of
  if (App::environment('production')) {
  ```

  ### Gebruik Constanten in plaats van Magische Strings

  ```php
  // Slecht: foutgevoelige magische string
  return $this->type === 'normal';

  // Goed: refactorbare constante
  return $this->type === self::TYPE_NORMAL;
  ```

  ### Gebruik Taalbestanden Wanneer Ze Al Aanwezig Zijn

  Als de applicatie al taalbestanden gebruikt voor lokalisatie, gebruik dan ook `__()` voor strings die aan gebruikers getoond worden. Introduceer geen taalbestanden puur voor Engelstalige apps — eenvoudige string-literals volstaan daar prima:

  ```php
  // Alleen wanneer er al lang-bestanden in het project bestaan
  return back()->with('message', __('app.article_added'));
  ```

  ### Gebruik Versleutelde Env voor Productiegeheimen

  Sla productiegeheimen nooit op in platte `.env`-bestanden in versiebeheer:

  ```bash
  php artisan env:encrypt --env=production --readable
  php artisan env:decrypt --env=production
  ```

  Geef bij cloud-deployments de voorkeur aan de native secret store van het platform (AWS Secrets Manager, Vault, enz.) en injecteer deze tijdens runtime.

  <a name="more-info"></a>
  ## Meer Info

  - [Laravel Configuration Documentation](https://laravel.com/docs/configuration)
  - [Laravel Environment Configuration](https://laravel.com/docs/configuration#environment-configuration)
  - [Laravel Encryption Documentation](https://laravel.com/docs/encryption)
  - [Prevent Common Vulnerabilities](../../../security-and-authentication/prevent-common-vulnerabilities/translations/nl.md) — voor het veilig houden van geheimen en het versleutelen van gevoelige velden
  - [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)
content_en: |-
  <a name="introduction"></a>
  ## Introduction

  Laravel's configuration system is designed with a clear rule: environment variables should only be accessed in config files, and application code should always use `config()`. This pattern ensures that configuration caching works correctly and that environment checks are reliable. Additionally, using constants and language files instead of hardcoded strings improves maintainability.

  <a name="why"></a>
  ## Why

  - **Caching compatibility**: Direct `env()` calls return `null` when config is cached — using `config()` always works correctly
  - **Centralized settings**: All configuration lives in config files, making it easy to find and audit what the application uses
  - **Reliable environment checks**: `App::environment()` and `app()->isProduction()` work regardless of config caching, unlike `env('APP_ENV')`
  - **Refactoring safety**: Using class constants instead of magic strings for model states and types makes IDE refactoring possible and eliminates typo-related bugs

  <a name="suitable-for"></a>
  ## Suitable For

  - All Laravel applications
  - Applications deployed with `config:cache` (i.e., most production environments)
  - Projects with multiple environments (local, staging, production)

  <a name="less-suitable"></a>
  ## Less Suitable

  - N/A — these practices apply to every Laravel application

  <a name="examples"></a>
  ## Examples

  ### `env()` Only in Config Files

  ```php
  // Bad: returns null when config is cached
  $key = env('API_KEY');

  // Good: define in config, use via config()
  // config/services.php
  'key' => env('API_KEY'),

  // Application code
  $key = config('services.key');
  ```

  ### Use `App::environment()` for Environment Checks

  ```php
  // Bad: breaks with config caching
  if (env('APP_ENV') === 'production') {

  // Good: always reliable
  if (app()->isProduction()) {
  // or
  if (App::environment('production')) {
  ```

  ### Use Constants Instead of Magic Strings

  ```php
  // Bad: typo-prone magic string
  return $this->type === 'normal';

  // Good: refactorable constant
  return $this->type === self::TYPE_NORMAL;
  ```

  ### Use Language Files When Already Present

  If the application already uses language files for localization, use `__()` for user-facing strings too. Do not introduce language files purely for English-only apps — simple string literals are fine there:

  ```php
  // Only when lang files already exist in the project
  return back()->with('message', __('app.article_added'));
  ```

  ### Use Encrypted Env for Production Secrets

  Never store production secrets in plain `.env` files in version control:

  ```bash
  php artisan env:encrypt --env=production --readable
  php artisan env:decrypt --env=production
  ```

  For cloud deployments, prefer the platform's native secret store (AWS Secrets Manager, Vault, etc.) and inject at runtime.

  <a name="more-info"></a>
  ## More Info

  - [Laravel Configuration Documentation](https://laravel.com/docs/configuration)
  - [Laravel Environment Configuration](https://laravel.com/docs/configuration#environment-configuration)
  - [Laravel Encryption Documentation](https://laravel.com/docs/encryption)
  - [Prevent Common Vulnerabilities](../../security-and-authentication/prevent-common-vulnerabilities/BEST_PRACTICE.md) — for keeping secrets secure and encrypting sensitive fields
  - [Laravel Boost Best Practices PR](https://github.com/laravel/boost/pull/628)
best_practice_categories:
  - project-structure-and-code-architecture
category_slug: project-structure-and-code-architecture
category_title: 'Projectstructuur en architectuur'
category_title_en: 'Project Structure and Code Architecture'
source_path: project-structure-and-code-architecture/use-configuration-properly/BEST_PRACTICE.md
source_sha: c7034be11f69954eac43e50486b6e5bebde98c46
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/project-structure-and-code-architecture/use-configuration-properly/BEST_PRACTICE.md'
has_skill: true
skill_content: |-
  Use this skill when a Laravel task touches this best practice. It is self-contained so it can be installed independently by Laravel Boost or another agent-skill system.

  ## Core Guidance

  Laravel's configuration system is designed with a clear rule: environment variables should only be accessed in config files, and application code should always use `config()`. This pattern ensures that configuration caching works correctly and that environment checks are reliable. Additionally, using constants and language files instead of hardcoded strings improves maintainability.

  ## Why It Matters

  - **Caching compatibility**: Direct `env()` calls return `null` when config is cached — using `config()` always works correctly
  - **Centralized settings**: All configuration lives in config files, making it easy to find and audit what the application uses
  - **Reliable environment checks**: `App::environment()` and `app()->isProduction()` work regardless of config caching, unlike `env('APP_ENV')`
  - **Refactoring safety**: Using class constants instead of magic strings for model states and types makes IDE refactoring possible and eliminates typo-related bugs

  ## Apply When

  - All Laravel applications
  - Applications deployed with `config:cache` (i.e., most production environments)
  - Projects with multiple environments (local, staging, production)

  ## Be Careful When

  - N/A — these practices apply to every Laravel application

  ## Canonical Source

  - Full best practice: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/project-structure-and-code-architecture/use-configuration-properly/BEST_PRACTICE.md
  - Dutch translation: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/project-structure-and-code-architecture/use-configuration-properly/translations/nl.md

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
skill_source_path: project-structure-and-code-architecture/use-configuration-properly/skill/SKILL.md
skill_github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/project-structure-and-code-architecture/use-configuration-properly/skill/SKILL.md'
skill_references: []
synced_at: 1785159222
---
