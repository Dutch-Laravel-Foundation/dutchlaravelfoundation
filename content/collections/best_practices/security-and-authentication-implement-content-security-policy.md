---
id: a8dccf3f-32d9-5c6a-a2f6-506a71e45332
blueprint: best_practices
title: 'Implement Content Security Policy (CSP)'
summary: 'Content Security Policy (CSP) is a security header that helps prevent Cross-Site Scripting (XSS) attacks and other code injection vulnerabilities by defining which sources of content are allowed to be loaded and executed on your web applica...'
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
  - security-and-authentication
category_slug: security-and-authentication
category_title: 'Security & Authentication'
source_path: security-and-authentication/implement-content-security-policy.md
source_sha: c0967a7f4ce3d79bcd2f427ac0c16eb1b20adff8
github_url: 'https://github.com/Dutch-Laravel-Foundation/best-practices/blob/c0967a7f4ce3d79bcd2f427ac0c16eb1b20adff8/security-and-authentication/implement-content-security-policy.md'
boost_skill_path: null
related_files: []
---
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
- [Prevent Common Vulnerabilities](./prevent-common-vulnerabilities.md) — for XSS output escaping, CSRF, SQL injection, and other security practices
