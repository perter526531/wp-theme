<?php
/**
 * OEM journey — interactive 7-step timeline (core innovation module #4).
 *
 * Renders a horizontally-scrolling journey on desktop (nodes along a gently
 * dipping SVG path with a measured progress fill) and a vertical timeline on
 * small screens. Keyboard: ArrowLeft/ArrowRight/Home/End, ARIA tablist.
 *
 * Data filters:
 * - dazhen_oem_journey_steps : array of step arrays.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

/**
 * Return the inline line-icon SVG for an OEM journey step.
 *
 * @param string $key Icon key.
 * @return string SVG markup (empty string for unknown keys).
 */
function dazhen_oem_journey_icon( string $key ): string {
	$icons = array(
		'talk'    => '<path d="M4 5h16v11H9l-5 4z"/><path d="M8 9h8M8 12h5"/>',
		'concept' => '<path d="M4 17l2-6 9-9 4 4-9 9z"/><path d="M14 3l4 4"/>',
		'spec'    => '<path d="M6 4h12v17l-3-2-3 2-3-2-3 2z"/><path d="M9 8h6M9 12h6"/>',
		'lab'     => '<path d="M10 3h4"/><path d="M11 3v6l-5 9a2 2 0 0 0 2 3h8a2 2 0 0 0 2-3l-5-9V3"/><path d="M8 15h8"/>',
		'factory' => '<path d="M3 21V9l6 4V9l6 4V9l6 4v8z"/><path d="M3 21h18"/><path d="M7 17h2M13 17h2"/>',
		'box'     => '<path d="M12 3l8 4v10l-8 4-8-4V7z"/><path d="M4 7l8 4 8-4M12 11v10"/>',
		'support' => '<path d="M4 13a8 8 0 0 1 16 0"/><path d="M4 13v4a2 2 0 0 0 2 2h1v-6H6a2 2 0 0 0-2 2z"/><path d="M20 13v4a2 2 0 0 1-2 2h-1v-6h1a2 2 0 0 1 2 2z"/>',
	);
	return isset( $icons[ $key ] )
		? '<svg class="oem-journey__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $icons[ $key ] . '</svg>'
		: '';
}

$steps = apply_filters(
	'dazhen_oem_journey_steps',
	array(
		array(
			'no'           => '01',
			'title'        => __( 'Requirement talk', 'dazhen' ),
			'week'         => __( 'Week 0', 'dazhen' ),
			'icon'         => 'talk',
			'detail'       => __( 'Every program starts with a focused conversation: your market, expected volume, target price and the certifications you need. From that brief we recommend the platform — instant-heat, ice-water, sparkling or filtration — that fits your program best.', 'dazhen' ),
			'deliverables' => array( __( 'Requirement brief', 'dazhen' ), __( 'Platform shortlist', 'dazhen' ), __( 'Project timeline', 'dazhen' ) ),
		),
		array(
			'no'           => '02',
			'title'        => __( 'Concept development', 'dazhen' ),
			'week'         => __( 'Week 1–2', 'dazhen' ),
			'icon'         => 'concept',
			'detail'       => __( 'Our industrial designers translate the brief into appearance concepts and a complete technical proposal. You review renders, functions and costs before any tooling is committed.', 'dazhen' ),
			'deliverables' => array( __( 'Concept renders', 'dazhen' ), __( 'Technical proposal', 'dazhen' ), __( 'Cost breakdown', 'dazhen' ) ),
		),
		array(
			'no'           => '03',
			'title'        => __( 'Spec confirmation', 'dazhen' ),
			'week'         => __( 'Week 2', 'dazhen' ),
			'icon'         => 'spec',
			'detail'       => __( 'Together we lock every parameter — heating power, dispensing modes, materials, surface finish and packaging layout. The frozen spec sheet becomes the single reference for sampling and production.', 'dazhen' ),
			'deliverables' => array( __( 'Spec sheet', 'dazhen' ), __( 'Material list', 'dazhen' ), __( 'Packaging die-line', 'dazhen' ) ),
		),
		array(
			'no'           => '04',
			'title'        => __( 'Sampling & testing', 'dazhen' ),
			'week'         => __( 'Week 3–4', 'dazhen' ),
			'icon'         => 'lab',
			'detail'       => __( 'Prototype units are built and run through lab testing — heating curves, flow rates, safety and endurance. Samples ship with the reports so your team can validate them in-market.', 'dazhen' ),
			'deliverables' => array( __( 'Prototype', 'dazhen' ), __( 'Lab report', 'dazhen' ), __( 'Test data', 'dazhen' ) ),
		),
		array(
			'no'           => '05',
			'title'        => __( 'Production & QC', 'dazhen' ),
			'week'         => __( 'Week 4–5', 'dazhen' ),
			'icon'         => 'factory',
			'detail'       => __( 'Your program runs on an ISO 9001 certified line with in-line inspection at key stations. Batch traceability is maintained from component intake to final assembly.', 'dazhen' ),
			'deliverables' => array( __( 'QC report', 'dazhen' ), __( 'Inspection records', 'dazhen' ), __( 'Production photos', 'dazhen' ) ),
		),
		array(
			'no'           => '06',
			'title'        => __( 'Packaging & shipping', 'dazhen' ),
			'week'         => __( 'Week 5–6', 'dazhen' ),
			'icon'         => 'box',
			'detail'       => __( 'Retail-ready or bulk, units are packed into export cartons and the container loading plan is documented with photos. Export documents are handled by our team.', 'dazhen' ),
			'deliverables' => array( __( 'Loading plan', 'dazhen' ), __( 'Container photos', 'dazhen' ), __( 'Export documents', 'dazhen' ) ),
		),
		array(
			'no'           => '07',
			'title'        => __( 'After-delivery support', 'dazhen' ),
			'week'         => __( 'Ongoing', 'dazhen' ),
			'icon'         => 'support',
			'detail'       => __( 'A one-year warranty covers the program, with spare parts and updates available throughout the cycle. Field feedback feeds directly into the next batch iteration.', 'dazhen' ),
			'deliverables' => array( __( 'Warranty coverage', 'dazhen' ), __( 'Spare parts', 'dazhen' ), __( 'Next-batch iteration', 'dazhen' ) ),
		),
	)
);

if ( empty( $steps ) ) {
	return;
}

wp_enqueue_script( 'dazhen-oem-journey' );

/* Journey path geometry: nodes at even 100-unit intervals, dips between them. */
$path_d = 'M 50 22';
foreach ( array( 58, 46, 62, 48, 60, 50 ) as $i => $dip ) {
	$x0 = 50 + $i * 100;
	$x1 = $x0 + 100;
	$path_d .= " Q " . ( $x0 + 50 ) . " {$dip} {$x1} 22";
}
?>
<section class="zone-a section oem-journey" id="oem-journey">
	<div class="container">
		<?php
		dazhen_section_head(
			array(
				'zone'    => 'a',
				'eyebrow' => __( 'OEM Journey', 'dazhen' ),
				'title'   => __( 'How an OEM program runs', 'dazhen' ),
				'sub'     => __( 'From first message to container loading, one team owns your program — typically 4–6 weeks from spec freeze to shipment.', 'dazhen' ),
			)
		);
		?>

		<div class="oem-journey__wrap" data-dz-oem-journey>
			<svg class="oem-journey__path" viewBox="0 0 700 72" preserveAspectRatio="none" aria-hidden="true" focusable="false">
				<path class="oem-journey__path-base" d="<?php echo esc_attr( $path_d ); ?>"></path>
				<path class="oem-journey__path-fill" d="<?php echo esc_attr( $path_d ); ?>"></path>
			</svg>

			<ol class="oem-journey__nodes" role="tablist" aria-label="<?php esc_attr_e( 'OEM program steps', 'dazhen' ); ?>">
				<?php foreach ( $steps as $i => $step ) : ?>
					<li class="oem-journey__node">
						<button
							type="button"
							role="tab"
							id="dz-oem-tab-<?php echo esc_attr( (string) $i ); ?>"
							aria-controls="dz-oem-panel-<?php echo esc_attr( (string) $i ); ?>"
							aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>"
							tabindex="<?php echo 0 === $i ? '0' : '-1'; ?>"
							class="oem-journey__tab<?php echo 0 === $i ? ' is-active' : ''; ?>"
						>
							<span class="oem-journey__dot"><?php echo dazhen_oem_journey_icon( $step['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG markup. ?></span>
							<span class="oem-journey__no"><?php echo esc_html( $step['no'] ); ?></span>
							<span class="oem-journey__title"><?php echo esc_html( $step['title'] ); ?></span>
							<span class="oem-journey__week"><?php echo esc_html( $step['week'] ); ?></span>
						</button>
					</li>
				<?php endforeach; ?>
			</ol>

			<div class="oem-journey__panels">
				<?php foreach ( $steps as $i => $step ) : ?>
					<div
						role="tabpanel"
						id="dz-oem-panel-<?php echo esc_attr( (string) $i ); ?>"
						aria-labelledby="dz-oem-tab-<?php echo esc_attr( (string) $i ); ?>"
						class="oem-journey__panel<?php echo 0 === $i ? ' is-active' : ''; ?>"
						<?php echo 0 === $i ? '' : 'hidden'; ?>
					>
						<p class="oem-journey__stage">
							<?php
							printf(
								/* translators: 1: step number, 2: week label. */
								esc_html__( 'Step %1$s · %2$s', 'dazhen' ),
								esc_html( $step['no'] ),
								esc_html( $step['week'] )
							);
							?>
						</p>
						<h3 class="oem-journey__panel-title"><?php echo esc_html( $step['title'] ); ?></h3>
						<p class="oem-journey__detail"><?php echo esc_html( $step['detail'] ); ?></p>
						<?php if ( ! empty( $step['deliverables'] ) ) : ?>
							<p class="oem-journey__deliverables-label"><?php esc_html_e( 'What you receive', 'dazhen' ); ?></p>
							<ul class="oem-journey__deliverables">
								<?php foreach ( (array) $step['deliverables'] as $chip ) : ?>
									<li><?php echo esc_html( $chip ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<p class="sr-only">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %s: number of steps. */
						__( 'The OEM program runs in %s steps: requirement talk, concept development, spec confirmation, sampling and testing, production and QC, packaging and shipping, after-delivery support.', 'dazhen' ),
						number_format_i18n( count( $steps ) )
					)
				);
				?>
			</p>
		</div>
	</div>
</section>
