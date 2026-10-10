<?php
/**
 * Inline CTA at the end of posts.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;
?>
<aside class="cta-inline">
	<p><?php esc_html_e( 'Sourcing smart water appliances?', 'dazhen' ); ?></p>
	<a class="btn btn--cta" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php echo dazhen_cta_text(); ?></a>
</aside>
