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
        </div>
</header>

<?php
$series = get_terms( array(
        'taxonomy'   => 'product_series',
        'hide_empty' => true,
) );
?>
<section class="zone-b section">
        <div class="container">
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

<?php
get_template_part( 'template-parts/form-inquiry' );
get_footer();
