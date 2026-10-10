<?php
/**
 * Inquiry admin — list columns, status management, CSV export, menu badge.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

/**
 * Status labels shared across list, editor and export.
 *
 * @return array<string,string>
 */
function dazhen_inquiry_statuses(): array {
	return array(
		'new'      => __( 'New', 'dazhen' ),
		'following' => __( 'Following up', 'dazhen' ),
		'won'      => __( 'Won', 'dazhen' ),
	);
}

/**
 * List table columns.
 */
function dazhen_inquiry_columns( array $columns ): array {
	return array(
		'cb'       => $columns['cb'] ?? '',
		'title'    => __( 'Company / Person', 'dazhen' ),
		'country'  => __( 'Country', 'dazhen' ),
		'product'  => __( 'Product of interest', 'dazhen' ),
		'status'   => __( 'Status', 'dazhen' ),
		'date'     => __( 'Received', 'dazhen' ),
	);
}
add_filter( 'manage_dazhen_inquiry_posts_columns', 'dazhen_inquiry_columns' );

/**
 * Render custom columns.
 */
function dazhen_inquiry_columns_content( string $column, int $post_id ): void {
	switch ( $column ) {
		case 'country':
			echo esc_html( get_post_meta( $post_id, '_dz_inq_country', true ) );
			break;
		case 'product':
			echo esc_html( get_post_meta( $post_id, '_dz_inq_product', true ) );
			break;
		case 'status':
			$status    = get_post_meta( $post_id, '_dz_inq_status', true );
			$statuses  = dazhen_inquiry_statuses();
			$classes   = array(
				'new'      => 'dz-badge dz-badge--new',
				'following' => 'dz-badge dz-badge--following',
				'won'      => 'dz-badge dz-badge--won',
			);
			printf(
				'<span class="%s">%s</span>',
				esc_attr( $classes[ $status ] ?? 'dz-badge' ),
				esc_html( $statuses[ $status ] ?? $status )
			);
			break;
	}
}
add_action( 'manage_dazhen_inquiry_posts_custom_column', 'dazhen_inquiry_columns_content', 10, 2 );

/**
 * Status filter dropdown above the list.
 */
function dazhen_inquiry_status_filter(): void {
	global $typenow;
	if ( 'dazhen_inquiry' !== $typenow ) {
		return;
	}
	$current = isset( $_GET['dz_status'] ) ? sanitize_key( wp_unslash( $_GET['dz_status'] ) ) : '';
	?>
	<select name="dz_status">
		<option value=""><?php esc_html_e( 'All statuses', 'dazhen' ); ?></option>
		<?php foreach ( dazhen_inquiry_statuses() as $slug => $label ) : ?>
			<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $current, $slug ); ?>>
				<?php echo esc_html( $label ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php
}
add_action( 'restrict_manage_posts', 'dazhen_inquiry_status_filter' );

/**
 * Apply the status filter to the query.
 */
function dazhen_inquiry_filter_query( WP_Query $query ): void {
	global $pagenow;
	if ( ! is_admin() || 'edit.php' !== $pagenow || ! $query->is_main_query() ) {
		return;
	}
	if ( ( $query->get( 'post_type' ) ?? '' ) !== 'dazhen_inquiry' ) {
		return;
	}
	$status = isset( $_GET['dz_status'] ) ? sanitize_key( wp_unslash( $_GET['dz_status'] ) ) : '';
	if ( $status ) {
		$query->set( 'meta_query', array(
			array(
				'key'   => '_dz_inq_status',
				'value' => $status,
			),
		) );
	}
}
add_action( 'pre_get_posts', 'dazhen_inquiry_filter_query' );

/**
 * Status metabox on the inquiry detail screen + read-only summary.
 */
function dazhen_inquiry_edit_metaboxes(): void {
	add_meta_box(
		'dazhen_inquiry_status',
		__( 'Follow-up', 'dazhen' ),
		'dazhen_inquiry_status_metabox',
		'dazhen_inquiry',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'dazhen_inquiry_edit_metaboxes' );

/**
 * Render the status metabox.
 */
function dazhen_inquiry_status_metabox( WP_Post $post ): void {
	wp_nonce_field( 'dazhen_inquiry_status', 'dazhen_inquiry_status_nonce' );
	$status = get_post_meta( $post->ID, '_dz_inq_status', true ) ?: 'new';
	?>
	<p>
		<label for="dz_status"><strong><?php esc_html_e( 'Status', 'dazhen' ); ?></strong></label><br>
		<select name="dz_status" id="dz_status" style="width:100%;">
			<?php foreach ( dazhen_inquiry_statuses() as $slug => $label ) : ?>
				<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $status, $slug ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<?php
	$email = get_post_meta( $post->ID, '_dz_inq_email', true );
	if ( $email ) {
		printf( '<p><a class="button" href="mailto:%s">%s</a></p>', esc_attr( $email ), esc_html__( 'Reply by email', 'dazhen' ) );
	}
	$source = get_post_meta( $post->ID, '_dz_inq_source', true );
	if ( $source ) {
		printf( '<p style="font-size:12px;color:#666;">%s<br><a href="%s">%s</a></p>', esc_html__( 'Submitted from:', 'dazhen' ), esc_url( $source ), esc_html( wp_parse_url( $source, PHP_URL_HOST ) . ( wp_parse_url( $source, PHP_URL_PATH ) ?? '' ) ) );
	}
}

/**
 * Save the status metabox.
 */
function dazhen_inquiry_save_status( int $post_id ): void {
	if ( 'dazhen_inquiry' !== get_post_type( $post_id ) ) {
		return;
	}
	$nonce = $_POST['dazhen_inquiry_status_nonce'] ?? '';
	if ( ! is_string( $nonce ) || ! wp_verify_nonce( $nonce, 'dazhen_inquiry_status' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$status = isset( $_POST['dz_status'] ) ? sanitize_key( wp_unslash( $_POST['dz_status'] ) ) : 'new';
	if ( array_key_exists( $status, dazhen_inquiry_statuses() ) ) {
		update_post_meta( $post_id, '_dz_inq_status', $status );
	}
}
add_action( 'save_post', 'dazhen_inquiry_save_status' );

/**
 * New-inquiry count badge on the admin menu.
 */
function dazhen_inquiry_menu_badge(): void {
	global $menu;
	$count = (int) get_transient( 'dazhen_new_inquiry_count' );
	if ( $count < 1 ) {
		return;
	}
	foreach ( $menu as &$item ) {
		if ( 'edit.php?post_type=dazhen_inquiry' === $item[2] ) {
			$item[0] .= sprintf(
				' <span class="awaiting-mod count-%1$d"><span class="pending-count">%1$d</span></span>',
				$count
			);
			break;
		}
	}
}
add_action( 'admin_menu', 'dazhen_inquiry_menu_badge', 99 );

/**
 * Keep the new-inquiry count fresh.
 */
function dazhen_inquiry_count_refresh( int $post_id ): void {
	if ( 'dazhen_inquiry' !== get_post_type( $post_id ) ) {
		return;
	}
	$query = new WP_Query( array(
		'post_type'      => 'dazhen_inquiry',
		'post_status'    => 'private',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => array(
			array( 'key' => '_dz_inq_status', 'value' => 'new' ),
		),
	) );
	set_transient( 'dazhen_new_inquiry_count', $query->found_posts, HOUR_IN_SECONDS );
}
add_action( 'save_post', 'dazhen_inquiry_count_refresh', 20 );
add_action( 'after_delete_post', 'dazhen_inquiry_count_refresh' );

/**
 * CSV export page under Inquiries.
 */
function dazhen_inquiry_export_menu(): void {
	add_submenu_page(
		'edit.php?post_type=dazhen_inquiry',
		__( 'Export CSV', 'dazhen' ),
		__( 'Export CSV', 'dazhen' ),
		'manage_options',
		'dazhen-inquiry-export',
		'dazhen_inquiry_export_download'
	);
}
add_action( 'admin_menu', 'dazhen_inquiry_export_menu' );

/**
 * Stream the CSV file.
 */
function dazhen_inquiry_export_download(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to export inquiries.', 'dazhen' ) );
	}

	$query = new WP_Query( array(
		'post_type'      => 'dazhen_inquiry',
		'post_status'    => 'private',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );

	$statuses = dazhen_inquiry_statuses();

	$filename = 'dazhen-inquiries-' . gmdate( 'Ymd-His' ) . '.csv';
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . $filename );
	header( 'Pragma: no-cache' );

	$out = fopen( 'php://output', 'w' );
	fputcsv( $out, array( 'received_at', 'company', 'person', 'email', 'phone', 'country', 'product', 'message', 'status', 'source' ) );

	foreach ( $query->posts as $post ) {
		fputcsv( $out, array(
			get_the_date( 'Y-m-d H:i:s', $post ),
			get_post_meta( $post->ID, '_dz_inq_company', true ),
			get_post_meta( $post->ID, '_dz_inq_person', true ),
			get_post_meta( $post->ID, '_dz_inq_email', true ),
			get_post_meta( $post->ID, '_dz_inq_phone', true ),
			get_post_meta( $post->ID, '_dz_inq_country', true ),
			get_post_meta( $post->ID, '_dz_inq_product', true ),
			strip_tags( (string) $post->post_content ),
			$statuses[ get_post_meta( $post->ID, '_dz_inq_status', true ) ] ?? '',
			get_post_meta( $post->ID, '_dz_inq_source', true ),
		) );
	}
	fclose( $out );
	exit;
}

/**
 * Small CSS for status badges (kept inline to avoid an extra file request).
 */
function dazhen_inquiry_admin_css(): void {
	$screen = get_current_screen();
	if ( ! $screen || 'dazhen_inquiry' !== $screen->post_type ) {
		return;
	}
	echo '<style>
	.dz-badge{display:inline-block;padding:2px 10px;border-radius:99px;font-size:12px;font-weight:600;}
	.dz-badge--new{background:#d1e7dd;color:#0a3622;border:1px solid #badbcc;}
	.dz-badge--following{background:#fff3cd;color:#664d03;border:1px solid #ffecb5;}
	.dz-badge--won{background:#e9ecef;color:#41464b;border:1px solid #ced4da;}
	</style>';
}
add_action( 'admin_head', 'dazhen_inquiry_admin_css' );
