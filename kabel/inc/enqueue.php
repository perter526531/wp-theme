<?php
/**
 * Asset loading: CSS, JS, self-hosted fonts.
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Front-end asset queue. Cache-busting is bound to file modification time.
 */
function kabel_enqueue_assets() {
        $css_path = KABEL_DIR . '/assets/css/main.css';
        $css_ver  = file_exists( $css_path ) ? (string) filemtime( $css_path ) : KABEL_VERSION;

        wp_enqueue_style( 'kabel-main', KABEL_URI . '/assets/css/main.css', array(), $css_ver );

        /*
         * Fonts: Barlow (300–700 + 400 italic) and Oregano are declared as
         * @font-face rules at the top of main.css, pointing at
         * assets/fonts/kabel/*.woff2 — no separate font stylesheet needed.
         */

        $js_path = KABEL_DIR . '/assets/js/main.js';
        $js_ver  = file_exists( $js_path ) ? (string) filemtime( $js_path ) : KABEL_VERSION;

        // Native ES module — no jQuery, no bundler output, module pattern inside.
        wp_enqueue_script( 'kabel-main', KABEL_URI . '/assets/js/main.js', array(), $js_ver, true );

        if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
                wp_enqueue_script( 'comment-reply' );
        }
}
add_action( 'wp_enqueue_scripts', 'kabel_enqueue_assets' );

/**
 * v3 block skin when the Kabel Blocks plugin is NOT active.
 *
 * kbl/* blocks (v3) are static markup styled by the plugin's
 * kabel-blocks.css. If the plugin gets deactivated, those pages would lose
 * their skin — the theme ships the same stylesheet inside its bundled copy
 * and enqueues it as a fallback (the plugin's own handle wins when active).
 */
function kabel_enqueue_bundled_blocks_css() {
        /*
         * Skip when the plugin is active — it registers its own (same) handle
         * at init. The KABEL_BLOCKS_VERSION constant is NOT a reliable guard
         * here: the theme's bundled fallback defines it too.
         */
        if ( wp_style_is( 'kabel-blocks-v3', 'registered' ) ) {
                return;
        }
        $base = KABEL_DIR . '/bundled/kabel-blocks/assets/css/kabel-blocks';
        /*
         * Prefer the minified sibling the plugin build ships; fall back to
         * the readable original (same behaviour as inc/assets.php).
         */
        $file = $base . '.min.css';
        if ( ! file_exists( $file ) ) {
                $file = $base . '.css';
                if ( ! file_exists( $file ) ) {
                        return;
                }
        }
        wp_enqueue_style(
                'kabel-blocks-v3-fallback',
                KABEL_URI . '/bundled/kabel-blocks/assets/css/' . basename( $file ),
                array(),
                (string) filemtime( $file )
        );
}
add_action( 'wp_enqueue_scripts', 'kabel_enqueue_bundled_blocks_css', 20 );

/**
 * Global-color variables when the plugin is NOT active.
 *
 * Blocks may store var(--kbl-gc-N) references. With the plugin active its
 * own inline style prints the values; in safe mode the theme re-prints
 * them from the saved option. When no custom palette was ever saved the
 * option is absent and the defaults equal the theme tokens — nothing to
 * print.
 */
function kabel_print_global_color_vars_fallback(): void {
        if ( wp_style_is( 'kabel-blocks-v3', 'registered' ) ) {
                return; // The real plugin owns the variables.
        }
        $raw = get_option( 'kbl3_global_colors' );
        if ( ! is_array( $raw ) || ! $raw ) {
                return;
        }
        $css = '';
        foreach ( array_values( $raw ) as $i => $slot ) {
                if ( ! is_array( $slot ) || empty( $slot['color'] ) ) {
                        continue;
                }
                $color = sanitize_hex_color( (string) $slot['color'] );
                if ( $color ) {
                        $css .= '--kbl-gc-' . ( $i + 1 ) . ':' . $color . ';';
                }
        }
        if ( '' !== $css ) {
                echo '<style id="kabel-gc-fallback">:root{' . $css . '}</style>' . "\n";
        }
}
add_action( 'wp_head', 'kabel_print_global_color_vars_fallback', 60 );

/**
 * Preload critical font files for faster first paint:
 * Barlow 400 (body) and Barlow 600 (headings/buttons).
 */
function kabel_preload_fonts() {
        $fonts = array(
                'kabel/barlow--v13-normal-400.woff2' => 'Barlow',
                'kabel/barlow--v13-normal-600.woff2' => 'Barlow',
        );
        foreach ( $fonts as $file => $name ) {
                printf(
                        '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin="anonymous">',
                        esc_url( KABEL_URI . '/assets/fonts/' . $file )
                );
        }
}
add_action( 'wp_head', 'kabel_preload_fonts', 5 );
