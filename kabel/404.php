<?php
/**
 * 404.
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="zone-a section error-404">
	<div class="container">
		<p class="eyebrow eyebrow--a">404</p>
		<h1><?php esc_html_e( 'This wall is bare.', 'kabel' ); ?></h1>
		<p><?php esc_html_e( 'The page you were looking for has been primed over, renamed, or never got its first coat. Let us put some colour back — try one of these instead.', 'kabel' ); ?></p>
		<p>
			<a class="btn btn--cta" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to homepage', 'kabel' ); ?></a>
			<a class="btn btn--ghost" href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ?: home_url( '/products/' ) ); ?>"><?php esc_html_e( 'Explore paint series', 'kabel' ); ?></a>
		</p>
	</div>
</section>
<?php
get_footer();
