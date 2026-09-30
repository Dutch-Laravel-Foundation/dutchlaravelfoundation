# DLF page families

Choose by information architecture and behavior, then reuse that family's template, namespace, components, and responsive contract as one unit.

## Ownership map

| Concern                 | Owner                                                                                                                   |
| ----------------------- | ----------------------------------------------------------------------------------------------------------------------- |
| Page entries            | `content/collections/pages/*.md`                                                                                        |
| Page blueprint          | `resources/blueprints/collections/pages/pages.yaml`                                                                     |
| Bard schemas/sets       | `resources/fieldsets/content.yaml` and referenced fieldsets                                                             |
| Public routing          | `routes/web.php`; the route order and explicit public-page allowlist are part of the contract                            |
| CMS access              | `app/Content/*/*Repository.php` through the in-process Statamic GraphQL client                                           |
| Page boundary           | `app/Http/Controllers/*PageController.php` maps GraphQL records into typed `app/Data/**` DTOs                            |
| Document shell          | `resources/views/app.blade.php` and `resources/js/components/site/PersistentSiteLayout.tsx`                              |
| Header/navigation       | `resources/js/components/site/Header.tsx`, `DesktopNavigation.tsx`, and `MobileNavigation.tsx`                           |
| Footer and CTA decision | `resources/js/components/site/Footer.tsx` plus the page-family CTA adapter; configure `call_to_action`, never duplicate it |
| Bard renderer           | The selected React family's `ContentBlocks.tsx`                                                                         |
| CSS order               | `resources/css/tailwind.css`: shell → blocks → home → landing → editorial → community → public                          |
| Sticky header/TOC       | `resources/js/hooks/useHeaderBehavior.ts` and the editorial React article components                                    |

Keep layout → shell → family template → compatible content blocks → footer. Configure owners; do not copy their markup or behavior into a page.

## Family map

| Family              | Canonical routes                                                                                                     | React/CSS owner                                                                                                      | Use when                                                                                                |
| ------------------- | -------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------- |
| Home                | `/`                                                                                                                  | `pages/Home.tsx`, `components/home/`, `redesign-home.css`                                                            | Homepage only; its intro stripes and dense bento are not defaults.                                      |
| Editorial index     | `/nieuws`, `/kennis`, `/podcast`, `/agenda`                                                                          | `pages/Editorial/*Index.tsx`, `components/editorial-react/ArticleIndex.tsx`, `redesign-editorial.css`                 | Chronological/filterable collection overview. Reuse the closest metadata/card grid.                     |
| Editorial detail    | detail routes for those collections plus events                                                                      | `pages/Editorial/*Show.tsx`, `components/editorial-react/`, `redesign-editorial.css`                                  | Authored articles, facts, author, active TOC, related content.                                          |
| Public information  | default pages, `/privacy-statement`, `/newsletter`                                                                    | `pages/PublicPages/`, `components/public-pages-react/`, `redesign-public.css`                                         | Ordinary durable information, policy, or simple campaign. Default new-page family.                      |
| Community/directory | `/over-ons`, `/wat-is-laravel`, `/leden`, `/larabelles`, `/stagebank`, `/cases`                                      | `pages/Community/`, `components/community-react/`, `redesign-community.css`                                           | Narrative, directories/filters, membership, cases, and internships.                                    |
| Forms               | `/contact`, `/lid-worden`                                                                                             | `pages/Forms/`, `components/forms-react/`, host-family CSS                                                            | CMS-backed forms with Inertia hydration and server-side submission.                                     |
| Acquisition         | preferred `/een-eigen-systeem-laten-bouwen-is-betaalbaarder-dan-je-denkt`; also Laravel-system and `/aanbestedingen` | `pages/PublicPages/*Landing.tsx`, `components/public-pages-react/LandingParts.tsx`, `redesign-landing.css`            | Conversion funnel with problem/benefit/proof/CTA bands. Keep `.dlf-bm` out of editorial pages.          |
| Wizard              | `/aanvraag`                                                                                                          | `pages/Forms/SalesFunnel.tsx`, `components/forms-react/SalesFunnelWizard.tsx`                                         | Existing multi-step match flow only unless explicitly requested.                                       |

## Content compatibility

| Host                                             | Compatible authoring path                                                                                                                                                              |
| ------------------------------------------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Default public page / editorial detail            | Ordinary narrow-column Bard data through the family's `ContentBlocks`; do not inject full-rail `dlf_*` sets. The existing TOC progressively owns its H2 visibility—do not invent a page-local threshold. |
| Home/acquisition/community dedicated React pages | Keep their current composition and components. A shared DLF block is allowed only when its rail width and responsive behavior are intentionally integrated and browser-proven.                      |
| New modular rail-level page explicitly requested | Registered `dlf_*` data may form the top-level stream only after the repository, DTO, and React renderer all support it; do not clone an existing renderer.                                           |

If editors must repeat/reorder content that no compatible set represents, first determine whether an existing set can be adapted without changing other pages. Add a set only for a genuinely new semantic block.

## Family rules

### Editorial/public information

- Start ordinary pages from `PublicPages/Default.tsx`: white unstriped intro, 1152px outer rail, narrow prose, reduced desktop TOC, and footer-owned CTA.
- Keep the ordinary public-information body's desktop terminal padding at `80px` or more so the last authored content does not crowd the section's closing divider. Set it on the shared body owner; do not depend on the last Bard node's margin.
- Keep author/facts/media/related content in the order of the closest detail type. Remove authored line breaks and visual bold.
- Keep long-form prose at a `580px` maximum on desktop/tablet, including when the TOC is hidden; mobile remains fluid within the page inset.
- On index pages, use the shared horizontally scrollable mobile filter row with its right-edge fade and the shared one-line `newer / count / older` pagination. Keep list-ending space balanced before the footer CTA.
- Populated `/nieuws`, `/kennis`, `/podcast`, and `/cases` indexes use the documented open-feed divider state: retain the outer rail and closing bottom rule, remove row and image/text dividers, and let only the first text pane own the opening top rule. Do not apply `dlf-divider-list`. Empty states keep the ordinary full-width header divider. `/agenda` retains its established section/list ownership unless that family is intentionally redesigned.
- Reuse the detail page's author portrait and channel-button treatments on related index/summary surfaces rather than rebuilding lighter substitutes.
- A logo-led split hero may follow `/larabelles` only when co-branding is central. Add a TOC only by adopting the established long-form template/behavior, never a second script.
- Reuse contact/newsletter form semantics and improve ARIA links when needed.

### Community/directory

- Preserve the contiguous bordered grid and current filter/form behavior.
- Close the final directory or membership region with one continuous bottom divider before the footer CTA staging gap. Patterned filler cells complete an incomplete row's internal top/left geometry; the final section or explicit tail segments still own the external bottom edge.
- When a static overview reuses a horizontal scroller's item styles, reset every scroller-only bleed, start padding, width, and overflow value before assigning divider ownership. An ordinary region's top rule starts at its own left border, never at a stale full-bleed viewport edge.
- Mobile becomes one ordered flow without losing labels, organization data, or actions.
- Align desktop card contents to the section-head inset. Apply card padding and border corrections to every item in the owning grid, including first/last and odd/even variants.
- Keep member locations structured as city plus a non-breaking province span. If the location wraps, match its line height to the compact card typography rather than the prose default.
- Use the community button family and align benefits/forms/CTA to its rail.

### Acquisition

- Follow the preferred affordable-custom-system page's sequence: opening → pain/problem → explanation → proof → benefits → conversion close.
- Alternate media/text only when reading order benefits; keep semantic mobile order.
- Use red/black striped bands sparingly. Keep `.dlf-bm` and landing buttons inside this family.
- `/aanbestedingen` intentionally omits side rails for its specified grid while retaining a full-width divider; do not generalize this.

### Homepage

- Treat the intro, bento/grid, partner strip, member/customer modules, and closing CTA as established composition.
- Keep `data-dlf-footer-cta-stage` on `.dlf-home-main`; the shared measurement owns its desktop tail padding, while the stacked CTA uses the same `24px` vertical and horizontal inset.
- Reuse elsewhere only modules already extracted as shared partials/blocks; never copy home-only CSS.

## New-page sequence

1. Inspect the nearest route's entry, blueprint values, GraphQL query, DTO mapper, rendered React page/components, CSS, and hook owner.
2. Create/configure the Statamic entry with the existing page blueprint and map its template value to the closest React page in `PublicPageController` when needed.
3. Use the host's compatible Bard-to-DTO-to-React path; add no fieldset, DTO field, CSS import, or hook without a demonstrated structural need.
4. Keep one page-family namespace. Shared blocks may retain their own internal namespace; do not mix page-family CSS.
5. Configure `call_to_action`; never render a second footer banner.
6. Browser-validate desktop, tablet, mobile, interaction, content completeness, rails/dividers, and sticky/header behavior.
