---
id: 579c5355-3e76-5906-9790-0bca947736e8
blueprint: best_practices
title: 'Gebruik Policies en Gates voor Autorisatie'
title_nl: 'Gebruik Policies en Gates voor Autorisatie'
title_en: 'Use Policies and Gates for Authorization'
summary_nl: 'Policies en gates zijn standaardcomponenten binnen Laravel die gebruikt kunnen worden om te bepalen of een actie uitgevoerd mag worden.'
summary_en: 'Policies and gates are standard components within Laravel that can be used to determine whether an action may be performed.'
chapters_nl:
  - title: Introductie
    anchor: introductie
  - title: Waarom
    anchor: waarom
  - title: 'Geschikt Voor'
    anchor: geschikt-voor
  - title: 'Minder Geschikt'
    anchor: minder-geschikt
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
  - title: 'More Info'
    anchor: more-info
content_nl: |-
  <a name="introduction"></a>
  ## Introductie

  Policies en gates zijn standaardcomponenten binnen Laravel die gebruikt kunnen worden om te bepalen of een actie uitgevoerd mag worden.

  <a name="why"></a>
  ## Waarom

  - Het stelt je in staat om autorisatielogica op één plek te zetten (in een Policy-class of Service Provider) in plaats van in losse if-statements. Dit voorkomt gedupliceerde code
  - De autorisatiecode is beter herbruikbaar
  - Het ontkoppelt autorisatiecode van businesslogica (separation of concerns)
  - First class citizen binnen Laravel, wat betekent dat het goed onderhouden wordt, en policies en gates kunnen ook in unit tests gebruikt worden.

  <a name="suitable-for"></a>
  ## Geschikt Voor

  - Vrijwel elke Laravel-applicatie

  <a name="less-suitable"></a>
  ## Minder Geschikt

  - Voor kleinere applicaties kan het wat onnodige overhead veroorzaken om zeer strikte policies en gates toe te passen. Het kan dan nuttiger zijn om autorisatie met losse if-statements te regelen

  <a name="more-info"></a>
  ## Meer Info

  - [Laravel Authorization Documentation](https://laravel.com/docs/authorization)
  - [Spatie Laravel Permission Package](https://spatie.be/docs/laravel-permission/v6/introduction) — sla rollen en permissies op in de database
  - [Prevent Common Vulnerabilities](../../../security-and-authentication/prevent-common-vulnerabilities/translations/nl.md) — voor aanvullende beveiligingspraktijken zoals mass assignment-bescherming en CSRF
content_en: |-
  <a name="introduction"></a>
  ## Introduction

  Policies and gates are standard components within Laravel that can be used to determine whether an action may be performed.

  <a name="why"></a>
  ## Why

  - It allows you to put authorization logic in one place (in a Policy class or Service Provider) instead of separate if statements. This prevents duplicated code
  - The authorization code is more reusable
  - It is decoupled authorization code from business logic (separation of concerns)
  - First class citizen within Laravel, which means it is well maintained, and policies gates can also be used in unit tests.

  <a name="suitable-for"></a>
  ## Suitable For

  - Almost every Laravel application

  <a name="less-suitable"></a>
  ## Less Suitable

  - For smaller applications it can cause some unnecessary government effort to apply very strict policies and gates. It may be more useful to provide authorization with separate if statements

  <a name="more-info"></a>
  ## More Info

  - [Laravel Authorization Documentation](https://laravel.com/docs/authorization)
  - [Spatie Laravel Permission Package](https://spatie.be/docs/laravel-permission/v6/introduction) — save roles and permissions to the database
  - [Prevent Common Vulnerabilities](../../security-and-authentication/prevent-common-vulnerabilities/BEST_PRACTICE.md) — for additional security practices like mass assignment protection and CSRF
best_practice_categories:
  - project-structure-and-code-architecture
category_slug: project-structure-and-code-architecture
category_title: 'Projectstructuur en architectuur'
category_title_en: 'Project Structure and Code Architecture'
source_path: project-structure-and-code-architecture/use-policies-and-gates-for-authorization/BEST_PRACTICE.md
source_sha: c7034be11f69954eac43e50486b6e5bebde98c46
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/project-structure-and-code-architecture/use-policies-and-gates-for-authorization/BEST_PRACTICE.md'
has_skill: true
skill_content: |-
  Use this skill when a Laravel task touches this best practice. It is self-contained so it can be installed independently by Laravel Boost or another agent-skill system.

  ## Core Guidance

  Policies and gates are standard components within Laravel that can be used to determine whether an action may be performed.

  ## Why It Matters

  - It allows you to put authorization logic in one place (in a Policy class or Service Provider) instead of separate if statements. This prevents duplicated code
  - The authorization code is more reusable
  - It is decoupled authorization code from business logic (separation of concerns)
  - First class citizen within Laravel, which means it is well maintained, and policies gates can also be used in unit tests.

  ## Apply When

  - Almost every Laravel application

  ## Be Careful When

  - For smaller applications it can cause some unnecessary government effort to apply very strict policies and gates. It may be more useful to provide authorization with separate if statements

  ## Canonical Source

  - Full best practice: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/project-structure-and-code-architecture/use-policies-and-gates-for-authorization/BEST_PRACTICE.md
  - Dutch translation: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/project-structure-and-code-architecture/use-policies-and-gates-for-authorization/translations/nl.md

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
skill_source_path: project-structure-and-code-architecture/use-policies-and-gates-for-authorization/skill/SKILL.md
skill_github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/project-structure-and-code-architecture/use-policies-and-gates-for-authorization/skill/SKILL.md'
skill_references: []
synced_at: 1785159222
---
