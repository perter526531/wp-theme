<?php
/**
 * Fallback template — index.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<header class="zone-a page-hero">
	<div class="container">
		<p class="eyebrow eyebrow--a"><?php esc_html_e( 'Latest', 'dazhen' ); ?></p>
		<h1 class="page-hero__title"><?php echo esc_html( is_home() ? __( 'News & Insights', 'dazhen' ) : get_bloginfo( 'name' ) ); ?></h1>
	</div>
</header>

<section class="zone-b section">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="grid grid--3">
				<?php
				while ( have_posts() ) :
					the_post();
					dazhen_post_card( get_the_ID() );
				endwhile;
				?>
			</div>
			<?php dazhen_pagination(); ?>
		<?php else : ?>
			<p class="empty-state"><?php esc_html_e( 'Nothing published yet.', 'dazhen' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
