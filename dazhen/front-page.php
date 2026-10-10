<?php
/**
 * Front page — the confirmed A×B homepage design, ported from hi-fi v1.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

/*
 * Block takeover: editor content (Dazhen Blocks kit or anything else)
 * renders full-canvas as blocks; an empty page keeps this designed template.
 */
if ( dazhen_render_block_page() ) {
	return;
}


get_header();

$products_query = dazhen_get_products( array( 'posts_per_page' => 6 ) );
$hero_product   = dazhen_get_products( array( 'posts_per_page' => 1 ) );
$cases_query    = new WP_Query( array(
        'post_type'      => 'case_study',
        'post_status'    => 'publish',
        'posts_per_page' => 3,
) );
$posts_query    = new WP_Query( array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => 3,
) );

/* ---------------- Hero (Zone A) ---------------- */ ?>
<section class="zone-a hero-home">
        <div class="hero-home__glow" aria-hidden="true"></div>
        <div class="container hero-home__grid">
                <div class="hero-home__content">
                        <p class="eyebrow eyebrow--a"><?php echo esc_html( (string) dazhen_get_option( 'dazhen_hero_eyebrow', 'HIGH-END ICE & HEAT TECHNOLOGY SPECIALIST' ) ); ?></p>
                        <h1 class="hero-home__title"><?php esc_html_e( 'Instant hot. Instant cold. Engineered in 3 seconds.', 'dazhen' ); ?></h1>
                        <p class="hero-home__sub"><?php esc_html_e( 'Dazhen designs and manufactures smart water appliances for global brands — from home faucets to hotel engineering systems, with one-stop OEM/ODM service.', 'dazhen' ); ?></p>
                        <div class="hero-home__actions">
                                <a class="btn btn--cta" href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ?: home_url( '/products/' ) ); ?>"><?php esc_html_e( 'Explore Products', 'dazhen' ); ?></a>
                                <a class="btn btn--ghost" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Talk to Our Team', 'dazhen' ); ?></a>
                        </div>
                </div>

                <div class="hero-home__visual">
                        <div class="hero-product-card">
                                <?php
                                if ( $hero_product->have_posts() ) :
                                        $hero_product->the_post();
                                        if ( has_post_thumbnail() ) {
                                                the_post_thumbnail( 'dazhen-large', array( 'class' => 'hero-product-card__img' ) );
                                        }
                                        $hero_model = get_post_meta( get_the_ID(), '_dz_product_model', true );
                                        wp_reset_postdata();
                                endif;
                                ?>
                                <div class="hero-gauge" data-dz-gauge>
                                        <svg viewBox="0 0 80 80" role="img" aria-label="<?php esc_attr_e( 'Water temperature rising to 95 degrees Celsius', 'dazhen' ); ?>">
                                                <circle class="hero-gauge__track" cx="40" cy="40" r="34"/>
                                                <circle class="hero-gauge__bar" cx="40" cy="40" r="34"/>
                                        </svg>
                                        <div class="hero-gauge__readout">
                                                <span class="hero-gauge__temp" data-dz-gauge-temp>25°</span>
                                                <span class="hero-gauge__unit">CELSIUS</span>
                                        </div>
                                        <div class="hero-gauge__label">
                                                <strong><?php echo esc_html( $hero_model ?? __( '3s Instant Heat', 'dazhen' ) ); ?></strong>
                                                <span><?php esc_html_e( '25°C to 95°C in three seconds, held constant by dual algorithms.', 'dazhen' ); ?></span>
                                        </div>
                                </div>
                        </div>
                </div>
        </div>

        <div class="container hero-stats">
                <?php
                $stats = array(
                        array( '10', '+', __( 'Years of Technology', 'dazhen' ) ),
                        array( '30', '+', __( 'Patented Technologies', 'dazhen' ) ),
                        array( '20', '+', __( 'Core R&D Engineers', 'dazhen' ) ),
                        array( '100', '', __( 'Units Minimum Order', 'dazhen' ) ),
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

<?php /* ---------------- Selling points (Zone B) ---------------- */ ?>
<section class="zone-b section">
        <div class="container">
                <?php
                dazhen_section_head( array(
                        'zone'   => 'b',
                        'eyebrow' => __( 'Why Dazhen', 'dazhen' ),
                        'title'  => __( 'Four reasons global brands build with us', 'dazhen' ),
                ) );
                ?>
                <div class="grid grid--4">
                        <?php
                        $features = array(
                                array( __( 'Self-Developed Heating Tech', 'dazhen' ), __( 'Thick-film heating with dual-algorithm control: 3-second heat-up, precise constant temperature, tankless efficiency.', 'dazhen' ) ),
                                array( __( 'All-Scenario Lineup', 'dazhen' ), __( 'From home faucets to hotel engineering systems — one factory covers every drinking-water scenario.', 'dazhen' ) ),
                                array( __( 'OEM / ODM One-Stop', 'dazhen' ), __( 'Industrial design, certification and packaging handled in one continuous flow, from concept to shipment.', 'dazhen' ) ),
                                array( __( 'Global Compliance', 'dazhen' ), __( 'ISO 9001, BSCI and food-grade material standards — products ready for worldwide markets.', 'dazhen' ) ),
                        );
                        foreach ( $features as $feature ) :
                                ?>
                                <article class="card-feature" data-dz-reveal>
                                        <span class="card-feature__dot" aria-hidden="true"></span>
                                        <h3><?php echo esc_html( $feature[0] ); ?></h3>
                                        <p><?php echo esc_html( $feature[1] ); ?></p>
                                </article>
                        <?php endforeach; ?>
                </div>
        </div>
</section>

<?php /* ---------------- Products (Zone B, alt) ---------------- */ ?>
<?php if ( $products_query->have_posts() ) : ?>
<section class="zone-b zone-b--alt section">
        <div class="container">
                <?php
                dazhen_section_head( array(
                        'zone'    => 'b',
                        'eyebrow' => __( 'Product Center', 'dazhen' ),
                        'title'   => __( 'Built for every drinking-water scenario', 'dazhen' ),
                        'sub'     => __( 'Ten series, one engineering platform — all available for OEM/ODM customization.', 'dazhen' ),
                ) );
                ?>
                <div class="grid grid--3">
                        <?php
                        while ( $products_query->have_posts() ) :
                                $products_query->the_post();
                                dazhen_product_card( get_the_ID() );
                        endwhile;
                        wp_reset_postdata();
                        ?>
                </div>
                <p class="section-foot">
                        <a class="btn btn--outline-teal" href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ?: home_url( '/products/' ) ); ?>"><?php esc_html_e( 'View all products', 'dazhen' ); ?></a>
                        <a class="section-foot__alt" href="<?php echo esc_url( ( get_post_type_archive_link( 'product' ) ?: home_url( '/products/' ) ) . '#solution-finder' ); ?>"><?php esc_html_e( 'Not sure which model? Try the 3-question Solution Finder →', 'dazhen' ); ?></a>
                </p>
        </div>
</section>
<?php endif; ?>

<?php /* ---------------- About / timeline (Zone B) ---------------- */ ?>
<section class="zone-b section">
        <div class="container about-grid">
                <div>
                        <?php
                        dazhen_section_head( array(
                                'zone'    => 'b',
                                'eyebrow' => __( 'About Dazhen', 'dazhen' ),
                                'title'   => __( 'Ten years of quiet, serious engineering', 'dazhen' ),
                                'sub'     => __( 'A research team founded in 2012, now a full manufacturing platform in Dayawan, Huizhou.', 'dazhen' ),
                        ) );
                        ?>
                        <ol class="timeline">
                                <?php
                                $milestones = array(
                                        array( '2012', __( 'R&D center established — three core research directions', 'dazhen' ) ),
                                        array( '2014', __( 'Industry-first nano rare-earth thick-film heating', 'dazhen' ) ),
                                        array( '2021', __( 'Instant heating + refrigeration integration breakthrough', 'dazhen' ) ),
                                        array( '2025', __( 'Full product ecosystem and global layout', 'dazhen' ) ),
                                );
                                foreach ( $milestones as $milestone ) :
                                        ?>
                                        <li class="timeline__item">
                                                <span class="timeline__year"><?php echo esc_html( $milestone[0] ); ?></span>
                                                <span class="timeline__text"><?php echo esc_html( $milestone[1] ); ?></span>
                                        </li>
                                <?php endforeach; ?>
                        </ol>
                </div>
        </div>
</section>

<?php /* ---------------- Core technology (Zone A) ---------------- */ ?>
<section class="zone-a section">
        <div class="container">
                <?php
                dazhen_section_head( array(
                        'zone'    => 'a',
                        'eyebrow' => __( 'Core Technology', 'dazhen' ),
                        'title'   => __( 'One platform, every water technology', 'dazhen' ),
                ) );
                ?>
                <div class="grid grid--4">
                        <?php
                        $tech = array(
                                array( __( 'Instant Heating', 'dazhen' ), __( 'Tankless thick-film heating — 3-second heat-up and zero stored-water heat loss.', 'dazhen' ) ),
                                array( __( 'Hot-Cold Integration', 'dazhen' ), __( 'Heating and cooling unified on one platform — one machine serves tea, ice water and everything between.', 'dazhen' ) ),
                                array( __( 'Purification (UV + RO)', 'dazhen' ), __( 'UV sterilization and reverse-osmosis filtration built into the water path.', 'dazhen' ) ),
                                array( __( 'Specialized Platforms', 'dazhen' ), __( 'Hydrogen-rich, boil-and-cool and sparkling water — differentiated SKUs for premium markets.', 'dazhen' ) ),
                        );
                        foreach ( $tech as $item ) :
                                ?>
                                <article class="card-tech" data-dz-reveal>
                                        <h3><?php echo esc_html( $item[0] ); ?></h3>
                                        <p><?php echo esc_html( $item[1] ); ?></p>
                                </article>
                        <?php endforeach; ?>
                </div>
                <ul class="chip-row">
                        <?php
                        $chips = array( 'Nano rare-earth thick film', 'Dual-algorithm control', 'UV sterilization', 'RO filtration', 'Hydrogen-rich water', 'Boil-and-cool platform', 'Sparkling water', 'Food-grade materials' );
                        foreach ( $chips as $chip ) :
                                ?>
                                <li class="chip chip--a"><?php echo esc_html( $chip ); ?></li>
                        <?php endforeach; ?>
                </ul>
        </div>
</section>

<?php /* ---------------- Solutions + Cases (Zone B) ---------------- */ ?>
<section class="zone-b zone-b--alt section">
        <div class="container">
                <?php
                dazhen_section_head( array(
                        'zone'    => 'b',
                        'eyebrow' => __( 'Solutions & Success Cases', 'dazhen' ),
                        'title'   => __( 'Chosen by distributors, hotels, schools and brands', 'dazhen' ),
                        'sub'     => __( 'Scenario-matched lineups, proven in real deployments across continents.', 'dazhen' ),
                ) );
                ?>
                <div class="grid grid--4">
                        <?php
                        $solutions = array(
                                array( 'home', __( 'Household', 'dazhen' ), __( 'Compact instant-heat taps and countertop units for family kitchens.', 'dazhen' ) ),
                                array( 'office', __( 'Office & Commercial', 'dazhen' ), __( 'High-duty systems for daily workplace hydration.', 'dazhen' ) ),
                                array( 'edu', __( 'Education & Medical', 'dazhen' ), __( 'Safe-temperature dispensing with child-proof controls.', 'dazhen' ) ),
                                array( 'hotel', __( 'Hotel & Engineering', 'dazhen' ), __( 'Wall-mounted and embedded units engineered for renovation projects.', 'dazhen' ) ),
                        );
                        foreach ( $solutions as $solution ) :
                                ?>
                                <article class="card-solution" data-dz-reveal>
                                        <span class="card-solution__icon card-solution__icon--<?php echo esc_attr( $solution[0] ); ?>" aria-hidden="true"></span>
                                        <h3><?php echo esc_html( $solution[1] ); ?></h3>
                                        <p><?php echo esc_html( $solution[2] ); ?></p>
                                        <a class="card-solution__link" href="<?php echo esc_url( home_url( '/solutions/' ) ); ?>"><?php esc_html_e( 'Explore solution →', 'dazhen' ); ?></a>
                                </article>
                        <?php endforeach; ?>
                </div>

                <?php if ( $cases_query->have_posts() ) : ?>
                        <div class="cases-strip">
                                <h3 class="cases-strip__title"><?php esc_html_e( 'Recent success stories', 'dazhen' ); ?></h3>
                                <div class="grid grid--3">
                                        <?php
                                        while ( $cases_query->have_posts() ) :
                                                $cases_query->the_post();
                                                dazhen_case_card( get_the_ID() );
                                        endwhile;
                                        wp_reset_postdata();
                                        ?>
                                </div>
                        </div>
                <?php endif; ?>
        </div>
</section>

<?php /* ---------------- News (Zone B) ---------------- */ ?>
<?php if ( $posts_query->have_posts() ) : ?>
<section class="zone-b section">
        <div class="container">
                <?php
                dazhen_section_head( array(
                        'zone'    => 'b',
                        'eyebrow' => __( 'News & Insights', 'dazhen' ),
                        'title'   => __( 'From the factory floor and the industry', 'dazhen' ),
                ) );
                ?>
                <div class="grid grid--3">
                        <?php
                        while ( $posts_query->have_posts() ) :
                                $posts_query->the_post();
                                dazhen_post_card( get_the_ID() );
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
                        <h2><?php esc_html_e( 'Ready to spec your next water appliance?', 'dazhen' ); ?></h2>
                        <p><?php esc_html_e( 'Tell us your market, volume and target price — our engineers reply within one business day.', 'dazhen' ); ?></p>
                        <a class="btn btn--cta" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php echo dazhen_cta_text(); ?></a>
                </div>
        </div>
</section>

<?php
get_footer();
