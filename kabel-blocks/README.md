# Kabel Blocks (v3.0.0)

The Stackable-style block suite that pairs with the [Kabel theme](../kabel).
Runs standalone on any WordPress site too.

## The architecture (why v3 is different)

```
src/*.js (JSX sources)
   │  build.mjs (esbuild, @wordpress/* aliased to window.wp globals)
   ▼
assets/js/blocks.js — one self-contained editor bundle
   │
   ├── 17 client-side blocks: containers (Section, Columns, Column),
   │    content (Heading, Text, Button, Button row, Image, Icon,
   │    Icon box, List, Spacer, Separator, Stat, FAQ item) and
   │    dynamic (Inquiry form, Post grid)
   │
   └── inc/emitter.php — the PHP twin of every save():
        layouts, patterns and the one-click seeder emit the exact
        markup the editor would write (byte-for-byte, so seeded
        pages open with zero block-validation warnings)
```

* **Free nesting**: Section holds anything; Columns is a 1–6 column row
  (live count control, gap, vertical alignment); every Column is a free
  container with background color/image, padding, radius, shadow, border.
* **Inline editing**: every heading, paragraph, button label, list item,
  stat number and icon-box text is click-to-edit right on the canvas.
* **Stackable-style sidebar**: Layout / Style / Advanced tabs per block —
  colors, alignment (text + content), corner radius, box shadows, borders.
* **Discovery**: the "Kabel Blocks" category is pinned to the top of the
  inserter and every kbl/* block is grouped under one collection header.
* **Dynamic pair**: the inquiry form and the products/projects/posts grid
  render server-side (fresh nonces, live queries).
* **Legacy safety**: pages seeded by v1/v2 keep rendering through the
  retained engine; untouched ones are auto-upgraded to v3 blocks on
  plugin update (byte-hash comparison — user-edited pages are never
  touched).

## Build

```
bun install && bun run build   # src/ -> assets/js/blocks.js
```

## v3.1 — Stackable-grade controls

- Responsive typography (desktop / tablet / mobile), device visibility,
  hover effects, entrance animations, gradient backgrounds, shape dividers.
- Six new blocks: Card, Tabs, Testimonial, Video, Team, Pricing.
