<?php
/**
 * Single post — news article.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<header class="zone-b page-hero page-hero--post">
		<div class="container container--narrow">
			<?php dazhen_breadcrumbs(); ?>
			<p class="card-post__date">
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
			</p>
			<h1 class="page-hero__title page-hero__title--ink"><?php echo esc_html( get_the_title() ); ?></h1>
		</div>
	</header>

	<section class="zone-b section">
		<div class="container container--narrow">
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="single-post__thumb"><?php the_post_thumbnail( 'dazhen-large' ); ?></figure>
			<?php endif; ?>
			<div class="prose"><?php the_content(); ?></div>
			<?php get_template_part( 'template-parts/cta-inline' ); ?>
		</div>
	</section>
	<?php
endwhile;

get_footer();
