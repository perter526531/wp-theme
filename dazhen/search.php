<?php
/**
 * Search results.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<header class="zone-a page-hero">
        <div class="container">
                <p class="eyebrow eyebrow--a"><?php esc_html_e( 'Search', 'dazhen' ); ?></p>
                <h1 class="page-hero__title">
                        <?php
                        printf(
                                /* translators: %s: search query */
                                esc_html__( 'Results for “%s”', 'dazhen' ),
                                esc_html( (string) get_search_query() )
                        );
                        ?>
                </h1>
                <?php get_search_form(); ?>
        </div>
</header>

<section class="zone-b section">
        <?php dazhen_sidebar_open(); ?>
        <div class="container">
                <?php if ( have_posts() ) : ?>
                        <div class="grid grid--3">
                                <?php
                                while ( have_posts() ) :
                                        the_post();
                                        if ( 'product' === get_post_type() ) {
                                                dazhen_product_card( get_the_ID() );
                                        } elseif ( 'case_study' === get_post_type() ) {
                                                dazhen_case_card( get_the_ID() );
                                        } else {
                                                get_template_part( 'template-parts/post-card' );
                                        }
                                endwhile;
                                ?>
                        </div>
                        <?php dazhen_pagination(); ?>
                <?php else : ?>
                        <p class="empty-state"><?php esc_html_e( 'No results. Try a different keyword, or browse our products.', 'dazhen' ); ?></p>
                <?php endif; ?>
        </div>
        <?php get_sidebar(); ?>
        <?php dazhen_sidebar_close(); ?>
</section>

<?php
get_footer();
