<?php
/**
 * Custom post types and taxonomies.
 *
 * Data model (confirmed in Phase 4):
 *   product         — product catalog (10 series)
 *   solution        — scenario landing pages (4 scenarios)
 *   case_study      — success cases, living under Solutions
 *   dazhen_inquiry  — inquiries (private, admin-managed)
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register all post types and taxonomies.
 */
function dazhen_register_post_types() {

	/* ---------- Product ---------- */
	register_post_type( 'product', array(
		'labels'              => array(
			'name'          => __( 'Products', 'dazhen' ),
			'singular_name' => __( 'Product', 'dazhen' ),
			'add_new_item'  => __( 'Add New Product', 'dazhen' ),
			'edit_item'     => __( 'Edit Product', 'dazhen' ),
			'menu_name'     => __( 'Products', 'dazhen' ),
		),
		'public'              => true,
		'show_ui'             => true,
		'menu_icon'           => 'dashicons-screenoptions',
		'menu_position'       => 21,
		'has_archive'         => 'products',
		'rewrite'             => array( 'slug' => 'products', 'with_front' => false ),
		'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
		'show_in_rest'        => true, // Block editor for the long description.
		'exclude_from_search' => false,
	) );

	register_taxonomy( 'product_series', 'product', array(
		'labels'            => array(
			'name'          => __( 'Product Series', 'dazhen' ),
			'singular_name' => __( 'Product Series', 'dazhen' ),
			'menu_name'     => __( 'Series', 'dazhen' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'product-series', 'with_front' => false ),
	) );

	/* ---------- Solution (scenario landing pages) ---------- */
	register_post_type( 'solution', array(
		'labels'        => array(
			'name'          => __( 'Solutions', 'dazhen' ),
			'singular_name' => __( 'Solution', 'dazhen' ),
			'add_new_item'  => __( 'Add New Solution', 'dazhen' ),
			'edit_item'     => __( 'Edit Solution', 'dazhen' ),
			'menu_name'     => __( 'Solutions', 'dazhen' ),
		),
		'public'        => true,
		'show_ui'       => true,
		'menu_icon'     => 'dashicons-welcome-learn-more',
		'menu_position' => 22,
		'has_archive'   => false, // The /solutions/ overview is a static page.
		'rewrite'       => array( 'slug' => 'solutions', 'with_front' => false, 'pages' => false ),
		'supports'      => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		'show_in_rest'  => true,
	) );

	/* ---------- Case study (success cases under Solutions) ---------- */
	register_post_type( 'case_study', array(
		'labels'        => array(
			'name'          => __( 'Success Cases', 'dazhen' ),
			'singular_name' => __( 'Success Case', 'dazhen' ),
			'add_new_item'  => __( 'Add New Case', 'dazhen' ),
			'edit_item'     => __( 'Edit Case', 'dazhen' ),
			'menu_name'     => __( 'Cases', 'dazhen' ),
		),
		'public'        => true,
		'show_ui'       => true,
		'menu_icon'     => 'dashicons-awards',
		'menu_position' => 23,
		'has_archive'   => 'solutions/cases',
		'rewrite'       => array( 'slug' => 'solutions/cases', 'with_front' => false ),
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'show_in_rest'  => true,
	) );

	register_taxonomy( 'case_region', 'case_study', array(
		'labels'            => array(
			'name'          => __( 'Case Regions', 'dazhen' ),
			'singular_name' => __( 'Case Region', 'dazhen' ),
			'menu_name'     => __( 'Regions', 'dazhen' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'case-region', 'with_front' => false ),
	) );

	/* ---------- Inquiry (private, admin-managed) ---------- */
	register_post_type( 'dazhen_inquiry', array(
		'labels'              => array(
			'name'          => __( 'Inquiries', 'dazhen' ),
			'singular_name' => __( 'Inquiry', 'dazhen' ),
			'edit_item'     => __( 'View Inquiry', 'dazhen' ),
			'search_items'  => __( 'Search Inquiries', 'dazhen' ),
			'not_found'     => __( 'No inquiries yet.', 'dazhen' ),
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
add_action( 'init', 'dazhen_register_post_types' );

/**
 * Flush rewrite rules on activation so custom URLs work immediately.
 * (Rewrite rules are also regenerated automatically on permalink settings changes.)
 */
function dazhen_flush_rewrite_on_activation() {
	dazhen_register_post_types();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'dazhen_flush_rewrite_on_activation' );
