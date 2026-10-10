<?php
/**
 * Block specs + renderers — the proof group.
 *
 * gallery, testimonials, logos, specs (table), faq.
 *
 * Required from inside kbl_specs() — $register is provided by the engine.
 *
 * v7: every spec carries an `edit` tree — a declarative node layout the JS
 * runtime renders ON the canvas with inline RichText / repeater editing
 * (see docs/kabel-v7-inline-editing.md). Panels keep style controls only;
 * content is edited where it lives.
 *
 * Documented exception: gallery + logos hold plain attachment-ID arrays
 * ('ids'), which the canvas items node cannot bind to (it needs objects) —
 * their image lists stay in the sidebar (media-array) and the canvas shows
 * a hint instead.
 *
 * @package Kabel_Blocks
 */

defined( 'ABSPATH' ) || exit;

/* =====================================================================
 * Gallery — image wall
 * =================================================================== */

$register( array(
        'name'        => kbl_name( 'gallery' ),
        'title'       => __( 'Gallery', 'kabel-blocks' ),
        'description' => __( 'A grid of images — products, factory floor, trade fair booths.', 'kabel-blocks' ),
        'icon'        => 'format-gallery',
        'keywords'    => array( 'gallery', 'images', 'photos', 'image wall' ),
        'attributes'  => array_merge( kbl_common_attrs(), array(
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
        'edit'        => array(
                'tree'  => array(
                        array( 'static' => '<div class="kbl-hint">' . esc_html__( 'The image wall is managed in the sidebar — open "Images" to add, remove or reorder photos; they appear as a grid on the published page.', 'kabel-blocks' ) . '</div>' ),
                ),
        ),
        'panels'      => array_merge(
                array(
                        array(
                                'title'       => __( 'Images', 'kabel-blocks' ),
                                'initialOpen' => true,
                                'fields'      => array(
                                        array( 'attr' => 'ids', 'label' => __( 'Images', 'kabel-blocks' ), 'type' => 'media-array' ),
                                        array( 'attr' => 'columns', 'label' => __( 'Columns', 'kabel-blocks' ), 'type' => 'range', 'min' => 2, 'max' => 4 ),
                                ),
                        ),
                ),
                kbl_common_panels_tail()
        ),
        'render'      => static function ( array $a ): string {
                $ids = array_filter( array_map( 'intval', (array) $a['ids'] ) );
                if ( ! $ids ) {
                        return '';
                }

                $body  = kbl_grid_open( $a['columns'], 'kbl-gallery' );
                foreach ( $ids as $id ) {
                        $body .= '<figure class="kbl-gallery__item">' . kbl_img( $id, 'large', 'kbl-gallery__img' ) . '</figure>';
                }
                $body .= '</div>';

                return kbl_shell( kbl_name( 'gallery' ), $a, $body );
        },
) );

/* =====================================================================
 * Testimonials — client quotes
 * =================================================================== */

$register( array(
        'name'        => kbl_name( 'testimonials' ),
        'title'       => __( 'Testimonials', 'kabel-blocks' ),
        'description' => __( 'Client quotes with name and role — social proof for B2B buyers.', 'kabel-blocks' ),
        'icon'        => 'format-quote',
        'keywords'    => array( 'testimonials', 'quotes', 'reviews', 'customer testimonials' ),
        'attributes'  => array_merge( kbl_common_attrs(), array(
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
        'edit'        => array(
                'tree'  => array(
                        array(
                                'items'     => 'items',
                                'classAttr' => 'columns',
                                'classBase' => 'grid grid--',
                                'itemClass' => 'kbl-quote',
                                'addLabel'  => __( 'Add a quote', 'kabel-blocks' ),
                                'item'      => array(
                                        /* Mirrors the render: the real sparkle SVG, not a stand-in glyph. */
                                        array( 'static' => '<span class="kbl-quote__mark" aria-hidden="true">' . kbl_icon( 'sparkle' ) . '</span>' ),
                                        array( 'rich' => 'quote', 'tag' => 'blockquote', 'class' => 'kbl-quote__text', 'placeholder' => __( 'Client quote', 'kabel-blocks' ) ),
                                        array( 'wrap' => array( 'figcaption', 'kbl-quote__by' ), 'nodes' => array(
                                                array( 'text' => 'name', 'tag' => 'strong', 'placeholder' => __( 'Person', 'kabel-blocks' ) ),
                                                array( 'text' => 'role', 'tag' => 'span', 'placeholder' => __( 'Role / company', 'kabel-blocks' ) ),
                                        ) ),
                                ),
                        ),
                ),
        ),
        'panels'      => array_merge(
                array(
                        array(
                                'title'       => __( 'Cards', 'kabel-blocks' ),
                                'initialOpen' => false,
                                'fields'      => array(
                                        array( 'attr' => 'columns', 'label' => __( 'Columns', 'kabel-blocks' ), 'type' => 'range', 'min' => 1, 'max' => 3 ),
                                ),
                        ),
                ),
                kbl_common_panels_tail()
        ),
        'render'      => static function ( array $a ): string {
                $items = kbl_items( $a['items'], array( 'quote', 'name', 'role' ) );
                if ( ! $items ) {
                        return '';
                }

                $body  = kbl_grid_open( $a['columns'], 'kbl-quotes' );
                foreach ( $items as $item ) {
                        $body .= '<figure class="kbl-quote">';
                        $body .= '<span class="kbl-quote__mark" aria-hidden="true">' . kbl_icon( 'sparkle' ) . '</span>';
                        $body .= '<blockquote class="kbl-quote__text">' . kbl_rich( $item['quote'] ) . '</blockquote>';
                        if ( '' !== $item['name'] || '' !== $item['role'] ) {
                                $body .= '<figcaption class="kbl-quote__by">';
                                $body .= $item['name'] ? '<strong>' . kbl_text( $item['name'] ) . '</strong>' : '';
                                $body .= $item['role'] ? '<span>' . kbl_text( $item['role'] ) . '</span>' : '';
                                $body .= '</figcaption>';
                        }
                        $body .= '</figure>';
                }
                $body .= '</div>';

                return kbl_shell( kbl_name( 'testimonials' ), $a, $body );
        },
) );

/* =====================================================================
 * Logos — client / partner wall
 * =================================================================== */

$register( array(
        'name'        => kbl_name( 'logos' ),
        'title'       => __( 'Client Logos', 'kabel-blocks' ),
        'description' => __( 'A quiet wall of client or partner logos. Falls back to text chips when no images are set.', 'kabel-blocks' ),
        'icon'        => 'awards',
        'keywords'    => array( 'logos', 'clients', 'partners', 'brands' ),
        'attributes'  => array_merge( kbl_common_attrs(), array(
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
        'edit'        => array(
                'tree'  => array(
                        array( 'static' => '<div class="kbl-hint">' . esc_html__( 'Logo images are managed in the sidebar — open "Logos" to pick them. When none are set, the text chips below are shown on the page instead.', 'kabel-blocks' ) . '</div>' ),
                        array( 'rich' => 'names', 'tag' => 'ul', 'class' => 'chip-row kbl-logos-fallback', 'multiline' => 'li', 'placeholder' => __( 'Fallback names — one brand per line', 'kabel-blocks' ) ),
                ),
        ),
        'panels'      => array_merge(
                array(
                        array(
                                'title'       => __( 'Logos', 'kabel-blocks' ),
                                'initialOpen' => true,
                                'fields'      => array(
                                        array( 'attr' => 'ids', 'label' => __( 'Logo images', 'kabel-blocks' ), 'type' => 'media-array' ),
                                ),
                        ),
                ),
                kbl_common_panels_tail()
        ),
        'render'      => static function ( array $a ): string {
                $ids   = array_filter( array_map( 'intval', (array) $a['ids'] ) );
                $names = kbl_lines( $a['names'] );

                if ( $ids ) {
                        $body = '<div class="kbl-logos">';
                        foreach ( $ids as $id ) {
                                $body .= '<span class="kbl-logos__item">' . kbl_img( $id, 'medium', 'kbl-logos__img' ) . '</span>';
                        }
                        $body .= '</div>';
                } elseif ( $names ) {
                        $body = '<ul class="chip-row kbl-logos-fallback">';
                        foreach ( $names as $name ) {
                                $body .= '<li class="chip">' . kbl_text( $name ) . '</li>';
                        }
                        $body .= '</ul>';
                } else {
                        return '';
                }

                return kbl_shell( kbl_name( 'logos' ), $a, $body );
        },
) );

/* =====================================================================
 * Specs — parameter table
 * =================================================================== */

$register( array(
        'name'        => kbl_name( 'specs' ),
        'title'       => __( 'Specs Table', 'kabel-blocks' ),
        'description' => __( 'A two-column parameter table — model specs, material data, service terms.', 'kabel-blocks' ),
        'icon'        => 'editor-table',
        'keywords'    => array( 'specs', 'specifications', 'parameters', 'table' ),
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
                                'class'     => 'kbl-specs',
                                'itemClass' => 'kbl-specs-row',
                                'addLabel'  => __( 'Add a row', 'kabel-blocks' ),
                                'item'      => array(
                                        array( 'text' => 'label', 'tag' => 'span', 'class' => 'kbl-specs-row__th', 'placeholder' => __( 'Parameter', 'kabel-blocks' ) ),
                                        array( 'text' => 'value', 'tag' => 'span', 'class' => 'kbl-specs-row__td', 'placeholder' => __( 'Value', 'kabel-blocks' ) ),
                                ),
                        ),
                ),
        ),
        'panels'      => kbl_common_panels_tail(),
        'render'      => static function ( array $a ): string {
                $items = kbl_items( $a['items'], array( 'label', 'value' ) );
                if ( ! $items ) {
                        return '';
                }

                $body  = '<div class="kbl-specs-wrap">';
                $body .= '<table class="spec-table kbl-specs"><tbody>';
                foreach ( $items as $item ) {
                        $body .= '<tr><th scope="row">' . kbl_text( $item['label'] ) . '</th><td>' . kbl_text( $item['value'] ) . '</td></tr>';
                }
                $body .= '</tbody></table></div>';

                return kbl_shell( kbl_name( 'specs' ), $a, $body );
        },
) );

/* =====================================================================
 * FAQ — accordions
 * =================================================================== */

$register( array(
        'name'        => kbl_name( 'faq' ),
        'title'       => __( 'FAQ', 'kabel-blocks' ),
        'description' => __( 'Accordion questions and answers — OEM terms, after-sales, logistics.', 'kabel-blocks' ),
        'icon'        => 'editor-help',
        'keywords'    => array( 'faq', 'questions', 'accordion', 'common questions' ),
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
                                'class'     => 'kbl-faq',
                                'itemClass' => 'kbl-faq__item',
                                'addLabel'  => __( 'Add a question', 'kabel-blocks' ),
                                'item'      => array(
                                        array( 'text' => 'q', 'tag' => 'summary', 'class' => 'kbl-faq__q', 'placeholder' => __( 'Question', 'kabel-blocks' ) ),
                                        array( 'rich' => 'a', 'tag' => 'div', 'class' => 'kbl-faq__a', 'placeholder' => __( 'Answer', 'kabel-blocks' ) ),
                                ),
                        ),
                ),
        ),
        'panels'      => kbl_common_panels_tail(),
        'render'      => static function ( array $a ): string {
                $items = kbl_items( $a['items'], array( 'q', 'a' ) );
                if ( ! $items ) {
                        return '';
                }

                $body = '<div class="kbl-faq">';
                foreach ( $items as $item ) {
                        $body .= '<details class="kbl-faq__item">';
                        $body .= '<summary class="kbl-faq__q">' . kbl_text( $item['q'] ) . '</summary>';
                        $body .= '<div class="kbl-faq__a">' . kbl_rich( $item['a'] ) . '</div>';
                        $body .= '</details>';
                }
                $body .= '</div>';

                return kbl_shell( kbl_name( 'faq' ), $a, $body, '', kbl_head_html( $a, $a['zone'] ?? 'b' ) );
        },
) );
