<?php
/**
 * Plugin Name:       Kabel Blocks
 * Description:       The Stackable-style block suite for the Kabel theme: free-nesting containers (sections, 1–6 column rows), inline-editable heading / text / button / image / icon blocks, full style controls (colors, alignment, radius, shadow), insert patterns and a one-click site bootstrap. Pairs best with the Kabel theme — and runs standalone on any WordPress site, taking over the site header & footer when the theme is absent. Built for Kabel Art Paint (decorative coatings) with zero third-party block dependencies.
 * Version:           3.12.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Kabel Team
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       kabel-blocks
 * Domain Path:       /languages
 *
 * @package Kabel_Blocks
 */

defined( 'ABSPATH' ) || exit;

/*
 * The constants are define()-guarded: when the Kabel theme is running its
 * plugin-less render fallback (theme inc/blocks-fallback.php) the engine is
 * already pointed at the theme's bundled copy, and activating this plugin
 * in that very request must not re-define them.
 */
if ( ! defined( 'KABEL_BLOCKS_VERSION' ) ) {
        define( 'KABEL_BLOCKS_VERSION', '3.12.0' );
}
if ( ! defined( 'KABEL_BLOCKS_FILE' ) ) {
        define( 'KABEL_BLOCKS_FILE', __FILE__ );
}
if ( ! defined( 'KABEL_BLOCKS_DIR' ) ) {
        define( 'KABEL_BLOCKS_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'KABEL_BLOCKS_URI' ) ) {
        define( 'KABEL_BLOCKS_URI', plugin_dir_url( __FILE__ ) );
}

/**
 * Load the plugin translations.
 */
function kabel_blocks_load_textdomain(): void {
        load_plugin_textdomain( 'kabel-blocks', false, dirname( plugin_basename( KABEL_BLOCKS_FILE ) ) . '/languages' );
}
add_action( 'init', 'kabel_blocks_load_textdomain' );

/**
 * Boot the suite.
 *
 * Modules are required on after_setup_theme (AFTER the theme's functions.php
 * has loaded) so kabel_blocks_theme_active() decisions are final, and so a
 * legacy Kabel theme that still bundles an old copy of this library makes
 * the plugin stay dormant instead of redeclaring functions.
 */
function kabel_blocks_boot(): void {
        if ( function_exists( 'kbl_specs' ) ) {
                // An old Kabel theme (<= 0.2) already bundles a copy of the kit.
                add_action( 'admin_notices', 'kabel_blocks_dormant_notice' );
                return;
        }

        require_once KABEL_BLOCKS_DIR . 'inc/assets.php';    // Handles + top category.
        require_once KABEL_BLOCKS_DIR . 'inc/emitter.php';   // kbl/* serializer (JS twins).
        require_once KABEL_BLOCKS_DIR . 'inc/dynamic.php';   // inquiry-form + post-grid.
        require_once KABEL_BLOCKS_DIR . 'inc/gtranslate.php'; // Language switcher bridge.
        require_once KABEL_BLOCKS_DIR . 'inc/engine.php';    // Legacy v1/v2 renderer.
        require_once KABEL_BLOCKS_DIR . 'inc/cache-purge.php'; // Standalone-chrome cache flushes.
        require_once KABEL_BLOCKS_DIR . 'inc/site-chrome.php'; // Header/footer without the theme.
        require_once KABEL_BLOCKS_DIR . 'inc/layouts.php';   // 18 page layouts (content data).
        require_once KABEL_BLOCKS_DIR . 'inc/patterns.php';  // Insert patterns.
        require_once KABEL_BLOCKS_DIR . 'inc/seeder.php';    // One-click filler.
        require_once KABEL_BLOCKS_DIR . 'inc/global-colors.php'; // Global color tokens + settings.
        require_once KABEL_BLOCKS_DIR . 'inc/layout-library.php'; // Kabel menu + layout gallery.
        require_once KABEL_BLOCKS_DIR . 'inc/tools.php';       // Demo reset + hex→token migration.
        require_once KABEL_BLOCKS_DIR . 'inc/onboarding.php';  // Editor welcome notice.
        require_once KABEL_BLOCKS_DIR . 'inc/bootstrap.php'; // Fresh-site initializer.
        require_once KABEL_BLOCKS_DIR . 'inc/migrate.php';   // v1/v2 → v3 page upgrade.

        /*
         * Standalone inquiry fallback — the Kabel theme ships the richer
         * inquiry system, so only load our compact leads module when the
         * theme is absent.
         */
        if ( ! kabel_blocks_theme_active() ) {
                require_once KABEL_BLOCKS_DIR . 'inc/leads.php';
        }
}
add_action( 'after_setup_theme', 'kabel_blocks_boot', 5 );

/**
 * "Plugin is dormant" admin notice — only next to an outdated Kabel theme.
 */
function kabel_blocks_dormant_notice(): void {
        if ( ! current_user_can( 'update_themes' ) ) {
                return;
        }
        printf(
                '<div class="notice notice-warning is-dismissible"><p><strong>%s</strong> %s</p></div>',
                esc_html__( 'Kabel Blocks is standing by.', 'kabel-blocks' ),
                esc_html__( 'Your active Kabel theme (v0.2 or older) already includes a block kit. Update the Kabel theme to v0.4 or newer and this plugin will take over automatically — no action needed.', 'kabel-blocks' )
        );
}
