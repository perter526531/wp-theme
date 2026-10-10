<?php
/**
 * Plugin Name:       Dazhen Blocks — 大振内容块
 * Plugin URI:        https://example.com/dazhen_blocks
 * Description:       The self-developed block engine that powers the Dazhen theme: 17 content blocks with a spec-driven editor runtime (every block = auto-built sidebar controls, zero lock-in), 8 full-page layouts, 18 insert patterns and a one-click page filler. Pairs best with the Dazhen theme — and runs standalone on any WordPress site. 自研内容块引擎:17 个内容块、8 种整页版式、18 个插入模式、空页面一键填充;与大振主题配套效果最佳,也可独立使用。
 * Version:           2.0.2
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Dazhen Technology
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       dazhen-blocks
 * Domain Path:       /languages
 *
 * @package Dazhen_Blocks
 */

defined( 'ABSPATH' ) || exit;

define( 'DAZHEN_BLOCKS_VERSION', '2.0.2' );
define( 'DAZHEN_BLOCKS_FILE', __FILE__ );
define( 'DAZHEN_BLOCKS_DIR', plugin_dir_path( __FILE__ ) );
define( 'DAZHEN_BLOCKS_URI', plugin_dir_url( __FILE__ ) );

/**
 * Is the Dazhen theme (the companion theme) active?
 *
 * When it is, the theme already styles every A×B class the blocks emit, so
 * the plugin skips its own front-end stylesheet and reuses the theme's
 * renderers — the pair behaves like one product.
 */
function dazhen_blocks_theme_active(): bool {
        $theme = wp_get_theme();
        return 'dazhen' === $theme->get_template() || 'dazhen' === $theme->get( 'TextDomain' );
}

/**
 * Load the plugin translations.
 */
function dazhen_blocks_load_textdomain(): void {
        load_plugin_textdomain( 'dazhen-blocks', false, dirname( plugin_basename( DAZHEN_BLOCKS_FILE ) ) . '/languages' );
}
add_action( 'init', 'dazhen_blocks_load_textdomain' );

/**
 * Boot the engine.
 *
 * Modules are required on after_setup_theme (AFTER the theme's functions.php
 * has loaded) so dazhen_blocks_theme_active() decisions are final, and so a
 * legacy Dazhen theme that still bundles an old copy of this library makes
 * the plugin stay dormant instead of redeclaring functions.
 */
function dazhen_blocks_boot(): void {
        if ( function_exists( 'dzb_specs' ) ) {
                // An old Dazhen theme (<= 0.2) already bundles a copy of the kit.
                add_action( 'admin_notices', 'dazhen_blocks_dormant_notice' );
                return;
        }

        require_once DAZHEN_BLOCKS_DIR . 'inc/engine.php';    // The interpreter.
        require_once DAZHEN_BLOCKS_DIR . 'inc/layouts.php';   // 8 page layouts.
        require_once DAZHEN_BLOCKS_DIR . 'inc/patterns.php';  // Insert patterns.
        require_once DAZHEN_BLOCKS_DIR . 'inc/seeder.php';    // One-click filler.
        require_once DAZHEN_BLOCKS_DIR . 'inc/bootstrap.php'; // Fresh-site initializer.
        require_once DAZHEN_BLOCKS_DIR . 'inc/assets.php';    // Specs + styles.

        /*
         * Standalone inquiry fallback — the Dazhen theme ships the richer
         * inquiry system, so only load our compact leads module when the
         * theme is absent.
         */
        if ( ! dazhen_blocks_theme_active() ) {
                require_once DAZHEN_BLOCKS_DIR . 'inc/leads.php';
        }
}
add_action( 'after_setup_theme', 'dazhen_blocks_boot', 5 );

/**
 * "Plugin is dormant" admin notice — only next to an outdated Dazhen theme.
 */
function dazhen_blocks_dormant_notice(): void {
        if ( ! current_user_can( 'update_themes' ) ) {
                return;
        }
        printf(
                '<div class="notice notice-warning is-dismissible"><p><strong>%s</strong> %s</p></div>',
                esc_html__( 'Dazhen Blocks is standing by.', 'dazhen-blocks' ),
                esc_html__( 'Your active Dazhen theme (v0.2 or older) already includes a block kit. Update the Dazhen theme to v0.4 or newer and this plugin will take over automatically — no action needed.', 'dazhen-blocks' )
        );
}
