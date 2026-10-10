# Dazhen Blocks — 大振内容块 (v2.0.0)

The self-developed block engine that pairs with the [Dazhen theme](../dazhen).
Runs standalone on any WordPress site too.

## The architecture (why v2 is different)

```
spec (data, PHP)  ──►  engine (inc/engine.php)
                        ├──► editor: generic runtime (assets/js/blocks-editor.js)
                        │      reads window.DazhenBlocksSpecs → registers every
                        │      block + auto-builds its sidebar controls
                        └──► front end: dzb_render_block() dispatch
                               spec.render callback → shared section shell
```

* **A block = one spec array.** Name, title, icon, attributes, panels
  (sidebar fields), render callback. No per-block JS, ever.
* **Field types**: `text`, `textarea`, `range`, `select`, `toggle`, `media`
  (single image), `media-array` (gallery), `repeater` (cards / rows / steps
  with add, remove, reorder, collapse).
* **Shared section anatomy**: every block understands `zone` (deep sea /
  paper / alt tint), `pad` (compact / default / spacious) and the
  eyebrow + title + intro heading — restyle from the sidebar, no CSS.
* **Serialization**: `dzb_block( 'dazhen-blocks/hero', array( … ) )` emits
  the Gutenberg comment markup, so layouts, patterns and the one-click
  seeder produce exactly what a user would hand-build.

## The 17 blocks

| Block | What it does |
| --- | --- |
| Hero | Page opener: split or centered, two buttons, image |
| Text + Image | Two-column story with checklist |
| Feature Cards | Icon card grid (12 icons) |
| Stats | Big-number strip |
| Products | **Live** catalog grid — new products appear automatically |
| Process | Numbered steps |
| Gallery | Image wall (multi-select) |
| Testimonials | Client quotes |
| Client Logos | Logo wall (image or text-chip fallback) |
| Specs Table | Two-column parameter table |
| FAQ | Native accordions |
| CTA Banner | Conversion banner with the amber quote button |
| Inquiry Form | 7-field lead form (theme) / compact form + Leads inbox (standalone) |
| Contact Cards | Phone / email / address cards |
| Milestones | Year timeline |
| Section | Universal container — drop ANY blocks inside (core, Stackable…) |
| Quote Button | Inline conversion button |

## Where things live

| Concern | File |
| --- | --- |
| Engine (registry / dispatch / shell / serialization) | `inc/engine.php` |
| Block specs: hero…milestones | `inc/blocks-content.php` |
| Block specs: gallery…faq | `inc/blocks-proof.php` |
| Block specs: products…section | `inc/blocks-convert.php` |
| 8 page layouts | `inc/layouts.php` |
| Pattern registration (2 categories) | `inc/patterns.php` |
| One-click filler (notices / handlers) | `inc/seeder.php` |
| Standalone leads (CPT + REST + form) | `inc/leads.php` |
| Enqueue + spec injection | `inc/assets.php` |
| Generic editor runtime | `assets/js/blocks-editor.js` |
| Standalone A×B skin | `assets/css/dazhen-blocks.css` |

## One-click filler — three entrances

1. **Pages list** → notice with “Fill all empty pages · 一键填充”.
2. **Row action** on any empty page → “Fill with Dazhen layout”.
3. **Editor sidebar** → “Dazhen layouts · 大振版式” panel (pick layout,
   fill or replace with confirmation).

Rule everywhere: a page with content renders blocks; an empty page falls
back to the theme’s built-in designed template (Dazhen theme).

## Pairing with the Dazhen theme

With the theme active the plugin reuses the theme's product card, inquiry
system (7-field form + CPT inbox + email), RFQ popup and full A×B CSS —
and loads **no** front-end CSS of its own. Without the theme, everything
still works via the standalone skin and the leads module.

## Extending

```php
add_filter( 'dzb_specs', function ( array $specs ): array {
    $specs['dazhen-blocks/hero']['panels'][0]['fields'][] = array(
        'attr'  => 'badge',
        'label' => 'Badge',
        'type'  => 'text',
    );
    return $specs;
} );
```

The sidebar control appears automatically — that is the whole point.
