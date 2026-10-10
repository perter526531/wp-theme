<?php
/**
 * Block specs + renderers — the storytelling group.
 *
 * hero, split, features, stats, process, milestones.
 *
 * This file is required from inside dzb_specs() — the $register closure and
 * the $specs registry are provided by the engine.
 *
 * @package Dazhen_Blocks
 */

defined( 'ABSPATH' ) || exit;

/* =====================================================================
 * Hero — the page opener
 * =================================================================== */

$register( array(
        'name'        => dzb_name( 'hero' ),
        'title'       => __( 'Hero', 'dazhen-blocks' ),
        'description' => __( 'The page opener: eyebrow, big headline, intro and two buttons — centered, or split with a product image.', 'dazhen-blocks' ),
        'icon'        => 'superhero-alt',
        'keywords'    => array( 'hero', 'banner', 'opener', 'hero region' ),
        'attributes'  => array_merge( dzb_common_attrs(), array(
                'zone'            => array( 'type' => 'string', 'default' => 'a' ),
                'layout'          => array( 'type' => 'string', 'default' => 'split' ),
                'eyebrow'         => array( 'type' => 'string', 'default' => '' ),
                'title'           => array( 'type' => 'string', 'default' => '' ),
                'sub'             => array( 'type' => 'string', 'default' => '' ),
                'image'           => array( 'type' => 'number', 'default' => 0 ),
                'primaryLabel'    => array( 'type' => 'string', 'default' => '' ),
                'primaryUrl'      => array( 'type' => 'string', 'default' => '' ),
                'primaryProduct'  => array( 'type' => 'string', 'default' => '' ),
                'secondaryLabel'  => array( 'type' => 'string', 'default' => '' ),
                'secondaryUrl'    => array( 'type' => 'string', 'default' => '' ),
        ) ),
        'panels'      => array_merge(
                array( dzb_common_panels()[0] ),
                array( array(
                        'title'       => __( 'Buttons', 'dazhen-blocks' ),
                        'initialOpen' => false,
                        'fields'      => array(
                                array( 'attr' => 'primaryLabel', 'label' => __( 'Primary button — label', 'dazhen-blocks' ), 'type' => 'text', 'placeholder' => __( 'Get a Quote', 'dazhen-blocks' ) ),
                                array( 'attr' => 'primaryUrl', 'label' => __( 'Primary button — link', 'dazhen-blocks' ), 'type' => 'text', 'help' => __( 'Leave empty with the Dazhen theme active to open the inquiry popup.', 'dazhen-blocks' ) ),
                                array( 'attr' => 'primaryProduct', 'label' => __( 'Primary button — pre-filled product (optional)', 'dazhen-blocks' ), 'type' => 'text' ),
                                array( 'attr' => 'secondaryLabel', 'label' => __( 'Secondary button — label', 'dazhen-blocks' ), 'type' => 'text' ),
                                array( 'attr' => 'secondaryUrl', 'label' => __( 'Secondary button — link', 'dazhen-blocks' ), 'type' => 'text' ),
                        ),
                ) ),
                array( array(
                        'title'       => __( 'Layout', 'dazhen-blocks' ),
                        'initialOpen' => false,
                        'fields'      => array(
                                array(
                                        'attr'    => 'layout',
                                        'label'   => __( 'Arrangement', 'dazhen-blocks' ),
                                        'type'    => 'select',
                                        'options' => array(
                                                array( 'value' => 'split', 'label' => __( 'Split — text + image', 'dazhen-blocks' ) ),
                                                array( 'value' => 'center', 'label' => __( 'Centered', 'dazhen-blocks' ) ),
                                        ),
                                ),
                                array( 'attr' => 'image', 'label' => __( 'Image (split layout)', 'dazhen-blocks' ), 'type' => 'media' ),
                        ),
                ) ),
                dzb_common_panels_tail()
        ),
        'render'      => static function ( array $a ): string {
                $eyebrow = dzb_text( $a['eyebrow'] );
                $title   = dzb_text( $a['title'] );
                $sub     = dzb_text( $a['sub'] );

                $buttons = '';
                if ( '' !== dzb_text( $a['primaryLabel'] ) ) {
                        $buttons .= dzb_button_html( $a['primaryLabel'], $a['primaryUrl'], $a['primaryProduct'], 'cta' );
                }
                if ( '' !== dzb_text( $a['secondaryLabel'] ) ) {
                        $buttons .= dzb_button_html( $a['secondaryLabel'], $a['secondaryUrl'], '', 'ghost' );
                }
                if ( '' !== $buttons ) {
                        $buttons = '<div class="dzb-hero__actions">' . $buttons . '</div>';
                }

                $copy  = $eyebrow ? '<p class="eyebrow eyebrow--a">' . $eyebrow . '</p>' : '';
                $copy .= $title ? '<h1 class="dzb-hero__title">' . $title . '</h1>' : '';
                $copy .= $sub ? '<p class="dzb-hero__sub">' . $sub . '</p>' : '';
                $copy .= $buttons;

                /*
                 * Split layout needs an image; without one the copy would sit beside
                 * an empty placeholder — fall back to the centered arrangement.
                 */
                $has_image = (int) $a['image'] > 0;
                if ( 'center' === $a['layout'] || ! $has_image ) {
                        $body = '<div class="dzb-hero-center">' . $copy . '</div>';
                } else {
                        $media = '<div class="dzb-hero__visual">' . dzb_img( $a['image'], 'large', 'dzb-hero__img' ) . '</div>';
                        $body  = '<div class="dzb-hero-split">' . $copy . $media . '</div>';
                }

                /* '' head override: the hero renders its own heading — suppress the shared section head. */
                return dzb_shell( dzb_name( 'hero' ), $a, $body, 'dzb-hero', '' );
        },
) );

/* =====================================================================
 * Split — text beside an image
 * =================================================================== */

$register( array(
        'name'        => dzb_name( 'split' ),
        'title'       => __( 'Text + Image', 'dazhen-blocks' ),
        'description' => __( 'A two-column section: story or argument on one side, image on the other. Great for factory, process or about passages.', 'dazhen-blocks' ),
        'icon'        => 'columns',
        'keywords'    => array( 'split', 'two column', 'text + image', 'two-column layout' ),
        'attributes'  => array_merge( dzb_common_attrs(), array(
                'eyebrow' => array( 'type' => 'string', 'default' => '' ),
                'title'   => array( 'type' => 'string', 'default' => '' ),
                'sub'     => array( 'type' => 'string', 'default' => '' ),
                'body'    => array( 'type' => 'string', 'default' => '' ),
                'bullets' => array( 'type' => 'string', 'default' => '' ),
                'image'   => array( 'type' => 'number', 'default' => 0 ),
                'reverse' => array( 'type' => 'boolean', 'default' => false ),
        ) ),
        'panels'      => array_merge(
                array(
                        array(
                                'title'       => __( 'Content', 'dazhen-blocks' ),
                                'initialOpen' => true,
                                'fields'      => array(
                                        array( 'attr' => 'body', 'label' => __( 'Body text', 'dazhen-blocks' ), 'type' => 'textarea' ),
                                        array( 'attr' => 'bullets', 'label' => __( 'Checklist (one per line)', 'dazhen-blocks' ), 'type' => 'textarea', 'help' => __( 'Each line becomes a check bullet.', 'dazhen-blocks' ) ),
                                        array( 'attr' => 'image', 'label' => __( 'Image', 'dazhen-blocks' ), 'type' => 'media' ),
                                        array( 'attr' => 'reverse', 'label' => __( 'Flip sides (image left)', 'dazhen-blocks' ), 'type' => 'toggle' ),
                                ),
                        ),
                ),
                dzb_common_panels()
        ),
        'render'      => static function ( array $a ): string {
                $eyebrow = dzb_text( $a['eyebrow'] );
                $title   = dzb_text( $a['title'] );
                $sub     = dzb_text( $a['sub'] );

                $copy  = $eyebrow ? '<p class="eyebrow eyebrow--b">' . $eyebrow . '</p>' : '';
                $copy .= $title ? '<h2 class="section-head__title dzb-split__title">' . $title . '</h2>' : '';
                $copy .= $sub ? '<p class="section-head__sub">' . $sub . '</p>' : '';
                $copy .= dzb_text( $a['body'] ) ? '<p class="dzb-split__body">' . dzb_text( $a['body'] ) . '</p>' : '';

                $bullets = dzb_lines( $a['bullets'] );
                if ( $bullets ) {
                        $copy .= '<ul class="dzb-checklist">';
                        foreach ( $bullets as $line ) {
                                $copy .= '<li>' . dzb_icon( 'check' ) . '<span>' . dzb_text( $line ) . '</span></li>';
                        }
                        $copy .= '</ul>';
                }

                $media = (int) $a['image'] > 0 ? dzb_img( $a['image'], 'large', 'dzb-split__img' ) : '';
                if ( '' === $media ) {
                        /* No image yet → single column, full-width copy (no grey box). */
                        $inner = '<div class="dzb-split dzb-split--text-only">' . $copy . '</div>';
                } else {
                        $inner = $a['reverse'] ? $media . $copy : $copy . $media;
                        $inner = '<div class="dzb-split">' . $inner . '</div>';
                }

                return dzb_shell( dzb_name( 'split' ), $a, $inner, 'dzb-split-section', dzb_head_html( array(), 'b' ) );
        },
) );

/* =====================================================================
 * Features — icon card grid
 * =================================================================== */

$register( array(
        'name'        => dzb_name( 'features' ),
        'title'       => __( 'Feature Cards', 'dazhen-blocks' ),
        'description' => __( 'A grid of icon cards — reasons to trust you, service highlights, capabilities.', 'dazhen-blocks' ),
        'icon'        => 'screenoptions',
        'keywords'    => array( 'features', 'cards', 'reasons', 'selling points' ),
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
                                                'defaults'  => array( 'icon' => 'cube', 'title' => '', 'text' => '' ),
                                                'fields'    => array(
                                                        array( 'attr' => 'title', 'label' => __( 'Title', 'dazhen-blocks' ), 'type' => 'text' ),
                                                        array( 'attr' => 'text', 'label' => __( 'Text', 'dazhen-blocks' ), 'type' => 'textarea' ),
                                                        array( 'attr' => 'icon', 'label' => __( 'Icon', 'dazhen-blocks' ), 'type' => 'select', 'options' => dzb_icon_options() ),
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

                $body  = dzb_grid_open( $a['columns'] );
                foreach ( $items as $item ) {
                        $body .= '<article class="card-feature dzb-card">';
                        $body .= '<span class="card-feature__dot dzb-card__dot">' . dzb_icon( $item['icon'] ) . '</span>';
                        $body .= $item['title'] ? '<h3>' . dzb_text( $item['title'] ) . '</h3>' : '';
                        $body .= $item['text'] ? '<p>' . dzb_text( $item['text'] ) . '</p>' : '';
                        $body .= '</article>';
                }
                $body .= '</div>';

                return dzb_shell( dzb_name( 'features' ), $a, $body );
        },
) );

/* =====================================================================
 * Stats — big number strip
 * =================================================================== */

$register( array(
        'name'        => dzb_name( 'stats' ),
        'title'       => __( 'Stats Strip', 'dazhen-blocks' ),
        'description' => __( 'Big numbers with labels — capacity, experience, countries served. Reads best on the deep background.', 'dazhen-blocks' ),
        'icon'        => 'chart-bar',
        'keywords'    => array( 'stats', 'numbers', 'facts', 'data strip' ),
        'attributes'  => array_merge( dzb_common_attrs(), array(
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
        'panels'      => array_merge(
                array(
                        array(
                                'title'       => __( 'Numbers', 'dazhen-blocks' ),
                                'initialOpen' => true,
                                'fields'      => array(
                                        array(
                                                'attr'      => 'items',
                                                'label'     => __( 'Stats', 'dazhen-blocks' ),
                                                'type'      => 'repeater',
                                                'itemLabel' => __( 'Stat', 'dazhen-blocks' ),
                                                'addLabel'  => __( 'Add a stat', 'dazhen-blocks' ),
                                                'defaults'  => array( 'value' => '', 'text' => '' ),
                                                'fields'    => array(
                                                        array( 'attr' => 'value', 'label' => __( 'Number (e.g. 99% / 12+)', 'dazhen-blocks' ), 'type' => 'text' ),
                                                        array( 'attr' => 'text', 'label' => __( 'Label', 'dazhen-blocks' ), 'type' => 'text' ),
                                                ),
                                        ),
                                ),
                        ),
                ),
                dzb_common_panels()
        ),
        'render'      => static function ( array $a ): string {
                $items = dzb_items( $a['items'], array( 'value', 'text' ) );
                if ( ! $items ) {
                        return '';
                }

                $body = '<ul class="dzb-stats">';
                foreach ( $items as $item ) {
                        $body .= '<li class="dzb-stats__item">';
                        $body .= '<span class="dzb-stats__value">' . dzb_text( $item['value'] ) . '</span>';
                        $body .= '<span class="dzb-stats__label">' . dzb_text( $item['text'] ) . '</span>';
                        $body .= '</li>';
                }
                $body .= '</ul>';

                return dzb_shell( dzb_name( 'stats' ), $a, $body, 'dzb-stats-section' );
        },
) );

/* =====================================================================
 * Process — numbered steps
 * =================================================================== */

$register( array(
        'name'        => dzb_name( 'process' ),
        'title'       => __( 'Process Steps', 'dazhen-blocks' ),
        'description' => __( 'A numbered journey — the OEM workflow, an onboarding path, a quality procedure.', 'dazhen-blocks' ),
        'icon'        => 'editor-ol',
        'keywords'    => array( 'process', 'steps', 'workflow', 'how it works' ),
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
                                'title'       => __( 'Steps', 'dazhen-blocks' ),
                                'initialOpen' => true,
                                'fields'      => array(
                                        array(
                                                'attr'      => 'items',
                                                'label'     => __( 'Steps', 'dazhen-blocks' ),
                                                'type'      => 'repeater',
                                                'itemLabel' => __( 'Step', 'dazhen-blocks' ),
                                                'addLabel'  => __( 'Add a step', 'dazhen-blocks' ),
                                                'defaults'  => array( 'title' => '', 'text' => '' ),
                                                'fields'    => array(
                                                        array( 'attr' => 'title', 'label' => __( 'Step title', 'dazhen-blocks' ), 'type' => 'text' ),
                                                        array( 'attr' => 'text', 'label' => __( 'Step text', 'dazhen-blocks' ), 'type' => 'textarea' ),
                                                ),
                                        ),
                                ),
                        ),
                ),
                dzb_common_panels()
        ),
        'render'      => static function ( array $a ): string {
                $items = dzb_items( $a['items'], array( 'title', 'text' ) );
                if ( ! $items ) {
                        return '';
                }

                $body = '<ol class="dzb-process">';
                foreach ( $items as $i => $item ) {
                        $body .= '<li class="dzb-process__item">';
                        $body .= '<span class="dzb-process__num" aria-hidden="true">' . (string) ( $i + 1 ) . '</span>';
                        $body .= '<div class="dzb-process__copy">';
                        $body .= $item['title'] ? '<h3>' . dzb_text( $item['title'] ) . '</h3>' : '';
                        $body .= $item['text'] ? '<p>' . dzb_text( $item['text'] ) . '</p>' : '';
                        $body .= '</div></li>';
                }
                $body .= '</ol>';

                return dzb_shell( dzb_name( 'process' ), $a, $body );
        },
) );

/* =====================================================================
 * Milestones — company timeline
 * =================================================================== */

$register( array(
        'name'        => dzb_name( 'milestones' ),
        'title'       => __( 'Milestones', 'dazhen-blocks' ),
        'description' => __( 'The company story as a year-by-year timeline.', 'dazhen-blocks' ),
        'icon'        => 'backup',
        'keywords'    => array( 'milestones', 'timeline', 'history', 'journey' ),
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
                                'title'       => __( 'Timeline', 'dazhen-blocks' ),
                                'initialOpen' => true,
                                'fields'      => array(
                                        array(
                                                'attr'      => 'items',
                                                'label'     => __( 'Milestones', 'dazhen-blocks' ),
                                                'type'      => 'repeater',
                                                'itemLabel' => __( 'Milestone', 'dazhen-blocks' ),
                                                'addLabel'  => __( 'Add a milestone', 'dazhen-blocks' ),
                                                'defaults'  => array( 'year' => '', 'text' => '' ),
                                                'fields'    => array(
                                                        array( 'attr' => 'year', 'label' => __( 'Year', 'dazhen-blocks' ), 'type' => 'text' ),
                                                        array( 'attr' => 'text', 'label' => __( 'What happened', 'dazhen-blocks' ), 'type' => 'textarea' ),
                                                ),
                                        ),
                                ),
                        ),
                ),
                dzb_common_panels()
        ),
        'render'      => static function ( array $a ): string {
                $items = dzb_items( $a['items'], array( 'year', 'text' ) );
                if ( ! $items ) {
                        return '';
                }

                $body = '<ol class="timeline dzb-timeline">';
                foreach ( $items as $item ) {
                        $body .= '<li class="timeline__item">';
                        $body .= '<span class="timeline__year">' . dzb_text( $item['year'] ) . '</span>';
                        $body .= '<span class="timeline__text">' . dzb_text( $item['text'] ) . '</span>';
                        $body .= '</li>';
                }
                $body .= '</ol>';

                return dzb_shell( dzb_name( 'milestones' ), $a, $body );
        },
) );
