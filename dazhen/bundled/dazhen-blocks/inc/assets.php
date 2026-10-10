<?php
/**
 * Asset loading — specs injection, standalone skin, front-end form JS.
 *
 * Pairing rule:
 *   Dazhen theme ACTIVE  → the theme's main.css already styles every A×B
 *                          class our blocks emit, so the plugin loads NO
 *                          front-end CSS at all.
 *   Dazhen theme ABSENT  → the plugin's compact dazhen-blocks.css takes
 *                          over so the blocks look right on any theme.
 *
 * The editor script is always the same GENERIC runtime: it reads the spec
 * registry injected below and builds every block's sidebar automatically.
 *
 * @package Dazhen_Blocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register (not enqueue) the plugin's asset handles.
 */
function dazhen_blocks_register_assets(): void {
        wp_register_script(
                'dazhen-blocks-editor',
                DAZHEN_BLOCKS_URI . 'assets/js/blocks-editor.js',
                array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-i18n', 'wp-api-fetch', 'wp-editor', 'wp-data', 'wp-plugins', 'wp-edit-post' ),
                DAZHEN_BLOCKS_VERSION,
                true
        );
        wp_set_script_translations( 'dazhen-blocks-editor', 'dazhen-blocks', DAZHEN_BLOCKS_DIR . 'languages' );

        wp_register_style(
                'dazhen-blocks',
                DAZHEN_BLOCKS_URI . 'assets/css/dazhen-blocks.css',
                array(),
                DAZHEN_BLOCKS_VERSION
        );

        wp_register_script(
                'dazhen-blocks-form',
                DAZHEN_BLOCKS_URI . 'assets/js/dazhen-form.js',
                array(),
                DAZHEN_BLOCKS_VERSION,
                true
        );
}
add_action( 'init', 'dazhen_blocks_register_assets', 20 );

/**
 * Standalone skin — only when the Dazhen theme is NOT active.
 * Fires on the front end and inside the editor canvas iframe.
 */
function dazhen_blocks_maybe_styles(): void {
        if ( dazhen_blocks_theme_active() ) {
                return;
        }
        wp_enqueue_style( 'dazhen-blocks' );
}
add_action( 'enqueue_block_assets', 'dazhen_blocks_maybe_styles' );

/**
 * Editor script + the spec registry it runs on.
 */
function dazhen_blocks_editor_assets(): void {
        wp_enqueue_script( 'dazhen-blocks-editor' );

        /*
         * The skin also covers the editor parent document: the sidebar UI rules
         * (.dzb-repeater, .dzb-media-* etc.) live in the admin chrome, outside
         * the canvas iframe. The section classes are inert there (admin DOM
         * never uses them), so loading the whole skin is safe.
         */
        wp_enqueue_style( 'dazhen-blocks' );

        $specs = function_exists( 'dzb_specs' ) ? dzb_specs() : array();

        // Strip engine-only keys (closures are not JSON-serializable).
        $clean = array();
        foreach ( $specs as $spec ) {
                unset( $spec['render'] );
                $clean[] = $spec;
        }

        wp_localize_script( 'dazhen-blocks-editor', 'DazhenBlocksSpecs', array(
                'version' => DAZHEN_BLOCKS_VERSION,
                'blocks'  => array_values( $clean ),
        ) );

        // Layout list for the "Dazhen layouts" sidebar panel (pages only).
        $layouts   = array();
        $screen    = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        $is_page   = $screen && 'page' === $screen->post_type;
        $post_id   = $is_page ? (int) get_the_ID() : 0;
        if ( function_exists( 'dazhen_layout_options' ) ) {
                $layouts = dazhen_layout_options();
        }

        wp_localize_script( 'dazhen-blocks-editor', 'DazhenBlocksLayouts', array(
                'options'  => $layouts,
                'fillUrl'  => admin_url( 'admin-post.php' ),
                'isPage'   => $is_page,
                'postId'   => $post_id,
                'nonce'    => wp_create_nonce( 'dazhen_fill_page_' . $post_id ),
        ) );
}
add_action( 'enqueue_block_editor_assets', 'dazhen_blocks_editor_assets' );

/**
 * Front-end form script (standalone leads form only, no theme).
 */
function dazhen_blocks_maybe_form_script(): void {
        if ( dazhen_blocks_theme_active() ) {
                return;
        }
        wp_enqueue_script( 'dazhen-blocks-form' );
        wp_localize_script( 'dazhen-blocks-form', 'DazhenBlocksForm', array(
                'rest'  => esc_url_raw( rest_url( 'dazhen-blocks/v1/lead' ) ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
        ) );
}
add_action( 'wp_enqueue_scripts', 'dazhen_blocks_maybe_form_script' );
