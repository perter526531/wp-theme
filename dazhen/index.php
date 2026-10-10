<?php
/**
 * Fallback template — index (posts page /blog/).
 *
 * Design-confirmed blog list: marketing headline, category filter chips
 * (no-JS links), article cards, pagination, empty state.
 *
 * Filters:
 * - dazhen_blog_title : headline above the article grid.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

get_header();

$blog_title = apply_filters(
	'dazhen_blog_title',
	__( 'What’s new at Dazhen', 'dazhen' )
);
?>

<header class="zone-a page-hero">
	<div class="container">
		<?php dazhen_breadcrumbs(); ?>
		<p class="eyebrow eyebrow--a"><?php esc_html_e( 'News & Insights', 'dazhen' ); ?></p>
		<h1 class="page-hero__title"><?php echo esc_html( $blog_title ); ?></h1>
		<p class="page-hero__sub"><?php esc_html_e( 'Program milestones, trade-show recaps and plain-language technology notes from the Dayawan team.', 'dazhen' ); ?></p>
	</div>
</header>

<section class="zone-b section">
	<?php dazhen_sidebar_open(); ?>
	<div class="container">
		<?php get_template_part( 'template-parts/blog-toolbar' ); ?>

		<?php if ( have_posts() ) : ?>
			<div class="grid grid--3">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/post-card' );
				endwhile;
				?>
			</div>
			<?php dazhen_pagination(); ?>
		<?php else : ?>
			<p class="empty-state"><?php esc_html_e( 'Nothing published here yet.', 'dazhen' ); ?></p>
		<?php endif; ?>
	</div>
	<?php get_sidebar(); ?>
	<?php dazhen_sidebar_close(); ?>
</section>

<?php
get_footer();
