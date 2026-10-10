<?php
/**
 * Custom post types and taxonomies.
 *
 * Data model (Kabel):
 *   product       — paint families / series (6 families, /products/ archive)
 *   projects      — project references (home renovation + engineering,
 *                   /projects/ archive, classified with the post Category
 *                   taxonomy — same URLs as the original site)
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

	/* ---------- Projects (project references) ----------
	 * Same shape as the original site: the two reference classes
	 * (Home Renovation Projects / Engineering Projects) are ordinary post
	 * categories, so the archive URLs stay /category/... as before.
	 */
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
		'taxonomies'    => array( 'category' ),
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'show_in_rest'  => true,
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
 * Category archives include project references.
 *
 * WordPress limits /category/... archives to posts, but on this site the two
 * reference classes (Home Renovation Projects / Engineering Projects) are
 * categories whose entries are 'projects' — include them in the loop so the
 * original /category/<class>/ URLs keep working.
 */
function kabel_projects_in_category_archives( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_category() ) {
		return;
	}
	$post_type = $query->get( 'post_type' );
	if ( $post_type && 'post' !== $post_type ) {
		return; // Something already customised the query.
	}
	$query->set( 'post_type', array( 'post', 'projects' ) );
}
add_action( 'pre_get_posts', 'kabel_projects_in_category_archives' );

/**
 * Flush rewrite rules on activation so custom URLs work immediately.
 * (Rewrite rules are also regenerated automatically on permalink settings changes.)
 */
function kabel_flush_rewrite_on_activation() {
	kabel_register_post_types();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'kabel_flush_rewrite_on_activation' );
