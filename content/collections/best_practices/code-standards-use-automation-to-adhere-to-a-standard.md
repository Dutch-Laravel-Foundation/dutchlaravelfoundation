---
id: 24d6667f-8d69-5eec-b77f-1721b0866e0b
blueprint: best_practices
title: 'Houd je aan een standaard met automatisering'
title_nl: 'Houd je aan een standaard met automatisering'
title_en: 'Use Automation to Adhere to a Standard'
summary_nl: "Het naleven van een bepaalde standaard kan handmatig of via geautomatiseerde tooling. Deze tooling kan de vorm aannemen van IDE-plugins, git hooks of CI/CD-stappen. Doordat de ontwikkelaar niet hoeft na te 'denken' over de *exacte* regels d..."
summary_en: "Adhering to a given standard can be done manually or via automated tooling. This tooling can come in the form of IDE plugins, git hooks, CI/CD steps. The fact that the developer doesn't have to 'think' about the *exact* rules defined in the..."
chapters_nl:
  - title: Introductie
    anchor: introductie
  - title: Waarom
    anchor: waarom
  - title: 'Geschikt voor'
    anchor: geschikt-voor
  - title: 'Minder geschikt voor'
    anchor: minder-geschikt-voor
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
  - title: 'More Info'
    anchor: more-info
content_nl: |-
  <a name="introduction"></a>
  ## Introductie

  Het naleven van een bepaalde standaard kan handmatig of via geautomatiseerde tooling. Deze tooling kan de vorm aannemen van IDE-plugins, git hooks of CI/CD-stappen. Doordat de ontwikkelaar niet hoeft na te 'denken' over de *exacte* regels die in de standaard zijn gedefinieerd, ontstaat er ruimte voor het daadwerkelijke probleem zelf en niet voor de opmaak van de code.

  <a name="why"></a>
  ## Waarom

  - Door geautomatiseerde tooling in te zetten voor het naleven van de code (git hook, IDE-functies, geautomatiseerde CI/CD-acties) wordt er minder (menselijke) tijd en moeite besteed aan opmaak dan nodig is. Het kan en zou een non-issue moeten worden.
  - Automatisering zorgt voor een consistente toepassing van de gekozen standaard in de volledige codebase. Wanneer regels worden aangepast en/of bijgewerkt, kan automatisering opnieuw zorgen voor een consistente toepassing van de updates.

  <a name="suitable-for"></a>
  ## Geschikt voor

  - Middelgrote tot grote projecten

  <a name="less-suitable"></a>
  ## Minder geschikt voor

  - Voor kleinere projecten kan de IDE-integratie of het af en toe handmatig draaien van een tool volstaan om consistentie te waarborgen.

  <a name="more-info"></a>
  ## Meer info

  - [Laravel Pint Documentatie](https://laravel.com/docs/pint)
  - [Laravel Pint GitHub Action](https://github.com/marketplace/actions/laravel-pint)
  - [Houd je aan een Enkele Standaard](../../adhere-to-a-single-standard/translations/nl.md)
content_en: |-
  <a name="introduction"></a>
  ## Introduction

  Adhering to a given standard can be done manually or via automated tooling. This tooling can come in the form of IDE plugins, git hooks, CI/CD steps. The fact that the developer doesn't have to 'think' about the *exact* rules defined in the standard leaves room for the actual problem at hand and not the formatting of the code itself.

  <a name="why"></a>
  ## Why

  - By utilizing automated tooling for adhering to the code (git hook, IDE features, CI/CD automated actions) less (human) time and effort is spent on formatting than needed. It can and should become a non-issue.
  - Automation ensures consistent application of the chosen standard across the entire codebase. When rules are adjusted and/or updated automation can again ensure consistent application of the updates.

  <a name="suitable-for"></a>
  ## Suitable For

  - Medium to large projects

  <a name="less-suitable"></a>
  ## Less Suitable

  - Smaller projects might suffice with just the IDE-integration or manually running of a tool once in a while to ensure consistency.

  <a name="more-info"></a>
  ## More Info

  - [Laravel Pint Documentation](https://laravel.com/docs/pint)
  - [Laravel Pint GitHub Action](https://github.com/marketplace/actions/laravel-pint)
  - [Adhere to a Single Standard](../adhere-to-a-single-standard/BEST_PRACTICE.md), choose which standard to enforce
best_practice_categories:
  - code-standards
category_slug: code-standards
category_title: Codekwaliteit
category_title_en: 'Code Standards'
source_path: code-standards/use-automation-to-adhere-to-a-standard/BEST_PRACTICE.md
source_sha: c7034be11f69954eac43e50486b6e5bebde98c46
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/code-standards/use-automation-to-adhere-to-a-standard/BEST_PRACTICE.md'
has_skill: true
skill_content: |-
  Use this skill when a Laravel task touches this best practice. It is self-contained so it can be installed independently by Laravel Boost or another agent-skill system.

  ## Core Guidance

  Adhering to a given standard can be done manually or via automated tooling. This tooling can come in the form of IDE plugins, git hooks, CI/CD steps. The fact that the developer doesn't have to 'think' about the *exact* rules defined in the standard leaves room for the actual problem at hand and not the formatting of the code itself.

  ## Why It Matters

  - By utilizing automated tooling for adhering to the code (git hook, IDE features, CI/CD automated actions) less (human) time and effort is spent on formatting than needed. It can and should become a non-issue.
  - Automation ensures consistent application of the chosen standard across the entire codebase. When rules are adjusted and/or updated automation can again ensure consistent application of the updates.

  ## Apply When

  - Medium to large projects

  ## Be Careful When

  - Smaller projects might suffice with just the IDE-integration or manually running of a tool once in a while to ensure consistency.

  ## Canonical Source

  - Full best practice: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/code-standards/use-automation-to-adhere-to-a-standard/BEST_PRACTICE.md
  - Dutch translation: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/code-standards/use-automation-to-adhere-to-a-standard/translations/nl.md

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
skill_source_path: code-standards/use-automation-to-adhere-to-a-standard/skill/SKILL.md
skill_github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/code-standards/use-automation-to-adhere-to-a-standard/skill/SKILL.md'
skill_references: []
synced_at: 1785231871
---
