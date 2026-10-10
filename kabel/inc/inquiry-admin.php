<?php
/**
 * Inquiry admin — list columns, status management, CSV export, menu badge.
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Status labels shared across list, editor and export.
 *
 * @return array<string,string>
 */
function kabel_inquiry_statuses(): array {
        return array(
                'new'      => __( 'New', 'kabel' ),
                'following' => __( 'Following up', 'kabel' ),
                'won'      => __( 'Won', 'kabel' ),
        );
}

/**
 * List table columns.
 */
function kabel_inquiry_columns( array $columns ): array {
        return array(
                'cb'           => $columns['cb'] ?? '',
                'title'        => __( 'Name / Company', 'kabel' ),
                'country'      => __( 'Country', 'kabel' ),
                'project_type' => __( 'Project Type', 'kabel' ),
                'product'      => __( 'Product', 'kabel' ),
                'status'       => __( 'Status', 'kabel' ),
                'date'         => __( 'Received', 'kabel' ),
        );
}
add_filter( 'manage_kabel_inquiry_posts_columns', 'kabel_inquiry_columns' );

/**
 * Render custom columns.
 */
function kabel_inquiry_columns_content( string $column, int $post_id ): void {
        switch ( $column ) {
                case 'country':
                        echo esc_html( get_post_meta( $post_id, '_kb_inq_country', true ) );
                        break;
                case 'project_type':
                        $types = kabel_inquiry_project_types();
                        $type  = (string) get_post_meta( $post_id, '_kb_inq_project_type', true );
                        echo esc_html( $types[ $type ] ?? '—' );
                        break;
                case 'product':
                        echo esc_html( get_post_meta( $post_id, '_kb_inq_product', true ) );
                        break;
                case 'status':
                        $status    = get_post_meta( $post_id, '_kb_inq_status', true );
                        $statuses  = kabel_inquiry_statuses();
                        $classes   = array(
                                'new'      => 'kb-badge kb-badge--new',
                                'following' => 'kb-badge kb-badge--following',
                                'won'      => 'kb-badge kb-badge--won',
                        );
                        printf(
                                '<span class="%s">%s</span>',
                                esc_attr( $classes[ $status ] ?? 'kb-badge' ),
                                esc_html( $statuses[ $status ] ?? $status )
                        );
                        break;
        }
}
add_action( 'manage_kabel_inquiry_posts_custom_column', 'kabel_inquiry_columns_content', 10, 2 );

/**
 * Status filter dropdown above the list.
 */
function kabel_inquiry_status_filter(): void {
        global $typenow;
        if ( 'kabel_inquiry' !== $typenow ) {
                return;
        }
        $current = isset( $_GET['kb_status'] ) ? sanitize_key( wp_unslash( $_GET['kb_status'] ) ) : '';
        ?>
        <select name="kb_status">
                <option value=""><?php esc_html_e( 'All statuses', 'kabel' ); ?></option>
                <?php foreach ( kabel_inquiry_statuses() as $slug => $label ) : ?>
                        <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $current, $slug ); ?>>
                                <?php echo esc_html( $label ); ?>
                        </option>
                <?php endforeach; ?>
        </select>
        <?php
}
add_action( 'restrict_manage_posts', 'kabel_inquiry_status_filter' );

/**
 * Apply the status filter to the query.
 */
function kabel_inquiry_filter_query( WP_Query $query ): void {
        global $pagenow;
        if ( ! is_admin() || 'edit.php' !== $pagenow || ! $query->is_main_query() ) {
                return;
        }
        if ( ( $query->get( 'post_type' ) ?? '' ) !== 'kabel_inquiry' ) {
                return;
        }
        $status = isset( $_GET['kb_status'] ) ? sanitize_key( wp_unslash( $_GET['kb_status'] ) ) : '';
        if ( $status ) {
                $query->set( 'meta_query', array(
                        array(
                                'key'   => '_kb_inq_status',
                                'value' => $status,
                        ),
                ) );
        }
}
add_action( 'pre_get_posts', 'kabel_inquiry_filter_query' );

/**
 * Status metabox on the inquiry detail screen + read-only summary.
 */
function kabel_inquiry_edit_metaboxes(): void {
        add_meta_box(
                'kabel_inquiry_status',
                __( 'Follow-up', 'kabel' ),
                'kabel_inquiry_status_metabox',
                'kabel_inquiry',
                'side',
                'high'
        );
}
add_action( 'add_meta_boxes', 'kabel_inquiry_edit_metaboxes' );

/**
 * Render the status metabox.
 */
function kabel_inquiry_status_metabox( WP_Post $post ): void {
        wp_nonce_field( 'kabel_inquiry_status', 'kabel_inquiry_status_nonce' );
        $status = get_post_meta( $post->ID, '_kb_inq_status', true ) ?: 'new';
        ?>
        <p>
                <label for="kb_status"><strong><?php esc_html_e( 'Status', 'kabel' ); ?></strong></label><br>
                <select name="kb_status" id="kb_status" style="width:100%;">
                        <?php foreach ( kabel_inquiry_statuses() as $slug => $label ) : ?>
                                <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $status, $slug ); ?>><?php echo esc_html( $label ); ?></option>
                        <?php endforeach; ?>
                </select>
        </p>
        <?php
        $project_type = (string) get_post_meta( $post->ID, '_kb_inq_project_type', true );
        $types        = kabel_inquiry_project_types();
        if ( $project_type && isset( $types[ $project_type ] ) ) {
                printf( '<p style="font-size:12px;color:#666;">%s<br><strong>%s</strong></p>', esc_html__( 'Project type:', 'kabel' ), esc_html( $types[ $project_type ] ) );
        }
        $email = get_post_meta( $post->ID, '_kb_inq_email', true );
        if ( $email ) {
                printf( '<p><a class="button" href="mailto:%s">%s</a></p>', esc_attr( $email ), esc_html__( 'Reply by email', 'kabel' ) );
        }
        $source = get_post_meta( $post->ID, '_kb_inq_source', true );
        if ( $source ) {
                printf( '<p style="font-size:12px;color:#666;">%s<br><a href="%s">%s</a></p>', esc_html__( 'Submitted from:', 'kabel' ), esc_url( $source ), esc_html( wp_parse_url( $source, PHP_URL_HOST ) . ( wp_parse_url( $source, PHP_URL_PATH ) ?? '' ) ) );
        }
}

/**
 * Save the status metabox.
 */
function kabel_inquiry_save_status( int $post_id ): void {
        if ( 'kabel_inquiry' !== get_post_type( $post_id ) ) {
                return;
        }
        $nonce = $_POST['kabel_inquiry_status_nonce'] ?? '';
        if ( ! is_string( $nonce ) || ! wp_verify_nonce( $nonce, 'kabel_inquiry_status' ) ) {
                return;
        }
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
        }
        $status = isset( $_POST['kb_status'] ) ? sanitize_key( wp_unslash( $_POST['kb_status'] ) ) : 'new';
        if ( array_key_exists( $status, kabel_inquiry_statuses() ) ) {
                update_post_meta( $post_id, '_kb_inq_status', $status );
        }
}
add_action( 'save_post', 'kabel_inquiry_save_status' );

/**
 * New-inquiry count badge on the admin menu.
 */
function kabel_inquiry_menu_badge(): void {
        global $menu;
        $count = (int) get_transient( 'kabel_new_inquiry_count' );
        if ( $count < 1 ) {
                return;
        }
        foreach ( $menu as &$item ) {
                if ( 'edit.php?post_type=kabel_inquiry' === $item[2] ) {
                        $item[0] .= sprintf(
                                ' <span class="awaiting-mod count-%1$d"><span class="pending-count">%1$d</span></span>',
                                $count
                        );
                        break;
                }
        }
}
add_action( 'admin_menu', 'kabel_inquiry_menu_badge', 99 );

/**
 * Keep the new-inquiry count fresh.
 */
function kabel_inquiry_count_refresh( int $post_id ): void {
        if ( 'kabel_inquiry' !== get_post_type( $post_id ) ) {
                return;
        }
        $query = new WP_Query( array(
                'post_type'      => 'kabel_inquiry',
                'post_status'    => 'private',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'meta_query'     => array(
                        array( 'key' => '_kb_inq_status', 'value' => 'new' ),
                ),
        ) );
        set_transient( 'kabel_new_inquiry_count', $query->found_posts, HOUR_IN_SECONDS );
}
add_action( 'save_post', 'kabel_inquiry_count_refresh', 20 );
add_action( 'after_delete_post', 'kabel_inquiry_count_refresh' );

/**
 * CSV export page under Inquiries.
 */
function kabel_inquiry_export_menu(): void {
        add_submenu_page(
                'edit.php?post_type=kabel_inquiry',
                __( 'Export CSV', 'kabel' ),
                __( 'Export CSV', 'kabel' ),
                'manage_options',
                'kabel-inquiry-export',
                'kabel_inquiry_export_download'
        );
}
add_action( 'admin_menu', 'kabel_inquiry_export_menu' );

/**
 * Stream the CSV file.
 */
function kabel_inquiry_export_download(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( esc_html__( 'You are not allowed to export inquiries.', 'kabel' ) );
        }

        $query = new WP_Query( array(
                'post_type'      => 'kabel_inquiry',
                'post_status'    => 'private',
                'posts_per_page' => -1,
                'orderby'        => 'date',
                'order'          => 'DESC',
        ) );

        $statuses = kabel_inquiry_statuses();

        $filename = 'kabel-inquiries-' . gmdate( 'Ymd-His' ) . '.csv';
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $filename );
        header( 'Pragma: no-cache' );

        $fputcsv_header = array( 'received_at', 'name', 'company', 'email', 'whatsapp_or_phone', 'country', 'project_type', 'product', 'message', 'status', 'source' );
        $out = fopen( 'php://output', 'w' );
        fputcsv( $out, $fputcsv_header );

        foreach ( $query->posts as $post ) {
                fputcsv( $out, array(
                        get_the_date( 'Y-m-d H:i:s', $post ),
                        get_post_meta( $post->ID, '_kb_inq_person', true ),
                        get_post_meta( $post->ID, '_kb_inq_company', true ),
                        get_post_meta( $post->ID, '_kb_inq_email', true ),
                        get_post_meta( $post->ID, '_kb_inq_phone', true ),
                        get_post_meta( $post->ID, '_kb_inq_country', true ),
                        kabel_inquiry_project_types()[ get_post_meta( $post->ID, '_kb_inq_project_type', true ) ] ?? '',
                        get_post_meta( $post->ID, '_kb_inq_product', true ),
                        strip_tags( (string) $post->post_content ),
                        $statuses[ get_post_meta( $post->ID, '_kb_inq_status', true ) ] ?? '',
                        get_post_meta( $post->ID, '_kb_inq_source', true ),
                ) );
        }
        fclose( $out );
        exit;
}

/**
 * Small CSS for status badges (kept inline to avoid an extra file request).
 */
function kabel_inquiry_admin_css(): void {
        $screen = get_current_screen();
        if ( ! $screen || 'kabel_inquiry' !== $screen->post_type ) {
                return;
        }
        echo '<style>
        .kb-badge{display:inline-block;padding:2px 10px;border-radius:99px;font-size:12px;font-weight:600;}
        .kb-badge--new{background:#d1e7dd;color:#0a3622;border:1px solid #badbcc;}
        .kb-badge--following{background:#fff3cd;color:#664d03;border:1px solid #ffecb5;}
        .kb-badge--won{background:#e9ecef;color:#41464b;border:1px solid #ced4da;}
        </style>';
}
add_action( 'admin_head', 'kabel_inquiry_admin_css' );
