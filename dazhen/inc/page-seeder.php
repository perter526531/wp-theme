<?php
/**
 * One-click page seeder — fill empty pages with Dazhen block layouts.
 *
 * Non-technical owners should never face a blank editor. This module adds:
 *   - an admin notice on Pages listing every empty page, with a
 *     "fill all in one click" button,
 *   - a "Fill with Dazhen blocks" row action per empty page,
 *   - a "Dazhen layouts · 大振版式" panel in the page editor sidebar
 *     (pick any layout, fill or replace).
 *
 * Filling only writes block markup into post_content — clearing the canvas
 * in the editor instantly restores the theme's built-in template (the rule
 * in inc/template-tags.php: content = blocks, empty = designed template).
 *
 * @package Dazhen
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
 * Published pages with empty content (posts page excluded — its content
 * is never displayed, WP renders the blog index instead).
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
                return new WP_Error( 'dazhen_capability', __( 'Only administrators can fill layouts (your account lacks the unfiltered_html capability).', 'dazhen' ) );
        }

        $post = get_post( $post_id );
        if ( ! $post instanceof WP_Post || 'page' !== $post->post_type ) {
                return new WP_Error( 'dazhen_post', __( 'Layouts can only be filled into pages.', 'dazhen' ) );
        }

        $layout = $layout ? sanitize_key( $layout ) : dazhen_match_page_layout( $post );
        $markup = dazhen_get_block_layout( $layout );
        if ( null === $markup ) {
                return new WP_Error( 'dazhen_layout', __( 'Unknown layout.', 'dazhen' ) );
        }

        $result = wp_update_post(
                array(
                        'ID'           => $post_id,
                        'post_content' => $markup,
                ),
                true
        );

        if ( is_wp_error( $result ) ) {
                return $result;
        }

        return true;
}

/**
 * Stackable plugin active? (Add a friendly compatibility line when it is.)
 */
function dazhen_stackable_active(): bool {
        if ( ! function_exists( 'is_plugin_active' ) ) {
                return false;
        }
        foreach ( array(
                'stackable-ultimate-gutenberg-blocks/plugin.php',
                'stackable-ultimate-gutenberg-blocks/stackable-ultimate-gutenberg-blocks.php',
        ) as $plugin_path ) {
                if ( is_plugin_active( $plugin_path ) ) {
                        return true;
                }
        }
        return false;
}

/* ---------------------------------------------------------------------
 * Pages screen — notice + row actions.
 * ------------------------------------------------------------------- */

/**
 * Render the "empty pages" helper notice on the Pages list.
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
                $count = (int) $_GET['dazhen_filled']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                if ( $count > 0 ) {
                        $fill_url = admin_url( 'edit.php?post_type=page' );
                        echo '<div class="notice notice-success is-dismissible"><p><strong>' .
                                esc_html( sprintf(
                                        /* translators: %d: number of pages filled with block layouts. */
                                        _n( 'Filled %d page with Dazhen blocks.', 'Filled %d pages with Dazhen blocks.', $count, 'dazhen' ),
                                        $count
                                ) ) . '</strong> ' .
                                esc_html__( 'Open any page in the editor — every section, card and column is now drag-and-drop editable.', 'dazhen' ) .
                                ' <a href="' . esc_url( $fill_url ) . '">' . esc_html__( 'Refresh list', 'dazhen' ) . '</a></p></div>';
                }
        }
        if ( isset( $_GET['dazhen_fill_error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                echo '<div class="notice notice-error is-dismissible"><p>' .
                        esc_html__( 'Filling the layout failed:', 'dazhen' ) . ' ' .
                        esc_html( sanitize_text_field( wp_unslash( $_GET['dazhen_fill_error'] ) ) ) . // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                        '</p></div>';
        }

        /* Helper notice for remaining empty pages. */
        $empty = dazhen_get_empty_pages();
        if ( empty( $empty ) || get_option( 'dazhen_seed_notice_done' ) ) {
                return;
        }

        $fill_all_url = wp_nonce_url( admin_url( 'admin-post.php?action=dazhen_fill_all_empty' ), 'dazhen-fill-all' );
        $dismiss_url  = wp_nonce_url( admin_url( 'admin-post.php?action=dazhen_dismiss_seed_notice' ), 'dazhen-dismiss-seed' );
        ?>
        <div class="notice notice-info dazhen-seed-notice">
                <p>
                        <strong><?php esc_html_e( 'Dazhen blocks · 大振区块', 'dazhen' ); ?></strong>
                        <?php
                        echo esc_html( sprintf(
                                /* translators: %d: number of empty pages. */
                                _n( '%d page is still empty in the editor.', '%d pages are still empty in the editor.', count( $empty ), 'dazhen' ),
                                count( $empty )
                        ) );
                        ?>
                        <?php esc_html_e( 'Fill them with the ready-made Dazhen layouts, then edit anything by drag and drop.', 'dazhen' ); ?>
                </p>
                <p>
                        <a class="button button-primary" href="<?php echo esc_url( $fill_all_url ); ?>">
                                <?php echo esc_html( sprintf(
                                        /* translators: %d: number of empty pages. */
                                        __( 'Fill all empty pages (%d) · 一键填充', 'dazhen' ),
                                        count( $empty )
                                ) ); ?>
                        </a>
                        <a class="button" href="<?php echo esc_url( $dismiss_url ); ?>"><?php esc_html_e( 'Hide this hint · 隐藏提示', 'dazhen' ); ?></a>
                </p>
                <?php if ( dazhen_stackable_active() ) : ?>
                        <p class="dazhen-seed-notice__compat">
                                <span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
                                <?php esc_html_e( 'Stackable detected — fully compatible. Dazhen layouts are native blocks; mix in Stackable blocks freely.', 'dazhen' ); ?>
                        </p>
                <?php endif; ?>
        </div>
        <?php
}
add_action( 'admin_notices', 'dazhen_seed_screen_notice' );

/**
 * Add a "Fill with Dazhen blocks" row action on empty pages.
 */
function dazhen_seed_row_action( array $actions, WP_Post $post ): array {
        if ( ! current_user_can( 'edit_post', $post->ID ) || ! dazhen_page_content_is_empty( $post ) ) {
                return $actions;
        }
        if ( (int) get_option( 'page_for_posts' ) === $post->ID ) {
                return $actions;
        }

        $url = wp_nonce_url(
                admin_url( 'admin-post.php?action=dazhen_fill_page&post=' . $post->ID ),
                'dazhen-fill-' . $post->ID
        );

        $actions['dazhen_fill'] = '<a href="' . esc_url( $url ) . '" style="color:#0F766E;font-weight:600;">' .
                esc_html__( 'Fill with blocks · 填充区块模板', 'dazhen' ) . '</a>';

        return $actions;
}
add_filter( 'page_row_actions', 'dazhen_seed_row_action', 10, 2 );

/* ---------------------------------------------------------------------
 * Editor sidebar — layout picker panel.
 * ------------------------------------------------------------------- */

/**
 * Register the side meta box on pages.
 */
function dazhen_layout_meta_box(): void {
        add_meta_box(
                'dazhen-layouts',
                __( 'Dazhen layouts · 大振版式', 'dazhen' ),
                'dazhen_layout_meta_box_html',
                'page',
                'side',
                'high'
        );
}
add_action( 'add_meta_boxes', 'dazhen_layout_meta_box' );

/**
 * Render the layout picker.
 */
function dazhen_layout_meta_box_html( WP_Post $post ): void {
        $posts_page = (int) get_option( 'page_for_posts' );
        if ( $posts_page === $post->ID ) {
                echo '<p style="margin:0">' .
                        esc_html__( 'This is the blog index page — WordPress shows your posts here, so it needs no block layout. Edit it only to change the title.', 'dazhen' ) .
                        '</p>';
                return;
        }

        $is_empty = dazhen_page_content_is_empty( $post );
        $matched  = dazhen_match_page_layout( $post );
        $layouts  = dazhen_block_layouts();

        /*
         * NOTE: the block editor renders side meta boxes inside its own
         * <form>, so a nested <form> here would be stripped by the browser.
         * The action therefore travels as a GET link (like the row action),
         * with the chosen layout appended by a tiny inline handler.
         */
        $base_url = wp_nonce_url(
                admin_url( 'admin-post.php?action=dazhen_fill_page&post=' . $post->ID ),
                'dazhen-fill-' . $post->ID
        );

        if ( $is_empty ) {
                $onclick = 'var s=document.getElementById(\'dazhen-layout-select\');'
                        . 'this.href=this.dataset.base+\'&dazhen_layout=\'+encodeURIComponent(s.value);'
                        . 'return true;';
                $label   = __( 'Fill this page · 填入本页', 'dazhen' );
        } else {
                $confirm = esc_js( __( 'Replace the current content of this page? This cannot be undone with the editor Undo.', 'dazhen' ) );
                $tick    = esc_js( __( 'Tick “Replace the current content” first if you really want to swap the layout.', 'dazhen' ) );
                $onclick = 'var s=document.getElementById(\'dazhen-layout-select\'),c=document.getElementById(\'dazhen-replace-check\');'
                        . 'if(!c.checked){alert(\'' . $tick . '\');return false;}'
                        . 'if(!confirm(\'' . $confirm . '\')){return false;}'
                        . 'this.href=this.dataset.base+\'&dazhen_layout=\'+encodeURIComponent(s.value)+\'&dazhen_replace=1\';'
                        . 'return true;';
                $label   = __( 'Apply layout · 应用版式', 'dazhen' );
        }
        ?>
        <p style="margin-top:0">
                <?php if ( $is_empty ) : ?>
                        <strong style="color:#0F766E"><?php esc_html_e( 'This page is empty.', 'dazhen' ); ?></strong><br>
                        <?php esc_html_e( 'Pick a ready-made layout and fill it in one click — then edit every block visually.', 'dazhen' ); ?>
                <?php else : ?>
                        <strong><?php esc_html_e( 'This page already uses blocks.', 'dazhen' ); ?></strong><br>
                        <?php esc_html_e( 'Clearing the whole canvas in the editor restores the theme’s built-in template. To swap the layout instead, tick the box below and pick another one.', 'dazhen' ); ?>
                <?php endif; ?>
        </p>

        <label for="dazhen-layout-select" style="display:block;margin-bottom:4px"><strong><?php esc_html_e( 'Layout', 'dazhen' ); ?></strong></label>
        <select id="dazhen-layout-select" name="dazhen_layout" style="width:100%;max-width:100%">
                <?php foreach ( $layouts as $slug => $layout ) : ?>
                        <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $slug, $matched ); ?>>
                                <?php echo esc_html( $layout['title'] ); ?>
                        </option>
                <?php endforeach; ?>
        </select>

        <?php if ( ! $is_empty ) : ?>
                <p style="margin:10px 0 6px">
                        <label>
                                <input type="checkbox" id="dazhen-replace-check" value="1">
                                <?php esc_html_e( 'Replace the current content', 'dazhen' ); ?>
                        </label>
                </p>
        <?php endif; ?>

        <p style="margin:10px 0 0">
                <a class="button button-primary" style="display:block;width:100%;text-align:center"
                        data-base="<?php echo esc_url( $base_url ); ?>"
                        href="<?php echo esc_url( $base_url ); ?>"
                        onclick="<?php echo esc_attr( $onclick ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled from esc_js/esc_attr-safe parts above. ?>">
                        <?php echo esc_html( $label ); ?>
                </a>
        </p>

        <?php if ( dazhen_stackable_active() ) : ?>
                <p style="margin:10px 0 0">
                        <span class="dashicons dashicons-yes-alt" style="font-size:16px;width:16px;height:16px;color:#0F766E" aria-hidden="true"></span>
                        <?php esc_html_e( 'Stackable detected — fully compatible, mix freely.', 'dazhen' ); ?>
                </p>
        <?php endif; ?>
        <?php
}

/* ---------------------------------------------------------------------
 * Handlers.
 * ------------------------------------------------------------------- */

/**
 * Handle the single-page fill (row action or editor panel).
 */
function dazhen_handle_fill_page(): void {
        $post_id = isset( $_REQUEST['post'] ) ? (int) $_REQUEST['post'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- checked below.
        if ( $post_id < 1 ) {
                wp_die( esc_html__( 'Missing page.', 'dazhen' ) );
        }
        check_admin_referer( 'dazhen-fill-' . $post_id );

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
                wp_die( esc_html__( 'You are not allowed to edit this page.', 'dazhen' ) );
        }

        $replace = ! empty( $_REQUEST['dazhen_replace'] );
        if ( ! $replace && ! dazhen_page_content_is_empty( $post_id ) ) {
                wp_safe_redirect( add_query_arg(
                        'dazhen_fill_error',
                        rawurlencode( __( 'The page already has content — tick “Replace the current content” in the Dazhen layouts panel to overwrite it.', 'dazhen' ) ),
                        admin_url( 'post.php?post=' . $post_id . '&action=edit' )
                ) );
                exit;
        }

        $layout = isset( $_REQUEST['dazhen_layout'] ) ? sanitize_key( wp_unslash( $_REQUEST['dazhen_layout'] ) ) : null;
        $result = dazhen_seed_page( $post_id, $layout );

        if ( is_wp_error( $result ) ) {
                wp_safe_redirect( add_query_arg(
                        'dazhen_fill_error',
                        rawurlencode( $result->get_error_message() ),
                        admin_url( 'post.php?post=' . $post_id . '&action=edit' )
                ) );
                exit;
        }

        wp_safe_redirect( add_query_arg(
                array(
                        'post'           => $post_id,
                        'action'         => 'edit',
                        'dazhen_filled'  => '1',
                ),
                admin_url( 'post.php' )
        ) );
        exit;
}
add_action( 'admin_post_dazhen_fill_page', 'dazhen_handle_fill_page' );

/**
 * Handle the bulk "fill all empty pages" button.
 */
function dazhen_handle_fill_all(): void {
        check_admin_referer( 'dazhen-fill-all' );

        if ( ! current_user_can( 'edit_others_pages' ) ) {
                wp_die( esc_html__( 'You are not allowed to edit pages.', 'dazhen' ) );
        }

        $count = 0;
        $error = '';
        foreach ( dazhen_get_empty_pages() as $page ) {
                $result = dazhen_seed_page( $page->ID );
                if ( is_wp_error( $result ) ) {
                        $error = $result->get_error_message();
                        break;
                }
                ++$count;
        }

        $back = wp_get_referer() ?: admin_url( 'edit.php?post_type=page' );
        if ( '' !== $error ) {
                wp_safe_redirect( add_query_arg( 'dazhen_fill_error', rawurlencode( $error ), $back ) );
                exit;
        }
        wp_safe_redirect( add_query_arg( 'dazhen_filled', $count, $back ) );
        exit;
}
add_action( 'admin_post_dazhen_fill_all_empty', 'dazhen_handle_fill_all' );

/**
 * Handle the notice dismiss button.
 */
function dazhen_handle_dismiss_notice(): void {
        check_admin_referer( 'dazhen-dismiss-seed' );

        if ( ! current_user_can( 'edit_pages' ) ) {
                wp_die( esc_html__( 'You are not allowed to edit pages.', 'dazhen' ) );
        }

        update_option( 'dazhen_seed_notice_done', 1, false );
        wp_safe_redirect( wp_get_referer() ?: admin_url( 'edit.php?post_type=page' ) );
        exit;
}
add_action( 'admin_post_dazhen_dismiss_seed_notice', 'dazhen_handle_dismiss_notice' );

/**
 * Success notice inside the editor after a fill.
 */
function dazhen_editor_fill_notice(): void {
        if ( ! isset( $_GET['dazhen_filled'] ) || '1' !== (string) $_GET['dazhen_filled'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flag.
                return;
        }

        echo '<div class="notice notice-success is-dismissible"><p><strong>' .
                esc_html__( 'Dazhen blocks inserted!', 'dazhen' ) . '</strong> ' .
                esc_html__( 'Scroll the canvas — every section, card and column is editable. Click any text to rewrite it; drag blocks to rearrange.', 'dazhen' ) .
                '</p></div>';
}
add_action( 'admin_notices', 'dazhen_editor_fill_notice' );
