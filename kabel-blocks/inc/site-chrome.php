<?php
/**
 * Standalone site chrome — header & footer without the Kabel theme.
 *
 * The owner's real-world flow that exposed the gap: a brand-new WordPress,
 * Kabel Blocks activated, the Kabel THEME never uploaded. The bootstrap
 * built all 14 demo pages, but the site chrome belonged to the default
 * theme — a page-list fallback navigation that no menu edit could ever
 * change ("I deleted the menu but the page still shows it") and no header
 * layout mechanism at all ("switched the nav, the site still shows the old
 * one"). Plugin mode needs the same editable chrome the theme ships.
 *
 * This module ports the theme's site-parts system (inc/site-parts.php) to
 * the plugin, using the SAME storage so a site can move between the two
 * worlds without losing its chrome:
 *
 *   kabel-header  "Site Header"  private page → renders as the site header
 *   kabel-footer  "Site Footer"  private page → renders as the site footer
 *
 * Activation model (kbl3_site_chrome_enabled()):
 *   - Kabel theme active → never (the theme owns the chrome — this module
 *     stays completely quiet, its admin screen and admin-bar links hidden).
 *   - Option kbl3_site_chrome 'on' / 'off' → the owner's explicit choice.
 *   - Default (no option yet): ON for block themes on a site this plugin
 *     bootstrapped (option kabel_bootstrapped) — the "fresh WP + plugin"
 *     flow gets the full Kabel experience. OFF everywhere else, so an
 *     existing site that merely uses the blocks is never hijacked.
 *
 * Rendering model:
 *   - Block themes (Twenty Twenty-Five & friends): the theme's header /
 *     footer TEMPLATE PARTS are swapped for our rendered part through the
 *     render_block filter — exactly one header, exactly one footer, the
 *     theme's own chrome gone. Empty part → the theme's part renders
 *     again ("clear the page to fall back to the theme header").
 *   - Classic themes: opt-in injection at wp_body_open + wp_footer (the
 *     theme's own header/footer markup cannot be suppressed cleanly, so
 *     the toggle defaults OFF and the screen explains the trade-off).
 *
 * The Header & Footer admin screen (Kabel → Header & Footer) carries the
 * two starter layouts (Classic / Minimal — same option keys as the theme:
 * kabel_header_layout + kabel_header_layout_seed_md5, so the applied state
 * survives a theme switch), the Edit Header / Edit Footer shortcuts, the
 * chrome toggle, and the cache purge the $wpdb writes need.
 *
 * @package Kabel_Blocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * The two site parts: key → slug + page title (same slugs as the theme).
 *
 * @return array<string, array{slug:string, title:string}>
 */
function kbl3_site_part_defs(): array {
        return array(
                'header' => array(
                        'slug'  => 'kabel-header',
                        'title' => 'Site Header',
                ),
                'footer' => array(
                        'slug'  => 'kabel-footer',
                        'title' => 'Site Footer',
                ),
        );
}

/**
 * Look up one site-part page (WP_Post or null), cached per request.
 *
 * Slug-exact and private-only; get_posts() defaults to suppress_filters,
 * so front-end renders are immune to plugins that hide private posts from
 * anonymous queries.
 *
 * @param string $which 'header' or 'footer'.
 * @return WP_Post|null
 */
function kbl3_site_part( string $which ) {
        static $cache = array();

        $defs = kbl3_site_part_defs();
        if ( ! isset( $defs[ $which ] ) ) {
                return null;
        }
        if ( array_key_exists( $which, $cache ) ) {
                return $cache[ $which ];
        }

        $posts = get_posts( array(
                'name'                   => $defs[ $which ]['slug'],
                'post_type'              => 'page',
                'post_status'            => 'private',
                'posts_per_page'         => 1,
                'no_found_rows'          => true,
                'update_post_term_cache' => false,
        ) );

        $cache[ $which ] = $posts[0] ?? null;
        return $cache[ $which ];
}

/**
 * Render one site part through the full the_content filter chain.
 *
 * do_blocks (priority 9 on the_content) parses the block comments and
 * renders the saved markup, so no global post setup is needed. The two
 * chain side effects are contained exactly like the theme's twin:
 * prepend_attachment is parked (it would inline the main query's
 * attachment into the header) and wpautop — which do_blocks removes when
 * it sees blocks — is restored so classic-content pages rendered later
 * keep their paragraphs.
 *
 * @param string $which 'header' or 'footer'.
 * @return string '' when the part is missing or empty.
 */
function kbl3_site_part_render( string $which ): string {
        $post = kbl3_site_part( $which );
        if ( ! $post instanceof WP_Post ) {
                return '';
        }

        $content = trim( (string) $post->post_content );
        if ( '' === $content ) {
                return '';
        }

        $attachment_priority = has_filter( 'the_content', 'prepend_attachment' );
        if ( false !== $attachment_priority ) {
                remove_filter( 'the_content', 'prepend_attachment', $attachment_priority );
        }
        $autop_priority = has_filter( 'the_content', 'wpautop' );

        $out = apply_filters( 'the_content', $content );

        if ( false !== $attachment_priority ) {
                add_filter( 'the_content', 'prepend_attachment', $attachment_priority );
        }
        if ( false !== $autop_priority && false === has_filter( 'the_content', 'wpautop' ) ) {
                add_filter( 'the_content', 'wpautop', $autop_priority );
        }

        return $out;
}

/* ---------------------------------------------------------------------
 * Starter layouts
 * ------------------------------------------------------------------- */

/**
 * The available header layouts — same registry shape as the theme's, and
 * the SAME option keys record the applied state (kabel_header_layout), so
 * switching between the Kabel theme and plugin mode keeps the choice.
 *
 * @return array<string, array{label:string, desc:string, seed:string, preview:string}>
 */
function kbl3_header_layouts(): array {
        return array(
                'classic' => array(
                        'label'   => __( 'Classic — top bar', 'kabel-blocks' ),
                        'desc'    => __( 'Slim top bar with contact info and language links above the main bar: logo · menu · search · button.', 'kabel-blocks' ),
                        'seed'    => 'kbl3_header_seed_classic',
                        'preview' => 'kbl3_header_preview_classic',
                ),
                'minimal' => array(
                        'label'   => __( 'Minimal — single bar', 'kabel-blocks' ),
                        'desc'    => __( 'One clean bar without a top bar: logo · menu · search · language links on the right · button.', 'kabel-blocks' ),
                        'seed'    => 'kbl3_header_seed_minimal',
                        'preview' => 'kbl3_header_preview_minimal',
                ),
        );
}

/**
 * The header layout recorded as active ('classic' until an owner applies
 * another one). Shared with the theme: the option only tracks the last
 * applied preset — manual edits are the content's own state, detected
 * through the stored seed hash instead.
 */
function kbl3_header_layout_current(): string {
        $key     = (string) get_option( 'kabel_header_layout', 'classic' );
        $layouts = kbl3_header_layouts();
        return isset( $layouts[ $key ] ) ? $key : 'classic';
}

/**
 * Language-switcher seed shared by every header layout.
 *
 * GTranslate-aware: when the GTranslate plugin is active the seed starts
 * in GTranslate mode with real language codes — clicking the links
 * machine-translates the site out of the box. Otherwise the plain custom
 * link mode is seeded.
 *
 * @param array $args Attributes forwarded to the emitter.
 */
function kbl3_header_lang_seed( array $args ): string {
        if ( function_exists( 'kbl3_gtranslate_active' ) && kbl3_gtranslate_active() ) {
                $args['source'] = 'gtranslate';
                /* The plugin's settings own the list — seed exactly what
                 * GTranslate is configured to expose (front-end renders
                 * re-derive it on every request anyway). */
                $items = function_exists( 'kbl3_gtranslate_items' ) ? kbl3_gtranslate_items() : array();
                if ( array() === $items ) {
                        $items = array(
                                array( 'code' => 'en',    'label' => 'English',  'url' => '', 'current' => true ),
                                array( 'code' => 'zh-CN', 'label' => '中文',     'url' => '', 'current' => false ),
                                array( 'code' => 'it',    'label' => 'Italiano', 'url' => '', 'current' => false ),
                        );
                }
                return kbl3_language_switcher( $args, $items );
        }
        return kbl3_language_switcher( $args );
}

/**
 * Header seed dispatcher — builds the markup for one layout key ('' or an
 * unknown key falls back to the recorded current layout).
 */
function kbl3_site_part_seed_header( string $layout = '' ): string {
        $layouts = kbl3_header_layouts();
        if ( ! isset( $layouts[ $layout ] ) ) {
                $layout = kbl3_header_layout_current();
        }
        $seed = $layouts[ $layout ]['seed'];
        return $seed();
}

/**
 * Seed markup for a site part ('' when the emitter twins are unavailable —
 * practically never; they ship with this very plugin).
 *
 * @param string $which 'header' or 'footer'.
 */
function kbl3_site_chrome_seed( string $which ): string {
        if ( ! function_exists( 'kbl3_columns' ) ) {
                return '';
        }
        return 'header' === $which ? kbl3_site_part_seed_header( kbl3_header_layout_current() ) : kbl3_site_chrome_seed_footer();
}

/**
 * The plugin's own logo asset (the theme file is not reachable without
 * the theme — the seed would point at a dead URL).
 *
 * @return string
 */
function kbl3_chrome_logo_url(): string {
        return esc_url( KABEL_BLOCKS_URI . 'assets/img/logo-kabel.svg' );
}

/**
 * Layout "classic" — two rows: slim top bar (contact · languages) above
 * the main bar (logo · nav · search · CTA). Column rhythm matches the
 * theme's twin seed (20 / 52 / 15 / 13) so the visual result is the same
 * on both worlds.
 */
function kbl3_header_seed_classic(): string {
        $muted  = 'rgba(255,255,255,0.72)';
        $bright = '#D3BC97';

        /* Row 1 — top bar. */
        $top_left = kbl3_column( array( 'width' => 50 ), kbl3_contact_info( array(
                'color'     => $muted,
                'iconColor' => '#B39A74',
                'fontSize'  => 12,
                'gap'       => 22,
        ) ) );

        $top_right = kbl3_column( array( 'width' => 50 ), kbl3_header_lang_seed( array(
                'color'       => $muted,
                'activeColor' => $bright,
                'fontSize'    => 12,
                'align'       => 'right',
        ) ) );

        $top = kbl3_columns( array( 'gap' => 16 ), $top_left . "\n\n" . $top_right );

        /* Row 2 — main bar. Column rhythm tuned for a seven-item primary
         * menu (four items carry submenus + chevrons). Measured at the
         * 1290px container the previous 20/52 shares + 28px gaps left the
         * seventh item ~60px short — CONTACT wrapped onto a second line
         * (the "chaotic header" report). Wider nav column + tighter
         * rhythm keeps the row intact with ~60px of headroom. */
        $col_logo = kbl3_column( array( 'width' => 18 ), kbl3_logo( array(
                'url'     => kbl3_chrome_logo_url(),
                'alt'     => 'Kabel Paints',
                'width'   => 150,
                'linkURL' => esc_url( home_url( '/' ) ),
        ) ) );

        $col_nav = kbl3_column( array( 'width' => 54 ), kbl3_nav_menu( array(
                'color'      => '#FFFFFF',
                'hoverColor' => $bright,
                'fontSize'   => 14,
                'fontSizeT'  => 12,
                'gap'        => 18,
                'padX'       => 3,
        ) ) );

        $col_search = kbl3_column( array( 'width' => 15 ), kbl3_search( array(
                'width'       => 170,
                'placeholder' => 'Search…',
                'inputBg'     => 'rgba(255,255,255,0.08)',
                'borderColor' => 'rgba(255,255,255,0.18)',
                'iconColor'   => '#B39A74',
                'textColor'   => '#FFFFFF',
        ) ) );

        $col_cta = kbl3_column( array( 'width' => 13 ), kbl3_button( array(
                'text'    => 'Get a Quote',
                'url'     => home_url( '/contact/' ),
                'variant' => 'gold',
                'size'    => 's',
                'align'   => 'right',
        ) ) );

        $main = kbl3_columns( array(
                'gap'    => 24,
                'vAlign' => 'center',
        ), $col_logo . "\n\n" . $col_nav . "\n\n" . $col_search . "\n\n" . $col_cta );

        return trim( $top . "\n\n" . $main );
}

/**
 * Layout "minimal" — one single main bar, no top bar: logo · nav · search
 * · language dropdown · CTA (16 / 48 / 12 / 12 / 12, 18px rhythm, nav
 * 14px with 14px gaps).
 */
function kbl3_header_seed_minimal(): string {
        $muted  = 'rgba(255,255,255,0.72)';
        $bright = '#D3BC97';

        $col_logo = kbl3_column( array( 'width' => 16 ), kbl3_logo( array(
                'url'     => kbl3_chrome_logo_url(),
                'alt'     => 'Kabel Paints',
                'width'   => 150,
                'linkURL' => esc_url( home_url( '/' ) ),
        ) ) );

        $col_nav = kbl3_column( array( 'width' => 48 ), kbl3_nav_menu( array(
                'color'      => '#FFFFFF',
                'hoverColor' => $bright,
                'fontSize'   => 14,
                'fontSizeT'  => 12,
                'gap'        => 14,
                'padX'       => 2,
        ) ) );

        $col_search = kbl3_column( array( 'width' => 12 ), kbl3_search( array(
                'width'       => 130,
                'placeholder' => 'Search…',
                'inputBg'     => 'rgba(255,255,255,0.08)',
                'borderColor' => 'rgba(255,255,255,0.18)',
                'iconColor'   => '#B39A74',
                'textColor'   => '#FFFFFF',
                'hideT'       => true,
        ) ) );

        $col_lang = kbl3_column( array( 'width' => 12 ), kbl3_header_lang_seed( array(
                'color'       => $muted,
                'activeColor' => $bright,
                'fontSize'    => 13,
                'align'       => 'right',
        ) ) );

        $col_cta = kbl3_column( array( 'width' => 12 ), kbl3_button( array(
                'text'    => 'Get a Quote',
                'url'     => home_url( '/contact/' ),
                'variant' => 'gold',
                'size'    => 's',
                'align'   => 'right',
        ) ) );

        return trim( kbl3_columns( array(
                'gap'    => 18,
                'vAlign' => 'center',
        ), $col_logo . "\n\n" . $col_nav . "\n\n" . $col_search . "\n\n" . $col_lang . "\n\n" . $col_cta ) );
}

/**
 * Footer seed — four quarter columns (brand + address, products, company,
 * contact), a thin bronze separator and the centered copyright line.
 * Identical content to the theme's twin seed.
 */
function kbl3_site_chrome_seed_footer(): string {
        $email = 'kabelpaints@gmail.com';

        $address = sprintf(
                'Druento, Torino, Italy<br>Branch: Foshan, Guangdong, China<br>WhatsApp: <a href="%s">+86 18033205148</a>',
                esc_url( 'https://wa.me/8618033205148' )
        );

        $products = sprintf(
                '<a href="%s">All paint series</a><br><a href="%s">Velvet Texture</a><br><a href="%s">Lime Plaster</a><br><a href="%s">Stone Texture</a><br><a href="%s">Microcement</a>',
                esc_url( home_url( '/products/' ) ),
                esc_url( home_url( '/product-series/velvet-texture/' ) ),
                esc_url( home_url( '/product-series/lime-plaster/' ) ),
                esc_url( home_url( '/product-series/stone-texture/' ) ),
                esc_url( home_url( '/product-series/microcement/' ) )
        );

        $company = sprintf(
                '<a href="%s">About</a><br><a href="%s">Blog</a><br><a href="%s">Contact</a>',
                esc_url( home_url( '/about/' ) ),
                esc_url( home_url( '/blog/' ) ),
                esc_url( home_url( '/contact/' ) )
        );

        $col_brand = kbl3_column( array(
                'width'     => 25,
                'textColor' => '#FFFFFF',
        ), kbl3_heading( array(
                'level' => 5,
                'text'  => 'KABEL TECHNOLOGY',
        ) ) . "\n\n" . kbl3_text( array(
                'size' => 's',
                'text' => $address,
        ) ) );

        $col_products = kbl3_column( array(
                'width'     => 25,
                'textColor' => '#FFFFFF',
        ), kbl3_heading( array(
                'level'   => 6,
                'eyebrow' => true,
                'text'    => 'PRODUCTS',
        ) ) . "\n\n" . kbl3_text( array(
                'size' => 's',
                'text' => $products,
        ) ) );

        $col_company = kbl3_column( array(
                'width'     => 25,
                'textColor' => '#FFFFFF',
        ), kbl3_heading( array(
                'level'   => 6,
                'eyebrow' => true,
                'text'    => 'COMPANY',
        ) ) . "\n\n" . kbl3_text( array(
                'size' => 's',
                'text' => $company,
        ) ) );

        $col_contact = kbl3_column( array(
                'width'     => 25,
                'textColor' => '#FFFFFF',
        ), kbl3_heading( array(
                'level'   => 6,
                'eyebrow' => true,
                'text'    => 'CONTACT',
        ) ) . "\n\n" . kbl3_text( array(
                'size' => 's',
                'text' => sprintf( '<a href="mailto:%s">%s</a>', $email, $email ),
        ) ) );

        $columns = kbl3_columns( array(
                'gap'  => 32,
                'vGap' => 24,
        ), $col_brand . "\n\n" . $col_products . "\n\n" . $col_company . "\n\n" . $col_contact );

        $separator = kbl3_separator( array(
                'align' => 'center',
                'color' => '#B39A74',
        ) );

        $copyright = kbl3_text( array(
                'size'  => 's',
                'align' => 'center',
                'text'  => '© 2026 Kabel Technology. All rights reserved.',
        ) );

        return trim( $columns . "\n\n" . $separator . "\n\n" . $copyright );
}

/* ---------------------------------------------------------------------
 * Part pages lifecycle
 * ------------------------------------------------------------------- */

/**
 * Menu rescue for upgraded plugin-mode sites.
 *
 * Sites bootstrapped before v3.11 (on a block theme) never got the seed
 * "Primary Menu" — the old builder only created menus when the theme
 * registered classic locations. The seeded header's nav block would then
 * resolve nothing and render empty. When the site has NO menu at all and
 * the blueprint pages exist, rebuild the seed menu exactly like the
 * bootstrap does — ONCE per site lifetime (a deliberate later deletion of
 * every menu is respected, not resurrected).
 */
function kbl3_rescue_seed_menu(): void {
        if ( ! function_exists( 'kbl_build_nav_menus' ) || ! function_exists( 'kbl_bootstrap_terms' ) ) {
                return;
        }

        /* Once per site lifetime: the rescue exists for sites bootstrapped
         * before v3.11 (which never got the seed menu on block themes). A
         * deliberate "delete every menu" later must NOT be second-guessed by
         * an auto-resurrecting menu — that reads as a bug, not a rescue. */
        if ( get_option( 'kbl3_seed_menu_rescued' ) ) {
                return;
        }

        $menus = wp_get_nav_menus( array( 'orderby' => 'name' ) );
        if ( ! empty( $menus ) && ! is_wp_error( $menus ) ) {
                return; // The site has a menu — never touch it.
        }

        /* Blueprint page IDs (they exist on any bootstrapped site). */
        $ids = array();
        foreach ( array_keys( kbl_bootstrap_blueprint() ) as $slug ) {
                $page = get_page_by_path( $slug );
                if ( $page instanceof WP_Post && in_array( $page->post_status, array( 'publish', 'private' ), true ) ) {
                        $ids[ $slug ] = (int) $page->ID;
                }
        }
        if ( ! isset( $ids['home'] ) ) {
                return; // Not a Kabel-blueprint site.
        }

        update_option( 'kbl3_seed_menu_rescued', '1', false );
        kbl_build_nav_menus( $ids, kbl_bootstrap_terms() );
}

/**
 * Create the two site-part pages when they are missing (plugin mode only).
 *
 * Runs on admin_init and after the one-click bootstrap. Seeding is
 * admin-only: the block markup is written importer-style through $wpdb
 * because wp_insert_post()'s content filters (and KSES for accounts
 * without unfiltered_html) would corrupt the block comments.
 */
function kbl3_ensure_site_parts(): void {
        if ( kabel_blocks_theme_active() ) {
                return; // The theme owns the chrome (it has its own twin routine).
        }
        if ( ! current_user_can( 'unfiltered_html' ) ) {
                return; // Non-admin editors get the pages on the next admin visit.
        }

        /* Upgraded sites: make sure the nav block has a menu to resolve. */
        kbl3_rescue_seed_menu();

        global $wpdb;

        foreach ( kbl3_site_part_defs() as $which => $def ) {
                if ( kbl3_site_part( $which ) instanceof WP_Post ) {
                        continue;
                }

                /*
                 * Slug guard across ALL statuses (a raw query — post_status 'any'
                 * hides trashed pages). If anything else already owns the slug, leave
                 * it alone instead of spawning "kabel-header-2" duplicates forever.
                 */
                $owner = $wpdb->get_var( $wpdb->prepare(
                        "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = 'page' LIMIT 1",
                        $def['slug']
                ) );
                if ( $owner ) {
                        continue;
                }

                $post_id = wp_insert_post( array(
                        'post_type'    => 'page',
                        'post_status'  => 'private',
                        'post_title'   => $def['title'],
                        'post_name'    => $def['slug'],
                        'post_content' => '',
                ) );

                if ( ! is_int( $post_id ) || $post_id <= 0 ) {
                        continue;
                }

                $seed = kbl3_site_chrome_seed( $which );
                if ( '' !== $seed ) {
                        $wpdb->update(
                                $wpdb->posts,
                                array(
                                        'post_content'      => $seed,
                                        'post_modified'     => current_time( 'mysql' ),
                                        'post_modified_gmt' => current_time( 'mysql', true ),
                                ),
                                array( 'ID' => $post_id )
                        );
                        clean_post_cache( $post_id );

                        /* Remember the pristine header seed so the layout screen can
                         * tell "still the preset" from "customized in the editor". */
                        if ( 'header' === $which ) {
                                update_option( 'kabel_header_layout_seed_md5', md5( $seed ), false );
                        }

                        /* Seeded chrome — purge the page caches that never saw a
                         * save_post for this $wpdb write. */
                        if ( function_exists( 'kbl3_flush_after_site_parts_seed' ) ) {
                                kbl3_flush_after_site_parts_seed();
                        }
                }
        }
}
add_action( 'admin_init', 'kbl3_ensure_site_parts' );

/**
 * Apply a header layout: rewrite the Site Header page with the preset seed.
 *
 * POST fields: layout (key), _wpnonce. Same contract as the theme's twin
 * handler (importer-style $wpdb write, option keys shared with the theme so
 * the applied layout survives a Kabel theme ↔ plugin-mode switch).
 */
function kbl3_handle_apply_header_layout(): void {
        if ( kabel_blocks_theme_active() ) {
                wp_die( esc_html__( 'The Kabel theme is active — manage the header layout from Appearance → Header & Footer.', 'kabel-blocks' ) );
        }
        if ( ! current_user_can( 'unfiltered_html' ) ) {
                wp_die( esc_html__( 'You are not allowed to change the header layout.', 'kabel-blocks' ) );
        }
        check_admin_referer( 'kbl3_apply_header_layout' );

        $back = add_query_arg( 'page', 'kabel-site-chrome', admin_url( 'admin.php' ) );

        $key     = isset( $_POST['layout'] ) ? sanitize_key( wp_unslash( $_POST['layout'] ) ) : '';
        $layouts = kbl3_header_layouts();
        if ( ! isset( $layouts[ $key ] ) ) {
                wp_safe_redirect( add_query_arg( 'kabel-layout', 'invalid', $back ) );
                exit;
        }

        /* Pages are normally ensured by admin_init earlier in this request;
         * call it directly for safety (idempotent, admin-only). */
        kbl3_ensure_site_parts();

        $header = kbl3_site_part( 'header' );
        if ( ! $header instanceof WP_Post ) {
                wp_safe_redirect( add_query_arg( 'kabel-layout', 'failed', $back ) );
                exit;
        }

        $seed = kbl3_site_part_seed_header( $key );
        if ( '' === $seed ) {
                wp_safe_redirect( add_query_arg( 'kabel-layout', 'failed', $back ) );
                exit;
        }

        global $wpdb;
        $ok = $wpdb->update(
                $wpdb->posts,
                array(
                        'post_content'      => $seed,
                        'post_modified'     => current_time( 'mysql' ),
                        'post_modified_gmt' => current_time( 'mysql', true ),
                ),
                array( 'ID' => $header->ID )
        );
        if ( false === $ok ) {
                wp_safe_redirect( add_query_arg( 'kabel-layout', 'failed', $back ) );
                exit;
        }

        clean_post_cache( $header->ID );
        update_option( 'kabel_header_layout', $key, false );
        update_option( 'kabel_header_layout_seed_md5', md5( $seed ), false );

        /* The $wpdb write above bypasses save_post — page-cache plugins
         * (LiteSpeed on Hostinger, WPSC, W3TC, Rocket…) never learn the chrome
         * changed and keep serving the stale cached header to visitors. Flush
         * them now so the front end matches the editor. */
        if ( function_exists( 'kbl3_flush_after_layout_apply' ) ) {
                kbl3_flush_after_layout_apply();
        }

        wp_safe_redirect( add_query_arg( 'kabel-layout', 'applied', $back ) );
        exit;
}
add_action( 'admin_post_kbl3_apply_header_layout', 'kbl3_handle_apply_header_layout' );

/* ---------------------------------------------------------------------
 * Front-end rendering
 * ------------------------------------------------------------------- */

/**
 * Is the standalone chrome allowed to render on this site?
 *
 * Three gates, in order: the Kabel theme active (never — it owns the
 * chrome), the owner's explicit toggle (option kbl3_site_chrome), and the
 * safe default — ON for block themes on a site this plugin bootstrapped,
 * OFF everywhere else so existing installs are never hijacked.
 */
function kbl3_site_chrome_enabled(): bool {
        static $enabled = null;
        if ( null !== $enabled ) {
                return $enabled;
        }

        $enabled = false;
        if ( kabel_blocks_theme_active() ) {
                return $enabled; // The Kabel theme owns the chrome.
        }

        $opt = (string) get_option( 'kbl3_site_chrome', '' );
        if ( 'on' === $opt ) {
                $enabled = true;
                return $enabled;
        }
        if ( 'off' === $opt ) {
                return $enabled;
        }

        if ( ! function_exists( 'wp_is_block_theme' ) || ! wp_is_block_theme() ) {
                return $enabled;
        }
        $enabled = (bool) get_option( 'kabel_bootstrapped' );
        return $enabled;
}

/**
 * Which chrome area does this template part represent?
 *
 * Header/footer template parts announce themselves through the block
 * attributes — area first (the canonical marker), then slug heuristics
 * for themes that omit it, then the tagName as the last resort.
 *
 * @param array $attrs Template-part block attributes.
 * @return string|null 'header' | 'footer' | null.
 */
function kbl3_chrome_part_area( array $attrs ): ?string {
        $area = isset( $attrs['area'] ) ? strtolower( (string) $attrs['area'] ) : '';
        if ( 'header' === $area ) {
                return 'header';
        }
        if ( 'footer' === $area ) {
                return 'footer';
        }

        $slug = isset( $attrs['slug'] ) ? strtolower( (string) $attrs['slug'] ) : '';
        if ( '' === $slug ) {
                $tag = isset( $attrs['tagName'] ) ? strtolower( (string) $attrs['tagName'] ) : '';
                if ( 'header' === $tag ) {
                        return 'header';
                }
                if ( 'footer' === $tag ) {
                        return 'footer';
                }
                return null;
        }

        if ( 'header' === $slug || 0 === strpos( $slug, 'header' ) || in_array( $slug, array( 'masthead', 'site-header', 'top-bar' ), true ) ) {
                return 'header';
        }
        if ( 'footer' === $slug || 0 === strpos( $slug, 'footer' ) || in_array( $slug, array( 'colophon', 'site-footer', 'bottom-bar' ), true ) ) {
                return 'footer';
        }
        return null;
}

/**
 * The chrome wrapper for one part, ready to print.
 *
 * @param string $which 'header' or 'footer'.
 * @return string '' when the part is missing or empty.
 */
function kbl3_site_chrome_render( string $which ): string {
        $inner = kbl3_site_part_render( $which );
        if ( '' === $inner ) {
                return '';
        }

        /*
         * Logo portability: a header seeded while the Kabel theme was active
         * baked the THEME's logo URL into the block markup. If that theme is
         * gone the logo 404s — swap the URL for the plugin's identical copy
         * (both in the img src and the block-comment attributes).
         */
        $theme_logo = content_url( 'themes/kabel/assets/img/logo-kabel.svg' );
        if ( false !== strpos( $inner, $theme_logo ) ) {
                $inner = str_replace( $theme_logo, KABEL_BLOCKS_URI . 'assets/img/logo-kabel.svg', $inner );
        }

        if ( 'header' === $which ) {
                return '<header class="kbl3-site-header" data-kb-header>'
                        . '<div class="kbl3-chrome-inner">' . $inner . '</div>'
                        . '</header>';
        }

        return '<footer class="kbl3-site-footer">'
                . '<div class="kbl3-chrome-inner">' . $inner . '</div>'
                . '</footer>';
}

/**
 * Swap the active theme's header / footer template parts for the Kabel
 * chrome (block themes).
 *
 * Exactly one header and one footer render: the theme's part is replaced,
 * not wrapped. An empty Kabel part returns the theme's own content —
 * "clear the page to fall back to the theme header" keeps working.
 * Editor/REST/AJAX contexts are skipped: the Site Editor must keep
 * showing the theme's real parts.
 *
 * @param string $block_content The rendered template part.
 * @param array  $block         The parsed block.
 * @return string
 */
function kbl3_replace_theme_chrome_part( string $block_content, array $block ): string {
        if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || wp_doing_ajax() ) {
                return $block_content;
        }
        if ( 'core/template-part' !== ( $block['blockName'] ?? '' ) ) {
                return $block_content;
        }
        if ( ! kbl3_site_chrome_enabled() ) {
                return $block_content;
        }

        $which = kbl3_chrome_part_area( $block['attrs'] ?? array() );
        if ( null === $which ) {
                return $block_content;
        }

        $chrome = kbl3_site_chrome_render( $which );
        return '' !== $chrome ? $chrome : $block_content;
}
add_filter( 'render_block', 'kbl3_replace_theme_chrome_part', 10, 2 );

/**
 * Is the current request a site-part page view (the preview harness)?
 *
 * The preview template renders the part's own blocks — the chrome
 * injections must stay out of it or the header would double (chrome
 * header at the top, the same blocks again in the stage).
 */
function kbl3_is_site_part_view(): bool {
        if ( ! is_singular( 'page' ) ) {
                return false;
        }
        $post = get_queried_object();
        if ( ! $post instanceof WP_Post ) {
                return false;
        }
        foreach ( kbl3_site_part_defs() as $def ) {
                if ( $post->post_name === $def['slug'] ) {
                        return true;
                }
        }
        return false;
}

/**
 * Front-end @font-face for the chrome (plugin mode only).
 *
 * The Kabel look is Barlow — without the theme nobody loads it on the
 * front end (only the editor canvas had it), the browser fell back to
 * the system sans stack, and the wider glyphs wrapped the seven-item nav
 * onto a second line. Self-hosted copies ship with the plugin; they are
 * only enqueued when the standalone chrome actually renders.
 */
function kbl3_chrome_front_fonts(): void {
        if ( ! kbl3_site_chrome_enabled() ) {
                return;
        }

        $faces = '';
        foreach ( array(
                array( 'Barlow', 300, 'normal', 'barlow--v13-normal-300.woff2' ),
                array( 'Barlow', 400, 'normal', 'barlow--v13-normal-400.woff2' ),
                array( 'Barlow', 500, 'normal', 'barlow--v13-normal-500.woff2' ),
                array( 'Barlow', 600, 'normal', 'barlow--v13-normal-600.woff2' ),
                array( 'Barlow', 700, 'normal', 'barlow--v13-normal-700.woff2' ),
                array( 'Barlow', 400, 'italic', 'barlow--v13-italic-400.woff2' ),
                array( 'Oregano', 400, 'normal', 'oregano--v17-normal-400.woff2' ),
        ) as $font ) {
                $faces .= sprintf(
                        '@font-face{font-family:"%1$s";src:url(%2$s) format("woff2");font-weight:%3$d;font-style:%4$s;font-display:swap;}',
                        $font[0],
                        esc_url( KABEL_BLOCKS_URI . 'assets/fonts/' . $font[3] ),
                        $font[1],
                        $font[2]
                );
        }
        wp_add_inline_style( 'kabel-blocks-v3', $faces );
}
add_action( 'wp_enqueue_scripts', 'kbl3_chrome_front_fonts', 15 );

/**
 * Preload the two chrome-critical font weights.
 *
 * The header text (logo, nav, contact row) is above the fold on every page
 * and renders in Barlow 500/600; with font-display:swap the browser paints
 * the fallback first and re-lays-out when the woff2 arrives. Two <link
 * rel=preload> hints (~34 KB) let the browser fetch them in parallel with
 * the CSS instead of after it, removing the visible swap on the first
 * viewport. Only the weights the shipped header seeds actually use.
 */
function kbl3_chrome_preload_fonts(): void {
        if ( ! kbl3_site_chrome_enabled() || ! wp_style_is( 'kabel-blocks-v3', 'enqueued' ) ) {
                return;
        }
        foreach ( array( 'barlow--v13-normal-500.woff2', 'barlow--v13-normal-600.woff2' ) as $file ) {
                printf(
                        "<link rel=\"preload\" as=\"font\" type=\"font/woff2\" crossorigin href=\"%s\">\n",
                        esc_url( KABEL_BLOCKS_URI . 'assets/fonts/' . $file )
                );
        }
}
add_action( 'wp_head', 'kbl3_chrome_preload_fonts', 5 );

/**
 * Classic-theme injection: the header at wp_body_open.
 *
 * Classic themes print their own header markup from header.php — it cannot
 * be suppressed without output buffering, so this path is strictly opt-in
 * (the toggle on the Header & Footer screen) and the screen explains the
 * double-chrome risk before anyone flips it.
 */
function kbl3_inject_chrome_header(): void {
        if ( ! kbl3_site_chrome_enabled() || wp_is_block_theme() || kbl3_is_site_part_view() ) {
                return;
        }
        echo kbl3_site_chrome_render( 'header' );
}
add_action( 'wp_body_open', 'kbl3_inject_chrome_header' );

/**
 * Classic-theme injection: the footer at wp_footer.
 */
function kbl3_inject_chrome_footer(): void {
        if ( ! kbl3_site_chrome_enabled() || wp_is_block_theme() || kbl3_is_site_part_view() ) {
                return;
        }
        echo kbl3_site_chrome_render( 'footer' );
}
add_action( 'wp_footer', 'kbl3_inject_chrome_footer', 5 );

/* ---------------------------------------------------------------------
 * Chrome toggle
 * ------------------------------------------------------------------- */

/**
 * The chrome on/off toggle (Kabel → Header & Footer).
 *
 * POST fields: chrome ('on'|'off'), _wpnonce. Writes the explicit option
 * and flushes every page cache — the chrome swap is a whole-site change.
 */
function kbl3_handle_toggle_site_chrome(): void {
        if ( ! current_user_can( 'edit_pages' ) ) {
                wp_die( esc_html__( 'You are not allowed to change this setting.', 'kabel-blocks' ) );
        }
        check_admin_referer( 'kbl3_toggle_site_chrome' );

        $back = add_query_arg( 'page', 'kabel-site-chrome', admin_url( 'admin.php' ) );

        $value = isset( $_POST['chrome'] ) ? sanitize_key( wp_unslash( $_POST['chrome'] ) ) : '';
        if ( ! in_array( $value, array( 'on', 'off' ), true ) ) {
                wp_safe_redirect( add_query_arg( 'kabel-chrome', 'invalid', $back ) );
                exit;
        }

        update_option( 'kbl3_site_chrome', $value, false );

        if ( function_exists( 'kbl3_flush_page_caches' ) ) {
                kbl3_flush_page_caches( 'chrome_toggle' );
        }

        wp_safe_redirect( add_query_arg( 'kabel-chrome', 'saved', $back ) );
        exit;
}
add_action( 'admin_post_kbl3_toggle_site_chrome', 'kbl3_handle_toggle_site_chrome' );

/* ---------------------------------------------------------------------
 * Admin surface
 * ------------------------------------------------------------------- */

/**
 * Register Kabel → Header & Footer (plugin mode only — the theme's
 * Appearance → Header & Footer owns the topic when it is active).
 */
function kbl3_site_chrome_menu(): void {
        if ( kabel_blocks_theme_active() ) {
                return;
        }
        add_submenu_page(
                'kabel-layout-library',
                __( 'Header & Footer', 'kabel-blocks' ),
                __( 'Header & Footer', 'kabel-blocks' ),
                'edit_pages',
                'kabel-site-chrome',
                'kbl3_site_chrome_screen'
        );
}
add_action( 'admin_menu', 'kbl3_site_chrome_menu', 11 );

/**
 * Admin-bar shortcuts: "Edit Header" / "Edit Footer" root nodes (plugin
 * mode only; the theme registers the same node IDs when it is active).
 *
 * @param WP_Admin_Bar $admin_bar The admin bar.
 */
function kbl3_site_chrome_admin_bar( WP_Admin_Bar $admin_bar ): void {
        if ( kabel_blocks_theme_active() || ! kbl3_site_chrome_enabled() ) {
                return;
        }
        if ( ! current_user_can( 'edit_pages' ) ) {
                return;
        }

        $header = kbl3_site_part( 'header' );
        $footer = kbl3_site_part( 'footer' );
        if ( ! $header instanceof WP_Post || ! $footer instanceof WP_Post ) {
                return;
        }

        $admin_bar->add_node( array(
                'id'    => 'kabel-edit-header',
                'title' => __( 'Edit Header', 'kabel-blocks' ),
                'href'  => admin_url( 'post.php?post=' . $header->ID . '&action=edit' ),
        ) );

        $admin_bar->add_node( array(
                'id'    => 'kabel-edit-footer',
                'title' => __( 'Edit Footer', 'kabel-blocks' ),
                'href'  => admin_url( 'post.php?post=' . $footer->ID . '&action=edit' ),
        ) );
}
add_action( 'admin_bar_menu', 'kbl3_site_chrome_admin_bar', 80 );

/**
 * Wireframe preview for the "classic" layout card (top bar + main bar).
 * Same art as the theme's twin screen.
 */
function kbl3_header_preview_classic(): string {
        return <<<'SVG'
<svg viewBox="0 0 440 132" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Classic header layout preview: contact info and language links in a slim top bar above logo, menu, search and button">
        <rect width="440" height="132" fill="#0B0B0D"/>
        <g stroke="#B39A74" stroke-width="1.4" fill="none">
                <circle cx="24" cy="24" r="4.5"/>
                <circle cx="104" cy="24" r="4.5"/>
        </g>
        <g fill="#FFFFFF" opacity="0.38">
                <rect x="36" y="22" width="52" height="4" rx="2"/>
                <rect x="116" y="22" width="72" height="4" rx="2"/>
        </g>
        <text x="422" y="28" text-anchor="end" font-family="system-ui, sans-serif" font-size="10">
                <tspan fill="#D3BC97">EN</tspan><tspan fill="#FFFFFF" opacity="0.55"> · 中文 · IT</tspan>
        </text>
        <line x1="20" y1="46" x2="420" y2="46" stroke="#FFFFFF" stroke-opacity="0.10"/>
        <text x="20" y="93" font-family="system-ui, sans-serif" font-size="15" font-weight="700" letter-spacing="3" fill="#D3BC97">KABEL</text>
        <g>
                <rect x="112" y="85" width="24" height="5" rx="2.5" fill="#B39A74"/>
                <rect x="150" y="85" width="34" height="5" rx="2.5" fill="#FFFFFF" opacity="0.55"/>
                <rect x="198" y="85" width="24" height="5" rx="2.5" fill="#FFFFFF" opacity="0.55"/>
                <rect x="236" y="85" width="20" height="5" rx="2.5" fill="#FFFFFF" opacity="0.55"/>
                <rect x="268" y="85" width="30" height="5" rx="2.5" fill="#FFFFFF" opacity="0.55"/>
        </g>
        <g>
                <rect x="316" y="77" width="56" height="22" rx="11" stroke="#FFFFFF" stroke-opacity="0.30" fill="none"/>
                <circle cx="357" cy="85" r="3.2" stroke="#B39A74" stroke-width="1.2" fill="none"/>
                <line x1="359.4" y1="87.4" x2="362" y2="90" stroke="#B39A74" stroke-width="1.2"/>
        </g>
        <g>
                <rect x="384" y="77" width="36" height="22" rx="3" fill="#C9A96B"/>
                <rect x="391" y="86" width="22" height="4" rx="2" fill="#0B0B0D" opacity="0.4"/>
        </g>
</svg>
SVG;
}

/**
 * Wireframe preview for the "minimal" layout card (single bar, languages
 * on the right).
 */
function kbl3_header_preview_minimal(): string {
        return <<<'SVG'
<svg viewBox="0 0 440 84" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Minimal header layout preview: one bar with logo, menu, search, language links on the right and button">
        <rect width="440" height="84" fill="#0B0B0D"/>
        <text x="20" y="48" font-family="system-ui, sans-serif" font-size="15" font-weight="700" letter-spacing="3" fill="#D3BC97">KABEL</text>
        <g>
                <rect x="104" y="40" width="22" height="5" rx="2.5" fill="#B39A74"/>
                <rect x="140" y="40" width="30" height="5" rx="2.5" fill="#FFFFFF" opacity="0.55"/>
                <rect x="182" y="40" width="22" height="5" rx="2.5" fill="#FFFFFF" opacity="0.55"/>
                <rect x="216" y="40" width="18" height="5" rx="2.5" fill="#FFFFFF" opacity="0.55"/>
                <rect x="246" y="40" width="28" height="5" rx="2.5" fill="#FFFFFF" opacity="0.55"/>
        </g>
        <g>
                <rect x="292" y="31" width="48" height="22" rx="11" stroke="#FFFFFF" stroke-opacity="0.30" fill="none"/>
                <circle cx="328" cy="39" r="3" stroke="#B39A74" stroke-width="1.2" fill="none"/>
                <line x1="330.2" y1="41.2" x2="332.6" y2="43.6" stroke="#B39A74" stroke-width="1.2"/>
        </g>
        <text x="378" y="46" text-anchor="middle" font-family="system-ui, sans-serif" font-size="9">
                <tspan fill="#D3BC97">EN</tspan><tspan fill="#FFFFFF" opacity="0.55"> · 中文 · IT</tspan>
        </text>
        <g>
                <rect x="400" y="31" width="30" height="22" rx="3" fill="#C9A96B"/>
                <rect x="406" y="40" width="18" height="4" rx="2" fill="#0B0B0D" opacity="0.4"/>
        </g>
</svg>
SVG;
}

/**
 * The Kabel → Header & Footer screen: chrome toggle, header layout
 * presets, the two big edit buttons, and the menu pointer — the nav block
 * is menu-driven, which is exactly where plugin-mode owners got lost
 * ("deleted the menu but the page still shows it" was the default theme's
 * page-list fallback, not a menu at all).
 */
function kbl3_site_chrome_screen(): void {
        $header    = kbl3_site_part( 'header' );
        $footer    = kbl3_site_part( 'footer' );
        $layouts   = kbl3_header_layouts();
        $current   = kbl3_header_layout_current();
        $ready     = function_exists( 'kbl3_columns' );
        $enabled   = kbl3_site_chrome_enabled();
        $block     = function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();

        /* "Customized" detection: the header content no longer matches the
         * seed of the last applied layout (manual edits in the editor). */
        $seed_md5   = (string) get_option( 'kabel_header_layout_seed_md5', '' );
        $customized = $header instanceof WP_Post && '' !== $seed_md5
                && md5( trim( (string) $header->post_content ) ) !== $seed_md5;

        $flag   = isset( $_GET['kabel-layout'] ) ? sanitize_key( wp_unslash( $_GET['kabel-layout'] ) ) : '';
        $cflag  = isset( $_GET['kabel-chrome'] ) ? sanitize_key( wp_unslash( $_GET['kabel-chrome'] ) ) : '';

        $confirm_js = esc_js( __( 'Applying a layout replaces the current header content — any custom edits are lost. Continue?', 'kabel-blocks' ) );
        ?>
        <div class="wrap">
                <h1><?php esc_html_e( 'Header & Footer', 'kabel-blocks' ); ?></h1>

                <?php if ( 'applied' === $flag ) : ?>
                        <div class="notice notice-success is-dismissible">
                                <p><?php
                                        printf(
                                                esc_html__( 'Header layout applied — the site header now uses the %s layout.', 'kabel-blocks' ),
                                                '<strong>' . esc_html( $layouts[ kbl3_header_layout_current() ]['label'] ) . '</strong>'
                                        );
                                ?></p>
                                <p class="description"><?php esc_html_e( 'Page caches were flushed automatically. If the site still shows the old header, your host may keep its own cache — clear it from your hosting panel (e.g. Hostinger → Advanced → Cache Manager) and check again.', 'kabel-blocks' ); ?></p>
                        </div>
                <?php elseif ( 'failed' === $flag ) : ?>
                        <div class="notice notice-error is-dismissible">
                                <p><?php esc_html_e( 'The header layout could not be applied — the Kabel Blocks engine is not available. Reinstall the plugin and try again.', 'kabel-blocks' ); ?></p>
                        </div>
                <?php elseif ( 'invalid' === $flag ) : ?>
                        <div class="notice notice-error is-dismissible">
                                <p><?php esc_html_e( 'Unknown header layout.', 'kabel-blocks' ); ?></p>
                        </div>
                <?php endif; ?>

                <?php if ( 'saved' === $cflag ) : ?>
                        <div class="notice notice-success is-dismissible">
                                <p><?php esc_html_e( 'Site chrome setting saved — page caches were flushed.', 'kabel-blocks' ); ?></p>
                        </div>
                <?php elseif ( 'invalid' === $cflag ) : ?>
                        <div class="notice notice-error is-dismissible">
                                <p><?php esc_html_e( 'Unknown chrome setting.', 'kabel-blocks' ); ?></p>
                        </div>
                <?php endif; ?>

                <h2 class="kbl3-hf-sub"><?php esc_html_e( 'Site chrome', 'kabel-blocks' ); ?></h2>
                <p class="description"><?php esc_html_e( 'Use the editable Kabel header & footer on this site (instead of the active theme’s own).', 'kabel-blocks' ); ?></p>

                <?php if ( $block ) : ?>
                        <p>
                                <strong><?php echo $enabled ? esc_html__( 'Status: on — the Kabel header & footer replace the theme’s.', 'kabel-blocks' ) : esc_html__( 'Status: off — the theme’s own header & footer are shown.', 'kabel-blocks' ); ?></strong>
                        </p>
                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                                <input type="hidden" name="action" value="kbl3_toggle_site_chrome">
                                <input type="hidden" name="chrome" value="<?php echo $enabled ? 'off' : 'on'; ?>">
                                <?php wp_nonce_field( 'kbl3_toggle_site_chrome' ); ?>
                                <button type="submit" class="button <?php echo $enabled ? '' : 'button-primary'; ?>">
                                        <?php echo $enabled ? esc_html__( 'Turn off the Kabel chrome', 'kabel-blocks' ) : esc_html__( 'Turn on the Kabel chrome', 'kabel-blocks' ); ?>
                                </button>
                        </form>
                <?php else : ?>
                        <div class="notice notice-warning inline" style="max-width:860px">
                                <p><strong><?php esc_html_e( 'Classic theme detected.', 'kabel-blocks' ); ?></strong>
                                <?php esc_html_e( 'Your active theme is a classic theme — it prints its own header and footer from PHP, which cannot be replaced cleanly. With the chrome enabled the Kabel header renders at the very top and the Kabel footer at the very bottom, and the theme’s own chrome stays in between. Best results: use a block theme (e.g. Twenty Twenty-Five) or the Kabel theme.', 'kabel-blocks' ); ?></p>
                                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:8px">
                                        <input type="hidden" name="action" value="kbl3_toggle_site_chrome">
                                        <input type="hidden" name="chrome" value="<?php echo $enabled ? 'off' : 'on'; ?>">
                                        <?php wp_nonce_field( 'kbl3_toggle_site_chrome' ); ?>
                                        <button type="submit" class="button <?php echo $enabled ? '' : 'button-primary'; ?>">
                                                <?php echo $enabled ? esc_html__( 'Turn off the Kabel chrome', 'kabel-blocks' ) : esc_html__( 'Turn it on anyway', 'kabel-blocks' ); ?>
                                        </button>
                                </form>
                        </div>
                <?php endif; ?>

                <hr style="margin: 24px 0" />

                <h2 class="kbl3-hf-sub"><?php esc_html_e( 'Header layout', 'kabel-blocks' ); ?></h2>
                <p class="description"><?php esc_html_e( 'Pick a starting arrangement for the site header. Every element stays an editable block — after applying, fine-tune anything under Edit Header.', 'kabel-blocks' ); ?></p>

                <?php if ( ! $ready ) : ?>
                        <div class="notice notice-warning inline">
                                <p><?php esc_html_e( 'The Kabel Blocks engine is unavailable — reinstall the plugin to build header layouts.', 'kabel-blocks' ); ?></p>
                        </div>
                <?php endif; ?>

                <div class="kbl3-layouts">
                        <?php foreach ( $layouts as $key => $layout ) :
                                $is_current = $key === $current;
                                $preview    = $layout['preview'];
                        ?>
                                <div class="kbl3-layout-card<?php echo $is_current ? ' is-current' : ''; ?>">
                                        <div class="kbl3-layout-card__preview"><?php echo $preview(); ?></div>
                                        <div class="kbl3-layout-card__body">
                                                <h3 class="kbl3-layout-card__title">
                                                        <?php echo esc_html( $layout['label'] ); ?>
                                                        <?php if ( $is_current ) : ?>
                                                                <span class="kbl3-layout-card__badge"><?php esc_html_e( 'Current', 'kabel-blocks' ); ?></span>
                                                        <?php endif; ?>
                                                </h3>
                                                <p class="kbl3-layout-card__desc"><?php echo esc_html( $layout['desc'] ); ?></p>
                                                <div class="kbl3-layout-card__actions">
                                                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
                                                                onsubmit="<?php echo esc_attr( 'return window.confirm(' . "'" . $confirm_js . "'" . ');' ); ?>">
                                                                <input type="hidden" name="action" value="kbl3_apply_header_layout">
                                                                <input type="hidden" name="layout" value="<?php echo esc_attr( $key ); ?>">
                                                                <?php wp_nonce_field( 'kbl3_apply_header_layout' ); ?>
                                                                <?php if ( $is_current ) : ?>
                                                                        <button type="submit" class="button" <?php disabled( ! $ready ); ?>><?php esc_html_e( 'Re-apply this layout', 'kabel-blocks' ); ?></button>
                                                                <?php else : ?>
                                                                        <button type="submit" class="button button-primary" <?php disabled( ! $ready ); ?>><?php esc_html_e( 'Apply this layout', 'kabel-blocks' ); ?></button>
                                                                <?php endif; ?>
                                                        </form>
                                                        <?php if ( $is_current && $header instanceof WP_Post ) : ?>
                                                                <a class="button button-link" href="<?php echo esc_url( admin_url( 'post.php?post=' . $header->ID . '&action=edit' ) ); ?>"><?php esc_html_e( 'Edit Header', 'kabel-blocks' ); ?></a>
                                                        <?php endif; ?>
                                                </div>
                                        </div>
                                </div>
                        <?php endforeach; ?>
                </div>

                <?php if ( $customized ) : ?>
                        <p class="description" style="max-width:640px">
                                <strong><?php esc_html_e( 'Note:', 'kabel-blocks' ); ?></strong>
                                <?php esc_html_e( 'the header has been customized since this layout was applied — applying any layout replaces those edits.', 'kabel-blocks' ); ?>
                        </p>
                <?php endif; ?>

                <hr style="margin: 24px 0" />

                <p><?php esc_html_e( 'Edit these two private pages with the block editor to change the site-wide header and footer.', 'kabel-blocks' ); ?></p>
                <p><?php esc_html_e( 'The pages stay private — they never appear in your navigation or search results, and visitors cannot open them.', 'kabel-blocks' ); ?></p>
                <p>
                        <?php
                        printf(
                                /* translators: %s: URL of theMenus screen. */
                                esc_html__( 'The menu block in the header follows your navigation menu — manage its items under %s (the seeded “Primary Menu”).', 'kabel-blocks' ),
                                '<a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">' . esc_html__( 'Appearance → Menus', 'kabel-blocks' ) . '</a>'
                        );
                        ?>
                </p>

                <?php if ( $header instanceof WP_Post && $footer instanceof WP_Post ) : ?>
                        <p style="margin-top: 2em;">
                                <a class="button button-primary button-hero" href="<?php echo esc_url( admin_url( 'post.php?post=' . $header->ID . '&action=edit' ) ); ?>"><?php esc_html_e( 'Edit Header', 'kabel-blocks' ); ?></a>
                                <a class="button button-primary button-hero" href="<?php echo esc_url( admin_url( 'post.php?post=' . $footer->ID . '&action=edit' ) ); ?>"><?php esc_html_e( 'Edit Footer', 'kabel-blocks' ); ?></a>
                        </p>
                <?php else : ?>
                        <div class="notice notice-info inline" style="margin-top: 1em;">
                                <p><?php esc_html_e( 'The pages do not exist yet — they are created automatically the first time an administrator opens the admin area.', 'kabel-blocks' ); ?></p>
                        </div>
                <?php endif; ?>
        </div>

        <style>
                .kbl3-hf-sub { margin-top: 8px; }
                .kbl3-layouts {
                        display: grid;
                        grid-template-columns: repeat(auto-fill, minmax(320px, 400px));
                        gap: 16px;
                        max-width: 860px;
                        margin: 16px 0 12px;
                }
                .kbl3-layout-card {
                        display: flex; flex-direction: column;
                        border: 1px solid #dcdcde; border-radius: 6px;
                        background: #fff; overflow: hidden;
                }
                .kbl3-layout-card.is-current { border-color: #B39A74; box-shadow: 0 0 0 1px #B39A74; }
                .kbl3-layout-card__preview { background: #0B0B0D; padding: 14px 14px 10px; }
                .kbl3-layout-card__preview svg { display: block; width: 100%; height: auto; }
                .kbl3-layout-card__body { padding: 12px 14px 14px; display: flex; flex-direction: column; gap: 8px; flex: 1; }
                .kbl3-layout-card__title { display: flex; align-items: center; gap: 8px; margin: 0; font-size: 14px; }
                .kbl3-layout-card__badge {
                        font-size: 11px; font-weight: 600; line-height: 1.6;
                        padding: 1px 9px; border-radius: 999px;
                        background: #B39A74; color: #0B0B0D;
                        text-transform: uppercase; letter-spacing: 0.04em;
                }
                .kbl3-layout-card__desc { margin: 0; color: #50575e; font-size: 13px; }
                .kbl3-layout-card__actions { margin-top: auto; display: flex; align-items: center; gap: 4px; }
        </style>
        <?php
}

/* ---------------------------------------------------------------------
 * Part-page viewing experience
 * ------------------------------------------------------------------- */

/**
 * Site-part pages render through their own preview template.
 *
 * Rendered as normal pages they would show the site chrome ABOVE the same
 * blocks as the page body — every element doubled. The dedicated template
 * shows the part once in a neutral harness (plugin copy, active whenever
 * the theme's own is not — template_include priority 20 vs the theme's 20:
 * both run, last one wins; the theme checks the same slugs and points at
 * its richer template, so only one actually swaps the path).
 *
 * @param string $template Resolved template path.
 * @return string
 */
function kbl3_site_part_preview_template( string $template ): string {
        if ( kabel_blocks_theme_active() ) {
                return $template; // The theme's own preview template takes over.
        }
        if ( ! is_singular( 'page' ) || is_embed() || is_feed() ) {
                return $template;
        }

        $post = get_queried_object();
        if ( ! $post instanceof WP_Post ) {
                return $template;
        }

        foreach ( kbl3_site_part_defs() as $def ) {
                if ( $post->post_name === $def['slug'] ) {
                        $preview = KABEL_BLOCKS_DIR . 'template-parts/site-part-preview.php';
                        return is_readable( $preview ) ? $preview : $template;
                }
        }

        return $template;
}
add_filter( 'template_include', 'kbl3_site_part_preview_template', 25 );

/**
 * Context notice on the site-part editor screens.
 *
 * Someone opening "Site Header" from the Pages list should immediately
 * understand the blast radius of their edits. Two channels:
 *
 *   - edit_form_after_title for the classic editor fallback, and
 *   - the block editor's own notices store — since WP 6.x post.php renders
 *     the classic form area inside <div id="metaboxes" class="hidden">,
 *     so classic admin notices NEVER become visible in the block editor
 *     (the same trap the theme's engine warning documents).
 */
function kbl3_site_part_editor_notice(): void {
        if ( kabel_blocks_theme_active() ) {
                return; // The theme prints its own twin notice.
        }
        $which = kbl3_site_part_being_edited();
        if ( null === $which ) {
                return;
        }
        printf(
                '<div class="notice notice-info inline"><p>%s</p></div>',
                'header' === $which
                        ? esc_html__( 'Changes here apply to the site-wide header.', 'kabel-blocks' )
                        : esc_html__( 'Changes here apply to the site-wide footer.', 'kabel-blocks' )
        );
}
add_action( 'edit_form_after_title', 'kbl3_site_part_editor_notice' );

/**
 * Which site part (if any) is being edited on the current admin screen?
 *
 * @return string|null 'header' | 'footer' | null.
 */
function kbl3_site_part_being_edited(): ?string {
        $post = get_post();
        if ( ! $post instanceof WP_Post || 'page' !== $post->post_type ) {
                return null;
        }
        foreach ( kbl3_site_part_defs() as $which => $def ) {
                $part = kbl3_site_part( $which );
                if ( $part instanceof WP_Post && (int) $part->ID === (int) $post->ID ) {
                        return $which;
                }
        }
        return null;
}

/**
 * The context notice through the BLOCK EDITOR's notice system.
 *
 * wp-admin/js/common.js relocates every div.notice to "after the first
 * .wrap h1" — on post.php that h1 lives inside the hidden fallback, so
 * classic notices never show. The Gutenberg notices store renders in the
 * editor's own notice area (top of the canvas), exactly where the owner
 * is looking.
 */
function kbl3_site_part_editor_notice_assets(): void {
        if ( kabel_blocks_theme_active() ) {
                return;
        }
        if ( ! current_user_can( 'edit_pages' ) ) {
                return;
        }
        $which = kbl3_site_part_being_edited();
        if ( null === $which ) {
                return;
        }

        $js = sprintf(
                "wp.data.dispatch('core/notices').createNotice('info', %s, { id: 'kbl3-part-scope', isDismissible: false });",
                wp_json_encode(
                        'header' === $which
                                ? __( 'Changes here apply to the site-wide header.', 'kabel-blocks' )
                                : __( 'Changes here apply to the site-wide footer.', 'kabel-blocks' ),
                        JSON_HEX_TAG | JSON_HEX_AMP
                )
        );

        wp_register_script( 'kbl3-part-scope', false, array( 'wp-data' ), KABEL_BLOCKS_VERSION, true );
        wp_enqueue_script( 'kbl3-part-scope' );
        wp_add_inline_script( 'kbl3-part-scope', $js );
}
add_action( 'enqueue_block_editor_assets', 'kbl3_site_part_editor_notice_assets', 15 );
