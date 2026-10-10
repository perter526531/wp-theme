# Kabel WordPress Theme

High-end decorative-coating brand theme for **Kabel Art Paint** (headquarters:
Druento, Torino, Italy · branch: Foshan, Guangdong, China).

- **Design system**: dark luxury — pure black canvas, deep navy `#222E5E`
  actions, bronze `#B39A74` accents, self-hosted **Barlow** + handwritten
  **Oregano** flourishes, right-angle aesthetic
- **Zero commercial dependencies**: no page builder, no ACF, no slider plugin,
  no Stackable, no WooCommerce — runs on the bundled **Kabel Blocks** engine
- **Built-in consultation system**: 7-field form → private Inquiries CPT →
  admin management → email forwarding (multiple recipients, Reply-To = visitor)
- **Content model**: Paint Series (6 families, structured spec tables, photo
  galleries), Projects (home-renovation + engineering categories), News
  (company / industry / knowledge), 4 color-collection pages, FAQ
- **One-click site**: with Kabel Blocks active, a fresh WordPress installs the
  complete site automatically — 14 pages, 6 paint series, 3 articles, 2 project
  references, 3 menus, 120+ compressed sample images
- **Migration-aware**: sample images reuse the existing media library when the
  original upload paths are found — no duplicates on an established site
- **Multilingual-ready**: GTranslate slot in header; Polylang upgrade path;
  all strings translatable (text domain `kabel`)

## Requirements

- WordPress 6.5+
- PHP 8.1+
- The **Kabel Blocks** plugin (a current copy is bundled at
  `bundled/kabel-blocks/` and installable from Appearance → Kabel Setup)

## Install

1. Zip the `kabel` folder → `kabel.zip`
2. WP Admin → Appearance → Themes → Add New → Upload → Activate
3. Open any wp-admin page — you are redirected to **Appearance → Kabel Setup**
4. Click **Install & activate now** for the block engine (bundled copy)
5. On a fresh site the complete page structure is created automatically.
   On an existing site press **Create the pages** (nothing you already have
   is ever overwritten)

## Replacing a Blocksy / Stackable / Smart Slider setup

The theme renders every page with its own block engine. After activating:

1. Appearance → Kabel Setup → follow the cards
2. Pages are created as regular block pages — your existing posts, media and
   products stay untouched
3. You may then deactivate and delete: Blocksy, Blocksy Companion,
   Smart Slider 3, Stackable, WPForms, ACF, WooCommerce, Hostinger helper
   plugins (keep WP Mail SMTP if you want inquiry emails delivered)

## Assets

- `sample-assets/` — 121 compressed WebP images + `manifest.json`
  (original upload path → shipped file). Used by the one-click bootstrap;
  safe to delete after your site has its own media.
- `assets/fonts/kabel/` — Barlow 300–700 + italic 400, Oregano 400
  (GPL-friendly self-hosted subsets)

## Credits

Theme, block engine and sample-page design: built for Kabel.
License: GNU General Public License v2 or later.
