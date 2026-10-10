<?php
/**
 * Solution Finder — 3-question product wizard (scenario → installation → water).
 *
 * Renders the static skeleton; assets/js/selector-wizard.js handles the steps
 * and scoring. Product data is printed as JSON derived from the `product`
 * posts + the series→tags map below (filterable).
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

/**
 * Map product-series slugs to finder answer tags.
 *
 * scenario: home | office | edu | hotel
 * install : countertop | wall | built-in | faucet
 * water   : hot | cold | hot-cold | sparkling
 *
 * Series with no entry here simply never match (the OEM card covers them).
 *
 * @return array<string, array{scenario:string[],install:string[],water:string[]}>
 */
function dazhen_finder_series_tags(): array {
	return apply_filters(
		'dazhen_finder_series_tags',
		array(
			'instant-heat-faucet' => array( 'scenario' => array( 'home', 'office' ), 'install' => array( 'faucet' ), 'water' => array( 'hot' ) ),
			'built-in'            => array( 'scenario' => array( 'office', 'edu', 'hotel' ), 'install' => array( 'built-in', 'countertop' ), 'water' => array( 'hot-cold', 'cold' ) ),
			'engineering'         => array( 'scenario' => array( 'hotel', 'office' ), 'install' => array( 'built-in', 'wall' ), 'water' => array( 'hot', 'hot-cold' ) ),
			'ice-water-faucet'    => array( 'scenario' => array( 'home', 'office' ), 'install' => array( 'faucet' ), 'water' => array( 'cold' ) ),
			'sparkling-faucet'    => array( 'scenario' => array( 'home', 'office' ), 'install' => array( 'faucet' ), 'water' => array( 'sparkling' ) ),
			'instant-cold'        => array( 'scenario' => array( 'home', 'office' ), 'install' => array( 'countertop' ), 'water' => array( 'cold' ) ),
			'boil-and-cool'       => array( 'scenario' => array( 'home', 'edu' ), 'install' => array( 'countertop' ), 'water' => array( 'hot' ) ),
			'self-priming'        => array( 'scenario' => array( 'home' ), 'install' => array( 'countertop' ), 'water' => array( 'hot' ) ),
			'wall-mounted'         => array( 'scenario' => array( 'hotel', 'office' ), 'install' => array( 'wall' ), 'water' => array( 'hot', 'hot-cold' ) ),
		)
	);
}

/**
 * Collect finder product data (model, image, url, tags) as an array.
 *
 * @return array<int, array{id:int,model:string,series:string,url:string,img:string,tags:array{scenario:string[],install:string[],water:string[]}}>
 */
function dazhen_finder_products(): array {
	$tag_map = dazhen_finder_series_tags();
	$query   = new WP_Query( array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 60,
		'orderby'        => 'menu_order date',
		'order'          => 'ASC',
		'no_found_rows'  => true,
	) );

	$data = array();
	foreach ( $query->posts as $post ) {
		$series = get_the_terms( $post, 'product_series' );
		$slug   = ( $series && ! is_wp_error( $series ) ) ? $series[0]->slug : '';
		if ( ! $slug || ! isset( $tag_map[ $slug ] ) ) {
			continue; // Series without tags (e.g. pet fountain) stay out of the wizard.
		}
		$model = get_post_meta( $post->ID, '_dz_product_model', true );
		$thumb = get_the_post_thumbnail_url( $post->ID, 'dazhen-card' );

		$data[] = array(
			'id'     => $post->ID,
			'model'  => $model ? $model : $post->post_title,
			'series' => $series[0]->name,
			'url'    => get_permalink( $post ),
			'img'    => $thumb ? $thumb : '',
			'tags'   => $tag_map[ $slug ],
		);
	}
	return $data;
}

/**
 * Print one inline SVG icon (decorative, aria-hidden) for finder options.
 *
 * @param string $key Icon key.
 */
function dazhen_finder_icon( string $key ): void {
	$paths = array(
		'home'       => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M10 21v-6h4v6"/>',
		'office'     => '<rect x="4" y="3" width="16" height="18" rx="1.5"/><path d="M9 7h2M13 7h2M9 11h2M13 11h2M9 15h2M13 15h2"/>',
		'edu'        => '<path d="M12 4 2 9l10 5 10-5-10-5z"/><path d="M6 11.5V16c0 1.5 2.7 3 6 3s6-1.5 6-3v-4.5"/>',
		'hotel'      => '<path d="M4 21V5a1 1 0 0 1 1-1h8v17"/><path d="M13 9h6a1 1 0 0 1 1 1v11"/><path d="M7 8h3M7 12h3M7 16h3"/>',
		'countertop' => '<path d="M3 15h18"/><rect x="7" y="6" width="10" height="9" rx="1.5"/>',
		'wall'       => '<rect x="5" y="4" width="14" height="16" rx="1.5"/><path d="M5 9.3h14M5 14.6h14M12 4v5.3M8.5 9.3v5.3M15.5 9.3v5.3"/>',
		'builtin'    => '<path d="m12 3 9 4.5-9 4.5-9-4.5L12 3z"/><path d="m3 13 9 4.5 9-4.5"/>',
		'faucet'     => '<path d="M12 3.5c3 3.2 5.5 6 5.5 9a5.5 5.5 0 1 1-11 0c0-3 2.5-5.8 5.5-9z"/>',
		'hot'        => '<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.07-2.14-.22-4.05 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.15.43-2.29 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>',
		'cold'       => '<path d="M12 2v20M4.5 6.5l15 11M19.5 6.5l-15 11"/>',
		'hotcold'    => '<path d="M10 4a2 2 0 1 1 4 0v9.5a4 4 0 1 1-4 0z"/><path d="M12 8v7"/>',
		'sparkling'  => '<path d="M12 3l1.8 4.7L18.5 9.5l-4.7 1.8L12 16l-1.8-4.7L5.5 9.5l4.7-1.8L12 3z"/><path d="M18 15l.9 2.3L21 18l-2.1.7L18 21l-.9-2.3L15 18l2.1-.7z"/>',
	);
	if ( ! isset( $paths[ $key ] ) ) {
		return;
	}
	printf(
		'<svg class="finder__option-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%s</svg>',
		$paths[ $key ] // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG markup, no user input.
	);
}

$finder_steps = array(
	array(
		'key'   => 'scenario',
		'label' => __( 'Scenario', 'dazhen' ),
		'question' => __( 'Where will it serve?', 'dazhen' ),
		'options'  => array(
			array( 'home', __( 'Home & Kitchen', 'dazhen' ), __( 'Family drinking and cooking', 'dazhen' ) ),
			array( 'office', __( 'Office & Commercial', 'dazhen' ), __( 'Teams, pantries, lounges', 'dazhen' ) ),
			array( 'edu', __( 'School & Medical', 'dazhen' ), __( 'Safe-temperature priorities', 'dazhen' ) ),
			array( 'hotel', __( 'Hotel & Engineering', 'dazhen' ), __( 'Renovation and new builds', 'dazhen' ) ),
		),
	),
	array(
		'key'   => 'install',
		'label' => __( 'Installation', 'dazhen' ),
		'question' => __( 'How should it install?', 'dazhen' ),
		'options'  => array(
			array( 'countertop', __( 'Countertop', 'dazhen' ), __( 'Sits on the counter', 'dazhen' ) ),
			array( 'wall', __( 'Wall-mounted', 'dazhen' ), __( 'Fixed to the wall', 'dazhen' ) ),
			array( 'built-in', __( 'Built-in', 'dazhen' ), __( 'Under-sink or embedded', 'dazhen' ) ),
			array( 'faucet', __( 'Faucet-mount', 'dazhen' ), __( 'Replaces the tap', 'dazhen' ) ),
		),
	),
	array(
		'key'   => 'water',
		'label' => __( 'Water', 'dazhen' ),
		'question' => __( 'What water do you need?', 'dazhen' ),
		'options'  => array(
			array( 'hot', __( 'Instant hot', 'dazhen' ), __( '3-second 95°C', 'dazhen' ) ),
			array( 'cold', __( 'Ice-cold', 'dazhen' ), __( '5–8°C chilled', 'dazhen' ) ),
			array( 'hot-cold', __( 'Hot + Cold', 'dazhen' ), __( 'One machine, both ends', 'dazhen' ) ),
			array( 'sparkling', __( 'Sparkling', 'dazhen' ), __( 'Carbonated water platform', 'dazhen' ) ),
		),
	),
);
?>
<section class="zone-b zone-b--alt section" id="solution-finder">
	<div class="container">
		<?php
		dazhen_section_head( array(
			'zone'    => 'b',
			'eyebrow' => __( 'Solution Finder', 'dazhen' ),
			'title'   => __( 'Three questions to your model', 'dazhen' ),
			'sub'     => __( 'Answer scenario, installation and water-type questions — we match the catalog for you, or build it OEM.', 'dazhen' ),
		) );
		?>

		<div class="finder" data-dz-finder>
			<script type="application/json" data-dz-finder-data><?php echo wp_json_encode( dazhen_finder_products() ); ?></script>

			<ol class="finder__steps" data-dz-finder-steps>
				<?php foreach ( $finder_steps as $i => $step ) : ?>
					<li class="finder__step" data-dz-finder-step="<?php echo esc_attr( (string) $i ); ?>">
						<button type="button" class="finder__step-btn" data-dz-finder-goto="<?php echo esc_attr( (string) $i ); ?>">
							<span class="finder__step-dot" aria-hidden="true"><?php echo esc_html( (string) ( $i + 1 ) ); ?></span>
							<span class="finder__step-label"><?php echo esc_html( $step['label'] ); ?></span>
						</button>
					</li>
				<?php endforeach; ?>
			</ol>

			<div class="finder__body">
				<?php foreach ( $finder_steps as $i => $step ) : ?>
					<fieldset class="finder__panel" data-dz-finder-panel="<?php echo esc_attr( $step['key'] ); ?>" <?php echo 0 === $i ? '' : 'hidden'; ?>>
						<?php if ( $i > 0 ) : ?>
							<button type="button" class="finder__back" data-dz-finder-back><?php echo esc_html__( '← Back', 'dazhen' ); ?></button>
						<?php endif; ?>
						<legend class="finder__question"><?php echo esc_html( $step['question'] ); ?></legend>
						<div class="finder__options">
							<?php foreach ( $step['options'] as $option ) : ?>
								<button type="button" class="finder__option" data-dz-finder-answer="<?php echo esc_attr( $option[0] ); ?>">
									<?php dazhen_finder_icon( $option[0] ); ?>
									<span class="finder__option-label"><?php echo esc_html( $option[1] ); ?></span>
									<span class="finder__option-hint"><?php echo esc_html( $option[2] ); ?></span>
								</button>
							<?php endforeach; ?>
						</div>
					</fieldset>
				<?php endforeach; ?>

				<div class="finder__panel finder__panel--results" data-dz-finder-panel="results" hidden>
					<button type="button" class="finder__back" data-dz-finder-back><?php echo esc_html__( '← Back', 'dazhen' ); ?></button>
					<p class="finder__question" data-dz-finder-results-title><?php esc_html_e( 'Your matches', 'dazhen' ); ?></p>
					<div class="finder__matches" data-dz-finder-matches></div>

					<div class="finder__oem">
						<p class="finder__oem-title"><?php esc_html_e( 'Don’t see exactly it?', 'dazhen' ); ?></p>
						<p class="finder__oem-text"><?php esc_html_e( 'Hydrogen-rich, boil-and-cool and sparkling platforms are ready for OEM/ODM — from 100 units.', 'dazhen' ); ?></p>
						<a class="btn btn--cta" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php echo dazhen_cta_text(); ?></a>
					</div>

					<div class="finder__restart">
						<button type="button" class="btn btn--outline-teal" data-dz-finder-restart><?php esc_html_e( 'Start over', 'dazhen' ); ?></button>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
