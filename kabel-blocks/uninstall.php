<?php
/**
 * Kabel Blocks uninstall — remove plugin-owned options only.
 *
 * Content is NEVER touched: pages built with Kabel blocks, the media
 * library, and inquiry leads belong to the site owner and survive the
 * uninstall (the same contract Stackable and Elementor follow). Blocks
 * that referenced global-color slots degrade gracefully — with the
 * variables gone the browser falls back to inherited colors.
 *
 * @package Kabel_Blocks
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

foreach ( array(
        'kbl3_global_colors',       // Global Colors slots.
        'kbl3_site_chrome',         // Standalone chrome on/off toggle.
        'kbl3_seed_menu_rescued',   // Seed-menu rescue (once per site).
        'kabel_seeder_notice_off',  // Pages-screen helper notice dismissal.
        'kabel_bootstrapped',       // Fresh-site bootstrap marker.
        'kabel_bootstrap_incomplete', // Bootstrap resume flag.
        'kabel_bootstrap_notice_off', // Bootstrap notice dismissal.
        'kbl_asset_map',            // Sample-asset → attachment ID cache.
        'kbl_welcome_done',         // Editor welcome-notice marker.
) as $kbl_option ) {
        delete_option( $kbl_option );
}
/* kabel_header_layout / kabel_header_layout_seed_md5 stay: the Kabel theme
 * reads them, and deleting them while the theme is still active would
 * reset its layout tracking. */
delete_transient( 'kbl_bootstrap_report' );
