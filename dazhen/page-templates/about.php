<?php
/**
 * Template Name: About
 * Template Post Type: page
 *
 * The /about/ page: slogan hero, company intro + stats strip, vertical
 * milestones timeline, core technology cards (zone A), certification badge
 * wall, factory & manufacturing module, inline CTA.
 *
 * Filters (defaults mirror the confirmed design, all overridable):
 * - dazhen_about_slogan         : hero headline string.
 * - dazhen_about_stats          : array( array( 'value' => ..., 'label' => ... ) ).
 * - dazhen_about_milestones     : array( array( 'year' => ..., 'event' => ..., 'current' => bool ) ).
 * - dazhen_about_tech           : array of cards (icon: thickfilm|dualalgo|hotcold|platforms).
 * - dazhen_about_tech_products  : array( array( 'model' => ..., 'role' => ... ) ) — matched against
 *                                 the product CPT `_dz_product_model` meta for thumbnails/links.
 * - dazhen_about_certs          : array( array( 'lines' => array, 'title' => ..., 'text' => ... ) ).
 * - dazhen_about_factory_chips  : array of capability chip strings.
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


/**
 * Return the inline line-icon SVG for an About core-tech card.
 *
 * @param string $key Icon key.
 * @return string SVG markup (empty string for unknown keys).
 */
function dazhen_about_tech_icon( string $key ): string {
	$icons = array(
		'thickfilm' => '<rect x="4" y="11" width="16" height="8.5" rx="2"/><path d="M6.5 15.5h2l1.5-2.5 2 4 1.5-2.5h4"/><path d="M9 4.5c-.8 1-.8 2 0 3M14 4.5c-.8 1-.8 2 0 3"/>',
		'dualalgo'  => '<rect x="6.5" y="6.5" width="11" height="11" rx="2"/><path d="M9.5 3.5v3M14.5 3.5v3M9.5 17.5v3M14.5 17.5v3M3.5 9.5h3M3.5 14.5h3M17.5 9.5h3M17.5 14.5h3"/><circle cx="12" cy="12" r="2.2"/>',
		'hotcold'   => '<path d="M12 3.5C9.2 7.6 6.8 10 6.8 13.6a5.2 5.2 0 0 0 10.4 0C17.2 10 14.8 7.6 12 3.5Z"/><path d="M8.4 13.4c.7-.7 1.5-.7 2.2 0"/><path d="M14 11.9v3.2M12.7 12.7l2.6 1.6M15.3 12.7l-2.6 1.6"/>',
		'platforms' => '<path d="M7 3.5h10l-1.1 15.6a2.1 2.1 0 0 1-2.1 1.9h-3.6a2.1 2.1 0 0 1-2.1-1.9L7 3.5Z"/><path d="M8.2 9.7h7.6"/><circle cx="10.6" cy="13.2" r="1.1"/><circle cx="13.8" cy="15.3" r="1.4"/><circle cx="12" cy="17.7" r="0.8"/>',
	);
	return isset( $icons[ $key ] )
		? '<svg class="tech-card__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $icons[ $key ] . '</svg>'
		: '';
}

/**
 * Return the self-drawn certification rosette badge (ribbon + dashed ring).
 *
 * Badge line fragments are certification marks — deliberately not translatable;
 * the card title/text carry the meaning. Override via the dazhen_about_certs filter.
 *
 * @param array $lines Badge text lines (1 or 2 lines).
 * @return string SVG markup.
 */
function dazhen_about_cert_badge( array $lines ): string {
	$lines = array_values( array_filter( array_map( 'strval', $lines ) ) );
	if ( empty( $lines ) ) {
		return '';
	}

	$y_positions = count( $lines ) > 1 ? array( 27.5, 37.5 ) : array( 33.5 );

	$svg  = '<svg class="cert-badge" viewBox="0 0 64 76" aria-hidden="true" focusable="false">';
	$svg .= '<g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">';
	$svg .= '<path d="M25.5 50.5 20 70.5l9.8-4.6"/>';
	$svg .= '<path d="M38.5 50.5 44 70.5l-9.8-4.6"/>';
	$svg .= '<circle cx="32" cy="30" r="22"/>';
	$svg .= '<circle cx="32" cy="30" r="16.5" stroke-dasharray="2.5 3.5" stroke-width="1.4"/>';
	$svg .= '</g>';

	foreach ( $lines as $index => $line ) {
		$y    = $y_positions[ min( $index, count( $y_positions ) - 1 ) ];
		$svg .= sprintf(
			'<text x="32" y="%s" text-anchor="middle" fill="currentColor" font-size="9" font-weight="700" letter-spacing="0.5">%s</text>',
			esc_attr( (string) $y ),
			esc_html( $line )
		);
	}

	$svg .= '</svg>';
	return $svg;
}

get_header();

while ( have_posts() ) :
	the_post();

	$slogan = apply_filters(
		'dazhen_about_slogan',
		__( 'Making water better since 2012.', 'dazhen' )
	);
	$stats = apply_filters(
		'dazhen_about_stats',
		array(
			array(
				'value' => '10+',
				'label' => __( 'Years of technology', 'dazhen' ),
			),
			array(
				'value' => '30+',
				'label' => __( 'Patented technologies', 'dazhen' ),
			),
			array(
				'value' => '20+',
				'label' => __( 'Core R&D engineers', 'dazhen' ),
			),
			array(
				'value' => '4',
				'label' => __( 'Application scenarios', 'dazhen' ),
			),
		)
	);
	$milestones = apply_filters(
		'dazhen_about_milestones',
		array(
			array(
				'year'  => '2012',
				'event' => __( 'R&D center established with three core research directions.', 'dazhen' ),
			),
			array(
				'year'  => '2014',
				'event' => __( 'Industry-first nano rare-earth thick-film heating platform.', 'dazhen' ),
			),
			array(
				'year'  => '2018',
				'event' => __( 'Global strategy launched — certified volumes begin reaching overseas markets.', 'dazhen' ),
			),
			array(
				'year'  => '2021',
				'event' => __( 'Instant heating + refrigeration integration breakthrough.', 'dazhen' ),
			),
			array(
				'year'   => '2025',
				'event'  => __( 'Full product ecosystem and global market layout.', 'dazhen' ),
				'current' => true,
			),
		)
	);
	$tech = apply_filters(
		'dazhen_about_tech',
		array(
			array(
				'icon'  => 'thickfilm',
				'title' => __( 'Thick-film heating', 'dazhen' ),
				'text'  => __( 'A nano rare-earth thick-film element heats water as it flows — dispensing temperature in about three seconds, then holds it steady. No stored tank, no re-boiling.', 'dazhen' ),
			),
			array(
				'icon'  => 'dualalgo',
				'title' => __( 'Dual-algorithm control', 'dazhen' ),
				'text'  => __( 'Two control algorithms coordinate temperature and flow in real time, keeping output stable across seasons, voltages and supply pressures.', 'dazhen' ),
			),
			array(
				'icon'  => 'hotcold',
				'title' => __( 'Integrated hot & cold', 'dazhen' ),
				'text'  => __( 'Instant heating and refrigeration combined in one system — an integration Dazhen pioneered in 2021 for built-in and under-sink formats.', 'dazhen' ),
			),
			array(
				'icon'  => 'platforms',
				'title' => __( 'Water platforms', 'dazhen' ),
				'text'  => __( 'Hydrogen-rich, boil-and-cool and sparkling-water platforms — differentiated SKUs your market can grow into, ready for OEM programs.', 'dazhen' ),
			),
		)
	);
	$tech_products = apply_filters(
		'dazhen_about_tech_products',
		array(
			array(
				'model' => '18E-2',
				'role'  => __( 'Thick-film platform', 'dazhen' ),
			),
			array(
				'model' => '18E-3',
				'role'  => __( 'Dual-algorithm control', 'dazhen' ),
			),
			array(
				'model' => 'F900-QC',
				'role'  => __( 'Hot-cold integration', 'dazhen' ),
			),
		)
	);
	$certs = apply_filters(
		'dazhen_about_certs',
		array(
			array(
				'lines' => array( 'ISO', '9001' ),
				'title' => __( 'ISO 9001', 'dazhen' ),
				'text'  => __( 'Quality management system covering the full production line.', 'dazhen' ),
			),
			array(
				'lines' => array( 'BSCI' ),
				'title' => __( 'BSCI', 'dazhen' ),
				'text'  => __( 'Social compliance, audited for international supply chains.', 'dazhen' ),
			),
			array(
				'lines' => array( 'Food', 'Grade' ),
				'title' => __( 'Food-Grade Materials', 'dazhen' ),
				'text'  => __( 'Water-contact parts meet food-grade material standards.', 'dazhen' ),
			),
			array(
				'lines' => array( 'CE · CB' ),
				'title' => __( 'CE · CB', 'dazhen' ),
				'text'  => __( 'Product safety schemes handled per program and market.', 'dazhen' ),
			),
		)
	);
	$factory_chips = apply_filters(
		'dazhen_about_factory_chips',
		array(
			__( 'ISO 9001 line', 'dazhen' ),
			__( 'In-line inspection', 'dazhen' ),
			__( 'Batch traceability', 'dazhen' ),
			__( 'Export packing', 'dazhen' ),
		)
	);

	// Match the proof thumbnails against real product CPT entries (model meta).
	$product_ids = array();
	$models      = array();
	foreach ( $tech_products as $item ) {
		if ( ! empty( $item['model'] ) ) {
			$models[] = (string) $item['model'];
		}
	}
	if ( $models ) {
		$found = new WP_Query( array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => count( $models ),
			'no_found_rows'  => true,
			'fields'         => 'ids',
			'orderby'        => 'menu_order date',
			'order'          => 'ASC',
			'meta_query'     => array(
				array(
					'key'     => '_dz_product_model',
					'value'   => $models,
					'compare' => 'IN',
				),
			),
		) );
		foreach ( $found->posts as $product_id ) {
			$model = get_post_meta( $product_id, '_dz_product_model', true );
			if ( $model ) {
				$product_ids[ $model ] = (int) $product_id;
			}
		}
		wp_reset_postdata();
	}
	?>

	<header class="zone-a page-hero about-hero">
		<div class="container">
			<?php dazhen_breadcrumbs(); ?>
			<p class="eyebrow eyebrow--a"><?php esc_html_e( 'About Dazhen', 'dazhen' ); ?></p>
			<h1 class="page-hero__title"><?php echo esc_html( $slogan ); ?></h1>
			<p class="page-hero__sub"><?php esc_html_e( 'From a three-direction research team in Dayawan to a full OEM/ODM platform — this is the engineering behind the water your brand pours.', 'dazhen' ); ?></p>
		</div>
	</header>

	<?php /* ---------- Company intro + stats (editor content wins when present) ---------- */ ?>
	<section class="zone-b section">
		<div class="container">
			<div class="about-intro">
				<div>
					<?php
					dazhen_section_head(
						array(
							'zone'    => 'b',
							'eyebrow' => __( 'Who we are', 'dazhen' ),
							'title'   => __( 'Quiet, serious engineering since 2012', 'dazhen' ),
						)
					);
					?>
					<?php if ( trim( (string) get_the_content() ) ) : ?>
						<div class="prose about-intro__prose"><?php the_content(); ?></div>
					<?php else : ?>
						<div class="about-intro__text">
							<p><?php esc_html_e( 'Dazhen designs and manufactures smart water appliances in Dayawan, Huizhou. What began as a three-direction research team in 2012 is today a full OEM/ODM platform: nano rare-earth thick-film heating, dual-algorithm control, and integrated hot-cold systems — shipped to brands across five continents.', 'dazhen' ); ?></p>
							<p><?php esc_html_e( 'Today the Dayawan base runs as a one-stop OEM/ODM partner for global brands — household faucets, built-in stations, engineering systems and specialty units, developed, certified and shipped by one team. Wherever your market sits on the map, the appliances pouring water there can be built here.', 'dazhen' ); ?></p>
						</div>
					<?php endif; ?>
				</div>

				<figure class="about-figure">
					<?php if ( has_post_thumbnail() ) : ?>
						<span class="about-figure__media"><?php the_post_thumbnail( 'dazhen-large', array( 'alt' => esc_attr( get_the_title() ) ) ); ?></span>
						<figcaption class="about-figure__caption"><?php esc_html_e( 'The current platform lineup — faucets, built-in stations and specialty units.', 'dazhen' ); ?></figcaption>
					<?php else : ?>
						<span class="about-figure__blank" aria-hidden="true">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M14.5 4h-5L7.5 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3.5z"/><circle cx="12" cy="13" r="3"/></svg>
							<span class="about-figure__blank-label"><?php esc_html_e( 'Family lineup photo pending — set it as this page’s featured image.', 'dazhen' ); ?></span>
						</span>
						<figcaption class="about-figure__caption"><?php esc_html_e( 'The current platform lineup — faucets, built-in stations and specialty units.', 'dazhen' ); ?></figcaption>
					<?php endif; ?>
				</figure>
			</div>

			<?php if ( ! empty( $stats ) ) : ?>
				<dl class="about-stats">
					<?php foreach ( $stats as $stat ) : ?>
						<div class="about-stats__item">
							<dt class="about-stats__label"><?php echo esc_html( $stat['label'] ); ?></dt>
							<dd class="about-stats__value"><?php echo esc_html( $stat['value'] ); ?></dd>
						</div>
					<?php endforeach; ?>
				</dl>
			<?php endif; ?>
		</div>
	</section>

	<?php /* ---------- Vertical milestones timeline ---------- */ ?>
	<?php if ( ! empty( $milestones ) ) : ?>
		<section class="zone-b zone-b--alt section">
			<div class="container">
				<?php
				dazhen_section_head(
					array(
						'zone'    => 'b',
						'eyebrow' => __( 'Milestones', 'dazhen' ),
						'title'   => __( 'Thirteen years, five turning points', 'dazhen' ),
						'sub'     => __( 'The short version of a longer story — research directions first, platforms second, the world third.', 'dazhen' ),
						'center'  => true,
					)
				);
				?>
				<ol class="about-timeline" aria-label="<?php esc_attr_e( 'Company milestones', 'dazhen' ); ?>">
					<?php foreach ( $milestones as $milestone ) : ?>
						<?php $is_current = ! empty( $milestone['current'] ); ?>
						<li class="about-timeline__item<?php echo $is_current ? ' about-timeline__item--current' : ''; ?>">
							<span class="about-timeline__dot" aria-hidden="true"></span>
							<span class="about-timeline__head">
								<span class="about-timeline__year"><?php echo esc_html( (string) $milestone['year'] ); ?></span>
								<?php if ( $is_current ) : ?>
									<span class="about-timeline__now"><?php esc_html_e( 'Now', 'dazhen' ); ?></span>
								<?php endif; ?>
							</span>
							<p class="about-timeline__event"><?php echo esc_html( (string) $milestone['event'] ); ?></p>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
		</section>
	<?php endif; ?>

	<?php /* ---------- R&D and core technology (zone A) ---------- */ ?>
	<?php if ( ! empty( $tech ) ) : ?>
		<section class="zone-a section">
			<div class="container">
				<?php
				dazhen_section_head(
					array(
						'zone'    => 'a',
						'eyebrow' => __( 'R&D and core technology', 'dazhen' ),
						'title'   => __( 'Four platforms, engineered in-house', 'dazhen' ),
						'sub'     => __( '30+ patents and 20+ core R&D engineers keep these platforms moving — and every one of them is ready to carry your brand.', 'dazhen' ),
						'center'  => true,
					)
				);
				?>
				<div class="grid grid--4 about-tech">
					<?php foreach ( $tech as $card ) : ?>
						<article class="tech-card" data-dz-reveal>
							<span class="tech-card__icon-wrap">
								<?php echo dazhen_about_tech_icon( isset( $card['icon'] ) ? (string) $card['icon'] : '' ); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG markup. ?>
							</span>
							<h3 class="tech-card__title"><?php echo esc_html( (string) $card['title'] ); ?></h3>
							<p class="tech-card__text"><?php echo esc_html( (string) $card['text'] ); ?></p>
						</article>
					<?php endforeach; ?>
				</div>

				<?php if ( ! empty( $tech_products ) ) : ?>
					<div class="tech-proof">
						<p class="tech-proof__label"><?php esc_html_e( 'Shipping today on these platforms', 'dazhen' ); ?></p>
						<div class="tech-proof__grid">
							<?php
							foreach ( $tech_products as $item ) :
								$model    = isset( $item['model'] ) ? (string) $item['model'] : '';
								$role     = isset( $item['role'] ) ? (string) $item['role'] : '';
								$pid      = isset( $product_ids[ $model ] ) ? (int) $product_ids[ $model ] : 0;
								$tag      = $pid ? 'a' : 'div';
								$href_att = $pid ? ' href="' . esc_url( (string) get_permalink( $pid ) ) . '"' : '';
								?>
								<<?php echo esc_html( $tag ); ?> class="tech-proof__item"<?php echo $href_att; // phpcs:ignore WordPress.Security.EscapeOutput -- assembled with esc_url() above. ?>>
									<?php if ( $pid && has_post_thumbnail( $pid ) ) : ?>
										<?php echo get_the_post_thumbnail( $pid, 'dazhen-card', array( 'class' => 'tech-proof__thumb' ) ); ?>
									<?php else : ?>
										<span class="tech-proof__thumb tech-proof__thumb--placeholder" aria-hidden="true">DZ</span>
									<?php endif; ?>
									<span class="tech-proof__info">
										<span class="tech-proof__model"><?php echo esc_html( $model ); ?></span>
										<span class="tech-proof__role"><?php echo esc_html( $role ); ?></span>
									</span>
								</<?php echo esc_html( $tag ); ?>>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>

	<?php /* ---------- Certification wall ---------- */ ?>
	<?php if ( ! empty( $certs ) ) : ?>
		<section class="zone-b section">
			<div class="container">
				<?php
				dazhen_section_head(
					array(
						'zone'    => 'b',
						'eyebrow' => __( 'Certifications', 'dazhen' ),
						'title'   => __( 'Compliance you can forward to your buyers', 'dazhen' ),
						'sub'     => __( 'The paperwork behind the promise — audited factories, food-safe materials and market-specific product safety schemes.', 'dazhen' ),
						'center'  => true,
					)
				);
				?>
				<div class="grid grid--4 about-certs">
					<?php foreach ( $certs as $cert ) : ?>
						<article class="cert-card" data-dz-reveal>
							<span class="cert-card__badge">
								<?php echo dazhen_about_cert_badge( is_array( $cert['lines'] ?? null ) ? $cert['lines'] : array() ); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG markup with escaped text nodes. ?>
							</span>
							<h3 class="cert-card__title"><?php echo esc_html( (string) $cert['title'] ); ?></h3>
							<p class="cert-card__text"><?php echo esc_html( (string) $cert['text'] ); ?></p>
						</article>
					<?php endforeach; ?>
				</div>
				<p class="cert-note"><?php esc_html_e( 'Certificate scans to be provided by the client — badge graphics shown as placeholders.', 'dazhen' ); ?></p>
			</div>
		</section>
	<?php endif; ?>

	<?php /* ---------- Factory & manufacturing (photos pending from the client) ---------- */ ?>
	<section class="zone-b zone-b--alt section">
		<div class="container">
			<?php
			dazhen_section_head(
				array(
					'zone'    => 'b',
					'eyebrow' => __( 'Factory & manufacturing', 'dazhen' ),
					'title'   => __( 'Made in Dayawan, shipped worldwide', 'dazhen' ),
					'sub'     => __( 'Programs run on an ISO 9001 certified line with in-line inspection at key stations; batch traceability is maintained from component intake to final assembly. Retail-ready or bulk, units leave in export cartons with container loading documented photo by photo — from UK distributor programs to hotel projects across the Middle East.', 'dazhen' ),
					'center'  => true,
				)
			);
			?>
			<div class="factory-media">
				<figure class="factory-media__frame">
					<span class="factory-media__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M14.5 4h-5L7.5 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3.5z"/><circle cx="12" cy="13" r="3"/></svg>
					</span>
					<figcaption>
						<p class="factory-media__label"><?php esc_html_e( 'Assembly line', 'dazhen' ); ?></p>
						<p class="factory-media__sub"><?php esc_html_e( 'Production floor · photo to be provided', 'dazhen' ); ?></p>
					</figcaption>
				</figure>
				<figure class="factory-media__frame">
					<span class="factory-media__icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M14.5 4h-5L7.5 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3.5z"/><circle cx="12" cy="13" r="3"/></svg>
					</span>
					<figcaption>
						<p class="factory-media__label"><?php esc_html_e( 'QC & testing area', 'dazhen' ); ?></p>
						<p class="factory-media__sub"><?php esc_html_e( 'In-line inspection · photo to be provided', 'dazhen' ); ?></p>
					</figcaption>
				</figure>
			</div>

			<?php if ( ! empty( $factory_chips ) ) : ?>
				<ul class="chip-row chip-row--center factory-chips" aria-label="<?php esc_attr_e( 'Manufacturing capabilities', 'dazhen' ); ?>">
					<?php foreach ( $factory_chips as $chip ) : ?>
						<li class="chip"><?php echo esc_html( (string) $chip ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</section>

	<?php
endwhile;

get_template_part( 'template-parts/cta-inline' );
get_footer();
