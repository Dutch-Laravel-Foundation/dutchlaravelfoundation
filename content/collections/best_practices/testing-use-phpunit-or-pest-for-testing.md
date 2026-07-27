---
id: 58761ce6-4e51-5f29-b985-39befd1572e9
blueprint: best_practices
title: 'Gebruik PHPUnit of Pest voor testen'
title_nl: 'Gebruik PHPUnit of Pest voor testen'
title_en: 'Use PHPUnit or Pest for Testing'
summary_nl: 'Laravel biedt first-class ondersteuning voor zowel PHPUnit als Pest als testframeworks. PHPUnit is het traditionele, class-gebaseerde testframework dat al vele jaren de standaard is in PHP, terwijl Pest een moderne, function-gebaseerde aanp...'
summary_en: 'Laravel provides first-class support for both PHPUnit and Pest as testing frameworks. PHPUnit is the traditional, class-based testing framework that has been the standard in PHP for many years, while Pest offers a modern, function-based app...'
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
  - title: 'Kiezen Tussen PHPUnit en Pest'
    anchor: kiezen-tussen-phpunit-en-pest
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
  - title: 'Choosing Between PHPUnit and Pest'
    anchor: choosing-between-phpunit-and-pest
  - title: 'More Info'
    anchor: more-info
content_nl: |-
  <a name="introduction"></a>
  ## Introductie

  Laravel biedt first-class ondersteuning voor zowel PHPUnit als Pest als testframeworks. PHPUnit is het traditionele, class-gebaseerde testframework dat al vele jaren de standaard is in PHP, terwijl Pest een moderne, function-gebaseerde aanpak biedt met een eenvoudigere syntax. Beide frameworks worden officieel ondersteund en integreren naadloos met de testfunctionaliteit van Laravel.

  <a name="why"></a>
  ## Waarom

  - **Officiële ondersteuning**: Beide frameworks worden officieel onderhouden en gedocumenteerd door Laravel
  - **Consistente tooling**: De testhelpers van Laravel werken identiek met beide frameworks
  - **Community-standaarden**: Het gebruik van deze frameworks garandeert compatibiliteit met community-packages en best practices
  - **Ingebouwde assertions**: Beide bieden uitgebreide assertion-libraries die specifiek zijn ontworpen voor Laravel-applicaties
  - **Eenvoudige opzet**: Laravel wordt vooraf geconfigureerd geleverd met PHPUnit, en Pest kan met minimale configuratie worden toegevoegd
  - **Continue verbetering**: Beide frameworks ontvangen regelmatig updates en verbeteringen samen met Laravel-releases

  <a name="suitable-for"></a>
  ## Geschikt Voor

  - Alle Laravel-applicaties die geautomatiseerd testen vereisen
  - Projecten waar teamleden de voorkeur geven aan traditioneel testen in xUnit-stijl (PHPUnit)
  - Projecten waar teamleden de voorkeur geven aan moderne, function-gebaseerde testsyntax (Pest)
  - Applicaties die moeten integreren met de testhelpers van Laravel (database factories, HTTP-testen, etc.)
  - Teams die overstappen van andere PHP-frameworks die PHPUnit gebruiken
  - Nieuwe projecten die op zoek zijn naar een schone, expressieve testsyntax (Pest)

  <a name="less-suitable"></a>
  ## Minder Geschikt

  - Applicaties met bestaande testsuites in andere PHP-testframeworks (hoewel migratie mogelijk is)
  - Projecten met specifieke eisen voor alternatieve testtools die niet compatibel zijn met de testarchitectuur van Laravel
  - Teams met een sterke voorkeur voor testframeworks buiten het PHP-ecosysteem (hoewel dit doorgaans zou wijzen op een geheel andere taalkeuze)

  <a name="examples"></a>
  ## Voorbeelden

  ### PHPUnit-voorbeeld

  ```php
  <?php

  namespace Tests\Feature;

  use Tests\TestCase;
  use App\Models\User;
  use Illuminate\Foundation\Testing\RefreshDatabase;

  class UserRegistrationTest extends TestCase
  {
      use RefreshDatabase;

      public function test_users_can_register(): void
      {
          $response = $this->post('/register', [
              'name' => 'John Doe',
              'email' => 'john@example.com',
              'password' => 'password',
              'password_confirmation' => 'password',
          ]);

          $response->assertRedirect('/dashboard');
          $this->assertDatabaseHas('users', [
              'email' => 'john@example.com',
          ]);
      }
  }
  ```

  ### Pest-voorbeeld

  ```php
  <?php

  use App\Models\User;

  test('users can register', function () {
      $response = $this->post('/register', [
          'name' => 'John Doe',
          'email' => 'john@example.com',
          'password' => 'password',
          'password_confirmation' => 'password',
      ]);

      $response->assertRedirect('/dashboard');
      expect(User::where('email', 'john@example.com')->exists())->toBeTrue();
  });
  ```

  <a name="choosing-between-phpunit-and-pest"></a>
  ## Kiezen Tussen PHPUnit en Pest

  **Kies PHPUnit als:**
  - Je team al bekend is met traditioneel testen in xUnit-stijl
  - Je een bestaand Laravel-project onderhoudt dat PHPUnit gebruikt
  - Je de voorkeur geeft aan een class-gebaseerde, objectgeoriënteerde teststructuur
  - Je compatibiliteit nodig hebt met oudere Laravel-versies (vóór Laravel 8)

  **Kies Pest als:**
  - Je een nieuw project start en een moderne testsyntax wilt
  - Je team de voorkeur geeft aan functionele programmeerstijlen
  - Je minder boilerplate-code in je tests wilt
  - Je Laravel 8 of nieuwer gebruikt

  **Let op:** Beide frameworks kunnen naast elkaar bestaan in hetzelfde project, wat een geleidelijke migratie mogelijk maakt indien gewenst.

  <a name="more-info"></a>
  ## Meer Info

  - [Laravel Testing Documentation](https://laravel.com/docs/testing)
  - [PHPUnit Documentation](https://phpunit.de/documentation.html)
  - [Pest PHP Official Website](https://pestphp.com/)
  - [Laravel Testing with Pest](https://laravel.com/docs/testing#introduction-to-pest)
  - [PHPUnit Assertions](https://docs.phpunit.de/en/11.0/assertions.html)
  - [Pest Expectations](https://pestphp.com/docs/expectations)
content_en: |-
  <a name="introduction"></a>
  ## Introduction

  Laravel provides first-class support for both PHPUnit and Pest as testing frameworks. PHPUnit is the traditional, class-based testing framework that has been the standard in PHP for many years, while Pest offers a modern, function-based approach with a simpler syntax. Both frameworks are officially supported and integrate seamlessly with Laravel's testing features.

  <a name="why"></a>
  ## Why

  - **Official support**: Both frameworks are officially maintained and documented by Laravel
  - **Consistent tooling**: Laravel's testing helpers work identically with both frameworks
  - **Community standards**: Using these frameworks ensures compatibility with community packages and best practices
  - **Built-in assertions**: Both provide comprehensive assertion libraries specifically designed for Laravel applications
  - **Easy setup**: Laravel comes pre-configured with PHPUnit, and Pest can be added with minimal configuration
  - **Continuous improvement**: Both frameworks receive regular updates and improvements alongside Laravel releases

  <a name="suitable-for"></a>
  ## Suitable For

  - All Laravel applications that require automated testing
  - Projects where team members prefer traditional xUnit-style testing (PHPUnit)
  - Projects where team members prefer modern, function-based testing syntax (Pest)
  - Applications that need to integrate with Laravel's testing helpers (database factories, HTTP testing, etc.)
  - Teams transitioning from other PHP frameworks that use PHPUnit
  - New projects looking for a clean, expressive testing syntax (Pest)

  <a name="less-suitable"></a>
  ## Less Suitable

  - Applications with existing test suites in other PHP testing frameworks (though migration is possible)
  - Projects with specific requirements for alternative testing tools not compatible with Laravel's testing architecture
  - Teams with strong preferences for testing frameworks outside the PHP ecosystem (though this would typically indicate a different language choice altogether)

  <a name="examples"></a>
  ## Examples

  ### PHPUnit Example

  ```php
  <?php

  namespace Tests\Feature;

  use Tests\TestCase;
  use App\Models\User;
  use Illuminate\Foundation\Testing\RefreshDatabase;

  class UserRegistrationTest extends TestCase
  {
      use RefreshDatabase;

      public function test_users_can_register(): void
      {
          $response = $this->post('/register', [
              'name' => 'John Doe',
              'email' => 'john@example.com',
              'password' => 'password',
              'password_confirmation' => 'password',
          ]);

          $response->assertRedirect('/dashboard');
          $this->assertDatabaseHas('users', [
              'email' => 'john@example.com',
          ]);
      }
  }
  ```

  ### Pest Example

  ```php
  <?php

  use App\Models\User;

  test('users can register', function () {
      $response = $this->post('/register', [
          'name' => 'John Doe',
          'email' => 'john@example.com',
          'password' => 'password',
          'password_confirmation' => 'password',
      ]);

      $response->assertRedirect('/dashboard');
      expect(User::where('email', 'john@example.com')->exists())->toBeTrue();
  });
  ```

  <a name="choosing-between-phpunit-and-pest"></a>
  ## Choosing Between PHPUnit and Pest

  **Choose PHPUnit if:**
  - Your team is already familiar with traditional xUnit-style testing
  - You're maintaining an existing Laravel project that uses PHPUnit
  - You prefer class-based, object-oriented testing structure
  - You need compatibility with older Laravel versions (pre-Laravel 8)

  **Choose Pest if:**
  - You're starting a new project and want a modern testing syntax
  - Your team prefers functional programming styles
  - You want less boilerplate code in your tests
  - You're using Laravel 8 or newer

  **Note:** Both frameworks can coexist in the same project, allowing for gradual migration if desired.

  <a name="more-info"></a>
  ## More Info

  - [Laravel Testing Documentation](https://laravel.com/docs/testing)
  - [PHPUnit Documentation](https://phpunit.de/documentation.html)
  - [Pest PHP Official Website](https://pestphp.com/)
  - [Laravel Testing with Pest](https://laravel.com/docs/testing#introduction-to-pest)
  - [PHPUnit Assertions](https://docs.phpunit.de/en/11.0/assertions.html)
  - [Pest Expectations](https://pestphp.com/docs/expectations)
best_practice_categories:
  - testing
category_slug: testing
category_title: Testen
category_title_en: Testing
source_path: testing/use-phpunit-or-pest-for-testing/BEST_PRACTICE.md
source_sha: c7034be11f69954eac43e50486b6e5bebde98c46
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/testing/use-phpunit-or-pest-for-testing/BEST_PRACTICE.md'
has_skill: true
skill_content: |-
  Use this skill when a Laravel task touches this best practice. It is self-contained so it can be installed independently by Laravel Boost or another agent-skill system.

  ## Core Guidance

  Laravel provides first-class support for both PHPUnit and Pest as testing frameworks. PHPUnit is the traditional, class-based testing framework that has been the standard in PHP for many years, while Pest offers a modern, function-based approach with a simpler syntax. Both frameworks are officially supported and integrate seamlessly with Laravel's testing features.

  ## Why It Matters

  - **Official support**: Both frameworks are officially maintained and documented by Laravel
  - **Consistent tooling**: Laravel's testing helpers work identically with both frameworks
  - **Community standards**: Using these frameworks ensures compatibility with community packages and best practices
  - **Built-in assertions**: Both provide comprehensive assertion libraries specifically designed for Laravel applications
  - **Easy setup**: Laravel comes pre-configured with PHPUnit, and Pest can be added with minimal configuration
  - **Continuous improvement**: Both frameworks receive regular updates and improvements alongside Laravel releases

  ## Apply When

  - All Laravel applications that require automated testing
  - Projects where team members prefer traditional xUnit-style testing (PHPUnit)
  - Projects where team members prefer modern, function-based testing syntax (Pest)
  - Applications that need to integrate with Laravel's testing helpers (database factories, HTTP testing, etc.)
  - Teams transitioning from other PHP frameworks that use PHPUnit
  - New projects looking for a clean, expressive testing syntax (Pest)

  ## Be Careful When

  - Applications with existing test suites in other PHP testing frameworks (though migration is possible)
  - Projects with specific requirements for alternative testing tools not compatible with Laravel's testing architecture
  - Teams with strong preferences for testing frameworks outside the PHP ecosystem (though this would typically indicate a different language choice altogether)

  ## Canonical Source

  - Full best practice: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/testing/use-phpunit-or-pest-for-testing/BEST_PRACTICE.md
  - Dutch translation: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/testing/use-phpunit-or-pest-for-testing/translations/nl.md

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
skill_source_path: testing/use-phpunit-or-pest-for-testing/skill/SKILL.md
skill_github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/testing/use-phpunit-or-pest-for-testing/skill/SKILL.md'
skill_references: []
---
