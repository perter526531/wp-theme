<?php
/**
 * Block editor integration — pattern category + small editor enhancements.
 * (The patterns themselves live in inc/patterns.php.)
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register a "Dazhen" block pattern category.
 */
function dazhen_pattern_category(): void {
        if ( function_exists( 'register_block_pattern_category' ) ) {
                register_block_pattern_category( 'dazhen', array(
                        'label' => __( 'Dazhen', 'dazhen' ),
                ) );
        }
}
add_action( 'init', 'dazhen_pattern_category' );

/**
 * Add body class helper so CSS can target singular vs archive contexts.
 */
function dazhen_body_classes( array $classes ): array {
        if ( is_front_page() ) {
                $classes[] = 'is-front-page';
        }
        if ( ! is_active_sidebar( 'sidebar-1' ) ) {
                $classes[] = 'no-sidebar';
        }
        return $classes;
}
add_filter( 'body_class', 'dazhen_body_classes' );
