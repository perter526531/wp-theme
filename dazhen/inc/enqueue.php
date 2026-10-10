<?php
/**
 * Asset loading: CSS, JS, self-hosted fonts.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

/**
 * Front-end asset queue. Cache-busting is bound to file modification time.
 */
function dazhen_enqueue_assets() {
        $css_path = DAZHEN_DIR . '/assets/css/main.css';
        $css_ver  = file_exists( $css_path ) ? (string) filemtime( $css_path ) : DAZHEN_VERSION;

        wp_enqueue_style( 'dazhen-main', DAZHEN_URI . '/assets/css/main.css', array(), $css_ver );

        // Self-hosted fonts: preload the two critical woff2 files (latin subsets).
        // Noto Sans SC is intentionally NOT loaded in phase 1 (EN launch) to save ~1MB.
        wp_enqueue_style(
                'dazhen-fonts',
                DAZHEN_URI . '/assets/css/fonts.css',
                array(),
                $css_ver
        );

        $js_path = DAZHEN_DIR . '/assets/js/main.js';
        $js_ver  = file_exists( $js_path ) ? (string) filemtime( $js_path ) : DAZHEN_VERSION;

        // Native ES module — no jQuery, no bundler output, module pattern inside.
        wp_enqueue_script( 'dazhen-main', DAZHEN_URI . '/assets/js/main.js', array(), $js_ver, true );
        wp_enqueue_script(
                'dazhen-heating-gauge',
                DAZHEN_URI . '/assets/js/heating-gauge.js',
                array(),
                $js_ver,
                true
        );
        wp_enqueue_script(
                'dazhen-selector-wizard',
                DAZHEN_URI . '/assets/js/selector-wizard.js',
                array(),
                $js_ver,
                true
        );
        wp_enqueue_script(
                'dazhen-oem-journey',
                DAZHEN_URI . '/assets/js/oem-journey.js',
                array(),
                $js_ver,
                true
        );

        if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
                wp_enqueue_script( 'comment-reply' );
        }
}
add_action( 'wp_enqueue_scripts', 'dazhen_enqueue_assets' );

/**
 * Preload critical font files for faster first paint.
 */
function dazhen_preload_fonts() {
        $fonts = array(
                'space-grotesk-latin-var.woff2' => 'Space Grotesk',
                'inter-latin-var.woff2'         => 'Inter',
        );
        foreach ( $fonts as $file => $name ) {
                printf(
                        '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin="anonymous">',
                        esc_url( DAZHEN_URI . '/assets/fonts/' . $file )
                );
        }
}
add_action( 'wp_head', 'dazhen_preload_fonts', 5 );
