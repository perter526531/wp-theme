# Kabel Blocks v7 — Inline Editing Contract (仿 Stackable)

## Goal

Replace the "sidebar-fields + ServerSideRender" editing model with a
Stackable-style experience:

- Every text field is edited **on the canvas** (click & type) via `RichText`.
- Every image field renders **on the canvas**; click opens the media picker.
- Repeater lists (cards) render **on the canvas** with per-item inline editing
  and add / remove / move controls.
- The sidebar keeps **style controls only** (zone, padding, columns, layout
  variant, links, toggles). No text/textarea/media fields in the sidebar.
- The canvas is **styled like the front-end** (editor CSS mirrors the theme).
- The `section` block is an open `InnerBlocks` container (core + kabel blocks),
  so the user can drop a heading / paragraph / image anywhere.
- `ServerSideRender` is gone. No REST round-trip per keystroke.

## Spec additions (PHP side)

Every non-section spec gets an `edit` key — a declarative node tree:

```php
'edit' => array(
    'bg'   => 'bgImage',            // optional: attr holding a background image id
    'tree' => array( ...nodes... ),
),
```

### Node types

| Node | Shape | Renders as |
|---|---|---|
| text | `array( 'text' => 'attrName', 'tag' => 'h1', 'class' => 'kbl-hero__title', 'placeholder' => 'Headline' )` | single-line `RichText` (no formatting) |
| rich | `array( 'rich' => 'attrName', 'tag' => 'p', 'class' => '...', 'placeholder' => '...', )` | multi-line `RichText` (`multiline="br"`) |
| media | `array( 'media' => 'attrName', 'class' => 'kbl-hero__visual', 'imgClass' => 'kbl-hero__img', 'size' => 'large' )` | on-canvas image (click = change, empty = dashed placeholder) |
| buttons | `array( 'buttons' => array( array( 'label' => 'primaryLabel', 'class' => 'btn btn--cta', 'placeholder' => 'Consult Now' ), ... ) )` | a `.kbl-edit-btnrow` of RichText-styled buttons; hidden while label empty (matches render) |
| items | `array( 'items' => 'attrName', 'class' => 'grid grid--3', 'itemClass' => 'card card-feature', 'addLabel' => 'Add card', 'limit' => 0, 'item' => array( ...nodes... ) )` | repeater: real cards inline; hover shows ↑ ↓ ✕; "+ Add card" at end |
| switch | `array( 'switch' => 'layoutAttr', 'default' => 'split', 'cases' => array( 'split' => array(...nodes), 'center' => array(...nodes) ) )` | picks subtree by attribute value (reactive) |
| hasMedia | `array( 'hasMedia' => 'attrName', 'nodes' => array( ... ) )` | subtree only when media id > 0 |
| wrap | `array( 'wrap' => array( 'div', 'kbl-hero-center' ), 'nodes' => array( ... ) )` | a plain container div |
| static | `array( 'static' => '<div>…</div>' )` | RawHTML — static replica (forms, chips) |
| html of items' field is addressed by attr name; nesting items inside items is NOT allowed |

Rules:

- Inside `items`, `text`/`rich`/`media`/`buttons` nodes bind to the **item's**
  fields (per-item schema from the existing repeater `fields`).
- `placeholder` is required on text/rich nodes (empty-state UX).
- Tree must **mirror the PHP render markup** (same classes, same order) so the
  editor CSS makes it pixel-credible.
- Render callbacks that `esc_html` text fields stay valid (single-line
  RichText stores plain text). Multi-line `rich` values are rendered through
  `kbl_rich()` (kses: br/p/em/strong/…, plus `\n` → `<br>` normalization for
  legacy seeded values).

### Sidebar panels (panels[])

- REMOVE `text` / `textarea` / `media` / `media-array` / `repeater` fields —
  they now live on canvas.
- KEEP: `select`, `toggle`, `range`, and text fields that are **links or
  machine values** (button URLs, product prefill, IDs). Give such fields
  `'panel' => 'links'` grouping: keep them under a "Links" panel so no one
  mistakes them for content.

## JS runtime (assets/js/blocks-editor.js)

- Reads `window.KabelBlocksSpecs` (unchanged injection, now including `edit`).
- `buildTree(nodes, ctx)` renders the node list; `ctx` carries `attributes`,
  `setAttributes` and (inside items) a scoped item setter.
- `RichText` from `wp.blockEditor`; value normalized `\n` → `<br>` for display;
  onChange stores RichText HTML as-is.
- Media node: reuse `MediaThumb` (apiFetch). Click surface opens `MediaUpload`.
- Items node: array of item editors; controls on hover (move up/down, remove);
  add button appends `field.defaults`-shaped item (defaults live in the
  existing repeater spec field).
- `static` node: `wp.element.RawHTML`.
- Section block: `InnerBlocks` with `allowedBlocks` = all registered block
  names (core + kabel-blocks/*), plus a styled empty-state hint.
- Keep: "Kabel layouts" page sidebar, block collection, style sidebar.
- Vanilla `wp.element` only — no build step.

## Editor CSS (assets/css/kabel-editor.css)

- Scoped to `.kbl-wrap` (every kabel block root in the editor).
- Mirrors: zone backgrounds, container, hero/split/features/stats/process/
  milestones/gallery/testimonials/logos/specs/faq/cta/inquiry/contact/
  products/section shells, grids, cards, buttons, timeline, chips.
- Fonts: `assets.php` prints `@font-face` rules (pointing at the active
  theme's font files) into the editor via a dynamic handle.
- Editor affordances: dashed empty media placeholders, hover outlines on
  editable items, small round ↑ ↓ ✕ buttons, unobtrusive "+ add" buttons.

## Registration (inc/engine.php)

`register_block_type` gains:

```php
'style_handles'       => array( 'kabel-blocks-front' ),
'editor_style_handles'=> array( 'kabel-blocks-editor-css', 'kabel-blocks-fonts' ),
```

`inc/assets.php` registers those handles on every load (front + editor) and
keeps the current conditional enqueue behaviour for the front-end.

## Robustness (theme side, parallel workstream)

- Theme-side render fallback: when `kbl_specs()` does not exist on
  `after_setup_theme` (priority 20), the theme loads the **render-only** layer
  from `bundled/kabel-blocks/` (engine + spec files) so pages never render
  blank, plus an admin notice with one-click bundled-plugin activation.
- All "Create the pages" buttons guard on `kabel_blocks_plugin_is_current()`.

## Content compatibility (non-negotiable)

- Existing seeded pages (`kbl_block()` markup, plain-text attrs with `\n`)
  must keep rendering identically on the front-end and open in the editor
  with the same visible text.
- Blocks without an `edit` tree fall back to the generic ServerSideRender
  preview (safety net during migration; final v7 ships trees for all 17).
