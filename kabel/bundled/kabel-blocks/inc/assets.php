<?php
/**
 * Kabel Blocks v3 — asset loading.
 *
 * Handles:
 *   - kabel-blocks-v3 (editor script, built from src/ via build.mjs)
 *   - kabel-blocks-v3 (front-end + canvas styles for kbl/* markup)
 *   - kabel-blocks-v3-editor (editor-only canvas/inspector polish)
 *   - kabel-blocks-legacy (v1/v2 front skin — only without the Kabel theme)
 *   - kabel-blocks-form (standalone REST form handler)
 *   - the 'kabel' block category, pinned to the TOP of the inserter.
 *
 * @package Kabel_Blocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Resolve an asset URL, preferring the minified sibling.
 *
 * `bun run build` regenerates the .min copies (see build.mjs →
 * minifyHandAuthored()); a missing or stale .min simply falls back to the
 * readable original, so hand-edits can never 404 the skin.
 *
 * @param string $rel Path relative to the plugin root, e.g. 'assets/css/kabel-blocks.css'.
 * @return string Full URL (prefers .min.css / .min.js when the file exists).
 */
function kbl3_asset_uri( string $rel ): string {
        $min = (string) preg_replace( '/\.(css|js)$/i', '.min.$1', $rel );
        if ( $min !== $rel && is_readable( KABEL_BLOCKS_DIR . $min ) ) {
                return KABEL_BLOCKS_URI . $min;
        }
        return KABEL_BLOCKS_URI . $rel;
}

/**
 * Register (not enqueue) the plugin's asset handles.
 */
function kabel_blocks_register_assets(): void {
        wp_register_script(
                'kabel-blocks-v3',
                kbl3_asset_uri( 'assets/js/blocks.js' ),
                array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-data', 'wp-i18n', 'wp-api-fetch', 'wp-core-data' ),
                KABEL_BLOCKS_VERSION,
                true
        );
        wp_set_script_translations( 'kabel-blocks-v3', 'kabel-blocks', KABEL_BLOCKS_DIR . 'languages' );

        wp_register_script(
                'kabel-blocks-front',
                kbl3_asset_uri( 'assets/js/front.js' ),
                array(),
                KABEL_BLOCKS_VERSION,
                true
        );

        wp_register_style(
                'kabel-blocks-v3',
                kbl3_asset_uri( 'assets/css/kabel-blocks.css' ),
                array(),
                KABEL_BLOCKS_VERSION
        );

        wp_register_style(
                'kabel-blocks-v3-editor',
                kbl3_asset_uri( 'assets/css/kabel-editor.css' ),
                array( 'kabel-blocks-v3' ),
                KABEL_BLOCKS_VERSION
        );

        wp_register_style(
                'kabel-blocks-legacy',
                kbl3_asset_uri( 'assets/css/kabel-legacy.css' ),
                array(),
                KABEL_BLOCKS_VERSION
        );

        wp_register_script(
                'kabel-blocks-form',
                kbl3_asset_uri( 'assets/js/kabel-form.js' ),
                array(),
                KABEL_BLOCKS_VERSION,
                true
        );

        /* Self-hosted fonts for the canvas (Barlow + Oregano, from the theme). */
        kabel_blocks_editor_fonts();
}
add_action( 'init', 'kabel_blocks_register_assets', 15 );

/**
 * Pin the Kabel category to the top of the inserter.
 */
function kabel_blocks_block_categories( array $categories ): array {
        $kabel = array(
                array(
                        'slug'  => 'kabel',
                        'title' => __( 'Kabel 区块', 'kabel-blocks' ),
                        'icon'  => 'admin-customizer',
                ),
        );
        foreach ( $categories as $category ) {
                if ( isset( $category['slug'] ) && 'kabel' === $category['slug'] ) {
                        return $categories;
                }
        }
        return array_merge( $kabel, $categories );
}
add_filter( 'block_categories_all', 'kabel_blocks_block_categories' );

/**
 * Append @font-face rules for the editor canvas.
 *
 * The fonts live in the Kabel theme (assets/fonts/kabel/*). When the theme is
 * present we point at its files; without it the canvas falls back to the
 * generic sans stack.
 */
function kabel_blocks_editor_fonts(): void {
        $theme_uri = get_template_directory_uri();
        $theme_dir = get_template_directory();
        if ( 'kabel' !== wp_get_theme()->get_template() || ! is_dir( $theme_dir . '/assets/fonts/kabel' ) ) {
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
                        esc_url( $theme_uri . '/assets/fonts/kabel/' . $font[3] ),
                        $font[1],
                        $font[2]
                );
        }
        wp_add_inline_style( 'kabel-blocks-v3-editor', $faces );
}

/**
 * Editor: the v3 script + canvas styles. The v3 front skin loads INSIDE the
 * canvas too (kabel-blocks-v3-editor depends on it). The script also learns
 * which optional integrations are live (KBL3_ENV), so blocks can show
 * accurate guidance — the language switcher reads the GTranslate settings
 * (settings URL + current language list) so its sidebar shows the plugin's
 * languages without a REST round-trip.
 */
function kabel_blocks_editor_assets(): void {
        wp_enqueue_script( 'kabel-blocks-v3' );
        wp_enqueue_style( 'kabel-blocks-v3-editor' );

        $env = array(
                'gtranslate'  => kbl3_gtranslate_active(),
                'globalColors' => kbl3_global_colors(),
        );
        if ( current_user_can( 'manage_options' ) ) {
                $env['gcSettingsUrl'] = admin_url( 'admin.php?page=kabel-global-colors' );
        }
        if ( $env['gtranslate'] ) {
                $env['gtSettingsUrl'] = admin_url( 'options-general.php?page=gtranslate_options' );
                $env['gtLanguages']   = kbl3_gtranslate_languages();
        }
        wp_add_inline_script(
                'kabel-blocks-v3',
                'window.KBL3_ENV = Object.assign( {}, window.KBL3_ENV, ' . wp_json_encode( $env ) . ' );',
                'before'
        );
}
add_action( 'enqueue_block_editor_assets', 'kabel_blocks_editor_assets' );

/**
 * Iframe canvas (apiVersion 3 blocks): WP 6.9+ renders v3 blocks inside an
 * iframe document, and only styles enqueued on `enqueue_block_assets` are
 * mirrored into it — `enqueue_block_editor_assets` styles land in the parent
 * page and trigger the "added to the iframe incorrectly" console warning.
 * The same handles are enqueued in both places; WordPress prints a handle
 * once per document, so parent + iframe each get exactly one copy.
 */
function kabel_blocks_iframe_canvas_assets(): void {
        if ( ! is_admin() ) {
                return;
        }
        wp_enqueue_style( 'kabel-blocks-v3' );
        wp_enqueue_style( 'kabel-blocks-v3-editor' );
}
add_action( 'enqueue_block_assets', 'kabel_blocks_iframe_canvas_assets', 20 );

/**
 * Front end: does THIS request render any Kabel markup at all?
 *
 * The v3 skin + runtime are ~42 KB minified; on a pure-plugin site plenty
 * of views (a plain blog post, a category archive of them, 404) contain no
 * Kabel block at all. Skipping the assets there is free page weight — but
 * the check must be conservative, because a missing skin is far worse than
 * a wasted 42 KB:
 *
 *   - Kabel theme active → always (its templates emit kbl3-* markup).
 *   - Standalone chrome on → always (the header/footer render everywhere).
 *   - Logged-in editors → always (they can spot and fix a missed case).
 *   - The main post, synced patterns it references, the first 20 loop
 *     posts (full-content archives) and block widgets are scanned for
 *     'wp:kbl/' / 'wp:kabel-blocks/' markers and the legacy form shortcode.
 */
function kbl3_front_renders_kbl(): bool {
        static $needs = null;
        if ( null !== $needs ) {
                return $needs;
        }

        if ( kabel_blocks_theme_active() ) {
                return $needs = true;
        }
        if ( kbl3_site_chrome_enabled() ) {
                return $needs = true;
        }
        if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
                return $needs = true;
        }

        $needs = false;

        /* The main query's post (singular views). */
        $post = get_post();
        if ( $post instanceof WP_Post ) {
                if ( kbl3_content_has_kbl( (string) $post->post_content ) ) {
                        return $needs = true;
                }
                /* Synced patterns referenced from it (one indexed query). */
                if ( preg_match_all( '#<!-- wp:block \{"ref":(\d+)\}#', (string) $post->post_content, $m ) ) {
                        $parts = get_posts( array(
                                'post_type'              => 'wp_block',
                                'post__in'               => array_map( 'absint', array_slice( $m[1], 0, 10 ) ),
                                'post_status'            => 'any',
                                'posts_per_page'         => 10,
                                'no_found_rows'          => true,
                                'update_post_term_cache' => false,
                        ) );
                        foreach ( $parts as $part ) {
                                if ( kbl3_content_has_kbl( (string) $part->post_content ) ) {
                                        return $needs = true;
                                }
                        }
                }
        }

        /* Archives / search: full-content templates render the loop. */
        global $wp_query;
        if ( ! empty( $wp_query->posts ) ) {
                foreach ( array_slice( $wp_query->posts, 0, 20 ) as $loop_post ) {
                        if ( $loop_post instanceof WP_Post && kbl3_content_has_kbl( (string) $loop_post->post_content ) ) {
                                return $needs = true;
                        }
                }
        }

        /* Block widgets (footer sidebars etc.). */
        if ( kbl3_widgets_have_kbl_blocks() ) {
                return $needs = true;
        }

        return $needs;
}

/**
 * Does a content string carry Kabel markers?
 *
 * @param string $content Raw post content.
 */
function kbl3_content_has_kbl( string $content ): bool {
        if ( '' === $content ) {
                return false;
        }
        return false !== strpos( $content, 'wp:kbl/' )
                || false !== strpos( $content, 'wp:kabel-blocks/' )
                || false !== strpos( $content, '[kabel_form' );
}

/**
 * Any block widget (sidebars, footer areas) containing Kabel markup?
 */
function kbl3_widgets_have_kbl_blocks(): bool {
        static $found = null;
        if ( null !== $found ) {
                return $found;
        }

        $found    = false;
        $sidebars = function_exists( 'wp_get_sidebars_widgets' ) ? wp_get_sidebars_widgets() : null;
        if ( ! is_array( $sidebars ) ) {
                return $found;
        }
        $blocks = get_option( 'widget_block', array() );
        if ( ! is_array( $blocks ) ) {
                return $found;
        }
        foreach ( $sidebars as $widget_ids ) {
                if ( ! is_array( $widget_ids ) ) {
                        continue;
                }
                foreach ( $widget_ids as $widget_id ) {
                        if ( 0 === strpos( (string) $widget_id, 'block-' ) ) {
                                $idx = (int) substr( (string) $widget_id, 6 );
                                if ( isset( $blocks[ $idx ]['content'] ) && kbl3_content_has_kbl( (string) $blocks[ $idx ]['content'] ) ) {
                                        $found = true;
                                        return $found;
                                }
                        }
                }
        }
        return $found;
}

/**
 * Front end: the v3 skin only when the view actually renders Kabel markup
 * (see kbl3_front_renders_kbl()); the front runtime (kb-js flag + entrance
 * animations + nav/tab/lang interactions) with it — the theme's main.js
 * also adds .kb-js, but the plugin must run standalone; the legacy v1/v2
 * skin + form JS only without the Kabel theme.
 */
function kabel_blocks_front_assets(): void {
        if ( kbl3_front_renders_kbl() ) {
                wp_enqueue_style( 'kabel-blocks-v3' );
                wp_enqueue_script( 'kabel-blocks-front' );
        }

        if ( kabel_blocks_theme_active() ) {
                return;
        }

        /* No theme → the legacy skin is needed whenever kbl/v1 markup can
         * render, which is the same gate as above. */
        if ( kbl3_front_renders_kbl() ) {
                wp_enqueue_style( 'kabel-blocks-legacy' );
                wp_enqueue_script( 'kabel-blocks-form' );
                wp_localize_script( 'kabel-blocks-form', 'KabelBlocksForm', array(
                        'rest'  => esc_url_raw( rest_url( 'kabel-blocks/v1/lead' ) ),
                        'nonce' => wp_create_nonce( 'wp_rest' ),
                ) );
        }
}
add_action( 'wp_enqueue_scripts', 'kabel_blocks_front_assets' );

/**
 * Legacy v1/v2 markup inside the editor canvas (blocks still render through
 * the engine when a v1/v2 page is opened) — keep the legacy skin there too.
 *
 * is_admin() gate: `enqueue_block_assets` also fires on the FRONT end for
 * block themes (the template renders through blocks), which used to leak
 * the 15 KB legacy skin onto every page of a plugin-mode site even when the
 * front assets above were skipped.
 */
function kabel_blocks_legacy_editor_skin(): void {
        if ( kabel_blocks_theme_active() || ! is_admin() ) {
                return;
        }
        wp_enqueue_style( 'kabel-blocks-legacy' );
}
add_action( 'enqueue_block_assets', 'kabel_blocks_legacy_editor_skin' );
