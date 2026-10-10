<?php
/**
 * v13 header-seed replay — the v14 upgrade path's "untouched?" oracle.
 *
 * The v14 header seed changed shape (kbl/header chrome container, top
 * contact bar, language pills, menu-driven kbl/navigation that reads
 * 外观 → 菜单). Sites that seeded the v13 header carry markup the v14
 * editor can no longer produce, so on the first admin visit after the
 * upgrade we regenerate the v13 seed HERE and compare byte-for-byte:
 *
 *   stored content === replayed v13 seed → the owner never touched it
 *                                        → silently re-seed the v14 template
 *   anything else                       → customized → keep it, surface a
 *                                        one-click Reset (Blocksy-style)
 *
 * Everything lives under the KabelLegacyV13 namespace so the v14 emitter's
 * identically named functions are never redeclared. The extracts are
 * dependency-closed verbatim copies from the v3.5.0 emitter (plugin) and
 * site-parts (theme 1.6.0) — do NOT hand-edit them; the byte-twin contract
 * only holds while they stay verbatim.
 *
 * @package Kabel
 */

namespace KabelLegacyV13 {

defined( 'ABSPATH' ) || exit;

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

function kbl3_rich( string $s ): string {
        return preg_replace(
                '/&(?!(?:[a-zA-Z][a-zA-Z0-9]*|#[0-9]+|#x[0-9a-fA-F]+);)/',
                '&amp;',
                $s
        ) ?? $s;
}

function kbl3_cx( ...$parts ): string {
        $out = array();
        foreach ( $parts as $p ) {
                if ( is_string( $p ) && '' !== $p ) {
                        $out[] = $p;
                }
        }
        return implode( ' ', $out );
}

function kbl3_block( string $name, array $attrs, string $inner ): string {
        if ( '' === $inner ) {
                return '<!-- wp:' . $name . kbl3_json( $attrs ) . ' /-->';
        }
        return '<!-- wp:' . $name . kbl3_json( $attrs ) . " -->\n" . $inner . "\n<!-- /wp:" . $name . ' -->';
}

function kbl3_visibility( array $a ): string {
        $c = array();
        if ( ! empty( $a['hideD'] ) ) { $c[] = 'kbl3-hide-d'; }
        if ( ! empty( $a['hideT'] ) ) { $c[] = 'kbl3-hide-t'; }
        if ( ! empty( $a['hideM'] ) ) { $c[] = 'kbl3-hide-m'; }
        return implode( ' ', $c );
}

function kbl3_visibility_attrs( array $a, array &$attrs ): void {
        if ( ! empty( $a['hideD'] ) ) { $attrs['hideD'] = true; }
        if ( ! empty( $a['hideT'] ) ) { $attrs['hideT'] = true; }
        if ( ! empty( $a['hideM'] ) ) { $attrs['hideM'] = true; }
}

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

function kbl3_spacing_attrs( array $a, array &$attrs ): void {
        foreach ( array( 'mt', 'mb', 'mr', 'ml', 'mtM', 'mbM', 'pt', 'pr', 'pb', 'pl' ) as $k ) {
                if ( isset( $a[ $k ] ) ) { $attrs[ $k ] = (int) $a[ $k ]; }
        }
}

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

function kbl3_navigation( array $a = array() ): string {
        $items = isset( $a['items'] ) ? (array) $a['items'] : kbl3_navigation_defaults();
        $align = $a['align'] ?? '';
        $mbp   = (string) ( $a['mobileBreak'] ?? '1024' );
        $gap   = isset( $a['gap'] ) ? (int) $a['gap'] : 4;

        $class = kbl3_cx(
                'wp-block-kbl-navigation',
                'kbl3-nav',
                'center' === $align ? 'is-center' : '',
                'right' === $align ? 'is-right' : '',
                'kbl3-nav--mbp-' . $mbp,
                kbl3_visibility( $a ),
                kbl3_spacing_class( $a )
        );
        $style = kbl3_style( array_merge( array(
                array( '--kbl3-nav-gap', $gap . 'px' ),
                array( '--kbl3-nav-link', $a['textColor'] ?? '' ),
                array( '--kbl3-nav-hover', $a['hoverColor'] ?? '' ),
                array( '--kbl3-nav-accent', $a['accentColor'] ?? '' ),
                array( '--kbl3-nav-sub-bg', $a['subBgColor'] ?? '' ),
                array( '--kbl3-nav-sub-link', $a['subTextColor'] ?? '' ),
        ), kbl3_typography( $a, array( 'lh' => false ) ), kbl3_spacing_pairs( $a ) ) );

        $attrs = array();
        if ( ! kbl3_navigation_items_equal( $items, kbl3_navigation_defaults() ) ) {
                $attrs['items'] = array_values( $items );
        }
        if ( '' !== $align ) { $attrs['align'] = $align; }
        if ( 4 !== $gap ) { $attrs['gap'] = $gap; }
        if ( '1024' !== $mbp ) { $attrs['mobileBreak'] = $mbp; }
        foreach ( array( 'textColor', 'hoverColor', 'accentColor', 'subBgColor', 'subTextColor' ) as $key ) {
                if ( ! empty( $a[ $key ] ) ) { $attrs[ $key ] = $a[ $key ]; }
        }
        kbl3_typography_attrs( $a, $attrs, array( 'lh' => false ) );
        kbl3_visibility_attrs( $a, $attrs );
        kbl3_spacing_attrs( $a, $attrs );

        $list = '';
        foreach ( $items as $item ) {
                $item = (array) $item;
                $subs = isset( $item['sub'] ) && is_array( $item['sub'] ) ? $item['sub'] : array();

                $link_attrs = ! empty( $item['newTab'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';
                $list .= '<li class="' . esc_attr( kbl3_cx( 'kbl3-nav__item', $subs ? 'has-sub' : '', ! empty( $item['mega'] ) ? 'is-mega' : '' ) ) . '">'
                        . '<a class="kbl3-nav__link" href="' . esc_url( kbl3_navigation_href( $item ) ) . '"' . $link_attrs . '>'
                        . kbl3_rich( (string) ( $item['label'] ?? '' ) )
                        . '</a>';

                if ( $subs ) {
                        $list .= '<ul class="' . esc_attr( kbl3_cx( 'kbl3-nav__sub', ! empty( $item['mega'] ) ? 'kbl3-nav__sub--mega' : '' ) ) . '">';
                        foreach ( $subs as $sub ) {
                                $sub        = (array) $sub;
                                $sub_attrs  = ! empty( $sub['newTab'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';
                                $list      .= '<li class="kbl3-nav__subitem">'
                                        . '<a class="kbl3-nav__sublink" href="' . esc_url( kbl3_navigation_href( $sub ) ) . '"' . $sub_attrs . '>'
                                        . kbl3_rich( (string) ( $sub['label'] ?? '' ) )
                                        . '</a></li>';
                        }
                        $list .= '</ul>';
                }

                $list .= '</li>';
        }

        $markup = '<nav class="' . esc_attr( $class ) . '" style="' . esc_attr( $style ) . '" data-kbl-nav="">'
                . '<button class="kbl3-nav__toggle" type="button" aria-expanded="false">'
                . '<span class="kbl3-nav__bars" aria-hidden="true"><span></span><span></span><span></span></span>'
                . '<span class="screen-reader-text">Menu</span>'
                . '</button>'
                . '<ul class="kbl3-nav__list">' . $list . '</ul>'
                . '</nav>';
        return kbl3_block( 'kbl/navigation', $attrs, $markup );
}

function kbl3_navigation_href( array $item ): string {
        $url = (string) ( $item['url'] ?? '' );
        return '' !== $url ? $url : '#';
}

function kbl3_navigation_defaults(): array {
        return array(
                array( 'label' => 'Home', 'url' => '/', 'newTab' => false, 'sub' => array() ),
                array( 'label' => 'About', 'url' => '/about/', 'newTab' => false, 'sub' => array() ),
                array( 'label' => 'Contact', 'url' => '/contact/', 'newTab' => false, 'sub' => array() ),
        );
}

function kbl3_navigation_items_equal( array $items, array $defaults ): bool {
        $norm = function ( array $list ): array {
                $out = array();
                foreach ( $list as $item ) {
                        $item = (array) $item;
                        $subs = array();
                        foreach ( ( array ) ( $item['sub'] ?? array() ) as $sub ) {
                                $sub    = (array) $sub;
                                $subs[] = array(
                                        'label'   => (string) ( $sub['label'] ?? '' ),
                                        'url'     => (string) ( $sub['url'] ?? '' ),
                                        'newTab'  => ! empty( $sub['newTab'] ),
                                );
                        }
                        $out[] = array(
                                'label'   => (string) ( $item['label'] ?? '' ),
                                'url'     => (string) ( $item['url'] ?? '' ),
                                'newTab'  => ! empty( $item['newTab'] ),
                                'mega'    => ! empty( $item['mega'] ),
                                'sub'     => $subs,
                        );
                }
                return $out;
        };
        return $norm( $items ) === $norm( $defaults );
}

function kbl3_anim_attrs( array $a, array &$attrs ): void {
        if ( ! empty( $a['anim'] ) ) { $attrs['anim'] = $a['anim']; }
        if ( ! empty( $a['animDelay'] ) ) { $attrs['animDelay'] = (int) $a['animDelay']; }
}

function kbl3_json( array $attrs ): string {
        if ( array() === $attrs ) {
                return '';
        }
        $json = wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        return false === $json ? '' : ' ' . $json;
}

function kbl3_shadow( string $key ): ?string {
        $shadows = array(
                'soft'   => '0 2px 14px rgba(0, 0, 0, 0.18)',
                'medium' => '0 12px 32px rgba(0, 0, 0, 0.30)',
                'strong' => '0 26px 64px rgba(0, 0, 0, 0.45)',
        );
        return $shadows[ $key ] ?? null;
}

function kbl3_anim_delay( array $a ): array {
        if ( empty( $a['anim'] ) || empty( $a['animDelay'] ) ) {
                return array();
        }
        return array( array( '--kbl3-anim-d', (int) $a['animDelay'] . 'ms' ) );
}

function kbl3_gradient_attrs( array $a, array &$attrs ): void {
        if ( ! empty( $a['gFrom'] ) ) { $attrs['gFrom'] = $a['gFrom']; }
        if ( ! empty( $a['gTo'] ) ) { $attrs['gTo'] = $a['gTo']; }
        if ( isset( $a['gAngle'] ) && 135 !== (int) $a['gAngle'] ) { $attrs['gAngle'] = (int) $a['gAngle']; }
}

function kbl3_gradient( array $a ): ?string {
        if ( ! empty( $a['bgURL'] ) || empty( $a['gFrom'] ) || empty( $a['gTo'] ) ) {
                return null;
        }
        $angle = isset( $a['gAngle'] ) ? (int) $a['gAngle'] : 135;
        return 'linear-gradient(' . $angle . 'deg, ' . $a['gFrom'] . ', ' . $a['gTo'] . ')';
}

function kbl3_anim( array $a ): string {
        if ( empty( $a['anim'] ) ) {
                return '';
        }
        return 'kbl3-anim kbl3-anim--' . $a['anim'];
}

function kbl3_overlay_value( $overlay ): string {
        $overlay = ( null === $overlay || '' === $overlay ) ? 45 : (float) $overlay;
        $value   = $overlay / 100;
        $str     = (string) $value;
        // Match JS String(): trailing zero decimals trimmed by PHP float cast already.
        return $str;
}

function kbl3_box_attrs( array $a, array &$attrs ): void {
        if ( ! empty( $a['contentWidth'] ) ) { $attrs['contentWidth'] = (int) $a['contentWidth']; }
        if ( ! empty( $a['blockAlign'] ) ) { $attrs['blockAlign'] = $a['blockAlign']; }
}

function kbl3_box_pairs( array $a ): array {
        $align = $a['blockAlign'] ?? '';
        return array(
                array( 'max-width', ! empty( $a['contentWidth'] ) ? $a['contentWidth'] . 'px' : null ),
                array( 'margin-left', ( 'center' === $align || 'right' === $align ) ? 'auto' : null ),
                array( 'margin-right', 'center' === $align ? 'auto' : null ),
        );
}


function kabel_legacy_seed_header(): string {
        $products = (string) ( get_post_type_archive_link( 'product' ) ?: home_url( '/products/' ) );
        $projects = (string) ( get_post_type_archive_link( 'projects' ) ?: home_url( '/projects/' ) );

        $nav_items = array(
                array( 'label' => 'HOME', 'url' => home_url( '/' ) ),
                array(
                        'label' => 'ABOUT',
                        'url'   => home_url( '/about/' ),
                        'sub'   => array(
                                array( 'label' => 'About Us', 'url' => home_url( '/about/' ) ),
                                array( 'label' => 'ECO Friendly', 'url' => home_url( '/eco-friendly/' ) ),
                                array( 'label' => 'Designer', 'url' => home_url( '/designer/' ) ),
                        ),
                ),
                array(
                        'label' => 'PRODUCTS',
                        'url'   => $products,
                        'mega'  => true,
                        'sub'   => array(
                                array( 'label' => 'All paint series', 'url' => $products ),
                                array( 'label' => 'Velvet Texture', 'url' => home_url( '/product-series/velvet-texture/' ) ),
                                array( 'label' => 'Lime Plaster', 'url' => home_url( '/product-series/lime-plaster/' ) ),
                                array( 'label' => 'Stone Texture', 'url' => home_url( '/product-series/stone-texture/' ) ),
                                array( 'label' => 'Microcement', 'url' => home_url( '/product-series/microcement/' ) ),
                        ),
                ),
                array(
                        'label' => 'COLORS',
                        'url'   => '#',
                        'sub'   => array(
                                array( 'label' => 'Morandi Colors', 'url' => home_url( '/morandi-colors/' ) ),
                                array( 'label' => 'Van Gogh Color', 'url' => home_url( '/van-gogh-color/' ) ),
                                array( 'label' => 'Macaron colors', 'url' => home_url( '/macaron-colors/' ) ),
                                array( 'label' => 'Museum Collection Colors', 'url' => home_url( '/museum-collection-colors/' ) ),
                        ),
                ),
                array(
                        'label' => 'PROJECTS',
                        'url'   => $projects,
                        'sub'   => array(
                                array( 'label' => 'Home Renovation Projects', 'url' => home_url( '/category/home-renovation-projects/' ) ),
                                array( 'label' => 'Engineering Projects', 'url' => home_url( '/category/engineering-projects/' ) ),
                        ),
                ),
                array(
                        'label' => 'PROJECT PROCUREMENT',
                        'url'   => '#',
                        'sub'   => array(
                                array( 'label' => 'Project Procurement', 'url' => home_url( '/project-procurement/' ) ),
                                array( 'label' => 'Customize', 'url' => home_url( '/customize/' ) ),
                        ),
                ),
                array( 'label' => 'DISTRIBUTOR', 'url' => home_url( '/distributor/' ) ),
                array(
                        'label' => 'NEWS',
                        'url'   => home_url( '/blog/' ),
                        'sub'   => array(
                                array( 'label' => 'Company News', 'url' => home_url( '/category/company-news/' ) ),
                                array( 'label' => 'Industry News', 'url' => home_url( '/category/industry-news/' ) ),
                                array( 'label' => 'Knowledge', 'url' => home_url( '/category/knowledge/' ) ),
                                array( 'label' => 'Faq', 'url' => home_url( '/faq/' ) ),
                        ),
                ),
                array( 'label' => 'CONTACT', 'url' => home_url( '/contact/' ) ),
        );

        $left = kbl3_column( array(
                'width'     => 22,
                'textColor' => '#FFFFFF',
        ), kbl3_heading( array(
                'level'   => 4,
                'eyebrow' => true,
                'text'    => 'KABEL PAINTS',
        ) ) );

        $right = kbl3_column( array(
                'width'     => 78,
                'textColor' => '#FFFFFF',
        ), kbl3_navigation( array(
                'align'       => 'right',
                'mobileBreak' => '1280',
                'items'       => $nav_items,
        ) ) );

        return trim( kbl3_columns( array(
                'gap'  => 24,
                'vGap' => 8,
        ), $left . "\n\n" . $right ) );
}

}

namespace {

        /**
         * Replays the v13 header seed with this site's own URLs.
         *
         * @return string The v13 seed ('' when the replay can't run).
         */
        function kabel_legacy_header_seed_v13(): string {
                static $seed = null;
                if ( null === $seed ) {
                        try {
                                $seed = \KabelLegacyV13\kabel_legacy_seed_header();
                        } catch ( \Throwable $e ) {
                                $seed = '';
                        }
                }
                return $seed;
        }
}

