<?php
/**
 * Product archive — catalog with series filter.
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<header class="zone-a page-hero">
        <div class="container">
                <?php kabel_breadcrumbs(); ?>
                <p class="eyebrow eyebrow--a"><?php esc_html_e( 'Products', 'kabel' ); ?></p>
                <h1 class="page-hero__title"><?php esc_html_e( 'High-end artistic coating series', 'kabel' ); ?></h1>
                <p class="page-hero__sub"><?php esc_html_e( 'Texture, colour and craftsmanship — inspired by Italian aesthetics, driven by material innovation.', 'kabel' ); ?></p>
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
                                        <li class="chip"><a href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>"><?php esc_html_e( 'All', 'kabel' ); ?></a></li>
                                        <?php foreach ( $series as $term ) : ?>
                                                <li class="chip">
                                                        <a href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a>
                                                </li>
                                        <?php endforeach; ?>
                                </ul>
                        <?php endif; ?>
                        <form role="search" method="get" class="search-form search-form--light" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                                <input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search models…', 'kabel' ); ?>" aria-label="<?php esc_attr_e( 'Search products', 'kabel' ); ?>">
                                <input type="hidden" name="post_type" value="product">
                                <button type="submit" class="btn btn--outline-teal"><?php esc_html_e( 'Search', 'kabel' ); ?></button>
                        </form>
                </div>

                <?php if ( have_posts() ) : ?>
                        <div class="grid grid--3">
                                <?php
                                while ( have_posts() ) :
                                        the_post();
                                        kabel_product_card( get_the_ID() );
                                endwhile;
                                ?>
                        </div>
                        <?php kabel_pagination(); ?>
                <?php else : ?>
                        <p class="empty-state"><?php esc_html_e( 'Products are being prepared. Please check back soon.', 'kabel' ); ?></p>
                <?php endif; ?>
        </div>
</section>

<?php /* ---------- Inquiry ---------- */ ?>
<?php
get_template_part( 'template-parts/form-inquiry' );
get_footer();
