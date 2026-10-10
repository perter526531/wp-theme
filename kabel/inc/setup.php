<?php
/**
 * Theme setup: supports, menus, image sizes.
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register theme supports and core features.
 */
function kabel_setup() {
        load_theme_textdomain( 'kabel', KABEL_DIR . '/languages' );

        add_theme_support( 'automatic-feed-links' );
        add_theme_support( 'title-tag' );
        add_theme_support( 'post-thumbnails' );
        add_theme_support( 'responsive-embeds' );
        add_theme_support( 'customize-selective-refresh-widgets' );
        // Wide/full alignments: theme.json already sets layout.wideSize, but a
        // classic theme should declare the support explicitly so non-block
        // contexts and plugin feature detection see it.
        add_theme_support( 'align-wide' );
        // Load core block default styles (quote, table, search…) — without
        // this, core blocks inserted into content render unstyled.
        add_theme_support( 'wp-block-styles' );
        add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
        add_theme_support( 'custom-logo', array(
                'height'      => 60,
                'width'       => 240,
                'flex-height' => true,
                'flex-width'  => true,
        ) );

        // Editor receives the same design tokens (theme.json) + editor styles.
        add_theme_support( 'editor-styles' );
        add_editor_style( 'assets/css/editor.css' );

        register_nav_menus( array(
                'primary'         => __( 'Primary Navigation (header)', 'kabel' ),
                'footer_products' => __( 'Footer — Product Series', 'kabel' ),
                'footer_company'  => __( 'Footer — Solutions & Company', 'kabel' ),
        ) );

        // Image sizes aligned with the confirmed design system.
        add_image_size( 'kabel-card', 600, 600, true );    // Product/case/post cards (square).
        add_image_size( 'kabel-large', 1200, 1200, false ); // Product detail gallery.
        add_image_size( 'kabel-banner', 1920, 800, true );  // Hero & section banners.

        // Blog sidebar — an EMPTY sidebar keeps the full-width layout (zero intrusion).
        register_sidebar( array(
                'name'          => __( 'Blog Sidebar', 'kabel' ),
                'id'            => 'blog-sidebar',
                'description'   => __( 'Shows next to blog posts, archives and search results. Leave empty to keep the full-width layout.', 'kabel' ),
                'before_widget' => '<section id="%1$s" class="widget %2$s">',
                'after_widget'  => '</section>',
                'before_title'  => '<h2 class="widget__title">',
                'after_title'   => '</h2>',
        ) );
}
add_action( 'after_setup_theme', 'kabel_setup' );

/**
 * Content width (recommended by WP theme review guidelines).
 */
function kabel_content_width() {
        $GLOBALS['content_width'] = 768;
}
add_action( 'after_setup_theme', 'kabel_content_width', 0 );
