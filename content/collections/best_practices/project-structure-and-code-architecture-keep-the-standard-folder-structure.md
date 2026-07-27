---
id: 17f5f2d9-e957-5a9f-b9a2-055e431b8e23
blueprint: best_practices
title: 'Houd de Standaard Mappenstructuur Aan'
title_nl: 'Houd de Standaard Mappenstructuur Aan'
title_en: 'Keep the Standard Folder Structure'
summary_nl: 'Laravel heeft een standaard mappenstructuur. De structuur kan naar wens worden aangepast, maar het is over het algemeen niet aan te raden om hier te veel van af te wijken.'
summary_en: 'Laravel has a standard folder structure. The structure can be adjusted as desired, but it is generally not recommended to deviate too much from this.'
chapters_nl:
  - title: Introductie
    anchor: introductie
  - title: Waarom
    anchor: waarom
  - title: 'Geschikt Voor'
    anchor: geschikt-voor
  - title: 'Minder Geschikt'
    anchor: minder-geschikt
  - title: 'Meer Informatie'
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
  - title: 'More Info'
    anchor: more-info
content_nl: |-
  <a name="introduction"></a>
  ## Introductie

  Laravel heeft een standaard mappenstructuur. De structuur kan naar wens worden aangepast, maar het is over het algemeen niet aan te raden om hier te veel van af te wijken.

  <a name="why"></a>
  ## Waarom

  - Door niet te veel af te wijken van de standaard mappenstructuur blijft je project overzichtelijk en herkenbaar. Dit betaalt zich uit bij het uitvoeren van Laravel-upgrades en bij het onboarden en samenwerken met andere developers.
  - Je hoeft het wiel niet opnieuw uit te vinden. De mappenstructuur is een bewuste indeling, waardoor je minder keuzes hoeft te maken over waar je bepaalde zaken plaatst. Dit bespaart tijd en zorgt ervoor dat nieuwe functionaliteiten sneller ontwikkeld kunnen worden.
  - Je project is beter compatibel met packages. Doordat alles staat waar het hoort, kom je minder fouten tegen.

  <a name="suitable-for"></a>
  ## Geschikt Voor

  - Kleine tot middelgrote projecten

  <a name="less-suitable"></a>
  ## Minder Geschikt

  - Grote projecten. Voor grote projecten kan het nuttig zijn om met modules te werken.

  <a name="more-info"></a>
  ## Meer Informatie

  - [Laravel Folder Structure Explained (YouTube)](https://www.youtube.com/watch?v=KBigS5vLwZk)
  - [Laravel Best Practices — Stick to the Default Folder Structure](https://benjamincrozat.com/laravel-best-practices#stick-to-the-default-folder-structure)
  - [Laravel Architecture Best Practices — Keep the Default Folder Structure](https://benjamincrozat.com/laravel-architecture-best-practices#keep-the-default-folder-structure)
  - [Use Action Classes for Business Logic](../../use-action-classes-for-business-logic/translations/nl.md) — voor het organiseren van business logic binnen de standaard structuur
content_en: |-
  <a name="introduction"></a>
  ## Introduction

  Laravel has a standard folder structure. The structure can be adjusted as desired, but it is generally not recommended to deviate too much from this.

  <a name="why"></a>
  ## Why

  - By not deviating too much from the standard folder structure, your project remains clear and recognizable. This pays off when implementing Laravel upgrades and onboarding and collaborating with other developers.
  - You don't have to reinvent the wheel. The folder structure is a conscious division, so you have to make fewer choices about where to place certain things. This saves time and allows new functionalities to be developed faster.
  - Your project is more compatible with packages. Because everything is where it belongs, you will encounter fewer errors.

  <a name="suitable-for"></a>
  ## Suitable For

  - Small to medium projects

  <a name="less-suitable"></a>
  ## Less Suitable

  - Major projects. For large projects it can be useful to work with modules.

  <a name="more-info"></a>
  ## More Info

  - [Laravel Folder Structure Explained (YouTube)](https://www.youtube.com/watch?v=KBigS5vLwZk)
  - [Laravel Best Practices — Stick to the Default Folder Structure](https://benjamincrozat.com/laravel-best-practices#stick-to-the-default-folder-structure)
  - [Laravel Architecture Best Practices — Keep the Default Folder Structure](https://benjamincrozat.com/laravel-architecture-best-practices#keep-the-default-folder-structure)
  - [Use Action Classes for Business Logic](../use-action-classes-for-business-logic/BEST_PRACTICE.md) — for organizing business logic within the standard structure
best_practice_categories:
  - project-structure-and-code-architecture
category_slug: project-structure-and-code-architecture
category_title: 'Projectstructuur en architectuur'
category_title_en: 'Project Structure and Code Architecture'
source_path: project-structure-and-code-architecture/keep-the-standard-folder-structure/BEST_PRACTICE.md
source_sha: c7034be11f69954eac43e50486b6e5bebde98c46
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/project-structure-and-code-architecture/keep-the-standard-folder-structure/BEST_PRACTICE.md'
has_skill: true
skill_content: |-
  Use this skill when a Laravel task touches this best practice. It is self-contained so it can be installed independently by Laravel Boost or another agent-skill system.

  ## Core Guidance

  Laravel has a standard folder structure. The structure can be adjusted as desired, but it is generally not recommended to deviate too much from this.

  ## Why It Matters

  - By not deviating too much from the standard folder structure, your project remains clear and recognizable. This pays off when implementing Laravel upgrades and onboarding and collaborating with other developers.
  - You don't have to reinvent the wheel. The folder structure is a conscious division, so you have to make fewer choices about where to place certain things. This saves time and allows new functionalities to be developed faster.
  - Your project is more compatible with packages. Because everything is where it belongs, you will encounter fewer errors.

  ## Apply When

  - Small to medium projects

  ## Be Careful When

  - Major projects. For large projects it can be useful to work with modules.

  ## Canonical Source

  - Full best practice: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/project-structure-and-code-architecture/keep-the-standard-folder-structure/BEST_PRACTICE.md
  - Dutch translation: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/project-structure-and-code-architecture/keep-the-standard-folder-structure/translations/nl.md

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
skill_source_path: project-structure-and-code-architecture/keep-the-standard-folder-structure/skill/SKILL.md
skill_github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/project-structure-and-code-architecture/keep-the-standard-folder-structure/skill/SKILL.md'
skill_references: []
synced_at: 1785159222
---
