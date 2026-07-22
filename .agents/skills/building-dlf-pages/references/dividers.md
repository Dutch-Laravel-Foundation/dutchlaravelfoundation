# DLF divider ownership

Use one owner per visible edge. A shared boundary must resolve to exactly `1px`: never `0px`, never `2px`.

## Ownership contract

- The page rail owns the outer left and right rules.
- Every top-level content section owns its bottom rule with `dlf-divider-section`. A section never owns a top rule.
- A full-width grid, list, split, or region that ends flush with its section yields its terminal bottom edge to `dlf-divider-section`; the nested owner may not add a second adjacent bottom rule.
- When a lower top-level section intentionally bleeds wider than the section above it, the upper section adds `dlf-divider-section--yield-to-next` and the immediately adjacent lower section adds `dlf-divider-section--full-bleed-top`. The lower full-bleed section then owns the single shared boundary across its complete width.
- The final page-content stack always ends with one continuous bottom rule before any footer CTA staging gap. An ordinary final section keeps its own `dlf-divider-section` bottom rule.
- In an editorial rail, the final visible direct child owns that closing rule. This includes author/project-attribution blocks, pagination, and empty states; mark those terminal blocks with `dlf-divider-section` instead of relying on the earlier article/feed rule.
- If the final desktop content edge is owned by one full-width child or composed from adjacent panes, add `dlf-divider-section--composite-tail` to the section and mark every child that jointly covers that edge with `dlf-divider-tail-segment`. One or more segments own one continuous bottom rule while the section wrapper remains at `0px`; below desktop they yield to the stacked section/split ownership automatically.
- The footer band's top rule is a separate boundary at the far side of the CTA staging tail. It never replaces the final page-content rule because the two edges do not share a coordinate.
- Every compatible page-family rail carries exactly one `data-dlf-footer-cta-stage` owner, whether or not its current CMS configuration renders a banner. The owner is inert without a CTA. With a CTA, desktop reserves the shared measured stage variable; stacked layouts reserve no rail padding and let the in-flow card's `24px` margin own the gap. Never add route-specific footer-stage padding.
- A lower region inside a section owns the boundary above it with `dlf-divider-region`. When it immediately follows another child, that previous child yields its bottom edge automatically.
- A flush grid uses `dlf-divider-grid`. Its cells own internal top and left rules only; cells never own right or bottom rules unless the explicit sticky-pane ownership state below applies.
- When a shorter trailing desktop pane uses `position: sticky` or otherwise cannot cover the complete grid-row height, add `dlf-divider-grid--desktop-leading-owns-rule` to a `dlf-divider-grid--desktop-2` grid. The full-height leading pane then owns the shared rule on its right edge, while the trailing pane yields its left edge. Tablet and mobile stacking keep their ordinary top-edge ownership.
- A repeated-card grid whose rows are intentionally open uses `dlf-divider-grid--column-rules-only`. While it has multiple columns, cells own internal left rules only and must not draw row rules to the rail edge. When it collapses to one mobile column, ordinary top rules separate the resulting vertical list.
- A flush grid with deliberately patterned empty space in its final row also uses `dlf-divider-grid--fill` and one final `dlf-divider-grid__filler` cell. The filler spans the unused columns and owns their missing internal top/left rules; it disappears automatically when the row is complete.
- An inset grid adds `dlf-divider-grid--framed`. The wrapper owns all four outer edges while the cells still own internal top and left rules.
- A vertical sequence uses `dlf-divider-list`: the first item has no top rule and every later item owns its top rule.
- A split uses `dlf-divider-split`: the later/right pane owns the left rule on desktop and the later/lower pane owns the top rule when stacked.
- Red and dark surfaces add `dlf-divider-theme-inverse` so the same ownership model uses the established translucent light rule.

## Responsive grid classes

Declare the visual column count for every state; do not infer it with final-row selectors.

```html
<section class="dlf-divider-section">
    <header>...</header>
    <div
        class="dlf-divider-region dlf-divider-grid
            dlf-divider-grid--desktop-4
            dlf-divider-grid--tablet-2
            dlf-divider-grid--mobile-1"
    >
        ...
    </div>
</section>
```

For a grid whose unused final-row columns remain visibly patterned, add a real filler cell after all collection items (and after an Alpine `x-for` template):

```html
<div
    class="dlf-fill-grid dlf-divider-grid dlf-divider-grid--fill
        dlf-divider-grid--desktop-4
        dlf-divider-grid--tablet-2
        dlf-divider-grid--mobile-2"
>
    ...
    <span class="dlf-divider-grid__filler" aria-hidden="true"></span>
</div>
```

Do not rely on the wrapper background alone for an incomplete row: empty background has no box and therefore cannot own the internal edges. Keep exactly one filler as the final non-template child; the shared primitive calculates its responsive span.

Available counts are `desktop-1` through `desktop-6`, `tablet-1` through `tablet-3`, and `mobile-1` or `mobile-2`. The shared breakpoints are desktop `>=1024px`, tablet `640–1023px`, and mobile `<640px`.

For a two-pane desktop grid with a shorter sticky trailing pane:

```html
<div
    class="dlf-divider-grid dlf-divider-grid--desktop-2
        dlf-divider-grid--desktop-leading-owns-rule
        dlf-divider-grid--tablet-1 dlf-divider-grid--mobile-1"
>
    <article>...</article>
    <aside>...</aside>
</div>
```

For a normal split:

```html
<section class="dlf-divider-section dlf-divider-split">...</section>
```

When CSS reverses the visual desktop order, also add `dlf-divider-split--desktop-reversed`.
When CSS reverses the visual order only after stacking, add `dlf-divider-split--stacked-reversed`.

For a final desktop edge owned below the section wrapper, mark its full-width child or every adjacent segment rather than drawing another wrapper border:

```html
<section class="dlf-divider-section dlf-divider-section--composite-tail dlf-divider-split">
    <div class="dlf-divider-tail-segment">...</div>
    <aside class="dlf-divider-tail-segment">...</aside>
</section>
```

The segment or segments must share the section's bottom coordinate, touch without gaps, and cover at least the section's full width. A segment may extend beyond the rail for an intentional bleed.

For an adjacent full-bleed lower section, transfer the boundary instead of adding another top rule:

```html
<section class="dlf-divider-section dlf-divider-section--yield-to-next">...</section>
<section class="dlf-divider-section dlf-divider-section--full-bleed-top">...</section>
```

The pair must be immediate siblings at the same vertical boundary. The lower section must cover the upper section's full horizontal span.

## Rules that are forbidden

- Do not put both `border-bottom` on an upper block and `border-top` on the lower block.
- Do not combine a section bottom rule with a full-width nested structural owner's bottom rule at the section tail.
- Do not use `dlf-divider-section--yield-to-next` without an immediately following `dlf-divider-section--full-bleed-top`, or vice versa.
- Do not use `last-child`, `nth-last-child`, or final-row selectors to remove borders from responsive grids.
- Do not give ordinary grid cells right or bottom structural borders. The only right-edge exception is the shared `dlf-divider-grid--desktop-leading-owns-rule` state for a full-height leading pane.
- Do not use ordinary row rules in a `dlf-divider-grid--column-rules-only` layout while it has multiple columns; the column separator is its only internal divider.
- Do not fake incomplete-grid cells with background alone or page-local pseudo-elements; use the fill primitive.
- Do not repair one viewport with a rule that changes ownership at another viewport.
- Do not add a page-local border exception when a divider primitive expresses the relationship.
- Do not draw structural divider edges with `box-shadow`, `::before`, or `::after` on a divider primitive or its cells. Structural edges use the primitive's border ownership only; decorative shadows and pseudo-elements must not coincide with a shared edge.
- Do not remove the final page-content divider because the footer band has its own top rule; those rules close opposite sides of the CTA staging tail.
- Do not use `dlf-divider-tail-segment` without `dlf-divider-section--composite-tail` on its owning section, or declare the composite state without covering tail segments.
- Do not combine `dlf-divider-tail-segment` with a bottom border on its section wrapper; that totals `2px` at the same edge.

Borders that belong to controls, focus states, images, decorative panels, and individually framed cards are not structural dividers and remain component-owned.

## Validation

After a divider/layout change, run:

```bash
node .agents/skills/building-dlf-pages/scripts/audit-dividers.mjs --route /wat-is-laravel
```

Run the full canonical-page audit for shared changes:

```bash
node .agents/skills/building-dlf-pages/scripts/audit-dividers.mjs
```

For a release or site-wide structural review, pass `--sitemap <url>` or a newline-delimited export with `--routes-file <path>`; add `--quiet` when only failures and the final count are needed. Keep the default canonical family matrix for routine iteration, then use the full route list to catch content-dependent wrapping and CTA variants.

The audit checks widths `390`, `639`, `640`, `768`, `1023`, `1024`, `1440`, and `2048`, including breakpoint boundaries and wide desktop. It verifies section ownership, nested terminal-owner handoffs, full-bleed boundary transfers, adjacent top-level rail boundary totals (including legacy component borders), every editorial rail's final direct-child edge, a continuous terminal page-content edge before CTA staging, the separate footer-band edge, composite tail-segment coverage, exactly one stage owner on every banner page, rejects duplicate or page-local CTAs outside the shared footer, measures floating and stacked CTA staging gaps, enforces the ordinary public-information desktop terminal padding, ordinary and column-rule-only grid behavior, complete vertical-rule coverage for unequal-height panes, internal split/list edges, framed-grid edges, patterned filler-cell coverage, structural inset-shadow and pseudo-line duplication, and horizontal overflow. Still inspect screenshots because geometry checks cannot judge color, alignment, or whether a semantic grouping chose the correct primitive.
