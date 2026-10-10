<?php
/**
 * Global Colors — one palette, every block follows (Stackable-style).
 *
 * The review against Stackable / Elementor flagged the biggest flow gap:
 * every block stored raw hex values, so a brand re-color meant editing
 * hundreds of attributes. This module introduces site-wide design tokens:
 *
 *   - Six color slots stored in the `kbl3_global_colors` option.
 *   - Rendered as CSS custom properties (`--kbl-gc-1` … `--kbl-gc-6`) on
 *     the front end AND inside the editor canvas, so a block that saved
 *     `color: var(--kbl-gc-1)` re-skins instantly when the slot changes.
 *   - The editor color pickers offer the global swatches first (injected
 *     through window.KBL3_ENV — see assets.php + inspector.js).
 *   - A "Kabel → Global Colors" admin page manages the slots; non-admins
 *     never see it, and deactivation keeps the option (colors survive).
 *
 * The emitter twins are untouched: a global color is just a string value
 * (`var(--kbl-gc-N)`) inside the existing color attributes, serialized
 * byte-identically by JS save() and the PHP emitter.
 *
 * @package Kabel_Blocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * The six default slots — mirrors the Kabel design tokens (bronze accents
 * on near-black), so a fresh install already matches the shipped layouts.
 *
 * @return array<int, array{name:string,color:string}>
 */
function kbl3_global_color_defaults(): array {
        return array(
                array( 'name' => __( 'Accent — Bronze', 'kabel-blocks' ), 'color' => '#B39A74' ),
                array( 'name' => __( 'Accent — Bronze bright', 'kabel-blocks' ), 'color' => '#D3BC97' ),
                array( 'name' => __( 'Ink — Near black', 'kabel-blocks' ), 'color' => '#0B0B0D' ),
                array( 'name' => __( 'Ink — Navy black', 'kabel-blocks' ), 'color' => '#101828' ),
                array( 'name' => __( 'Navy', 'kabel-blocks' ), 'color' => '#222E5E' ),
                array( 'name' => __( 'Light — White', 'kabel-blocks' ), 'color' => '#FFFFFF' ),
        );
}

/**
 * The saved global colors, normalized.
 *
 * @return array<int, array{name:string,color:string,var:string,slug:string}>
 */
function kbl3_global_colors(): array {
        static $cache = null;
        if ( null !== $cache ) {
                return $cache;
        }

        $raw = get_option( 'kbl3_global_colors' );
        $out = array();

        if ( is_array( $raw ) && $raw ) {
                $i = 0;
                foreach ( $raw as $slot ) {
                        $i++;
                        if ( ! is_array( $slot ) ) {
                                continue;
                        }
                        $color = isset( $slot['color'] ) ? sanitize_hex_color( (string) $slot['color'] ) : '';
                        if ( ! $color ) {
                                $color = '#000000';
                        }
                        $out[] = array(
                                'name'  => isset( $slot['name'] ) ? sanitize_text_field( (string) $slot['name'] ) : sprintf( /* translators: %d: slot number. */ __( 'Color %d', 'kabel-blocks' ), $i ),
                                'color' => strtoupper( $color ),
                                'var'   => 'var(--kbl-gc-' . $i . ')',
                                'slug'  => 'gc-' . $i,
                        );
                }
                return $cache = $out;
        }

        /* Fresh site: derive from the defaults, keeping names translatable. */
        $i = 0;
        foreach ( kbl3_global_color_defaults() as $slot ) {
                $i++;
                $out[] = array(
                        'name'  => $slot['name'],
                        'color' => $slot['color'],
                        'var'   => 'var(--kbl-gc-' . $i . ')',
                        'slug'  => 'gc-' . $i,
                );
        }
        return $cache = $out;
}

/**
 * The `:root { … }` rule exposing every slot as a CSS variable.
 */
function kbl3_global_colors_css(): string {
        $rules = '';
        foreach ( kbl3_global_colors() as $i => $gc ) {
                $rules .= '--kbl-gc-' . ( $i + 1 ) . ':' . $gc['color'] . ';';
        }
        return ':root{' . $rules . '}';
}

/**
 * Front end: attach the variables to the always-enqueued v3 skin.
 */
function kbl3_global_colors_front_css(): void {
        wp_add_inline_style( 'kabel-blocks-v3', kbl3_global_colors_css() );
}
add_action( 'wp_enqueue_scripts', 'kbl3_global_colors_front_css', 20 );

/**
 * Editor canvas (apiVersion 3 iframe): the same variables inside the iframe
 * document, attached to the mirrored skin handle. Parent page + iframe are
 * separate requests, so each document prints the rule exactly once.
 */
function kbl3_global_colors_editor_css(): void {
        if ( ! is_admin() ) {
                return;
        }
        wp_add_inline_style( 'kabel-blocks-v3', kbl3_global_colors_css() );
}
add_action( 'enqueue_block_assets', 'kbl3_global_colors_editor_css', 21 );

/* ---------------------------------------------------------------------
 * Settings page — "Kabel → Global Colors"
 * ------------------------------------------------------------------- */

/**
 * Register the submenu (the top-level "Kabel" menu lives in
 * inc/layout-library.php and registers at admin_menu priority 9 — the
 * parent's self-referencing "Layout Library" submenu must stay first).
 */
function kbl3_global_colors_menu(): void {
        add_submenu_page(
                'kabel-layout-library',
                __( 'Global Colors', 'kabel-blocks' ),
                __( 'Global Colors', 'kabel-blocks' ),
                'manage_options',
                'kabel-global-colors',
                'kbl3_global_colors_page'
        );
}
add_action( 'admin_menu', 'kbl3_global_colors_menu', 10 );

/**
 * The page markup: six editable slots + reset + an explanation of the
 * "change once, every block follows" contract.
 */
function kbl3_global_colors_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
                return;
        }

        $updated = isset( $_GET['kbl_gc_saved'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flag.
        $reset   = isset( $_GET['kbl_gc_reset'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flag.
        $colors  = kbl3_global_colors();
        ?>
        <div class="wrap kbl-gc-wrap">
                <h1><?php esc_html_e( 'Kabel Global Colors', 'kabel-blocks' ); ?></h1>

                <?php if ( $updated ) : ?>
                        <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Global colors saved — every block that uses a slot has already re-skinned.', 'kabel-blocks' ); ?></p></div>
                <?php elseif ( $reset ) : ?>
                        <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Global colors reset to the Kabel defaults.', 'kabel-blocks' ); ?></p></div>
                <?php endif; ?>

                <p class="kbl-gc-lead">
                        <?php esc_html_e( 'Set your brand palette once. Every Kabel block that picks a global color follows this page instantly — no block-by-block editing, no broken leftovers. In the editor, global swatches appear at the top of every color picker.', 'kabel-blocks' ); ?>
                </p>

                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                        <input type="hidden" name="action" value="kabel_save_global_colors">
                        <?php wp_nonce_field( 'kabel_save_global_colors' ); ?>

                        <table class="form-table kbl-gc-table" role="presentation">
                                <?php foreach ( $colors as $i => $gc ) : ?>
                                        <tr>
                                                <th scope="row">
                                                        <label for="kbl_gc_name_<?php echo (int) $i; ?>"><?php printf( esc_html__( 'Slot %d', 'kabel-blocks' ), $i + 1 ); ?></label>
                                                </th>
                                                <td class="kbl-gc-slot">
                                                        <input type="text" class="regular-text kbl-gc-name" id="kbl_gc_name_<?php echo (int) $i; ?>" name="kbl_gc[<?php echo (int) $i; ?>][name]" value="<?php echo esc_attr( $gc['name'] ); ?>" placeholder="<?php esc_attr_e( 'Color name', 'kabel-blocks' ); ?>">
                                                        <input type="color" class="kbl-gc-picker" name="kbl_gc[<?php echo (int) $i; ?>][color]" value="<?php echo esc_attr( $gc['color'] ); ?>" aria-label="<?php echo esc_attr( $gc['name'] ); ?>">
                                                        <code class="kbl-gc-var">var(--kbl-gc-<?php echo (int) ( $i + 1 ); ?>)</code>
                                                        <code class="kbl-gc-hex"><?php echo esc_html( $gc['color'] ); ?></code>
                                                </td>
                                        </tr>
                                <?php endforeach; ?>
                        </table>

                        <p class="submit kbl-gc-actions">
                                <?php submit_button( __( 'Save global colors', 'kabel-blocks' ), 'primary', 'kbl_gc_submit', false ); ?>
                        </p>
                </form>

                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( __( 'Reset all six slots to the Kabel defaults?', 'kabel-blocks' ) ); ?>');">
                        <input type="hidden" name="action" value="kabel_save_global_colors">
                        <input type="hidden" name="kbl_gc_reset" value="1">
                        <?php wp_nonce_field( 'kabel_save_global_colors' ); ?>
                        <button type="submit" class="button button-link-delete"><?php esc_html_e( 'Reset to Kabel defaults', 'kabel-blocks' ); ?></button>
                </form>
        </div>

        <style>
                .kbl-gc-lead { max-width: 680px; font-size: 14px; color: #3c434a; }
                .kbl-gc-slot { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
                .kbl-gc-picker { width: 52px; height: 36px; padding: 2px; border: 1px solid #8c8f94; border-radius: 3px; background: #fff; cursor: pointer; }
                .kbl-gc-var { background: #f0f0f1; padding: 4px 8px; border-radius: 3px; }
                .kbl-gc-hex { background: transparent; padding: 4px 0; }
                .kbl-gc-actions { margin-top: 8px; }
        </style>
        <?php
}

/**
 * Save (or reset) the slots.
 */
function kbl3_handle_save_global_colors(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
                wp_safe_redirect( admin_url() );
                exit;
        }
        check_admin_referer( 'kabel_save_global_colors' );

        $back = admin_url( 'admin.php?page=kabel-global-colors' );

        if ( isset( $_POST['kbl_gc_reset'] ) ) {
                delete_option( 'kbl3_global_colors' );
                wp_safe_redirect( add_query_arg( 'kbl_gc_reset', '1', $back ) );
                exit;
        }

        $slots = isset( $_POST['kbl_gc'] ) && is_array( $_POST['kbl_gc'] ) ? wp_unslash( $_POST['kbl_gc'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized below.
        $save  = array();
        foreach ( $slots as $slot ) {
                if ( ! is_array( $slot ) ) {
                        continue;
                }
                $color = isset( $slot['color'] ) ? sanitize_hex_color( (string) $slot['color'] ) : '';
                $save[] = array(
                        'name'  => isset( $slot['name'] ) ? sanitize_text_field( (string) $slot['name'] ) : '',
                        'color' => $color ? $color : '#000000',
                );
        }
        if ( $save ) {
                update_option( 'kbl3_global_colors', array_values( $save ), false );
        }

        wp_safe_redirect( add_query_arg( 'kbl_gc_saved', '1', $back ) );
        exit;
}
add_action( 'admin_post_kabel_save_global_colors', 'kbl3_handle_save_global_colors' );
