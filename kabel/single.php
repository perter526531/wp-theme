<?php
/**
 * Single post — news article with design-confirmed typography.
 *
 * Head block (category badge, date, reading time, byline) + article body
 * (headings, teal quote block, check-mark lists) + end-of-article CTA +
 * previous/next navigation + comments + optional blog sidebar (widgets).
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( is_singular( 'post' ) ) :
        ?>
        <div class="read-progress" data-kb-progress aria-hidden="true"><span></span></div>
        <?php
endif;

$has_sidebar = kabel_has_blog_sidebar();

while ( have_posts() ) :
        the_post();

        $categories = get_the_category( get_the_ID() );
        $category   = $categories[0] ?? null;
        $minutes    = kabel_reading_time( get_the_ID() );
        ?>
        <header class="zone-b page-hero page-hero--post">
                <div class="container container--narrow">
                        <?php kabel_breadcrumbs(); ?>
                        <div class="article-meta">
                                <?php if ( $category ) : ?>
                                        <a class="article-meta__cat" href="<?php echo esc_url( get_category_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a>
                                <?php endif; ?>
                                <time class="article-meta__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
                                <span class="article-meta__minutes"><?php printf( esc_html__( '%d min read', 'kabel' ), (int) $minutes ); ?></span>
                        </div>
                        <h1 class="page-hero__title page-hero__title--ink"><?php echo esc_html( get_the_title() ); ?></h1>
                        <p class="article-meta__byline">
                                <?php
                                printf(
                                        /* translators: %s: author name */
                                        esc_html__( 'By %s', 'kabel' ),
                                        esc_html( get_the_author() )
                                );
                                ?>
                        </p>
                </div>
        </header>

        <section class="zone-b section single-article">
                <?php kabel_sidebar_open(); ?>
                        <div class="<?php echo $has_sidebar ? 'container' : 'container container--narrow'; ?>">
                                <?php if ( has_post_thumbnail() ) : ?>
                                        <figure class="single-post__thumb"><?php the_post_thumbnail( 'kabel-large' ); ?></figure>
                                <?php endif; ?>
                                <div class="prose prose--cap">
                                        <?php the_content(); ?>
                                        <?php get_template_part( 'template-parts/cta-inline' ); ?>
                                        <?php
                                        the_post_navigation(
                                                array(
                                                        'prev_text' => '<span class="post-nav__label">← ' . esc_html__( 'Previous', 'kabel' ) . '</span><span class="post-nav__title">%title</span>',
                                                        'next_text' => '<span class="post-nav__label">' . esc_html__( 'Next', 'kabel' ) . ' →</span><span class="post-nav__title">%title</span>',
                                                )
                                        );

                                        /*
                                         * Related reading — same category first, topped up
                                         * with the newest posts when the category runs short.
                                         * Purely server-side; degrades to nothing without JS.
                                         */
                                        $related_posts = array();
                                        $related_query = new WP_Query( array(
                                                'post_type'           => 'post',
                                                'posts_per_page'      => 3,
                                                'post__not_in'        => array( get_the_ID() ),
                                                'category__in'        => wp_get_post_categories( get_the_ID(), array( 'fields' => 'ids' ) ),
                                                'ignore_sticky_posts' => true,
                                                'no_found_rows'       => true,
                                        ) );
                                        if ( $related_query->have_posts() ) {
                                                $related_posts = $related_query->posts;
                                        }
                                        if ( count( $related_posts ) < 3 ) {
                                                $fill_query = new WP_Query( array(
                                                        'post_type'           => 'post',
                                                        'posts_per_page'      => 3 - count( $related_posts ),
                                                        'post__not_in'        => array_merge( array( get_the_ID() ), wp_list_pluck( $related_posts, 'ID' ) ),
                                                        'ignore_sticky_posts' => true,
                                                        'no_found_rows'       => true,
                                                ) );
                                                if ( $fill_query->have_posts() ) {
                                                        $related_posts = array_merge( $related_posts, $fill_query->posts );
                                                }
                                        }
                                        if ( $related_posts ) :
                                                ?>
                                                <section class="related-posts" aria-labelledby="related-posts-title">
                                                        <h2 class="related-posts__title" id="related-posts-title"><?php esc_html_e( 'Related reading', 'kabel' ); ?></h2>
                                                        <div class="grid grid--3">
                                                                <?php
                                                                global $post;
                                                                foreach ( $related_posts as $related_post ) {
                                                                        $post = $related_post;
                                                                        setup_postdata( $post );
                                                                        get_template_part( 'template-parts/post-card' );
                                                                }
                                                                wp_reset_postdata();
                                                                ?>
                                                        </div>
                                                </section>
                                                <?php
                                        endif;

                                        if ( comments_open() || (int) get_comments_number() > 0 ) {
                                                comments_template();
                                        }
                                        ?>
                                </div>
                        </div>
                        <?php if ( $has_sidebar ) : ?>
                                <aside class="blog-sidebar site-secondary" role="complementary" aria-label="<?php esc_attr_e( 'Blog sidebar', 'kabel' ); ?>">
                                        <?php dynamic_sidebar( 'blog-sidebar' ); ?>
                                </aside>
                        <?php endif; ?>
                <?php kabel_sidebar_close(); ?>
        </section>
        <?php
endwhile;

get_footer();
