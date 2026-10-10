<?php
/**
 * One-time editor welcome notice (version-keyed).
 *
 * Stackable / Elementor both greet users after an update. The review found
 * Kabel only had a static Setup card — the first editor visit after an
 * upgrade gave no hint of what's new. This prints a Gutenberg-native
 * notice (core/notices) ONCE per plugin version, linking to the two new
 * destinations: the Layout Library and Global Colors.
 *
 * The "shown" flag is written server-side the moment the notice script is
 * printed (first editor screen after the update) — Stackable's "what's
 * new" behaves the same way.
 *
 * @package Kabel_Blocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Print the welcome notice script inside the editor (once per version).
 */
function kbl_welcome_notice_assets(): void {
        if ( get_option( 'kbl_welcome_done' ) === KABEL_BLOCKS_VERSION ) {
                return;
        }
        if ( ! current_user_can( 'edit_posts' ) ) {
                return;
        }

        update_option( 'kbl_welcome_done', KABEL_BLOCKS_VERSION, false );

        /* Plugin mode (no Kabel theme): the editable site header & footer is
         * the headline capability — the theme's welcome keeps pointing at the
         * two studio destinations only. */
        $actions = array();
        if ( ! kabel_blocks_theme_active() ) {
                $actions[] = sprintf(
                        "{ label: %s, url: %s }",
                        wp_json_encode( __( 'Set up the site header', 'kabel-blocks' ), JSON_HEX_TAG | JSON_HEX_AMP ),
                        wp_json_encode( admin_url( 'admin.php?page=kabel-site-chrome' ), JSON_HEX_TAG | JSON_HEX_AMP )
                );
        }
        $actions[] = sprintf(
                "{ label: %s, url: %s }",
                wp_json_encode( __( 'Browse layouts', 'kabel-blocks' ), JSON_HEX_TAG | JSON_HEX_AMP ),
                wp_json_encode( admin_url( 'admin.php?page=kabel-layout-library' ), JSON_HEX_TAG | JSON_HEX_AMP )
        );
        $actions[] = sprintf(
                "{ label: %s, url: %s }",
                wp_json_encode( __( 'Global colors', 'kabel-blocks' ), JSON_HEX_TAG | JSON_HEX_AMP ),
                wp_json_encode( admin_url( 'admin.php?page=kabel-global-colors' ), JSON_HEX_TAG | JSON_HEX_AMP )
        );

        $js = sprintf(
                "wp.data.dispatch('core/notices').createNotice('info', %s, { id: 'kbl-welcome', isDismissible: true, explicitDismiss: true, actions: [ %s ] });",
                wp_json_encode(
                        sprintf(
                                /* translators: %s: version number. */
                                __( 'Kabel Blocks %s is ready — an editable site header & footer, your brand colors applied everywhere, and an 18-layout page library.', 'kabel-blocks' ),
                                KABEL_BLOCKS_VERSION
                        ),
                        JSON_HEX_TAG | JSON_HEX_AMP
                ),
                implode( ', ', $actions )
        );

        wp_register_script( 'kbl-welcome', false, array( 'wp-data' ), KABEL_BLOCKS_VERSION, true );
        wp_enqueue_script( 'kbl-welcome' );
        wp_add_inline_script( 'kbl-welcome', $js );
}
add_action( 'enqueue_block_editor_assets', 'kbl_welcome_notice_assets', 20 );
