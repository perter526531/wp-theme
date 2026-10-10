=== Kabel Blocks ===
Contributors: kabelteam
Tags: blocks, block patterns, page builder, landing page, b2b
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 3.12.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Self-developed content-block engine: 17 blocks, 18 page layouts, 30 insert patterns, one-click site bootstrap. Best with the Kabel theme, works standalone.

== Description ==

The block engine that powers the Kabel Art Paint theme — a spec-driven
interpreter shared by PHP and the editor:

* **17 content blocks** (hero, split, features, stats, products, process,
  gallery, testimonials, logos, faq, cta, inquiry, contact-cards, milestones,
  specs, cta-quote, section container) — every block is registered from one
  PHP spec; the editor sidebar is generated automatically (text, textarea,
  range, select, toggle, media, multi-image, repeater with reorder)
* **18 full-page layouts** (home, about, eco-friendly, designer, products,
  4 color collections, projects, procurement, customize, distributor, blog,
  contact, faq, landing, starter)
* **30 insert patterns** in two collections (Kabel Pages / Kabel Sections)
* **One-click site bootstrap** — fresh installs get the complete Kabel site
  automatically; established sites get a guarded "Create the pages" button;
  existing content is never overwritten
* **Migration-aware media** — sample images reuse existing attachments by
  their original upload path before side-loading anything
* **Standalone mode** — on any other theme the blocks render with their own
  dark skin and the consultation form posts to a REST endpoint (kbl_lead CPT)

== Installation ==

1. Upload `kabel-blocks.zip` via Plugins → Add New → Upload
2. Activate — pairs automatically with the Kabel theme
3. Fresh site: pages appear on your next wp-admin visit
   Existing site: Appearance → Kabel Setup → Create the pages

== Changelog ==

= 3.12.0 =

* Performance: every shipped asset is now minified at build time — the
  editor bundle drops from 292 KB to 157 KB (-46%), the front skin from
  60 KB to 36 KB (-39%), the front runtime from 9 KB to 4.7 KB. A dead
  52 KB editor runtime file that nothing referenced was removed.
* Performance: pages without any Kabel block (a plain blog post on a
  pure-plugin site) no longer load the plugin's CSS/JS at all — the
  check covers the post, synced patterns, archive loops and block
  widgets, and always loads for logged-in editors.
* Performance: request-level caches for the GTranslate language table,
  the global-color slots, the site-chrome gate and the nav-menu
  resolution (header + footer nav on one page share a single query);
  the standalone chrome now preloads the two Barlow weights the header
  text uses, removing the first-viewport font swap.
* New: Kabel → Tools — move all demo pages to the Trash (reading
  settings self-heal), and migrate raw hex colors on existing pages
  into global-color tokens (exact matches only, in block attributes
  and inline styles; every page keeps a revision for one-click undo).
* New: the Layout Library cards show REAL screenshots of every layout
  (regenerated from the live previews) instead of wireframe SVGs —
  with a zero-asset fallback when images are stripped.
* Fixed: the Chinese editor dictionary was missing 44 newer strings,
  which had silently broken the build gate since v3.10; an interrupted
  bootstrap now keeps its resume flag instead of wiping it (a run
  without unfiltered_html previously left 14 empty pages with no retry).

= 3.11.0 =

* Standalone site chrome: without the Kabel theme the plugin now takes
  over the site header & footer — the editable "Site Header" / "Site
  Footer" part pages, the Classic / Minimal starter layouts (Kabel →
  Header & Footer), and the admin-bar Edit Header / Edit Footer
  shortcuts, all previously theme-only. On block themes (e.g. Twenty
  Twenty-Five) the theme's own header/footer template parts are swapped
  for the Kabel chrome; classic themes get an opt-in injection. Fresh
  plugin-only sites are seeded automatically; existing installs stay
  untouched until the toggle is flipped.
* Plugin-mode navigation: the one-click bootstrap now creates the seed
  "Primary Menu" even on location-less block themes (the kbl/nav-menu
  block resolves menus by name there) — the default theme's page-list
  fallback could never be edited from Appearance → Menus, which is why
  deleted menu items kept "showing" on the page.
* Nav-menu auto resolution: a deleted Primary Menu no longer falls
  through to an unrelated footer menu just because it sorts first
  alphabetically; by-name lookup runs before the generic first-menu
  fallback.
* Cache purging for the standalone chrome: layout applies, chrome
  seeding, part-page saves and the chrome toggle now flush LiteSpeed /
  WP Super Cache / W3TC / WP Rocket / SiteGround / Cache Enabler (the
  theme had this; plugin-only installs now do too).
* Version header fix: the Plugin Version header lagged the internal
  constant since 3.10.0 (WP showed "3.9.0" for the fixed builds) — both
  now read 3.11.0.

= 3.10.0 =

* Language switcher dropdown: the header switcher now renders as a compact
  globe + current-language + chevron button that opens the full language
  panel — a 9-language GTranslate configuration no longer wraps the header
  into two rows. Switch back to the flat link row any time from the block
  sidebar (Layout → Inline links); existing blocks upgrade automatically.
* GTranslate bridge: the current-language marker (and the dropdown's toggle
  label) now follows the live ?lang= request language after a switch.
* Demo menu blueprint: the auto-created Primary Menu converges to seven
  top-level items (HOME / ABOUT / PRODUCTS / COLORS / PROJECTS / NEWS /
  CONTACT) — Project Procurement, Customize and Distributor nest under
  PROJECTS / ABOUT, so the seeded header row no longer wraps. Existing
  sites keep their menu; adjust it under Appearance → Menus.

= 3.9.0 =

* Global Colors (Stackable-style): six site-wide color slots managed under
  Kabel → Global Colors. Every block color picker offers the slots first —
  pick one and the block stores var(--kbl-gc-N), so re-skinning the brand
  palette is a single save. Slots render on the front end AND inside the
  editor canvas.
* Layout Library (Blocksy/Elementor starter-flow): new top-level "Kabel"
  menu — 18 ready layouts as a thumbnail gallery (SVG wireframes grouped by
  category) with live standalone previews (real markup on the plugin skin)
  and one-click "Create page" (draft + sample media sideload + straight
  into the editor).
* Editor welcome notice: one Gutenberg-native notice per plugin version
  linking to the Layout Library and Global Colors.
* uninstall.php: plugin-owned options cleaned on uninstall — content,
  media and pages are never touched.
* Fixed duplicated heading keyword in the block inserter search.


= 3.8.0 =
* GTranslate integration rebuilt around a single source of truth: the plugin's own settings now own the language list. The front end re-renders the switcher row from GTranslate's configured languages (fincl_langs / incl_langs, honouring its native-names preference) on every request — a language added under Settings → GTranslate appears in the header immediately, a removed one disappears.
* The language-switcher sidebar no longer maintains a second editable copy in GTranslate mode: it shows the plugin's languages read-only with a "Resync from GTranslate" button (new REST endpoint kbl3/v1/gtranslate/languages) and a direct link to the GTranslate settings page. Custom-links mode keeps the full item editor.
* Entering GTranslate mode adopts the plugin's live language list instead of remapping the seed codes (the remap stays as the no-plugin fallback); switching back to custom restores the owner's links untouched.

= 3.7.1 =
* Fixed: the tablet (fontSizeT) and mobile (fontSizeM) type overrides on the header elements (nav menu / language switcher / contact info) never applied — their responsive rules sat before the component base rules in the stylesheet, losing the CSS cascade. The rules now live after the components, so header rows keep single-line integrity through the tablet breakpoint.

= 3.7.0 =
* Editor hardening: every block's edit() now renders inside an error boundary — a failing block degrades to a placeholder instead of the fatal "The editor has encountered an unexpected error" modal; the rest of the page stays editable (verified by injecting a simulated crash).
* All 28 blocks migrated to block API version 3 (the iframe editor direction WordPress 6.9+ recommends); canvas styles now load through `enqueue_block_assets` so they reach the iframe document — the 56 "deprecated API version 2" / "added to the iframe incorrectly" console warnings are gone.
* Language switcher: new "Language source" control. "GTranslate (auto translate)" turns the row into live language switching — entries carry GTranslate codes (en, zh-CN, it …), the front-end anchors gain data-gt-lang, and a render_block bridge loads GTranslate's own base.js so clicking really switches the language (seed codes remap automatically; a sidebar notice detects whether GTranslate is installed). The active-language underline now follows the live language while switching.
* WordPress 7.1 verified end to end in a real install.

= 3.6.0 =
* Heading: full H1–H6 level range in the toolbar and the Layout tab (was H2–H5); a hint under the size presets points to the custom px typography panel.
* Section: the custom Background/Text color panel (palette + custom hex picker with alpha, clear button, live contrast check) moved into the Layout tab directly under the Tone preset — no more hunting through the Style tab.
* Image: on-canvas drag-to-resize (core-image pattern — side handles while selected, live px readout committed on release), an exact Width (px) slider (40–1920) that wins over the percent width, an Aspect ratio control (original / 1:1 / 4:3 / 3:4 / 16:9 / 9:16 / custom WxH), and a matching .kbl3-fig--px front-end rule; the PHP emitter twin already spoke this contract — the editor side now does too (TWIN_MATCH 36/36).

= 3.5.0 =
* Five Blocksy-style header elements for the block-based site header: Logo (width control + optional link, MediaPlaceholder/Replace flow), Nav menu (reads a WordPress menu, one nesting level with flattened deep items, current-page highlight, hover/focus sub menus, mobile hamburger collapse scoped to html.kb-js), Search (GET form to the site search results with width/radius/colors), Language switcher (repeater: code/label/link/current, alignment, active-language color) and Contact info (repeater: 28-icon picker, label, optional link, icon/text colors).
* All five support the suite's Spacing (margin top/bottom + mobile) and device Visibility panels; nav menu adds the full responsive Typography panel.
* Nav toggle: 44px touch target, aria-expanded synced, Esc collapses, link clicks auto-collapse; without JavaScript the menu stays fully expanded.
* Emitter twins (kbl3_logo / kbl3_nav_menu / kbl3_search / kbl3_language_switcher / kbl3_contact_info) keep seeded pages validation-clean; 38 new zh-CN editor strings.

= 3.4.0 =
* Stackable's column workflow, ported wholesale: inserting a Columns block now shows a layout picker (100 / 50-50 / 30-70 / 70-30 / 33-33-33 / 25-50-25 / 25-25-25-25) — registered as real block variations, so the inserter offers them too.
* Sidebar width kit on the Columns block: preset thumbnails (2/3/4-column arrangements), a proportional slider with draggable dividers, and one number input per column whose widths mirror the real proportions — type 40 + 40 + 20 and the canvas renders exactly that.
* Widths are Stackable shares that always sum to 100: changing one column hands the difference to its neighbor, inputs normalize on blur, and the minimum per column is 10% (controls) / 5% (canvas).
* Canvas: BOTH edges of every column are drag handles (right unless last, left unless first); dragging moves the boundary with the neighbor and snaps to 25 / 33.3 / 50 / 66.7 / 75 / 100% (every 10% with Shift).
* The corner width badge is now clickable: a numeric popup (Enter applies, Esc closes, Tab hops to the next column) — Stackable's ResizableTooltip.
* New Column order control reorders columns left/right; adding or removing a column resets the row to an even split (Stackable semantics); the row stays selected while columns are added.

= 3.3.0 =
* Editor UI fully localized in Simplified Chinese — block names, descriptions, keywords, inspector labels, placeholders and pattern titles (block-level dictionary with a build gate; published pages stay byte-identical).
* Column widths are now row shares: set any column to any value (40 + 40 + 20) and the row renders exactly that proportion with no overflow at any gap.
* Setting a column width auto-balances unset siblings; resetting restores an even row; add-column re-equalizes custom rows; every column shows a live share badge (editor only).
* Columns toolbar: add column + equalize widths; column toolbar: remove column; count control now 1-8.
* Fixed "keep row on mobile" silently stacking (flex 100% -> real side-by-side with desktop shares).
* Icon box: icon position top/left/right, icon vertical alignment, icon background chip (color + padding + radius).
* Section: custom background color (17-swatch palette + custom picker, overrides tone), custom content width in px, padding sliders moved to the Layout tab.

= 3.1.0 =
* Stackable-grade style controls on every block: responsive typography
  (desktop/tablet/mobile font sizes, weight, line-height, letter spacing,
  text transform), device visibility (hide on desktop/tablet/mobile),
  hover effects (button colors + lift), entrance animations with delay,
  gradient backgrounds, and section shape dividers (wave / tilt / arc).
* Six new content blocks: Card, Tabs, Testimonial, Video, Team, Pricing.
* Fixes: bootstrap resume check no longer fatals on fresh installs.

= 3.0.0 =
* Complete rewrite as client-side blocks (Stackable-style): free layouts
  with 1-6 column rows, inline canvas editing, inserter collection pinned
  on top, PHP emitter keeps seeded pages validation-clean.

= 1.0.0 =
* First Kabel release: full engine rebrand from the Dazhen v2 block kit,
  18 Kabel page layouts, 30 patterns, paint-series content model,
  migration-aware media bootstrap, dark-luxury standalone skin.

= 3.2.0 =

Fine-grained styling controls (Stackable parity round 2):

* Custom colors everywhere — the full brand palette plus a free hex picker with transparency on every color control
* Column width control — drag the grip between columns or use the width slider (10-100%)
* Reverse column order on mobile + separate row gap when columns stack
* Section minimum height + vertically centered content
* Button horizontal/vertical padding, custom border with solid/dashed/dotted styles
* Icon box icon size, text block color
* Spacing panel on every block — margin top/bottom with separate mobile values
