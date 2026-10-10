# Dazhen WordPress Theme

Custom B2B inquiry-oriented theme for **Dazhen (大振科技)** — smart water
appliance manufacturer, Dayawan, Huizhou, China.

- **Design system**: A×B zones — deep-sea showcase (A) + minimal reading (B)
- **Zero commercial dependencies**: no page builder, no ACF, no slider plugin
- **Built-in inquiry system**: 7-field form → private CPT → admin management →
  email forwarding (multiple recipients, Reply-To = customer)
- **Multilingual**: GTranslate-ready (auto switcher slot in header); Polylang
  upgrade path kept; all strings translatable (text domain `dazhen`)

## Requirements

- WordPress 6.5+
- PHP 8.1+

## Install

1. Zip the `dazhen` folder → `dazhen.zip`
2. WP Admin → Appearance → Themes → Add New → Upload Theme
3. Activate, then visit Settings → Permalinks once (flushes rewrite rules)

## After activating — 10-minute setup

1. **Pages**: create `About Us` (template **About** — slogan hero + stats +
   milestones timeline + core-tech cards + certification wall + factory
   module), `Solutions` (slug `solutions`, template **Solutions Overview**),
   `Services` (template **Services** — hero + stats strip + advantages +
   interactive OEM journey + FAQ accordion + prefilled inquiry form),
   `Contact Us` (slug `contact`, template **Contact** — info cards + two-column
   inquiry form), `Blog` (set as posts page) — or skip this step by importing
   the sample content below, which pre-creates all of them with the right
   slugs and page templates
2. **Menus**: Appearance → Menus → assign to *Primary Navigation*
   (fallback links work until then)
3. **Contact & emails**: Appearance → Customize → *Contact & Inquiry Emails* —
   set phone / email / address, the **working-hours / time-zone line** shown
   next to the Contact form, and the **inquiry forwarding email list**
4. **Logo**: Customize → Site Identity
5. **Homepage**: Settings → Reading → static page (or leave blank — the theme
   front-page shows the A×B homepage)
6. **Content**: Products / Solutions / Cases each have their own admin menu
   with custom field panels (spec table = repeater rows; same "group" merges).
   The Services / About / Contact page templates carry the full confirmed
   copy (editable via the `dazhen_services_*`, `dazhen_oem_journey_steps`,
   `dazhen_about_*` and `dazhen_contact_slogan` filters); their editor areas
   replace the default intro when content is added. On the About page, set
   the **featured image** to fill the lineup photo; the three tech-proof
   thumbnails auto-link to products whose model matches `_dz_product_model`.
   The **Dazhen** inserter category keeps the reusable CTA pattern for any
   post or page
7. **Multilingual (optional)**: install the **GTranslate** plugin — the header
   language switcher appears automatically

## Sample content (recommended first run)

`sample-content/dazhen-sample.xml` ships the full first-run site: 6 pages
(Home, Solutions, About Us, Services, Blog, Contact Us — the four template
pages arrive with their page template already selected), 6 news posts across
the Company News / Industry News categories (titles and dates match the
confirmed blog design; the thick-film explainer carries the full design
article), 6 products (18E-1/2/3/4, F900-QC, Pet Fountain), 4 solution
scenarios and 3 success cases — enough to see every module working (blog +
cards + catalog + finder + map + case cards).

1. Tools → Import → WordPress (install the importer if prompted)
2. Upload `dazhen-sample.xml`, assign to your user, check **Download and import
   file attachments** off (no media in the file — cards and heroes fall back
   to clean placeholders until images are set)
3. Settings → Reading: set **Home** as the static front page and **Blog** as
   the posts page (until then the theme front page still shows the A×B
   homepage)
4. After import, open each product and set a **featured image** (the card,
   hero and finder use it), and tick **related products** on cases and
   **recommended products** on solutions (post-ID references cannot survive
   an import)
5. Delete the sample posts when real content is ready

Regenerate (dev only): `bun run tools/wxr/build-wxr.mjs` — the script keeps
PHP-serialized spec rows byte-accurate.

## Notes

- **Structure**: hierarchy templates (header / footer / front-page / single /
  archive …) live at the theme **root** as WordPress requires for classic
  themes; `page-templates/`, `template-parts/`, `inc/`, `assets/`,
  `sample-content/` and `languages/` are subdirectories
- Inquiries live in **Inquiries** admin menu (badge shows new count; CSV export
  submenu; status: New → Following up → Won)
- Every inquiry emails the site admin + the forwarding list; replying in your
  mail client goes directly to the customer (Reply-To)
- The **Solution Finder** on `/products/` scores products by series tags —
  extend or override the map via the `dazhen_finder_series_tags` filter
- **Blog**: the posts page and category archives show filter chips that link
  to category archives (no JS — server-rendered); empty categories stay
  visible so their "No articles in this category yet" state is reachable.
  Cards show category badge, date, reading time (`words / 200`) and the
  featured image when set; single posts add prev/next navigation and an
  end-of-article inquiry CTA
- The **delivery map** dots come from `assets/img/world-dots.svg` (generated
  from Natural Earth data; regenerate via `bun run tools/map/build-world-map.mjs`)
- Fonts are self-hosted (Space Grotesk + Inter, ~70KB). Noto Sans SC ships in
  phase 2 with the multilingual expansion
- Run `wp i18n make-pot . languages/dazhen.pot` after string changes to
  regenerate the translation template

## License

GPL v2 or later.

## Where to edit what · 哪里改什么 (v0.4)

| What | Where |
| --- | --- |
| **Pages / homepage (drag-and-drop)** | Open the page → the **Dazhen Blocks** engine renders editable blocks. Empty page = built-in designed template (fill from the Pages list or the editor sidebar "Dazhen layouts") |
| **Blocks library (17)** | Editor inserter → "Dazhen Blocks · 大振内容块" — every block edits from the right sidebar (repeater cards, image pickers, icons) |
| **Ready-made pages (8)** | Inserter → Patterns → "Dazhen Pages · 大振整页模板" (Home/About/Services/Solutions/Contact/FAQ/Landing/Starter) |
| **Products** | Products → Add New: model, tagline, bullets, multi-image gallery (carousel), grouped spec table, series (drives mega menu + related) |
| **Product CTA → inquiry popup** | Automatic: every "Inquire" button opens the RFQ modal (pre-fills the model) |
| **Inquiries inbox** | Inquiries admin menu — popup + page form submissions, CSV export |
| **Blog sidebar** | Appearance → Widgets → Blog Sidebar (empty = full-width layout) |
| **Comments** | Automatic under articles (comments.php); toggle in Settings → Discussion |
| **Header contact / CTA text / footer** | Appearance → Customize |
| **Multilingual** | Install GTranslate (machine) or Polylang (human) — the header shows the styled language dropdown automatically |
| **Companion plugin** | Appearance → **Dazhen Setup** — one-click install of the bundled Dazhen Blocks + recommended free plugins |

## Block editing rule (v0.4)

A page with editor content renders as blocks (Dazhen Blocks or anything
else); an empty page falls back to the built-in designed template. Clearing
a page's content switches back — both directions, no settings.

