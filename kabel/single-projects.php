<?php
/**
 * Single project reference (Home Renovation / Engineering Projects).
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
        the_post();

        $location = get_post_meta( get_the_ID(), '_kb_project_location', true );
        $summary  = get_post_meta( get_the_ID(), '_kb_project_summary', true );
        $categories = get_the_category( get_the_ID() );
        ?>

        <header class="zone-b page-hero page-hero--project">
                <div class="container">
                        <?php kabel_breadcrumbs(); ?>
                        <?php if ( $location ) : ?>
                                <p class="chip chip--region"><?php echo esc_html( $location ); ?></p>
                        <?php endif; ?>
                        <?php foreach ( $categories as $category ) : ?>
                                <a class="chip" href="<?php echo esc_url( get_category_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a>
                        <?php endforeach; ?>
                        <h1 class="page-hero__title page-hero__title--ink"><?php echo esc_html( get_the_title() ); ?></h1>
                        <?php if ( $summary ) : ?>
                                <p class="page-hero__sub"><?php echo esc_html( $summary ); ?></p>
                        <?php endif; ?>
                        <?php if ( has_post_thumbnail() ) : ?>
                                <div class="page-hero--project__media">
                                        <?php the_post_thumbnail( 'kabel-large', array( 'class' => 'page-hero--project__img', 'alt' => esc_attr( (string) get_the_title() ) ) ); ?>
                                </div>
                        <?php endif; ?>
                </div>
        </header>

        <section class="zone-b section section--narrow">
                <div class="container">
                        <?php if ( get_the_content() ) : ?>
                                <div class="prose"><?php the_content(); ?></div>
                        <?php endif; ?>

                        <?php
                        the_post_navigation( array(
                                'prev_text' => '<span class="post-nav__label">&larr; ' . esc_html__( 'Previous project', 'kabel' ) . '</span><span class="post-nav__title">%title</span>',
                                'next_text' => '<span class="post-nav__label">' . esc_html__( 'Next project', 'kabel' ) . ' &rarr;</span><span class="post-nav__title">%title</span>',
                        ) );
                        ?>
                </div>
        </section>

        <?php get_template_part( 'template-parts/form-inquiry' ); ?>

        <?php
endwhile;

get_footer();
