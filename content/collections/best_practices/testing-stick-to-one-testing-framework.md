---
id: cce8b5d6-a1c1-5d22-83f2-459887cc8398
blueprint: best_practices
title: 'Houd vast aan één testframework'
title_nl: 'Houd vast aan één testframework'
title_en: 'Stick to One Testing Framework'
summary_nl: 'Hoewel Pest bovenop PHPUnit is gebouwd en de twee frameworks technisch compatibel zijn, brengt het mengen van beide teststijlen in hetzelfde Laravel-project onnodige complexiteit en verwarring met zich mee. Een consistente testaanpak waarbi...'
summary_en: 'While Pest is built on top of PHPUnit and the two frameworks are technically compatible, mixing both testing styles in the same Laravel project introduces unnecessary complexity and confusion. A consistent testing approach using either PHPU...'
chapters_nl:
  - title: Introductie
    anchor: introductie
  - title: Waarom
    anchor: waarom
  - title: 'Geschikt voor'
    anchor: geschikt-voor
  - title: 'Minder geschikt voor'
    anchor: minder-geschikt-voor
  - title: 'Omgaan met bestaande gemengde testsuites'
    anchor: omgaan-met-bestaande-gemengde-testsuites
  - title: 'Wat er geconverteerd wordt (PHPUnit naar Pest)'
    anchor: wat-er-geconverteerd-wordt-phpunit-naar-pest
  - title: Uitzonderingen
    anchor: uitzonderingen
  - title: Voorbeelden
    anchor: voorbeelden
  - title: Projectdocumentatie
    anchor: projectdocumentatie
  - title: Testing
    anchor: testing
  - title: Testing
    anchor: testing
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
  - title: 'Handling Existing Mixed Test Suites'
    anchor: handling-existing-mixed-test-suites
  - title: 'What Gets Converted (PHPUnit to Pest)'
    anchor: what-gets-converted-phpunit-to-pest
  - title: Exceptions
    anchor: exceptions
  - title: Examples
    anchor: examples
  - title: 'Project Documentation'
    anchor: project-documentation
  - title: Testing
    anchor: testing
  - title: Testing
    anchor: testing
  - title: 'More Info'
    anchor: more-info
content_nl: |-
  <a name="introduction"></a>
  ## Introductie

  Hoewel Pest bovenop PHPUnit is gebouwd en de twee frameworks technisch compatibel zijn, brengt het mengen van beide teststijlen in hetzelfde Laravel-project onnodige complexiteit en verwarring met zich mee. Een consistente testaanpak waarbij je door je hele testsuite heen ofwel PHPUnit ofwel Pest gebruikt, wordt sterk aangeraden.

  <a name="why"></a>
  ## Waarom

  - **Verminderde cognitieve belasting**: Teamleden hoeven maar één testsyntaxis en -aanpak te leren en te onthouden
  - **Eenvoudigere onboarding**: Nieuwe ontwikkelaars die bij het project komen, hebben een eenvoudigere leercurve met één enkele, consistente teststijl
  - **Consistentie op de command line**: Voorkom verwarring over welke test runner je moet gebruiken (`vendor/bin/phpunit` versus `vendor/bin/pest`)
  - **Betere onderhoudbaarheid**: Consistente patronen maken het eenvoudiger om tests door de hele codebase heen bij te werken en te refactoren
  - **Vermijd compatibiliteitsproblemen**: Sommige PHPUnit-annotaties (zoals `@runTestsInSeparateProcesses`) zijn niet compatibel met Pest
  - **Duidelijkere projectstandaarden**: Eén enkel testframework stelt duidelijke verwachtingen voor alle bijdragers
  - **Vereenvoudigde CI/CD-pipelines**: Geen noodzaak om meerdere test runners of speciale configuraties af te handelen

  <a name="suitable-for"></a>
  ## Geschikt voor

  - Alle Laravel-projecten, ongeacht de omvang
  - Teams met meerdere ontwikkelaars
  - Projecten met langdurige onderhoudseisen
  - Codebases waar consistentie en leesbaarheid prioriteit hebben
  - Open-sourceprojecten waar externe bijdragers duidelijke richtlijnen nodig hebben

  <a name="less-suitable"></a>
  ## Minder geschikt voor

  - Projecten die actief migreren van PHPUnit naar Pest (een tijdelijke gemengde toestand is acceptabel tijdens de overgang)
  - Monorepo's waar verschillende packages legitieme redenen hebben om verschillende frameworks te gebruiken (hoewel consistentie ook hier over het algemeen beter is)

  <a name="handling-existing-mixed-test-suites"></a>
  ## Omgaan met bestaande gemengde testsuites

  Als je een project overneemt of momenteel hebt met gemengde PHPUnit- en Pest-tests, overweeg dan om te migreren naar één enkel framework:

  ### Migratie naar Pest

  Pest biedt verschillende tools om PHPUnit-tests te helpen converteren:

  1. **Drift Plugin** (Automatisch):
  ```bash
  composer require pestphp/pest-plugin-drift --dev
  ./vendor/bin/pest --drift
  ```

  2. **Laravel Shift** (Semi-Automatisch):
     - Gebruik de [Pest Converter](https://laravelshift.com/phpunit-to-pest-converter)-dienst
     - Geschatte tijdsbesparing: ~3 uur voor typische projecten
     - Handelt de meeste conversies automatisch af

  3. **Handmatige Migratie**:
     - Converteer tests geleidelijk terwijl je aan gerelateerde features werkt
     - Gebruik Pest's compatibiliteitslaag tijdens de overgangsperiode
     - Zorg ervoor dat alle nieuwe tests het beoogde framework gebruiken

  ### Migratie naar PHPUnit

  Als je team de voorkeur geeft aan PHPUnit:
  - Pest-tests kunnen handmatig worden herschreven als PHPUnit-testklassen
  - Dit is doorgaans arbeidsintensiever dan migreren naar Pest
  - Overweeg deze route als het team een sterke voorkeur heeft voor class-gebaseerd testen

  <a name="what-gets-converted"></a>
  ## Wat er geconverteerd wordt (PHPUnit naar Pest)

  Bij het gebruik van geautomatiseerde conversietools:
  - ✅ Lifecycle-methoden (`setUp`, `tearDown`) → Pest-hooks (`beforeEach`, `afterEach`)
  - ✅ Testmethoden → Pest `test()`- of `it()`-functies
  - ✅ Data providers → Pest-datasets
  - ✅ Testgroepen → Pest `group()`-chaining
  - ✅ PHPUnit-assertions → Pest-expectations (waar beschikbaar)

  **Belangrijk**: Private hulpmethoden in testklassen worden functies, wat handmatige aanpassingen kan vereisen omdat ze toegang tot `$this` verliezen.

  <a name="exceptions"></a>
  ## Uitzonderingen

  Het enige scenario waarin het mengen van frameworks acceptabel is:

  **Tijdens Actieve Migratie**: Een tijdelijke periode waarin je tests van het ene framework naar het andere converteert is acceptabel, maar zou moeten zijn:
  - Time-boxed (stel een deadline voor voltooiing)
  - Duidelijk gecommuniceerd naar het team
  - Gedocumenteerd in de project-README of de bijdragerichtlijnen
  - Geprioriteerd om de duur van de gemengde toestand te minimaliseren

  <a name="examples"></a>
  ## Voorbeelden

  ### ❌ Slecht: gemengde testsuite

  ```
  tests/
  ├── Feature/
  │   ├── UserRegistrationTest.php    # PHPUnit class
  │   ├── checkout_test.php            # Pest functional test
  │   └── ProfileTest.php              # PHPUnit class
  └── Unit/
      ├── calculate_discount_test.php  # Pest functional test
      └── OrderTest.php                # PHPUnit class
  ```

  ### ✅ Goed: consistente PHPUnit-testsuite

  ```
  tests/
  ├── Feature/
  │   ├── UserRegistrationTest.php
  │   ├── CheckoutTest.php
  │   └── ProfileTest.php
  └── Unit/
      ├── DiscountCalculatorTest.php
      └── OrderTest.php
  ```

  ### ✅ Goed: consistente Pest-testsuite

  ```
  tests/
  ├── Feature/
  │   ├── UserRegistrationTest.php
  │   ├── CheckoutTest.php
  │   └── ProfileTest.php
  └── Unit/
      ├── DiscountCalculatorTest.php
      └── OrderTest.php
  ```

  <a name="project-documentation"></a>
  ## Projectdocumentatie

  Documenteer je gekozen testframework in je project:

  **In README.md of CONTRIBUTING.md:**

  ```markdown
  ## Testing

  This project uses Pest for all tests. When writing new tests:

  - Use `test()` or `it()` functions, not PHPUnit classes
  - Run tests with `./vendor/bin/pest` or `php artisan test`
  - Follow existing test patterns in the `tests/` directory

  See [Pest documentation](https://pestphp.com/docs) for syntax reference.
  ```

  Of voor PHPUnit:

  ```markdown
  ## Testing

  This project uses PHPUnit for all tests. When writing new tests:

  - Extend `Tests\TestCase` for feature tests
  - Extend `PHPUnit\Framework\TestCase` for unit tests
  - Run tests with `./vendor/bin/phpunit` or `php artisan test`
  - Follow PSR-4 naming conventions for test classes

  See [PHPUnit documentation](https://docs.phpunit.de) for syntax reference.
  ```

  <a name="more-info"></a>
  ## Meer informatie

  - [Migreren van PHPUnit naar Pest](https://pestphp.com/docs/migrating-from-phpunit-guide)
  - [Pest Converter door Laravel Shift](https://laravelshift.com/phpunit-to-pest-converter)
  - [Een PHPUnit-Testsuite Converteren naar Pest - Spatie](https://spatie.be/courses/testing-laravel-with-pest/converting-a-phpunit-testsuite-to-pest)
  - [Documentatie Pest Drift Plugin](https://pestphp.com/docs/plugins#drift)
  - [Laravel Testing Documentatie](https://laravel.com/docs/testing)
content_en: |-
  <a name="introduction"></a>
  ## Introduction

  While Pest is built on top of PHPUnit and the two frameworks are technically compatible, mixing both testing styles in the same Laravel project introduces unnecessary complexity and confusion. A consistent testing approach using either PHPUnit or Pest throughout your entire test suite is strongly recommended.

  <a name="why"></a>
  ## Why

  - **Reduced cognitive load**: Team members only need to learn and remember one testing syntax and approach
  - **Easier onboarding**: New developers joining the project face a simpler learning curve with a single, consistent testing style
  - **Command-line consistency**: Avoid confusion about which test runner to use (`vendor/bin/phpunit` vs `vendor/bin/pest`)
  - **Better maintainability**: Consistent patterns make it easier to update and refactor tests across the entire codebase
  - **Avoid compatibility issues**: Some PHPUnit annotations (like `@runTestsInSeparateProcesses`) are not compatible with Pest
  - **Clearer project standards**: A single testing framework establishes clear expectations for all contributors
  - **Simplified CI/CD pipelines**: No need to handle multiple test runners or special configurations

  <a name="suitable-for"></a>
  ## Suitable For

  - All Laravel projects, regardless of size
  - Teams with multiple developers
  - Projects with long-term maintenance requirements
  - Codebases where consistency and readability are priorities
  - Open-source projects where external contributors need clear guidelines

  <a name="less-suitable"></a>
  ## Less Suitable

  - Projects in active migration from PHPUnit to Pest (temporary mixed state is acceptable during transition)
  - Monorepos where different packages have legitimate reasons for using different frameworks (though even here, consistency is generally better)

  <a name="handling-existing-mixed-test-suites"></a>
  ## Handling Existing Mixed Test Suites

  If you inherit or currently have a project with mixed PHPUnit and Pest tests, consider migrating to a single framework:

  ### Migration to Pest

  Pest provides several tools to help convert PHPUnit tests:

  1. **Drift Plugin** (Automatic):
  ```bash
  composer require pestphp/pest-plugin-drift --dev
  ./vendor/bin/pest --drift
  ```

  2. **Laravel Shift** (Semi-Automatic):
     - Use the [Pest Converter](https://laravelshift.com/phpunit-to-pest-converter) service
     - Estimated time savings: ~3 hours for typical projects
     - Handles most conversions automatically

  3. **Manual Migration**:
     - Convert tests gradually as you work on related features
     - Use Pest's compatibility layer during the transition period
     - Ensure all new tests use the target framework

  ### Migration to PHPUnit

  If your team prefers PHPUnit:
  - Pest tests can be manually rewritten as PHPUnit test classes
  - This is typically more labor-intensive than migrating to Pest
  - Consider this route if the team strongly prefers class-based testing

  <a name="what-gets-converted"></a>
  ## What Gets Converted (PHPUnit to Pest)

  When using automated conversion tools:
  - ✅ Lifecycle methods (`setUp`, `tearDown`) → Pest hooks (`beforeEach`, `afterEach`)
  - ✅ Test methods → Pest `test()` or `it()` functions
  - ✅ Data providers → Pest datasets
  - ✅ Test groups → Pest `group()` chaining
  - ✅ PHPUnit assertions → Pest expectations (where available)

  **Important**: Private helper methods in test classes become functions, which may require manual fixes since they lose access to `$this`.

  <a name="exceptions"></a>
  ## Exceptions

  The only scenario where mixing frameworks is acceptable:

  **During Active Migration**: A temporary period where you're converting tests from one framework to another is acceptable, but should be:
  - Time-boxed (set a deadline for completion)
  - Clearly communicated to the team
  - Documented in the project README or contributing guidelines
  - Prioritized to minimize the mixed state duration

  <a name="examples"></a>
  ## Examples

  ### ❌ Bad: Mixed Test Suite

  ```
  tests/
  ├── Feature/
  │   ├── UserRegistrationTest.php    # PHPUnit class
  │   ├── checkout_test.php            # Pest functional test
  │   └── ProfileTest.php              # PHPUnit class
  └── Unit/
      ├── calculate_discount_test.php  # Pest functional test
      └── OrderTest.php                # PHPUnit class
  ```

  ### ✅ Good: Consistent PHPUnit Test Suite

  ```
  tests/
  ├── Feature/
  │   ├── UserRegistrationTest.php
  │   ├── CheckoutTest.php
  │   └── ProfileTest.php
  └── Unit/
      ├── DiscountCalculatorTest.php
      └── OrderTest.php
  ```

  ### ✅ Good: Consistent Pest Test Suite

  ```
  tests/
  ├── Feature/
  │   ├── UserRegistrationTest.php
  │   ├── CheckoutTest.php
  │   └── ProfileTest.php
  └── Unit/
      ├── DiscountCalculatorTest.php
      └── OrderTest.php
  ```

  <a name="project-documentation"></a>
  ## Project Documentation

  Document your chosen testing framework in your project:

  **In README.md or CONTRIBUTING.md:**

  ```markdown
  ## Testing

  This project uses Pest for all tests. When writing new tests:

  - Use `test()` or `it()` functions, not PHPUnit classes
  - Run tests with `./vendor/bin/pest` or `php artisan test`
  - Follow existing test patterns in the `tests/` directory

  See [Pest documentation](https://pestphp.com/docs) for syntax reference.
  ```

  Or for PHPUnit:

  ```markdown
  ## Testing

  This project uses PHPUnit for all tests. When writing new tests:

  - Extend `Tests\TestCase` for feature tests
  - Extend `PHPUnit\Framework\TestCase` for unit tests
  - Run tests with `./vendor/bin/phpunit` or `php artisan test`
  - Follow PSR-4 naming conventions for test classes

  See [PHPUnit documentation](https://docs.phpunit.de) for syntax reference.
  ```

  <a name="more-info"></a>
  ## More Info

  - [Migrating from PHPUnit to Pest](https://pestphp.com/docs/migrating-from-phpunit-guide)
  - [Pest Converter by Laravel Shift](https://laravelshift.com/phpunit-to-pest-converter)
  - [Converting a PHPUnit Testsuite to Pest - Spatie](https://spatie.be/courses/testing-laravel-with-pest/converting-a-phpunit-testsuite-to-pest)
  - [Pest Drift Plugin Documentation](https://pestphp.com/docs/plugins#drift)
  - [Laravel Testing Documentation](https://laravel.com/docs/testing)
best_practice_categories:
  - testing
category_slug: testing
category_title: Testen
category_title_en: Testing
source_path: testing/stick-to-one-testing-framework/BEST_PRACTICE.md
source_sha: c7034be11f69954eac43e50486b6e5bebde98c46
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/testing/stick-to-one-testing-framework/BEST_PRACTICE.md'
has_skill: true
skill_content: |-
  Use this skill when a Laravel task touches this best practice. It is self-contained so it can be installed independently by Laravel Boost or another agent-skill system.

  ## Core Guidance

  While Pest is built on top of PHPUnit and the two frameworks are technically compatible, mixing both testing styles in the same Laravel project introduces unnecessary complexity and confusion. A consistent testing approach using either PHPUnit or Pest throughout your entire test suite is strongly recommended.

  ## Why It Matters

  - **Reduced cognitive load**: Team members only need to learn and remember one testing syntax and approach
  - **Easier onboarding**: New developers joining the project face a simpler learning curve with a single, consistent testing style
  - **Command-line consistency**: Avoid confusion about which test runner to use (`vendor/bin/phpunit` vs `vendor/bin/pest`)
  - **Better maintainability**: Consistent patterns make it easier to update and refactor tests across the entire codebase
  - **Avoid compatibility issues**: Some PHPUnit annotations (like `@runTestsInSeparateProcesses`) are not compatible with Pest
  - **Clearer project standards**: A single testing framework establishes clear expectations for all contributors

  ## Apply When

  - All Laravel projects, regardless of size
  - Teams with multiple developers
  - Projects with long-term maintenance requirements
  - Codebases where consistency and readability are priorities
  - Open-source projects where external contributors need clear guidelines

  ## Be Careful When

  - Projects in active migration from PHPUnit to Pest (temporary mixed state is acceptable during transition)
  - Monorepos where different packages have legitimate reasons for using different frameworks (though even here, consistency is generally better)

  ## Canonical Source

  - Full best practice: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/testing/stick-to-one-testing-framework/BEST_PRACTICE.md
  - Dutch translation: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/testing/stick-to-one-testing-framework/translations/nl.md

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
skill_source_path: testing/stick-to-one-testing-framework/skill/SKILL.md
skill_github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/testing/stick-to-one-testing-framework/skill/SKILL.md'
skill_references: []
synced_at: 1785231076
---
