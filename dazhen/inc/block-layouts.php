<?php
/**
 * Full-page block layouts — one-click editable versions of every key page.
 *
 * Each layout is a plain Gutenberg markup string built from native blocks
 * with the theme's A×B classes, so pages can be:
 *   - seeded in one click from the Pages screen or the editor sidebar
 *     (see inc/page-seeder.php),
 *   - inserted manually as block patterns (see inc/patterns.php),
 *   - edited, rearranged or extended with any block plugin (Stackable
 *     included) without touching a line of code.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------
 * Low-level markup builders (shared by layouts and patterns).
 * ------------------------------------------------------------------- */

/**
 * Wrap inner blocks in a full-width A or B zone section.
 */
function dazhen_layout_zone( string $inner, string $zone = 'a', string $extra = '' ): string {
        $class = 'zone-' . $zone . ' section' . ( '' !== $extra ? ' ' . $extra : '' );
        return '<!-- wp:group {"align":"full","className":"' . $class . '","layout":{"type":"constrained","contentSize":"1280px"}} -->
<div class="wp-block-group alignfull ' . $class . '">' . $inner . '</div>
<!-- /wp:group -->';
}

/**
 * Eyebrow label paragraph.
 */
function dazhen_layout_eyebrow( string $text, string $zone = 'a' ): string {
        return '<!-- wp:paragraph {"className":"eyebrow eyebrow--' . $zone . '","fontSize":"small"} -->
<p class="eyebrow eyebrow--' . $zone . ' has-small-font-size">' . esc_html__( $text, 'dazhen' ) . '</p>
<!-- /wp:paragraph -->';
}

/**
 * Heading block.
 */
function dazhen_layout_heading( string $text, array $args = array() ): string {
        $level = isset( $args['level'] ) ? (int) $args['level'] : 2;
        $size  = isset( $args['size'] ) ? $args['size'] : 'x-large';
        $align = ! empty( $args['align'] ) ? '"textAlign":"' . $args['align'] . '",' : '';
        return '<!-- wp:heading {' . $align . '"level":' . $level . ',"fontSize":"' . $size . '"} -->
<h' . $level . ' class="wp-block-heading' . ( ! empty( $args['align'] ) ? ' has-text-align-' . $args['align'] : '' ) . ' has-' . $size . '-font-size">' . esc_html__( $text, 'dazhen' ) . '</h' . $level . '>
<!-- /wp:heading -->';
}

/**
 * Intro paragraph under a section heading.
 */
function dazhen_layout_sub( string $text, string $color = '' ): string {
        $style = '' !== $color ? ',"style":{"color":{"text":"' . $color . '"}}' : '';
        $class = '' !== $color ? ' has-text-color' : '';
        return '<!-- wp:paragraph {"className":"section-head__sub","fontSize":"large"' . $style . '} -->
<p class="section-head__sub has-large-font-size' . $class . '"' . ( '' !== $color ? ' style="color:' . $color . '"' : '' ) . '>' . esc_html__( $text, 'dazhen' ) . '</p>
<!-- /wp:paragraph -->';
}

/**
 * Card grid: columns of heading + text cards.
 */
function dazhen_layout_cards( array $items, string $card_class ): string {
        $cols = '';
        foreach ( $items as $item ) {
                $cols .= '<!-- wp:column -->
<div class="wp-block-column"><!-- wp:group {"className":"' . $card_class . '","layout":{"type":"constrained"}} -->
<div class="wp-block-group ' . $card_class . '"><!-- wp:heading {"level":3,"fontSize":"medium"} -->
<h3 class="wp-block-heading has-medium-font-size">' . esc_html__( $item[0], 'dazhen' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"small"} -->
<p class="has-small-font-size">' . esc_html__( $item[1], 'dazhen' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->';
        }
        return '<!-- wp:columns -->
<div class="wp-block-columns">' . $cols . '</div>
<!-- /wp:columns -->';
}

/**
 * Spacer block.
 */
function dazhen_layout_spacer(): string {
        return '<!-- wp:spacer {"height":"var:preset|spacing|40"} -->
<div style="height:var(--wp--preset--spacing--40)" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->';
}

/**
 * Inner-page hero (deep zone, H1 + sub).
 */
function dazhen_layout_page_hero( string $eyebrow, string $title, string $sub ): string {
        return dazhen_layout_zone(
                dazhen_layout_eyebrow( $eyebrow, 'a' ) . '
<!-- wp:heading {"level":1,"style":{"typography":{"lineHeight":"1.1"}},"fontSize":"xx-large"} -->
<h1 class="wp-block-heading has-xx-large-font-size" style="line-height:1.1">' . esc_html__( $title, 'dazhen' ) . '</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"color":{"text":"#D6E2E7"}},"fontSize":"large"} -->
<p class="has-large-font-size has-text-color" style="color:#D6E2E7">' . esc_html__( $sub, 'dazhen' ) . '</p>
<!-- /wp:paragraph -->',
                'a',
                'hero-page'
        );
}

/**
 * Stats strip on the deep zone (value + label columns).
 */
function dazhen_layout_stats( array $items ): string {
        $cols = '';
        foreach ( $items as $item ) {
                $cols .= '<!-- wp:column -->
<div class="wp-block-column"><!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"2.5rem","fontWeight":"700"}}} -->
<p class="has-text-align-center" style="font-size:2.5rem;font-weight:700">' . esc_html__( $item[0], 'dazhen' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"align":"center","style":{"color":{"text":"#8FB6C2"}},"fontSize":"small"} -->
<p class="has-text-align-center has-small-font-size has-text-color" style="color:#8FB6C2">' . esc_html__( $item[1], 'dazhen' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->';
        }
        return dazhen_layout_zone(
                '<!-- wp:columns {"className":"hero-stats"} -->
<div class="wp-block-columns hero-stats">' . $cols . '</div>
<!-- /wp:columns -->',
                'a'
        );
}

/**
 * Numbered journey step cards (flex-wrap group of .dz-step cards).
 */
function dazhen_layout_steps( array $steps ): string {
        $cards = '';
        foreach ( $steps as $index => $step ) {
                $no = str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT );
                $cards .= '<!-- wp:group {"className":"dz-step","layout":{"type":"constrained"}} -->
<div class="wp-block-group dz-step"><!-- wp:paragraph {"className":"dz-step__no","fontSize":"x-large"} -->
<p class="dz-step__no has-x-large-font-size">' . $no . '</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3,"fontSize":"medium"} -->
<h3 class="wp-block-heading has-medium-font-size">' . esc_html__( $step[0], 'dazhen' ) . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"dz-step__week","fontSize":"small"} -->
<p class="dz-step__week has-small-font-size">' . esc_html__( $step[1], 'dazhen' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"fontSize":"small"} -->
<p class="has-small-font-size">' . esc_html__( $step[2], 'dazhen' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->';
        }
        return '<!-- wp:group {"className":"dz-steps","layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group dz-steps">' . $cards . '</div>
<!-- /wp:group -->';
}

/**
 * FAQ accordions (native details blocks).
 */
function dazhen_layout_faq( array $groups ): string {
        $markup = '';
        foreach ( $groups as $group ) {
                $markup .= dazhen_layout_heading( $group[0], array( 'level' => 3, 'size' => 'large' ) );
                foreach ( $group[1] as $item ) {
                        $markup .= '<!-- wp:details -->
<details class="wp-block-details"><summary>' . esc_html__( $item[0], 'dazhen' ) . '</summary>
<!-- wp:paragraph {"fontSize":"small"} -->
<p class="has-small-font-size">' . esc_html__( $item[1], 'dazhen' ) . '</p>
<!-- /wp:paragraph --></details>
<!-- /wp:details -->';
                }
        }
        return '<!-- wp:group {"className":"dz-faq","layout":{"type":"constrained","contentSize":"880px"}} -->
<div class="wp-block-group dz-faq">' . $markup . '</div>
<!-- /wp:group -->';
}

/**
 * Dynamic query-loop grid (products / cases / posts) with .dz-grid-card items.
 */
function dazhen_layout_query_grid( string $post_type, int $per_page = 6, string $order = 'asc', string $order_by = 'menuOrder' ): string {
        $attrs = '"query":{"perPage":' . $per_page . ',"pages":0,"offset":0,"postType":"' . $post_type . '","order":"' . $order . '","orderBy":"' . $order_by . '","author":"","search":"","exclude":[],"sticky":"","inherit":false},"layout":{"type":"default"}';

        return '<!-- wp:query {' . $attrs . '} -->
<div class="wp-block-query"><!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
<!-- wp:group {"className":"dz-grid-card","layout":{"type":"constrained"}} -->
<div class="wp-block-group dz-grid-card"><!-- wp:post-featured-image {"isLink":true,"aspectRatio":"4/3","style":{"border":{"radius":"10px"}}} /-->

<!-- wp:post-title {"level":3,"isLink":true,"fontSize":"medium"} /-->

<!-- wp:post-excerpt {"excerptLength":18,"fontSize":"small"} /--></div>
<!-- /wp:group -->
<!-- /wp:post-template -->

<!-- wp:query-no-results -->
<!-- wp:paragraph {"align":"center","fontSize":"small"} -->
<p class="has-text-align-center has-small-font-size">' . esc_html__( 'Nothing published here yet — add items in the admin menu and they appear automatically.', 'dazhen' ) . '</p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results --></div>
<!-- /wp:query -->';
}

/**
 * Inquiry form (shortcode renders the full designed section).
 */
function dazhen_layout_inquiry(): string {
        return '<!-- wp:shortcode -->
[dazhen_inquiry_form]
<!-- /wp:shortcode -->';
}

/* ---------------------------------------------------------------------
 * Section builders (reused by the homepage layout AND section patterns).
 * ------------------------------------------------------------------- */

/**
 * Homepage hero — deep zone, centered, two buttons.
 */
function dazhen_layout_home_hero(): string {
        return dazhen_layout_zone(
                dazhen_layout_eyebrow( 'HIGH-END ICE & HEAT TECHNOLOGY SPECIALIST', 'a' ) . '
<!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"lineHeight":"1.1"}},"fontSize":"xx-large"} -->
<h1 class="wp-block-heading has-text-align-center has-xx-large-font-size" style="line-height:1.1">' . esc_html__( 'Instant hot. Instant cold. Engineered in 3 seconds.', 'dazhen' ) . '</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"color":{"text":"#D6E2E7"}},"fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size has-text-color" style="color:#D6E2E7">' . esc_html__( 'Dazhen designs and manufactures smart water appliances for global brands — from home faucets to hotel engineering systems, with one-stop OEM/ODM service.', 'dazhen' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/products/">' . esc_html__( 'Explore Products', 'dazhen' ) . '</a></div>
<!-- /wp:button -->

<!-- wp:button {"style":{"border":{"width":"1px","color":"#00C2D1"},"color":{"text":"#00C2D1"},"spacing":{"padding":{"left":"var:preset|spacing|40","right":"var:preset|spacing|40","top":"var:preset|spacing|30","bottom":"var:preset|spacing|30"}}}} -->
<div class="wp-block-button has-border-color has-custom-border-color"><a class="wp-block-button__link has-text-color has-border-color wp-element-button" href="/contact/" style="border-color:#00C2D1;border-width:1px;color:#00C2D1;padding-top:var(--wp--preset--spacing--30);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--30);padding-left:var(--wp--preset--spacing--40)">' . esc_html__( 'Talk to Our Team', 'dazhen' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->',
                'a',
                'hero-home-blocks'
        );
}

/**
 * Homepage stats strip section.
 */
function dazhen_layout_home_stats(): string {
        return dazhen_layout_stats( array(
                array( '10+', 'Years of Technology' ),
                array( '30+', 'Patented Technologies' ),
                array( '20+', 'Core R&D Engineers' ),
                array( '100', 'Units Minimum Order' ),
        ) );
}

/**
 * "Why Dazhen" feature cards (light zone).
 */
function dazhen_layout_home_why(): string {
        return dazhen_layout_zone(
                dazhen_layout_eyebrow( 'Why Dazhen', 'b' ) .
                dazhen_layout_heading( 'Four reasons global brands build with us' ) .
                dazhen_layout_spacer() .
                dazhen_layout_cards( array(
                        array( 'Self-Developed Heating Tech', 'Thick-film heating with dual-algorithm control: 3-second heat-up, precise constant temperature, tankless efficiency.' ),
                        array( 'All-Scenario Lineup', 'From home faucets to hotel engineering systems — one factory covers every drinking-water scenario.' ),
                        array( 'OEM / ODM One-Stop', 'Industrial design, certification and packaging handled in one continuous flow, from concept to shipment.' ),
                        array( 'Global Compliance', 'ISO 9001, BSCI and food-grade material standards — products ready for worldwide markets.' ),
                ), 'card-feature' ),
                'b'
        );
}

/**
 * Company story + milestones (light zone).
 */
function dazhen_layout_home_about(): string {
        return dazhen_layout_zone(
                dazhen_layout_eyebrow( 'About Dazhen', 'b' ) .
                dazhen_layout_heading( 'Ten years of quiet, serious engineering' ) .
                dazhen_layout_sub( 'A research team founded in 2012, now a full manufacturing platform in Dayawan, Huizhou.' ) .
                dazhen_layout_milestones(),
                'b'
        );
}

/**
 * Core technology cards + chip row (deep zone).
 */
function dazhen_layout_home_tech(): string {
        return dazhen_layout_zone(
                dazhen_layout_eyebrow( 'Core Technology', 'a' ) .
                dazhen_layout_heading( 'One platform, every water technology' ) .
                dazhen_layout_cards( array(
                        array( 'Instant Heating', 'Tankless thick-film heating — 3-second heat-up and zero stored-water heat loss.' ),
                        array( 'Hot-Cold Integration', 'Heating and cooling unified on one platform — one machine serves tea, ice water and everything between.' ),
                        array( 'Purification (UV + RO)', 'UV sterilization and reverse-osmosis filtration built into the water path.' ),
                        array( 'Specialized Platforms', 'Hydrogen-rich, boil-and-cool and sparkling water — differentiated SKUs for premium markets.' ),
                ), 'card-tech' ) .
                dazhen_layout_chips(),
                'a'
        );
}

/**
 * Products grid section — live query of the product catalog.
 */
function dazhen_layout_home_products(): string {
        return dazhen_layout_zone(
                dazhen_layout_eyebrow( 'Product Lineup', 'b' ) .
                dazhen_layout_heading( 'Built on four proven platforms' ) .
                dazhen_layout_sub( 'A live grid of your product catalog — new products appear here automatically.' ) .
                dazhen_layout_query_grid( 'product', 6 ),
                'b'
        );
}

/**
 * Solution scenario cards (light zone).
 */
function dazhen_layout_home_solutions(): string {
        return dazhen_layout_zone(
                dazhen_layout_eyebrow( 'Solutions & Success Cases', 'b' ) .
                dazhen_layout_heading( 'Chosen by distributors, hotels, schools and brands' ) .
                dazhen_layout_sub( 'Scenario-matched lineups, proven in real deployments across continents.' ) .
                dazhen_layout_cards( array(
                        array( 'Household', 'Compact instant-heat taps and countertop units for family kitchens.' ),
                        array( 'Office & Commercial', 'High-duty systems for daily workplace hydration.' ),
                        array( 'Education & Medical', 'Safe-temperature dispensing with child-proof controls.' ),
                        array( 'Hotel & Engineering', 'Wall-mounted and embedded units engineered for renovation projects.' ),
                ), 'card-solution' ),
                'b'
        );
}

/**
 * Milestones columns (shared by home and about layouts).
 */
function dazhen_layout_milestones(): string {
        $milestones = array(
                array( '2012', __( 'R&D center established — three core research directions', 'dazhen' ) ),
                array( '2014', __( 'Industry-first nano rare-earth thick-film heating', 'dazhen' ) ),
                array( '2021', __( 'Instant heating + refrigeration integration breakthrough', 'dazhen' ) ),
                array( '2025', __( 'Full product ecosystem and global layout', 'dazhen' ) ),
        );

        $cols = '';
        foreach ( $milestones as $m ) {
                $cols .= '<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3,"textColor":"teal","fontSize":"x-large"} -->
<h3 class="wp-block-heading has-teal-color has-text-color has-x-large-font-size">' . esc_html( $m[0] ) . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"color":{"text":"#5B6B73"}},"fontSize":"small"} -->
<p class="has-small-font-size has-text-color" style="color:#5B6B73">' . esc_html( $m[1] ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->';
        }

        return dazhen_layout_spacer() . '
<!-- wp:columns -->
<div class="wp-block-columns">' . $cols . '</div>
<!-- /wp:columns -->';
}

/**
 * Technology chip row (shared by home and about layouts).
 */
function dazhen_layout_chips(): string {
        $chips  = array( 'Nano rare-earth thick film', 'Dual-algorithm control', 'UV sterilization', 'RO filtration', 'Hydrogen-rich water', 'Boil-and-cool platform', 'Sparkling water', 'Food-grade materials' );
        $markup = '';
        foreach ( $chips as $chip ) {
                $markup .= '<!-- wp:paragraph {"className":"chip chip--a","fontSize":"small"} -->
<p class="chip chip--a has-small-font-size">' . esc_html__( $chip, 'dazhen' ) . '</p>
<!-- /wp:paragraph -->';
        }

        return dazhen_layout_spacer() . '
<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group">' . $markup . '</div>
<!-- /wp:group -->';
}

/**
 * Amber CTA banner (deep zone).
 */
function dazhen_layout_cta(): string {
        return '<!-- wp:group {"align":"full","className":"zone-a section cta-bottom","layout":{"type":"constrained","contentSize":"1280px"}} -->
<div class="wp-block-group alignfull zone-a section cta-bottom"><!-- wp:group {"className":"cta-bottom__card","layout":{"type":"constrained"}} -->
<div class="wp-block-group cta-bottom__card"><!-- wp:heading {"textAlign":"center","level":2,"fontSize":"x-large"} -->
<h2 class="wp-block-heading has-text-align-center has-x-large-font-size">' . esc_html__( 'Ready to spec your next water appliance?', 'dazhen' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"color":{"text":"#D6E2E7"}},"fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size has-text-color" style="color:#D6E2E7">' . esc_html__( 'Tell us your market, volume and target price — our engineers reply within one business day.', 'dazhen' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/contact/">' . esc_html__( 'Get a Quote', 'dazhen' ) . '</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->';
}

/**
 * Contact info cards section.
 */
function dazhen_layout_contact_cards(): string {
        return dazhen_layout_zone(
                dazhen_layout_eyebrow( 'Contact', 'b' ) .
                dazhen_layout_heading( 'Talk to our team' ) .
                dazhen_layout_cards( array(
                        array( 'Phone · 电话', '+86 180 2699 3135' ),
                        array( 'Email · 邮箱', '2046772003@qq.com' ),
                        array( 'Factory · 厂址', 'Dayawan, Huizhou City, Guangdong, China' ),
                ), 'card-solution' ) .
                dazhen_layout_sub( 'Working hours: Monday–Saturday, 9:00–18:30 (GMT+8, China Standard Time).' ),
                'b'
        );
}

/**
 * FAQ section (services copy).
 */
function dazhen_layout_faq_section(): string {
        return dazhen_layout_zone(
                dazhen_layout_eyebrow( 'FAQ', 'b' ) .
                dazhen_layout_heading( 'Answers before you ask' ) .
                dazhen_layout_faq( array(
                        array(
                                'OEM & ODM',
                                array(
                                        array( 'What is the minimum order quantity?', 'Most series start at 100 units for OEM customization; standard models can be trialed in smaller quantities.' ),
                                        array( 'How long does customization take?', 'Typically 4–6 weeks from spec confirmation to shipment, depending on certification scope.' ),
                                        array( 'Can you develop a fully custom product, or only rebrand existing models?', 'Both. Most programs start from a proven platform — instant-heat, ice-water, sparkling or filtration — and customize appearance, parameters and packaging; ground-up ODM development is available with our in-house industrial design team.' ),
                                ),
                        ),
                        array(
                                'Product',
                                array(
                                        array( 'Which certifications do you support?', 'ISO 9001 and BSCI factories, food-grade materials; CE/CB and market-specific schemes are handled per program.' ),
                                        array( 'How do we evaluate samples before committing to production?', 'Sample units ship with lab reports covering heating performance, flow rates and safety tests. For qualified programs, pilot-run units can be arranged so your team can validate the product in-market before mass production.' ),
                                ),
                        ),
                        array(
                                'After-sales',
                                array(
                                        array( 'What does the warranty cover?', 'Every OEM program includes a one-year warranty from shipment. Claims are handled with replacement parts or units, coordinated through your account engineer.' ),
                                        array( 'Are spare parts available after delivery?', 'Yes. Serviceable module sets and key spare parts are listed at spec confirmation, so your service team knows exactly what to stock for after-sales support.' ),
                                ),
                        ),
                        array(
                                'Payment & Logistics',
                                array(
                                        array( 'Which payment terms do you accept?', 'Common terms are T/T and L/C at sight; the exact deposit-and-balance split is agreed per program before production starts.' ),
                                        array( 'Do you ship by sea or by air?', 'Both. Sea freight in export cartons is standard for full programs; air freight is available for samples and urgent pilot runs. Export documents are handled by our team.' ),
                                ),
                        ),
                ) ),
                'b'
        );
}

/* ---------------------------------------------------------------------
 * Full-page layouts.
 * ------------------------------------------------------------------- */

/**
 * Homepage — complete block version.
 */
function dazhen_layout_home(): string {
        return dazhen_layout_home_hero() .
                dazhen_layout_home_stats() .
                dazhen_layout_home_why() .
                dazhen_layout_home_about() .
                dazhen_layout_home_tech() .
                dazhen_layout_home_products() .
                dazhen_layout_home_solutions() .
                dazhen_layout_cta();
}

/**
 * About page — hero, story, stats, milestones, tech, certifications, CTA.
 */
function dazhen_layout_about(): string {
        return dazhen_layout_page_hero( 'About Dazhen', 'Making water better since 2012.', 'A research-driven manufacturer of smart water appliances in Dayawan, Huizhou — designing the platforms itself, then manufacturing, certifying and shipping them for brands worldwide.' ) .
                dazhen_layout_zone(
                        dazhen_layout_eyebrow( 'Our story', 'b' ) .
                        dazhen_layout_heading( 'From a research team to a manufacturing platform' ) .
                        dazhen_layout_sub( 'A research team founded in 2012, now a full manufacturing platform in Dayawan, Huizhou.' ) . '
<!-- wp:paragraph -->
<p>' . esc_html__( 'We build the heating, cooling and filtration platforms in-house — every heating curve, safety lockout and filter stage is our own engineering. That is why brands come to us when the specification matters.', 'dazhen' ) . '</p>
<!-- /wp:paragraph -->',
                        'b'
                ) .
                dazhen_layout_stats( array(
                        array( '10+', 'Years of technology' ),
                        array( '30+', 'Patented technologies' ),
                        array( '20+', 'Core R&D engineers' ),
                        array( '4', 'Application scenarios' ),
                ) ) .
                dazhen_layout_zone(
                        dazhen_layout_eyebrow( 'Milestones', 'b' ) .
                        dazhen_layout_heading( 'Ten years of quiet, serious engineering' ) .
                        dazhen_layout_milestones(),
                        'b'
                ) .
                dazhen_layout_home_tech() .
                dazhen_layout_zone(
                        dazhen_layout_eyebrow( 'Compliance', 'b' ) .
                        dazhen_layout_heading( 'Certified for worldwide markets' ) .
                        dazhen_layout_cards( array(
                                array( 'ISO 9001:2015', 'Certified quality management across design, production and shipment.' ),
                                array( 'BSCI', 'Social compliance audited for international retail supply chains.' ),
                                array( 'CE · CB · RoHS', 'Product safety and material compliance for EU and global markets.' ),
                        ), 'card-solution' ) .
                        dazhen_layout_cta_note(),
                        'b'
                ) .
                dazhen_layout_cta();
}

/**
 * Small helper: muted note paragraph (certs section).
 */
function dazhen_layout_cta_note(): string {
        return dazhen_layout_sub( 'Certificates and factory audit reports are shared under NDA during program talks.' );
}

/**
 * Services page — hero, program stats, advantages, OEM journey, FAQ, inquiry form.
 */
function dazhen_layout_services(): string {
        return dazhen_layout_page_hero( 'OEM / ODM Services', 'Your brand. Our engineering.', 'A one-stop OEM/ODM partner for smart water appliances — from the first requirement talk to container loading, one team owns your program.' ) .
                dazhen_layout_stats( array(
                        array( '100 units', 'MOQ' ),
                        array( '4–6 weeks', 'Lead time' ),
                        array( '1 year', 'Warranty' ),
                        array( 'Global', 'After-sales' ),
                ) ) .
                dazhen_layout_zone(
                        dazhen_layout_eyebrow( 'What we do', 'b' ) .
                        dazhen_layout_heading( 'Manufacturing for brands that ship worldwide' ) .
                        dazhen_layout_sub( 'Proven platforms, in-house R&D and certified production — customized to your market.' ) .
                        dazhen_layout_cards( array(
                                array( 'Full customization', 'Appearance, parameters and packaging tailored to your brand — built on proven instant-heat, cooling and filtration platforms.' ),
                                array( 'Faster to market', 'Proven platforms and an in-house R&D team keep development tight — typically 4–6 weeks from spec freeze to shipment.' ),
                                array( 'Global compliance', 'ISO 9001 and BSCI factories with food-grade materials; CE/CB and market-specific schemes are handled per program.' ),
                                array( 'Experienced team', '10+ years of technology accumulation, 30+ patents and 20+ core R&D engineers behind every program.' ),
                        ), 'card-feature' ),
                        'b'
                ) .
                dazhen_layout_zone(
                        dazhen_layout_eyebrow( 'OEM Journey', 'a' ) .
                        dazhen_layout_heading( 'How an OEM program runs' ) .
                        dazhen_layout_sub( 'From first message to container loading — typically 4–6 weeks from spec freeze to shipment.', '#D6E2E7' ) .
                        dazhen_layout_steps( array(
                                array( 'Requirement talk', 'Week 0', 'Every program starts with a focused conversation: your market, volume, target price and certifications.' ),
                                array( 'Concept development', 'Week 1–2', 'Industrial designers translate the brief into appearance concepts and a complete technical proposal.' ),
                                array( 'Spec confirmation', 'Week 2', 'Together we lock every parameter — heating power, modes, materials, finish and packaging layout.' ),
                                array( 'Sampling & testing', 'Week 3–4', 'Prototype units run through lab testing — heating curves, flow rates, safety and endurance.' ),
                                array( 'Production & QC', 'Week 4–5', 'Your program runs on an ISO 9001 certified line with in-line inspection at key stations.' ),
                                array( 'Packaging & shipping', 'Week 5–6', 'Retail-ready or bulk, units are packed into export cartons; loading is documented with photos.' ),
                                array( 'After-delivery support', 'Ongoing', 'A one-year warranty covers the program; field feedback feeds the next batch iteration.' ),
                        ) ),
                        'a'
                ) .
                dazhen_layout_faq_section() .
                dazhen_layout_inquiry();
}

/**
 * Solutions page — hero, scenario cards, live case grid, CTA.
 */
function dazhen_layout_solutions(): string {
        return dazhen_layout_page_hero( 'Solutions & Success Cases', 'Scenario-matched lineups, proven in the field.', 'From family kitchens to hotel engineering — the deployments that proved them.' ) .
                dazhen_layout_home_solutions() .
                dazhen_layout_zone(
                        dazhen_layout_eyebrow( 'Field references', 'a' ) .
                        dazhen_layout_heading( 'Recent deployments' ) .
                        dazhen_layout_sub( 'A live grid of your case studies — new cases appear here automatically.', '#D6E2E7' ) .
                        dazhen_layout_query_grid( 'case_study', 6, 'desc', 'date' ),
                        'a'
                ) .
                dazhen_layout_cta();
}

/**
 * Contact page — hero, contact cards, inquiry form.
 */
function dazhen_layout_contact(): string {
        return dazhen_layout_page_hero( 'Contact us', 'Let’s talk about your program.', 'A question, a spec or a full program brief — send it over. A sales engineer replies within one business day.' ) .
                dazhen_layout_contact_cards() .
                dazhen_layout_inquiry();
}

/**
 * Generic starter page — hero, two-column intro, feature cards, CTA.
 */
function dazhen_layout_generic(): string {
        return dazhen_layout_page_hero( 'Dazhen', 'Your page title goes here', 'Replace this intro with your own copy — every block below can be edited, moved or deleted.' ) .
                dazhen_layout_zone(
                        dazhen_layout_eyebrow( 'Section', 'b' ) .
                        dazhen_layout_heading( 'A two-column section with structure' ) .
                        '<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"width":"58.33%"}>
<div class="wp-block-column" style="flex-basis:58.33%"><!-- wp:paragraph -->
<p>' . esc_html__( 'Use this column for running text. The block toolbar lets you change widths, duplicate or remove columns — the layout adapts on mobile automatically.', 'dazhen' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>' . esc_html__( 'Mix native blocks with any plugin blocks (Stackable, Kadence…) — the theme styles stay consistent.', 'dazhen' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"41.67%"}>
<div class="wp-block-column" style="flex-basis:41.67%"><!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>' . esc_html__( 'Bullet point one', 'dazhen' ) . '</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>' . esc_html__( 'Bullet point two', 'dazhen' ) . '</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>' . esc_html__( 'Bullet point three', 'dazhen' ) . '</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->',
                        'b'
                ) .
                dazhen_layout_home_why() .
                dazhen_layout_cta();
}

/* ---------------------------------------------------------------------
 * Layout registry + page matcher.
 * ------------------------------------------------------------------- */

/**
 * The catalogue of seedable full-page layouts.
 *
 * @return array<string, array{title: string, description: string, render: string}>
 */
function dazhen_block_layouts(): array {
        return array(
                'home'      => array(
                        'title'       => __( 'Homepage · 首页(A×B 完整版)', 'dazhen' ),
                        'description' => __( 'Hero, stats, reasons, milestones, core tech, live product grid, solutions and CTA.', 'dazhen' ),
                        'render'      => 'dazhen_layout_home',
                ),
                'about'     => array(
                        'title'       => __( 'About Us · 关于我们', 'dazhen' ),
                        'description' => __( 'Story, stats, milestones, technology, certifications and CTA.', 'dazhen' ),
                        'render'      => 'dazhen_layout_about',
                ),
                'services'  => array(
                        'title'       => __( 'OEM/ODM Services · 服务(含 FAQ 与询盘)', 'dazhen' ),
                        'description' => __( 'Program stats, advantages, the 7-step OEM journey, FAQ accordions and the inquiry form.', 'dazhen' ),
                        'render'      => 'dazhen_layout_services',
                ),
                'solutions' => array(
                        'title'       => __( 'Solutions & Cases · 方案与案例(动态网格)', 'dazhen' ),
                        'description' => __( 'Scenario cards plus a live grid of your case studies.', 'dazhen' ),
                        'render'      => 'dazhen_layout_solutions',
                ),
                'contact'   => array(
                        'title'       => __( 'Contact · 联系我们(含询盘表单)', 'dazhen' ),
                        'description' => __( 'Contact cards and the 7-field inquiry form.', 'dazhen' ),
                        'render'      => 'dazhen_layout_contact',
                ),
                'generic'   => array(
                        'title'       => __( 'Starter page · 通用起步页', 'dazhen' ),
                        'description' => __( 'Hero, two-column intro, feature cards and CTA — for any new page.', 'dazhen' ),
                        'render'      => 'dazhen_layout_generic',
                ),
        );
}

/**
 * Render a layout's markup by slug.
 *
 * @return string|null Markup, or null for an unknown slug.
 */
function dazhen_get_block_layout( string $slug ): ?string {
        $layouts = dazhen_block_layouts();
        if ( ! isset( $layouts[ $slug ] ) || ! is_callable( $layouts[ $slug ]['render'] ) ) {
                return null;
        }
        return (string) call_user_func( $layouts[ $slug ]['render'] );
}

/**
 * Pick the layout that best matches an existing page (front page,
 * page template or slug heuristics).
 */
function dazhen_match_page_layout( $post ): string {
        $post = get_post( $post );
        if ( ! $post instanceof WP_Post ) {
                return 'generic';
        }

        if ( 'page' === get_option( 'show_on_front' ) && (int) get_option( 'page_on_front' ) === $post->ID ) {
                return 'home';
        }

        $template_map = array(
                'page-templates/about.php'            => 'about',
                'page-templates/services.php'         => 'services',
                'page-templates/contact.php'          => 'contact',
                'page-templates/solutions-overview.php' => 'solutions',
        );
        $template = get_page_template_slug( $post );
        if ( isset( $template_map[ $template ] ) ) {
                return $template_map[ $template ];
        }

        $slug_map = array(
                'about'    => 'about',
                'service'  => 'services',
                'contact'  => 'contact',
                'solution' => 'solutions',
                'home'     => 'home',
        );
        $slug = (string) $post->post_name;
        foreach ( $slug_map as $needle => $layout ) {
                if ( false !== strpos( $slug, $needle ) ) {
                        return $layout;
                }
        }

        return 'generic';
}
