# DLF page families

Choose by information architecture and behavior, then reuse that family's template, namespace, components, and responsive contract as one unit.

## Ownership map

| Concern                 | Owner                                                                                                                        |
| ----------------------- | ---------------------------------------------------------------------------------------------------------------------------- |
| Page entries            | `content/collections/pages/*.md`                                                                                             |
| Page blueprint          | `resources/blueprints/collections/pages/pages.yaml`                                                                          |
| Bard schemas/sets       | `resources/fieldsets/content.yaml` and referenced fieldsets                                                                  |
| Document shell          | `resources/views/layouts/layout.antlers.html`                                                                                |
| Header/navigation       | `resources/views/partials/_header.antlers.html`, `_navigation.antlers.html`, `_navigation_mobile.antlers.html`               |
| Footer and CTA decision | `resources/views/partials/_footer.antlers.html`; configure `call_to_action` rather than rendering `_footer_cta_banner` again |
| Bard dispatcher         | `resources/views/partials/sets/_autoload.antlers.html`                                                                       |
| Shared DLF blocks       | `resources/views/partials/sets/_dlf-*.antlers.html`, `resources/css/redesign-blocks.css`                                     |
| CSS order               | `resources/css/tailwind.css`: shell → blocks → home → landing → editorial → community → public                               |
| Sticky header/TOC       | `resources/js/components/header-aware-sticky.js`, `editorial-article.js`, imported by `resources/js/site.js`                 |

Keep layout → shell → family template → compatible content blocks → footer. Configure owners; do not copy their markup or behavior into a page.

## Family map

| Family              | Canonical routes                                                                                                     | Template/CSS owner                                                                         | Use when                                                                                                |
| ------------------- | -------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------- |
| Home                | `/`                                                                                                                  | `templates/home/index`, `redesign-home.css`                                                | Homepage only; its intro stripes and dense bento are not defaults.                                      |
| Editorial index     | `/nieuws`, `/kennis`, `/podcast`, `/cases`, `/agenda`                                                                | matching index templates, `redesign-editorial.css`                                         | Chronological/filterable collection overview. Reuse the closest metadata/card grid.                     |
| Editorial detail    | detail routes for those collections plus events                                                                      | matching show templates, `partials/editorial/`, `redesign-editorial.css`                   | Authored articles, facts, author, active TOC, related content.                                          |
| Public information  | default pages, `/privacy-statement`, `/larabelles`, `/newsletter`, `/contact`                                        | `templates/default` or closest dedicated template; `redesign-public.css` + editorial prose | Ordinary durable information, policy, simple campaign, newsletter, or contact. Default new-page family. |
| Community/directory | `/over-ons`, `/wat-is-laravel`, `/leden`, `/lid-worden`, `/stagebank`                                                | matching templates, `redesign-community.css`                                               | Narrative, directories/filters, membership, benefits, and forms.                                        |
| Acquisition         | preferred `/een-eigen-systeem-laten-bouwen-is-betaalbaarder-dan-je-denkt`; also Laravel-system and `/aanbestedingen` | `templates/landings-page/*`, `redesign-landing.css`                                        | Conversion funnel with problem/benefit/proof/CTA bands. Keep `.dlf-bm` out of editorial pages.          |
| Wizard              | `/aanvraag`                                                                                                          | `templates/aanvraag/index`, `partials/wizard/_sales-funnel`                                | Existing multi-step match flow only unless explicitly requested.                                        |

Paths are relative to `resources/views/` and omit `.antlers.html` where several siblings exist.

## Content compatibility

| Host                                             | Compatible authoring path                                                                                                                                                              |
| ------------------------------------------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `templates/default` / editorial detail           | Ordinary narrow-column Bard sets through `_content`; do not inject full-rail `dlf_*` sets. The existing TOC progressively owns its H2 visibility—do not invent a page-local threshold. |
| Home/acquisition/community dedicated templates   | Keep their current composition and existing partials. A shared DLF block is allowed only when its rail width and responsive behavior are intentionally integrated and browser-proven.  |
| New modular rail-level page explicitly requested | The nine registered `dlf_*` blocks may form the top-level stream. Reuse `_autoload`, block CSS, and schemas; do not clone them.                                                        |

If editors must repeat/reorder content that no compatible set represents, first determine whether an existing set can be adapted without changing other pages. Add a set only for a genuinely new semantic block.

## Family rules

### Editorial/public information

- Start ordinary pages from `templates/default`: white unstriped intro, 1152px outer rail, narrow prose, reduced desktop TOC, and footer-owned CTA.
- Keep the ordinary public-information body's desktop terminal padding at `80px` or more so the last authored content does not crowd the section's closing divider. Set it on the shared body owner; do not depend on the last Bard node's margin.
- Keep author/facts/media/related content in the order of the closest detail type. Remove authored line breaks and visual bold.
- Keep long-form prose at the established `38rem` measure when the TOC is hidden on tablet; only shrink when the viewport and page inset require it.
- On index pages, use the shared horizontally scrollable mobile filter row with its right-edge fade and the shared one-line `newer / count / older` pagination. Keep list-ending space balanced before the footer CTA.
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

1. Inspect the nearest route's entry, blueprint values, rendered page, template/partials, CSS, and JavaScript owner.
2. Create/configure the Statamic entry with the existing page blueprint and closest template.
3. Use the host's compatible Bard path; add no template, fieldset, CSS import, or script without a demonstrated structural need.
4. Keep one page-family namespace. Shared blocks may retain their own internal namespace; do not mix page-family CSS.
5. Configure `call_to_action`; never render a second footer banner.
6. Browser-validate desktop, tablet, mobile, interaction, content completeness, rails/dividers, and sticky/header behavior.
