<?php
/**
 * One-click page seeder — fill empty pages with Dazhen block layouts.
 *
 * Non-technical owners should never face a blank editor:
 *   - Pages list: a notice listing empty pages + "fill all in one click"
 *   - Row action: "Fill with Dazhen layout" per empty page
 *   - Editor sidebar: "Dazhen layouts" panel (pick any layout, fill or
 *     replace) — rendered by the generic runtime in blocks-editor.js
 *
 * Filling writes our own block markup into post_content. On the Dazhen theme
 * a cleared canvas instantly restores the theme's built-in PHP template
 * (content = blocks, empty = designed template).
 *
 * @package Dazhen_Blocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Is this page's content empty?
 */
function dazhen_page_content_is_empty( $post ): bool {
        $post = get_post( $post );
        return $post instanceof WP_Post && '' === trim( (string) $post->post_content );
}

/**
 * Published pages with empty content (posts page excluded).
 *
 * @return WP_Post[]
 */
function dazhen_get_empty_pages(): array {
        $posts_page = (int) get_option( 'page_for_posts' );
        $pages      = get_pages( array( 'post_status' => 'publish' ) );

        return array_values( array_filter(
                $pages,
                static function ( WP_Post $page ) use ( $posts_page ): bool {
                        return $page->ID !== $posts_page && '' === trim( (string) $page->post_content );
                }
        ) );
}

/**
 * Seed a page with a layout's block markup.
 *
 * @return true|WP_Error
 */
function dazhen_seed_page( int $post_id, ?string $layout = null ) {
        if ( ! current_user_can( 'unfiltered_html' ) ) {
                // Without unfiltered_html, KSES would strip the block delimiters.
                return new WP_Error( 'dazhen_capability', __( 'Only administrators can fill layouts (your account lacks the unfiltered_html capability).', 'dazhen-blocks' ) );
        }

        $post = get_post( $post_id );
        if ( ! $post instanceof WP_Post || 'page' !== $post->post_type ) {
                return new WP_Error( 'dazhen_post', __( 'Layouts can only be filled into pages.', 'dazhen-blocks' ) );
        }

        $layout = $layout ? sanitize_key( $layout ) : dazhen_match_page_layout( $post );
        $markup = dazhen_get_block_layout( $layout );
        if ( null === $markup ) {
                return new WP_Error( 'dazhen_layout', __( 'Unknown layout.', 'dazhen-blocks' ) );
        }

        /*
         * Write with $wpdb directly, importer-style. wp_update_post() runs the
         * content through sanitize_post() → content_save_pre filters, and one of
         * them strips the JSON backslash escapes inside block comments
         * (\n → n, \u2014 → u2014), corrupting every attribute that carries a
         * newline or a non-ASCII character. A raw column update skips all
         * content filters — the capability + nonce checks above are the gate.
         */
        global $wpdb;
        $updated = $wpdb->update(
                $wpdb->posts,
                array(
                        'post_content'      => $markup,
                        'post_modified'     => current_time( 'mysql' ),
                        'post_modified_gmt' => current_time( 'mysql', true ),
                ),
                array( 'ID' => $post_id )
        );

        if ( false === $updated ) {
                return new WP_Error( 'dazhen_db', __( 'Could not write the layout into the page.', 'dazhen-blocks' ) );
        }

        clean_post_cache( $post_id );

        return true;
}

/* ---------------------------------------------------------------------
 * Pages screen — notice + row actions
 * ------------------------------------------------------------------- */

/**
 * The "empty pages" helper notice on the Pages list.
 */
function dazhen_seed_screen_notice(): void {
        if ( ! function_exists( 'get_current_screen' ) ) {
                return;
        }
        $screen = get_current_screen();
        if ( ! $screen || 'edit' !== $screen->base || 'page' !== $screen->post_type ) {
                return;
        }

        /* Result notices (redirects from the handlers). */
        if ( isset( $_GET['dazhen_filled'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flag.
                $n = (int) $_GET['dazhen_filled'];
                printf(
                        '<div class="notice notice-success is-dismissible"><p><strong>%s</strong> %s</p></div>',
                        esc_html( sprintf( _n( '%d page filled.', '%d pages filled.', $n, 'dazhen-blocks' ), $n ) ),
                        esc_html__( 'Open any of them in the editor — every section is now a Dazhen block you can restyle.', 'dazhen-blocks' )
                );
                return;
        }
        if ( isset( $_GET['dazhen_error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flag.
                printf(
                        '<div class="notice notice-error is-dismissible"><p>%s</p></div>',
                        esc_html__( 'Filling failed — only administrator accounts with unfiltered_html can fill layouts.', 'dazhen-blocks' )
                );
                return;
        }

        /* The standing helper notice. */
        $dismissed = (string) get_option( 'dazhen_seeder_notice_off', '' );
        if ( '1' === $dismissed ) {
                return;
        }

        $empty = dazhen_get_empty_pages();
        if ( ! $empty ) {
                return;
        }

        $fill_url = wp_nonce_url(
                add_query_arg( 'action', 'dazhen_fill_all', admin_url( 'admin-post.php' ) ),
                'dazhen_fill_all'
        );
        $dismiss_url = wp_nonce_url(
                add_query_arg( 'action', 'dazhen_dismiss_notice', admin_url( 'admin-post.php' ) ),
                'dazhen_dismiss_notice'
        );
        ?>
        <div class="notice notice-info dazhen-seed-notice">
                <p>
                        <strong><?php echo esc_html( sprintf( _n( '%d page still empty.', '%d pages are still empty.', count( $empty ), 'dazhen-blocks' ), count( $empty ) ) ); ?></strong>
                        <?php
                        echo esc_html( _n( 'Fill it with a Dazhen layout so it becomes drag-and-drop editable?', 'Fill them with Dazhen layouts so they become drag-and-drop editable?', count( $empty ), 'dazhen-blocks' ) );
                        ?>
                </p>
                <p>
                        <a class="button button-primary" href="<?php echo esc_url( $fill_url ); ?>"><?php esc_html_e( 'Fill all empty pages · 一键填充', 'dazhen-blocks' ); ?></a>
                        <a class="button" href="<?php echo esc_url( $dismiss_url ); ?>"><?php esc_html_e( 'Dismiss', 'dazhen-blocks' ); ?></a>
                </p>
        </div>
        <?php
}
add_action( 'admin_notices', 'dazhen_seed_screen_notice' );

/**
 * Row action: "Fill with Dazhen layout" on empty pages.
 */
function dazhen_seed_row_action( array $actions, WP_Post $post ): array {
        if ( 'page' !== $post->post_type || ! dazhen_page_content_is_empty( $post ) ) {
                return $actions;
        }
        if ( ! current_user_can( 'edit_post', $post->ID ) || ! current_user_can( 'unfiltered_html' ) ) {
                return $actions;
        }

        $url = wp_nonce_url(
                add_query_arg(
                        array(
                                'action'  => 'dazhen_fill_page',
                                'page_ID' => $post->ID,
                        ),
                        admin_url( 'admin-post.php' )
                ),
                'dazhen_fill_page_' . $post->ID
        );

        $actions['dazhen_fill'] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Fill with Dazhen layout · 填充区块模板', 'dazhen-blocks' ) . '</a>';
        return $actions;
}
add_filter( 'page_row_actions', 'dazhen_seed_row_action', 10, 2 );

/* ---------------------------------------------------------------------
 * Handlers
 * ------------------------------------------------------------------- */

/**
 * Fill one page (row action / editor sidebar).
 */
function dazhen_handle_fill_page(): void {
        if ( ! current_user_can( 'edit_pages' ) ) {
                wp_safe_redirect( add_query_arg( 'dazhen_error', '1', admin_url( 'edit.php?post_type=page' ) ) );
                exit;
        }

        $post_id = isset( $_GET['page_ID'] ) ? (int) $_GET['page_ID'] : 0;
        check_admin_referer( 'dazhen_fill_page_' . $post_id );

        $layout = isset( $_GET['layout'] ) ? sanitize_key( (string) $_GET['layout'] ) : null;
        $result = dazhen_seed_page( $post_id, $layout );

        $back = get_edit_post_link( $post_id, 'raw' );
        if ( ! $back ) {
                $back = admin_url( 'edit.php?post_type=page' );
        }

        if ( is_wp_error( $result ) ) {
                wp_safe_redirect( add_query_arg( 'dazhen_error', '1', admin_url( 'edit.php?post_type=page' ) ) );
                exit;
        }

        wp_safe_redirect( add_query_arg( 'dazhen_filled', '1', $back ) );
        exit;
}
add_action( 'admin_post_dazhen_fill_page', 'dazhen_handle_fill_page' );

/**
 * Fill every empty page.
 */
function dazhen_handle_fill_all(): void {
        if ( ! current_user_can( 'edit_pages' ) ) {
                wp_safe_redirect( add_query_arg( 'dazhen_error', '1', admin_url( 'edit.php?post_type=page' ) ) );
                exit;
        }
        check_admin_referer( 'dazhen_fill_all' );

        $filled = 0;
        foreach ( dazhen_get_empty_pages() as $page ) {
                if ( ! is_wp_error( dazhen_seed_page( $page->ID ) ) ) {
                        $filled++;
                }
        }

        wp_safe_redirect( add_query_arg( 'dazhen_filled', (string) $filled, admin_url( 'edit.php?post_type=page' ) ) );
        exit;
}
add_action( 'admin_post_dazhen_fill_all', 'dazhen_handle_fill_all' );

/**
 * Dismiss the standing notice.
 */
function dazhen_handle_dismiss_notice(): void {
        if ( ! current_user_can( 'edit_pages' ) ) {
                wp_safe_redirect( admin_url( 'edit.php?post_type=page' ) );
                exit;
        }
        check_admin_referer( 'dazhen_dismiss_notice' );

        update_option( 'dazhen_seeder_notice_off', '1', false );
        wp_safe_redirect( admin_url( 'edit.php?post_type=page' ) );
        exit;
}
add_action( 'admin_post_dazhen_dismiss_notice', 'dazhen_handle_dismiss_notice' );

/* ---------------------------------------------------------------------
 * Editor sidebar payload
 * ------------------------------------------------------------------- */

/**
 * The layouts list for the editor sidebar panel (generic runtime renders it).
 *
 * @return array<int, array{value:string,label:string}>
 */
function dazhen_layout_options(): array {
        $out = array();
        foreach ( dazhen_block_layouts() as $slug => $data ) {
                $out[] = array(
                        'value' => $slug,
                        'label' => $data['label'],
                );
        }
        return $out;
}
