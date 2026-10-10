<?php
/**
 * Product archive — catalog with series filter.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<header class="zone-a page-hero">
        <div class="container">
                <?php dazhen_breadcrumbs(); ?>
                <p class="eyebrow eyebrow--a"><?php esc_html_e( 'Product Center', 'dazhen' ); ?></p>
                <h1 class="page-hero__title"><?php esc_html_e( 'Ten series. One engineering platform.', 'dazhen' ); ?></h1>
                <p class="page-hero__sub"><?php esc_html_e( 'Every model below is available for OEM/ODM customization — from 100 units.', 'dazhen' ); ?></p>
                <p class="page-hero__finder">
                        <a href="#solution-finder"><?php esc_html_e( 'Not sure which model? Try the 3-question Solution Finder ↓', 'dazhen' ); ?></a>
                </p>
        </div>
</header>

<?php
$series = get_terms( array(
        'taxonomy'   => 'product_series',
        'hide_empty' => true,
) );
?>
<section class="zone-b section" id="catalog">
        <div class="container">
                <div class="catalog-toolbar">
                        <?php if ( ! empty( $series ) && ! is_wp_error( $series ) ) : ?>
                                <ul class="chip-row chip-row--filter">
                                        <li class="chip"><a href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>"><?php esc_html_e( 'All', 'dazhen' ); ?></a></li>
                                        <?php foreach ( $series as $term ) : ?>
                                                <li class="chip">
                                                        <a href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a>
                                                </li>
                                        <?php endforeach; ?>
                                </ul>
                        <?php endif; ?>
                        <form role="search" method="get" class="search-form search-form--light" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                                <input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search models…', 'dazhen' ); ?>" aria-label="<?php esc_attr_e( 'Search products', 'dazhen' ); ?>">
                                <input type="hidden" name="post_type" value="product">
                                <button type="submit" class="btn btn--outline-teal"><?php esc_html_e( 'Search', 'dazhen' ); ?></button>
                        </form>
                </div>

                <?php if ( have_posts() ) : ?>
                        <div class="grid grid--3">
                                <?php
                                while ( have_posts() ) :
                                        the_post();
                                        dazhen_product_card( get_the_ID() );
                                endwhile;
                                ?>
                        </div>
                        <?php dazhen_pagination(); ?>
                <?php else : ?>
                        <p class="empty-state"><?php esc_html_e( 'Products are being prepared. Please check back soon.', 'dazhen' ); ?></p>
                <?php endif; ?>
        </div>
</section>

<?php /* ---------- Solution Finder wizard ---------- */ ?>
<?php get_template_part( 'template-parts/selector-wizard' ); ?>

<?php
get_template_part( 'template-parts/form-inquiry' );
get_footer();
