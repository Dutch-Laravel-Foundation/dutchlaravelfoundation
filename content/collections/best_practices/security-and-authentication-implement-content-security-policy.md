---
id: 2664b42e-f909-5387-8701-d2ade778fe08
blueprint: best_practices
title: 'Implementeer een Content Security Policy (CSP)'
title_nl: 'Implementeer een Content Security Policy (CSP)'
title_en: 'Implement Content Security Policy (CSP)'
summary_nl: 'Een Content Security Policy (CSP) is een security header die helpt om Cross-Site Scripting (XSS)-aanvallen en andere code-injectiekwetsbaarheden te voorkomen, door te definiëren welke bronnen van content geladen en uitgevoerd mogen worden i...'
summary_en: 'Content Security Policy (CSP) is a security header that helps prevent Cross-Site Scripting (XSS) attacks and other code injection vulnerabilities by defining which sources of content are allowed to be loaded and executed on your web applica...'
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

  Een Content Security Policy (CSP) is een security header die helpt om Cross-Site Scripting (XSS)-aanvallen en andere code-injectiekwetsbaarheden te voorkomen, door te definiëren welke bronnen van content geladen en uitgevoerd mogen worden in je webapplicatie.

  <a name="why"></a>
  ## Waarom

  - **Voorkomt XSS-aanvallen** - Blokkeert de uitvoering van kwaadaardige scripts die in je applicatie worden geïnjecteerd
  - **Verkleint risico's op code-injectie** - Beperkt het gebruik van inline scripts en eval()
  - **Voorkomt data-exfiltratie** - Bepaalt waar je applicatie data naartoe mag sturen
  - **Bescherming tegen clickjacking** - Voorkomt dat je site in kwaadaardige frames wordt ingebed
  - **Voorkomt mixed content** - Zorgt ervoor dat HTTPS-sites geen onveilige HTTP-bronnen laden
  - **Compliance-eisen** - Veel securitystandaarden vereisen inmiddels een CSP-implementatie

  <a name="suitable-for"></a>
  ## Geschikt Voor

  - Alle Laravel-applicaties, met name die gevoelige data verwerken
  - Applicaties met door gebruikers gegenereerde content
  - E-commerce- en financiële applicaties
  - Applicaties die aan security-compliance moeten voldoen
  - Publiek toegankelijke webapplicaties

  <a name="less-suitable"></a>
  ## Minder Geschikt

  - Legacy-applicaties met veel inline scripts die niet gerefactord kunnen worden
  - Applicaties in een vroeg ontwikkelstadium met snel veranderende contentbronnen
  - Interne tools met zeer gecontroleerde gebruikerstoegang (hoewel nog steeds aanbevolen)

  <a name="more-info"></a>
  ## Meer Info

  - [Spatie Laravel CSP Package](https://github.com/spatie/laravel-csp) — aanbevolen Laravel-implementatie
  - [MDN Content Security Policy Documentation](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP)
  - [CSP Level 3 Specification](https://www.w3.org/TR/CSP3/)
  - [Google CSP Evaluator](https://csp-evaluator.withgoogle.com/)
  - [OWASP CSP Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Content_Security_Policy_Cheat_Sheet.html)
  - [Dutch Laravel Foundation CSP Guide](https://dutchlaravelfoundation.nl/kennis/verbeter-de-beveiliging-van-je-laravel-applicatie-met-csp-content-security-policies)
  - [Prevent Common Vulnerabilities](../../prevent-common-vulnerabilities/translations/nl.md) — voor XSS-output-escaping, CSRF, SQL-injectie en andere securitypraktijken
content_en: |-
  <a name="introduction"></a>
  ## Introduction

  Content Security Policy (CSP) is a security header that helps prevent Cross-Site Scripting (XSS) attacks and other code injection vulnerabilities by defining which sources of content are allowed to be loaded and executed on your web application.

  <a name="why"></a>
  ## Why

  - **Prevents XSS attacks** - Blocks execution of malicious scripts injected into your application
  - **Reduces code injection risks** - Restricts inline scripts and eval() usage
  - **Prevents data exfiltration** - Controls where your application can send data
  - **Clickjacking protection** - Prevents your site from being embedded in malicious frames
  - **Mixed content prevention** - Ensures HTTPS sites don't load insecure HTTP resources
  - **Compliance requirements** - Many security standards now require CSP implementation

  <a name="suitable-for"></a>
  ## Suitable For

  - All Laravel applications, especially those handling sensitive data
  - Applications with user-generated content
  - E-commerce and financial applications
  - Applications requiring security compliance
  - Public-facing web applications

  <a name="less-suitable"></a>
  ## Less Suitable

  - Legacy applications with extensive inline scripts that cannot be refactored
  - Applications in early development with rapidly changing content sources
  - Internal tools with very controlled user access (though still recommended)

  <a name="more-info"></a>
  ## More Info

  - [Spatie Laravel CSP Package](https://github.com/spatie/laravel-csp) — recommended Laravel implementation
  - [MDN Content Security Policy Documentation](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP)
  - [CSP Level 3 Specification](https://www.w3.org/TR/CSP3/)
  - [Google CSP Evaluator](https://csp-evaluator.withgoogle.com/)
  - [OWASP CSP Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Content_Security_Policy_Cheat_Sheet.html)
  - [Dutch Laravel Foundation CSP Guide](https://dutchlaravelfoundation.nl/kennis/verbeter-de-beveiliging-van-je-laravel-applicatie-met-csp-content-security-policies)
  - [Prevent Common Vulnerabilities](../prevent-common-vulnerabilities/BEST_PRACTICE.md) — for XSS output escaping, CSRF, SQL injection, and other security practices
best_practice_categories:
  - security-and-authentication
category_slug: security-and-authentication
category_title: 'Security en authenticatie'
category_title_en: 'Security & Authentication'
source_path: security-and-authentication/implement-content-security-policy/BEST_PRACTICE.md
source_sha: c7034be11f69954eac43e50486b6e5bebde98c46
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/security-and-authentication/implement-content-security-policy/BEST_PRACTICE.md'
has_skill: true
skill_content: |-
  Use this skill when a Laravel task touches this best practice. It is self-contained so it can be installed independently by Laravel Boost or another agent-skill system.

  ## Core Guidance

  Content Security Policy (CSP) is a security header that helps prevent Cross-Site Scripting (XSS) attacks and other code injection vulnerabilities by defining which sources of content are allowed to be loaded and executed on your web application.

  ## Why It Matters

  - **Prevents XSS attacks** - Blocks execution of malicious scripts injected into your application
  - **Reduces code injection risks** - Restricts inline scripts and eval() usage
  - **Prevents data exfiltration** - Controls where your application can send data
  - **Clickjacking protection** - Prevents your site from being embedded in malicious frames
  - **Mixed content prevention** - Ensures HTTPS sites don't load insecure HTTP resources
  - **Compliance requirements** - Many security standards now require CSP implementation

  ## Apply When

  - All Laravel applications, especially those handling sensitive data
  - Applications with user-generated content
  - E-commerce and financial applications
  - Applications requiring security compliance
  - Public-facing web applications

  ## Be Careful When

  - Legacy applications with extensive inline scripts that cannot be refactored
  - Applications in early development with rapidly changing content sources
  - Internal tools with very controlled user access (though still recommended)

  ## Canonical Source

  - Full best practice: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/security-and-authentication/implement-content-security-policy/BEST_PRACTICE.md
  - Dutch translation: https://github.com/Dutch-Laravel-Foundation/best-practices/blob/main/security-and-authentication/implement-content-security-policy/translations/nl.md

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
skill_source_path: security-and-authentication/implement-content-security-policy/skill/SKILL.md
skill_github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c7034be11f69954eac43e50486b6e5bebde98c46/security-and-authentication/implement-content-security-policy/skill/SKILL.md'
skill_references: []
---
