<?php
/**
 * 404.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="zone-a section error-404">
	<div class="container">
		<p class="eyebrow eyebrow--a">404</p>
		<h1><?php esc_html_e( 'This page went for a water break.', 'dazhen' ); ?></h1>
		<p><?php esc_html_e( 'The page you are looking for does not exist or has moved. Try the links below instead.', 'dazhen' ); ?></p>
		<p>
			<a class="btn btn--cta" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to homepage', 'dazhen' ); ?></a>
			<a class="btn btn--ghost" href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ?: home_url( '/products/' ) ); ?>"><?php esc_html_e( 'Browse products', 'dazhen' ); ?></a>
		</p>
	</div>
</section>
<?php
get_footer();
