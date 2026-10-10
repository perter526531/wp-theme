<?php
/**
 * Global delivery map — Solutions overview module (Zone A).
 *
 * Dotted world map (assets/img/world-dots.svg, shared asset generated from
 * Natural Earth data) + animated delivery arcs and markers. Coordinates use
 * the map's equirectangular viewBox "0 0 1000 418".
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

/* Marker coordinates in the 1000×418 viewBox (see tools/map in the repo). */
$dazhen_map = array(
	'origin'    => array( 818.1, 186.2 ),
	'dests'     => array(
		array( 499.7, 106.9 ), // London
		array( 524.1, 110.8 ), // Frankfurt
		array( 653.5, 180.0 ), // Dubai
		array( 779.2, 211.8 ), // Bangkok
		array( 920.0, 344.1 ), // Sydney
	),
	'arcs'      => array(
		'M 818.1 186.2 Q 658.9 34.7 499.7 106.9',
		'M 818.1 186.2 Q 671.1 44.0 524.1 110.8',
		'M 818.1 186.2 Q 735.8 143.8 653.5 180',
		'M 818.1 186.2 Q 798.7 176.0 779.2 211.8',
		'M 818.1 186.2 Q 869.0 144.9 920 344.1',
	),
);
?>
<section class="zone-a section delivery-map">
	<div class="container">
		<?php
		dazhen_section_head( array(
			'zone'    => 'a',
			'eyebrow' => __( 'Global Delivery', 'dazhen' ),
			'title'   => __( 'From Dayawan to five continents', 'dazhen' ),
			'sub'     => __( 'OEM/ODM programs shipped to distributor, hotel and education networks across Europe, Southeast Asia, the Middle East and Oceania.', 'dazhen' ),
		) );
		?>

		<div class="delivery-map__frame">
			<img
				class="delivery-map__dots"
				src="<?php echo esc_url( DAZHEN_URI . '/assets/img/world-dots.svg' ); ?>"
				alt=""
				width="1000" height="418" loading="lazy" aria-hidden="true"
			>
			<svg class="delivery-map__overlay" viewBox="0 0 1000 418" aria-hidden="true" focusable="false">
				<?php foreach ( $dazhen_map['arcs'] as $i => $arc ) : ?>
					<path class="delivery-map__arc" style="animation-delay: <?php echo esc_attr( (string) ( $i * 0.4 ) ); ?>s" d="<?php echo esc_attr( $arc ); ?>"/>
				<?php endforeach; ?>

				<?php foreach ( $dazhen_map['dests'] as $dest ) : ?>
					<circle class="delivery-map__pulse" cx="<?php echo esc_attr( (string) $dest[0] ); ?>" cy="<?php echo esc_attr( (string) $dest[1] ); ?>" r="7"/>
					<circle class="delivery-map__dot" cx="<?php echo esc_attr( (string) $dest[0] ); ?>" cy="<?php echo esc_attr( (string) $dest[1] ); ?>" r="3.5"/>
				<?php endforeach; ?>

				<circle class="delivery-map__pulse delivery-map__pulse--origin" cx="818.1" cy="186.2" r="10"/>
				<circle class="delivery-map__dot--mid" cx="818.1" cy="186.2" r="6"/>
				<circle class="delivery-map__dot--core" cx="818.1" cy="186.2" r="3"/>

				<text class="delivery-map__label delivery-map__label--origin" x="818.1" y="216" text-anchor="middle">DAZHEN · HUIZHOU HQ</text>

				<g class="delivery-map__chip">
					<rect x="462" y="64" width="118" height="22" rx="11"/>
					<text x="521" y="78.5" text-anchor="middle">UK · 1,500 units</text>
				</g>
				<g class="delivery-map__chip">
					<rect x="726" y="224" width="132" height="22" rx="11"/>
					<text x="792" y="238.5" text-anchor="middle">Thailand · 1,800 units</text>
				</g>
			</svg>

			<!-- Screen-reader equivalent of the map (overlay is decorative). -->
			<ul class="screen-reader-text">
				<li><?php esc_html_e( 'Dazhen headquarters and factory: Dayawan, Huizhou, China.', 'dazhen' ); ?></li>
				<li><?php esc_html_e( 'United Kingdom — 1,500 ice-water units delivered.', 'dazhen' ); ?></li>
				<li><?php esc_html_e( 'Thailand — 1,800 sparkling-water units delivered.', 'dazhen' ); ?></li>
				<li><?php esc_html_e( 'Delivery coverage also includes Frankfurt, Dubai and Sydney.', 'dazhen' ); ?></li>
			</ul>
		</div>

		<ul class="chip-row chip-row--center">
			<li class="chip chip--a"><?php esc_html_e( 'UK — 1,500 ice-water units', 'dazhen' ); ?></li>
			<li class="chip chip--a"><?php esc_html_e( 'Thailand — 1,800 sparkling units', 'dazhen' ); ?></li>
			<li class="chip chip--a"><?php esc_html_e( 'Middle East — hotel engineering', 'dazhen' ); ?></li>
			<li class="chip chip--a"><?php esc_html_e( 'Oceania — retail programs', 'dazhen' ); ?></li>
		</ul>
	</div>
</section>
