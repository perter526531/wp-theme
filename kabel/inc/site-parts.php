<?php
/**
 * Editable site-wide header & footer — "site parts" as private block pages.
 *
 * The owner complained the classic header/footer were frozen PHP. This module
 * hosts them as two PRIVATE pages (post_type=page, post_status=private):
 *
 *   kabel-header  "Site Header"  → renders inside <header class="site-header">
 *   kabel-footer  "Site Footer"  → renders inside <footer class="site-footer">
 *
 * Private pages are invisible to the front end (direct URLs 404 for visitors,
 * no search / nav / query leakage) but fully editable with the block editor,
 * so a non-technical owner can restyle the chrome visually. header.php and
 * footer.php fall back to the built-in design whenever a part is missing or
 * empty — deleting the content restores the original header/footer.
 *
 * Seed markup is generated through the Kabel Blocks emitter twins (kbl3_*),
 * the byte-exact PHP mirrors of the blocks' JS save() output, so the editor
 * opens the seeds without a single block-validation warning. When neither the
 * plugin nor the bundled render fallback loaded the emitter, the seeds are
 * skipped (empty pages) rather than risking invalid markup.
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

/**
 * The two site parts: key → slug + page title.
 *
 * @return array<string, array{slug:string, title:string}>
 */
function kabel_site_part_defs(): array {
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
 * The lookup is slug-exact and private-only; get_posts() defaults to
 * suppress_filters, so front-end renders are immune to plugins that hide
 * private posts from anonymous queries.
 *
 * @param string $which 'header' or 'footer'.
 * @return WP_Post|null
 */
function kabel_site_part( string $which ) {
        global $kabel_site_parts;

        $defs = kabel_site_part_defs();
        if ( ! isset( $defs[ $which ] ) ) {
                return null;
        }

        if ( ! is_array( $kabel_site_parts ) ) {
                $kabel_site_parts = array();
        }
        if ( array_key_exists( $which, $kabel_site_parts ) ) {
                return $kabel_site_parts[ $which ];
        }

        $posts = get_posts( array(
                'name'                   => $defs[ $which ]['slug'],
                'post_type'              => 'page',
                'post_status'            => 'private',
                'posts_per_page'         => 1,
                'no_found_rows'          => true,
                'update_post_term_cache' => false,
        ) );

        $kabel_site_parts[ $which ] = $posts[0] ?? null;
        return $kabel_site_parts[ $which ];
}

/**
 * Render one site part through the full the_content filter chain.
 *
 * do_blocks (priority 9 on the_content) parses the block comments and renders
 * the saved markup, so no global post setup is needed. Two chain side effects
 * are contained: do_blocks removes wpautop when it sees blocks — restored
 * afterwards so classic-content pages rendered later keep their paragraphs —
 * and prepend_attachment (which would inline the main query's attachment into
 * the header) is parked for the duration of the call.
 *
 * @param string $which 'header' or 'footer'.
 * @return string '' when the part is missing or empty.
 */
function kabel_site_part_render( string $which ): string {
        $post = kabel_site_part( $which );
        if ( ! $post instanceof WP_Post ) {
                return '';
        }

        $content = trim( (string) $post->post_content );
        if ( '' === $content ) {
                return '';
        }

        /*
         * Snapshot the two the_content side effects we must contain:
         * prepend_attachment would inline the main query's attachment into the
         * header/footer, and do_blocks strips wpautop once it sees blocks —
         * classic-content pages rendered later on the same request would lose
         * their paragraphs. Both are restored exactly as they were found.
         */
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

/**
 * Make sure the kbl3_* emitter twins are loaded.
 *
 * True when the real plugin booted (its emitter loads at after_setup_theme 5)
 * or when the bundled copy can be pulled in safely. The bundled path covers
 * "safe mode" — the theme's render fallback (inc/blocks-fallback.php) loads
 * only the engine, not the emitter — and plugin-less installs.
 */
function kabel_site_part_emitter_ready(): bool {
        if ( function_exists( 'kbl3_columns' ) ) {
                return true;
        }

        if ( ! defined( 'KABEL_BLOCKS_DIR' ) ) {
                define( 'KABEL_BLOCKS_DIR', KABEL_DIR . '/bundled/kabel-blocks/' );
        }

        /*
         * Pre-flight every file the require can pull in (emitter.php itself plus
         * its icons dependency). A require of a missing file is an uncatchable
         * compile error — the pre-check is the real shield (same doctrine as
         * inc/blocks-fallback.php).
         */
        $needed = array(
                KABEL_BLOCKS_DIR . 'inc/emitter.php',
                KABEL_BLOCKS_DIR . 'inc/icons.php',
        );
        foreach ( $needed as $file ) {
                if ( ! is_file( $file ) || ! is_readable( $file ) || 0 === filesize( $file ) ) {
                        return false;
                }
        }

        try {
                require_once KABEL_BLOCKS_DIR . 'inc/emitter.php';
        } catch ( \Throwable $e ) {
                return false;
        }

        return function_exists( 'kbl3_columns' );
}

/**
 * Seed markup for a site part ('' when the emitter twins are unavailable).
 *
 * Attribute keys are the emitter's own (gap/vGap/width/textColor/level/
 * eyebrow/align/size/variant/url…) — passing anything else would break the
 * JS save() byte-twin contract and trigger block validation warnings.
 *
 * @param string $which 'header' or 'footer'.
 */
function kabel_site_part_seed( string $which ): string {
        if ( ! kabel_site_part_emitter_ready() ) {
                return '';
        }
        return 'header' === $which ? kabel_site_part_seed_header( kabel_header_layout_current() ) : kabel_site_part_seed_footer();
}

/**
 * The available header layouts — preset arrangements of the header element
 * blocks, switchable from Appearance → Header & Footer (Blocksy-style
 * starter layouts). Each entry pairs a seed callback (emitter-twin markup
 * that opens in the editor without validation warnings) with a wireframe
 * preview drawn on the admin screen.
 *
 * @return array<string, array{label:string, desc:string, seed:string, preview:string}>
 */
function kabel_header_layouts(): array {
        return array(
                'classic' => array(
                        'label'   => __( 'Classic — top bar', 'kabel' ),
                        'desc'    => __( 'Slim top bar with contact info and language links above the main bar: logo · menu · search · button.', 'kabel' ),
                        'seed'    => 'kabel_header_seed_classic',
                        'preview' => 'kabel_header_preview_classic',
                ),
                'minimal' => array(
                        'label'   => __( 'Minimal — single bar', 'kabel' ),
                        'desc'    => __( 'One clean bar without a top bar: logo · menu · search · language links on the right · button.', 'kabel' ),
                        'seed'    => 'kabel_header_seed_minimal',
                        'preview' => 'kabel_header_preview_minimal',
                ),
        );
}

/**
 * The header layout recorded as active ('classic' until an owner applies
 * another one). The option only tracks the last applied preset — manual
 * edits in the block editor are the content's own state, detected through
 * the stored seed hash instead.
 */
function kabel_header_layout_current(): string {
        $key     = (string) get_option( 'kabel_header_layout', 'classic' );
        $layouts = kabel_header_layouts();
        return isset( $layouts[ $key ] ) ? $key : 'classic';
}

/**
 * Language-switcher seed shared by every header layout.
 *
 * GTranslate-aware: when the GTranslate plugin AND the Kabel Blocks engine
 * (whose inc/gtranslate.php carries the bridge) are both active, the seed
 * starts in GTranslate mode with real language codes — clicking the links
 * machine-translates the site out of the box. Otherwise the plain custom
 * link mode is seeded.
 *
 * @param array $args Attributes forwarded to the emitter (color/activeColor/fontSize/align…).
 */
function kabel_header_lang_seed( array $args ): string {
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
function kabel_site_part_seed_header( string $layout = '' ): string {
        $layouts = kabel_header_layouts();
        if ( ! isset( $layouts[ $layout ] ) ) {
                $layout = kabel_header_layout_current();
        }
        $seed = $layouts[ $layout ]['seed'];
        return $seed();
}

/**
 * Layout "classic" — Blocksy-style element header across two rows:
 *
 *   Row 1 (slim top bar): contact info left · language switcher right
 *   Row 2 (main bar):     logo · nav menu (live WP menu) · search · CTA
 *
 * Every element is an independent block with its own color / typography /
 * spacing controls — the layout rows only carry position and gaps, exactly
 * like a Blocksy header row.
 */
function kabel_header_seed_classic(): string {
        $muted  = 'rgba(255,255,255,0.72)';
        $bright = '#D3BC97';

        /* Row 1 — top bar. */
        $top_left = kbl3_column( array( 'width' => 50 ), kbl3_contact_info( array(
                'color'     => $muted,
                'iconColor' => '#B39A74',
                'fontSize'  => 12,
                'gap'       => 22,
        ) ) );

        $top_right = kbl3_column( array( 'width' => 50 ), kabel_header_lang_seed( array(
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
                'url'     => esc_url( get_template_directory_uri() . '/assets/img/logo-kabel.svg' ),
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
 * Layout "minimal" — one single main bar, no top bar:
 *
 *   logo · nav menu (live WP menu) · search · language dropdown · CTA
 *
 * The language switcher moves from the top bar down to the right side of
 * the main row (right-aligned, just before the CTA). The dropdown button
 * (globe + current language) needs one narrow column; the nav gets the
 * reclaimed width for the seven-item demo menu. Column shares
 * 16 + 48 + 12 + 12 + 12 = 100 with a tight 18px rhythm so five elements
 * fit one row at the 1290px container; the nav runs 14px with 14px gaps
 * and drops to 12px on tablets (fontSizeT) to keep the row intact
 * through the 1080px breakpoint.
 */
function kabel_header_seed_minimal(): string {
        $muted  = 'rgba(255,255,255,0.72)';
        $bright = '#D3BC97';

        $col_logo = kbl3_column( array( 'width' => 16 ), kbl3_logo( array(
                'url'     => esc_url( get_template_directory_uri() . '/assets/img/logo-kabel.svg' ),
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

        $col_lang = kbl3_column( array( 'width' => 12 ), kabel_header_lang_seed( array(
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
 * contact), then a thin bronze separator and the centered copyright line.
 */
function kabel_site_part_seed_footer(): string {
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

/**
 * Create the two site-part pages when they are missing.
 *
 * Runs on admin_init (plus once right after a theme switch). Seeding is
 * admin-only: the block markup is written importer-style through $wpdb
 * because wp_insert_post()'s content filters (and KSES for accounts without
 * unfiltered_html) would corrupt the block comments. The page itself is
 * inserted empty and filled afterwards, exactly like the Kabel Blocks
 * bootstrap does for its own seeded pages.
 */
function kabel_ensure_site_parts(): void {
        if ( ! current_user_can( 'unfiltered_html' ) ) {
                return; // Non-admin editors get the pages on the next admin visit.
        }

        global $wpdb;

        foreach ( kabel_site_part_defs() as $which => $def ) {
                if ( kabel_site_part( $which ) instanceof WP_Post ) {
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

                $seed = kabel_site_part_seed( $which );
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

                        /* Remember the pristine header seed so the layout
                         * screen can tell "still the preset" from
                         * "customized in the editor" later. */
                        if ( 'header' === $which ) {
                                update_option( 'kabel_header_layout_seed_md5', md5( $seed ), false );
                        }

                        /* Seeded chrome — purge the page caches that never
                         * saw a save_post for this $wpdb write. */
                        if ( function_exists( 'kabel_flush_after_site_parts_seed' ) ) {
                                kabel_flush_after_site_parts_seed();
                        }
                }

                // Refresh the per-request cache with the fresh page.
                $GLOBALS['kabel_site_parts'][ $which ] = get_post( $post_id );
        }
}
add_action( 'admin_init', 'kabel_ensure_site_parts' );
add_action( 'after_switch_theme', 'kabel_ensure_site_parts' );

/**
 * Apply a header layout: rewrite the Site Header page with the preset seed.
 *
 * POST fields: layout (key), _wpnonce. The write is importer-style through
 * $wpdb (block comments would be mangled by wp_insert_post()'s content
 * filters / KSES), exactly like the initial seeding. Applying overwrites
 * whatever the header currently holds — the screen warns before the POST
 * leaves, and the pristine-seed hash is refreshed so "customized" tracking
 * restarts from the new layout.
 */
function kabel_handle_apply_header_layout(): void {
        if ( ! current_user_can( 'unfiltered_html' ) ) {
                wp_die( esc_html__( 'You are not allowed to change the header layout.', 'kabel' ) );
        }
        check_admin_referer( 'kabel_apply_header_layout' );

        $back = add_query_arg( 'page', 'kabel-header-footer', admin_url( 'themes.php' ) );

        $key = isset( $_POST['layout'] ) ? sanitize_key( wp_unslash( $_POST['layout'] ) ) : '';
        $layouts = kabel_header_layouts();
        if ( ! isset( $layouts[ $key ] ) ) {
                wp_safe_redirect( add_query_arg( 'kabel-layout', 'invalid', $back ) );
                exit;
        }

        /* Pages are normally ensured by admin_init earlier in this request;
         * call it directly for safety (idempotent, admin-only). */
        kabel_ensure_site_parts();

        $header = kabel_site_part( 'header' );
        if ( ! $header instanceof WP_Post ) {
                wp_safe_redirect( add_query_arg( 'kabel-layout', 'failed', $back ) );
                exit;
        }

        $seed = kabel_site_part_seed_header( $key );
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
         * (LiteSpeed on Hostinger, WPSC, W3TC, Rocket…) never learn the
         * chrome changed and keep serving the stale cached header to
         * visitors. Flush them now so the front end matches the editor. */
        if ( function_exists( 'kabel_flush_after_layout_apply' ) ) {
                kabel_flush_after_layout_apply();
        }

        wp_safe_redirect( add_query_arg( 'kabel-layout', 'applied', $back ) );
        exit;
}
add_action( 'admin_post_kabel_apply_header_layout', 'kabel_handle_apply_header_layout' );

/**
 * Admin-bar shortcuts: "Edit Header" / "Edit Footer" root nodes.
 *
 * @param WP_Admin_Bar $admin_bar The admin bar.
 */
function kabel_site_parts_admin_bar( WP_Admin_Bar $admin_bar ): void {
        if ( ! current_user_can( 'edit_pages' ) ) {
                return;
        }

        $header = kabel_site_part( 'header' );
        $footer = kabel_site_part( 'footer' );
        if ( ! $header instanceof WP_Post || ! $footer instanceof WP_Post ) {
                return;
        }

        $admin_bar->add_node( array(
                'id'    => 'kabel-edit-header',
                'title' => __( 'Edit Header', 'kabel' ),
                'href'  => admin_url( 'post.php?post=' . $header->ID . '&action=edit' ),
        ) );

        $admin_bar->add_node( array(
                'id'    => 'kabel-edit-footer',
                'title' => __( 'Edit Footer', 'kabel' ),
                'href'  => admin_url( 'post.php?post=' . $footer->ID . '&action=edit' ),
        ) );
}
add_action( 'admin_bar_menu', 'kabel_site_parts_admin_bar', 80 );

/**
 * Site-part pages render through their own preview template.
 *
 * "Site Header" / "Site Footer" are private pages holding the part blocks.
 * Rendered as normal pages they showed the site header ABOVE the same blocks
 * as the page body — every element doubled (two nav menus, two contact bars).
 * The dedicated template shows the part once in a neutral harness instead.
 *
 * @param string $template Resolved template path.
 * @return string
 */
function kabel_site_part_preview_template( string $template ): string {
        if ( ! is_singular( 'page' ) || is_embed() || is_feed() ) {
                return $template;
        }

        $post = get_queried_object();
        if ( ! $post instanceof WP_Post ) {
                return $template;
        }

        foreach ( kabel_site_part_defs() as $def ) {
                if ( $post->post_name === $def['slug'] ) {
                        $preview = get_template_directory() . '/template-parts/site-part-preview.php';
                        return is_readable( $preview ) ? $preview : $template;
                }
        }

        return $template;
}
add_filter( 'template_include', 'kabel_site_part_preview_template', 20 );

/**
 * Outdated-engine warning on the site-part edit screens.
 *
 * Editing "Site Header" / "Site Footer" with an old (or missing) Kabel
 * Blocks engine leaves every kbl/* element unregistered — the canvas comes
 * up empty and WP warns "Your site doesn't include support for the …
 * block". This notice puts the one-click update right where the owner is
 * looking, instead of hoping they find the Setup page first.
 */
function kabel_site_parts_engine_notice(): void {
        $data = kabel_site_parts_engine_notice_data();
        if ( null === $data ) {
                return;
        }
        ?>
        <div class="notice notice-warning">
                <p>
                        <strong><?php echo esc_html( $data['label'] ); ?></strong>
                        <a class="button button-primary" style="margin-inline-start:8px;vertical-align:middle" href="<?php echo esc_url( $data['url'] ); ?>">
                                <?php echo $data['version'] ? esc_html__( 'Update the block engine now', 'kabel' ) : esc_html__( 'Install & activate now', 'kabel' ); ?>
                        </a>
                </p>
        </div>
        <?php
}
add_action( 'admin_notices', 'kabel_site_parts_engine_notice' );

/**
 * The same engine warning, delivered through the block editor's own notice
 * system instead of an admin_notices div.
 *
 * Why: wp-admin/js/common.js relocates every div.notice to "after the first
 * .wrap h1" — on post.php that h1 lives inside the HIDDEN .block-editor-no-js
 * fallback, so classic admin notices never become visible on the editor
 * screen. The Gutenberg notices store renders in the editor's own notice
 * area (top of the canvas), which is exactly where the owner is looking.
 */
function kabel_site_parts_engine_notice_assets(): void {
        $data = kabel_site_parts_engine_notice_data();
        if ( null === $data ) {
                return;
        }

        $handle = 'kabel-hf-engine-notice';
        wp_register_script( $handle, false, array( 'wp-data' ), (string) KABEL_VERSION, true );
        wp_enqueue_script( $handle );

        /*
         * JSON_HEX_TAG|JSON_HEX_AMP: wp_get_inline_script_tag() runs the code
         * through WP_HTML_Tag_Processor::set_modifiable_text(), which encodes
         * raw "&" as "&amp;" — but script content is raw text in HTML, so the
         * browser never decodes it back. Emitting \u0026 keeps the JS valid
         * and decodes to a plain "&" at runtime.
         */
        $flags = JSON_HEX_TAG | JSON_HEX_AMP;
        $label = wp_json_encode( $data['label'], $flags );
        $url   = wp_json_encode( $data['url'], $flags );
        $btn   = wp_json_encode( $data['version'] ? __( 'Update the block engine now', 'kabel' ) : __( 'Install & activate now', 'kabel' ), $flags );
        $js    = "( function () {
                window.wpKabelHfEngineNotice = window.wpKabelHfEngineNotice || function () {
                        if ( ! window.wp || ! window.wp.data || ! window.wp.data.dispatch ) {
                                return false;
                        }
                        var notices = window.wp.data.dispatch( 'core/notices' );
                        if ( ! notices || ! notices.createNotice ) {
                                return false;
                        }
                        /* Stable id: a re-run (e.g. both enqueue passes) replaces
                         * the notice instead of stacking a duplicate. */
                        notices.createNotice( 'warning', {$label}, {
                                id: 'kabel-hf-engine-notice',
                                isDismissible: false,
                                actions: [ { label: {$btn}, url: {$url} } ]
                        } );
                        return true;
                };
                if ( ! window.wpKabelHfEngineNotice() ) {
                        window.setTimeout( window.wpKabelHfEngineNotice, 500 );
                }
        } )();";
        wp_add_inline_script( $handle, $js );
}
add_action( 'enqueue_block_editor_assets', 'kabel_site_parts_engine_notice_assets' );

/**
 * Shared state for the engine warning on the site-part edit screens.
 *
 * @return array{label:string,url:string,version:string}|null Null when the
 *                                                            screen/state does not apply.
 */
function kabel_site_parts_engine_notice_data(): ?array {
        if ( ! function_exists( 'get_current_screen' ) ) {
                return null;
        }
        $screen = get_current_screen();
        if ( ! $screen || 'page' !== $screen->post_type || 'post' !== $screen->base ) {
                return null;
        }
        $post = get_post();
        if ( ! $post instanceof WP_Post || ! in_array( $post->post_name, array( 'kabel-header', 'kabel-footer' ), true ) ) {
                return null;
        }
        if ( ! current_user_can( 'install_plugins' ) ) {
                return null;
        }

        if (
                function_exists( 'kabel_blocks_plugin_is_current' ) && kabel_blocks_plugin_is_current()
                && function_exists( 'kabel_blocks_plugin_is_latest' ) && kabel_blocks_plugin_is_latest()
        ) {
                return null;
        }

        $defs = kabel_site_part_defs();
        $key  = 'kabel-header' === $post->post_name ? 'header' : 'footer';
        $version = function_exists( 'kabel_blocks_plugin_version' ) ? kabel_blocks_plugin_version() : '';
        /*
         * Raw "&" URL — NOT wp_nonce_url(), which esc_html()s the query string
         * into "&amp;" for direct href="" use. This value also feeds the editor
         * notice script (JS context), where entities would never decode.
         */
        $url = add_query_arg(
                array(
                        'action'   => 'kabel_install_bundled_blocks',
                        '_wpnonce' => wp_create_nonce( 'kabel_install_bundled_blocks' ),
                ),
                admin_url( 'admin-post.php' )
        );

        if ( $version && function_exists( 'kabel_blocks_plugin_is_current' ) && kabel_blocks_plugin_is_current() ) {
                /* Active but older than the bundled copy — newest blocks missing. */
                $label = sprintf(
                        /* translators: 1: the active plugin version, 2: the bundled version, 3: site part title. */
                        __( 'Update available — the active Kabel Blocks engine (v%1$s) is older than the one bundled with this theme (v%2$s). The newest %3$s features (GTranslate language source, responsive sizes) need the newer engine.', 'kabel' ),
                        $version,
                        function_exists( 'kabel_blocks_bundled_version' ) ? kabel_blocks_bundled_version() : '?',
                        $defs[ $key ]['title']
                );
        } elseif ( $version ) {
                /* Really old copy — no site initializer at all. */
                $label = sprintf(
                        /* translators: 1: the active plugin version, 2: site part title. */
                        __( 'The active Kabel Blocks plugin (v%1$s) is older than this theme — the %2$s needs the bundled engine.', 'kabel' ),
                        $version,
                        $defs[ $key ]['title']
                );
        } else {
                $label = sprintf(
                        /* translators: %s: site part title, e.g. "Site Header". */
                        __( 'The Kabel Blocks engine is not active — the %1$s cannot be edited without it.', 'kabel' ),
                        $defs[ $key ]['title']
                );
        }

        return array(
                'label'   => $label,
                'url'     => $url,
                'version' => $version,
        );
}

/**
 * Register Appearance → Header & Footer.
 */
function kabel_site_parts_menu(): void {
        add_theme_page(
                __( 'Header & Footer', 'kabel' ),
                __( 'Header & Footer', 'kabel' ),
                'edit_pages',
                'kabel-header-footer',
                'kabel_site_parts_screen'
        );
}
add_action( 'admin_menu', 'kabel_site_parts_menu' );

/**
 * Wireframe preview for the "classic" layout card (top bar + main bar).
 */
function kabel_header_preview_classic(): string {
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
 * Wireframe preview for the "minimal" layout card (single bar, languages on
 * the right).
 */
function kabel_header_preview_minimal(): string {
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
 * The Appearance → Header & Footer screen: header layout presets + the two
 * big edit buttons.
 */
function kabel_site_parts_screen(): void {
        $header  = kabel_site_part( 'header' );
        $footer  = kabel_site_part( 'footer' );
        $layouts = kabel_header_layouts();
        $current = kabel_header_layout_current();
        $ready   = kabel_site_part_emitter_ready();

        /* Engine older than the bundled copy: renders fine, but the newest
         * header blocks (GTranslate language source, responsive sizes) are
         * missing from the inserter and the seeds. */
        $outdated = $ready && function_exists( 'kabel_blocks_plugin_is_latest' ) && ! kabel_blocks_plugin_is_latest();

        $install_url = wp_nonce_url(
                add_query_arg( 'action', 'kabel_install_bundled_blocks', admin_url( 'admin-post.php' ) ),
                'kabel_install_bundled_blocks'
        );

        /* "Customized" detection: the header content no longer matches the
         * seed of the last applied layout (manual edits in the editor). */
        $seed_md5    = (string) get_option( 'kabel_header_layout_seed_md5', '' );
        $customized  = $header instanceof WP_Post && '' !== $seed_md5
                && md5( trim( (string) $header->post_content ) ) !== $seed_md5;

        $flag = isset( $_GET['kabel-layout'] ) ? sanitize_key( wp_unslash( $_GET['kabel-layout'] ) ) : '';

        $confirm_js = esc_js( __( 'Applying a layout replaces the current header content — any custom edits are lost. Continue?', 'kabel' ) );
        ?>
        <div class="wrap">
                <h1><?php esc_html_e( 'Header & Footer', 'kabel' ); ?></h1>

                <?php if ( 'applied' === $flag ) : ?>
                        <div class="notice notice-success is-dismissible">
                                <p><?php
                                        printf(
                                                esc_html__( 'Header layout applied — the site header now uses the %s layout.', 'kabel' ),
                                                '<strong>' . esc_html( $layouts[ kabel_header_layout_current() ]['label'] ) . '</strong>'
                                        );
                                ?></p>
                                <p class="description"><?php esc_html_e( 'Page caches were flushed automatically. If the site still shows the old header, your host may keep its own cache — clear it from your hosting panel (e.g. Hostinger → Advanced → Cache Manager) and check again.', 'kabel' ); ?></p>
                        </div>
                <?php elseif ( 'failed' === $flag ) : ?>
                        <div class="notice notice-error is-dismissible">
                                <p><?php esc_html_e( 'The header layout could not be applied — the Kabel Blocks engine is not available. Install or update it first, then try again.', 'kabel' ); ?></p>
                        </div>
                <?php elseif ( 'invalid' === $flag ) : ?>
                        <div class="notice notice-error is-dismissible">
                                <p><?php esc_html_e( 'Unknown header layout.', 'kabel' ); ?></p>
                        </div>
                <?php endif; ?>

                <h2 class="kabel-hf-sub"><?php esc_html_e( 'Header layout', 'kabel' ); ?></h2>
                <p class="description"><?php esc_html_e( 'Pick a starting arrangement for the site header. Every element stays an editable block — after applying, fine-tune anything under Edit Header.', 'kabel' ); ?></p>

                <?php if ( ! $ready ) : ?>
                        <div class="notice notice-warning inline">
                                <p><?php esc_html_e( 'The Kabel Blocks engine is required to build header layouts. Install & activate it first (Appearance → Kabel Setup).', 'kabel' ); ?></p>
                        </div>
                <?php elseif ( $outdated ) : ?>
                        <div class="notice notice-warning inline">
                                <p>
                                        <strong><?php
                                        printf(
                                                esc_html__( 'Update available — the active Kabel Blocks engine (v%1$s) is older than the one bundled with this theme (v%2$s).', 'kabel' ),
                                                esc_html( function_exists( 'kabel_blocks_plugin_version' ) ? kabel_blocks_plugin_version() : '?' ),
                                                esc_html( function_exists( 'kabel_blocks_bundled_version' ) ? kabel_blocks_bundled_version() : '?' )
                                        );
                                        ?></strong>
                                        <?php if ( current_user_can( 'install_plugins' ) ) : ?>
                                                <a class="button button-primary" style="margin-inline-start:8px;vertical-align:middle" href="<?php echo esc_url( $install_url ); ?>"><?php esc_html_e( 'Update the block engine now', 'kabel' ); ?></a>
                                        <?php endif; ?>
                                </p>
                                <p class="description"><?php esc_html_e( 'The newest header features — the GTranslate language source and responsive header sizes — need the newer engine. Applying layouts works either way; your pages and blocks are untouched by the update.', 'kabel' ); ?></p>
                        </div>
                <?php endif; ?>

                <div class="kabel-layouts">
                        <?php foreach ( $layouts as $key => $layout ) :
                                $is_current = $key === $current;
                                $preview    = $layout['preview'];
                        ?>
                                <div class="kabel-layout-card<?php echo $is_current ? ' is-current' : ''; ?>">
                                        <div class="kabel-layout-card__preview"><?php echo $preview(); ?></div>
                                        <div class="kabel-layout-card__body">
                                                <h3 class="kabel-layout-card__title">
                                                        <?php echo esc_html( $layout['label'] ); ?>
                                                        <?php if ( $is_current ) : ?>
                                                                <span class="kabel-layout-card__badge"><?php esc_html_e( 'Current', 'kabel' ); ?></span>
                                                        <?php endif; ?>
                                                </h3>
                                                <p class="kabel-layout-card__desc"><?php echo esc_html( $layout['desc'] ); ?></p>
                                                <div class="kabel-layout-card__actions">
                                                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
                                                                onsubmit="<?php echo esc_attr( 'return window.confirm(' . "'" . $confirm_js . "'" . ');' ); ?>">
                                                                <input type="hidden" name="action" value="kabel_apply_header_layout">
                                                                <input type="hidden" name="layout" value="<?php echo esc_attr( $key ); ?>">
                                                                <?php wp_nonce_field( 'kabel_apply_header_layout' ); ?>
                                                                <?php if ( $is_current ) : ?>
                                                                        <button type="submit" class="button" <?php disabled( ! $ready ); ?>><?php esc_html_e( 'Re-apply this layout', 'kabel' ); ?></button>
                                                                <?php else : ?>
                                                                        <button type="submit" class="button button-primary" <?php disabled( ! $ready ); ?>><?php esc_html_e( 'Apply this layout', 'kabel' ); ?></button>
                                                                <?php endif; ?>
                                                        </form>
                                                        <?php if ( $is_current && $header instanceof WP_Post ) : ?>
                                                                <a class="button button-link" href="<?php echo esc_url( admin_url( 'post.php?post=' . $header->ID . '&action=edit' ) ); ?>"><?php esc_html_e( 'Edit Header', 'kabel' ); ?></a>
                                                        <?php endif; ?>
                                                </div>
                                        </div>
                                </div>
                        <?php endforeach; ?>
                </div>

                <?php if ( $customized ) : ?>
                        <p class="description" style="max-width:640px">
                                <strong><?php esc_html_e( 'Note:', 'kabel' ); ?></strong>
                                <?php esc_html_e( 'the header has been customized since this layout was applied — applying any layout replaces those edits.', 'kabel' ); ?>
                        </p>
                <?php endif; ?>

                <hr style="margin: 24px 0" />

                <p><?php esc_html_e( 'Edit these two private pages with the block editor to change the site-wide header and footer.', 'kabel' ); ?></p>
                <p><?php esc_html_e( 'The pages stay private — they never appear in your navigation or search results, and visitors cannot open them.', 'kabel' ); ?></p>
                <p><?php esc_html_e( 'Clear a page to fall back to the theme’s built-in header or footer.', 'kabel' ); ?></p>

                <?php if ( $header instanceof WP_Post && $footer instanceof WP_Post ) : ?>
                        <p style="margin-top: 2em;">
                                <a class="button button-primary button-hero" href="<?php echo esc_url( admin_url( 'post.php?post=' . $header->ID . '&action=edit' ) ); ?>"><?php esc_html_e( 'Edit Header', 'kabel' ); ?></a>
                                <a class="button button-primary button-hero" href="<?php echo esc_url( admin_url( 'post.php?post=' . $footer->ID . '&action=edit' ) ); ?>"><?php esc_html_e( 'Edit Footer', 'kabel' ); ?></a>
                        </p>
                <?php else : ?>
                        <div class="notice notice-info inline" style="margin-top: 1em;">
                                <p><?php esc_html_e( 'The pages do not exist yet — they are created automatically the first time an administrator opens the admin area.', 'kabel' ); ?></p>
                        </div>
                <?php endif; ?>
        </div>

        <style>
                .kabel-hf-sub { margin-top: 8px; }
                .kabel-layouts {
                        display: grid;
                        grid-template-columns: repeat(auto-fill, minmax(320px, 400px));
                        gap: 16px;
                        max-width: 860px;
                        margin: 16px 0 12px;
                }
                .kabel-layout-card {
                        display: flex; flex-direction: column;
                        border: 1px solid #dcdcde; border-radius: 6px;
                        background: #fff; overflow: hidden;
                }
                .kabel-layout-card.is-current { border-color: #B39A74; box-shadow: 0 0 0 1px #B39A74; }
                .kabel-layout-card__preview { background: #0B0B0D; padding: 14px 14px 10px; }
                .kabel-layout-card__preview svg { display: block; width: 100%; height: auto; }
                .kabel-layout-card__body { padding: 12px 14px 14px; display: flex; flex-direction: column; gap: 8px; flex: 1; }
                .kabel-layout-card__title { display: flex; align-items: center; gap: 8px; margin: 0; font-size: 14px; }
                .kabel-layout-card__badge {
                        font-size: 11px; font-weight: 600; line-height: 1.6;
                        padding: 1px 9px; border-radius: 999px;
                        background: #B39A74; color: #0B0B0D;
                        text-transform: uppercase; letter-spacing: 0.04em;
                }
                .kabel-layout-card__desc { margin: 0; color: #50575e; font-size: 13px; }
                .kabel-layout-card__actions { margin-top: auto; display: flex; align-items: center; gap: 4px; }
        </style>
        <?php
}

/**
 * Context notice on the site-part editor screens.
 *
 * Someone opening "Site Header" from the Pages list should immediately
 * understand the blast radius of their edits. Two channels:
 *
 *   - edit_form_after_title for the classic editor fallback, and
 *   - the block editor's own notices store (the twin below) — since
 *     WP 6.x post.php renders the classic form area inside
 *     <div id="metaboxes" class="hidden">, so classic admin notices
 *     never become visible in the block editor.
 */
function kabel_site_part_editor_notice(): void {
        $post = get_post();
        if ( ! $post instanceof WP_Post || 'page' !== $post->post_type ) {
                return;
        }

        foreach ( kabel_site_part_defs() as $which => $def ) {
                $part = kabel_site_part( $which );
                if ( $part instanceof WP_Post && (int) $part->ID === (int) $post->ID ) {
                        printf(
                                '<div class="notice notice-info inline"><p>%s</p></div>',
                                'header' === $which
                                        ? esc_html__( 'Changes here apply to the site-wide header.', 'kabel' )
                                        : esc_html__( 'Changes here apply to the site-wide footer.', 'kabel' )
                        );
                        return;
                }
        }
}
add_action( 'edit_form_after_title', 'kabel_site_part_editor_notice' );

/**
 * The same context notice through the BLOCK EDITOR's notice system.
 *
 * Classic admin notices never show on post.php (hidden metaboxes area —
 * the same trap the engine warning above documents); the Gutenberg
 * notices store renders in the editor's own notice area.
 */
function kabel_site_part_editor_notice_assets(): void {
        if ( ! current_user_can( 'edit_pages' ) ) {
                return;
        }
        $post = get_post();
        if ( ! $post instanceof WP_Post || 'page' !== $post->post_type ) {
                return;
        }

        $msg = null;
        foreach ( kabel_site_part_defs() as $which => $def ) {
                $part = kabel_site_part( $which );
                if ( $part instanceof WP_Post && (int) $part->ID === (int) $post->ID ) {
                        $msg = 'header' === $which
                                ? __( 'Changes here apply to the site-wide header.', 'kabel' )
                                : __( 'Changes here apply to the site-wide footer.', 'kabel' );
                        break;
                }
        }
        if ( null === $msg ) {
                return;
        }

        $js = sprintf(
                "wp.data.dispatch('core/notices').createNotice('info', %s, { id: 'kabel-part-scope', isDismissible: false });",
                wp_json_encode( $msg, JSON_HEX_TAG | JSON_HEX_AMP )
        );

        wp_register_script( 'kabel-part-scope', false, array( 'wp-data' ), KABEL_VERSION, true );
        wp_enqueue_script( 'kabel-part-scope' );
        wp_add_inline_script( 'kabel-part-scope', $js );
}
add_action( 'enqueue_block_editor_assets', 'kabel_site_part_editor_notice_assets', 15 );
