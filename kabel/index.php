<?php
/**
 * Fallback template — index (posts page /blog/).
 *
 * Design-confirmed blog list: marketing headline, category filter chips
 * (no-JS links), article cards, pagination, empty state.
 *
 * Filters:
 * - kabel_blog_title : headline above the article grid.
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

get_header();

$blog_title = apply_filters(
	'kabel_blog_title',
	__( 'What’s new at Kabel', 'kabel' )
);
?>

<header class="zone-a page-hero">
	<div class="container">
		<?php kabel_breadcrumbs(); ?>
		<p class="eyebrow eyebrow--a"><?php esc_html_e( 'News & Insights', 'kabel' ); ?></p>
		<h1 class="page-hero__title"><?php echo esc_html( $blog_title ); ?></h1>
		<p class="page-hero__sub"><?php esc_html_e( 'Company news, industry updates and plain-language paint knowledge from the Kabel team.', 'kabel' ); ?></p>
	</div>
</header>

<section class="zone-b section">
	<?php kabel_sidebar_open(); ?>
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
			<?php kabel_pagination(); ?>
		<?php else : ?>
			<p class="empty-state"><?php esc_html_e( 'Nothing published here yet.', 'kabel' ); ?></p>
		<?php endif; ?>
	</div>
	<?php get_sidebar(); ?>
	<?php kabel_sidebar_close(); ?>
</section>

<?php
get_footer();
