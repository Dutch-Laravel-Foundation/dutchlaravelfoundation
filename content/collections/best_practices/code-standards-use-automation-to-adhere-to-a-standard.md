---
id: fb52e8f5-289c-5cdf-88a5-22710229ff77
blueprint: best_practices
title: 'Use Automation to Adhere to a Standard'
summary: "Adhering to a given standard can be done manually or via automated tooling. This tooling can come in the form of IDE plugins, git hooks, CI/CD steps. The fact that the developer doesn't have to 'think' about the *exact* rules defined in the..."
chapters:
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
best_practice_categories:
  - code-standards
category_slug: code-standards
category_title: 'Code Standards'
source_path: code-standards/use-automation-to-adhere-to-a-standard.md
source_sha: c0967a7f4ce3d79bcd2f427ac0c16eb1b20adff8
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c0967a7f4ce3d79bcd2f427ac0c16eb1b20adff8/code-standards/use-automation-to-adhere-to-a-standard.md'
boost_skill_path: null
related_files: []
---
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
- [Adhere to a Single Standard](./adhere-to-a-single-standard.md) — choose which standard to enforce
