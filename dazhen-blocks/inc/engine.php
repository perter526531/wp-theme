<?php
/**
 * The Dazhen Blocks engine — our own block interpreter.
 *
 * Every Dazhen block is declared ONCE as a spec (data), and this engine does
 * the rest on both sides of the fence:
 *
 *   Editor (JS)      assets/js/blocks-editor.js is a GENERIC runtime: it reads
 *                    the specs injected by inc/assets.php and registers every
 *                    block with auto-built sidebar controls. Adding a block
 *                    never touches the JS again.
 *   Front end (PHP)  dzb_render_block() looks the spec up and dispatches to
 *                    the block's render callback. Every block shares the same
 *                    section anatomy (zone / padding / heading), so the whole
 *                    kit behaves like one coherent product.
 *
 * Spec shape (see dzb_specs() consumers):
 *   name, title, description, icon, keywords, category, supports, inner,
 *   attributes (Gutenberg schema incl. defaults), panels (sidebar UI),
 *   render (PHP callback, engine-side only).
 *
 * @package Dazhen_Blocks
 */

defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------
 * Registry
 * ------------------------------------------------------------------- */

/**
 * The shared section anatomy every layout block understands.
 * Blocks merge this into their attributes and panels.
 */
function dzb_common_attrs(): array {
        return array(
                'zone' => array(
                        'type'    => 'string',
                        'default' => 'b',
                ),
                'pad'  => array(
                        'type'    => 'string',
                        'default' => 'default',
                ),
        );
}

/**
 * Shared "Section heading" + "Section look" panel definitions.
 */
function dzb_common_panels(): array {
        return array(
                array(
                        'title'       => __( 'Section heading', 'dazhen-blocks' ),
                        'initialOpen' => true,
                        'fields'      => array(
                                array( 'attr' => 'eyebrow', 'label' => __( 'Eyebrow (small label above the title)', 'dazhen-blocks' ), 'type' => 'text' ),
                                array( 'attr' => 'title', 'label' => __( 'Title', 'dazhen-blocks' ), 'type' => 'textarea' ),
                                array( 'attr' => 'sub', 'label' => __( 'Intro line', 'dazhen-blocks' ), 'type' => 'textarea' ),
                        ),
                ),
                array(
                        'title'       => __( 'Section look', 'dazhen-blocks' ),
                        'initialOpen' => false,
                        'fields'      => array(
                                array(
                                        'attr'    => 'zone',
                                        'label'   => __( 'Background', 'dazhen-blocks' ),
                                        'type'    => 'select',
                                        'options' => array(
                                                array( 'value' => 'a', 'label' => __( 'Deep sea (dark)', 'dazhen-blocks' ) ),
                                                array( 'value' => 'b', 'label' => __( 'Paper (light)', 'dazhen-blocks' ) ),
                                                array( 'value' => 'alt', 'label' => __( 'Paper — alternate tint', 'dazhen-blocks' ) ),
                                        ),
                                ),
                                array(
                                        'attr'    => 'pad',
                                        'label'   => __( 'Vertical padding', 'dazhen-blocks' ),
                                        'type'    => 'select',
                                        'options' => array(
                                                array( 'value' => 'compact', 'label' => __( 'Compact', 'dazhen-blocks' ) ),
                                                array( 'value' => 'default', 'label' => __( 'Default', 'dazhen-blocks' ) ),
                                                array( 'value' => 'spacious', 'label' => __( 'Spacious', 'dazhen-blocks' ) ),
                                        ),
                                ),
                        ),
                ),
        );
}

/**
 * Just the "Section look" panel (the tail of dzb_common_panels()).
 */
function dzb_common_panels_tail(): array {
        return array_slice( dzb_common_panels(), 1 );
}

/**
 * All block specs, keyed by block name.
 *
 * Specs are plain data: the editor runtime builds its UI from them and the
 * renderer dispatches on them. New blocks = one entry + one render callback.
 */
function dzb_specs(): array {
        static $specs = null;
        if ( null !== $specs ) {
                return $specs;
        }

        $specs = array();

        $register = static function ( array $spec ) use ( &$specs ): void {
                $spec['category'] = $spec['category'] ?? 'dazhen-blocks';
                $spec['supports'] = $spec['supports'] ?? array( 'html' => false );
                $spec['keywords'] = $spec['keywords'] ?? array();
                $spec['panels']   = $spec['panels'] ?? array();
                $specs[ $spec['name'] ] = $spec;
        };

        require_once DAZHEN_BLOCKS_DIR . 'inc/blocks-content.php'; // hero … milestones.
        require_once DAZHEN_BLOCKS_DIR . 'inc/blocks-proof.php';   // gallery … faq.
        require_once DAZHEN_BLOCKS_DIR . 'inc/blocks-convert.php'; // products … section.

        /**
         * Filter the full spec registry (add / remove / restyle blocks).
         */
        $specs = (array) apply_filters( 'dzb_specs', $specs );

        return $specs;
}

/**
 * One spec by name (or null).
 */
function dzb_spec( string $name ): ?array {
        $specs = dzb_specs();
        return $specs[ $name ] ?? null;
}

/* ---------------------------------------------------------------------
 * Attribute helpers
 * ------------------------------------------------------------------- */

/**
 * Merge user attributes over the spec defaults (type-coerced).
 */
function dzb_attrs( string $name, array $attributes ): array {
        $spec = dzb_spec( $name );
        if ( null === $spec ) {
                return $attributes;
        }

        $merged = array();
        foreach ( ( $spec['attributes'] ?? array() ) as $key => $schema ) {
                $value = $attributes[ $key ] ?? ( $schema['default'] ?? null );
                switch ( $schema['type'] ?? 'string' ) {
                        case 'number':
                                if ( is_numeric( $value ) ) {
                                        $value = (float) $value;
                                        if ( (int) $value === $value ) {
                                                $value = (int) $value;
                                        }
                                } else {
                                        $value = $schema['default'] ?? 0;
                                }
                                break;
                        case 'boolean':
                                $value = (bool) $value;
                                break;
                        case 'array':
                                $value = is_array( $value ) ? $value : ( $schema['default'] ?? array() );
                                break;
                        case 'object':
                                if ( ! is_array( $value ) ) {
                                        $value = $schema['default'] ?? array();
                                }
                                break;
                        default:
                                $value = is_string( $value ) ? $value : (string) ( $value ?? '' );
                }
                $merged[ $key ] = $value;
        }

        /**
         * Filter the merged attributes for one block instance.
         */
        return (array) apply_filters( 'dzb_attrs', $merged, $name, $attributes );
}

/**
 * Escaped plain text.
 */
function dzb_text( $value ): string {
        return esc_html( trim( (string) $value ) );
}

/**
 * Multi-line textarea → array of trimmed non-empty lines.
 *
 * @return string[]
 */
function dzb_lines( $value ): array {
        $lines = is_array( $value ) ? $value : explode( "\n", (string) $value );
        $out   = array();
        foreach ( $lines as $line ) {
                $line = trim( (string) $line );
                if ( '' !== $line ) {
                        $out[] = $line;
                }
        }
        return $out;
}

/**
 * Repeater attribute → array of item arrays with the given keys defaulted.
 *
 * @param string[] $keys Item keys to keep (in order).
 * @return array<int, array<string,string>>
 */
function dzb_items( $value, array $keys ): array {
        $out = array();
        if ( is_array( $value ) ) {
                foreach ( $value as $item ) {
                        if ( ! is_array( $item ) ) {
                                continue;
                        }
                        $clean = array();
                        $has   = false;
                        foreach ( $keys as $key ) {
                                $text          = trim( (string) ( $item[ $key ] ?? '' ) );
                                $clean[ $key ] = $text;
                                if ( '' !== $text ) {
                                        $has = true;
                                }
                        }
                        if ( $has ) {
                                $out[] = $clean;
                        }
                }
        }
        return $out;
}

/**
 * Safe URL (empty string when invalid).
 */
function dzb_url( $value ): string {
        $raw = trim( (string) $value );
        if ( '' === $raw ) {
                return '';
        }
        if ( preg_match( '/^(https?:\/\/|\/|#|mailto:|tel:)/i', $raw ) ) {
                return esc_url( $raw );
        }
        return esc_url( 'https://' . $raw );
}

/**
 * Attachment image URL with graceful fallbacks.
 */
function dzb_media_src( $id, string $size = 'large' ): string {
        $id = (int) $id;
        if ( $id < 1 ) {
                return '';
        }
        $src = wp_get_attachment_image_url( $id, $size );
        if ( ! $src ) {
                $src = wp_get_attachment_url( $id );
        }
        return $src ? esc_url( $src ) : '';
}

/**
 * Attachment <img> (or a styled placeholder when the id is empty/missing).
 */
function dzb_img( $id, string $size = 'large', string $class = '' ): string {
        $id  = (int) $id;
        $alt = $id ? (string) get_post_field( 'post_title', $id ) : '';
        if ( $id < 1 ) {
                return '<span class="dzb-ph ' . esc_attr( $class ) . '" aria-hidden="true"></span>';
        }
        $html = wp_get_attachment_image( $id, $size, false, array( 'class' => $class, 'alt' => $alt ) );
        if ( ! $html ) {
                $src  = dzb_media_src( $id );
                $html = $src
                        ? '<img class="' . esc_attr( $class ) . '" src="' . $src . '" alt="' . esc_attr( $alt ) . '" loading="lazy" />'
                        : '<span class="dzb-ph ' . esc_attr( $class ) . '" aria-hidden="true"></span>';
        }
        return $html;
}

/* ---------------------------------------------------------------------
 * Inline icon set (stroke, currentColor)
 * ------------------------------------------------------------------- */

/**
 * Inline SVG icon by slug (used as default card icons etc.).
 */
function dzb_icon( string $slug ): string {
        $paths = array(
                'shield'  => '<path d="M12 3l7 3v5c0 4.4-3 8.4-7 9.5C8 19.4 5 15.4 5 11V6l7-3z"/><path d="M9.5 11.5l2 2 3.5-3.5"/>',
                'bolt'    => '<path d="M13 2L5 13h5l-1.5 9L19 10h-5l-1-8z"/>',
                'cube'    => '<path d="M12 2l8 4.5v9L12 20l-8-4.5v-9L12 2z"/><path d="M4 6.5l8 4.5 8-4.5M12 11v9"/>',
                'globe'   => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.5 3.8 5.6 3.8 9S14.5 18.5 12 21c-2.5-2.5-3.8-5.6-3.8-9S9.5 5.5 12 3z"/>',
                'medal'   => '<circle cx="12" cy="9" r="5.5"/><path d="M8.5 13.5L7 21l5-2.5L17 21l-1.5-7.5"/>',
                'wrench'  => '<path d="M14.5 6.5a4.5 4.5 0 1 0-6 6L3 18l3 3 5.5-5.5a4.5 4.5 0 0 0 6-6L14 13l-3-3 3.5-3.5z"/>',
                'truck'   => '<path d="M2 6h11v9H2zM13 9h4l4 4v2h-8z"/><circle cx="6" cy="17.5" r="1.8"/><circle cx="16.5" cy="17.5" r="1.8"/>',
                'search'  => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="M15.5 15.5L21 21"/>',
                'sparkle' => '<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3z"/><path d="M19 15l.9 2.1L22 18l-2.1.9L19 21l-.9-2.1L16 18l2.1-.9L19 15z"/>',
                'phone'   => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/>',
                'mail'    => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
                'pin'     => '<path d="M12 21s-7-6.2-7-11a7 7 0 1 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
                'clock'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
                'check'   => '<circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.5 2.5L16 9.5"/>',
                'leaf'    => '<path d="M20 4C10 4 5 9 5 15v5"/><path d="M20 4c0 9-5 13-11 13H5c0-7 6-13 15-13z"/>',
                'drop'    => '<path d="M12 3s6 6.5 6 11a6 6 0 1 1-12 0c0-4.5 6-11 6-11z"/>',
                'gear'    => '<circle cx="12" cy="12" r="3"/><path d="M12 2l1.2 2.8 3-.6.6 3L19.6 9l1.9 2.4-2 2.3.9 3-3 .8-.4 3-3-.5-1.8 2.5-2.6-1.6-2.8 1.3-1.3-2.8-3-.4.3-3-2.6-1.6 1.8-2.4L2 11.4l2.7-1.5-.3-3 3 .2L9.1 4 12 2z" opacity=".55"/>',
        );
        $d = $paths[ $slug ] ?? $paths['check'];
        return '<svg class="dzb-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $d . '</svg>';
}

/**
 * The icon picker options shared by card-style blocks.
 *
 * @return array<int, array{value:string,label:string}>
 */
function dzb_icon_options(): array {
        $labels = array(
                'shield'  => __( 'Shield — reliability', 'dazhen-blocks' ),
                'bolt'    => __( 'Bolt — speed / power', 'dazhen-blocks' ),
                'cube'    => __( 'Cube — product / OEM', 'dazhen-blocks' ),
                'globe'   => __( 'Globe — export / worldwide', 'dazhen-blocks' ),
                'medal'   => __( 'Medal — certification', 'dazhen-blocks' ),
                'wrench'  => __( 'Wrench — engineering', 'dazhen-blocks' ),
                'truck'   => __( 'Truck — logistics', 'dazhen-blocks' ),
                'search'  => __( 'Magnifier — inspection', 'dazhen-blocks' ),
                'sparkle' => __( 'Sparkle — innovation', 'dazhen-blocks' ),
                'drop'    => __( 'Drop — water', 'dazhen-blocks' ),
                'leaf'    => __( 'Leaf — eco', 'dazhen-blocks' ),
                'gear'    => __( 'Gear — manufacturing', 'dazhen-blocks' ),
        );
        $out = array();
        foreach ( $labels as $value => $label ) {
                $out[] = array( 'value' => $value, 'label' => $label );
        }
        return $out;
}

/* ---------------------------------------------------------------------
 * Section shell — the shared anatomy
 * ------------------------------------------------------------------- */

/**
 * Zone/pad → CSS classes for a block instance.
 */
function dzb_zone_classes( array $attrs, string $extra = '' ): string {
        $zone = $attrs['zone'] ?? 'b';
        $pad  = $attrs['pad'] ?? 'default';

        $classes = 'dzb';
        if ( 'a' === $zone ) {
                $classes .= ' zone-a';
        } elseif ( 'alt' === $zone ) {
                $classes .= ' zone-b zone-b--alt';
        } else {
                $classes .= ' zone-b';
        }
        if ( 'compact' === $pad ) {
                $classes .= ' section section--pad-compact';
        } elseif ( 'spacious' === $pad ) {
                $classes .= ' section section--pad-spacious';
        } else {
                $classes .= ' section';
        }
        if ( '' !== $extra ) {
                $classes .= ' ' . $extra;
        }
        return $classes;
}

/**
 * The shared section heading (eyebrow + h2 + intro).
 */
function dzb_head_html( array $attrs, string $context = 'b' ): string {
        $eyebrow = dzb_text( $attrs['eyebrow'] ?? '' );
        $title   = dzb_text( $attrs['title'] ?? '' );
        $sub     = dzb_text( $attrs['sub'] ?? '' );

        if ( '' === $eyebrow && '' === $title && '' === $sub ) {
                return '';
        }

        $eyebrow_class = 'a' === $context ? 'eyebrow eyebrow--a' : 'eyebrow eyebrow--b';
        $html  = '<header class="section-head dzb-head">';
        $html .= $eyebrow ? '<p class="' . esc_attr( $eyebrow_class ) . '">' . $eyebrow . '</p>' : '';
        $html .= $title ? '<h2 class="section-head__title">' . $title . '</h2>' : '';
        $html .= $sub ? '<p class="section-head__sub">' . $sub . '</p>' : '';
        $html .= '</header>';
        return $html;
}

/**
 * Wrap a block body in the shared section shell.
 *
 * @param string      $name  Block name (for the data-dzb trace attribute).
 * @param array       $attrs Block attributes (zone/pad/eyebrow/title/sub used here).
 * @param string      $body  Inner body HTML (cards, grids, forms…).
 * @param string      $extra Extra classes on the <section>.
 * @param string|null $inner Heading override: a string (even '') replaces the
 *                           shared section head — pass '' to suppress it, null
 *                           (default) to render it from $attrs.
 */
function dzb_shell( string $name, array $attrs, string $body, string $extra = '', ?string $inner = null ): string {
        $head = null !== $inner ? $inner : dzb_head_html( $attrs, $attrs['zone'] ?? 'b' );
        return sprintf(
                '<section class="%1$s" data-dzb="%2$s"><div class="container">%3$s%4$s</div></section>',
                esc_attr( dzb_zone_classes( $attrs, $extra ) ),
                esc_attr( $name ),
                $head,
                $body
        );
}

/**
 * A grid wrapper (theme + standalone both style .grid / .grid--N).
 */
function dzb_grid_open( $columns, string $extra = '' ): string {
        $columns = max( 1, min( 4, (int) $columns ) );
        $class   = 'grid grid--' . $columns . ( '' !== $extra ? ' ' . $extra : '' );
        return '<div class="' . esc_attr( $class ) . '">';
}

/**
 * A themed button (amber CTA / ghost / outline-teal).
 *
 * With the Dazhen theme active and no URL set, "cta" buttons open the RFQ
 * inquiry popup (main.js binds every .dz-rfq-open). On other themes the
 * button is a plain link to whatever URL is given (or no-op).
 *
 * @param string $label   Button label.
 * @param string $url     Target URL (may be empty).
 * @param string $product Pre-filled product name for the inquiry popup.
 * @param string $style   cta | ghost | outline.
 */
function dzb_button_html( string $label, string $url, string $product = '', string $style = 'cta' ): string {
        $label = dzb_text( $label );
        if ( '' === $label ) {
                return '';
        }

        $theme = dazhen_blocks_theme_active();

        $classes = 'cta' === $style
                ? ( $theme ? 'btn btn--cta' : 'dzb-btn dzb-btn--cta' )
                : ( $theme ? 'btn btn--ghost' : 'dzb-btn dzb-btn--ghost' );
        if ( 'outline' === $style ) {
                $classes = $theme ? 'btn btn--outline-teal' : 'dzb-btn dzb-btn--outline';
        }

        if ( '' === $url && $theme && in_array( $style, array( 'cta', 'outline' ), true ) ) {
                $url   = '#dz-rfq';
                $class = $classes . ' dz-rfq-open';
                $data  = '' !== $product ? ' data-dz-product="' . esc_attr( $product ) . '"' : '';
                return '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '"' . $data . '>' . $label . '</a>';
        }

        $url = '' !== $url ? dzb_url( $url ) : '#';
        return '<a class="' . esc_attr( $classes ) . '" href="' . $url . '">' . $label . '</a>';
}

/* ---------------------------------------------------------------------
 * Renderer — dispatch specs to callbacks
 * ------------------------------------------------------------------- */

/**
 * Render one Dazhen block by name + attributes.
 */
function dzb_render_block( string $name, array $attributes, string $content = '' ): string {
        $spec = dzb_spec( $name );
        if ( null === $spec || ! isset( $spec['render'] ) || ! is_callable( $spec['render'] ) ) {
                return '';
        }
        $attrs = dzb_attrs( $name, $attributes );
        $html  = (string) call_user_func( $spec['render'], $attrs, $content );

        /**
         * Filter any rendered Dazhen block.
         */
        return (string) apply_filters( 'dzb_render_block', $html, $name, $attrs );
}

/**
 * Register every spec as a server-rendered Gutenberg block.
 */
function dzb_register_blocks(): void {
        if ( ! function_exists( 'register_block_type' ) ) {
                return;
        }
        foreach ( dzb_specs() as $name => $spec ) {
                register_block_type( $name, array(
                        'api_version'     => 3,
                        'attributes'      => $spec['attributes'],
                        'render_callback' => static function ( $attributes, $content = '' ) use ( $name ) {
                                return dzb_render_block( $name, (array) $attributes, (string) $content );
                        },
                        'supports'        => $spec['supports'],
                ) );
        }
}
add_action( 'init', 'dzb_register_blocks', 20 );

/* ---------------------------------------------------------------------
 * Serialization — emit our own blocks from PHP (layouts / patterns)
 * ------------------------------------------------------------------- */

/**
 * Serialize one block instance to Gutenberg markup.
 *
 * Self-closing form when there is no inner content (all dynamic blocks),
 * classic opener/closer around $inner otherwise (e.g. the section block).
 */
function dzb_block( string $name, array $attributes = array(), string $inner = '' ): string {
        /*
         * UNESCAPED_UNICODE / UNESCAPED_SLASHES on purpose: the fewer backslash
         * escapes the markup carries, the less any content filter can mangle
         * (newlines still escape as \n — required for single-line comments).
         */
        $json = empty( $attributes ) ? '' : wp_json_encode( $attributes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
        if ( '' === $inner ) {
                return '<!-- wp:' . $name . ( $json ? ' ' . $json : '' ) . ' /-->';
        }
        return '<!-- wp:' . $name . ( $json ? ' ' . $json : '' ) . ' -->' . "\n" . $inner . "\n" . '<!-- /wp:' . $name . ' -->';
}

/**
 * Block name shortcut.
 */
function dzb_name( string $short ): string {
        return 'dazhen-blocks/' . $short;
}
