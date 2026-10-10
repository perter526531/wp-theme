<?php
/**
 * Block specs + renderers — the conversion group.
 *
 * products, cta, inquiry, contact, section (universal container), cta-quote.
 *
 * Required from inside dzb_specs() — $register is provided by the engine.
 *
 * @package Dazhen_Blocks
 */

defined( 'ABSPATH' ) || exit;

/* =====================================================================
 * Products — the live catalog grid
 * =================================================================== */

$register( array(
	'name'        => dzb_name( 'products' ),
	'title'       => __( 'Products Grid', 'dazhen-blocks' ),
	'description' => __( 'A live grid of your product catalog — new products appear automatically. With the Dazhen theme active each card uses the theme\'s product card design.', 'dazhen-blocks' ),
	'icon'        => 'screenoptions',
	'keywords'    => array( 'products', 'catalog', 'grid', 'shop' ),
	'attributes'  => array_merge( dzb_common_attrs(), array(
		'eyebrow'  => array( 'type' => 'string', 'default' => '' ),
		'title'    => array( 'type' => 'string', 'default' => '' ),
		'sub'      => array( 'type' => 'string', 'default' => '' ),
		'count'    => array( 'type' => 'number', 'default' => 6 ),
		'columns'  => array( 'type' => 'number', 'default' => 3 ),
		'series'   => array( 'type' => 'string', 'default' => '' ),
		'orderby'  => array( 'type' => 'string', 'default' => 'menu_order' ),
		'ctaLabel' => array( 'type' => 'string', 'default' => '' ),
		'ctaUrl'   => array( 'type' => 'string', 'default' => '' ),
	) ),
	'panels'      => array_merge(
		array(
			array(
				'title'       => __( 'Products settings', 'dazhen-blocks' ),
				'initialOpen' => true,
				'fields'      => array(
					array( 'attr' => 'count', 'label' => __( 'How many products', 'dazhen-blocks' ), 'type' => 'range', 'min' => 1, 'max' => 12 ),
					array( 'attr' => 'columns', 'label' => __( 'Columns', 'dazhen-blocks' ), 'type' => 'range', 'min' => 2, 'max' => 4 ),
					array( 'attr' => 'series', 'label' => __( 'Product series slug (optional)', 'dazhen-blocks' ), 'type' => 'text', 'help' => __( 'With the Dazhen theme active pick from Appearance → product series slugs, e.g. pet-fountain.', 'dazhen-blocks' ) ),
					array(
						'attr'    => 'orderby',
						'label'   => __( 'Order by', 'dazhen-blocks' ),
						'type'    => 'select',
						'options' => array(
							array( 'value' => 'menu_order', 'label' => __( 'Menu order (curated)', 'dazhen-blocks' ) ),
							array( 'value' => 'date', 'label' => __( 'Newest first', 'dazhen-blocks' ) ),
							array( 'value' => 'title', 'label' => __( 'Title A–Z', 'dazhen-blocks' ) ),
							array( 'value' => 'rand', 'label' => __( 'Random', 'dazhen-blocks' ) ),
						),
					),
					array( 'attr' => 'ctaLabel', 'label' => __( 'Button under the grid — label', 'dazhen-blocks' ), 'type' => 'text', 'placeholder' => __( 'View all products', 'dazhen-blocks' ) ),
					array( 'attr' => 'ctaUrl', 'label' => __( 'Button under the grid — link', 'dazhen-blocks' ), 'type' => 'text' ),
				),
			),
		),
		dzb_common_panels()
	),
	'render'      => static function ( array $a ): string {
		if ( ! post_type_exists( 'product' ) ) {
			if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
				return '<div class="dzb-hint">' . esc_html__( 'No "product" catalog on this site yet. Activate the Dazhen theme (it registers the product post type), then this grid fills itself.', 'dazhen-blocks' ) . '</div>';
			}
			return '';
		}

		$count = max( 1, min( 12, (int) $a['count'] ) );
		$args  = array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'posts_per_page'      => $count,
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
		);

		switch ( $a['orderby'] ) {
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

		$series = sanitize_title( (string) $a['series'] );
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
				return '<div class="dzb-hint">' . esc_html__( 'No published products match yet — add products in the admin, or relax the series filter.', 'dazhen-blocks' ) . '</div>';
			}
			return '';
		}

		$theme  = dazhen_blocks_theme_active();
		$body   = dzb_grid_open( $a['columns'], 'dzb-products' );
		while ( $query->have_posts() ) :
			$query->the_post();
			if ( $theme && function_exists( 'dazhen_product_card' ) ) {
				ob_start();
				dazhen_product_card( get_the_ID() );
				$body .= (string) ob_get_clean();
			} else {
				$body .= dzb_standalone_product_card( get_the_ID() );
			}
		endwhile;
		wp_reset_postdata();
		$body .= '</div>';

		if ( '' !== dzb_text( $a['ctaLabel'] ) ) {
			$url  = '' !== dzb_url( $a['ctaUrl'] )
				? dzb_url( $a['ctaUrl'] )
				: (string) ( get_post_type_archive_link( 'product' ) ?: home_url( '/products/' ) );
			$body .= '<p class="section-foot dzb-section-foot">' . dzb_button_html( $a['ctaLabel'], $url, '', 'outline' ) . '</p>';
		}

		return dzb_shell( dzb_name( 'products' ), $a, $body );
	},
) );

/**
 * Standalone product card (Dazhen theme NOT active).
 */
function dzb_standalone_product_card( int $post_id ): string {
	$series = get_the_term_list( $post_id, 'product_series', '', ', ' );
	$html  = '<article class="dzb-card dzb-pcard">';
	$html .= '<a href="' . esc_url( (string) get_permalink( $post_id ) ) . '">';
	$html .= '<span class="dzb-pcard__media">';
	if ( has_post_thumbnail( $post_id ) ) {
		$html .= (string) get_the_post_thumbnail( $post_id, 'medium_large', array( 'alt' => esc_attr( (string) get_the_title( $post_id ) ) ) );
	} else {
		$html .= '<span class="dzb-ph dzb-pcard__placeholder" aria-hidden="true"></span>';
	}
	$html .= '</span><span class="dzb-pcard__info">';
	$html .= '<strong>' . esc_html( get_the_title( $post_id ) ) . '</strong>';
	if ( $series && ! is_wp_error( $series ) ) {
		$html .= '<span class="dzb-pcard__terms">' . wp_kses_post( $series ) . '</span>';
	}
	$html .= '<span class="dzb-pcard__more">' . esc_html__( 'View details →', 'dazhen-blocks' ) . '</span>';
	$html .= '</span></a></article>';
	return $html;
}

/* =====================================================================
 * CTA — the conversion banner
 * =================================================================== */

$register( array(
	'name'        => dzb_name( 'cta' ),
	'title'       => __( 'CTA Banner', 'dazhen-blocks' ),
	'description' => __( 'The conversion banner: headline, promise line and the amber quote button (opens the inquiry popup with the Dazhen theme).', 'dazhen-blocks' ),
	'icon'        => 'megaphone',
	'keywords'    => array( 'cta', 'call to action', 'quote', 'conversion' ),
	'attributes'  => array_merge( dzb_common_attrs(), array(
		'zone'    => array( 'type' => 'string', 'default' => 'a' ),
		'pad'     => array( 'type' => 'string', 'default' => 'spacious' ),
		'eyebrow' => array( 'type' => 'string', 'default' => '' ),
		'title'   => array( 'type' => 'string', 'default' => '' ),
		'sub'     => array( 'type' => 'string', 'default' => '' ),
		'btnLabel'   => array( 'type' => 'string', 'default' => '' ),
		'btnProduct' => array( 'type' => 'string', 'default' => '' ),
		'btn2Label'  => array( 'type' => 'string', 'default' => '' ),
		'btn2Url'    => array( 'type' => 'string', 'default' => '' ),
	) ),
	'panels'      => array_merge(
		array(
			array(
				'title'       => __( 'Banner', 'dazhen-blocks' ),
				'initialOpen' => true,
				'fields'      => array(
					array( 'attr' => 'btnLabel', 'label' => __( 'Button label', 'dazhen-blocks' ), 'type' => 'text', 'placeholder' => __( 'Get a Quote', 'dazhen-blocks' ) ),
					array( 'attr' => 'btnProduct', 'label' => __( 'Pre-filled product (optional)', 'dazhen-blocks' ), 'type' => 'text' ),
					array( 'attr' => 'btn2Label', 'label' => __( 'Secondary button — label', 'dazhen-blocks' ), 'type' => 'text' ),
					array( 'attr' => 'btn2Url', 'label' => __( 'Secondary button — link', 'dazhen-blocks' ), 'type' => 'text' ),
				),
			),
		),
		dzb_common_panels()
	),
	'render'      => static function ( array $a ): string {
		$head = dzb_head_html( $a, 'a' );

		$buttons  = dzb_button_html( '' !== dzb_text( $a['btnLabel'] ) ? $a['btnLabel'] : __( 'Get a Quote', 'dazhen-blocks' ), '', $a['btnProduct'], 'cta' );
		if ( '' !== dzb_text( $a['btn2Label'] ) ) {
			$buttons .= dzb_button_html( $a['btn2Label'], $a['btn2Url'], '', 'ghost' );
		}

		$body = '<div class="dzb-cta-card">' . $head . '<div class="dzb-cta-card__actions">' . $buttons . '</div></div>';

		return dzb_shell( dzb_name( 'cta' ), $a, $body, 'dzb-cta', '' );
	},
) );

/* =====================================================================
 * Inquiry — the lead form section
 * =================================================================== */

$register( array(
	'name'        => dzb_name( 'inquiry' ),
	'title'       => __( 'Inquiry Form', 'dazhen-blocks' ),
	'description' => __( 'The B2B lead form. With the Dazhen theme active it renders the theme\'s 7-field inquiry form (submissions land in Inquiries + email). Standalone it renders the plugin\'s own compact form.', 'dazhen-blocks' ),
	'icon'        => 'email-alt',
	'keywords'    => array( 'inquiry', 'form', 'contact', 'lead', 'inquiry form' ),
	'attributes'  => array_merge( dzb_common_attrs(), array(
		'eyebrow'  => array( 'type' => 'string', 'default' => '' ),
		'title'    => array( 'type' => 'string', 'default' => '' ),
		'sub'      => array( 'type' => 'string', 'default' => '' ),
		'product'  => array( 'type' => 'string', 'default' => '' ),
		'message'  => array( 'type' => 'string', 'default' => '' ),
	) ),
	'panels'      => array_merge(
		array(
			array(
				'title'       => __( 'Form', 'dazhen-blocks' ),
				'initialOpen' => true,
				'fields'      => array(
					array( 'attr' => 'product', 'label' => __( 'Pre-filled product (optional)', 'dazhen-blocks' ), 'type' => 'text' ),
					array( 'attr' => 'message', 'label' => __( 'Pre-filled opening message (optional)', 'dazhen-blocks' ), 'type' => 'textarea' ),
				),
			),
		),
		dzb_common_panels()
	),
	'render'      => static function ( array $a ): string {
		$head = dzb_head_html( $a, $a['zone'] ?? 'b' );

		if ( dazhen_blocks_theme_active() && function_exists( 'dazhen_form_inquiry_part' ) ) {
			$form = dazhen_form_inquiry_part( $a['product'], $a['message'] );
		} else {
			$form = dzb_leads_form( $a['product'], $a['message'] );
		}

		$body = '<div class="dzb-inquiry">' . $form . '</div>';

		return dzb_shell( dzb_name( 'inquiry' ), $a, $body, 'dzb-inquiry-section', $head );
	},
) );

/* =====================================================================
 * Contact — info cards
 * =================================================================== */

$register( array(
	'name'        => dzb_name( 'contact' ),
	'title'       => __( 'Contact Cards', 'dazhen-blocks' ),
	'description' => __( 'Phone, email, address and working-hours cards — with the Dazhen theme these read your Customizer contact settings by default.', 'dazhen-blocks' ),
	'icon'        => 'phone',
	'keywords'    => array( 'contact', 'info', 'address', 'phone' ),
	'attributes'  => array_merge( dzb_common_attrs(), array(
		'eyebrow' => array( 'type' => 'string', 'default' => '' ),
		'title'   => array( 'type' => 'string', 'default' => '' ),
		'sub'     => array( 'type' => 'string', 'default' => '' ),
		'columns' => array( 'type' => 'number', 'default' => 3 ),
		'items'   => array(
			'type'    => 'array',
			'default' => array(),
			'items'   => array( 'type' => 'object' ),
		),
	) ),
	'panels'      => array_merge(
		array(
			array(
				'title'       => __( 'Cards', 'dazhen-blocks' ),
				'initialOpen' => true,
				'fields'      => array(
					array( 'attr' => 'columns', 'label' => __( 'Columns', 'dazhen-blocks' ), 'type' => 'range', 'min' => 2, 'max' => 4 ),
					array(
						'attr'      => 'items',
						'label'     => __( 'Cards', 'dazhen-blocks' ),
						'type'      => 'repeater',
						'itemLabel' => __( 'Card', 'dazhen-blocks' ),
						'addLabel'  => __( 'Add a card', 'dazhen-blocks' ),
						'defaults'  => array( 'icon' => 'phone', 'title' => '', 'text' => '' ),
						'fields'    => array(
							array( 'attr' => 'title', 'label' => __( 'Title', 'dazhen-blocks' ), 'type' => 'text' ),
							array( 'attr' => 'text', 'label' => __( 'Text', 'dazhen-blocks' ), 'type' => 'textarea' ),
							array(
								'attr'    => 'icon',
								'label'   => __( 'Icon', 'dazhen-blocks' ),
								'type'    => 'select',
								'options' => array(
									array( 'value' => 'phone', 'label' => __( 'Phone', 'dazhen-blocks' ) ),
									array( 'value' => 'mail', 'label' => __( 'Mail', 'dazhen-blocks' ) ),
									array( 'value' => 'pin', 'label' => __( 'Location', 'dazhen-blocks' ) ),
									array( 'value' => 'clock', 'label' => __( 'Clock', 'dazhen-blocks' ) ),
									array( 'value' => 'globe', 'label' => __( 'Globe', 'dazhen-blocks' ) ),
									array( 'value' => 'truck', 'label' => __( 'Truck', 'dazhen-blocks' ) ),
								),
							),
						),
					),
				),
			),
		),
		dzb_common_panels()
	),
	'render'      => static function ( array $a ): string {
		$items = dzb_items( $a['items'], array( 'icon', 'title', 'text' ) );
		if ( ! $items ) {
			return '';
		}

		$body = dzb_grid_open( $a['columns'], 'dzb-contact' );
		foreach ( $items as $item ) {
			$body .= '<article class="dzb-contact__card">';
			$body .= '<span class="dzb-contact__icon">' . dzb_icon( $item['icon'] ) . '</span>';
			$body .= '<div class="dzb-contact__copy">';
			$body .= $item['title'] ? '<h3>' . dzb_text( $item['title'] ) . '</h3>' : '';
			$body .= $item['text'] ? '<p>' . dzb_text( $item['text'] ) . '</p>' : '';
			$body .= '</div></article>';
		}
		$body .= '</div>';

		return dzb_shell( dzb_name( 'contact' ), $a, $body );
	},
) );

/* =====================================================================
 * Section — the universal container (InnerBlocks)
 * =================================================================== */

$register( array(
	'name'        => dzb_name( 'section' ),
	'title'       => __( 'Section (container)', 'dazhen-blocks' ),
	'description' => __( 'The universal container: pick the background and padding, then drop ANY blocks inside — Dazhen blocks, core blocks, Stackable, anything.', 'dazhen-blocks' ),
	'icon'        => 'editor-table',
	'keywords'    => array( 'section', 'container', 'wrapper', 'zone' ),
	'inner'       => true,
	'supports'    => array(
		'html'  => false,
		'align' => array( 'wide', 'full' ),
	),
	'attributes'  => array_merge( dzb_common_attrs(), array(
		'width' => array(
			'type'    => 'string',
			'default' => 'default',
		),
	) ),
	'panels'      => array_merge(
		array(
			array(
				'title'       => __( 'Container', 'dazhen-blocks' ),
				'initialOpen' => true,
				'fields'      => array(
					array(
						'attr'    => 'width',
						'label'   => __( 'Content width', 'dazhen-blocks' ),
						'type'    => 'select',
						'options' => array(
							array( 'value' => 'default', 'label' => __( 'Default', 'dazhen-blocks' ) ),
							array( 'value' => 'narrow', 'label' => __( 'Narrow (reading)', 'dazhen-blocks' ) ),
							array( 'value' => 'full', 'label' => __( 'Full width', 'dazhen-blocks' ) ),
						),
					),
				),
			),
		),
		dzb_common_panels()
	),
	'render'      => static function ( array $a, string $content = '' ): string {
		$container = 'narrow' === $a['width'] ? 'container container--narrow' : 'container';

		return sprintf(
			'<section class="%1$s" data-dzb="%2$s"><div class="%3$s">%4$s</div></section>',
			esc_attr( dzb_zone_classes( $a, 'dzb-section' ) ),
			esc_attr( dzb_name( 'section' ) ),
			esc_attr( $container ),
			$content
		);
	},
) );

/* =====================================================================
 * CTA quote — the inline button (small, flow-friendly)
 * =================================================================== */

$register( array(
	'name'        => dzb_name( 'cta-quote' ),
	'title'       => __( 'Quote Button', 'dazhen-blocks' ),
	'description' => __( 'The conversion button anywhere you need it — opens the inquiry popup with the Dazhen theme, or links anywhere on other themes.', 'dazhen-blocks' ),
	'icon'        => 'button',
	'keywords'    => array( 'quote', 'button', 'inquiry', 'cta' ),
	'supports'    => array(
		'html'  => false,
		'align' => array( 'center', 'wide', 'full' ),
	),
	'attributes'  => array(
		'text'    => array( 'type' => 'string', 'default' => '' ),
		'product' => array( 'type' => 'string', 'default' => '' ),
		'url'     => array( 'type' => 'string', 'default' => '' ),
		'style'   => array( 'type' => 'string', 'default' => 'cta' ),
		'align'   => array( 'type' => 'string', 'default' => '' ),
	),
	'panels'      => array(
		array(
			'title'       => __( 'Quote button', 'dazhen-blocks' ),
			'initialOpen' => true,
			'fields'      => array(
				array( 'attr' => 'text', 'label' => __( 'Button text', 'dazhen-blocks' ), 'type' => 'text', 'placeholder' => __( 'Get a Quote', 'dazhen-blocks' ) ),
				array( 'attr' => 'product', 'label' => __( 'Pre-filled product (optional)', 'dazhen-blocks' ), 'type' => 'text' ),
				array( 'attr' => 'url', 'label' => __( 'Link URL (optional)', 'dazhen-blocks' ), 'type' => 'text', 'help' => __( 'With the Dazhen theme and this empty, the button opens the inquiry popup.', 'dazhen-blocks' ) ),
				array(
					'attr'    => 'style',
					'label'   => __( 'Style', 'dazhen-blocks' ),
					'type'    => 'select',
					'options' => array(
						array( 'value' => 'cta', 'label' => __( 'Amber (primary)', 'dazhen-blocks' ) ),
						array( 'value' => 'outline', 'label' => __( 'Outline (secondary)', 'dazhen-blocks' ) ),
					),
				),
			),
		),
	),
	'render'      => static function ( array $a ): string {
		$text  = '' !== dzb_text( $a['text'] ) ? $a['text'] : __( 'Get a Quote', 'dazhen-blocks' );
		$style = 'outline' === $a['style'] ? 'outline' : 'cta';

		$wrap = 'dzb-cta-row';
		if ( '' !== (string) $a['align'] ) {
			$wrap .= ' align' . sanitize_html_class( (string) $a['align'] );
		}

		return sprintf(
			'<div class="%1$s">%2$s</div>',
			esc_attr( $wrap ),
			dzb_button_html( $text, $a['url'], $a['product'], $style )
		);
	},
) );
