<?php
/**
 * Inline CTA at the end of posts.
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;
?>
<aside class="cta-inline">
        <p><?php esc_html_e( 'Planning a project, or sourcing premium decorative coatings?', 'kabel' ); ?></p>
        <a class="btn btn--cta" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php echo kabel_cta_text(); ?></a>
</aside>
