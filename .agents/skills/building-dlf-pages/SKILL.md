---
name: building-dlf-pages
description: Use when building, adapting, or reviewing public frontend pages in the Dutch Laravel Foundation Statamic/Antlers new-design worktree, especially when choosing a page family, composing Bard blocks, applying the DLF brand system, or validating responsive UI.
---

# Building DLF Pages

Extend the established redesign instead of inventing a parallel page system. Match the closest content family, keep repeatable content authorable, and verify the result in a real browser.

## Start with evidence

1. Work from the Git repository root containing this skill. Confirm the current branch/worktree and preserve all unrelated and uncommitted work.
2. Read the final corrections in [foundations.md](references/foundations.md), then scan the map and selected family in [page-families.md](references/page-families.md). Load other sections only when relevant.
3. Inspect the target entry, its closest canonical rendered page, that page's blueprint, Antlers template/partials, scoped CSS, and interaction JavaScript before editing.
4. Use Orbit for the project lifecycle and `https://new-design.dutchlaravelfoundation.test` for browser review.

Resolve conflicts in this order:

1. The current task's explicit direction.
2. The normative final corrections in `foundations.md`.
3. The nearest established page-family component and its current rendered behavior.
4. General foundations in this skill.
5. The archived design handoff.

If current shared code conflicts with a final correction, do not silently normalize the whole site. Apply the correction within scope and report the broader mismatch.

## Choose the page family first

Classify the page by content and behavior, not by whichever screenshot looks attractive:

- collection index or detail;
- long-form editorial/public information;
- community directory, membership, or form;
- conversion-heavy acquisition landing;
- multi-step wizard;
- homepage-only composition.

Reuse the exact shell, rail, button family, blocks, and responsive behavior of the closest family in [page-families.md](references/page-families.md). Do not mix families merely to borrow isolated styling.

Keep author-controlled content in the host family's existing Bard stream. The registered `dlf_*` sets are full-rail blocks with their own shared namespace; use them only where the template emits blocks at rail level. Do not place them inside `templates/default`'s narrow prose stream without an intentional, tested breakout adapter. Add a new set only when existing sets cannot express the semantics. Hardcode only structural composition that the chosen family already hardcodes.

## Compose the page

- Keep the global layout, header/navigation, and footer. Configure `call_to_action`; the footer already renders the reusable CTA banner, so never render a second copy in a page template.
- Continue the 1152px bordered rail and its flush grids. Assign each divider to one edge so adjacent cells do not create double borders or gaps.
- Let one page family own the shell. A shared block may own its internal `dlf-*` classes and button, but do not import a second page-family namespace. Do not introduce another root token set, container primitive, button system, or near-duplicate component.
- Use balanced section padding and the established spacing scale. Avoid generic oversized whitespace or card padding.
- Keep body copy consistent with the homepage; headings are semibold. Remove content-authored `<br>` elements and embedded bold styling when layout or hierarchy should provide the emphasis.
- Preserve supplied SVG/icon geometry and the asset family already used by the page family. Recolor existing artwork; never replace it with an approximation.
- Treat desktop, tablet, and mobile as designed states. Collapse deliberately, preserve useful content, and keep mobile-only rules from leaking upward.

## Avoid redesign drift

Do not add generic intro stripes, floating divider fragments, isolated decorative left borders, circular icon treatments, heavy rounding, arbitrary shadows, or boxed quote marks. Do not hide content on mobile merely to make the layout easier. The documented exceptions are intentional and must not become global patterns.

## Validate before handoff

Use the `agent-browser` skill after frontend changes. Check at approximately 1440px, 768px, and 390px, plus the breakpoints affected by the change.

Verify:

- header, navigation, footer, and full-page border continuity;
- content order, grid collapse, padding, image crop, and horizontal overflow;
- hover, focus, keyboard, form, menu, filter, FAQ, and other changed interactions;
- header-aware sticky behavior and TOC active state while scrolling;
- reduced motion, semantic landmarks, heading order, alt text, and contrast;
- the relevant build/format checks and only behavior tests related to changed logic.

For purely visual changes, use browser inspection instead of adding screenshot/UI regression tests. Add automated tests only for meaningful logic or behavior.

## Example

For a new long-form information page, start from `templates/default.antlers.html`, keep its editorial rail/prose/TOC structure, use its ordinary narrow-column Bard content, and set `call_to_action` for the footer-owned close. Do not insert full-rail DLF blocks or copy the acquisition landing's `.dlf-bm` composition unless the template deliberately supports that layout.
