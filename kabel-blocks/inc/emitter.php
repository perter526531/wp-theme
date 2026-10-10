<?php
/**
 * Kabel Blocks v3 — the PHP emitter.
 *
 * Emits kbl/* block markup for layouts, patterns and the one-click site
 * bootstrap. Every function here is the byte-exact twin of the block save()
 * functions in src/blocks/*.js (bundled to assets/js/blocks.js):
 *
 *   - attribute order inside tags: class, style, id (useBlockProps merges
 *     className first when it is passed first — our save() always does);
 *   - style declarations serialize as `prop:value;` per declaration
 *     (React's style serialization), empty values skipped;
 *   - rich text is escaped for &, < and > only (RichText serialization);
 *   - comment attributes carry only non-sourced, non-default values;
 *   - inner blocks are joined with "\n\n", no whitespace inside wrappers.
 *
 * Changing a save() in JS REQUIRES the twin change here (and vice versa) —
 * otherwise the editor flags block validation warnings.
 *
 * @package Kabel_Blocks
 */

defined( 'ABSPATH' ) || exit;

require_once KABEL_BLOCKS_DIR . 'inc/icons.php';

/* =====================================================================
 * Primitives
 * =================================================================== */

/**
 * Serialize [[prop, value]] pairs to a React-style style attribute value.
 * JS twin: toStyle() + React serialization (trailing ; per declaration).
 */
function kbl3_style( array $pairs ): string {
        $out = '';
        foreach ( $pairs as $pair ) {
                $prop = (string) $pair[0];
                $val  = $pair[1];
                if ( $val === null || $val === false || $val === '' ) {
                        continue;
                }
                $out .= $prop . ':' . $val . ';';
        }
        return $out;
}

/**
 * Escape a rich-text value for markup output.
 *
 * Rich-text values are HTML by contract (RichText serializes <em>/<strong>/
 * <br> formatting as real tags), so tags must pass through untouched. Only a
 * bare ampersand — one not already part of an entity — gets escaped, exactly
 * like React/Gutenberg text-node serialization.
 */
function kbl3_rich( string $s ): string {
        return preg_replace(
                '/&(?!(?:[a-zA-Z][a-zA-Z0-9]*|#[0-9]+|#x[0-9a-fA-F]+);)/',
                '&amp;',
                $s
        ) ?? $s;
}

/**
 * Build a class attribute from fragments.
 */
function kbl3_cx( ...$parts ): string {
        $out = array();
        foreach ( $parts as $p ) {
                if ( is_string( $p ) && '' !== $p ) {
                        $out[] = $p;
                }
        }
        return implode( ' ', $out );
}

/**
 * Serialize one block with inner content.
 *
 * @param string $name  Block name (kbl/heading).
 * @param array  $attrs Comment attributes (already stripped of defaults).
 * @param string $inner Inner HTML (children joined with "\n\n").
 */
function kbl3_block( string $name, array $attrs, string $inner ): string {
        if ( '' === $inner ) {
                return '<!-- wp:' . $name . kbl3_json( $attrs ) . ' /-->';
        }
        return '<!-- wp:' . $name . kbl3_json( $attrs ) . " -->\n" . $inner . "\n<!-- /wp:" . $name . ' -->';
}

/**
 * The comment-attribute JSON blob ('' or ' {...}').
 */
function kbl3_json( array $attrs ): string {
        if ( array() === $attrs ) {
                return '';
        }
        $json = wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        return false === $json ? '' : ' ' . $json;
}

/**
 * Render an icon SVG. JS twin: iconSvg().
 */
function kbl3_icon_svg( string $name ): string {
        $icons = kbl3_icons();
        $paths = $icons[ $name ] ?? $icons['check'];
        $out   = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">';
        foreach ( $paths as $d ) {
                $out .= '<path d="' . esc_attr( $d ) . '"></path>';
        }
        return $out . '</svg>';
}

/**
 * Attachment URL for an id (or '' when unresolvable).
 */
function kbl3_media_url( $id ): string {
        $id = (int) $id;
        if ( $id <= 0 ) {
                return '';
        }
        $url = wp_get_attachment_url( $id );
        return is_string( $url ) ? $url : '';
}

/**
 * Overlay CSS value ("0.45").
 */
function kbl3_overlay_value( $overlay ): string {
        $overlay = ( null === $overlay || '' === $overlay ) ? 45 : (float) $overlay;
        $value   = $overlay / 100;
        $str     = (string) $value;
        // Match JS String(): trailing zero decimals trimmed by PHP float cast already.
        return $str;
}

/* =====================================================================
 * Shared capability helpers (twins of the util.js helpers).
 *
 * Class fragment order inside kbl3_cx() calls: [block classes] -> hover
 * -> anim -> visibility -> spacing. Style pair order inside kbl3_style()
 * calls: [block pairs] -> typography -> hover vars -> anim delay -> spacing.
 * Both orders are part of the block validation contract.
 * =================================================================== */

/**
 * Device-visibility class fragment. JS twin: applyVisibility().
 */
function kbl3_visibility( array $a ): string {
        $c = array();
        if ( ! empty( $a['hideD'] ) ) { $c[] = 'kbl3-hide-d'; }
        if ( ! empty( $a['hideT'] ) ) { $c[] = 'kbl3-hide-t'; }
        if ( ! empty( $a['hideM'] ) ) { $c[] = 'kbl3-hide-m'; }
        return implode( ' ', $c );
}

/**
 * Append the visibility comment-attrs (order: hideD, hideT, hideM).
 */
function kbl3_visibility_attrs( array $a, array &$attrs ): void {
        if ( ! empty( $a['hideD'] ) ) { $attrs['hideD'] = true; }
        if ( ! empty( $a['hideT'] ) ) { $attrs['hideT'] = true; }
        if ( ! empty( $a['hideM'] ) ) { $attrs['hideM'] = true; }
}

/**
 * Margin + padding spacing class fragment. JS twin: applySpacing(). Order:
 * mt, mb, mr, ml, mtM, mbM - always appended after the visibility fragment.
 */
function kbl3_spacing_class( array $a ): string {
        $c = array();
        if ( isset( $a['mt'] ) ) { $c[] = 'kbl3-mt'; }
        if ( isset( $a['mb'] ) ) { $c[] = 'kbl3-mb'; }
        if ( isset( $a['mr'] ) ) { $c[] = 'kbl3-mr'; }
        if ( isset( $a['ml'] ) ) { $c[] = 'kbl3-ml'; }
        if ( isset( $a['mtM'] ) ) { $c[] = 'kbl3-mt-m'; }
        if ( isset( $a['mbM'] ) ) { $c[] = 'kbl3-mb-m'; }
        return implode( ' ', $c );
}

/**
 * Margin + padding style pairs. JS twin: spacingPairs(). Always the LAST
 * pairs in a block style list (after the anim-delay pair). Order: mt, mb,
 * mr, ml, mtM, mbM, then padding-top/right/bottom/left as direct inline
 * styles (inline beats every class padding). Unset keys emit nothing, so
 * blocks without spacing attributes produce zero extra output.
 */
function kbl3_spacing_pairs( array $a ): array {
        $pairs = array();
        if ( isset( $a['mt'] ) ) { $pairs[] = array( '--kbl3-mt', $a['mt'] . 'px' ); }
        if ( isset( $a['mb'] ) ) { $pairs[] = array( '--kbl3-mb', $a['mb'] . 'px' ); }
        if ( isset( $a['mr'] ) ) { $pairs[] = array( '--kbl3-mr', $a['mr'] . 'px' ); }
        if ( isset( $a['ml'] ) ) { $pairs[] = array( '--kbl3-ml', $a['ml'] . 'px' ); }
        if ( isset( $a['mtM'] ) ) { $pairs[] = array( '--kbl3-mt-m', $a['mtM'] . 'px' ); }
        if ( isset( $a['mbM'] ) ) { $pairs[] = array( '--kbl3-mb-m', $a['mbM'] . 'px' ); }
        if ( isset( $a['pt'] ) ) { $pairs[] = array( 'padding-top', $a['pt'] . 'px' ); }
        if ( isset( $a['pr'] ) ) { $pairs[] = array( 'padding-right', $a['pr'] . 'px' ); }
        if ( isset( $a['pb'] ) ) { $pairs[] = array( 'padding-bottom', $a['pb'] . 'px' ); }
        if ( isset( $a['pl'] ) ) { $pairs[] = array( 'padding-left', $a['pl'] . 'px' ); }
        return $pairs;
}

/**
 * Append the spacing comment-attrs (order: mt, mb, mr, ml, mtM, mbM, pt,
 * pr, pb, pl).
 */
function kbl3_spacing_attrs( array $a, array &$attrs ): void {
        foreach ( array( 'mt', 'mb', 'mr', 'ml', 'mtM', 'mbM', 'pt', 'pr', 'pb', 'pl' ) as $k ) {
                if ( isset( $a[ $k ] ) ) { $attrs[ $k ] = (int) $a[ $k ]; }
        }
}

/**
 * Block-level box alignment pairs (max-width + auto margins). JS twin:
 * boxPairs(). Emitted BEFORE kbl3_typography() so an explicit margin in
 * kbl3_spacing_pairs() still overrides the auto margin.
 */
function kbl3_box_pairs( array $a ): array {
        $align = $a['blockAlign'] ?? '';
        return array(
                array( 'max-width', ! empty( $a['contentWidth'] ) ? $a['contentWidth'] . 'px' : null ),
                array( 'margin-left', ( 'center' === $align || 'right' === $align ) ? 'auto' : null ),
                array( 'margin-right', 'center' === $align ? 'auto' : null ),
        );
}

/**
 * Append the box-alignment comment-attrs (contentWidth, blockAlign).
 */
function kbl3_box_attrs( array $a, array &$attrs ): void {
        if ( ! empty( $a['contentWidth'] ) ) { $attrs['contentWidth'] = (int) $a['contentWidth']; }
        if ( ! empty( $a['blockAlign'] ) ) { $attrs['blockAlign'] = $a['blockAlign']; }
}

/**
 * Typography style pairs. JS twin: typographyPairs().
 *
 * $include switches off pairs per block (buttons expose size + weight,
 * stats / icon boxes only a size).
 */
function kbl3_typography( array $a, array $include = array() ): array {
        $fs = $include['fs'] ?? true;
        $fw = $include['fw'] ?? true;
        $lh = $include['lh'] ?? true;
        $ls = $include['ls'] ?? true;
        $tt = $include['tt'] ?? true;
        $pairs = array();
        if ( $fs ) {
                if ( isset( $a['fontSize'] ) ) { $pairs[] = array( '--kbl3-fs', $a['fontSize'] . 'px' ); }
                if ( isset( $a['fontSizeT'] ) ) { $pairs[] = array( '--kbl3-fs-t', $a['fontSizeT'] . 'px' ); }
                if ( isset( $a['fontSizeM'] ) ) { $pairs[] = array( '--kbl3-fs-m', $a['fontSizeM'] . 'px' ); }
        }
        if ( $fw && isset( $a['fontWeight'] ) ) { $pairs[] = array( '--kbl3-fw', (string) $a['fontWeight'] ); }
        if ( $lh && isset( $a['lineHeight'] ) ) { $pairs[] = array( '--kbl3-lh', (string) $a['lineHeight'] ); }
        if ( $ls && isset( $a['letterSpacing'] ) ) { $pairs[] = array( '--kbl3-ls', $a['letterSpacing'] . 'px' ); }
        if ( $tt && ! empty( $a['textTransform'] ) ) { $pairs[] = array( 'text-transform', $a['textTransform'] ); }
        return $pairs;
}

/**
 * Append the typography comment-attrs (order mirrors kbl3_typography()).
 */
function kbl3_typography_attrs( array $a, array &$attrs, array $include = array() ): void {
        $fs = $include['fs'] ?? true;
        $fw = $include['fw'] ?? true;
        $lh = $include['lh'] ?? true;
        $ls = $include['ls'] ?? true;
        $tt = $include['tt'] ?? true;
        if ( $fs ) {
                foreach ( array( 'fontSize', 'fontSizeT', 'fontSizeM' ) as $k ) {
                        if ( isset( $a[ $k ] ) ) { $attrs[ $k ] = (int) $a[ $k ]; }
                }
        }
        if ( $fw && isset( $a['fontWeight'] ) ) { $attrs['fontWeight'] = (int) $a['fontWeight']; }
        if ( $lh && isset( $a['lineHeight'] ) ) { $attrs['lineHeight'] = (float) $a['lineHeight']; }
        if ( $ls && isset( $a['letterSpacing'] ) ) { $attrs['letterSpacing'] = (float) $a['letterSpacing']; }
        if ( $tt && ! empty( $a['textTransform'] ) ) { $attrs['textTransform'] = $a['textTransform']; }
}

/**
 * Entrance-animation class fragment. JS twin: applyAnim().
 */
function kbl3_anim( array $a ): string {
        if ( empty( $a['anim'] ) ) {
                return '';
        }
        return 'kbl3-anim kbl3-anim--' . $a['anim'];
}

/**
 * Animation-delay style pair (only with an animation set and delay > 0).
 * JS twin: animDelayPairs().
 */
function kbl3_anim_delay( array $a ): array {
        if ( empty( $a['anim'] ) || empty( $a['animDelay'] ) ) {
                return array();
        }
        return array( array( '--kbl3-anim-d', (int) $a['animDelay'] . 'ms' ) );
}

/**
 * Linear-gradient value for section/column/icon-box backgrounds.
 * Priority: bgURL image > gradient > solid color. JS twin: gradientValue().
 */
function kbl3_gradient( array $a ): ?string {
        if ( ! empty( $a['bgURL'] ) || empty( $a['gFrom'] ) || empty( $a['gTo'] ) ) {
                return null;
        }
        $angle = isset( $a['gAngle'] ) ? (int) $a['gAngle'] : 135;
        return 'linear-gradient(' . $angle . 'deg, ' . $a['gFrom'] . ', ' . $a['gTo'] . ')';
}

/**
 * Append the gradient comment-attrs (order: gFrom, gTo, gAngle; 135 = default).
 */
function kbl3_gradient_attrs( array $a, array &$attrs ): void {
        if ( ! empty( $a['gFrom'] ) ) { $attrs['gFrom'] = $a['gFrom']; }
        if ( ! empty( $a['gTo'] ) ) { $attrs['gTo'] = $a['gTo']; }
        if ( isset( $a['gAngle'] ) && 135 !== (int) $a['gAngle'] ) { $attrs['gAngle'] = (int) $a['gAngle']; }
}

/**
 * Append the animation comment-attrs (order: anim, animDelay).
 */
function kbl3_anim_attrs( array $a, array &$attrs ): void {
        if ( ! empty( $a['anim'] ) ) { $attrs['anim'] = $a['anim']; }
        if ( ! empty( $a['animDelay'] ) ) { $attrs['animDelay'] = (int) $a['animDelay']; }
}

/**
 * Section divider shapes. viewBox 0 0 1440 90, filled from the bottom edge.
 * JS twin: DIVIDER_PATHS.
 */
function kbl3_divider_paths(): array {
        return array(
                'wave' => 'M0,90 L0,60 C240,20 480,100 720,60 C960,20 1200,100 1440,60 L1440,90 Z',
                'tilt' => 'M0,90 L0,30 L1440,75 L1440,90 Z',
                'arc'  => 'M0,90 L0,45 C360,0 1080,0 1440,45 L1440,90 Z',
        );
}

/**
 * One divider SVG (top or bottom edge of a section). JS twin: dividerSvg().
 * Attribute order is part of the validation contract: class, viewBox,
 * preserveAspectRatio, aria-hidden, focusable, style.
 */
function kbl3_divider( string $pos, string $shape, string $color = '' ): string {
        $paths = kbl3_divider_paths();
        if ( ! isset( $paths[ $shape ] ) ) {
                return '';
        }
        $style = '' !== $color ? ' style="color:' . esc_attr( $color ) . '"' : '';
        return '<svg class="kbl3-divider kbl3-divider--' . $pos . '" viewBox="0 0 1440 90" preserveAspectRatio="none" aria-hidden="true" focusable="false"' . $style . '>'
                . '<path fill="currentColor" d="' . esc_attr( $paths[ $shape ] ) . '"></path>'
                . '</svg>';
}

/* =====================================================================
 * Block builders — kbl/* twins of the JS save() functions
 * =================================================================== */

/**
 * kbl/section.
 *
 * @param array  $a     tone|pad|width|bgURL|bgId|overlay|textColor|padTop|padBottom|minH|vAlign|gFrom|gTo|gAngle|divTop|divBottom|divColor|anim|animDelay|hideD|hideT|hideM|mt|mb|mtM|mbM|anchor.
 * @param string $inner Child markup.
 */
function kbl3_section( array $a = array(), string $inner = '' ): string {
        $tone = $a['tone'] ?? 'dark';
        $pad  = $a['pad'] ?? 'normal';
        $bg   = $a['bgURL'] ?? '';
        $grad = kbl3_gradient( $a );

        $class = kbl3_cx(
                'wp-block-kbl-section',
                'kbl3-section',
                'white' === $tone ? 'kbl3-tone--white' : ( 'navy' === $tone ? 'kbl3-tone--navy' : 'kbl3-tone--dark' ),
                'kbl3-pad--' . $pad,
                'center' === ( $a['vAlign'] ?? '' ) ? 'kbl3-mid' : '',
                $bg ? 'has-bgimg' : '',
                kbl3_anim( $a ),
                kbl3_visibility( $a ),
                kbl3_spacing_class( $a )
        );
        $style = kbl3_style( array_merge( array(
                array( 'background-color', $a['bgColor'] ?? '' ),
                array( 'background-image', $bg ? "url('" . $bg . "')" : $grad ),
                array( '--kbl3-ov', $bg ? kbl3_overlay_value( $a['overlay'] ?? null ) : null ),
                array( 'color', $a['textColor'] ?? '' ),
                array( 'min-height', isset( $a['minH'] ) ? $a['minH'] . 'px' : null ),
                array( 'padding-top', isset( $a['padTop'] ) ? $a['padTop'] . 'px' : null ),
                array( 'padding-bottom', isset( $a['padBottom'] ) ? $a['padBottom'] . 'px' : null ),
        ), kbl3_anim_delay( $a ), kbl3_spacing_pairs( $a ) ) );

        $width   = $a['width'] ?? 'normal';
        $widthpx = $a['widthPx'] ?? null;
        $container = kbl3_cx(
                'kbl3-container',
                isset( $widthpx ) ? 'kbl3-container--custom' : '',
                'narrow' === $width ? 'kbl3-container--narrow' : '',
                'full' === $width ? 'kbl3-container--full' : ''
        );
        $container_style = isset( $widthpx ) ? ' style="--kbl3-cw:' . (int) $widthpx . 'px"' : '';

        $attrs = array();
        if ( 'dark' !== $tone ) { $attrs['tone'] = $tone; }
        if ( 'normal' !== $pad ) { $attrs['pad'] = $pad; }
        if ( 'normal' !== $width ) { $attrs['width'] = $width; }
        if ( isset( $widthpx ) ) { $attrs['widthPx'] = (int) $widthpx; }
        if ( ! empty( $a['bgColor'] ) ) { $attrs['bgColor'] = $a['bgColor']; }
        if ( $bg ) {
                $attrs['bgURL'] = $bg;
                if ( ! empty( $a['bgId'] ) ) { $attrs['bgId'] = (int) $a['bgId']; }
                if ( isset( $a['overlay'] ) && 45 !== (int) $a['overlay'] ) { $attrs['overlay'] = (int) $a['overlay']; }
        }
        if ( ! empty( $a['textColor'] ) ) { $attrs['textColor'] = $a['textColor']; }
        if ( isset( $a['padTop'] ) ) { $attrs['padTop'] = (int) $a['padTop']; }
        if ( isset( $a['padBottom'] ) ) { $attrs['padBottom'] = (int) $a['padBottom']; }
        if ( isset( $a['minH'] ) ) { $attrs['minH'] = (int) $a['minH']; }
        if ( 'center' === ( $a['vAlign'] ?? '' ) ) { $attrs['vAlign'] = 'center'; }
        kbl3_gradient_attrs( $a, $attrs );
        if ( ! empty( $a['divTop'] ) ) { $attrs['divTop'] = $a['divTop']; }
        if ( ! empty( $a['divBottom'] ) ) { $attrs['divBottom'] = $a['divBottom']; }
        if ( ! empty( $a['divColor'] ) ) { $attrs['divColor'] = $a['divColor']; }
        kbl3_anim_attrs( $a, $attrs );
        kbl3_visibility_attrs( $a, $attrs );
        kbl3_spacing_attrs( $a, $attrs );

        $markup = '<section class="' . esc_attr( $class ) . '"' . ( '' !== $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . '>'
                . ( ! empty( $a['divTop'] ) ? kbl3_divider( 'top', (string) $a['divTop'], (string) ( $a['divColor'] ?? '' ) ) : '' )
                . '<div class="' . esc_attr( $container ) . '"' . $container_style . '>'
                . $inner
                . '</div>'
                . ( ! empty( $a['divBottom'] ) ? kbl3_divider( 'bottom', (string) $a['divBottom'], (string) ( $a['divColor'] ?? '' ) ) : '' )
                . '</section>';

        return kbl3_block( 'kbl/section', $attrs, $markup );
}

/**
 * kbl/columns.
 *
 * @param array  $a     gap|vGap|vAlign|collapse|reverse|hideD|hideT|hideM|mt|mb|mtM|mbM.
 * @param string $inner Child markup.
 */
function kbl3_columns( array $a = array(), string $inner = '' ): string {
        $gap   = isset( $a['gap'] ) ? (int) $a['gap'] : 24;
        $v     = $a['vAlign'] ?? 'top';
        $coll  = ! isset( $a['collapse'] ) || $a['collapse'];

        $class = kbl3_cx( 'wp-block-kbl-columns', 'kbl3-columns', 'center' === $v ? 'kbl3-valign--center' : '', 'bottom' === $v ? 'kbl3-valign--bottom' : '', ( ! $coll ) ? 'kbl3-keep-row' : '', ! empty( $a['reverse'] ) ? 'kbl3-reverse' : '', kbl3_visibility( $a ), kbl3_spacing_class( $a ) );
        $style = kbl3_style( array_merge( array(
                array( '--kbl3-gap', $gap . 'px' ),
                array( '--kbl3-vgap', isset( $a['vGap'] ) ? $a['vGap'] . 'px' : null ),
        ), kbl3_spacing_pairs( $a ) ) );

        $attrs = array();
        if ( 24 !== $gap ) { $attrs['gap'] = $gap; }
        if ( 'top' !== $v ) { $attrs['vAlign'] = $v; }
        if ( ! $coll ) { $attrs['collapse'] = false; }
        if ( isset( $a['vGap'] ) ) { $attrs['vGap'] = (int) $a['vGap']; }
        if ( ! empty( $a['reverse'] ) ) { $attrs['reverse'] = true; }
        kbl3_visibility_attrs( $a, $attrs );
        kbl3_spacing_attrs( $a, $attrs );

        $markup = '<div class="' . esc_attr( $class ) . '" style="' . esc_attr( $style ) . '">' . $inner . '</div>';
        return kbl3_block( 'kbl/columns', $attrs, $markup );
}

/**
 * kbl/column.
 *
 * @param array  $a     bgColor|textColor|bgURL|bgId|overlay|padding|radius|shadow|borderWidth|borderColor|borderStyle|contentAlign|width|gFrom|gTo|gAngle|anim|animDelay|hideD|hideT|hideM|mt|mb|mtM|mbM.
 * @param string $inner Child markup.
 */
function kbl3_column( array $a = array(), string $inner = '' ): string {
        $bg    = $a['bgURL'] ?? '';
        $align = $a['contentAlign'] ?? 'left';
        $grad  = kbl3_gradient( $a );

        $class = kbl3_cx( 'wp-block-kbl-column', 'kbl3-col', isset( $a['width'] ) ? 'kbl3-col--w' : '', 'center' === $align ? 'is-center' : '', 'right' === $align ? 'is-right' : '', $bg ? 'has-bgimg' : '', kbl3_anim( $a ), kbl3_visibility( $a ), kbl3_spacing_class( $a ) );
        $style = kbl3_style( array_merge( array(
                array( 'background-color', $grad ? null : ( $a['bgColor'] ?? '' ) ),
                array( 'color', $a['textColor'] ?? '' ),
                array( 'background-image', $bg ? "url('" . $bg . "')" : $grad ),
                array( '--kbl3-ov', $bg ? kbl3_overlay_value( $a['overlay'] ?? null ) : null ),
                array( '--kbl3-w', isset( $a['width'] ) ? (string) $a['width'] : null ),
                array( 'padding', isset( $a['padding'] ) ? $a['padding'] . 'px' : null ),
                array( 'border-radius', ! empty( $a['radius'] ) ? $a['radius'] . 'px' : null ),
                array( 'box-shadow', kbl3_shadow( $a['shadow'] ?? '' ) ),
                array( 'border', ( ! empty( $a['borderColor'] ) && ! empty( $a['borderWidth'] ) ) ? $a['borderWidth'] . 'px ' . ( $a['borderStyle'] ?? 'solid' ) . ' ' . $a['borderColor'] : null ),
        ), kbl3_anim_delay( $a ), kbl3_spacing_pairs( $a ) ) );

        $attrs = array();
        foreach ( array( 'bgColor', 'textColor' ) as $k ) {
                if ( ! empty( $a[ $k ] ) ) { $attrs[ $k ] = $a[ $k ]; }
        }
        if ( $bg ) {
                $attrs['bgURL'] = $bg;
                if ( ! empty( $a['bgId'] ) ) { $attrs['bgId'] = (int) $a['bgId']; }
                if ( isset( $a['overlay'] ) && 45 !== (int) $a['overlay'] ) { $attrs['overlay'] = (int) $a['overlay']; }
        }
        if ( isset( $a['padding'] ) ) { $attrs['padding'] = (int) $a['padding']; }
        if ( ! empty( $a['radius'] ) ) { $attrs['radius'] = (int) $a['radius']; }
        if ( ! empty( $a['shadow'] ) ) { $attrs['shadow'] = $a['shadow']; }
        if ( ! empty( $a['borderWidth'] ) ) { $attrs['borderWidth'] = (int) $a['borderWidth']; }
        if ( ! empty( $a['borderColor'] ) ) { $attrs['borderColor'] = $a['borderColor']; }
        if ( ! empty( $a['borderStyle'] ) ) { $attrs['borderStyle'] = $a['borderStyle']; }
        if ( 'left' !== $align ) { $attrs['contentAlign'] = $align; }
        if ( isset( $a['width'] ) ) {
                $w = (float) $a['width'];
                $attrs['width'] = (int) $w === $w ? (int) $w : $w;
        }
        kbl3_gradient_attrs( $a, $attrs );
        kbl3_anim_attrs( $a, $attrs );
        kbl3_visibility_attrs( $a, $attrs );
        kbl3_spacing_attrs( $a, $attrs );

        $markup = '<div class="' . esc_attr( $class ) . '"' . ( '' !== $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . '>' . $inner . '</div>';
        return kbl3_block( 'kbl/column', $attrs, $markup );
}

/**
 * kbl/heading.
 *
 * @param array $a level|size|align|color|eyebrow|contentWidth|blockAlign|fontSize|fontSizeT|fontSizeM|fontWeight|lineHeight|letterSpacing|textTransform|anim|animDelay|hideD|hideT|hideM|mt|mb|mr|ml|mtM|mbM|pt|pr|pb|pl|text.
 */
function kbl3_heading( array $a = array() ): string {
        $level = isset( $a['level'] ) ? (int) $a['level'] : 2;
        $size  = $a['size'] ?? 'm';
        $tag   = 'h' . max( 1, min( 6, $level ) );

        $class = kbl3_cx( 'wp-block-kbl-heading', 'kbl3-h', 'kbl3-h--' . $size, ! empty( $a['eyebrow'] ) ? 'is-eyebrow' : '', kbl3_anim( $a ), kbl3_visibility( $a ), kbl3_spacing_class( $a ) );
        $style = kbl3_style( array_merge( array(
                array( 'text-align', $a['align'] ?? '' ),
                array( 'color', $a['color'] ?? '' ),
        ), kbl3_box_pairs( $a ), kbl3_typography( $a ), kbl3_spacing_pairs( $a ) ) );

        $attrs = array();
        if ( 2 !== $level ) { $attrs['level'] = $level; }
        if ( 'm' !== $size ) { $attrs['size'] = $size; }
        if ( ! empty( $a['align'] ) ) { $attrs['align'] = $a['align']; }
        if ( ! empty( $a['color'] ) ) { $attrs['color'] = $a['color']; }
        if ( ! empty( $a['eyebrow'] ) ) { $attrs['eyebrow'] = true; }
        kbl3_box_attrs( $a, $attrs );
        kbl3_typography_attrs( $a, $attrs );
        kbl3_anim_attrs( $a, $attrs );
        kbl3_visibility_attrs( $a, $attrs );
        kbl3_spacing_attrs( $a, $attrs );

        $markup = '<' . $tag . ' class="' . esc_attr( $class ) . '"' . ( '' !== $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . '>'
                . kbl3_rich( (string) ( $a['text'] ?? '' ) )
                . '</' . $tag . '>';
        return kbl3_block( 'kbl/heading', $attrs, $markup );
}

/**
 * kbl/text.
 *
 * @param array $a size|align|color|lead|contentWidth|blockAlign|fontSize|fontSizeT|fontSizeM|fontWeight|lineHeight|letterSpacing|textTransform|hideD|hideT|hideM|mt|mb|mr|ml|mtM|mbM|pt|pr|pb|pl|text.
 */
function kbl3_text( array $a = array() ): string {
        $size = $a['size'] ?? 'm';

        $class = kbl3_cx( 'wp-block-kbl-text', 'kbl3-text', 'kbl3-text--' . $size, ! empty( $a['lead'] ) ? 'is-lead' : '', kbl3_visibility( $a ), kbl3_spacing_class( $a ) );
        $style = kbl3_style( array_merge( array(
                array( 'text-align', $a['align'] ?? '' ),
                array( 'color', $a['color'] ?? '' ),
        ), kbl3_box_pairs( $a ), kbl3_typography( $a ), kbl3_spacing_pairs( $a ) ) );

        $attrs = array();
        if ( 'm' !== $size ) { $attrs['size'] = $size; }
        if ( ! empty( $a['align'] ) ) { $attrs['align'] = $a['align']; }
        if ( ! empty( $a['color'] ) ) { $attrs['color'] = $a['color']; }
        if ( ! empty( $a['lead'] ) ) { $attrs['lead'] = true; }
        kbl3_box_attrs( $a, $attrs );
        kbl3_typography_attrs( $a, $attrs );
        kbl3_visibility_attrs( $a, $attrs );
        kbl3_spacing_attrs( $a, $attrs );

        $markup = '<p class="' . esc_attr( $class ) . '"' . ( '' !== $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . '>'
                . kbl3_rich( (string) ( $a['text'] ?? '' ) )
                . '</p>';
        return kbl3_block( 'kbl/text', $attrs, $markup );
}

/**
 * kbl/button.
 *
 * @param array $a text|url|newTab|rfq|rfqProduct|variant|size|radius|padX|padY|borderWidth|borderColor|borderStyle|align|full|bgColor|textColor|typography|hoverBg|hoverText|hoverLift|hideD|hideT|hideM|mt|mb|mtM|mbM.
 */
function kbl3_button( array $a = array() ): string {
        $variant = $a['variant'] ?? 'gold';
        $size    = $a['size'] ?? 'm';
        $rfq     = ! empty( $a['rfq'] );
        $url     = $a['url'] ?? '';

        $class = kbl3_cx( 'wp-block-kbl-button', 'kbl3-btnwrap' );
        $wrap_style = kbl3_style( array( array( 'text-align', $a['align'] ?? '' ) ) );

        $btn_class = kbl3_cx(
                'kbl3-btn',
                'kbl3-btn--' . $variant,
                'kbl3-btn--' . $size,
                ! empty( $a['full'] ) ? 'kbl3-btn--full' : '',
                ( $rfq && '' === $url ) ? 'kb-rfq-open' : '',
                ! empty( $a['hoverLift'] ) ? 'kbl3-hover--lift' : '',
                ! empty( $a['hoverBg'] ) ? 'kbl3-has-hover-bg' : '',
                ! empty( $a['hoverText'] ) ? 'kbl3-has-hover-tx' : '',
                kbl3_visibility( $a ),
                kbl3_spacing_class( $a )
        );
        $btn_style = kbl3_style( array_merge( array(
                array( 'border-radius', ! empty( $a['radius'] ) ? $a['radius'] . 'px' : null ),
                array( '--kbl3-btn-px', isset( $a['padX'] ) ? $a['padX'] . 'px' : null ),
                array( '--kbl3-btn-py', isset( $a['padY'] ) ? $a['padY'] . 'px' : null ),
                array( 'border', ( ! empty( $a['borderColor'] ) && ! empty( $a['borderWidth'] ) ) ? $a['borderWidth'] . 'px ' . ( $a['borderStyle'] ?? 'solid' ) . ' ' . $a['borderColor'] : null ),
                array( 'background-color', $a['bgColor'] ?? '' ),
                array( 'color', $a['textColor'] ?? '' ),
        ), kbl3_typography( $a, array( 'lh' => false, 'ls' => false, 'tt' => false ) ), array(
                array( '--kbl3-h-bg', $a['hoverBg'] ?? '' ),
                array( '--kbl3-h-tx', $a['hoverText'] ?? '' ),
        ), kbl3_spacing_pairs( $a ) ) );

        $attrs = array();
        if ( '' !== $url ) { $attrs['url'] = $url; }
        if ( ! empty( $a['newTab'] ) ) { $attrs['newTab'] = true; }
        if ( $rfq ) { $attrs['rfq'] = true; }
        if ( ! empty( $a['rfqProduct'] ) ) { $attrs['rfqProduct'] = $a['rfqProduct']; }
        if ( 'gold' !== $variant ) { $attrs['variant'] = $variant; }
        if ( 'm' !== $size ) { $attrs['size'] = $size; }
        if ( ! empty( $a['radius'] ) ) { $attrs['radius'] = (int) $a['radius']; }
        if ( isset( $a['padX'] ) ) { $attrs['padX'] = (int) $a['padX']; }
        if ( isset( $a['padY'] ) ) { $attrs['padY'] = (int) $a['padY']; }
        if ( ! empty( $a['borderWidth'] ) ) { $attrs['borderWidth'] = (int) $a['borderWidth']; }
        if ( ! empty( $a['borderColor'] ) ) { $attrs['borderColor'] = $a['borderColor']; }
        if ( ! empty( $a['borderStyle'] ) ) { $attrs['borderStyle'] = $a['borderStyle']; }
        if ( ! empty( $a['align'] ) ) { $attrs['align'] = $a['align']; }
        if ( ! empty( $a['full'] ) ) { $attrs['full'] = true; }
        if ( ! empty( $a['bgColor'] ) ) { $attrs['bgColor'] = $a['bgColor']; }
        if ( ! empty( $a['textColor'] ) ) { $attrs['textColor'] = $a['textColor']; }
        kbl3_typography_attrs( $a, $attrs, array( 'lh' => false, 'ls' => false, 'tt' => false ) );
        if ( ! empty( $a['hoverBg'] ) ) { $attrs['hoverBg'] = $a['hoverBg']; }
        if ( ! empty( $a['hoverText'] ) ) { $attrs['hoverText'] = $a['hoverText']; }
        if ( ! empty( $a['hoverLift'] ) ) { $attrs['hoverLift'] = true; }
        kbl3_visibility_attrs( $a, $attrs );
        kbl3_spacing_attrs( $a, $attrs );

        $link = '';
        if ( '' !== $url ) {
                $link = ' href="' . esc_url( $url ) . '"';
                if ( ! empty( $a['newTab'] ) ) {
                        $link .= ' target="_blank" rel="noopener noreferrer"';
                }
        } elseif ( $rfq ) {
                $link = ' href="#kb-rfq"';
                if ( ! empty( $a['rfqProduct'] ) ) {
                        $link .= ' data-kb-product="' . esc_attr( $a['rfqProduct'] ) . '"';
                }
        }

        $markup = '<div class="' . esc_attr( $class ) . '"' . ( '' !== $wrap_style ? ' style="' . esc_attr( $wrap_style ) . '"' : '' ) . '>'
                . '<a class="' . esc_attr( $btn_class ) . '"' . ( '' !== $btn_style ? ' style="' . esc_attr( $btn_style ) . '"' : '' ) . $link . '>'
                . kbl3_rich( (string) ( $a['text'] ?? '' ) )
                . '</a></div>';
        return kbl3_block( 'kbl/button', $attrs, $markup );
}

/**
 * kbl/button-group.
 *
 * @param array  $a     gap|align|hideD|hideT|hideM|mt|mb|mtM|mbM.
 * @param string $inner Child markup.
 */
function kbl3_button_group( array $a = array(), string $inner = '' ): string {
        $gap   = isset( $a['gap'] ) ? (int) $a['gap'] : 14;
        $align = $a['align'] ?? '';

        $style = kbl3_style( array_merge( array(
                array( '--kbl3-btn-gap', $gap . 'px' ),
                array( 'justify-content', 'center' === $align ? 'center' : ( 'right' === $align ? 'flex-end' : null ) ),
        ), kbl3_spacing_pairs( $a ) ) );

        $attrs = array();
        if ( 14 !== $gap ) { $attrs['gap'] = $gap; }
        if ( '' !== $align ) { $attrs['align'] = $align; }
        kbl3_visibility_attrs( $a, $attrs );
        kbl3_spacing_attrs( $a, $attrs );

        $markup = '<div class="' . esc_attr( kbl3_cx( 'wp-block-kbl-button-group', 'kbl3-btnrow', kbl3_visibility( $a ), kbl3_spacing_class( $a ) ) ) . '" style="' . esc_attr( $style ) . '">' . $inner . '</div>';
        return kbl3_block( 'kbl/button-group', $attrs, $markup );
}

/**
 * kbl/image.
 *
 * @param array $a id|url|alt|caption|width|widthPx|heightPx|ratio|radius|align|linkURL|anim|animDelay|hideD|hideT|hideM|mt|mb|mr|ml|mtM|mbM|pt|pr|pb|pl.
 *
 * ratio: '' (original) | '1-1' | '4-3' | '3-4' | '16-9' | '9-16' | 'custom'.
 * A preset ratio crops the image with aspect-ratio + object-fit: cover;
 * 'custom' uses widthPx/heightPx directly on the <img>.
 */
function kbl3_image( array $a = array() ): string {
        $url = $a['url'] ?? '';
        if ( '' === $url ) {
                return '';
        }
        $align  = $a['align'] ?? '';
        $radius = ! empty( $a['radius'] ) ? (int) $a['radius'] : 0;
        $ratio  = $a['ratio'] ?? '';

        $fig_class = kbl3_cx( 'wp-block-kbl-image', 'kbl3-fig', ! empty( $a['widthPx'] ) ? 'kbl3-fig--px' : '', kbl3_anim( $a ), kbl3_visibility( $a ), kbl3_spacing_class( $a ) );
        $fig_style = kbl3_style( array_merge( array(
                array( 'width', ! empty( $a['widthPx'] ) ? $a['widthPx'] . 'px' : ( ! empty( $a['width'] ) ? $a['width'] . '%' : null ) ),
                array( 'margin-left', ( 'center' === $align || 'right' === $align ) ? 'auto' : null ),
                array( 'margin-right', 'center' === $align ? 'auto' : null ),
        ), kbl3_anim_delay( $a ), kbl3_spacing_pairs( $a ) ) );

        $img_pairs = array( array( 'border-radius', $radius ? $radius . 'px' : null ) );
        if ( 'custom' === $ratio ) {
                $img_pairs[] = array( 'width', ! empty( $a['widthPx'] ) ? $a['widthPx'] . 'px' : null );
                $img_pairs[] = array( 'height', ! empty( $a['heightPx'] ) ? $a['heightPx'] . 'px' : null );
        } elseif ( '' !== $ratio ) {
                $img_pairs[] = array( 'aspect-ratio', str_replace( '-', ' / ', $ratio ) );
                $img_pairs[] = array( 'object-fit', 'cover' );
                $img_pairs[] = array( 'width', '100%' );
        }
        $img_style = kbl3_style( $img_pairs );

        $attrs = array();
        if ( ! empty( $a['id'] ) ) { $attrs['id'] = (int) $a['id']; }
        if ( ! empty( $a['width'] ) && empty( $a['widthPx'] ) ) { $attrs['width'] = (int) $a['width']; }
        if ( ! empty( $a['widthPx'] ) ) { $attrs['widthPx'] = (int) $a['widthPx']; }
        if ( ! empty( $a['heightPx'] ) ) { $attrs['heightPx'] = (int) $a['heightPx']; }
        if ( ! empty( $ratio ) ) { $attrs['ratio'] = $ratio; }
        if ( $radius ) { $attrs['radius'] = $radius; }
        if ( '' !== $align ) { $attrs['align'] = $align; }
        if ( ! empty( $a['linkURL'] ) ) { $attrs['linkURL'] = $a['linkURL']; }
        kbl3_anim_attrs( $a, $attrs );
        kbl3_visibility_attrs( $a, $attrs );
        kbl3_spacing_attrs( $a, $attrs );

        $img = '<img class="kbl3-img" src="' . esc_url( $url ) . '" alt="' . esc_attr( (string) ( $a['alt'] ?? '' ) ) . '"' . ( '' !== $img_style ? ' style="' . esc_attr( $img_style ) . '"' : '') . '>';
        if ( ! empty( $a['linkURL'] ) ) {
                $img = '<a class="kbl3-fig__link" href="' . esc_url( $a['linkURL'] ) . '">' . $img . '</a>';
        }

        $markup = '<figure class="' . esc_attr( $fig_class ) . '"' . ( '' !== $fig_style ? ' style="' . esc_attr( $fig_style ) . '"' : '' ) . '>'
                . $img
                . ( ! empty( $a['caption'] ) ? '<figcaption class="kbl3-figcap">' . kbl3_rich( (string) $a['caption'] ) . '</figcaption>' : '' )
                . '</figure>';
        return kbl3_block( 'kbl/image', $attrs, $markup );
}

/**
 * kbl/icon.
 *
 * @param array $a name|size|color|shape|shapeBg|hideD|hideT|hideM|mt|mb|mtM|mbM.
 */
function kbl3_icon( array $a = array() ): string {
        $name  = $a['name'] ?? 'check';
        $size  = isset( $a['size'] ) ? (int) $a['size'] : 48;
        $shape = $a['shape'] ?? 'none';

        $class = kbl3_cx( 'wp-block-kbl-icon', 'kbl3-icon', 'none' !== $shape ? 'kbl3-icon--' . $shape : '', kbl3_visibility( $a ), kbl3_spacing_class( $a ) );
        $style = kbl3_style( array_merge( array(
                array( '--kbl3-icon-size', $size . 'px' ),
                array( 'color', $a['color'] ?? '' ),
                array( 'background-color', 'none' !== $shape ? ( $a['shapeBg'] ?? '' ) : null ),
        ), kbl3_spacing_pairs( $a ) ) );

        $attrs = array();
        if ( 'check' !== $name ) { $attrs['name'] = $name; }
        if ( 48 !== $size ) { $attrs['size'] = $size; }
        if ( ! empty( $a['color'] ) ) { $attrs['color'] = $a['color']; }
        if ( 'none' !== $shape ) { $attrs['shape'] = $shape; }
        if ( ! empty( $a['shapeBg'] ) ) { $attrs['shapeBg'] = $a['shapeBg']; }
        kbl3_visibility_attrs( $a, $attrs );
        kbl3_spacing_attrs( $a, $attrs );

        $markup = '<span class="' . esc_attr( $class ) . '" style="' . esc_attr( $style ) . '">' . kbl3_icon_svg( $name ) . '</span>';
        return kbl3_block( 'kbl/icon', $attrs, $markup );
}

/**
 * kbl/icon-box.
 *
 * @param array $a iconName|iconColor|title|text|layout|align|iconSize|bgColor|padding|radius|shadow|borderWidth|borderColor|gradient|hoverLift|anim|animDelay|hideD|hideT|hideM|mt|mb|mtM|mbM.
 */
function kbl3_icon_box( array $a = array() ): string {
        $icon  = $a['iconName'] ?? 'check';
        $lay   = $a['layout'] ?? 'v';
        $align = $a['align'] ?? 'left';
        $grad  = kbl3_gradient( $a );

        $class = kbl3_cx(
                'wp-block-kbl-icon-box',
                'kbl3-ibox',
                'h' === $lay ? 'kbl3-ibox--h' : '',
                'hr' === $lay ? 'kbl3-ibox--hr' : '',
                'center' === ( $a['iconValign'] ?? '' ) ? 'kbl3-ibox--mid' : '',
                'center' === $align ? 'is-center' : '',
                ! empty( $a['hoverLift'] ) ? 'kbl3-hover--lift' : '',
                kbl3_anim( $a ),
                kbl3_visibility( $a ),
                kbl3_spacing_class( $a )
        );
        $style = kbl3_style( array_merge( array(
                array( 'background-color', $grad ? null : ( $a['bgColor'] ?? '' ) ),
                array( 'background-image', $grad ),
                array( '--kbl3-ibox-ic', isset( $a['iconSize'] ) ? $a['iconSize'] . 'px' : null ),
                array( 'padding', ( isset( $a['padding'] ) ? $a['padding'] : 24 ) . 'px' ),
                array( 'border-radius', ! empty( $a['radius'] ) ? $a['radius'] . 'px' : null ),
                array( 'box-shadow', kbl3_shadow( $a['shadow'] ?? '' ) ),
                array( 'border', ( ! empty( $a['borderColor'] ) && ! empty( $a['borderWidth'] ) ) ? $a['borderWidth'] . 'px ' . ( $a['borderStyle'] ?? 'solid' ) . ' ' . $a['borderColor'] : null ),
        ), kbl3_typography( $a, array( 'fw' => false, 'lh' => false, 'ls' => false, 'tt' => false ) ), kbl3_anim_delay( $a ), kbl3_spacing_pairs( $a ) ) );
        $icon_style = kbl3_style( array(
                array( 'color', $a['iconColor'] ?? '' ),
                array( 'background-color', $a['iconBg'] ?? '' ),
                array( 'padding', ( ! empty( $a['iconBg'] ) && isset( $a['iconPad'] ) ) ? $a['iconPad'] . 'px' : null ),
                array( 'border-radius', ( ! empty( $a['iconBg'] ) && isset( $a['iconRadius'] ) ) ? $a['iconRadius'] . 'px' : null ),
        ) );
        $icon_class = 'kbl3-icon kbl3-ibox__icon' . ( ! empty( $a['iconBg'] ) ? ' kbl3-ibox__icon--chip' : '' );

        $attrs = array();
        if ( 'check' !== $icon ) { $attrs['iconName'] = $icon; }
        if ( ! empty( $a['iconColor'] ) ) { $attrs['iconColor'] = $a['iconColor']; }
        if ( ! empty( $a['iconBg'] ) ) { $attrs['iconBg'] = $a['iconBg']; }
        if ( ! empty( $a['iconBg'] ) && isset( $a['iconPad'] ) ) { $attrs['iconPad'] = (int) $a['iconPad']; }
        if ( ! empty( $a['iconBg'] ) && isset( $a['iconRadius'] ) ) { $attrs['iconRadius'] = (int) $a['iconRadius']; }
        if ( 'center' === ( $a['iconValign'] ?? '' ) ) { $attrs['iconValign'] = 'center'; }
        if ( 'v' !== $lay ) { $attrs['layout'] = $lay; }
        if ( 'left' !== $align ) { $attrs['align'] = $align; }
        if ( isset( $a['iconSize'] ) ) { $attrs['iconSize'] = (int) $a['iconSize']; }
        if ( ! empty( $a['bgColor'] ) ) { $attrs['bgColor'] = $a['bgColor']; }
        if ( isset( $a['padding'] ) && 24 !== (int) $a['padding'] ) { $attrs['padding'] = (int) $a['padding']; }
        if ( ! empty( $a['radius'] ) ) { $attrs['radius'] = (int) $a['radius']; }
        if ( ! empty( $a['shadow'] ) ) { $attrs['shadow'] = $a['shadow']; }
        if ( ! empty( $a['borderWidth'] ) ) { $attrs['borderWidth'] = (int) $a['borderWidth']; }
        if ( ! empty( $a['borderColor'] ) ) { $attrs['borderColor'] = $a['borderColor']; }
        if ( ! empty( $a['borderStyle'] ) ) { $attrs['borderStyle'] = $a['borderStyle']; }
        kbl3_typography_attrs( $a, $attrs, array( 'fw' => false, 'lh' => false, 'ls' => false, 'tt' => false ) );
        kbl3_gradient_attrs( $a, $attrs );
        if ( ! empty( $a['hoverLift'] ) ) { $attrs['hoverLift'] = true; }
        kbl3_anim_attrs( $a, $attrs );
        kbl3_visibility_attrs( $a, $attrs );
        kbl3_spacing_attrs( $a, $attrs );

        $markup = '<div class="' . esc_attr( $class ) . '" style="' . esc_attr( $style ) . '">'
                . '<span class="' . esc_attr( $icon_class ) . '"' . ( '' !== $icon_style ? ' style="' . esc_attr( $icon_style ) . '"' : '' ) . '>' . kbl3_icon_svg( $icon ) . '</span>'
                . '<div class="kbl3-ibox__body">'
                . '<h4 class="kbl3-ibox__title">' . kbl3_rich( (string) ( $a['title'] ?? '' ) ) . '</h4>'
                . '<p class="kbl3-ibox__text">' . kbl3_rich( (string) ( $a['text'] ?? '' ) ) . '</p>'
                . '</div></div>';
        return kbl3_block( 'kbl/icon-box', $attrs, $markup );
}

/**
 * kbl/list.
 *
 * @param array  $a     marker|color|hideD|hideT|hideM|mt|mb|mtM|mbM.
 * @param array  $items List item strings (plain text or inline HTML).
 */
function kbl3_list( array $a = array(), array $items = array() ): string {
        $marker = $a['marker'] ?? 'check';

        $class = kbl3_cx( 'wp-block-kbl-list', 'kbl3-list', 'kbl3-list--' . $marker, kbl3_visibility( $a ), kbl3_spacing_class( $a ) );
        $style = kbl3_style( array_merge( array(
                array( 'color', $a['color'] ?? '' ),
        ), kbl3_spacing_pairs( $a ) ) );

        $attrs = array();
        if ( 'check' !== $marker ) { $attrs['marker'] = $marker; }
        if ( ! empty( $a['color'] ) ) { $attrs['color'] = $a['color']; }
        kbl3_visibility_attrs( $a, $attrs );
        kbl3_spacing_attrs( $a, $attrs );

        $lis = '';
        foreach ( $items as $item ) {
                $lis .= '<li>' . kbl3_rich( (string) $item ) . '</li>';
        }

        $markup = '<ul class="' . esc_attr( $class ) . '"' . ( '' !== $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . '>' . $lis . '</ul>';
        return kbl3_block( 'kbl/list', $attrs, $markup );
}

/**
 * kbl/spacer. Accepts a height int (legacy) or an attrs array.
 *
 * @param array|int $a height|hideD|hideT|hideM|mt|mb|mtM|mbM (or a legacy height int).
 */
function kbl3_spacer( $a = 48 ): string {
        $arr    = is_array( $a ) ? $a : array();
        $height = is_array( $a ) ? ( $arr['height'] ?? 48 ) : $a;
        $h      = ( null === $height || '' === $height ) ? 48 : (int) $height;
        $class  = kbl3_cx( 'wp-block-kbl-spacer', 'kbl3-spacer', kbl3_visibility( $arr ), kbl3_spacing_class( $arr ) );
        $style  = kbl3_style( array_merge( array(
                array( 'height', $h . 'px' ),
        ), kbl3_spacing_pairs( $arr ) ) );

        $attrs = array();
        if ( 48 !== $h ) { $attrs['height'] = $h; }
        kbl3_visibility_attrs( $arr, $attrs );
        kbl3_spacing_attrs( $arr, $attrs );

        $markup = '<div class="' . esc_attr( $class ) . '" style="' . esc_attr( $style ) . '" aria-hidden="true"></div>';
        return kbl3_block( 'kbl/spacer', $attrs, $markup );
}

/**
 * kbl/separator.
 *
 * @param array $a width|align|color|hideD|hideT|hideM|mt|mb|mtM|mbM.
 */
function kbl3_separator( array $a = array() ): string {
        $width = isset( $a['width'] ) ? (int) $a['width'] : 96;
        $align = $a['align'] ?? 'left';

        $class = kbl3_cx( 'wp-block-kbl-separator', 'kbl3-sep', 'center' === $align ? 'is-center' : '', kbl3_visibility( $a ), kbl3_spacing_class( $a ) );
        $style = kbl3_style( array_merge( array(
                array( 'width', $width . 'px' ),
                array( 'border-color', $a['color'] ?? '' ),
        ), kbl3_spacing_pairs( $a ) ) );

        $attrs = array();
        if ( 96 !== $width ) { $attrs['width'] = $width; }
        if ( 'left' !== $align ) { $attrs['align'] = $align; }
        if ( ! empty( $a['color'] ) ) { $attrs['color'] = $a['color']; }
        kbl3_visibility_attrs( $a, $attrs );
        kbl3_spacing_attrs( $a, $attrs );

        $markup = '<hr class="' . esc_attr( $class ) . '" style="' . esc_attr( $style ) . '">';
        return kbl3_block( 'kbl/separator', $attrs, $markup );
}

/**
 * kbl/stat.
 *
 * @param array $a pre|number|suf|label|align|color|fontSize|fontSizeT|fontSizeM|anim|animDelay|hideD|hideT|hideM|mt|mb|mtM|mbM.
 */
function kbl3_stat( array $a = array() ): string {
        $align = $a['align'] ?? 'center';

        $class = kbl3_cx( 'wp-block-kbl-stat', 'kbl3-stat', 'center' === $align ? 'is-center' : '', kbl3_anim( $a ), kbl3_visibility( $a ), kbl3_spacing_class( $a ) );
        $style = kbl3_style( array_merge( array(
                array( 'color', $a['color'] ?? '' ),
        ), kbl3_typography( $a, array( 'fw' => false, 'lh' => false, 'ls' => false, 'tt' => false ) ), kbl3_spacing_pairs( $a ) ) );

        $attrs = array();
        if ( ! empty( $a['pre'] ) ) { $attrs['pre'] = $a['pre']; }
        if ( ! empty( $a['suf'] ) ) { $attrs['suf'] = $a['suf']; }
        if ( 'center' !== $align ) { $attrs['align'] = $align; }
        if ( ! empty( $a['color'] ) ) { $attrs['color'] = $a['color']; }
        kbl3_typography_attrs( $a, $attrs, array( 'fw' => false, 'lh' => false, 'ls' => false, 'tt' => false ) );
        kbl3_anim_attrs( $a, $attrs );
        kbl3_visibility_attrs( $a, $attrs );
        kbl3_spacing_attrs( $a, $attrs );

        $num = '<span class="kbl3-stat__num">';
        if ( ! empty( $a['pre'] ) ) {
                $num .= '<span class="kbl3-stat__pre">' . kbl3_rich( (string) $a['pre'] ) . '</span>';
        }
        $num .= '<span class="kbl3-stat__numtext">' . kbl3_rich( (string) ( $a['number'] ?? '' ) ) . '</span>';
        if ( ! empty( $a['suf'] ) ) {
                $num .= '<span class="kbl3-stat__suf">' . kbl3_rich( (string) $a['suf'] ) . '</span>';
        }
        $num .= '</span>';

        $markup = '<div class="' . esc_attr( $class ) . '"' . ( '' !== $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . '>'
                . $num
                . '<span class="kbl3-stat__label">' . kbl3_rich( (string) ( $a['label'] ?? '' ) ) . '</span>'
                . '</div>';
        return kbl3_block( 'kbl/stat', $attrs, $markup );
}

/**
 * kbl/accordion.
 *
 * @param array $a q|a|open|bgColor|radius|hideD|hideT|hideM|mt|mb|mtM|mbM.
 */
function kbl3_accordion( array $a = array() ): string {
        $open = ! empty( $a['open'] );

        $class = kbl3_cx( 'wp-block-kbl-accordion', 'kbl3-acc', $open ? 'is-open' : '', kbl3_visibility( $a ), kbl3_spacing_class( $a ) );
        $style = kbl3_style( array_merge( array(
                array( 'background-color', $a['bgColor'] ?? '' ),
                array( 'border-radius', ! empty( $a['radius'] ) ? $a['radius'] . 'px' : null ),
        ), kbl3_spacing_pairs( $a ) ) );

        $attrs = array();
        if ( $open ) { $attrs['open'] = true; }
        if ( ! empty( $a['bgColor'] ) ) { $attrs['bgColor'] = $a['bgColor']; }
        if ( ! empty( $a['radius'] ) ) { $attrs['radius'] = (int) $a['radius']; }
        kbl3_visibility_attrs( $a, $attrs );
        kbl3_spacing_attrs( $a, $attrs );

        $markup = '<details class="' . esc_attr( $class ) . '"' . ( '' !== $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . ( $open ? ' open' : '' ) . '>'
                . '<summary class="kbl3-acc__q">' . kbl3_rich( (string) ( $a['q'] ?? '' ) ) . '</summary>'
                . '<div class="kbl3-acc__a">' . kbl3_rich( (string) ( $a['a'] ?? '' ) ) . '</div>'
                . '</details>';
        return kbl3_block( 'kbl/accordion', $attrs, $markup );
}

/**
 * kbl/inquiry-form (dynamic — self-closing).
 *
 * Root markup comes from kbl3_render_inquiry_form() in inc/dynamic.php
 * (same class/style fragment contract).
 *
 * @param array $a title|sub|email|buttonLabel|hideD|hideT|hideM|mt|mb|mtM|mbM.
 */
function kbl3_inquiry_form( array $a = array() ): string {
        $attrs = array();
        if ( isset( $a['title'] ) && 'CONTACT US' !== $a['title'] ) { $attrs['title'] = $a['title']; }
        if ( isset( $a['sub'] ) && 'Global Service · Local Response' !== $a['sub'] ) { $attrs['sub'] = $a['sub']; }
        if ( ! empty( $a['email'] ) ) { $attrs['email'] = $a['email']; }
        if ( isset( $a['buttonLabel'] ) && 'Send Inquiry' !== $a['buttonLabel'] ) { $attrs['buttonLabel'] = $a['buttonLabel']; }
        kbl3_visibility_attrs( $a, $attrs );
        kbl3_spacing_attrs( $a, $attrs );
        return kbl3_block( 'kbl/inquiry-form', $attrs, '' );
}

/**
 * kbl/post-grid (dynamic — self-closing).
 *
 * Root markup comes from kbl3_render_post_grid() in inc/dynamic.php
 * (same class/style fragment contract).
 *
 * @param array $a postType|columns|count|showExcerpt|btnLabel|btnURL|hideD|hideT|hideM|mt|mb|mtM|mbM.
 */
function kbl3_post_grid( array $a = array() ): string {
        $attrs = array();
        if ( isset( $a['postType'] ) && 'product' !== $a['postType'] ) { $attrs['postType'] = $a['postType']; }
        if ( isset( $a['columns'] ) && 3 !== (int) $a['columns'] ) { $attrs['columns'] = (int) $a['columns']; }
        if ( isset( $a['count'] ) && 6 !== (int) $a['count'] ) { $attrs['count'] = (int) $a['count']; }
        if ( isset( $a['showExcerpt'] ) && false === $a['showExcerpt'] ) { $attrs['showExcerpt'] = false; }
        if ( isset( $a['btnLabel'] ) && 'View All' !== $a['btnLabel'] ) { $attrs['btnLabel'] = $a['btnLabel']; }
        if ( ! empty( $a['btnURL'] ) ) { $attrs['btnURL'] = $a['btnURL']; }
        kbl3_visibility_attrs( $a, $attrs );
        kbl3_spacing_attrs( $a, $attrs );
        return kbl3_block( 'kbl/post-grid', $attrs, '' );
}

/**
 * kbl/logo.
 *
 * @param array $a id|url|alt|width|linkURL|hideD|hideT|hideM|mt|mb|mtM|mbM|anchor.
 */
function kbl3_logo( array $a = array() ): string {
        $url   = isset( $a['url'] ) ? (string) $a['url'] : '';
        $alt   = isset( $a['alt'] ) ? (string) $a['alt'] : '';
        $width = isset( $a['width'] ) ? (int) $a['width'] : 120;
        $anchor = isset( $a['anchor'] ) ? (string) $a['anchor'] : '';

        $class = kbl3_cx( 'wp-block-kbl-logo', 'kbl3-logo', kbl3_visibility( $a ), kbl3_spacing_class( $a ) );
        $style = kbl3_style( array_merge( array(
                array( '--kbl3-logo-w', $width . 'px' ),
        ), kbl3_spacing_pairs( $a ) ) );

        $attrs = array();
        if ( ! empty( $a['id'] ) ) { $attrs['id'] = (int) $a['id']; }
        if ( '' !== $url ) { $attrs['url'] = $url; }
        if ( '' !== $alt ) { $attrs['alt'] = $alt; }
        if ( 120 !== $width ) { $attrs['width'] = $width; }
        if ( ! empty( $a['linkURL'] ) ) { $attrs['linkURL'] = $a['linkURL']; }
        kbl3_visibility_attrs( $a, $attrs );
        kbl3_spacing_attrs( $a, $attrs );

        $img = '';
        if ( '' !== $url ) {
                $img = '<img class="kbl3-logo__img" src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '">';
                if ( ! empty( $a['linkURL'] ) ) {
                        $img = '<a class="kbl3-logo__link" href="' . esc_url( $a['linkURL'] ) . '">' . $img . '</a>';
                }
        }

        $markup = '<figure class="' . esc_attr( $class ) . '" style="' . esc_attr( $style ) . '"' . ( '' !== $anchor ? ' id="' . esc_attr( $anchor ) . '"' : '' ) . '>'
                . $img
                . '</figure>';
        return kbl3_block( 'kbl/logo', $attrs, $markup );
}

/**
 * kbl/nav-menu (dynamic — self-closing).
 *
 * Root markup comes from kbl3_render_nav_menu() in inc/dynamic.php
 * (same class/style fragment contract).
 *
 * @param array $a menu|fontSize|fontSizeT|fontSizeM|fontWeight|lineHeight|letterSpacing|textTransform|color|hoverColor|bgColor|gap|padX|padY|hideD|hideT|hideM|mt|mb|mtM|mbM.
 */
function kbl3_nav_menu( array $a = array() ): string {
        $attrs = array();
        if ( isset( $a['menu'] ) && (int) $a['menu'] > 0 ) { $attrs['menu'] = (int) $a['menu']; }
        kbl3_typography_attrs( $a, $attrs );
        foreach ( array( 'color', 'hoverColor', 'bgColor' ) as $k ) {
                if ( ! empty( $a[ $k ] ) ) { $attrs[ $k ] = $a[ $k ]; }
        }
        if ( isset( $a['gap'] ) && 28 !== (int) $a['gap'] ) { $attrs['gap'] = (int) $a['gap']; }
        if ( isset( $a['padX'] ) && 4 !== (int) $a['padX'] ) { $attrs['padX'] = (int) $a['padX']; }
        if ( isset( $a['padY'] ) && 8 !== (int) $a['padY'] ) { $attrs['padY'] = (int) $a['padY']; }
        kbl3_visibility_attrs( $a, $attrs );
        kbl3_spacing_attrs( $a, $attrs );
        return kbl3_block( 'kbl/nav-menu', $attrs, '' );
}

/**
 * kbl/search (dynamic — self-closing).
 *
 * Root markup comes from kbl3_render_search() in inc/dynamic.php
 * (same class/style fragment contract).
 *
 * @param array $a placeholder|width|radius|inputBg|textColor|borderColor|iconColor|hideD|hideT|hideM|mt|mb|mtM|mbM.
 */
function kbl3_search( array $a = array() ): string {
        $attrs = array();
        if ( isset( $a['placeholder'] ) && 'Search…' !== (string) $a['placeholder'] ) { $attrs['placeholder'] = (string) $a['placeholder']; }
        if ( isset( $a['width'] ) && 220 !== (int) $a['width'] ) { $attrs['width'] = (int) $a['width']; }
        if ( isset( $a['radius'] ) && 8 !== (int) $a['radius'] ) { $attrs['radius'] = (int) $a['radius']; }
        foreach ( array( 'inputBg', 'textColor', 'borderColor', 'iconColor' ) as $k ) {
                if ( ! empty( $a[ $k ] ) ) { $attrs[ $k ] = $a[ $k ]; }
        }
        kbl3_visibility_attrs( $a, $attrs );
        kbl3_spacing_attrs( $a, $attrs );
        return kbl3_block( 'kbl/search', $attrs, '' );
}

/**
 * kbl/language-switcher — compact multi-language switcher (dropdown or inline).
 *
 * display = 'dropdown' (default): globe icon + current language + chevron
 * button that opens an absolutely-positioned panel with every language —
 * the shape GTranslate/Blocksy/Elementor switchers use, and the only one
 * that survives a 9-language GTranslate configuration inside a header row.
 * display = 'inline': the flat link row (v3.9 behavior) for owners who
 * want it back.
 *
 * @param array $a     items[{code,label,url,current}]|display|color|activeColor|fontSize|align|hideD|hideT|hideM|mt|mb|mtM|mbM|anchor.
 * @param array $items Optional explicit item list (falls back to $a['items'], then defaults).
 */
function kbl3_language_switcher( array $a = array(), array $items = array() ): string {
        if ( array() === $items ) {
                $items = array_key_exists( 'items', $a ) ? (array) $a['items'] : kbl3_lang_defaults();
        }
        $align  = $a['align'] ?? 'left';
        $font   = isset( $a['fontSize'] ) ? (int) $a['fontSize'] : 13;
        $source = ( $a['source'] ?? 'custom' ) === 'gtranslate' ? 'gtranslate' : 'custom';
        $mode   = ( $a['display'] ?? 'dropdown' ) === 'inline' ? 'inline' : 'dropdown';

        $attrs = array();
        if ( 'custom' !== $source ) { $attrs['source'] = $source; }
        if ( ! kbl3_lang_items_equal( $items, kbl3_lang_defaults() ) ) { $attrs['items'] = array_values( $items ); }
        if ( 'dropdown' !== $mode ) { $attrs['display'] = 'inline'; }
        if ( 'left' !== $align ) { $attrs['align'] = $align; }
        if ( ! empty( $a['color'] ) ) { $attrs['color'] = $a['color']; }
        if ( ! empty( $a['activeColor'] ) ) { $attrs['activeColor'] = $a['activeColor']; }
        if ( 13 !== $font ) { $attrs['fontSize'] = $font; }
        kbl3_visibility_attrs( $a, $attrs );
        kbl3_spacing_attrs( $a, $attrs );

        return kbl3_block( 'kbl/language-switcher', $attrs, kbl3_lang_markup( $a, $items ) );
}

/**
 * The rendered switcher for kbl/language-switcher — dropdown or inline.
 *
 * Split out of kbl3_language_switcher() so the GTranslate bridge
 * (inc/gtranslate.php) can re-render it from the plugin's own language
 * settings — same escaping, same markup contract as the JS save() twin.
 *
 * Dropdown shape (display = 'dropdown', the default):
 *
 *   <div class="…kbl3-lang kbl3-lang--dropdown">
 *     <button class="kbl3-lang__toggle" aria-expanded aria-haspopup>
 *       [globe svg] <span class="kbl3-lang__current">English</span> [chevron svg]
 *     </button>
 *     <ul class="kbl3-lang__menu"> <li>…</li> … </ul>
 *   </div>
 *
 * The panel is hidden until .is-open (front.js) or :focus-within (keyboard).
 *
 * @param array $a     Style attrs (source|display|align|color|activeColor|fontSize|anchor|visibility|spacing).
 * @param array $items Rows: code|label|url|current.
 */
function kbl3_lang_markup( array $a, array $items ): string {
        $align  = $a['align'] ?? 'left';
        $font   = isset( $a['fontSize'] ) ? (int) $a['fontSize'] : 13;
        $anchor = isset( $a['anchor'] ) ? (string) $a['anchor'] : '';
        $source = ( $a['source'] ?? 'custom' ) === 'gtranslate' ? 'gtranslate' : 'custom';
        $mode   = ( $a['display'] ?? 'dropdown' ) === 'inline' ? 'inline' : 'dropdown';

        $class = kbl3_cx( 'wp-block-kbl-language-switcher', 'kbl3-lang',
                'dropdown' === $mode ? 'kbl3-lang--dropdown' : '',
                'center' === $align ? 'kbl3-lang--center' : '',
                'right' === $align ? 'kbl3-lang--right' : '',
                kbl3_visibility( $a ), kbl3_spacing_class( $a ) );
        $style = kbl3_style( array_merge( array(
                array( '--kbl3-lang-fg', $a['color'] ?? '' ),
                array( '--kbl3-lang-active', $a['activeColor'] ?? '' ),
                array( '--kbl3-fs', $font . 'px' ),
        ), kbl3_spacing_pairs( $a ) ) );

        $lis = '';
        $current_label = '';
        foreach ( $items as $item ) {
                $label = (string) ( $item['label'] ?? '' );
                $url   = (string) ( $item['url'] ?? '' );
                $code  = (string) ( $item['code'] ?? '' );
                if ( ! empty( $item['current'] ) && '' === $current_label ) {
                        $current_label = $label;
                }

                if ( 'gtranslate' === $source ) {
                        /* GTranslate mode: our anchors carry data-gt-lang and
                         * inc/gtranslate.php makes GTranslate bind them. */
                        $lis .= '<li class="kbl3-lang__item">'
                                . '<a class="' . esc_attr( kbl3_cx( 'kbl3-lang__link', ! empty( $item['current'] ) ? 'is-current' : '', 'notranslate' ) ) . '" href="#" data-gt-lang="' . esc_attr( $code ) . '">'
                                . esc_html( $label )
                                . '</a></li>';
                        continue;
                }

                $lis .= '<li class="kbl3-lang__item">'
                        . '<a class="' . esc_attr( kbl3_cx( 'kbl3-lang__link', ! empty( $item['current'] ) ? 'is-current' : '' ) ) . '" href="' . esc_url( '' !== $url ? $url : '#' ) . '">'
                        . esc_html( $label )
                        . '</a></li>';
        }

        if ( 'dropdown' !== $mode ) {
                return '<ul class="' . esc_attr( $class ) . '"' . ( '' !== $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . ( '' !== $anchor ? ' id="' . esc_attr( $anchor ) . '"' : '' ) . '>' . $lis . '</ul>';
        }

        /* Dropdown: the toggle shows the current language's label (the first
         * flagged item; falls back to the first item, then an em dash). */
        if ( '' === $current_label ) {
                $current_label = isset( $items[0]['label'] ) ? (string) $items[0]['label'] : '—';
        }

        $toggle = '<button class="kbl3-lang__toggle" type="button" aria-expanded="false" aria-haspopup="true" aria-label="' . esc_attr__( 'Switch language', 'kabel-blocks' ) . '">'
                . '<span class="kbl3-lang__globe" aria-hidden="true">' . kbl3_icon_svg( 'globe' ) . '</span>'
                . '<span class="kbl3-lang__current">' . esc_html( $current_label ) . '</span>'
                . '<span class="kbl3-lang__chevron" aria-hidden="true">' . kbl3_icon_svg( 'chevron-down' ) . '</span>'
                . '</button>';

        return '<div class="' . esc_attr( $class ) . '"' . ( '' !== $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . ( '' !== $anchor ? ' id="' . esc_attr( $anchor ) . '"' : '' ) . '>'
                . $toggle
                . '<ul class="kbl3-lang__menu">' . $lis . '</ul>'
                . '</div>';
}

/** Default languages. JS twin: DEFAULT_ITEMS in blocks/language-switcher.js. */
function kbl3_lang_defaults(): array {
        return array(
                array( 'code' => 'EN', 'label' => 'English', 'url' => '', 'current' => true ),
                array( 'code' => '中文', 'label' => '中文', 'url' => '', 'current' => false ),
                array( 'code' => 'IT', 'label' => 'Italiano', 'url' => '', 'current' => false ),
        );
}

/** Loose equality for language lists (comment attrs keep only non-defaults). */
function kbl3_lang_items_equal( array $items, array $defaults ): bool {
        $norm = function ( array $list ): array {
                $out = array();
                foreach ( $list as $item ) {
                        $out[] = array(
                                'code'    => (string) ( $item['code'] ?? '' ),
                                'label'   => (string) ( $item['label'] ?? '' ),
                                'url'     => (string) ( $item['url'] ?? '' ),
                                'current' => ! empty( $item['current'] ),
                        );
                }
                return $out;
        };
        return $norm( $items ) === $norm( $defaults );
}

/**
 * kbl/contact-info — phone / email / address row with icons.
 *
 * @param array $a     items[{icon,label,url}]|color|iconColor|gap|fontSize|hideD|hideT|hideM|mt|mb|mtM|mbM|anchor.
 * @param array $items Optional explicit item list (falls back to $a['items'], then defaults).
 */
function kbl3_contact_info( array $a = array(), array $items = array() ): string {
        if ( array() === $items ) {
                $items = array_key_exists( 'items', $a ) ? (array) $a['items'] : kbl3_contact_defaults();
        }
        $gap    = isset( $a['gap'] ) ? (int) $a['gap'] : 20;
        $font   = isset( $a['fontSize'] ) ? (int) $a['fontSize'] : 13;
        $anchor = isset( $a['anchor'] ) ? (string) $a['anchor'] : '';

        $class = kbl3_cx( 'wp-block-kbl-contact-info', 'kbl3-contact', kbl3_visibility( $a ), kbl3_spacing_class( $a ) );
        $style = kbl3_style( array_merge( array(
                array( '--kbl3-contact-fg', $a['color'] ?? '' ),
                array( '--kbl3-contact-ic', $a['iconColor'] ?? '' ),
                array( '--kbl3-contact-gap', $gap . 'px' ),
                array( '--kbl3-fs', $font . 'px' ),
        ), kbl3_spacing_pairs( $a ) ) );

        $attrs = array();
        if ( ! kbl3_contact_items_equal( $items, kbl3_contact_defaults() ) ) { $attrs['items'] = array_values( $items ); }
        if ( ! empty( $a['color'] ) ) { $attrs['color'] = $a['color']; }
        if ( ! empty( $a['iconColor'] ) ) { $attrs['iconColor'] = $a['iconColor']; }
        if ( 20 !== $gap ) { $attrs['gap'] = $gap; }
        if ( 13 !== $font ) { $attrs['fontSize'] = $font; }
        kbl3_visibility_attrs( $a, $attrs );
        kbl3_spacing_attrs( $a, $attrs );

        $lis = '';
        foreach ( $items as $item ) {
                $icon  = (string) ( $item['icon'] ?? '' );
                $label = (string) ( $item['label'] ?? '' );
                $url   = (string) ( $item['url'] ?? '' );
                $link  = '' !== $url
                        ? '<a class="kbl3-contact__link" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>'
                        : '<span class="kbl3-contact__link">' . esc_html( $label ) . '</span>';
                $lis .= '<li class="kbl3-contact__item">'
                        . '<span class="kbl3-contact__icon">' . kbl3_icon_svg( $icon ) . '</span>'
                        . $link
                        . '</li>';
        }

        $markup = '<ul class="' . esc_attr( $class ) . '"' . ( '' !== $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . ( '' !== $anchor ? ' id="' . esc_attr( $anchor ) . '"' : '' ) . '>' . $lis . '</ul>';
        return kbl3_block( 'kbl/contact-info', $attrs, $markup );
}

/** Default contact items. JS twin: DEFAULT_ITEMS in blocks/contact-info.js. */
function kbl3_contact_defaults(): array {
        return array(
                array( 'icon' => 'phone', 'label' => '+86 180 3320 5148', 'url' => 'https://wa.me/8618033205148' ),
                array( 'icon' => 'mail', 'label' => 'kabelpaints@gmail.com', 'url' => 'mailto:kabelpaints@gmail.com' ),
                array( 'icon' => 'map-pin', 'label' => 'Foshan · Guangdong · China', 'url' => '' ),
        );
}

/** Loose equality for contact lists (comment attrs keep only non-defaults). */
function kbl3_contact_items_equal( array $items, array $defaults ): bool {
        $norm = function ( array $list ): array {
                $out = array();
                foreach ( $list as $item ) {
                        $out[] = array(
                                'icon'  => (string) ( $item['icon'] ?? '' ),
                                'label' => (string) ( $item['label'] ?? '' ),
                                'url'   => (string) ( $item['url'] ?? '' ),
                        );
                }
                return $out;
        };
        return $norm( $items ) === $norm( $defaults );
}


/**
 * Shadow value by key. JS twin: SHADOWS.
 */
function kbl3_shadow( string $key ): ?string {
        $shadows = array(
                'soft'   => '0 2px 14px rgba(0, 0, 0, 0.18)',
                'medium' => '0 12px 32px rgba(0, 0, 0, 0.30)',
                'strong' => '0 26px 64px rgba(0, 0, 0, 0.45)',
        );
        return $shadows[ $key ] ?? null;
}

/* =====================================================================
 * The v1/v2 → v3 converter
 * =================================================================== */

/**
 * Legacy-emission switch (upgrader only): when on, kbl_b() returns the old
 * v1/v2 spec markup so the migrator can compare a page's stored content
 * against what the OLD plugin would have generated.
 *
 * @param bool|null $on True/false to switch, null to query.
 * @return bool Current mode.
 */
function kbl3_legacy_mode( $on = null ): bool {
        static $mode = false;
        if ( null !== $on ) {
                $mode = (bool) $on;
        }
        return $mode;
}

/**
 * Serialize one of OUR blocks (v3 output).
 *
 * This is the function the page layouts call — same signature as the old
 * spec-driven kbl_b(), but the output is now fully editable kbl/* markup.
 * The legacy serializer lives on as kbl_b_legacy() for the upgrader.
 *
 * @param string $short   Layout block key (hero, split, …).
 * @param array  $attrs   v1/v2 attribute vocabulary.
 * @param string $inner   Inner markup (for section).
 */
function kbl_b( string $short, array $attributes = array(), string $inner = '' ): string {
        if ( kbl3_legacy_mode() ) {
                return kbl_b_legacy( $short, $attributes, $inner );
        }
        switch ( $short ) {
                case 'hero':
                        return kbl3c_hero( $attributes );
                case 'split':
                        return kbl3c_split( $attributes );
                case 'features':
                        return kbl3c_features( $attributes );
                case 'stats':
                        return kbl3c_stats( $attributes );
                case 'process':
                        return kbl3c_process( $attributes );
                case 'milestones':
                        return kbl3c_milestones( $attributes );
                case 'gallery':
                        return kbl3c_gallery( $attributes );
                case 'logos':
                        return kbl3c_logos( $attributes );
                case 'faq':
                        return kbl3c_faq( $attributes );
                case 'products':
                        return kbl3c_products( $attributes );
                case 'inquiry':
                        return kbl3c_inquiry( $attributes );
                case 'contact':
                        return kbl3c_contact( $attributes );
                case 'cta':
                        return kbl3c_cta( $attributes );
                case 'cta-quote':
                        return kbl3c_cta_quote( $attributes );
                case 'section':
                        return kbl3c_section( $attributes, $inner );
                default:
                        return '';
        }
}

/** zone → tone. */
function kbl3c_tone( $zone ): string {
        return 'b' === $zone ? 'navy' : 'dark';
}

/** Old (v1/v2) icon slugs → v3 icon names. */
function kbl3c_icon( $slug ): string {
        $map = array(
                'shield'  => 'shield-check',
                'bolt'    => 'flame',
                'cube'    => 'layers',
                'medal'   => 'award',
                'wrench'  => 'brush',
                'sparkle' => 'sparkles',
                'pin'     => 'map-pin',
                'drop'    => 'droplet',
                'gear'    => 'gear',
                'search'  => 'search',
        );
        $slug = (string) $slug;
        return isset( $map[ $slug ] ) ? $map[ $slug ] : $slug;
}

/** Old pad vocabulary maps 1:1. */
function kbl3c_pad( $pad ): string {
        return in_array( $pad, array( 'compact', 'normal', 'spacious' ), true ) ? $pad : 'normal';
}

/** hero: section + eyebrow + title + sub + button row (center or split). */
function kbl3c_hero( array $a ): string {
        $bg_id  = isset( $a['bgImage'] ) ? (int) $a['bgImage'] : 0;
        $bg_url = kbl3_media_url( $bg_id );
        $split  = ( isset( $a['layout'] ) && 'split' === $a['layout'] ) || ! empty( $a['image'] );
        $center = ! $split;

        $sec = array(
                'tone' => kbl3c_tone( $a['zone'] ?? 'a' ),
                'pad'  => kbl3c_pad( $a['pad'] ?? 'normal' ),
        );
        if ( $bg_url ) {
                $sec['bgURL'] = $bg_url;
                $sec['bgId']  = $bg_id;
        }

        $align = $center ? 'center' : 'left';

        $inner = '';
        if ( ! empty( $a['eyebrow'] ) ) {
                $inner .= kbl3_heading( array( 'text' => $a['eyebrow'], 'eyebrow' => true, 'align' => $align ) );
        }
        $inner .= kbl3_heading( array( 'text' => (string) ( $a['title'] ?? '' ), 'size' => 'xl', 'align' => $align, 'level' => $split ? 2 : 1 ) );
        if ( ! empty( $a['sub'] ) ) {
                $inner .= kbl3_text( array( 'text' => $a['sub'], 'lead' => true, 'align' => $align, 'size' => 'l' ) );
        }

        $buttons = '';
        if ( ! empty( $a['primaryLabel'] ) ) {
                $buttons .= kbl3_button( array( 'text' => $a['primaryLabel'], 'rfq' => true, 'variant' => 'gold', 'size' => 'm' ) );
        }
        if ( ! empty( $a['secondaryLabel'] ) ) {
                $buttons .= kbl3_button( array(
                        'text'    => $a['secondaryLabel'],
                        'url'     => (string) ( $a['secondaryUrl'] ?? '' ),
                        'variant' => 'outline',
                ) );
        }
        if ( '' !== $buttons ) {
                $inner .= kbl3_button_group( array( 'align' => $center ? 'center' : 'left', 'gap' => 14 ), $buttons );
        }

        if ( $split ) {
                $image_id = isset( $a['image'] ) ? (int) $a['image'] : 0;
                $url      = kbl3_media_url( $image_id );
                $media_col = '';
                if ( $url ) {
                        $media_col = kbl3_column( array(), kbl3_image( array( 'url' => $url, 'id' => $image_id, 'alt' => (string) ( $a['title'] ?? '' ) ) ) );
                }
                $copy_col = kbl3_column( array( 'contentAlign' => 'left' ), $inner );
                return kbl3_section( $sec, kbl3_columns( array( 'gap' => 48, 'vAlign' => 'center' ), $copy_col . $media_col ) );
        }

        $sec['width'] = isset( $sec['width'] ) ? $sec['width'] : 'narrow';
        return kbl3_section( $sec, $inner );
}

/** split: two columns — image one side, copy the other. */
function kbl3c_split( array $a ): string {
        $image_id = isset( $a['image'] ) ? (int) $a['image'] : 0;
        $url      = kbl3_media_url( $image_id );
        $reverse  = ! empty( $a['reverse'] );

        $copy = '';
        if ( ! empty( $a['eyebrow'] ) ) {
                $copy .= kbl3_heading( array( 'text' => $a['eyebrow'], 'eyebrow' => true ) );
        }
        $copy .= kbl3_heading( array( 'text' => (string) ( $a['title'] ?? '' ), 'size' => 'l' ) );
        if ( ! empty( $a['sub'] ) ) {
                $copy .= kbl3_text( array( 'text' => $a['sub'], 'lead' => true ) );
        }
        if ( ! empty( $a['body'] ) ) {
                $copy .= kbl3_text( array( 'text' => $a['body'] ) );
        }
        if ( ! empty( $a['bullets'] ) && is_array( $a['bullets'] ) ) {
                $copy .= kbl3_list( array( 'marker' => 'check' ), $a['bullets'] );
        }

        $media_col = '';
        if ( $url ) {
                $media_col = kbl3_column( array(), kbl3_image( array( 'url' => $url, 'id' => $image_id, 'alt' => (string) ( $a['title'] ?? '' ) ) ) );
        }
        $copy_col = kbl3_column( array(), $copy );
        $columns  = $reverse ? ( $copy_col . $media_col ) : ( $media_col . $copy_col );

        $sec = array(
                'tone' => kbl3c_tone( $a['zone'] ?? 'b' ),
                'pad'  => kbl3c_pad( $a['pad'] ?? 'normal' ),
        );
        return kbl3_section( $sec, kbl3_columns( array( 'gap' => 40, 'vAlign' => 'center' ), $columns ) );
}

/** features: N columns of icon boxes. */
function kbl3c_features( array $a ): string {
        $cols = isset( $a['columns'] ) ? (int) $a['columns'] : 3;
        $cols = max( 1, min( 6, $cols ) );

        $inner = '';
        if ( ! empty( $a['eyebrow'] ) ) {
                $inner .= kbl3_heading( array( 'text' => $a['eyebrow'], 'eyebrow' => true, 'align' => 'center' ) );
        }
        if ( ! empty( $a['title'] ) ) {
                $inner .= kbl3_heading( array( 'text' => $a['title'], 'size' => 'l', 'align' => 'center' ) );
        }
        if ( ! empty( $a['sub'] ) ) {
                $inner .= kbl3_text( array( 'text' => $a['sub'], 'align' => 'center' ) );
        }

        $cards = '';
        foreach ( (array) ( $a['items'] ?? array() ) as $item ) {
                $cards .= kbl3_column( array( 'contentAlign' => 'center' ), kbl3_icon_box( array(
                        'iconName' => kbl3c_icon( $item['icon'] ?? 'check' ),
                        'title'    => $item['title'] ?? '',
                        'text'     => $item['text'] ?? '',
                        'layout'   => 'v',
                        'align'    => 'center',
                ) ) );
        }
        $inner .= kbl3_columns( array( 'gap' => 24 ), $cards );

        $sec = array(
                'tone' => kbl3c_tone( $a['zone'] ?? 'a' ),
                'pad'  => kbl3c_pad( $a['pad'] ?? 'normal' ),
        );
        return kbl3_section( $sec, $inner );
}

/** stats: N columns of counters. */
function kbl3c_stats( array $a ): string {
        $inner = '';
        if ( ! empty( $a['title'] ) ) {
                $inner .= kbl3_heading( array( 'text' => $a['title'], 'size' => 'l', 'align' => 'center' ) );
        }
        if ( ! empty( $a['text'] ) ) {
                $inner .= kbl3_text( array( 'text' => $a['text'], 'align' => 'center' ) );
        }

        $cards = '';
        foreach ( (array) ( $a['items'] ?? array() ) as $item ) {
                $value = (string) ( $item['value'] ?? '' );
                $pre   = '';
                $suf   = '';
                if ( isset( $item['pre'] ) ) {
                        $pre = (string) $item['pre'];
                }
                if ( isset( $item['suf'] ) ) {
                        $suf = (string) $item['suf'];
                }
                $cards .= kbl3_column( array( 'contentAlign' => 'center' ), kbl3_stat( array(
                        'pre'    => $pre,
                        'number' => $value,
                        'suf'    => $suf,
                        'label'  => $item['label'] ?? '',
                        'align'  => 'center',
                ) ) );
        }
        $inner .= kbl3_columns( array( 'gap' => 24 ), $cards );

        $sec = array(
                'tone' => kbl3c_tone( $a['zone'] ?? 'a' ),
                'pad'  => kbl3c_pad( $a['pad'] ?? 'compact' ),
        );
        return kbl3_section( $sec, $inner );
}

/** process: step cards. */
function kbl3c_process( array $a ): string {
        $inner = '';
        if ( ! empty( $a['eyebrow'] ) ) {
                $inner .= kbl3_heading( array( 'text' => $a['eyebrow'], 'eyebrow' => true, 'align' => 'center' ) );
        }
        if ( ! empty( $a['title'] ) ) {
                $inner .= kbl3_heading( array( 'text' => $a['title'], 'size' => 'l', 'align' => 'center' ) );
        }
        if ( ! empty( $a['text'] ) ) {
                $inner .= kbl3_text( array( 'text' => $a['text'], 'align' => 'center' ) );
        }

        $cards = '';
        $items = (array) ( $a['items'] ?? array() );
        $count = count( $items );
        $cols  = max( 1, min( 6, $count > 4 ? 4 : ( $count > 0 ? $count : 1 ) ) );
        foreach ( $items as $i => $item ) {
                $cards .= kbl3_column( array(
                        'bgColor'      => 'rgba(255, 255, 255, 0.04)',
                        'borderWidth'  => 1,
                        'borderColor'  => 'rgba(255, 255, 255, 0.10)',
                        'padding'      => 24,
                ), kbl3_icon_box( array(
                        'iconName' => kbl3c_icon( $item['icon'] ?? 'check' ),
                        'title'    => sprintf( '%02d · %s', $i + 1, $item['title'] ?? '' ),
                        'text'     => $item['text'] ?? '',
                        'layout'   => 'v',
                        'align'    => 'left',
                        'bgColor'  => null,
                ) ) );
        }
        if ( '' !== $cards ) {
                $inner .= kbl3_columns( array( 'gap' => 20 ), $cards );
        }

        $sec = array(
                'tone' => kbl3c_tone( $a['zone'] ?? 'b' ),
                'pad'  => kbl3c_pad( $a['pad'] ?? 'normal' ),
        );
        return kbl3_section( $sec, $inner );
}

/** milestones: timeline rows (year + text). */
function kbl3c_milestones( array $a ): string {
        $rows = '';
        foreach ( (array) ( $a['items'] ?? array() ) as $item ) {
                $year = kbl3_column( array( 'contentAlign' => 'left' ), kbl3_stat( array(
                        'number' => (string) ( $item['year'] ?? '' ),
                        'label'  => $item['label'] ?? '',
                        'align'  => 'left',
                ) ) );
                $copy = kbl3_column( array(), kbl3_text( array( 'text' => (string) ( $item['text'] ?? '' ) ) ) );
                $rows .= kbl3_columns( array( 'gap' => 32, 'vAlign' => 'center' ), $year . $copy );
        }

        $inner = '';
        if ( ! empty( $a['title'] ) ) {
                $inner .= kbl3_heading( array( 'text' => $a['title'], 'size' => 'l' ) );
        }
        $inner .= $rows;

        $sec = array(
                'tone' => kbl3c_tone( $a['zone'] ?? 'b' ),
                'pad'  => kbl3c_pad( $a['pad'] ?? 'normal' ),
        );
        return kbl3_section( $sec, $inner );
}

/** gallery: image grid. */
function kbl3c_gallery( array $a ): string {
        $cols = isset( $a['columns'] ) ? max( 1, min( 6, (int) $a['columns'] ) ) : 3;

        $inner = '';
        if ( ! empty( $a['eyebrow'] ) ) {
                $inner .= kbl3_heading( array( 'text' => $a['eyebrow'], 'eyebrow' => true, 'align' => 'center' ) );
        }
        if ( ! empty( $a['title'] ) ) {
                $inner .= kbl3_heading( array( 'text' => $a['title'], 'size' => 'l', 'align' => 'center' ) );
        }
        if ( ! empty( $a['sub'] ) ) {
                $inner .= kbl3_text( array( 'text' => $a['sub'], 'align' => 'center' ) );
        }

        $images = '';
        $ids    = (array) ( $a['ids'] ?? array() );
        $chunk  = array_chunk( array_filter( $ids ), $cols );
        foreach ( $chunk as $row ) {
                $cols_html = '';
                foreach ( $row as $id ) {
                        $id  = (int) $id;
                        $url = kbl3_media_url( $id );
                        if ( ! $url ) {
                                continue;
                        }
                        $cols_html .= kbl3_column( array(), kbl3_image( array( 'url' => $url, 'id' => $id, 'radius' => 2 ) ) );
                }
                if ( '' !== $cols_html ) {
                        $images .= kbl3_columns( array( 'gap' => 18 ), $cols_html );
                }
        }
        $inner .= $images;

        $sec = array(
                'tone' => kbl3c_tone( $a['zone'] ?? 'b' ),
                'pad'  => kbl3c_pad( $a['pad'] ?? 'normal' ),
        );
        return kbl3_section( $sec, $inner );
}

/** logos: partner mark row. */
function kbl3c_logos( array $a ): string {
        $cols_html = '';
        foreach ( (array) ( $a['ids'] ?? array() ) as $i => $id ) {
                $id  = (int) $id;
                $url = kbl3_media_url( $id );
                if ( ! $url ) {
                        continue;
                }
                $name = '';
                if ( ! empty( $a['names'] ) && isset( $a['names'][ $i ] ) ) {
                        $name = (string) $a['names'][ $i ];
                }
                $cols_html .= kbl3_column( array( 'contentAlign' => 'center' ), kbl3_image( array(
                        'url'  => $url,
                        'id'   => $id,
                        'alt'  => $name,
                        'width' => 60,
                ) ) );
        }

        $inner = '';
        if ( ! empty( $a['eyebrow'] ) ) {
                $inner .= kbl3_heading( array( 'text' => $a['eyebrow'], 'eyebrow' => true, 'align' => 'center' ) );
        }
        if ( ! empty( $a['sub'] ) ) {
                $inner .= kbl3_text( array( 'text' => $a['sub'], 'align' => 'center', 'size' => 's' ) );
        }
        $inner .= kbl3_columns( array( 'gap' => 28, 'vAlign' => 'center' ), $cols_html );

        $sec = array(
                'tone' => kbl3c_tone( $a['zone'] ?? 'a' ),
                'pad'  => 'compact',
        );
        return kbl3_section( $sec, $inner );
}

/** faq: accordion list. */
function kbl3c_faq( array $a ): string {
        $inner = '';
        if ( ! empty( $a['eyebrow'] ) ) {
                $inner .= kbl3_heading( array( 'text' => $a['eyebrow'], 'eyebrow' => true, 'align' => 'center' ) );
        }
        if ( ! empty( $a['title'] ) ) {
                $inner .= kbl3_heading( array( 'text' => $a['title'], 'size' => 'l', 'align' => 'center' ) );
        }

        $items_html = '';
        $first      = true;
        foreach ( (array) ( $a['items'] ?? array() ) as $item ) {
                $items_html .= kbl3_accordion( array(
                        'q'    => (string) ( $item['q'] ?? '' ),
                        'a'    => (string) ( $item['a'] ?? '' ),
                        'open' => $first,
                ) );
                $first = false;
        }
        if ( '' !== $items_html ) {
                $inner .= kbl3_section_narrow_wrap( $items_html );
        }

        $sec = array(
                'tone' => kbl3c_tone( $a['zone'] ?? 'b' ),
                'pad'  => kbl3c_pad( $a['pad'] ?? 'normal' ),
        );
        return kbl3_section( $sec, $inner );
}

/** FAQ items sit in a narrow column. */
function kbl3_section_narrow_wrap( string $inner ): string {
        // Inside a section the container is already full width; narrow the list
        // with the section's own width attr instead of nesting sections.
        return $inner;
}

/** products: live product grid. */
function kbl3c_products( array $a ): string {
        $inner = '';
        if ( ! empty( $a['eyebrow'] ) ) {
                $inner .= kbl3_heading( array( 'text' => $a['eyebrow'], 'eyebrow' => true, 'align' => 'center' ) );
        }
        if ( ! empty( $a['title'] ) ) {
                $inner .= kbl3_heading( array( 'text' => $a['title'], 'size' => 'l', 'align' => 'center' ) );
        }
        $inner .= kbl3_post_grid( array(
                'postType'    => 'product',
                'columns'     => isset( $a['columns'] ) ? (int) $a['columns'] : 3,
                'count'       => isset( $a['count'] ) ? (int) $a['count'] : 6,
                'showExcerpt' => true,
                'btnLabel'    => __( 'View All', 'kabel-blocks' ),
                'btnURL'      => '/products/',
        ) );

        $sec = array(
                'tone' => kbl3c_tone( $a['zone'] ?? 'b' ),
                'pad'  => kbl3c_pad( $a['pad'] ?? 'normal' ),
        );
        return kbl3_section( $sec, $inner );
}

/** inquiry: live form. */
function kbl3c_inquiry( array $a ): string {
        $inner = '';
        if ( ! empty( $a['eyebrow'] ) ) {
                $inner .= kbl3_heading( array( 'text' => $a['eyebrow'], 'eyebrow' => true, 'align' => 'center' ) );
        }
        if ( ! empty( $a['title'] ) ) {
                $inner .= kbl3_heading( array( 'text' => $a['title'], 'size' => 'l', 'align' => 'center' ) );
        }
        if ( ! empty( $a['sub'] ) ) {
                $inner .= kbl3_text( array( 'text' => $a['sub'], 'align' => 'center' ) );
        }
        if ( ! empty( $a['message'] ) ) {
                $inner .= kbl3_text( array( 'text' => $a['message'], 'align' => 'center', 'size' => 's' ) );
        }
        $inner .= kbl3_inquiry_form( array(
                'title'       => '',
                'sub'         => '',
                'buttonLabel' => __( 'Send Inquiry', 'kabel-blocks' ),
        ) );

        $sec = array(
                'tone'  => kbl3c_tone( $a['zone'] ?? 'a' ),
                'pad'   => kbl3c_pad( $a['pad'] ?? 'normal' ),
                'width' => 'narrow',
        );
        return kbl3_section( $sec, $inner );
}

/** contact: info cards + form. */
function kbl3c_contact( array $a ): string {
        $cols = isset( $a['columns'] ) ? max( 2, min( 4, (int) $a['columns'] ) ) : 3;

        $inner = '';
        if ( ! empty( $a['eyebrow'] ) ) {
                $inner .= kbl3_heading( array( 'text' => $a['eyebrow'], 'eyebrow' => true, 'align' => 'center' ) );
        }
        if ( ! empty( $a['title'] ) ) {
                $inner .= kbl3_heading( array( 'text' => $a['title'], 'size' => 'l', 'align' => 'center' ) );
        }
        if ( ! empty( $a['text'] ) ) {
                $inner .= kbl3_text( array( 'text' => $a['text'], 'align' => 'center' ) );
        }

        $cards = '';
        foreach ( (array) ( $a['items'] ?? array() ) as $item ) {
                $cards .= kbl3_column( array( 'contentAlign' => 'center' ), kbl3_icon_box( array(
                        'iconName' => kbl3c_icon( $item['icon'] ?? 'mail' ),
                        'title'    => $item['title'] ?? '',
                        'text'     => $item['text'] ?? '',
                        'layout'   => 'v',
                        'align'    => 'center',
                ) ) );
        }
        if ( '' !== $cards ) {
                $inner .= kbl3_columns( array( 'gap' => 24 ), $cards );
        }

        $sec = array(
                'tone' => kbl3c_tone( $a['zone'] ?? 'b' ),
                'pad'  => kbl3c_pad( $a['pad'] ?? 'normal' ),
        );
        return kbl3_section( $sec, $inner );
}

/** cta: dark band with title + sub + one button. */
function kbl3c_cta( array $a ): string {
        $product = (string) ( $a['btnProduct'] ?? ( $a['product'] ?? '' ) );

        $inner = '';
        if ( ! empty( $a['title'] ) ) {
                $inner .= kbl3_heading( array( 'text' => $a['title'], 'size' => 'l', 'align' => 'center' ) );
        }
        if ( ! empty( $a['sub'] ) ) {
                $inner .= kbl3_text( array( 'text' => $a['sub'], 'align' => 'center' ) );
        }
        if ( ! empty( $a['btnLabel'] ) ) {
                $inner .= kbl3_button_group( array( 'align' => 'center' ), kbl3_button( array(
                        'text'       => $a['btnLabel'],
                        'variant'    => 'gold',
                        'size'       => 'm',
                        'rfq'        => true,
                        'rfqProduct' => $product,
                ) ) );
        }

        $sec = array(
                'tone' => kbl3c_tone( $a['zone'] ?? 'a' ),
                'pad'  => 'compact',
        );
        return kbl3_section( $sec, $inner );
}

/** cta-quote: one quote button. */
function kbl3c_cta_quote( array $a ): string {
        $variant = 'outline' === ( $a['style'] ?? '' ) ? 'outline' : 'gold';
        $btn     = kbl3_button( array(
                'text'       => (string) ( $a['text'] ?? __( 'Consult Now', 'kabel-blocks' ) ),
                'url'        => (string) ( $a['url'] ?? '' ),
                'variant'    => $variant,
                'rfq'        => '' === (string) ( $a['url'] ?? '' ),
                'rfqProduct' => (string) ( $a['product'] ?? '' ),
                'align'      => (string) ( $a['align'] ?? '' ),
        ) );

        $align = $a['align'] ?? '';
        return kbl3_button_group( array( 'align' => $align, 'gap' => 12 ), $btn );
}

/** section: universal container passthrough. */
function kbl3c_section( array $a, string $inner ): string {
        $sec = array(
                'tone' => kbl3c_tone( $a['zone'] ?? 'a' ),
                'pad'  => kbl3c_pad( $a['pad'] ?? 'normal' ),
        );
        return kbl3_section( $sec, $inner );
}

/* =====================================================================
 * v8-b content blocks — twins of src/blocks/{card,tabs,testimonial,
 * video,team,pricing}.js save() functions. Same contracts as above:
 * class order [block] -> [hover] -> [anim] -> [visibility]; style order
 * [block] -> [typography] -> [anim delay]; comment attrs carry only
 * non-default values.
 * =================================================================== */

/**
 * kbl/card — the universal content card.
 *
 * @param array $a mediaId|mediaURL|title|text|btnLabel|btnURL|btnStyle|align|padding|bgColor|textColor|radius|shadow|borderWidth|borderColor|borderStyle|gFrom|gTo|gAngle|hoverLift|fontSize|fontSizeT|fontSizeM|anim|animDelay|hideD|hideT|hideM|mt|mb|mtM|mbM.
 */
function kbl3_card( array $a = array() ): string {
        $grad  = kbl3_gradient( $a );
        $style = kbl3_card_style( $a );
        $class = kbl3_cx(
                'wp-block-kbl-card',
                'kbl3-card',
                'center' === ( $a['align'] ?? '' ) ? 'is-center' : '',
                'right' === ( $a['align'] ?? '' ) ? 'is-right' : '',
                ! empty( $a['hoverLift'] ) ? 'kbl3-hover--lift' : '',
                kbl3_anim( $a ),
                kbl3_visibility( $a ),
                kbl3_spacing_class( $a )
        );
        $btn_variant = 'outline' === ( $a['btnStyle'] ?? 'cta' ) ? 'outline' : 'gold';
        $btn_label   = (string) ( $a['btnLabel'] ?? '' );

        $attrs = array();
        if ( ! empty( $a['mediaId'] ) ) { $attrs['mediaId'] = (int) $a['mediaId']; }
        if ( ! empty( $a['mediaURL'] ) ) { $attrs['mediaURL'] = $a['mediaURL']; }
        if ( 'cta' !== ( $a['btnStyle'] ?? 'cta' ) ) { $attrs['btnStyle'] = $a['btnStyle']; }
        if ( ! empty( $a['btnURL'] ) ) { $attrs['btnURL'] = $a['btnURL']; }
        if ( ! empty( $a['align'] ) ) { $attrs['align'] = $a['align']; }
        if ( isset( $a['padding'] ) && 24 !== (int) $a['padding'] ) { $attrs['padding'] = (int) $a['padding']; }
        if ( ! empty( $a['bgColor'] ) ) { $attrs['bgColor'] = $a['bgColor']; }
        if ( ! empty( $a['textColor'] ) ) { $attrs['textColor'] = $a['textColor']; }
        if ( ! empty( $a['radius'] ) ) { $attrs['radius'] = (int) $a['radius']; }
        if ( ! empty( $a['shadow'] ) ) { $attrs['shadow'] = $a['shadow']; }
        if ( ! empty( $a['borderWidth'] ) ) { $attrs['borderWidth'] = (int) $a['borderWidth']; }
        if ( ! empty( $a['borderColor'] ) ) { $attrs['borderColor'] = $a['borderColor']; }
        if ( ! empty( $a['borderStyle'] ) ) { $attrs['borderStyle'] = $a['borderStyle']; }
        kbl3_typography_attrs( $a, $attrs, array( 'fw' => false, 'lh' => false, 'ls' => false, 'tt' => false ) );
        kbl3_gradient_attrs( $a, $attrs );
        if ( ! empty( $a['hoverLift'] ) ) { $attrs['hoverLift'] = true; }
        kbl3_anim_attrs( $a, $attrs );
        kbl3_visibility_attrs( $a, $attrs );
        kbl3_spacing_attrs( $a, $attrs );

        $media = '';
        if ( ! empty( $a['mediaURL'] ) ) {
                $media = '<figure class="kbl3-card__media"><img class="kbl3-card__img" src="' . esc_url( $a['mediaURL'] ) . '" alt=""></figure>';
        }
        $btn = '';
        if ( '' !== $btn_label ) {
                $btn = '<a class="kbl3-btn kbl3-btn--' . $btn_variant . ' kbl3-btn--s kbl3-card__btn"'
                        . ( ! empty( $a['btnURL'] ) ? ' href="' . esc_url( $a['btnURL'] ) . '"' : '' )
                        . '>' . kbl3_rich( $btn_label ) . '</a>';
        }

        $markup = '<div class="' . esc_attr( $class ) . '"' . ( '' !== $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . '>'
                . $media
                . '<div class="kbl3-card__body" style="' . esc_attr( kbl3_card_body_style( $a ) ) . '">'
                . '<h3 class="kbl3-card__title">' . kbl3_rich( (string) ( $a['title'] ?? 'Wall Color Collection' ) ) . '</h3>'
                . '<p class="kbl3-card__text">' . kbl3_rich( (string) ( $a['text'] ?? 'Velvet-matte finish with ultra-low VOC — rich pigment that stays true for years.' ) ) . '</p>'
                . $btn
                . '</div></div>';
        return kbl3_block( 'kbl/card', $attrs, $markup );
}

/** Card root style pairs. JS twin: cardStyle() in blocks/card.js. */
function kbl3_card_style( array $a ): string {
        $grad = kbl3_gradient( $a );
        return kbl3_style( array_merge( array(
                array( 'background-color', $grad ? null : ( $a['bgColor'] ?? '' ) ),
                array( 'background-image', $grad ),
                array( 'color', $a['textColor'] ?? '' ),
                array( 'border-radius', ! empty( $a['radius'] ) ? $a['radius'] . 'px' : null ),
                array( 'box-shadow', kbl3_shadow( $a['shadow'] ?? '' ) ),
                array( 'border', ( ! empty( $a['borderColor'] ) && ! empty( $a['borderWidth'] ) ) ? $a['borderWidth'] . 'px ' . ( $a['borderStyle'] ?? 'solid' ) . ' ' . $a['borderColor'] : null ),
        ), kbl3_typography( $a, array( 'fw' => false, 'lh' => false, 'ls' => false, 'tt' => false ) ), kbl3_anim_delay( $a ), kbl3_spacing_pairs( $a ) ) );
}

/** Card body padding style. JS twin: toStyle(padding) in blocks/card.js. */
function kbl3_card_body_style( array $a ): string {
        return kbl3_style( array( array( 'padding', ( isset( $a['padding'] ) ? (int) $a['padding'] : 24 ) . 'px' ) ) );
}

/**
 * kbl/tabs — tabbed content. The first tab is active in the saved markup;
 * assets/js/front.js switches panels afterwards.
 *
 * @param array $a items[{title,content}]|accentColor|fontSize|fontSizeT|fontSizeM|anim|animDelay|hideD|hideT|hideM|mt|mb|mtM|mbM.
 */
function kbl3_tabs( array $a = array() ): string {
        $items = isset( $a['items'] ) ? (array) $a['items'] : kbl3_tabs_defaults();

        $class = kbl3_cx( 'wp-block-kbl-tabs', 'kbl3-tabs', kbl3_anim( $a ), kbl3_visibility( $a ), kbl3_spacing_class( $a ) );
        $style = kbl3_style( array_merge( array(
                array( '--kbl3-tabs-accent', $a['accentColor'] ?? '' ),
        ), kbl3_typography( $a, array( 'fw' => false, 'lh' => false, 'ls' => false, 'tt' => false ) ), kbl3_anim_delay( $a ), kbl3_spacing_pairs( $a ) ) );

        $attrs = array();
        if ( ! kbl3_items_equal( $items, kbl3_tabs_defaults() ) ) { $attrs['items'] = array_values( $items ); }
        if ( ! empty( $a['accentColor'] ) ) { $attrs['accentColor'] = $a['accentColor']; }
        kbl3_typography_attrs( $a, $attrs, array( 'fw' => false, 'lh' => false, 'ls' => false, 'tt' => false ) );
        kbl3_anim_attrs( $a, $attrs );
        kbl3_visibility_attrs( $a, $attrs );
        kbl3_spacing_attrs( $a, $attrs );

        $nav = '';
        foreach ( $items as $i => $item ) {
                $nav .= '<button class="kbl3-tabs__tab' . ( 0 === $i ? ' is-active' : '' ) . '" role="tab" aria-selected="' . ( 0 === $i ? 'true' : 'false' ) . '" type="button">'
                        . '<span class="kbl3-tabs__label">' . kbl3_rich( (string) ( $item['title'] ?? '' ) ) . '</span>'
                        . '</button>';
        }
        $panels = '';
        foreach ( $items as $i => $item ) {
                $panels .= '<div class="kbl3-tabs__panel' . ( 0 === $i ? ' is-active' : '' ) . '" role="tabpanel">'
                        . kbl3_rich( (string) ( $item['content'] ?? '' ) )
                        . '</div>';
        }

        $markup = '<div class="' . esc_attr( $class ) . '"' . ( '' !== $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . ' data-kbl-tabs="">'
                . '<div class="kbl3-tabs__nav" role="tablist">' . $nav . '</div>'
                . $panels
                . '</div>';
        return kbl3_block( 'kbl/tabs', $attrs, $markup );
}

/** Default tab items. JS twin: DEFAULT_ITEMS in blocks/tabs.js. */
function kbl3_tabs_defaults(): array {
        return array(
                array(
                        'title'   => 'Interior',
                        'content' => 'Velvet-matte and scrubbable — made for living rooms, bedrooms and hallways.',
                ),
                array(
                        'title'   => 'Exterior',
                        'content' => 'Weather-shield acrylics that keep facade color true through sun and rain.',
                ),
        );
}

/** Loose equality for item lists (comment attrs keep only non-defaults). */
function kbl3_items_equal( array $items, array $defaults ): bool {
        $norm = function ( array $list ): array {
                $out = array();
                foreach ( $list as $item ) {
                        $out[] = array(
                                'title'    => (string) ( $item['title'] ?? '' ),
                                'content'  => (string) ( $item['content'] ?? '' ),
                                'name'     => (string) ( $item['name'] ?? '' ),
                                'role'     => (string) ( $item['role'] ?? '' ),
                                'photoId'  => (int) ( $item['photoId'] ?? 0 ),
                                'photoURL' => (string) ( $item['photoURL'] ?? '' ),
                                'linkURL'  => (string) ( $item['linkURL'] ?? '' ),
                        );
                }
                return $out;
        };
        return $norm( $items ) === $norm( $defaults );
}

/**
 * kbl/testimonial — one customer review.
 *
 * @param array $a quote|name|role|avatarId|avatarURL|rating|align|color|fontSize|fontSizeT|fontSizeM|anim|animDelay|hideD|hideT|hideM|mt|mb|mtM|mbM.
 */
function kbl3_testimonial( array $a = array() ): string {
        $rating = kbl3_rating( $a['rating'] ?? 5 );
        $style  = kbl3_testimonial_style( $a );
        $class  = kbl3_cx(
                'wp-block-kbl-testimonial',
                'kbl3-testi',
                'center' === ( $a['align'] ?? 'center' ) ? 'is-center' : '',
                'right' === ( $a['align'] ?? 'center' ) ? 'is-right' : '',
                kbl3_anim( $a ),
                kbl3_visibility( $a ),
                kbl3_spacing_class( $a )
        );

        $attrs = array();
        if ( ! empty( $a['avatarId'] ) ) { $attrs['avatarId'] = (int) $a['avatarId']; }
        if ( ! empty( $a['avatarURL'] ) ) { $attrs['avatarURL'] = $a['avatarURL']; }
        if ( 5 !== $rating ) { $attrs['rating'] = $rating; }
        if ( 'center' !== ( $a['align'] ?? 'center' ) ) { $attrs['align'] = $a['align']; }
        if ( ! empty( $a['color'] ) ) { $attrs['color'] = $a['color']; }
        kbl3_typography_attrs( $a, $attrs, array( 'fw' => false, 'lh' => false, 'ls' => false, 'tt' => false ) );
        kbl3_anim_attrs( $a, $attrs );
        kbl3_visibility_attrs( $a, $attrs );
        kbl3_spacing_attrs( $a, $attrs );

        $stars = '';
        if ( $rating > 0 ) {
                $stars = '<div class="kbl3-testi__stars" aria-label="' . esc_attr( sprintf( __( 'Rated %d out of 5', 'kabel-blocks' ), $rating ) ) . '">'
                        . str_repeat( kbl3_icon_svg( 'star' ), $rating )
                        . '</div>';
        }
        $avatar = '';
        if ( ! empty( $a['avatarURL'] ) ) {
                $avatar = '<img class="kbl3-testi__avatar" src="' . esc_url( $a['avatarURL'] ) . '" alt="">';
        }

        $markup = '<figure class="' . esc_attr( $class ) . '"' . ( '' !== $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . '>'
                . $stars
                . '<blockquote class="kbl3-testi__quote">' . kbl3_rich( (string) ( $a['quote'] ?? 'The matte finish is exactly what we hoped for — two years on, the walls still look freshly painted.' ) ) . '</blockquote>'
                . '<figcaption class="kbl3-testi__who">'
                . $avatar
                . '<span class="kbl3-testi__name">' . kbl3_rich( (string) ( $a['name'] ?? 'Daniel Whitmore' ) ) . '</span>'
                . '<span class="kbl3-testi__role">' . kbl3_rich( (string) ( $a['role'] ?? 'Homeowner, Brighton' ) ) . '</span>'
                . '</figcaption></figure>';
        return kbl3_block( 'kbl/testimonial', $attrs, $markup );
}

/** Clamp a rating to 0-5 whole stars. JS twin: clampRating(). */
function kbl3_rating( $value ): int {
        $rating = (int) round( (float) $value );
        return max( 0, min( 5, $rating ) );
}

/** Testimonial root style pairs. JS twin: testiStyle(). */
function kbl3_testimonial_style( array $a ): string {
        return kbl3_style( array_merge( array(
                array( 'color', $a['color'] ?? '' ),
        ), kbl3_typography( $a, array( 'fw' => false, 'lh' => false, 'ls' => false, 'tt' => false ) ), kbl3_anim_delay( $a ), kbl3_spacing_pairs( $a ) ) );
}

/**
 * kbl/video — cover + play button; front.js swaps in the player.
 *
 * @param array $a url|coverId|coverURL|radius|shadow|anim|animDelay|hideD|hideT|hideM|mt|mb|mtM|mbM.
 */
function kbl3_video( array $a = array() ): string {
        $url      = (string) ( $a['url'] ?? '' );
        $provider = kbl3_video_provider( $url );
        $data     = wp_json_encode( array( 'provider' => $provider, 'src' => $url ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );

        $class = kbl3_cx( 'wp-block-kbl-video', 'kbl3-video', kbl3_anim( $a ), kbl3_visibility( $a ), kbl3_spacing_class( $a ) );
        $style = kbl3_style( array_merge( array(
                array( 'border-radius', ! empty( $a['radius'] ) ? $a['radius'] . 'px' : null ),
                array( 'box-shadow', kbl3_shadow( $a['shadow'] ?? '' ) ),
        ), kbl3_anim_delay( $a ), kbl3_spacing_pairs( $a ) ) );

        $attrs = array();
        if ( '' !== $url ) { $attrs['url'] = $url; }
        if ( ! empty( $a['coverId'] ) ) { $attrs['coverId'] = (int) $a['coverId']; }
        if ( ! empty( $a['coverURL'] ) ) { $attrs['coverURL'] = $a['coverURL']; }
        if ( ! empty( $a['radius'] ) ) { $attrs['radius'] = (int) $a['radius']; }
        if ( ! empty( $a['shadow'] ) ) { $attrs['shadow'] = $a['shadow']; }
        kbl3_anim_attrs( $a, $attrs );
        kbl3_visibility_attrs( $a, $attrs );
        kbl3_spacing_attrs( $a, $attrs );

        $cover = '';
        if ( ! empty( $a['coverURL'] ) ) {
                $cover = '<img class="kbl3-video__img" src="' . esc_url( $a['coverURL'] ) . '" alt="">';
        }

        $markup = '<div class="' . esc_attr( $class ) . '"' . ( '' !== $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . ' data-kbl-video="' . esc_attr( (string) $data ) . '">'
                . '<button class="kbl3-video__cover" type="button" aria-label="' . esc_attr( __( 'Play video', 'kabel-blocks' ) ) . '">'
                . $cover
                . '<span class="kbl3-video__play"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.5v13l11-6.5z"></path></svg></span>'
                . '</button></div>';
        return kbl3_block( 'kbl/video', $attrs, $markup );
}

/** Provider bucket for a video link. JS twin: videoProvider(). */
function kbl3_video_provider( string $url ): string {
        if ( preg_match( '/youtube\.com|youtu\.be/i', $url ) ) {
                return 'youtube';
        }
        if ( preg_match( '/vimeo\.com/i', $url ) ) {
                return 'vimeo';
        }
        return 'file';
}

/**
 * kbl/team — member card grid.
 *
 * @param array $a items[{name,role,photoId,photoURL,linkURL}]|columns|fontSize|fontSizeT|fontSizeM|anim|animDelay|hideD|hideT|hideM|mt|mb|mtM|mbM.
 */
function kbl3_team( array $a = array() ): string {
        $items = isset( $a['items'] ) ? (array) $a['items'] : kbl3_team_defaults();
        $cols  = isset( $a['columns'] ) ? (int) $a['columns'] : 3;

        $class = kbl3_cx(
                'wp-block-kbl-team',
                'kbl3-team',
                count( $items ) >= 3 ? 'kbl3-team--2up' : '',
                kbl3_anim( $a ),
                kbl3_visibility( $a ),
                kbl3_spacing_class( $a )
        );
        $style = kbl3_style( array_merge( array(
                array( '--kbl3-team-cols', (string) $cols ),
        ), kbl3_typography( $a, array( 'fw' => false, 'lh' => false, 'ls' => false, 'tt' => false ) ), kbl3_anim_delay( $a ), kbl3_spacing_pairs( $a ) ) );

        $attrs = array();
        if ( ! kbl3_items_equal( $items, kbl3_team_defaults() ) ) { $attrs['items'] = array_values( $items ); }
        if ( 3 !== $cols ) { $attrs['columns'] = $cols; }
        kbl3_typography_attrs( $a, $attrs, array( 'fw' => false, 'lh' => false, 'ls' => false, 'tt' => false ) );
        kbl3_anim_attrs( $a, $attrs );
        kbl3_visibility_attrs( $a, $attrs );
        kbl3_spacing_attrs( $a, $attrs );

        $cards = '';
        foreach ( $items as $item ) {
                $name     = (string) ( $item['name'] ?? '' );
                $role     = (string) ( $item['role'] ?? '' );
                $photo    = (string) ( $item['photoURL'] ?? '' );
                $link_url = (string) ( $item['linkURL'] ?? '' );

                if ( '' !== $photo ) {
                        $photo_html = '<img class="kbl3-team__img" src="' . esc_url( $photo ) . '" alt="' . esc_attr( $name ) . '">';
                } else {
                        $photo_html = '<span class="kbl3-icon kbl3-team__phicon" style="--kbl3-icon-size:44px">' . kbl3_icon_svg( 'user' ) . '</span>';
                }

                $name_html = '<h3 class="kbl3-team__name">' . kbl3_rich( $name ) . '</h3>';
                if ( '' !== $link_url ) {
                        $name_html = '<a class="kbl3-team__link" href="' . esc_url( $link_url ) . '">' . $name_html . '</a>';
                }

                $cards .= '<div class="kbl3-team__card">'
                        . '<div class="kbl3-team__photo">' . $photo_html . '</div>'
                        . $name_html
                        . '<p class="kbl3-team__role">' . kbl3_rich( $role ) . '</p>'
                        . '</div>';
        }

        $markup = '<div class="' . esc_attr( $class ) . '" style="' . esc_attr( $style ) . '">' . $cards . '</div>';
        return kbl3_block( 'kbl/team', $attrs, $markup );
}

/** Default team members. JS twin: DEFAULT_ITEMS in blocks/team.js. */
function kbl3_team_defaults(): array {
        return array(
                array( 'name' => 'Elena Marsh', 'role' => 'Lead Color Consultant', 'photoId' => 0, 'photoURL' => '', 'linkURL' => '' ),
                array( 'name' => 'Victor Hale', 'role' => 'Coatings Engineer', 'photoId' => 0, 'photoURL' => '', 'linkURL' => '' ),
                array( 'name' => 'Ines Rocha', 'role' => 'Project Manager', 'photoId' => 0, 'photoURL' => '', 'linkURL' => '' ),
        );
}

/**
 * kbl/pricing — one package price card.
 *
 * @param array $a planName|price|period|features[]|btnLabel|btnURL|featured|footnote|bgColor|radius|shadow|fontSize|fontSizeT|fontSizeM|anim|animDelay|hideD|hideT|hideM|mt|mb|mtM|mbM.
 */
function kbl3_pricing( array $a = array() ): string {
        $featured   = ! empty( $a['featured'] );
        $features   = isset( $a['features'] ) ? (array) $a['features'] : kbl3_pricing_default_features();
        $btn_label  = (string) ( $a['btnLabel'] ?? 'Request this package' );
        $style      = kbl3_price_style( $a );
        $class      = kbl3_cx(
                'wp-block-kbl-pricing',
                'kbl3-price',
                $featured ? 'is-featured' : '',
                kbl3_anim( $a ),
                kbl3_visibility( $a ),
                kbl3_spacing_class( $a )
        );

        $attrs = array();
        if ( ! kbl3_features_equal( $features, kbl3_pricing_default_features() ) ) { $attrs['features'] = array_values( $features ); }
        if ( ! empty( $a['btnURL'] ) ) { $attrs['btnURL'] = $a['btnURL']; }
        if ( $featured ) { $attrs['featured'] = true; }
        if ( ! empty( $a['bgColor'] ) ) { $attrs['bgColor'] = $a['bgColor']; }
        if ( ! empty( $a['radius'] ) ) { $attrs['radius'] = (int) $a['radius']; }
        if ( ! empty( $a['shadow'] ) ) { $attrs['shadow'] = $a['shadow']; }
        kbl3_typography_attrs( $a, $attrs, array( 'fw' => false, 'lh' => false, 'ls' => false, 'tt' => false ) );
        kbl3_anim_attrs( $a, $attrs );
        kbl3_visibility_attrs( $a, $attrs );
        kbl3_spacing_attrs( $a, $attrs );

        $badge = '';
        if ( $featured ) {
                $badge = '<span class="kbl3-price__badge">' . kbl3_rich( __( 'Most popular', 'kabel-blocks' ) ) . '</span>';
        }
        $lis = '';
        foreach ( $features as $feature ) {
                $lis .= '<li>' . kbl3_rich( (string) $feature ) . '</li>';
        }
        $btn = '';
        if ( '' !== $btn_label ) {
                $btn = '<a class="kbl3-btn kbl3-btn--gold kbl3-btn--s kbl3-price__btn"'
                        . ( ! empty( $a['btnURL'] ) ? ' href="' . esc_url( $a['btnURL'] ) . '"' : '' )
                        . '>' . kbl3_rich( $btn_label ) . '</a>';
        }

        $markup = '<div class="' . esc_attr( $class ) . '"' . ( '' !== $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . '>'
                . $badge
                . '<h3 class="kbl3-price__plan">' . kbl3_rich( (string) ( $a['planName'] ?? 'Signature Package' ) ) . '</h3>'
                . '<div class="kbl3-price__amount">'
                . '<span class="kbl3-price__num">' . kbl3_rich( (string) ( $a['price'] ?? '38' ) ) . '</span>'
                . '<span class="kbl3-price__period">' . kbl3_rich( (string) ( $a['period'] ?? '/ 5L' ) ) . '</span>'
                . '</div>'
                . '<ul class="kbl3-list kbl3-list--check kbl3-price__features">' . $lis . '</ul>'
                . $btn
                . '<p class="kbl3-price__foot">' . kbl3_rich( (string) ( $a['footnote'] ?? '' ) ) . '</p>'
                . '</div>';
        return kbl3_block( 'kbl/pricing', $attrs, $markup );
}

/** Default feature list. JS twin: DEFAULT_FEATURES in blocks/pricing.js. */
function kbl3_pricing_default_features(): array {
        return array(
                'Color consultation included',
                'Two-coat premium application',
                '5-year finish warranty',
                'Free touch-up kit',
        );
}

/** Loose equality for feature lists (comment attrs keep only non-defaults). */
function kbl3_features_equal( array $features, array $defaults ): bool {
        $norm = array();
        foreach ( $features as $feature ) {
                $norm[] = (string) $feature;
        }
        return $norm === array_values( $defaults );
}

/** Pricing root style pairs. JS twin: priceStyle(). */
function kbl3_price_style( array $a ): string {
        return kbl3_style( array_merge( array(
                array( 'background-color', $a['bgColor'] ?? '' ),
                array( 'border-radius', ! empty( $a['radius'] ) ? $a['radius'] . 'px' : null ),
                array( 'box-shadow', kbl3_shadow( $a['shadow'] ?? '' ) ),
        ), kbl3_typography( $a, array( 'fw' => false, 'lh' => false, 'ls' => false, 'tt' => false ) ), kbl3_anim_delay( $a ), kbl3_spacing_pairs( $a ) ) );
}
