<?php
/**
 * Block specs + renderers — the storytelling group.
 *
 * hero, split, features, stats, process, milestones.
 *
 * This file is required from inside kbl_specs() — the $register closure and
 * the $specs registry are provided by the engine.
 *
 * v7: every spec carries an `edit` tree — a declarative node layout the JS
 * runtime renders ON the canvas with inline RichText / media / repeater
 * editing (see docs/kabel-v7-inline-editing.md). Panels keep style controls
 * only; content is edited where it lives.
 *
 * @package Kabel_Blocks
 */

defined( 'ABSPATH' ) || exit;

/* =====================================================================
 * Hero — the page opener
 * =================================================================== */

$kabel_hero_copy = array(
	array( 'text' => 'eyebrow', 'tag' => 'p', 'class' => 'eyebrow eyebrow--a', 'placeholder' => __( 'Eyebrow — small label', 'kabel-blocks' ) ),
	array( 'text' => 'title', 'tag' => 'h1', 'class' => 'kbl-hero__title', 'placeholder' => __( 'Headline', 'kabel-blocks' ) ),
	array( 'rich' => 'sub', 'tag' => 'p', 'class' => 'kbl-hero__sub', 'placeholder' => __( 'One-line intro', 'kabel-blocks' ) ),
	array( 'buttons' => array(
		array( 'label' => 'primaryLabel', 'class' => 'btn btn--cta', 'placeholder' => __( 'Consult Now', 'kabel-blocks' ) ),
		array( 'label' => 'secondaryLabel', 'class' => 'btn btn--ghost', 'placeholder' => __( 'Secondary', 'kabel-blocks' ) ),
	) ),
);

$register( array(
	'name'        => kbl_name( 'hero' ),
	'title'       => __( 'Hero', 'kabel-blocks' ),
	'description' => __( 'The page opener: eyebrow, big headline, intro and two buttons — centered, or split with a product image.', 'kabel-blocks' ),
	'icon'        => 'superhero-alt',
	'keywords'    => array( 'hero', 'banner', 'opener', 'hero region' ),
	'attributes'  => array_merge( kbl_common_attrs(), array(
		'zone'            => array( 'type' => 'string', 'default' => 'a' ),
		'layout'          => array( 'type' => 'string', 'default' => 'split' ),
		'eyebrow'         => array( 'type' => 'string', 'default' => '' ),
		'title'           => array( 'type' => 'string', 'default' => '' ),
		'sub'             => array( 'type' => 'string', 'default' => '' ),
		'image'           => array( 'type' => 'number', 'default' => 0 ),
		'bgImage'         => array( 'type' => 'number', 'default' => 0 ),
		'primaryLabel'    => array( 'type' => 'string', 'default' => '' ),
		'primaryUrl'      => array( 'type' => 'string', 'default' => '' ),
		'primaryProduct'  => array( 'type' => 'string', 'default' => '' ),
		'secondaryLabel'  => array( 'type' => 'string', 'default' => '' ),
		'secondaryUrl'    => array( 'type' => 'string', 'default' => '' ),
	) ),
	'edit'        => array(
		'bg'    => 'bgImage',
		'extra' => 'kbl-hero',
		'head'  => false,
		'tree'  => array(
			array( 'switch' => 'layout', 'default' => 'split', 'cases' => array(
				'center' => array(
					array( 'wrap' => array( 'div', 'kbl-hero-center' ), 'nodes' => $kabel_hero_copy ),
				),
				'split'  => array(
					array( 'wrap' => array( 'div', 'kbl-hero-split' ), 'nodes' => array_merge( $kabel_hero_copy, array(
						array( 'media' => 'image', 'class' => 'kbl-hero__visual', 'imgClass' => 'kbl-hero__img', 'size' => 'large' ),
					) ) ),
				),
			) ),
		),
	),
	'panels'      => array(
		array(
			'title'       => __( 'Button links', 'kabel-blocks' ),
			'initialOpen' => false,
			'fields'      => array(
				array( 'attr' => 'primaryUrl', 'label' => __( 'Primary button — link', 'kabel-blocks' ), 'type' => 'text', 'help' => __( 'Leave empty with the Kabel theme active to open the inquiry popup.', 'kabel-blocks' ) ),
				array( 'attr' => 'primaryProduct', 'label' => __( 'Primary button — pre-filled product (optional)', 'kabel-blocks' ), 'type' => 'text' ),
				array( 'attr' => 'secondaryUrl', 'label' => __( 'Secondary button — link', 'kabel-blocks' ), 'type' => 'text' ),
			),
		),
		array(
			'title'       => __( 'Layout', 'kabel-blocks' ),
			'initialOpen' => false,
			'fields'      => array(
				array(
					'attr'    => 'layout',
					'label'   => __( 'Arrangement', 'kabel-blocks' ),
					'type'    => 'select',
					'options' => array(
						array( 'value' => 'split', 'label' => __( 'Split — text + image', 'kabel-blocks' ) ),
						array( 'value' => 'center', 'label' => __( 'Centered', 'kabel-blocks' ) ),
					),
				),
			),
		),
		kbl_common_panels_tail(),
	),
	'render'      => static function ( array $a ): string {
		$eyebrow = kbl_text( $a['eyebrow'] );
		$title   = kbl_text( $a['title'] );
		$sub     = kbl_rich( $a['sub'] );

		$buttons = '';
		if ( '' !== kbl_text( $a['primaryLabel'] ) ) {
			$buttons .= kbl_button_html( $a['primaryLabel'], $a['primaryUrl'], $a['primaryProduct'], 'cta' );
		}
		if ( '' !== kbl_text( $a['secondaryLabel'] ) ) {
			$buttons .= kbl_button_html( $a['secondaryLabel'], $a['secondaryUrl'], '', 'ghost' );
		}
		if ( '' !== $buttons ) {
			$buttons = '<div class="kbl-hero__actions">' . $buttons . '</div>';
		}

		$copy  = $eyebrow ? '<p class="eyebrow eyebrow--a">' . $eyebrow . '</p>' : '';
		$copy .= $title ? '<h1 class="kbl-hero__title">' . $title . '</h1>' : '';
		$copy .= $sub ? '<p class="kbl-hero__sub">' . $sub . '</p>' : '';
		$copy .= $buttons;

		/* Full-bleed background photo (dark overlay via CSS). */
		$bg_id  = (int) ( $a['bgImage'] ?? 0 );
		$bg_src = $bg_id > 0 ? (string) wp_get_attachment_image_url( $bg_id, 'full' ) : '';
		$has_bg = '' !== $bg_src;

		/*
		 * Split layout needs an image; without one the copy would sit beside
		 * an empty placeholder — fall back to the centered arrangement.
		 * A background photo always centres the copy over it.
		 */
		$has_image = (int) $a['image'] > 0;
		if ( 'center' === $a['layout'] || ! $has_image || $has_bg ) {
			$body = '<div class="kbl-hero-center">' . $copy . '</div>';
		} else {
			$media = '<div class="kbl-hero__visual">' . kbl_img( $a['image'], 'large', 'kbl-hero__img' ) . '</div>';
			$body  = '<div class="kbl-hero-split">' . $copy . $media . '</div>';
		}

		/* '' head override: the hero renders its own heading — suppress the shared section head. */
		return kbl_shell( kbl_name( 'hero' ), $a, $body, 'kbl-hero' . ( $has_bg ? ' kbl-hero--has-bg' : '' ), '', $has_bg ? 'background-image:url(' . $bg_src . ')' : '' );
	},
) );

/* =====================================================================
 * Split — text beside an image
 * =================================================================== */

$kabel_split_copy = array(
	array( 'text' => 'eyebrow', 'tag' => 'p', 'class' => 'eyebrow eyebrow--b', 'placeholder' => __( 'Eyebrow — small label', 'kabel-blocks' ) ),
	array( 'text' => 'title', 'tag' => 'h2', 'class' => 'section-head__title kbl-split__title', 'placeholder' => __( 'Section title', 'kabel-blocks' ) ),
	array( 'rich' => 'sub', 'tag' => 'p', 'class' => 'section-head__sub', 'placeholder' => __( 'Intro line (optional)', 'kabel-blocks' ) ),
	array( 'rich' => 'body', 'tag' => 'p', 'class' => 'kbl-split__body', 'placeholder' => __( 'Body text', 'kabel-blocks' ) ),
	array( 'rich' => 'bullets', 'tag' => 'ul', 'class' => 'kbl-checklist', 'multiline' => 'li', 'placeholder' => __( 'Checklist — one item per line', 'kabel-blocks' ) ),
);

$register( array(
	'name'        => kbl_name( 'split' ),
	'title'       => __( 'Text + Image', 'kabel-blocks' ),
	'description' => __( 'A two-column section: story or argument on one side, image on the other. Great for factory, process or about passages.', 'kabel-blocks' ),
	'icon'        => 'columns',
	'keywords'    => array( 'split', 'two column', 'text + image', 'two-column layout' ),
	'attributes'  => array_merge( kbl_common_attrs(), array(
		'eyebrow' => array( 'type' => 'string', 'default' => '' ),
		'title'   => array( 'type' => 'string', 'default' => '' ),
		'sub'     => array( 'type' => 'string', 'default' => '' ),
		'body'    => array( 'type' => 'string', 'default' => '' ),
		'bullets' => array( 'type' => 'string', 'default' => '' ),
		'image'   => array( 'type' => 'number', 'default' => 0 ),
		'reverse' => array( 'type' => 'boolean', 'default' => false ),
	) ),
	'edit'        => array(
		'extra' => 'kbl-split-section',
		'head'  => false,
		'tree'  => array(
			array( 'wrap' => array( 'div', 'kbl-split' ), 'nodes' => array(
				array( 'switch' => 'reverse', 'default' => 'false', 'cases' => array(
					'false' => array( array( 'wrap' => array( 'div', 'kbl-split__copy' ), 'nodes' => $kabel_split_copy ), array( 'media' => 'image', 'class' => 'kbl-split__visual', 'imgClass' => 'kbl-split__img', 'size' => 'large' ) ),
					'true'  => array( array( 'media' => 'image', 'class' => 'kbl-split__visual', 'imgClass' => 'kbl-split__img', 'size' => 'large' ), array( 'wrap' => array( 'div', 'kbl-split__copy' ), 'nodes' => $kabel_split_copy ) ),
				) ),
			) ),
		),
	),
	'panels'      => array(
		array(
			'title'       => __( 'Layout', 'kabel-blocks' ),
			'initialOpen' => false,
			'fields'      => array(
				array( 'attr' => 'reverse', 'label' => __( 'Flip sides (image left)', 'kabel-blocks' ), 'type' => 'toggle' ),
			),
		),
		kbl_common_panels_tail(),
	),
	'render'      => static function ( array $a ): string {
		$eyebrow = kbl_text( $a['eyebrow'] );
		$title   = kbl_text( $a['title'] );
		$sub     = kbl_text( $a['sub'] );

		$copy  = $eyebrow ? '<p class="eyebrow eyebrow--b">' . $eyebrow . '</p>' : '';
		$copy .= $title ? '<h2 class="section-head__title kbl-split__title">' . $title . '</h2>' : '';
		$copy .= $sub ? '<p class="section-head__sub">' . $sub . '</p>' : '';
		$copy .= kbl_text( $a['body'] ) ? '<p class="kbl-split__body">' . kbl_rich( $a['body'] ) . '</p>' : '';

		$bullets = kbl_lines( $a['bullets'] );
		if ( $bullets ) {
			$copy .= '<ul class="kbl-checklist">';
			foreach ( $bullets as $line ) {
				$copy .= '<li>' . kbl_icon( 'check' ) . '<span>' . kbl_text( $line ) . '</span></li>';
			}
			$copy .= '</ul>';
		}

		$media = (int) $a['image'] > 0 ? '<div class="kbl-split__visual">' . kbl_img( $a['image'], 'large', 'kbl-split__img' ) . '</div>' : '';
		if ( '' === $media ) {
			/* No image yet → single column, full-width copy (no grey box). */
			$inner = '<div class="kbl-split kbl-split--text-only">' . $copy . '</div>';
		} else {
			$inner = $a['reverse'] ? $media . $copy : $copy . $media;
			$inner = '<div class="kbl-split">' . $inner . '</div>';
		}

		return kbl_shell( kbl_name( 'split' ), $a, $inner, 'kbl-split-section', kbl_head_html( array(), 'b' ) );
	},
) );

/* =====================================================================
 * Features — icon card grid
 * =================================================================== */

$register( array(
	'name'        => kbl_name( 'features' ),
	'title'       => __( 'Feature Cards', 'kabel-blocks' ),
	'description' => __( 'A grid of icon cards — reasons to trust you, service highlights, capabilities.', 'kabel-blocks' ),
	'icon'        => 'screenoptions',
	'keywords'    => array( 'features', 'cards', 'reasons', 'selling points' ),
	'attributes'  => array_merge( kbl_common_attrs(), array(
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
	'edit'        => array(
		'tree'  => array(
			array(
				'items'     => 'items',
				'classAttr' => 'columns',
				'classBase' => 'grid grid--',
				'itemClass' => 'card-feature kbl-card',
				'addLabel'  => __( 'Add a card', 'kabel-blocks' ),
				'item'      => array(
					array( 'static' => '<span class="card-feature__dot kbl-card__dot"></span>' ),
					array( 'text' => 'title', 'tag' => 'h3', 'placeholder' => __( 'Card title', 'kabel-blocks' ) ),
					array( 'rich' => 'text', 'tag' => 'p', 'placeholder' => __( 'Card text', 'kabel-blocks' ) ),
				),
			),
		),
	),
	'panels'      => array(
		array(
			'title'       => __( 'Cards', 'kabel-blocks' ),
			'initialOpen' => false,
			'fields'      => array(
				array( 'attr' => 'columns', 'label' => __( 'Columns', 'kabel-blocks' ), 'type' => 'range', 'min' => 2, 'max' => 4 ),
			),
		),
		kbl_common_panels_tail(),
	),
	'render'      => static function ( array $a ): string {
		$items = kbl_items( $a['items'], array( 'icon', 'title', 'text' ) );
		if ( ! $items ) {
			return '';
		}

		$body  = kbl_grid_open( $a['columns'] );
		foreach ( $items as $item ) {
			$body .= '<article class="card-feature kbl-card">';
			$body .= '<span class="card-feature__dot kbl-card__dot">' . kbl_icon( $item['icon'] ) . '</span>';
			$body .= $item['title'] ? '<h3>' . kbl_text( $item['title'] ) . '</h3>' : '';
			$body .= $item['text'] ? '<p>' . kbl_rich( $item['text'] ) . '</p>' : '';
			$body .= '</article>';
		}
		$body .= '</div>';

		return kbl_shell( kbl_name( 'features' ), $a, $body );
	},
) );

/* =====================================================================
 * Stats — big number strip
 * =================================================================== */

$register( array(
	'name'        => kbl_name( 'stats' ),
	'title'       => __( 'Stats Strip', 'kabel-blocks' ),
	'description' => __( 'Big numbers with labels — capacity, experience, countries served. Reads best on the deep background.', 'kabel-blocks' ),
	'icon'        => 'chart-bar',
	'keywords'    => array( 'stats', 'numbers', 'facts', 'data strip' ),
	'attributes'  => array_merge( kbl_common_attrs(), array(
		'zone' => array( 'type' => 'string', 'default' => 'a' ),
		'eyebrow' => array( 'type' => 'string', 'default' => '' ),
		'title' => array( 'type' => 'string', 'default' => '' ),
		'sub' => array( 'type' => 'string', 'default' => '' ),
		'items' => array(
			'type'    => 'array',
			'default' => array(),
			'items'   => array( 'type' => 'object' ),
		),
	) ),
	'edit'        => array(
		'extra' => 'kbl-stats-section',
		'tree'  => array(
			array(
				'items'     => 'items',
				'class'     => 'kbl-stats',
				'itemClass' => 'kbl-stats__item',
				'addLabel'  => __( 'Add a stat', 'kabel-blocks' ),
				'item'      => array(
					array( 'text' => 'value', 'tag' => 'span', 'class' => 'kbl-stats__value', 'placeholder' => '99%' ),
					array( 'text' => 'text', 'tag' => 'span', 'class' => 'kbl-stats__label', 'placeholder' => __( 'Label', 'kabel-blocks' ) ),
				),
			),
		),
	),
	'panels'      => array(
		kbl_common_panels_tail(),
	),
	'render'      => static function ( array $a ): string {
		$items = kbl_items( $a['items'], array( 'value', 'text' ) );
		if ( ! $items ) {
			return '';
		}

		$body = '<ul class="kbl-stats">';
		foreach ( $items as $item ) {
			$body .= '<li class="kbl-stats__item">';
			$body .= '<span class="kbl-stats__value">' . kbl_text( $item['value'] ) . '</span>';
			$body .= '<span class="kbl-stats__label">' . kbl_text( $item['text'] ) . '</span>';
			$body .= '</li>';
		}
		$body .= '</ul>';

		return kbl_shell( kbl_name( 'stats' ), $a, $body, 'kbl-stats-section' );
	},
) );

/* =====================================================================
 * Process — numbered steps
 * =================================================================== */

$register( array(
	'name'        => kbl_name( 'process' ),
	'title'       => __( 'Process Steps', 'kabel-blocks' ),
	'description' => __( 'A numbered journey — the OEM workflow, an onboarding path, a quality procedure.', 'kabel-blocks' ),
	'icon'        => 'editor-ol',
	'keywords'    => array( 'process', 'steps', 'workflow', 'how it works' ),
	'attributes'  => array_merge( kbl_common_attrs(), array(
		'eyebrow' => array( 'type' => 'string', 'default' => '' ),
		'title'   => array( 'type' => 'string', 'default' => '' ),
		'sub'     => array( 'type' => 'string', 'default' => '' ),
		'items'   => array(
			'type'    => 'array',
			'default' => array(),
			'items'   => array( 'type' => 'object' ),
		),
	) ),
	'edit'        => array(
		'tree'  => array(
			array(
				'items'     => 'items',
				'class'     => 'kbl-process',
				'itemClass' => 'kbl-process__item',
				'addLabel'  => __( 'Add a step', 'kabel-blocks' ),
				'item'      => array(
					array( 'static' => '<span class="kbl-process__num kbl-process__num--auto"></span>' ),
					array( 'wrap' => array( 'div', 'kbl-process__copy' ), 'nodes' => array(
						array( 'text' => 'title', 'tag' => 'h3', 'placeholder' => __( 'Step title', 'kabel-blocks' ) ),
						array( 'rich' => 'text', 'tag' => 'p', 'placeholder' => __( 'Step text', 'kabel-blocks' ) ),
					) ),
				),
			),
		),
	),
	'panels'      => array(
		kbl_common_panels_tail(),
	),
	'render'      => static function ( array $a ): string {
		$items = kbl_items( $a['items'], array( 'title', 'text' ) );
		if ( ! $items ) {
			return '';
		}

		$body = '<ol class="kbl-process">';
		foreach ( $items as $i => $item ) {
			$body .= '<li class="kbl-process__item">';
			$body .= '<span class="kbl-process__num" aria-hidden="true">' . (string) ( $i + 1 ) . '</span>';
			$body .= '<div class="kbl-process__copy">';
			$body .= $item['title'] ? '<h3>' . kbl_text( $item['title'] ) . '</h3>' : '';
			$body .= $item['text'] ? '<p>' . kbl_rich( $item['text'] ) . '</p>' : '';
			$body .= '</div></li>';
		}
		$body .= '</ol>';

		return kbl_shell( kbl_name( 'process' ), $a, $body );
	},
) );

/* =====================================================================
 * Milestones — company timeline
 * =================================================================== */

$register( array(
	'name'        => kbl_name( 'milestones' ),
	'title'       => __( 'Milestones', 'kabel-blocks' ),
	'description' => __( 'The company story as a year-by-year timeline.', 'kabel-blocks' ),
	'icon'        => 'backup',
	'keywords'    => array( 'milestones', 'timeline', 'history', 'journey' ),
	'attributes'  => array_merge( kbl_common_attrs(), array(
		'eyebrow' => array( 'type' => 'string', 'default' => '' ),
		'title'   => array( 'type' => 'string', 'default' => '' ),
		'sub'     => array( 'type' => 'string', 'default' => '' ),
		'items'   => array(
			'type'    => 'array',
			'default' => array(),
			'items'   => array( 'type' => 'object' ),
		),
	) ),
	'edit'        => array(
		'tree'  => array(
			array(
				'items'     => 'items',
				'class'     => 'timeline kbl-timeline',
				'itemClass' => 'timeline__item',
				'addLabel'  => __( 'Add a milestone', 'kabel-blocks' ),
				'item'      => array(
					array( 'text' => 'year', 'tag' => 'span', 'class' => 'timeline__year', 'placeholder' => __( 'Year / era', 'kabel-blocks' ) ),
					array( 'rich' => 'text', 'tag' => 'span', 'class' => 'timeline__text', 'placeholder' => __( 'What happened', 'kabel-blocks' ) ),
				),
			),
		),
	),
	'panels'      => array(
		kbl_common_panels_tail(),
	),
	'render'      => static function ( array $a ): string {
		$items = kbl_items( $a['items'], array( 'year', 'text' ) );
		if ( ! $items ) {
			return '';
		}

		$body = '<ol class="timeline kbl-timeline">';
		foreach ( $items as $item ) {
			$body .= '<li class="timeline__item">';
			$body .= '<span class="timeline__year">' . kbl_text( $item['year'] ) . '</span>';
			$body .= '<span class="timeline__text">' . kbl_rich( $item['text'] ) . '</span>';
			$body .= '</li>';
		}
		$body .= '</ol>';

		return kbl_shell( kbl_name( 'milestones' ), $a, $body );
	},
) );
