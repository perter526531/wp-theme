<?php
/**
 * Dynamic blocks — live data blocks registered from pure PHP (no build step).
 *
 *   dazhen-blocks/products   — product grid with sidebar controls
 *                              (count / columns / series / order).
 *   dazhen-blocks/cta-quote  — the RFQ call-to-action button. With the
 *                              Dazhen theme active it opens the inquiry
 *                              popup modal; on other themes it links to
 *                              any URL you set.
 *
 * The client-side editor registrations live in assets/js/blocks-editor.js
 * (plain wp.element code, no bundler) and use ServerSideRender, so what you
 * edit is exactly what visitors see.
 *
 * @package Dazhen_Blocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the two dynamic blocks (server-rendered).
 */
function dazhen_blocks_register_blocks(): void {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	register_block_type( 'dazhen-blocks/products', array(
		'api_version'     => 3,
		'attributes'      => array(
			'count'    => array( 'type' => 'number', 'default' => 6 ),
			'columns'  => array( 'type' => 'number', 'default' => 3 ),
			'series'   => array( 'type' => 'string', 'default' => '' ),
			'orderby'  => array( 'type' => 'string', 'default' => 'menu_order' ),
		),
		'render_callback' => 'dazhen_blocks_render_products',
	) );

	register_block_type( 'dazhen-blocks/cta-quote', array(
		'api_version'     => 3,
		'attributes'      => array(
			'text'    => array( 'type' => 'string', 'default' => '' ),
			'product' => array( 'type' => 'string', 'default' => '' ),
			'url'     => array( 'type' => 'string', 'default' => '' ),
			'style'   => array( 'type' => 'string', 'default' => 'cta' ),
			'align'   => array( 'type' => 'string', 'default' => '' ),
		),
		'render_callback' => 'dazhen_blocks_render_cta',
	) );
}
add_action( 'init', 'dazhen_blocks_register_blocks', 20 );

/**
 * Render the products grid.
 *
 * With the Dazhen theme active the grid reuses the theme's product card
 * markup (dazhen_product_card) so it looks native everywhere. On any other
 * theme the plugin's own compact card is used instead.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function dazhen_blocks_render_products( array $attributes ): string {
	if ( ! post_type_exists( 'product' ) ) {
		// No product catalog on this site — show a hint in the editor,
		// render nothing on the front end.
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return '<div class="dzb-hint">' .
				esc_html__( 'The Dazhen theme registers the "product" catalog. Activate the Dazhen theme (or any plugin that registers a "product" post type) to fill this grid.', 'dazhen-blocks' ) .
				'</div>';
		}
		return '';
	}

	$count   = max( 1, min( 12, (int) ( $attributes['count'] ?? 6 ) ) );
	$columns = max( 2, min( 4, (int) ( $attributes['columns'] ?? 3 ) ) );
	$orderby = $attributes['orderby'] ?? 'menu_order';

	$args = array(
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'posts_per_page'      => $count,
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	);

	switch ( $orderby ) {
		case 'date':
			$args['orderby'] = 'date';
			break;
		case 'title':
			$args['orderby'] = 'title';
			$args['order']   = 'ASC';
			break;
		case 'rand':
			$args['orderby'] = 'rand';
			break;
		default:
			$args['orderby'] = 'menu_order date';
			$args['order']   = 'ASC';
	}

	$series = sanitize_title( (string) ( $attributes['series'] ?? '' ) );
	if ( '' !== $series && taxonomy_exists( 'product_series' ) ) {
		$term = get_term_by( 'slug', $series, 'product_series' );
		if ( $term && ! is_wp_error( $term ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'product_series',
					'field'    => 'term_id',
					'terms'    => array( $term->term_id ),
				),
			);
		}
	}

	$query = new WP_Query( $args );
	if ( ! $query->have_posts() ) {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return '<div class="dzb-hint">' .
				esc_html__( 'No published products match yet — add products in the admin, or relax the series filter.', 'dazhen-blocks' ) .
				'</div>';
		}
		return '';
	}

	$theme   = dazhen_blocks_theme_active();
	$wrapper = $theme
		? 'dzb-products grid grid--' . $columns
		: 'dzb-products dzb-grid dzb-cols-' . $columns;

	ob_start();
	echo '<div class="' . esc_attr( $wrapper ) . '">';
	while ( $query->have_posts() ) :
		$query->the_post();
		if ( $theme && function_exists( 'dazhen_product_card' ) ) {
			dazhen_product_card( get_the_ID() );
		} else {
			dazhen_blocks_standalone_card( get_the_ID() );
		}
	endwhile;
	wp_reset_postdata();
	echo '</div>';
	return (string) ob_get_clean();
}

/**
 * Standalone product card (used when the Dazhen theme is NOT active).
 *
 * @param int $post_id Product post ID.
 */
function dazhen_blocks_standalone_card( int $post_id ): void {
	$series = get_the_term_list( $post_id, 'product_series', '', ', ' );
	?>
	<article class="dzb-card">
		<a href="<?php echo esc_url( (string) get_permalink( $post_id ) ); ?>">
			<span class="dzb-card__media">
				<?php
				if ( has_post_thumbnail( $post_id ) ) {
					echo get_the_post_thumbnail( $post_id, 'medium_large', array( 'alt' => esc_attr( (string) get_the_title( $post_id ) ) ) );
				} else {
					echo '<span class="dzb-card__placeholder" aria-hidden="true"></span>';
				}
				?>
			</span>
			<span class="dzb-card__info">
				<strong><?php echo esc_html( get_the_title( $post_id ) ); ?></strong>
				<?php if ( $series && ! is_wp_error( $series ) ) : ?>
					<span class="dzb-card__terms"><?php echo wp_kses_post( $series ); ?></span>
				<?php endif; ?>
				<span class="dzb-card__more"><?php esc_html_e( 'View details →', 'dazhen-blocks' ); ?></span>
			</span>
		</a>
	</article>
	<?php
}

/**
 * Render the quote CTA button.
 *
 * Dazhen theme active → opens the theme's RFQ popup modal (main.js binds
 * every .dz-rfq-open element, including this block's output).
 * Other themes → a normal link to the URL you set in the sidebar.
 *
 * @param array $attributes Block attributes.
 * @return string
 */
function dazhen_blocks_render_cta( array $attributes ): string {
	$text    = sanitize_text_field( (string) ( $attributes['text'] ?? '' ) );
	$product = sanitize_text_field( (string) ( $attributes['product'] ?? '' ) );
	$url     = esc_url_raw( (string) ( $attributes['url'] ?? '' ) );
	$outline = 'outline' === ( $attributes['style'] ?? 'cta' );
	$align   = sanitize_html_class( (string) ( $attributes['align'] ?? '' ) );

	if ( '' === $text ) {
		$text = __( 'Get a Quote', 'dazhen-blocks' );
	}

	$theme = dazhen_blocks_theme_active();

	if ( $theme ) {
		$classes = $outline ? 'btn btn--outline-teal' : 'btn btn--cta';
		if ( '' === $url ) {
			$url = '#dz-rfq';
		}
		$extra = ' dz-rfq-open';
		$data  = $product ? ' data-dz-product="' . esc_attr( $product ) . '"' : '';
		$wrap  = 'dzb-cta-row';
	} else {
		$classes = $outline ? 'dzb-btn dzb-btn--outline' : 'dzb-btn dzb-btn--cta';
		$extra   = '';
		$data    = '';
		$wrap    = 'dzb-cta-row dzb-cta-row--plain';
		if ( '' === $url ) {
			$url = '#';
		}
	}

	if ( '' !== $align ) {
		$wrap .= ' align' . $align;
	}

	return sprintf(
		'<div class="%1$s"><a class="%2$s%3$s" href="%4$s"%5$s>%6$s</a></div>',
		esc_attr( $wrap ),
		esc_attr( $classes ),
		esc_attr( $extra ),
		esc_url( $url ),
		$data, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped above.
		esc_html( $text )
	);
}
