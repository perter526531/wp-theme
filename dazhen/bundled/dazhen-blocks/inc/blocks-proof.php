<?php
/**
 * Block specs + renderers — the proof group.
 *
 * gallery, testimonials, logos, specs (table), faq.
 *
 * Required from inside dzb_specs() — $register is provided by the engine.
 *
 * @package Dazhen_Blocks
 */

defined( 'ABSPATH' ) || exit;

/* =====================================================================
 * Gallery — image wall
 * =================================================================== */

$register( array(
	'name'        => dzb_name( 'gallery' ),
	'title'       => __( 'Gallery', 'dazhen-blocks' ),
	'description' => __( 'A grid of images — products, factory floor, trade fair booths.', 'dazhen-blocks' ),
	'icon'        => 'format-gallery',
	'keywords'    => array( 'gallery', 'images', 'photos', 'image wall' ),
	'attributes'  => array_merge( dzb_common_attrs(), array(
		'eyebrow' => array( 'type' => 'string', 'default' => '' ),
		'title'   => array( 'type' => 'string', 'default' => '' ),
		'sub'     => array( 'type' => 'string', 'default' => '' ),
		'ids'     => array(
			'type'    => 'array',
			'default' => array(),
			'items'   => array( 'type' => 'number' ),
		),
		'columns' => array( 'type' => 'number', 'default' => 3 ),
	) ),
	'panels'      => array_merge(
		array(
			array(
				'title'       => __( 'Images', 'dazhen-blocks' ),
				'initialOpen' => true,
				'fields'      => array(
					array( 'attr' => 'ids', 'label' => __( 'Images', 'dazhen-blocks' ), 'type' => 'media-array' ),
					array( 'attr' => 'columns', 'label' => __( 'Columns', 'dazhen-blocks' ), 'type' => 'range', 'min' => 2, 'max' => 4 ),
				),
			),
		),
		dzb_common_panels()
	),
	'render'      => static function ( array $a ): string {
		$ids = array_filter( array_map( 'intval', (array) $a['ids'] ) );
		if ( ! $ids ) {
			return '';
		}

		$body  = dzb_grid_open( $a['columns'], 'dzb-gallery' );
		foreach ( $ids as $id ) {
			$body .= '<figure class="dzb-gallery__item">' . dzb_img( $id, 'large', 'dzb-gallery__img' ) . '</figure>';
		}
		$body .= '</div>';

		return dzb_shell( dzb_name( 'gallery' ), $a, $body );
	},
) );

/* =====================================================================
 * Testimonials — client quotes
 * =================================================================== */

$register( array(
	'name'        => dzb_name( 'testimonials' ),
	'title'       => __( 'Testimonials', 'dazhen-blocks' ),
	'description' => __( 'Client quotes with name and role — social proof for B2B buyers.', 'dazhen-blocks' ),
	'icon'        => 'format-quote',
	'keywords'    => array( 'testimonials', 'quotes', 'reviews', 'customer testimonials' ),
	'attributes'  => array_merge( dzb_common_attrs(), array(
		'zone' => array( 'type' => 'string', 'default' => 'alt' ),
		'eyebrow' => array( 'type' => 'string', 'default' => '' ),
		'title' => array( 'type' => 'string', 'default' => '' ),
		'sub' => array( 'type' => 'string', 'default' => '' ),
		'columns' => array( 'type' => 'number', 'default' => 2 ),
		'items' => array(
			'type'    => 'array',
			'default' => array(),
			'items'   => array( 'type' => 'object' ),
		),
	) ),
	'panels'      => array_merge(
		array(
			array(
				'title'       => __( 'Quotes', 'dazhen-blocks' ),
				'initialOpen' => true,
				'fields'      => array(
					array( 'attr' => 'columns', 'label' => __( 'Columns', 'dazhen-blocks' ), 'type' => 'range', 'min' => 1, 'max' => 3 ),
					array(
						'attr'      => 'items',
						'label'     => __( 'Quotes', 'dazhen-blocks' ),
						'type'      => 'repeater',
						'itemLabel' => __( 'Quote', 'dazhen-blocks' ),
						'addLabel'  => __( 'Add a quote', 'dazhen-blocks' ),
						'defaults'  => array( 'quote' => '', 'name' => '', 'role' => '' ),
						'fields'    => array(
							array( 'attr' => 'quote', 'label' => __( 'Quote', 'dazhen-blocks' ), 'type' => 'textarea' ),
							array( 'attr' => 'name', 'label' => __( 'Person', 'dazhen-blocks' ), 'type' => 'text' ),
							array( 'attr' => 'role', 'label' => __( 'Role / company', 'dazhen-blocks' ), 'type' => 'text' ),
						),
					),
				),
			),
		),
		dzb_common_panels()
	),
	'render'      => static function ( array $a ): string {
		$items = dzb_items( $a['items'], array( 'quote', 'name', 'role' ) );
		if ( ! $items ) {
			return '';
		}

		$body  = dzb_grid_open( $a['columns'], 'dzb-quotes' );
		foreach ( $items as $item ) {
			$body .= '<figure class="dzb-quote">';
			$body .= '<span class="dzb-quote__mark" aria-hidden="true">' . dzb_icon( 'sparkle' ) . '</span>';
			$body .= '<blockquote class="dzb-quote__text">' . dzb_text( $item['quote'] ) . '</blockquote>';
			if ( '' !== $item['name'] || '' !== $item['role'] ) {
				$body .= '<figcaption class="dzb-quote__by">';
				$body .= $item['name'] ? '<strong>' . dzb_text( $item['name'] ) . '</strong>' : '';
				$body .= $item['role'] ? '<span>' . dzb_text( $item['role'] ) . '</span>' : '';
				$body .= '</figcaption>';
			}
			$body .= '</figure>';
		}
		$body .= '</div>';

		return dzb_shell( dzb_name( 'testimonials' ), $a, $body );
	},
) );

/* =====================================================================
 * Logos — client / partner wall
 * =================================================================== */

$register( array(
	'name'        => dzb_name( 'logos' ),
	'title'       => __( 'Client Logos', 'dazhen-blocks' ),
	'description' => __( 'A quiet wall of client or partner logos. Falls back to text chips when no images are set.', 'dazhen-blocks' ),
	'icon'        => 'awards',
	'keywords'    => array( 'logos', 'clients', 'partners', 'brands' ),
	'attributes'  => array_merge( dzb_common_attrs(), array(
		'zone' => array( 'type' => 'string', 'default' => 'alt' ),
		'pad'  => array( 'type' => 'string', 'default' => 'compact' ),
		'eyebrow' => array( 'type' => 'string', 'default' => '' ),
		'title' => array( 'type' => 'string', 'default' => '' ),
		'sub' => array( 'type' => 'string', 'default' => '' ),
		'ids'  => array(
			'type'    => 'array',
			'default' => array(),
			'items'   => array( 'type' => 'number' ),
		),
		'names' => array( 'type' => 'string', 'default' => '' ),
	) ),
	'panels'      => array_merge(
		array(
			array(
				'title'       => __( 'Logos', 'dazhen-blocks' ),
				'initialOpen' => true,
				'fields'      => array(
					array( 'attr' => 'ids', 'label' => __( 'Logo images', 'dazhen-blocks' ), 'type' => 'media-array' ),
					array( 'attr' => 'names', 'label' => __( 'Text fallback (one name per line)', 'dazhen-blocks' ), 'type' => 'textarea', 'help' => __( 'Used only when no logo images are set.', 'dazhen-blocks' ) ),
				),
			),
		),
		dzb_common_panels()
	),
	'render'      => static function ( array $a ): string {
		$ids   = array_filter( array_map( 'intval', (array) $a['ids'] ) );
		$names = dzb_lines( $a['names'] );

		if ( $ids ) {
			$body = '<div class="dzb-logos">';
			foreach ( $ids as $id ) {
				$body .= '<span class="dzb-logos__item">' . dzb_img( $id, 'medium', 'dzb-logos__img' ) . '</span>';
			}
			$body .= '</div>';
		} elseif ( $names ) {
			$body = '<ul class="chip-row dzb-logos-fallback">';
			foreach ( $names as $name ) {
				$body .= '<li class="chip">' . dzb_text( $name ) . '</li>';
			}
			$body .= '</ul>';
		} else {
			return '';
		}

		return dzb_shell( dzb_name( 'logos' ), $a, $body );
	},
) );

/* =====================================================================
 * Specs — parameter table
 * =================================================================== */

$register( array(
	'name'        => dzb_name( 'specs' ),
	'title'       => __( 'Specs Table', 'dazhen-blocks' ),
	'description' => __( 'A two-column parameter table — model specs, material data, service terms.', 'dazhen-blocks' ),
	'icon'        => 'editor-table',
	'keywords'    => array( 'specs', 'specifications', 'parameters', 'table' ),
	'attributes'  => array_merge( dzb_common_attrs(), array(
		'eyebrow' => array( 'type' => 'string', 'default' => '' ),
		'title'   => array( 'type' => 'string', 'default' => '' ),
		'sub'     => array( 'type' => 'string', 'default' => '' ),
		'items'   => array(
			'type'    => 'array',
			'default' => array(),
			'items'   => array( 'type' => 'object' ),
		),
	) ),
	'panels'      => array_merge(
		array(
			array(
				'title'       => __( 'Rows', 'dazhen-blocks' ),
				'initialOpen' => true,
				'fields'      => array(
					array(
						'attr'      => 'items',
						'label'     => __( 'Parameters', 'dazhen-blocks' ),
						'type'      => 'repeater',
						'itemLabel' => __( 'Row', 'dazhen-blocks' ),
						'addLabel'  => __( 'Add a row', 'dazhen-blocks' ),
						'defaults'  => array( 'label' => '', 'value' => '' ),
						'fields'    => array(
							array( 'attr' => 'label', 'label' => __( 'Parameter', 'dazhen-blocks' ), 'type' => 'text' ),
							array( 'attr' => 'value', 'label' => __( 'Value', 'dazhen-blocks' ), 'type' => 'text' ),
						),
					),
				),
			),
		),
		dzb_common_panels()
	),
	'render'      => static function ( array $a ): string {
		$items = dzb_items( $a['items'], array( 'label', 'value' ) );
		if ( ! $items ) {
			return '';
		}

		$body  = '<div class="dzb-specs-wrap">';
		$body .= '<table class="spec-table dzb-specs"><tbody>';
		foreach ( $items as $item ) {
			$body .= '<tr><th scope="row">' . dzb_text( $item['label'] ) . '</th><td>' . dzb_text( $item['value'] ) . '</td></tr>';
		}
		$body .= '</tbody></table></div>';

		return dzb_shell( dzb_name( 'specs' ), $a, $body );
	},
) );

/* =====================================================================
 * FAQ — accordions
 * =================================================================== */

$register( array(
	'name'        => dzb_name( 'faq' ),
	'title'       => __( 'FAQ', 'dazhen-blocks' ),
	'description' => __( 'Accordion questions and answers — OEM terms, after-sales, logistics.', 'dazhen-blocks' ),
	'icon'        => 'editor-help',
	'keywords'    => array( 'faq', 'questions', 'accordion', 'common questions' ),
	'attributes'  => array_merge( dzb_common_attrs(), array(
		'eyebrow' => array( 'type' => 'string', 'default' => '' ),
		'title'   => array( 'type' => 'string', 'default' => '' ),
		'sub'     => array( 'type' => 'string', 'default' => '' ),
		'items'   => array(
			'type'    => 'array',
			'default' => array(),
			'items'   => array( 'type' => 'object' ),
		),
	) ),
	'panels'      => array_merge(
		array(
			array(
				'title'       => __( 'Questions', 'dazhen-blocks' ),
				'initialOpen' => true,
				'fields'      => array(
					array(
						'attr'      => 'items',
						'label'     => __( 'Q&A', 'dazhen-blocks' ),
						'type'      => 'repeater',
						'itemLabel' => __( 'Question', 'dazhen-blocks' ),
						'addLabel'  => __( 'Add a question', 'dazhen-blocks' ),
						'defaults'  => array( 'q' => '', 'a' => '' ),
						'fields'    => array(
							array( 'attr' => 'q', 'label' => __( 'Question', 'dazhen-blocks' ), 'type' => 'text' ),
							array( 'attr' => 'a', 'label' => __( 'Answer', 'dazhen-blocks' ), 'type' => 'textarea' ),
						),
					),
				),
			),
		),
		dzb_common_panels()
	),
	'render'      => static function ( array $a ): string {
		$items = dzb_items( $a['items'], array( 'q', 'a' ) );
		if ( ! $items ) {
			return '';
		}

		$body = '<div class="dzb-faq">';
		foreach ( $items as $item ) {
			$body .= '<details class="dzb-faq__item">';
			$body .= '<summary class="dzb-faq__q">' . dzb_text( $item['q'] ) . '</summary>';
			$body .= '<div class="dzb-faq__a">' . dzb_text( $item['a'] ) . '</div>';
			$body .= '</details>';
		}
		$body .= '</div>';

		return dzb_shell( dzb_name( 'faq' ), $a, $body, '', dzb_head_html( $a, $a['zone'] ?? 'b' ) );
	},
) );
