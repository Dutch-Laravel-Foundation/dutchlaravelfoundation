---
id: 60c54b4d-8d4d-5215-ab8e-a264fd4cc49a
blueprint: best_practices
title: 'Use Policies and Gates for Authorization'
summary: 'Policies and gates are standard components within Laravel that can be used to determine whether an action may be performed.'
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
source_path: project-structure-and-code-architecture/use-policies-and-gates-for-authorization.md
source_sha: c0967a7f4ce3d79bcd2f427ac0c16eb1b20adff8
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c0967a7f4ce3d79bcd2f427ac0c16eb1b20adff8/project-structure-and-code-architecture/use-policies-and-gates-for-authorization.md'
boost_skill_path: null
related_files: []
---
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
- [Prevent Common Vulnerabilities](../security-and-authentication/prevent-common-vulnerabilities.md) — for additional security practices like mass assignment protection and CSRF
