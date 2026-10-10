<?php
/**
 * Generic archive (blog categories, case archive, series terms).
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<header class="zone-a page-hero">
	<div class="container">
		<?php dazhen_breadcrumbs(); ?>
		<p class="eyebrow eyebrow--a"><?php esc_html_e( 'Insights', 'dazhen' ); ?></p>
		<h1 class="page-hero__title"><?php echo esc_html( wp_strip_all_tags( (string) get_the_archive_title() ) ); ?></h1>
	</div>
</header>

<section class="zone-b section">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="grid grid--3">
				<?php
				while ( have_posts() ) :
					the_post();
					if ( 'case_study' === get_post_type() ) {
						dazhen_case_card( get_the_ID() );
					} else {
						dazhen_post_card( get_the_ID() );
					}
				endwhile;
				?>
			</div>
			<?php dazhen_pagination(); ?>
		<?php else : ?>
			<p class="empty-state"><?php esc_html_e( 'Nothing published here yet.', 'dazhen' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
