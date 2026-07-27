---
id: 573db12a-8b8b-54fa-a6cd-10e156338c36
blueprint: best_practices
title: 'Houd je aan één standaard'
title_nl: 'Houd je aan één standaard'
title_en: 'Adhere to a Single Standard'
summary_nl: 'Uiteindelijk maakt het niet zoveel uit *welke* standaard je kiest, zolang je je er maar aan houdt. Alle (eigen) code die in een project wordt geschreven, moet zich eraan houden. Discussies over welke standaard het beste is, kosten vaak meer...'
summary_en: "In the end it doesn't really matter *which* standard you choose, as long as you stick to it. All (own) code written in a project must adhere to it. Discussions on which standard can often times take more energy out of the actual work than j..."
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

  Uiteindelijk maakt het niet zoveel uit *welke* standaard je kiest, zolang je je er maar aan houdt. Alle (eigen) code die in een project wordt geschreven, moet zich eraan houden. Discussies over welke standaard het beste is, kosten vaak meer energie dan het werk zelf dan wanneer je je gewoon aan de gekozen standaard houdt. Meningen komen en gaan, maar door de community gedefinieerde standaarden zoals PSR-1, PSR-2, PSR-12 en tot slot PER-2 worden breed geaccepteerd.

  <a name="why"></a>
  ## Waarom

  - Door één standaard te hanteren heeft alle code dezelfde cognitieve belasting voor de (menselijke) lezer. Omdat code vaker gelezen dan geschreven wordt, maakt dit het werken met de code gemakkelijker.
  - Het volgen van een door de community gedreven standaard maakt het eenvoudiger om nieuwe developers uit die community in te werken op een bestaand project. Dit verlicht de last voor nieuwe developers om een nieuwe standaard te moeten leren.
  - De uniformiteit die door de gekozen standaard wordt afgedwongen heeft als bijkomend effect dat je je ook aan meer gangbare coding guidelines uit de industrie houdt.

  <a name="suitable-for"></a>
  ## Geschikt Voor

  - Projecten van elke omvang

  <a name="less-suitable"></a>
  ## Minder Geschikt

  - N.v.t.

  <a name="more-info"></a>
  ## Meer Info

  - [PER Coding Style](https://www.php-fig.org/per/coding-style/)
  - [PSR-12: Extended Coding Style Guide](https://www.php-fig.org/psr/psr-12/)
  - [Use Automation to Adhere to a Standard](../../use-automation-to-adhere-to-a-standard/translations/nl.md) — dwing de door jou gekozen standaard af met tooling
content_en: |-
  <a name="introduction"></a>
  ## Introduction

  In the end it doesn't really matter *which* standard you choose, as long as you stick to it. All (own) code written in a project must adhere to it. Discussions on which standard can often times take more energy out of the actual work than just adhering to the chosen standard. Opinions come and go, but community defined standards like PSR-1, PSR-2, PSR-12 and finally PER-2 are broadly accepted.

  <a name="why"></a>
  ## Why

  - By implementing a single standard all code has the same amount of cognitive load for the (human) reader. As code is more often read than it is written, this eases with working with the code.
  - Following a community driven standard allows for easier ramping up of new developers from that community to an existing project. This lessens the burden on new developers having to learn a new standard.
  - Uniformity enforced by the chosen standard also has side effects of adhering to more industry standard coding guidelines.

  <a name="suitable-for"></a>
  ## Suitable For

  - Projects of all sizes

  <a name="less-suitable"></a>
  ## Less Suitable

  - N/A

  <a name="more-info"></a>
  ## More Info

  - [PER Coding Style](https://www.php-fig.org/per/coding-style/)
  - [PSR-12: Extended Coding Style Guide](https://www.php-fig.org/psr/psr-12/)
  - [Use Automation to Adhere to a Standard](../use-automation-to-adhere-to-a-standard/BEST_PRACTICE.md) — enforce your chosen standard with tooling
best_practice_categories:
  - code-standards
category_slug: code-standards
category_title: Codekwaliteit
category_title_en: 'Code Standards'
source_path: code-standards/adhere-to-a-single-standard/BEST_PRACTICE.md
source_sha: c7034be11f69954eac43e50486b6e5bebde98c46
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/code-standards/adhere-to-a-single-standard/BEST_PRACTICE.md'
has_skill: true
skill_content: |-
  Use this skill when a Laravel task touches this best practice. It is self-contained so it can be installed independently by Laravel Boost or another agent-skill system.

  ## Core Guidance

  In the end it doesn't really matter *which* standard you choose, as long as you stick to it. All (own) code written in a project must adhere to it. Discussions on which standard can often times take more energy out of the actual work than just adhering to the chosen standard. Opinions come and go, but community defined standards like PSR-1, PSR-2, PSR-12 and finally PER-2 are broadly accepted.

  ## Why It Matters

  - By implementing a single standard all code has the same amount of cognitive load for the (human) reader. As code is more often read than it is written, this eases with working with the code.
  - Following a community driven standard allows for easier ramping up of new developers from that community to an existing project. This lessens the burden on new developers having to learn a new standard.
  - Uniformity enforced by the chosen standard also has side effects of adhering to more industry standard coding guidelines.

  ## Apply When

  - Projects of all sizes

  ## Be Careful When

  - N/A

  ## Canonical Source

  - Full best practice: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/code-standards/adhere-to-a-single-standard/BEST_PRACTICE.md
  - Dutch translation: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/code-standards/adhere-to-a-single-standard/translations/nl.md

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
skill_source_path: code-standards/adhere-to-a-single-standard/skill/SKILL.md
skill_github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/code-standards/adhere-to-a-single-standard/skill/SKILL.md'
skill_references: []
synced_at: 1785159222
---
