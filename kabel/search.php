<?php
/**
 * Search results.
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<header class="zone-a page-hero">
        <div class="container">
                <p class="eyebrow eyebrow--a"><?php esc_html_e( 'Search', 'kabel' ); ?></p>
                <h1 class="page-hero__title">
                        <?php
                        printf(
                                /* translators: %s: search query */
                                esc_html__( 'Results for “%s”', 'kabel' ),
                                esc_html( (string) get_search_query() )
                        );
                        ?>
                </h1>
                <?php get_search_form(); ?>
        </div>
</header>

<section class="zone-b section">
        <?php kabel_sidebar_open(); ?>
        <div class="container">
                <?php if ( have_posts() ) : ?>
                        <div class="grid grid--3">
                                <?php
                                while ( have_posts() ) :
                                        the_post();
                                        if ( 'product' === get_post_type() ) {
                                                kabel_product_card( get_the_ID() );
                                        } elseif ( 'projects' === get_post_type() ) {
                                                get_template_part( 'template-parts/post-card' );
                                        } else {
                                                get_template_part( 'template-parts/post-card' );
                                        }
                                endwhile;
                                ?>
                        </div>
                        <?php kabel_pagination(); ?>
                <?php else : ?>
                        <p class="empty-state"><?php esc_html_e( 'No results. Try a different keyword, or browse our products.', 'kabel' ); ?></p>
                <?php endif; ?>
        </div>
        <?php get_sidebar(); ?>
        <?php kabel_sidebar_close(); ?>
</section>

<?php
get_footer();
