<?php
/**
 * The Kabel Blocks engine — our own block interpreter.
 *
 * Every Kabel block is declared ONCE as a spec (data), and this engine does
 * the rest on both sides of the fence:
 *
 *   Editor (JS)      assets/js/blocks.js (v3, JSX-built) is the editor bundle: it reads
 *                    the specs injected by inc/assets.php and registers every
 *                    block with auto-built sidebar controls. Adding a block
 *                    never touches the JS again.
 *   Front end (PHP)  kbl_render_block() looks the spec up and dispatches to
 *                    the block's render callback. Every block shares the same
 *                    section anatomy (zone / padding / heading), so the whole
 *                    kit behaves like one coherent product.
 *
 * Spec shape (see kbl_specs() consumers):
 *   name, title, description, icon, keywords, category, supports, inner,
 *   attributes (Gutenberg schema incl. defaults), panels (sidebar UI),
 *   render (PHP callback, engine-side only).
 *
 * @package Kabel_Blocks
 */

defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------
 * Registry
 * ------------------------------------------------------------------- */

/**
 * The shared section anatomy every layout block understands.
 * Blocks merge this into their attributes and panels.
 */
function kbl_common_attrs(): array {
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
function kbl_common_panels(): array {
        return array(
                array(
                        'title'       => __( 'Section heading', 'kabel-blocks' ),
                        'initialOpen' => true,
                        'fields'      => array(
                                array( 'attr' => 'eyebrow', 'label' => __( 'Eyebrow (small label above the title)', 'kabel-blocks' ), 'type' => 'text' ),
                                array( 'attr' => 'title', 'label' => __( 'Title', 'kabel-blocks' ), 'type' => 'textarea' ),
                                array( 'attr' => 'sub', 'label' => __( 'Intro line', 'kabel-blocks' ), 'type' => 'textarea' ),
                        ),
                ),
                array(
                        'title'       => __( 'Section look', 'kabel-blocks' ),
                        'initialOpen' => false,
                        'fields'      => array(
                                array(
                                        'attr'    => 'zone',
                                        'label'   => __( 'Background', 'kabel-blocks' ),
                                        'type'    => 'select',
                                        'options' => array(
                                                array( 'value' => 'a', 'label' => __( 'Deep sea (dark)', 'kabel-blocks' ) ),
                                                array( 'value' => 'b', 'label' => __( 'Paper (light)', 'kabel-blocks' ) ),
                                                array( 'value' => 'alt', 'label' => __( 'Paper — alternate tint', 'kabel-blocks' ) ),
                                        ),
                                ),
                                array(
                                        'attr'    => 'pad',
                                        'label'   => __( 'Vertical padding', 'kabel-blocks' ),
                                        'type'    => 'select',
                                        'options' => array(
                                                array( 'value' => 'compact', 'label' => __( 'Compact', 'kabel-blocks' ) ),
                                                array( 'value' => 'default', 'label' => __( 'Default', 'kabel-blocks' ) ),
                                                array( 'value' => 'spacious', 'label' => __( 'Spacious', 'kabel-blocks' ) ),
                                        ),
                                ),
                        ),
                ),
        );
}

/**
 * Just the "Section look" panel (the tail of kbl_common_panels()).
 */
function kbl_common_panels_tail(): array {
        return array_slice( kbl_common_panels(), 1 );
}

/**
 * All block specs, keyed by block name.
 *
 * Specs are plain data: the editor runtime builds its UI from them and the
 * renderer dispatches on them. New blocks = one entry + one render callback.
 */
function kbl_specs(): array {
        static $specs = null;
        if ( null !== $specs ) {
                return $specs;
        }

        $specs = array();

        $register = static function ( array $spec ) use ( &$specs ): void {
                $spec['category'] = $spec['category'] ?? 'kabel-blocks';
                $spec['supports'] = $spec['supports'] ?? array( 'html' => false );
                $spec['keywords'] = $spec['keywords'] ?? array();
                $spec['panels']   = $spec['panels'] ?? array();
                $specs[ $spec['name'] ] = $spec;
        };

        require_once KABEL_BLOCKS_DIR . 'inc/blocks-content.php'; // hero … milestones.
        require_once KABEL_BLOCKS_DIR . 'inc/blocks-proof.php';   // gallery … faq.
        require_once KABEL_BLOCKS_DIR . 'inc/blocks-convert.php'; // products … section.

        /**
         * Filter the full spec registry (add / remove / restyle blocks).
         */
        $specs = (array) apply_filters( 'kbl_specs', $specs );

        return $specs;
}

/**
 * One spec by name (or null).
 */
function kbl_spec( string $name ): ?array {
        $specs = kbl_specs();
        return $specs[ $name ] ?? null;
}

/* ---------------------------------------------------------------------
 * Attribute helpers
 * ------------------------------------------------------------------- */

/**
 * Merge user attributes over the spec defaults (type-coerced).
 */
function kbl_attrs( string $name, array $attributes ): array {
        $spec = kbl_spec( $name );
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
        return (array) apply_filters( 'kbl_attrs', $merged, $name, $attributes );
}

/**
 * Escaped plain text.
 */
function kbl_text( $value ): string {
        return esc_html( trim( (string) $value ) );
}

/**
 * Multi-line textarea → array of trimmed non-empty lines.
 *
 * @return string[]
 */
function kbl_lines( $value ): array {
        /* v7: RichText stores line breaks as <br> (or <li>…) — split on all. */
        $raw   = is_array( $value ) ? implode( "\n", $value ) : (string) $value;
        $lines = preg_split( '/\r?\n|<br\s*\/?>|<\/li>/i', $raw );
        $out   = array();
        foreach ( $lines as $line ) {
                $line = trim( wp_strip_all_tags( (string) $line ) );
                if ( '' !== $line ) {
                        $out[] = $line;
                }
        }
        return $out;
}

/**
 * A kses'ed rich-text value (RichText "rich" fields).
 *
 * Accepts both storage eras: legacy plain text with "\n" (seeded pages) and
 * v7 RichText HTML with <br>/<strong>/<em>. Line breaks survive either way.
 */
function kbl_rich( $value ): string {
        $value = (string) $value;
        if ( '' === $value ) {
                return '';
        }
        if ( false === strpos( $value, '<' ) ) {
                /* Legacy plain text: escape, then visualise the line breaks. */
                return str_replace( array( "\r\n", "\n", "\r" ), '<br>', esc_html( $value ) );
        }
        return wp_kses( $value, array(
                'br'     => array(),
                'p'      => array(),
                'strong' => array(),
                'em'     => array(),
                'b'      => array(),
                'i'      => array(),
                'span'   => array(),
        ) );
}

/**
 * Repeater attribute → array of item arrays with the given keys defaulted.
 *
 * @param string[] $keys Item keys to keep (in order).
 * @return array<int, array<string,string>>
 */
function kbl_items( $value, array $keys ): array {
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
function kbl_url( $value ): string {
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
function kbl_media_src( $id, string $size = 'large' ): string {
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
function kbl_img( $id, string $size = 'large', string $class = '' ): string {
        $id  = (int) $id;
        $alt = $id ? (string) get_post_field( 'post_title', $id ) : '';
        if ( $id < 1 ) {
                return '<span class="kbl-ph ' . esc_attr( $class ) . '" aria-hidden="true"></span>';
        }
        $html = wp_get_attachment_image( $id, $size, false, array( 'class' => $class, 'alt' => $alt ) );
        if ( ! $html ) {
                $src  = kbl_media_src( $id );
                $html = $src
                        ? '<img class="' . esc_attr( $class ) . '" src="' . $src . '" alt="' . esc_attr( $alt ) . '" loading="lazy" />'
                        : '<span class="kbl-ph ' . esc_attr( $class ) . '" aria-hidden="true"></span>';
        }
        return $html;
}

/* ---------------------------------------------------------------------
 * Inline icon set (stroke, currentColor)
 * ------------------------------------------------------------------- */

/**
 * Inline SVG icon by slug (used as default card icons etc.).
 */
function kbl_icon( string $slug ): string {
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
        return '<svg class="kbl-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $d . '</svg>';
}

/**
 * The icon picker options shared by card-style blocks.
 *
 * @return array<int, array{value:string,label:string}>
 */
function kbl_icon_options(): array {
        $labels = array(
                'shield'  => __( 'Shield — reliability', 'kabel-blocks' ),
                'bolt'    => __( 'Bolt — speed / power', 'kabel-blocks' ),
                'cube'    => __( 'Cube — product / OEM', 'kabel-blocks' ),
                'globe'   => __( 'Globe — export / worldwide', 'kabel-blocks' ),
                'medal'   => __( 'Medal — certification', 'kabel-blocks' ),
                'wrench'  => __( 'Wrench — engineering', 'kabel-blocks' ),
                'truck'   => __( 'Truck — logistics', 'kabel-blocks' ),
                'search'  => __( 'Magnifier — inspection', 'kabel-blocks' ),
                'sparkle' => __( 'Sparkle — innovation', 'kabel-blocks' ),
                'drop'    => __( 'Drop — water', 'kabel-blocks' ),
                'leaf'    => __( 'Leaf — eco', 'kabel-blocks' ),
                'gear'    => __( 'Gear — manufacturing', 'kabel-blocks' ),
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
function kbl_zone_classes( array $attrs, string $extra = '' ): string {
        $zone = $attrs['zone'] ?? 'b';
        $pad  = $attrs['pad'] ?? 'default';

        /*
         * "dzb" is a kept-alive alias class: a batch of front-end rules
         * (.dzb.zone-a …, .dzb .chip-row …) still target it.
         */
        $classes = 'kbl dzb';
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
function kbl_head_html( array $attrs, string $context = 'b' ): string {
        $eyebrow = kbl_text( $attrs['eyebrow'] ?? '' );
        $title   = kbl_text( $attrs['title'] ?? '' );
        $sub     = kbl_text( $attrs['sub'] ?? '' );

        if ( '' === $eyebrow && '' === $title && '' === $sub ) {
                return '';
        }

        $eyebrow_class = 'a' === $context ? 'eyebrow eyebrow--a' : 'eyebrow eyebrow--b';
        $html  = '<header class="section-head kbl-head">';
        $html .= $eyebrow ? '<p class="' . esc_attr( $eyebrow_class ) . '">' . $eyebrow . '</p>' : '';
        $html .= $title ? '<h2 class="section-head__title">' . $title . '</h2>' : '';
        $html .= $sub ? '<p class="section-head__sub">' . $sub . '</p>' : '';
        $html .= '</header>';
        return $html;
}

/**
 * Wrap a block body in the shared section shell.
 *
 * @param string      $name  Block name (for the data-kbl trace attribute).
 * @param array       $attrs Block attributes (zone/pad/eyebrow/title/sub used here).
 * @param string      $body  Inner body HTML (cards, grids, forms…).
 * @param string      $extra Extra classes on the <section>.
 * @param string|null $inner Heading override: a string (even '') replaces the
 *                           shared section head — pass '' to suppress it, null
 *                           (default) to render it from $attrs.
 */
function kbl_shell( string $name, array $attrs, string $body, string $extra = '', ?string $inner = null, string $style = '' ): string {
        $head = null !== $inner ? $inner : kbl_head_html( $attrs, $attrs['zone'] ?? 'b' );
        return sprintf(
                '<section class="%1$s"%2$s data-kbl="%3$s"><div class="container">%4$s%5$s</div></section>',
                esc_attr( kbl_zone_classes( $attrs, $extra ) ),
                '' !== $style ? ' style="' . esc_attr( $style ) . '"' : '',
                esc_attr( $name ),
                $head,
                $body
        );
}

/**
 * A grid wrapper (theme + standalone both style .grid / .grid--N).
 */
function kbl_grid_open( $columns, string $extra = '' ): string {
        $columns = max( 1, min( 4, (int) $columns ) );
        $class   = 'grid grid--' . $columns . ( '' !== $extra ? ' ' . $extra : '' );
        return '<div class="' . esc_attr( $class ) . '">';
}

/**
 * Is the Kabel theme (the companion theme) active?
 *
 * Lives in the engine — not in the plugin bootstrap — because the render
 * layer needs it: kbl_button_html() and the products/inquiry render
 * callbacks call it, and the Kabel theme's plugin-less render fallback
 * (inc/blocks-fallback.php in the theme) loads ONLY this file. Keeping it
 * here makes the engine self-contained for front-end rendering and lets the
 * real plugin be activated in a request where the fallback already booted
 * (no function-name collision).
 */
function kabel_blocks_theme_active(): bool {
        $theme = wp_get_theme();
        return 'kabel' === $theme->get_template() || 'kabel' === $theme->get( 'TextDomain' );
}

/**
 * A themed button (amber CTA / ghost / outline-teal).
 *
 * With the Kabel theme active and no URL set, "cta" buttons open the RFQ
 * inquiry popup (main.js binds every .kb-rfq-open). On other themes the
 * button is a plain link to whatever URL is given (or no-op).
 *
 * @param string $label   Button label.
 * @param string $url     Target URL (may be empty).
 * @param string $product Pre-filled product name for the inquiry popup.
 * @param string $style   cta | ghost | outline.
 */
function kbl_button_html( string $label, string $url, string $product = '', string $style = 'cta' ): string {
        $label = kbl_text( $label );
        if ( '' === $label ) {
                return '';
        }

        $theme = kabel_blocks_theme_active();

        $classes = 'cta' === $style
                ? ( $theme ? 'btn btn--cta' : 'kbl-btn kbl-btn--cta' )
                : ( $theme ? 'btn btn--ghost' : 'kbl-btn kbl-btn--ghost' );
        if ( 'outline' === $style ) {
                $classes = $theme ? 'btn btn--outline-teal' : 'kbl-btn kbl-btn--outline';
        }

        if ( '' === $url && $theme && in_array( $style, array( 'cta', 'outline' ), true ) ) {
                $url   = '#kb-rfq';
                $class = $classes . ' kb-rfq-open';
                $data  = '' !== $product ? ' data-kb-product="' . esc_attr( $product ) . '"' : '';
                return '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '"' . $data . '>' . $label . '</a>';
        }

        $url = '' !== $url ? kbl_url( $url ) : '#';
        return '<a class="' . esc_attr( $classes ) . '" href="' . $url . '">' . $label . '</a>';
}

/* ---------------------------------------------------------------------
 * Renderer — dispatch specs to callbacks
 * ------------------------------------------------------------------- */

/**
 * Render one Kabel block by name + attributes.
 */
function kbl_render_block( string $name, array $attributes, string $content = '' ): string {
        $spec = kbl_spec( $name );
        if ( null === $spec || ! isset( $spec['render'] ) || ! is_callable( $spec['render'] ) ) {
                return '';
        }
        $attrs = kbl_attrs( $name, $attributes );
        $html  = (string) call_user_func( $spec['render'], $attrs, $content );

        /**
         * Filter any rendered Kabel block.
         */
        return (string) apply_filters( 'kbl_render_block', $html, $name, $attrs );
}

/**
 * Register every spec as a server-rendered Gutenberg block.
 */
function kbl_register_blocks(): void {
        if ( ! function_exists( 'register_block_type' ) ) {
                return;
        }
        foreach ( kbl_specs() as $name => $spec ) {
                register_block_type( $name, array(
                        'api_version'          => 3,
                        'attributes'           => $spec['attributes'],
                        'render_callback'      => static function ( $attributes, $content = '' ) use ( $name ) {
                                return kbl_render_block( $name, (array) $attributes, (string) $content );
                        },
                        'supports'             => $spec['supports'],
                        /* v7: canvas styles — the editor iframe loads this handle so
                           the inline editing view looks like the front-end. (Front-end
                           styles stay conditionally enqueued in assets.php — the theme
                           supplies them when active, the standalone skin otherwise.) */
                        'editor_style_handles' => array( 'kabel-blocks-legacy' ),
                ) );
        }
}
add_action( 'init', 'kbl_register_blocks', 20 );

/* ---------------------------------------------------------------------
 * Serialization — emit our own blocks from PHP (layouts / patterns)
 * ------------------------------------------------------------------- */

/**
 * Serialize one block instance to Gutenberg markup.
 *
 * Self-closing form when there is no inner content (all dynamic blocks),
 * classic opener/closer around $inner otherwise (e.g. the section block).
 */
function kbl_block( string $name, array $attributes = array(), string $inner = '' ): string {
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
function kbl_name( string $short ): string {
        return 'kabel-blocks/' . $short;
}
