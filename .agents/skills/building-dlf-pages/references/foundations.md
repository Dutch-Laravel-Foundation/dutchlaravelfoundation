# DLF frontend foundations

This is the stable design contract for the new-design worktree. It distills the Codex feedback history and the archive named `Mobile optimization review - DLF.zip`; do not reapply that archive's generated README over these later decisions.

## Final corrections

These normative decisions override conflicting handoff text and page-local implementations. Keep a broader normalization outside the task's scope visible rather than silently rewriting the site.

- Body copy follows the homepage's Mulish treatment. Headings are semibold, not bold.
- Remove content-authored `<br>` elements and decorative embedded bold; CSS owns wrapping, spacing, and hierarchy.
- Generic intro sections are white and unstriped. The homepage is the intro exception.
- Reuse the reduced-width editorial TOC: desktop-sticky, active-section aware, and synchronized with the header's offset and animation.
- New/refined buttons, inputs, and media use 4px corners; checkbox/progress state uses 2px. Interactive/focus borders are 1.5px; the mobile hamburger treatment is 1px.
- Outline buttons start with the established solid-white face and use the bright-red/white hover state. Navigation and button labels are never underlined.
- Inline text links on white content surfaces are DLF red and underlined (1px thickness, 3px offset). Apply that contract through the host family's semantic wrapper (`.dlf-community-copy`, `.dlf-detail-properties`, `.editorial-article__prose`, or a white `.dlf-block`'s `.dlf-prose`), not a page- or URL-specific selector. Component links, linked headings/cards, buttons, navigation, and inverse/dark surfaces retain their component treatment.
- Preserve existing SVG/icon paths, proportions, stroke geometry, and view box when recoloring. Never substitute approximate artwork, emoji, or Unicode glyphs.
- The quote block is solid bright red with diagonal treatment, white text, and one large translucent unboxed quote mark at the top right.
- The contact emphasis panel is black with a lighter stripe treatment and generous spacing.
- “Match je project” may hide on mobile only; retain it on tablet and desktop.
- Reuse the shared near-footer CTA through the footer. Extend its central CMS configuration when custom copy is required; never hardcode or render a duplicate page-local banner.
- A floating footer CTA uses a measured staging tail: the page content first closes with one continuous bottom divider, then the clear vertical gap to the card’s top edge equals the card’s measured horizontal inset (`40px` for the ordinary desktop banner, `49px` for the dark desktop banner with its intentional inner offset, and `24px` stacked). The footer band's later top rule is a separate boundary and cannot replace the content divider. Mark every compatible family rail exactly once with `data-dlf-footer-cta-stage`, even when its current page has no banner; the marker is inert until a CTA is present. Do not hardcode page-specific stage padding or derive it from one card height because CTA wrapping and variants change across viewports and content.
- Ordinary public-information page bodies keep at least `80px` of terminal content padding on desktop before their closing divider. This belongs to the shared `.dlf-public-page__body`, not the last authored element or an individual route. Tablet and mobile retain their compact family spacing unless the task explicitly changes those viewports.
- Validate CSS-only changes in a browser. Do not add screenshot/UI regression tests for purely visual work.

## Brand, voice, and assets

- Write Dutch unless the source requires otherwise. Speak as `we/ons` and address the visitor as `je/jij`.
- Be professional, direct, community-minded, and knowledge-forward. Avoid hype, jargon, and emoji.
- Use sentence case for headings and CTA copy; CTAs are short and verb-led.
- Use natural community/event photography, full-color partner logos, existing red line illustrations, and assets from `public/assets/redesign/`.
- Preserve intrinsic logo proportions. Do not invent undocumented clearspace or minimum-size rules, and do not mix icon weights from legacy folders.

The handoff's “Mulish everywhere, bold headings, square corners, broad offset shadows, isometric floor texture” has evolved into Mulish prose/headings plus JetBrains Mono compact UI; semibold headings; subtle near-square corners; offset movement/shadow only where established (especially buttons); and the current restricted diagonal stripe language.

## Visual system

| Role             | Value or rule                                                                           |
| ---------------- | --------------------------------------------------------------------------------------- |
| Brand red        | `#ff2d20`                                                                               |
| Ink              | `#090910`                                                                               |
| Body copy        | `#525257`; inherit darker editorial prose where scoped                                  |
| Muted            | Existing scopes use `#737378` or `#848487`; inherit the chosen family                   |
| Line             | `#ececec`                                                                               |
| Soft             | Existing scopes use `#f7f7fb` or `#f5f5fa`; inherit the chosen family                   |
| Focus red        | `#b91c1c` where the shell uses it                                                       |
| Desktop rail     | Centered, maximum 1152px, normally with 1px side rules                                  |
| Horizontal inset | Usually 40px desktop, 24px mobile; copy the family at tablet                            |
| Spacing          | 4, 8, 12, 16, 20, 24, 32, 40, 48, 64, 80px; 28px only where an established card uses it |

Do not declare a third global token set to resolve the muted/soft collision. Use the selected family's scoped/inherited values. Do not use brand red or muted gray for small regular text on white when contrast fails; use ink or a sufficiently large/strong treatment.

### Section padding vocabulary

Use the owning page family's named section-padding unit when feedback describes vertical section spacing as a multiple. In the community family, `1x`, `1.5x`, `2x`, and `2.5x` resolve to `32`, `48`, `64`, and `80px` on desktop, then `24`, `36`, `48`, and `60px` below the 1024px collapse. Keep horizontal insets separate from this vertical rhythm.

Top and bottom may use different multiples when the composition calls for it. Record the pair explicitly, such as `1x / 2x`; do not infer symmetry or replace a documented exception. Reuse the corresponding `--dlf-section-padding-*` custom property in the owning family instead of entering the resolved pixel value. Existing card-specific `28px` spacing remains an intentional exception, not another section unit.

Use Mulish for prose and headings. Reserve uppercase, tracked JetBrains Mono for established eyebrows, compact metadata, and button labels. Copy the chosen family's type scale and line height instead of adding page-local sizes.

Keep ordinary surfaces white with thin gray dividers. Panels/media remain rectangular with subtle 3–4px rounding. Avoid pills/circles unless an existing semantic control requires them, generic card shadows, isolated decorative left borders, floating divider fragments, boxed quote glyphs, oversized card padding, and one-off buttons.

Diagonal stripes belong to the homepage, global footer/CTA, deliberate red/black emphasis bands, and established inactive/past/filler cells—not ordinary intros or cards. Use bright DLF red consistently.

## Layout and responsive behavior

- Build a contiguous rail/grid with the single-owner primitives in `dividers.md`: sections own bottom, lower regions own top, and grid cells own internal top/left only. Every shared edge must total exactly `1px` at every responsive state.
- Treat alignment and spacing as measurable relationships. Content that shares a rail should share its horizontal edge; repeated cards should share padding; adjacent block gaps should match their outer inset when requested.
- Prefer equal top/bottom padding unless the composition has a documented reason to differ. Include the final item and the space before the next section; do not stop checking at the last line of content.
- Common compact/ordinary gaps are 12/24px; larger regions use 32–48px. Reuse the exact owning component value rather than introducing a visually approximate number.
- Main collapse occurs around 1024px; mobile stacking around 640px. Copy a family's narrower 480px adjustment only when relevant.
- At a floating footer CTA, compare the final content divider, card top, card left edge, and later footer-band top as one geometry check. The content divider must exist across the complete page-content width; its vertical clear gap to the card and the card's horizontal inset must match within `1px`; the footer-band top remains a separate `1px` edge. Below `1024px`, the family rail owns no staging padding and the in-flow card owns the shared `24px` top/side inset. Include both `1023px` and `1024px` because ownership changes at that boundary.
- A smaller viewport does not automatically mean one column. Preserve useful density with two- or three-column grids when the item composition and minimum content width support it; change the internal composition at the breakpoint instead of stretching sparse cards.
- At smaller sizes, outer rail borders commonly disappear and grids become one ordered column with horizontal dividers. Preserve useful labels, data, and actions; do not hide content for layout convenience.
- Scope review feedback to its stated viewport. Put mobile-only corrections under the appropriate `max-width` rule and verify that tablet and desktop computed styles are unchanged.
- When a side rail or TOC disappears, keep editorial prose at the established readable maximum width and center it until the viewport becomes narrower than that measure.
- Use horizontal scrolling for compact filter/tab rows that cannot wrap cleanly. Hide the scrollbar visually without disabling scrolling and add an edge fade that indicates more content.
- Keep semantic tokens together: model city and province as separate spans and prevent a hyphenated province from breaking internally. Set a deliberate compact line height when the full location still wraps.
- Truncate values only in interfaces that require one-line rows; preserve the full value with an accessible tooltip/title. Do not truncate editorial copy.
- Sticky elements consume current header-aware CSS variables. Never hardcode a competing top offset.
- Motion normally finishes in roughly 150–250ms; keep slower image hover motion only where established and honor `prefers-reduced-motion` in CSS and JavaScript.

## Components and CMS

- Reuse the button belonging to the host family. Existing `.dlf-btn`, `.dlf-button`, and `.dlf-community-button` systems are intentional legacy boundaries; do not add a fourth or normalize all three as collateral work.
- Shared DLF Bard sets are registered but are full-rail, not universal prose blocks: `dlf_hero`, `dlf_stats`, `dlf_feature_grid`, `dlf_media_text`, `dlf_quote`, `dlf_cta_panel`, `dlf_logo_cloud`, `dlf_card_grid`, and `dlf_pricing`.
- Use those blocks only in a rail-level block stream. In the default 38rem prose stream, use its ordinary Bard sets unless a deliberate breakout adapter is part of the task and browser-verified.
- If a shared DLF block is valid in the host, let it keep its internal component/button namespace; do not import another page family's CSS to restyle it.
- Photos follow their block's crop with `object-fit: cover`; logos use `contain`. Provide intrinsic dimensions, responsive sources where supported, meaningful alt text, and lazy loading below the fold.
- In split media/text rows, make media match the content-side height at that breakpoint. On mobile, use the family's full-width or 16:9 treatment and remove the owning container inset instead of compensating with arbitrary offsets.
- If an author or person image already exists, show it consistently in both the compact attribution and full profile treatment unless the family intentionally omits portraits.
- Inputs need visible labels, connected errors (`aria-describedby`, `aria-invalid`), visible focus, sufficient targets, and no decorative shadows.
- Reuse the existing control/component variant exactly. Mobile pagination uses the established single-line previous/count/next composition; channel links use the existing button treatment; do not approximate either with plain text links.

## Accessibility and quality gate

Preserve the skip link, existing shell landmarks, one H1, logical heading order, keyboard navigation, visible focus, alt semantics, menu focus trap/inert behavior, filter/FAQ/form contracts, and reduced motion. Do not introduce a nested/duplicate landmark to repair a shared-shell issue on one page; flag or fix the shared owner when that is in scope.

Browser-check desktop (~1440px), tablet (~768px), mobile (~390px), and both sides of affected breakpoints. Check border continuity, content order/completeness, crop, overflow, 200% reflow, contrast, hover/focus, keyboard operation, sticky/header interaction, TOC anchors, forms, and changed JavaScript. Run the relevant build/format checks and tests only for behavior changed by the task.
