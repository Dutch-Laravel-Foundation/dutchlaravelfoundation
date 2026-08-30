---
id: 983f10b9-fbae-5ea7-96bf-419df6f81126
blueprint: best_practices
title: 'Adhere to a Single Standard'
summary: "In the end it doesn't really matter *which* standard you choose, as long as you stick to it. All (own) code written in a project must adhere to it. Discussions on which standard can often times take more energy out of the actual work than j..."
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
source_path: code-standards/adhere-to-a-single-standard.md
source_sha: c0967a7f4ce3d79bcd2f427ac0c16eb1b20adff8
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c0967a7f4ce3d79bcd2f427ac0c16eb1b20adff8/code-standards/adhere-to-a-single-standard.md'
boost_skill_path: null
related_files: []
---
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
- [Use Automation to Adhere to a Standard](./use-automation-to-adhere-to-a-standard.md) — enforce your chosen standard with tooling
