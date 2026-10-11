<?php
/**
 * Front page — block-editor content renders first (Kabel Blocks kit or
 * anything else); a compact designed fallback covers the empty-page case.
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

/*
 * Block takeover: editor content renders full-canvas as blocks; an empty
 * page keeps this designed template.
 */
if ( kabel_render_block_page() ) {
        return;
}


get_header();

/*
 * The one-click bootstrap seeds the homepage as blocks. Prefer that editable
 * content when it exists, while keeping the original curated PHP homepage as
 * a backwards-compatible fallback for installations without block content.
 */
if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		if ( '' !== trim( (string) get_post_field( 'post_content', get_the_ID() ) ) ) {
			?>
			<main id="primary" class="site-main">
				<?php the_content(); ?>
			</main>
			<?php
			get_footer();
			return;
		}
	}
	rewind_posts();
}

$products_query = kabel_get_products( array( 'posts_per_page' => 6 ) );
$posts_query    = new WP_Query( array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => 3,
) );

/* ---------------- Hero (Zone A) ---------------- */ ?>
<section class="zone-a hero-home">
        <div class="hero-home__glow" aria-hidden="true"></div>
        <div class="container">
                <div class="hero-home__content">
                        <p class="eyebrow eyebrow--a"><?php echo esc_html( (string) kabel_get_option( 'kabel_hero_eyebrow', 'GLOBAL HIGH-END DECORATIVE PAINT BRAND' ) ); ?></p>
                        <h1 class="hero-home__title"><?php esc_html_e( 'Transform every wall into a timeless work of art.', 'kabel' ); ?></h1>
                        <p class="hero-home__sub"><?php esc_html_e( 'KABEL Art Paint from Druento — Italian decorative-coating heritage, formaldehyde-purifying technology and six artisanal finish families for homes, hospitality and commercial projects worldwide.', 'kabel' ); ?></p>
                        <div class="hero-home__actions">
                                <a class="btn btn--cta" href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ?: home_url( '/products/' ) ); ?>"><?php esc_html_e( 'Explore Paint Series', 'kabel' ); ?></a>
                                <a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Consult Now', 'kabel' ); ?></a>
                        </div>
                </div>
        </div>

        <div class="container hero-stats">
                <?php
                $stats = array(
                        array( '94.1', '%', __( 'Formaldehyde purification rate', 'kabel' ) ),
                        array( '82.1', '%', __( 'Long-lasting purification efficiency', 'kabel' ) ),
                        array( '6', '', __( 'Artisanal finish families', 'kabel' ) ),
                        array( '100', '+', __( 'Years of Italian heritage', 'kabel' ) ),
                );
                foreach ( $stats as $stat ) :
                        ?>
                        <div class="hero-stats__item">
                                <span class="hero-stats__value"><?php echo esc_html( $stat[0] ); ?><em><?php echo esc_html( $stat[1] ); ?></em></span>
                                <span class="hero-stats__label"><?php echo esc_html( $stat[2] ); ?></span>
                        </div>
                <?php endforeach; ?>
        </div>
</section>

<?php /* ---------------- Paint series (Zone B) ---------------- */ ?>
<?php if ( $products_query->have_posts() ) : ?>
<section class="zone-b section">
        <div class="container">
                <?php
                kabel_section_head( array(
                        'zone'   => 'b',
                        'eyebrow' => __( 'Products', 'kabel' ),
                        'title'  => __( 'High-end artistic coating series', 'kabel' ),
                        'sub'    => __( 'Texture, colour and craftsmanship — inspired by Italian aesthetics, driven by material innovation.', 'kabel' ),
                ) );
                ?>
                <div class="grid grid--3">
                        <?php
                        while ( $products_query->have_posts() ) :
                                $products_query->the_post();
                                kabel_product_card( get_the_ID() );
                        endwhile;
                        wp_reset_postdata();
                        ?>
                </div>
                <p class="section-foot">
                        <a class="btn btn--outline-teal" href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ?: home_url( '/products/' ) ); ?>"><?php esc_html_e( 'View all series', 'kabel' ); ?></a>
                </p>
        </div>
</section>
<?php endif; ?>

<?php /* ---------------- News (Zone B, alt) ---------------- */ ?>
<?php if ( $posts_query->have_posts() ) : ?>
<section class="zone-b zone-b--alt section">
        <div class="container">
                <?php
                kabel_section_head( array(
                        'zone'    => 'b',
                        'eyebrow' => __( 'News & Insights', 'kabel' ),
                        'title'   => __( 'From the Kabel team', 'kabel' ),
                ) );
                ?>
                <div class="grid grid--3">
                        <?php
                        while ( $posts_query->have_posts() ) :
                                $posts_query->the_post();
                                kabel_post_card( get_the_ID() );
                        endwhile;
                        wp_reset_postdata();
                        ?>
                </div>
        </div>
</section>
<?php endif; ?>

<?php /* ---------------- Bottom CTA (Zone A) ---------------- */ ?>
<section class="zone-a section cta-bottom">
        <div class="container">
                <div class="cta-bottom__card">
                        <h2><?php esc_html_e( 'Create a healthier and more beautiful space.', 'kabel' ); ?></h2>
                        <p><?php esc_html_e( 'Tell us about your project, market or distribution plans — the Kabel team replies within one business day.', 'kabel' ); ?></p>
                        <a class="btn btn--cta" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php echo kabel_cta_text(); ?></a>
                </div>
        </div>
</section>

<?php
get_footer();
