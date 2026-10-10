<?php
/**
 * Template Name: Services
 * Template Post Type: page
 *
 * The /services/ page: OEM/ODM value proposition, commercial terms strip,
 * four advantages, the interactive OEM journey timeline, grouped FAQ
 * accordion and a prefilled inquiry form.
 *
 * Filters:
 * - dazhen_services_slogan      : hero headline string.
 * - dazhen_services_stats       : array( array( 'value' => ..., 'label' => ... ) ).
 * - dazhen_services_advantages  : array of advantage arrays (icon: sliders|stopwatch|shield|team).
 * - dazhen_services_faq_groups  : array( array( 'title' => ..., 'items' => array( array( 'q' => ..., 'a' => ... ) ) ) ).
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
 * Return the inline line-icon SVG for a services advantage card.
 *
 * @param string $key Icon key.
 * @return string SVG markup (empty string for unknown keys).
 */
function dazhen_services_advantage_icon( string $key ): string {
	$icons = array(
		'sliders'   => '<path d="M5 4v6M5 14v6M12 4v2M12 10v10M19 4v10M19 18v2"/><circle cx="5" cy="12" r="2"/><circle cx="12" cy="8" r="2"/><circle cx="19" cy="16" r="2"/>',
		'stopwatch' => '<circle cx="12" cy="13" r="8"/><path d="M12 9v4l3 2M10 2h4M12 2v3"/>',
		'shield'    => '<path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/><path d="M8.5 12l2.5 2.5 4.5-4.5"/>',
		'team'      => '<circle cx="9" cy="8" r="3"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><path d="M16 5.5a3 3 0 0 1 0 5M18 20c0-2.4-.9-4.4-2.5-5.6 3 .3 5.5 2.8 5.5 5.6"/>',
	);
	return isset( $icons[ $key ] )
		? '<svg class="advantage__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $icons[ $key ] . '</svg>'
		: '';
}

get_header();

while ( have_posts() ) :
	the_post();

	$slogan = apply_filters(
		'dazhen_services_slogan',
		__( 'Your brand. Our engineering.', 'dazhen' )
	);
	$stats = apply_filters(
		'dazhen_services_stats',
		array(
			array(
				'value' => __( '100 units', 'dazhen' ),
				'label' => __( 'MOQ', 'dazhen' ),
			),
			array(
				'value' => __( '4–6 weeks', 'dazhen' ),
				'label' => __( 'Lead time', 'dazhen' ),
			),
			array(
				'value' => __( '1 year', 'dazhen' ),
				'label' => __( 'Warranty', 'dazhen' ),
			),
			array(
				'value' => __( 'Global', 'dazhen' ),
				'label' => __( 'After-sales', 'dazhen' ),
			),
		)
	);
	$advantages = apply_filters(
		'dazhen_services_advantages',
		array(
			array(
				'icon'  => 'sliders',
				'title' => __( 'Full customization', 'dazhen' ),
				'text'  => __( 'Appearance, parameters and packaging tailored to your brand — built on proven instant-heat, cooling and filtration platforms.', 'dazhen' ),
			),
			array(
				'icon'  => 'stopwatch',
				'title' => __( 'Faster to market', 'dazhen' ),
				'text'  => __( 'Proven platforms and an in-house R&D team keep development tight — typically 4–6 weeks from spec freeze to shipment.', 'dazhen' ),
			),
			array(
				'icon'  => 'shield',
				'title' => __( 'Global compliance', 'dazhen' ),
				'text'  => __( 'ISO 9001 and BSCI factories with food-grade materials; CE/CB and market-specific schemes are handled per program.', 'dazhen' ),
			),
			array(
				'icon'  => 'team',
				'title' => __( 'Experienced team', 'dazhen' ),
				'text'  => __( '10+ years of technology accumulation, 30+ patents and 20+ core R&D engineers behind every program.', 'dazhen' ),
			),
		)
	);
	$faq_groups = apply_filters(
		'dazhen_services_faq_groups',
		array(
			array(
				'title' => __( 'OEM & ODM', 'dazhen' ),
				'items' => array(
					array(
						'q' => __( 'What is the minimum order quantity?', 'dazhen' ),
						'a' => __( 'Most series start at 100 units for OEM customization; standard models can be trialed in smaller quantities.', 'dazhen' ),
					),
					array(
						'q' => __( 'How long does customization take?', 'dazhen' ),
						'a' => __( 'Typically 4–6 weeks from spec confirmation to shipment, depending on certification scope.', 'dazhen' ),
					),
					array(
						'q' => __( 'Can you develop a fully custom product, or only rebrand existing models?', 'dazhen' ),
						'a' => __( 'Both. Most programs start from a proven platform — instant-heat, ice-water, sparkling or filtration — and customize appearance, parameters and packaging; ground-up ODM development is available with our in-house industrial design team.', 'dazhen' ),
					),
				),
			),
			array(
				'title' => __( 'Product', 'dazhen' ),
				'items' => array(
					array(
						'q' => __( 'Which certifications do you support?', 'dazhen' ),
						'a' => __( 'ISO 9001 and BSCI factories, food-grade materials; CE/CB and market-specific schemes are handled per program.', 'dazhen' ),
					),
					array(
						'q' => __( 'How do we evaluate samples before committing to production?', 'dazhen' ),
						'a' => __( 'Sample units ship with lab reports covering heating performance, flow rates and safety tests. For qualified programs, pilot-run units can be arranged so your team can validate the product in-market before mass production.', 'dazhen' ),
					),
				),
			),
			array(
				'title' => __( 'After-sales', 'dazhen' ),
				'items' => array(
					array(
						'q' => __( 'What does the warranty cover?', 'dazhen' ),
						'a' => __( 'Every OEM program includes a one-year warranty from shipment. Claims are handled with replacement parts or units, coordinated through your account engineer.', 'dazhen' ),
					),
					array(
						'q' => __( 'Are spare parts available after delivery?', 'dazhen' ),
						'a' => __( 'Yes. Serviceable module sets and key spare parts are listed at spec confirmation, so your service team knows exactly what to stock for after-sales support.', 'dazhen' ),
					),
				),
			),
			array(
				'title' => __( 'Payment & Logistics', 'dazhen' ),
				'items' => array(
					array(
						'q' => __( 'Which payment terms do you accept?', 'dazhen' ),
						'a' => __( 'Common terms are T/T and L/C at sight; the exact deposit-and-balance split is agreed per program before production starts.', 'dazhen' ),
					),
					array(
						'q' => __( 'Do you ship by sea or by air?', 'dazhen' ),
						'a' => __( 'Both. Sea freight in export cartons is standard for full programs; air freight is available for samples and urgent pilot runs. Export documents are handled by our team.', 'dazhen' ),
					),
				),
			),
		)
	);
	?>

	<header class="zone-a page-hero services-hero">
		<div class="container">
			<?php dazhen_breadcrumbs(); ?>
			<p class="eyebrow eyebrow--a"><?php esc_html_e( 'OEM / ODM Services', 'dazhen' ); ?></p>
			<h1 class="page-hero__title"><?php echo esc_html( $slogan ); ?></h1>
			<p class="page-hero__sub"><?php esc_html_e( 'A one-stop OEM/ODM partner for smart water appliances — from the first requirement talk to container loading, one team owns your program.', 'dazhen' ); ?></p>
		</div>
		<?php if ( ! empty( $stats ) ) : ?>
			<div class="stats-strip">
				<dl class="container stats-strip__grid">
					<?php foreach ( $stats as $stat ) : ?>
						<div class="stats-strip__item">
							<dd class="stats-strip__value"><?php echo esc_html( $stat['value'] ); ?></dd>
							<dt class="stats-strip__label"><?php echo esc_html( $stat['label'] ); ?></dt>
						</div>
					<?php endforeach; ?>
				</dl>
			</div>
		<?php endif; ?>
	</header>

	<?php /* ---------- What we do + four advantages ---------- */ ?>
	<section class="zone-b section">
		<div class="container">
			<?php
			dazhen_section_head(
				array(
					'zone'    => 'b',
					'eyebrow' => __( 'What we do', 'dazhen' ),
					'title'   => __( 'Manufacturing for brands that ship worldwide', 'dazhen' ),
				)
			);
			?>
			<?php if ( trim( (string) get_the_content() ) ) : ?>
				<div class="prose services-intro"><?php the_content(); ?></div>
			<?php else : ?>
				<div class="services-intro services-intro--default">
					<p><?php esc_html_e( 'Dazhen designs and manufactures smart water appliances in Dayawan, Huizhou — and builds them under other people’s brands. Retail chains, distributors, hotel groups and e-commerce brands come to us for one-stop OEM/ODM production: household instant-heat taps for family kitchens, high-duty systems for offices, safe-temperature units for education and medical facilities, and wall-mounted or built-in units for hotel and engineering projects.', 'dazhen' ); ?></p>
					<p><?php esc_html_e( 'One team covers the whole program — requirement analysis, industrial design, spec engineering, sampling and testing, ISO 9001 production, export packaging and after-delivery support. You bring the brand and the market; we bring the platforms, the factory and the engineers.', 'dazhen' ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $advantages ) ) : ?>
				<div class="services-advantages-head">
					<h3><?php esc_html_e( 'Why Dazhen', 'dazhen' ); ?></h3>
					<p class="services-advantages-sub"><?php esc_html_e( 'Four reasons brands choose Dazhen', 'dazhen' ); ?></p>
				</div>
				<div class="grid grid--4 services-advantages">
					<?php foreach ( $advantages as $advantage ) : ?>
						<article class="advantage" data-dz-reveal>
							<span class="advantage__icon-wrap">
								<?php echo dazhen_services_advantage_icon( $advantage['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG markup. ?>
							</span>
							<h4 class="advantage__title"><?php echo esc_html( $advantage['title'] ); ?></h4>
							<p class="advantage__text"><?php echo esc_html( $advantage['text'] ); ?></p>
						</article>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php /* ---------- OEM journey (interactive timeline, template part) ---------- */ ?>
	<?php get_template_part( 'template-parts/oem-journey' ); ?>

	<?php /* ---------- FAQ accordion ---------- */ ?>
	<?php if ( ! empty( $faq_groups ) ) : ?>
		<section class="zone-b section">
			<div class="container">
				<?php
				dazhen_section_head(
					array(
						'zone'    => 'b',
						'eyebrow' => __( 'FAQ', 'dazhen' ),
						'title'   => __( 'Frequently asked', 'dazhen' ),
					)
				);
				?>
				<div class="faq-accordion" data-dz-accordion>
					<?php
					$question_id = 0;
					foreach ( $faq_groups as $group ) :
						if ( empty( $group['items'] ) ) {
							continue;
						}
						?>
						<div class="faq-group">
							<h3 class="faq-group__title"><?php echo esc_html( $group['title'] ); ?></h3>
							<div class="faq-group__items">
								<?php foreach ( $group['items'] as $item ) : ?>
									<?php
									$is_first = 0 === $question_id;
									$panel_id = 'dz-faq-' . (int) $question_id;
									++$question_id;
									?>
									<div class="faq-item<?php echo $is_first ? ' is-open' : ''; ?>">
										<h4 class="faq-item__heading">
											<button
												type="button"
												class="faq-item__btn"
												data-dz-acc-btn
												aria-expanded="<?php echo $is_first ? 'true' : 'false'; ?>"
												aria-controls="<?php echo esc_attr( $panel_id ); ?>"
											>
												<span><?php echo esc_html( $item['q'] ); ?></span>
												<svg class="faq-item__chevron" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 8l5 5 5-5"/></svg>
											</button>
										</h4>
										<div class="faq-item__panel" id="<?php echo esc_attr( $panel_id ); ?>" <?php echo $is_first ? '' : 'hidden'; ?>>
											<p><?php echo esc_html( $item['a'] ); ?></p>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	get_template_part(
		'template-parts/form-inquiry',
		null,
		array(
			'product' => __( 'OEM / ODM program', 'dazhen' ),
			'message' => __( 'I’d like to start an OEM project. My requirements are:', 'dazhen' ),
			'heading' => __( 'Start your OEM project', 'dazhen' ),
			'sub'     => __( 'Tell us about your market, volumes and target specs — a sales engineer replies within one business day.', 'dazhen' ),
		)
	);

endwhile;

get_footer();
