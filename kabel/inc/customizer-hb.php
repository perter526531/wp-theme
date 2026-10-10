<?php
/**
 * Header Builder — Customizer integration (v1.7, Blocksy-style).
 *
 * The builder lives in a top-level Customizer section (`kabel_hb_builder`)
 * whose single control mounts the React app (assets/js/hb-customizer.js,
 * built from src/header-builder/). Saving is the Customizer's own Publish:
 * the app pushes every model change into the `kabel_header_builder` setting
 * (type option, transport postMessage, JSON string — byte-compatible with
 * the k-15 storage format) and the sanitize callback below funnels it
 * through kabel_hb_sanitize() before it lands in the option.
 *
 * The preview iframe runs assets/js/hb-preview.js (customize_preview_init):
 * it debounces setting changes, POSTs the model to the
 * kabel/v1/header-builder/render endpoint, swaps the header DOM and
 * re-initializes the front-end behaviors via window.kabelHBInit().
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Define the app-hosting control class (lazily — WP_Customize_Control only
 * exists once the Customizer manager booted, i.e. inside customize_register).
 */
function kabel_hb_customize_control_class(): void {
        if ( class_exists( 'Kabel_HB_Customize_Control' ) ) {
                return;
        }

        /**
         * The Customizer control that hosts the React app — its whole content is
         * the mount point (no native input; the app talks to the setting directly).
         */
        class Kabel_HB_Customize_Control extends WP_Customize_Control {

                /**
                 * Control type.
                 *
                 * @var string
                 */
                public $type = 'kabel_hb_app';

                /**
                 * Print the React mount point.
                 */
                public function render_content() {
                        echo '<div id="kabel-hb-root" class="kabel-hb-root"></div>';
                        echo '<noscript><p class="notice notice-warning inline">' . esc_html__( 'The header builder needs JavaScript to run.', 'kabel' ) . '</p></noscript>';

                        $bundle = KABEL_DIR . '/assets/js/hb-customizer.js';
                        if ( ! is_file( $bundle ) || 0 === (int) filesize( $bundle ) ) {
                                printf(
                                        '<div class="notice notice-info inline"><p>%s</p></div>',
                                        esc_html__( 'The builder bundle is not built yet (assets/js/hb-customizer.js missing). Run the theme build script.', 'kabel' )
                                );
                        }
                }
        }
}

/**
 * Sanitize the setting value: a JSON string in, a sanitized JSON string out
 * ('' when the model has no rows — the builder-off state).
 *
 * Runs on Publish and keeps the stored format identical to the k-15 option.
 *
 * @param mixed $input Raw JSON string from the React app.
 * @return string
 */
function kabel_hb_customize_sanitize( $input ): string {
        if ( ! is_string( $input ) || '' === trim( $input ) ) {
                return '';
        }
        $model = json_decode( wp_unslash( $input ), true );
        if ( ! is_array( $model ) ) {
                return '';
        }
        $clean = kabel_hb_sanitize( $model );
        if ( empty( $clean['rows'] ) ) {
                return '';
        }
        return (string) wp_json_encode( $clean );
}

/**
 * Register the section + setting + control.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function kabel_hb_customize_register( WP_Customize_Manager $wp_customize ): void {
        kabel_hb_customize_control_class();

        $wp_customize->add_section( 'kabel_hb_builder', array(
                'title'       => __( 'Kabel Header Builder', 'kabel' ),
                'priority'    => 21, /* Right after Site Identity — where a header belongs. */
                'description' => __( 'Build the site header visually: rows, elements and the mobile drawer. The preview updates live; Publish saves.', 'kabel' ),
        ) );

        $wp_customize->add_setting( 'kabel_header_builder', array(
                'type'                 => 'option',
                'transport'            => 'postMessage',
                'capability'           => 'edit_theme_options',
                'sanitize_callback'    => 'kabel_hb_customize_sanitize',
        ) );

        $wp_customize->add_control( new Kabel_HB_Customize_Control( $wp_customize, 'kabel_header_builder', array(
                'section'  => 'kabel_hb_builder',
                'settings' => 'kabel_header_builder',
        ) ) );
}
add_action( 'customize_register', 'kabel_hb_customize_register' );

/**
 * The app payload (KabelHBData). Trimmed from the k-13-key REST payload to
 * what the Customizer app consumes — no draft nonce, no block-page URL.
 *
 * @return array
 */
function kabel_hb_customizer_data(): array {
        $menus = array();
        foreach ( (array) wp_get_nav_menus() as $menu ) {
                if ( is_object( $menu ) && isset( $menu->term_id, $menu->name ) ) {
                        $menus[] = array(
                                'id'   => (int) $menu->term_id,
                                'name' => (string) $menu->name,
                        );
                }
        }

        $logo_url = '';
        if ( has_custom_logo() ) {
                $logo_id  = (int) get_theme_mod( 'custom_logo' );
                $logo_url = $logo_id > 0 ? (string) wp_get_attachment_image_url( $logo_id, 'full' ) : '';
        }

        return array(
                'model'         => kabel_hb_get(),
                'liveSource'    => kabel_hb_live_source(),
                'menus'         => $menus,
                'canUnfiltered' => current_user_can( 'unfiltered_html' ),
                'siteName'      => get_bloginfo( 'name' ),
                'logoUrl'       => $logo_url,
                'home'          => home_url( '/' ),
                'woocommerce'   => class_exists( 'WooCommerce' ),
                'rest'          => esc_url_raw( rest_url( 'kabel/v1/header-builder/render' ) ),
                'restNonce'     => wp_create_nonce( 'wp_rest' ),
                'l10n'          => kabel_hb_l10n(),
        );
}

/**
 * Controls-side assets: the React bundle + its stylesheet + KabelHBData.
 * Everything degrades to an inline notice when the bundle is missing.
 */
function kabel_hb_customize_controls_assets(): void {
        if ( ! current_user_can( 'edit_theme_options' ) ) {
                return;
        }

        wp_enqueue_media();

        $js = KABEL_DIR . '/assets/js/hb-customizer.js';
        if ( is_file( $js ) && 0 < (int) filesize( $js ) ) {
                wp_enqueue_script(
                        'kabel-hb-customizer',
                        KABEL_URI . '/assets/js/hb-customizer.js',
                        array( 'customize-controls', 'wp-element', 'wp-i18n' ),
                        (string) filemtime( $js ),
                        true
                );
                /*
                 * KabelHBData as real JSON (booleans/null keep their types —
                 * wp_localize_script would stringify them), same doctrine as k-15.
                 */
                wp_add_inline_script(
                        'kabel-hb-customizer',
                        'window.KabelHBData = ' . (string) wp_json_encode( kabel_hb_customizer_data() ) . ';',
                        'before'
                );
        }

        $css = KABEL_DIR . '/assets/css/hb-customizer.css';
        if ( is_file( $css ) && 0 < (int) filesize( $css ) ) {
                wp_enqueue_style(
                        'kabel-hb-customizer-css',
                        KABEL_URI . '/assets/css/hb-customizer.css',
                        array( 'customize-controls' ),
                        (string) filemtime( $css )
                );
        }
}
add_action( 'customize_controls_enqueue_scripts', 'kabel_hb_customize_controls_assets' );

/**
 * Preview-side assets: the render bridge (KabelHBPreview = endpoint + nonce).
 */
function kabel_hb_customize_preview_assets(): void {
        $js = KABEL_DIR . '/assets/js/hb-preview.js';
        if ( ! is_file( $js ) || 0 === (int) filesize( $js ) ) {
                return;
        }
        wp_enqueue_script(
                'kabel-hb-preview',
                KABEL_URI . '/assets/js/hb-preview.js',
                array( 'customize-preview' ),
                (string) filemtime( $js ),
                true
        );
        wp_add_inline_script(
                'kabel-hb-preview',
                'window.KabelHBPreview = ' . (string) wp_json_encode( array(
                        'rest'      => esc_url_raw( rest_url( 'kabel/v1/header-builder/render' ) ),
                        'restNonce' => wp_create_nonce( 'wp_rest' ),
                ) ) . ';',
                'before'
        );
}
add_action( 'customize_preview_init', 'kabel_hb_customize_preview_assets' );
