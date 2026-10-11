<?php
/**
 * Custom post types and taxonomies.
 *
 * Data model (Kabel):
 *   product       — paint families / series (6 families, /products/ archive)
 *   projects      — project references (home renovation + engineering,
 *                   /projects/ archive, classified with the dedicated
 *                   project_type taxonomy)
 *   kabel_inquiry — inquiries (private, admin-managed)
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register all post types and taxonomies.
 */
function kabel_register_post_types() {

	/* ---------- Product (paint family) ---------- */
	register_post_type( 'product', array(
		'labels'              => array(
			'name'               => __( 'Paint Series', 'kabel' ),
			'singular_name'      => __( 'Paint Series', 'kabel' ),
			'add_new'            => __( 'Add New Series', 'kabel' ),
			'add_new_item'       => __( 'Add New Paint Series', 'kabel' ),
			'edit_item'          => __( 'Edit Paint Series', 'kabel' ),
			'new_item'           => __( 'New Paint Series', 'kabel' ),
			'view_item'          => __( 'View Paint Series', 'kabel' ),
			'search_items'       => __( 'Search Paint Series', 'kabel' ),
			'not_found'          => __( 'No paint series found.', 'kabel' ),
			'not_found_in_trash' => __( 'No paint series found in Trash.', 'kabel' ),
			'menu_name'          => __( 'Paint Series', 'kabel' ),
			'all_items'          => __( 'All Paint Series', 'kabel' ),
		),
		'public'              => true,
		'show_ui'             => true,
		'menu_icon'           => 'dashicons-art',
		'menu_position'       => 21,
		'has_archive'         => 'products',
		'rewrite'             => array( 'slug' => 'products', 'with_front' => false ),
		'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
		'show_in_rest'        => true, // Block editor for the long description.
		'exclude_from_search' => false,
	) );

	register_taxonomy( 'product_series', 'product', array(
		'labels'            => array(
			'name'          => __( 'Product Families', 'kabel' ),
			'singular_name' => __( 'Product Family', 'kabel' ),
			'menu_name'     => __( 'Families', 'kabel' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'product-series', 'with_front' => false ),
	) );

	/* ---------- Projects (project references) ---------- */
	register_post_type( 'projects', array(
		'labels'        => array(
			'name'               => __( 'Projects', 'kabel' ),
			'singular_name'      => __( 'Project', 'kabel' ),
			'add_new'            => __( 'Add New Project', 'kabel' ),
			'add_new_item'       => __( 'Add New Project', 'kabel' ),
			'edit_item'          => __( 'Edit Project', 'kabel' ),
			'new_item'           => __( 'New Project', 'kabel' ),
			'view_item'          => __( 'View Project', 'kabel' ),
			'search_items'       => __( 'Search Projects', 'kabel' ),
			'not_found'          => __( 'No projects found.', 'kabel' ),
			'not_found_in_trash' => __( 'No projects found in Trash.', 'kabel' ),
			'menu_name'          => __( 'Projects', 'kabel' ),
			'all_items'          => __( 'All Projects', 'kabel' ),
		),
		'public'        => true,
		'show_ui'       => true,
		'menu_icon'     => 'dashicons-format-gallery',
		'menu_position' => 22,
		'has_archive'   => true,
		'rewrite'       => array( 'slug' => 'projects', 'with_front' => false ),
		'taxonomies'    => array( 'project_type' ),
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'show_in_rest'  => true,
	) );

	register_taxonomy( 'project_type', 'projects', array(
		'labels'            => array(
			'name'          => __( 'Project Types', 'kabel' ),
			'singular_name' => __( 'Project Type', 'kabel' ),
			'menu_name'     => __( 'Project Types', 'kabel' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'projects/type', 'with_front' => false ),
	) );

	/* ---------- Inquiry (private, admin-managed) ---------- */
	register_post_type( 'kabel_inquiry', array(
		'labels'              => array(
			'name'          => __( 'Inquiries', 'kabel' ),
			'singular_name' => __( 'Inquiry', 'kabel' ),
			'menu_name'     => __( 'Inquiries', 'kabel' ),
			'edit_item'     => __( 'View Inquiry', 'kabel' ),
			'search_items'  => __( 'Search Inquiries', 'kabel' ),
			'not_found'     => __( 'No inquiries yet.', 'kabel' ),
		),
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_icon'           => 'dashicons-email-alt',
		'menu_position'       => 26,
		'capability_type'     => 'post',
		'map_meta_cap'        => true,
		'capabilities'        => array( 'create_posts' => 'do_not_allow' ), // Created only via the public form.
		'supports'            => array( 'title' ),
		'exclude_from_search' => true,
		'show_in_rest'        => false,
	) );
}
add_action( 'init', 'kabel_register_post_types' );

/**
 * Move legacy project categories into the dedicated project taxonomy once.
 *
 * Category terms are retained so legacy inbound URLs can redirect safely; new
 * project editing is isolated from news categories from this point forward.
 */
function kabel_migrate_project_categories(): void {
	if ( get_option( 'kabel_project_taxonomy_migration_v1' ) ) {
		return;
	}

	$legacy_slugs = array( 'home-renovation-projects', 'engineering-projects' );
	$project_ids  = get_posts( array(
		'post_type'      => 'projects',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	) );

	foreach ( $project_ids as $project_id ) {
		$slugs = wp_get_object_terms( (int) $project_id, 'category', array( 'fields' => 'slugs' ) );
		if ( is_wp_error( $slugs ) ) {
			continue;
		}
		$project_types = array_values( array_intersect( $legacy_slugs, $slugs ) );
		if ( $project_types ) {
			wp_set_object_terms( (int) $project_id, $project_types, 'project_type', false );
		}
	}

	update_option( 'kabel_project_taxonomy_migration_v1', '1', false );
	flush_rewrite_rules();
}
add_action( 'admin_init', 'kabel_migrate_project_categories' );

/**
 * Redirect legacy project-category URLs to the dedicated project taxonomy.
 */
function kabel_redirect_legacy_project_category(): void {
	if ( ! is_category() ) {
		return;
	}

	$term = get_queried_object();
	if ( ! $term instanceof WP_Term || ! in_array( $term->slug, array( 'home-renovation-projects', 'engineering-projects' ), true ) ) {
		return;
	}

	$project_type = get_term_by( 'slug', $term->slug, 'project_type' );
	if ( ! $project_type || is_wp_error( $project_type ) ) {
		return;
	}

	$url = get_term_link( $project_type );
	if ( ! is_wp_error( $url ) ) {
		wp_safe_redirect( $url, 301 );
		exit;
	}
}
add_action( 'template_redirect', 'kabel_redirect_legacy_project_category' );

/**
 * Flush rewrite rules on activation so custom URLs work immediately.
 * (Rewrite rules are also regenerated automatically on permalink settings changes.)
 */
function kabel_flush_rewrite_on_activation() {
	kabel_register_post_types();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'kabel_flush_rewrite_on_activation' );
