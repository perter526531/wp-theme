<?php
/**
 * Block patterns — reusable CTA starter for editor content.
 *
 * The former About / Services / Contact starter patterns now live in
 * dedicated page templates (page-templates/about.php, services.php,
 * contact.php) — single source of truth, filter-overridable. This file
 * keeps the reusable CTA pattern for any post or page.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register theme block patterns.
 */
function dazhen_register_patterns(): void {
	if ( ! function_exists( 'register_block_pattern' ) ) {
		return;
	}

	register_block_pattern( 'dazhen/cta-quote', array(
		'title'      => __( 'Call to action — Get a Quote', 'dazhen' ),
		'categories' => array( 'dazhen' ),
		'content'    => '<!-- wp:group {"align":"wide","style":{"color":{"background":"#062A33"},"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}},"border":{"radius":"24px"}},"textColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide has-white-color has-text-color has-background" style="border-radius:24px;background-color:#062A33;padding:var(--wp--preset--spacing--40)"><!-- wp:heading {"textAlign":"center","level":2} -->
<h2 class="wp-block-heading has-text-align-center">' . esc_html__( 'Ready to spec your next water appliance?', 'dazhen' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html__( 'Tell us your market, volume and target price — our engineers reply within one business day.', 'dazhen' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"style":{"color":{"background":"#FFB454","text":"#062A33"},"border":{"radius":"999px"}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-text-color has-background wp-element-button" href="/contact/" style="border-radius:999px;background-color:#FFB454;color:#062A33">' . esc_html__( 'Get a Quote', 'dazhen' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group -->',
	) );
}
add_action( 'init', 'dazhen_register_patterns' );
