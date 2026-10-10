<?php
/**
 * Single post — news article with design-confirmed typography.
 *
 * Head block (category badge, date, reading time, byline) + article body
 * (headings, teal quote block, check-mark lists) + end-of-article CTA +
 * previous/next navigation + comments + optional blog sidebar (widgets).
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

get_header();

$has_sidebar = dazhen_has_blog_sidebar();

while ( have_posts() ) :
	the_post();

	$categories = get_the_category( get_the_ID() );
	$category   = $categories[0] ?? null;
	$minutes    = dazhen_reading_time( get_the_ID() );
	?>
	<header class="zone-b page-hero page-hero--post">
		<div class="container container--narrow">
			<?php dazhen_breadcrumbs(); ?>
			<div class="article-meta">
				<?php if ( $category ) : ?>
					<a class="article-meta__cat" href="<?php echo esc_url( get_category_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a>
				<?php endif; ?>
				<time class="article-meta__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
				<span class="article-meta__minutes"><?php printf( esc_html__( '%d min read', 'dazhen' ), (int) $minutes ); ?></span>
			</div>
			<h1 class="page-hero__title page-hero__title--ink"><?php echo esc_html( get_the_title() ); ?></h1>
			<p class="article-meta__byline">
				<?php
				printf(
					/* translators: %s: author name */
					esc_html__( 'By %s', 'dazhen' ),
					esc_html( get_the_author() )
				);
				?>
			</p>
		</div>
	</header>

	<section class="zone-b section single-article">
		<?php dazhen_sidebar_open(); ?>
			<div class="<?php echo $has_sidebar ? 'container' : 'container container--narrow'; ?>">
				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="single-post__thumb"><?php the_post_thumbnail( 'dazhen-large' ); ?></figure>
				<?php endif; ?>
				<div class="prose prose--cap">
					<?php the_content(); ?>
					<?php get_template_part( 'template-parts/cta-inline' ); ?>
					<?php
					the_post_navigation(
						array(
							'prev_text' => '<span class="post-nav__label">← ' . esc_html__( 'Previous', 'dazhen' ) . '</span><span class="post-nav__title">%title</span>',
							'next_text' => '<span class="post-nav__label">' . esc_html__( 'Next', 'dazhen' ) . ' →</span><span class="post-nav__title">%title</span>',
						)
					);

					if ( comments_open() || (int) get_comments_number() > 0 ) {
						comments_template();
					}
					?>
				</div>
			</div>
			<?php if ( $has_sidebar ) : ?>
				<aside class="blog-sidebar site-secondary" role="complementary" aria-label="<?php esc_attr_e( 'Blog sidebar', 'dazhen' ); ?>">
					<?php dynamic_sidebar( 'blog-sidebar' ); ?>
				</aside>
			<?php endif; ?>
		<?php dazhen_sidebar_close(); ?>
	</section>
	<?php
endwhile;

get_footer();
