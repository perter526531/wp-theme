=== Dazhen Blocks — 大振内容块 ===
Contributors: dazhenrd
Tags: blocks, block patterns, page builder, landing page, b2b
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 2.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Self-developed content-block engine: 17 blocks, 8 page layouts, one-click page filler. Best with the Dazhen theme, works standalone.

== Description ==

The block kit that powers the Dazhen theme — rebuilt as a real engine:

* **17 self-developed blocks** (dazhen-blocks/*): Hero, Text+Image, Feature Cards, Stats, Products grid (live), Process, Gallery, Testimonials, Client Logos, Specs table, FAQ, CTA banner, Inquiry form, Contact cards, Milestones, Specs, Section container (nested), Quote button.
* **Spec-driven editor runtime** — every block's sidebar controls are auto-built from its spec. What you edit is what visitors see (server-rendered).
* **8 full-page layouts** + **18 insert patterns**, all composed from our own blocks.
* **One-click filler**: empty pages get a full editable layout from the Pages list, the row action or the editor sidebar.
* **Standalone-safe**: without the Dazhen theme the plugin ships its own compact A×B skin + a lead form with REST endpoint and a Leads inbox.

== Installation ==

1. Upload the `dazhen-blocks` folder to `/wp-content/plugins/`.
2. Activate through the **Plugins** screen.
3. Open any page — the sidebar shows **Dazhen layouts · 大振版式**; empty pages can be filled in one click.

== Changelog ==

= 2.0.2 =
* Logged-out click on "Create the pages" now redirects to the login screen instead of WordPress' empty error box (admin-post.php has no built-in login redirect).
* A capability failure no longer redirects silently — a clear admin notice explains the Administrator-role requirement.

= 2.0.1 =
* Site bootstrap: a fresh WordPress now gets the complete site automatically — the seven core pages (Home, About Us, Services, Solutions, Contact, FAQ, Blog) are created pre-filled with their block layouts, the static front page + posts page are set, and the navigation menus are built. Sites that already have content get a one-click "Create the pages · 一键初始化整站" notice instead (missing pages only, nothing is ever overwritten).

= 2.0.0 =
* The engine rewrite: 17 blocks registered from declarative specs, generic schema-driven editor runtime (zero per-block JS), 8 page layouts, 18 patterns, standalone skin + leads fallback.

= 1.0.1 =
* First public release: 17 core-block patterns, seeder, 2 dynamic blocks.
