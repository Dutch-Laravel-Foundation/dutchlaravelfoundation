---
id: a6f2816b-23fc-59a7-8609-bf3ae8dcc2d1
blueprint: best_practices
title: 'Keep the Standard Folder Structure'
summary: 'Laravel has a standard folder structure. The structure can be adjusted as desired, but it is generally not recommended to deviate too much from this.'
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
  - project-structure-and-code-architecture
category_slug: project-structure-and-code-architecture
category_title: 'Project Structure and Code Architecture'
source_path: project-structure-and-code-architecture/keep-the-standard-folder-structure.md
source_sha: c0967a7f4ce3d79bcd2f427ac0c16eb1b20adff8
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c0967a7f4ce3d79bcd2f427ac0c16eb1b20adff8/project-structure-and-code-architecture/keep-the-standard-folder-structure.md'
boost_skill_path: null
related_files: []
---
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
- [Use Action Classes for Business Logic](./use-action-classes-for-business-logic.md) — for organizing business logic within the standard structure
