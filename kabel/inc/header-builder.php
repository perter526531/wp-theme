<?php
/**
 * Header Builder — the Kabel visual header (v1.7).
 *
 * Architecture: a structured JSON model (option `kabel_header_builder`) is
 * rendered natively by PHP (no blocks involved, immune to block validation)
 * and managed inside the Customizer (inc/customizer-hb.php registers the
 * `kabel_hb_builder` section that mounts the React app, built to
 * assets/js/hb-customizer.js). Saving is the Customizer's own Publish flow
 * (postMessage transport + kabel_hb_customize_sanitize), and the live preview
 * is bridged by assets/js/hb-preview.js through the
 * `kabel/v1/header-builder/render` REST endpoint below.
 *
 * Front-end markup namespace: `kbhb-` (styled by assets/css/hb.css, wired by
 * assets/js/hb.js — re-initializable via window.kabelHBInit() after the
 * Customizer preview swaps the header DOM). The render priority lives in
 * header.php: builder → classic header (two levels).
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------
 * Model
 * ------------------------------------------------------------------- */

/**
 * The 11 element types the builder understands.
 *
 * @return array<string>
 */
function kabel_hb_element_types(): array {
        return array( 'logo', 'menu', 'button', 'search', 'social', 'html', 'contact', 'text', 'lang', 'account', 'cart' );
}

/**
 * Default settings per element type (full shape — the sanitizer merges onto
 * these so the stored model always has every key the renderer reads).
 *
 * @param string $type Element type.
 * @return array
 */
function kabel_hb_element_defaults( string $type ): array {
        $defaults = array(
                'logo'    => array(
                        'source'      => 'site',
                        'text'        => '',
                        'tagline'     => '',
                        'taglineShow' => true,
                        'imgId'       => 0,
                        'imgUrl'      => '',
                        'imgWidth'    => 140,
                ),
                'menu'    => array(
                        'menuId'       => 0,
                        'fontSize'     => 15,
                        'gap'          => 28,
                        'align'        => 'left',
                        'color'        => '',
                        'hoverColor'   => '',
                        'style'        => 'plain',
                        'subIndicator' => true,
                ),
                'button'  => array(
                        'text'    => 'Contact',
                        'url'     => '',
                        'variant' => 'gold',
                        'size'    => 'm',
                        'target'  => false,
                ),
                'search'  => array(
                        'style'       => 'icon',
                        'placeholder' => 'Search…',
                        'fieldWidth'  => 180,
                ),
                'social'  => array(
                        'items' => array(),
                        'size'  => 18,
                        'gap'   => 10,
                ),
                'html'    => array(
                        'content' => '',
                ),
                'contact' => array(
                        'phone'       => '',
                        'email'       => '',
                        'address'     => '',
                        'showPhone'   => true,
                        'showEmail'   => true,
                        'showAddress' => false,
                ),
                'text'    => array(
                        'content' => '',
                        'size'    => 14,
                        'color'   => '',
                ),
                'lang'    => array(
                        'style' => 'code',
                ),
                'account' => array(
                        'loginText' => 'Login',
                        'url'       => '',
                ),
                'cart'    => array(
                        'showCount' => true,
                ),
        );
        return isset( $defaults[ $type ] ) ? $defaults[ $type ] : array();
}

/**
 * The default model: one main row (logo left, menu + gold CTA right), a mobile
 * bar with the logo and a burger opening a right-side drawer (menu + CTA).
 *
 * @return array
 */
function kabel_hb_defaults(): array {
        return array(
                'version' => 1,
                'rows'    => array(
                        array(
                                'id'       => 'r-1',
                                'settings' => array(
                                        'bg'          => '',
                                        'textColor'   => '',
                                        'padY'        => 16,
                                        'borderBottom' => false,
                                        'sticky'      => false,
                                        'transparent' => false,
                                ),
                                'zones'    => array(
                                        array( 'e-logo' ),
                                        array(),
                                        array( 'e-menu', 'e-cta' ),
                                ),
                        ),
                ),
                'mobile'  => array(
                        'zones'        => array( array( 'e-logo' ), array(), array() ),
                        'offcanvas'    => array( 'e-menu', 'e-cta' ),
                        'offcanvasDir' => 'right',
                        'trigger'      => 'right',
                        'breakpoint'   => 780,
                ),
                'elements' => array(
                        'e-logo' => array(
                                'type'     => 'logo',
                                'settings' => kabel_hb_element_defaults( 'logo' ),
                        ),
                        'e-menu' => array(
                                'type'     => 'menu',
                                'settings' => kabel_hb_element_defaults( 'menu' ),
                        ),
                        'e-cta'  => array(
                                'type'     => 'button',
                                'settings' => array_merge( kabel_hb_element_defaults( 'button' ), array(
                                        'url' => home_url( '/contact/' ),
                                ) ),
                        ),
                ),
        );
}

/**
 * The effective model, or null when the builder is off.
 *
 * @return array|null
 */
function kabel_hb_get() {
        $option = get_option( 'kabel_header_builder', '' );
        if ( ! is_string( $option ) || '' === $option ) {
                return null;
        }

        $model = json_decode( $option, true );
        if ( ! is_array( $model ) ) {
                return null;
        }
        if ( ! isset( $model['rows'] ) || ! is_array( $model['rows'] ) || empty( $model['rows'] ) ) {
                return null;
        }
        return $model;
}

/**
 * Will the builder render a header on this request? (Enqueue gate.)
 */
function kabel_hb_active(): bool {
        return null !== kabel_hb_get();
}

/**
 * Which header source is live right now: builder | classic (two-level stack
 * since v1.7 — the block-header middle layer is gone).
 */
function kabel_hb_live_source(): string {
        return kabel_hb_active() ? 'builder' : 'classic';
}

/* ---------------------------------------------------------------------
 * Sanitizer — the security boundary (fail closed: unknown input is dropped)
 * ------------------------------------------------------------------- */

/**
 * Clamp a numeric setting into [min, max].
 *
 * @param mixed $value   Raw value.
 * @param int   $min     Minimum.
 * @param int   $max     Maximum.
 * @param int   $default Used when the value is not numeric.
 * @return int
 */
function kabel_hb_clamp_num( $value, int $min, int $max, int $default ): int {
        if ( is_numeric( $value ) ) {
                return max( $min, min( $max, (int) round( (float) $value ) ) );
        }
        return $default;
}

/**
 * A hex color (#rgb #rgba #rrggbb #rrggbbaa) or ''.
 */
function kabel_hb_clean_color( $value ): string {
        $value = trim( (string) $value );
        if ( '' === $value ) {
                return '';
        }
        if ( 1 === preg_match( '/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $value ) ) {
                return $value;
        }
        return '';
}

/**
 * An allow-listed enum value, or the default.
 */
function kabel_hb_clean_enum( $value, array $allowed, $default ) {
        return in_array( $value, $allowed, true ) ? $value : $default;
}

/**
 * A builder id (element or row): ^[a-z0-9-]{2,40}$ or ''.
 */
function kabel_hb_clean_id( $value ): string {
        $value = (string) $value;
        if ( 1 === preg_match( '/^[a-z0-9-]{2,40}$/', $value ) ) {
                return $value;
        }
        return '';
}

/**
 * A plain-text setting (tags stripped, length capped).
 */
function kabel_hb_clean_text( $value, int $max = 200 ): string {
        $text = wp_strip_all_tags( (string) $value );
        if ( function_exists( 'mb_substr' ) ) {
                return mb_substr( $text, 0, $max );
        }
        return substr( $text, 0, $max );
}

/**
 * The extended KSES table for the html element (accounts without
 * unfiltered_html get their markup reduced to this; admins keep raw output —
 * same capability that could edit theme files anyway).
 *
 * @return array
 */
function kabel_hb_kses_extended(): array {
        return array(
                'a'          => array( 'href' => true, 'title' => true, 'target' => true, 'rel' => true, 'class' => true ),
                'abbr'       => array( 'title' => true ),
                'b'          => array(),
                'strong'     => array(),
                'i'          => array(),
                'em'         => array(),
                'u'          => array(),
                's'          => array(),
                'mark'       => array(),
                'small'      => array(),
                'sub'        => array(),
                'sup'        => array(),
                'code'       => array(),
                'br'         => array(),
                'hr'         => array(),
                'p'          => array( 'class' => true, 'style' => true ),
                'span'       => array( 'class' => true, 'style' => true ),
                'div'        => array( 'class' => true, 'style' => true ),
                'h1'         => array( 'class' => true, 'style' => true ),
                'h2'         => array( 'class' => true, 'style' => true ),
                'h3'         => array( 'class' => true, 'style' => true ),
                'h4'         => array( 'class' => true, 'style' => true ),
                'h5'         => array( 'class' => true, 'style' => true ),
                'h6'         => array( 'class' => true, 'style' => true ),
                'ul'         => array( 'class' => true ),
                'ol'         => array( 'class' => true, 'start' => true ),
                'li'         => array( 'class' => true ),
                'blockquote' => array( 'cite' => true ),
                'cite'       => array(),
                'img'        => array( 'src' => true, 'srcset' => true, 'sizes' => true, 'alt' => true, 'width' => true, 'height' => true, 'class' => true, 'loading' => true ),
                'figure'     => array( 'class' => true ),
                'figcaption' => array( 'class' => true ),
                'time'       => array( 'datetime' => true ),
                'table'      => array( 'class' => true ),
                'thead'      => array(),
                'tbody'      => array(),
                'tr'         => array(),
                'td'         => array( 'colspan' => true, 'rowspan' => true ),
                'th'         => array( 'scope' => true, 'colspan' => true, 'rowspan' => true ),
                'svg'        => array( 'viewbox' => true, 'xmlns' => true, 'class' => true, 'aria-hidden' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true ),
                'path'       => array( 'd' => true, 'fill' => true, 'stroke' => true ),
                'circle'     => array( 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true ),
                'rect'       => array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'fill' => true, 'stroke' => true ),
        );
}

/**
 * Sanitize one element's settings against the per-type whitelist.
 *
 * @param string $type     Element type.
 * @param array  $settings Raw settings.
 * @return array Clean settings (full shape).
 */
function kabel_hb_sanitize_settings( string $type, array $settings ): array {
        $d = kabel_hb_element_defaults( $type );

        switch ( $type ) {
                case 'logo':
                        $d['source']      = kabel_hb_clean_enum( $settings['source'] ?? '', array( 'site', 'text', 'image' ), 'site' );
                        $d['text']        = kabel_hb_clean_text( $settings['text'] ?? '', 120 );
                        $d['tagline']     = kabel_hb_clean_text( $settings['tagline'] ?? '', 160 );
                        $d['taglineShow'] = (bool) ( $settings['taglineShow'] ?? true );
                        $img_id           = $settings['imgId'] ?? 0;
                        $d['imgId']       = is_numeric( $img_id ) ? max( 0, (int) $img_id ) : 0;
                        $d['imgUrl']      = esc_url_raw( trim( (string) ( $settings['imgUrl'] ?? '' ) ) );
                        $d['imgWidth']    = kabel_hb_clamp_num( $settings['imgWidth'] ?? 140, 40, 400, 140 );
                        break;

                case 'menu':
                        $raw_menu_id = $settings['menuId'] ?? 0;
                        $menu_id     = is_numeric( $raw_menu_id ) ? (int) $raw_menu_id : 0;
                        if ( $menu_id > 0 ) {
                                $menu_id = kabel_hb_menu_exists( $menu_id ) ? $menu_id : 0;
                        }
                        $d['menuId']       = $menu_id;
                        $d['fontSize']     = kabel_hb_clamp_num( $settings['fontSize'] ?? 15, 10, 32, 15 );
                        $d['gap']          = kabel_hb_clamp_num( $settings['gap'] ?? 28, 0, 96, 28 );
                        $d['align']        = kabel_hb_clean_enum( $settings['align'] ?? '', array( 'left', 'center', 'right' ), 'left' );
                        $d['color']        = kabel_hb_clean_color( $settings['color'] ?? '' );
                        $d['hoverColor']   = kabel_hb_clean_color( $settings['hoverColor'] ?? '' );
                        $d['style']        = kabel_hb_clean_enum( $settings['style'] ?? '', array( 'plain', 'underline', 'pill' ), 'plain' );
                        $d['subIndicator'] = (bool) ( $settings['subIndicator'] ?? true );
                        break;

                case 'button':
                        $d['text']    = kabel_hb_clean_text( $settings['text'] ?? '', 80 );
                        $d['url']     = esc_url_raw( trim( (string) ( $settings['url'] ?? '' ) ) );
                        $d['variant'] = kabel_hb_clean_enum( $settings['variant'] ?? '', array( 'gold', 'outline', 'ghost', 'dark', 'light' ), 'gold' );
                        $d['size']    = kabel_hb_clean_enum( $settings['size'] ?? '', array( 's', 'm' ), 'm' );
                        $d['target']  = (bool) ( $settings['target'] ?? false );
                        break;

                case 'search':
                        $d['style']       = kabel_hb_clean_enum( $settings['style'] ?? '', array( 'icon', 'field' ), 'icon' );
                        $d['placeholder'] = kabel_hb_clean_text( $settings['placeholder'] ?? '', 80 );
                        $d['fieldWidth']  = kabel_hb_clamp_num( $settings['fieldWidth'] ?? 180, 120, 420, 180 );
                        break;

                case 'social':
                        $items = array();
                        if ( isset( $settings['items'] ) && is_array( $settings['items'] ) ) {
                                foreach ( $settings['items'] as $item ) {
                                        if ( ! is_array( $item ) ) {
                                                continue;
                                        }
                                        $icon = kabel_hb_clean_enum( $item['icon'] ?? '', array( 'whatsapp', 'facebook', 'instagram', 'linkedin', 'youtube', 'x', 'tiktok', 'email', 'phone' ), '' );
                                        if ( '' === $icon ) {
                                                continue;
                                        }
                                        $items[] = array(
                                                'icon' => $icon,
                                                'url'  => esc_url_raw( trim( (string) ( $item['url'] ?? '' ) ) ),
                                        );
                                }
                        }
                        $d['items'] = $items;
                        $d['size']  = kabel_hb_clamp_num( $settings['size'] ?? 18, 12, 32, 18 );
                        $d['gap']   = kabel_hb_clamp_num( $settings['gap'] ?? 10, 4, 40, 10 );
                        break;

                case 'html':
                        $content      = (string) ( $settings['content'] ?? '' );
                        $d['content'] = current_user_can( 'unfiltered_html' ) ? $content : wp_kses( $content, kabel_hb_kses_extended() );
                        break;

                case 'contact':
                        $d['phone']       = kabel_hb_clean_text( $settings['phone'] ?? '', 40 );
                        $d['email']       = kabel_hb_clean_text( $settings['email'] ?? '', 120 );
                        $d['address']     = kabel_hb_clean_text( $settings['address'] ?? '', 200 );
                        $d['showPhone']   = (bool) ( $settings['showPhone'] ?? true );
                        $d['showEmail']   = (bool) ( $settings['showEmail'] ?? true );
                        $d['showAddress'] = (bool) ( $settings['showAddress'] ?? false );
                        break;

                case 'text':
                        $d['content'] = wp_kses_post( (string) ( $settings['content'] ?? '' ) );
                        $d['size']    = kabel_hb_clamp_num( $settings['size'] ?? 14, 10, 40, 14 );
                        $d['color']   = kabel_hb_clean_color( $settings['color'] ?? '' );
                        break;

                case 'lang':
                        $d['style'] = kabel_hb_clean_enum( $settings['style'] ?? '', array( 'code', 'name' ), 'code' );
                        break;

                case 'account':
                        $d['loginText'] = kabel_hb_clean_text( $settings['loginText'] ?? '', 60 );
                        $d['url']       = esc_url_raw( trim( (string) ( $settings['url'] ?? '' ) ) );
                        break;

                case 'cart':
                        $d['showCount'] = (bool) ( $settings['showCount'] ?? true );
                        break;
        }

        return $d;
}

/**
 * Does a nav menu with this id exist? (Used by the sanitizer.)
 */
function kabel_hb_menu_exists( int $menu_id ): bool {
        foreach ( (array) wp_get_nav_menus() as $menu ) {
                $term_id = 0;
                if ( is_object( $menu ) && isset( $menu->term_id ) ) {
                        $term_id = (int) $menu->term_id;
                } elseif ( is_array( $menu ) && isset( $menu['term_id'] ) ) {
                        $term_id = (int) $menu['term_id'];
                }
                if ( $menu_id === $term_id ) {
                        return true;
                }
        }
        return false;
}

/**
 * Sanitize one zone (an ordered list of element ids).
 *
 * Unknown ids, malformed ids and duplicates within the zone are dropped;
 * order is preserved.
 *
 * @param mixed $zone     Raw zone.
 * @param array $elements Known elements (id => data), for reference checks.
 * @return array
 */
function kabel_hb_clean_zone( $zone, array $elements ): array {
        if ( ! is_array( $zone ) ) {
                return array();
        }
        $out = array();
        foreach ( $zone as $id ) {
                $id = (string) $id;
                if ( '' === kabel_hb_clean_id( $id ) ) {
                        continue;
                }
                if ( ! isset( $elements[ $id ] ) ) {
                        continue;
                }
                if ( in_array( $id, $out, true ) ) {
                        continue;
                }
                $out[] = $id;
        }
        return $out;
}

/**
 * Sanitize one element (id + payload). Null when unusable.
 *
 * @param string $id  Element id.
 * @param mixed  $el  Raw element data.
 * @return array|null
 */
function kabel_hb_sanitize_element( string $id, $el ) {
        if ( ! is_array( $el ) ) {
                return null;
        }
        if ( '' === kabel_hb_clean_id( $id ) ) {
                return null;
        }
        $type = (string) ( $el['type'] ?? '' );
        if ( ! in_array( $type, kabel_hb_element_types(), true ) ) {
                return null;
        }
        $settings = isset( $el['settings'] ) && is_array( $el['settings'] ) ? $el['settings'] : array();
        return array(
                'type'     => $type,
                'settings' => kabel_hb_sanitize_settings( $type, $settings ),
        );
}

/**
 * Sanitize the whole header-builder model (the REST write gate).
 *
 * Everything is whitelisted per the k15 contract: rows capped at 3, zone
 * references validated and de-duplicated, unknown keys and unreferenced
 * elements dropped, numbers clamped, colors reduced to #hex-or-empty, URLs
 * passed through esc_url_raw. Better to lose data than to let unsafe
 * input through.
 *
 * @param mixed $model Raw model (decoded JSON).
 * @return array Clean model (empty rows = builder disabled).
 */
function kabel_hb_sanitize( $model ): array {
        $clean = array(
                'version'  => 1,
                'rows'     => array(),
                'mobile'   => array(
                        'zones'        => array( array(), array(), array() ),
                        'offcanvas'    => array(),
                        'offcanvasDir' => 'right',
                        'trigger'      => 'right',
                        'breakpoint'   => 780,
                ),
                'elements' => array(),
        );

        if ( ! is_array( $model ) ) {
                return $clean;
        }

        /* 1. Elements first — zones are validated against them. */
        $elements = array();
        if ( isset( $model['elements'] ) && is_array( $model['elements'] ) ) {
                foreach ( $model['elements'] as $id => $el ) {
                        $sanitized = kabel_hb_sanitize_element( (string) $id, $el );
                        if ( null !== $sanitized ) {
                                $elements[ (string) $id ] = $sanitized;
                        }
                }
        }

        /* 2. Rows (max 3, ids normalized + unique). */
        $rows_in = array();
        if ( isset( $model['rows'] ) && is_array( $model['rows'] ) ) {
                $rows_in = array_slice( array_values( $model['rows'] ), 0, 3 );
        }
        $row_ids   = array();
        $rows_out  = array();
        foreach ( $rows_in as $i => $row ) {
                if ( ! is_array( $row ) ) {
                        continue;
                }
                $id = kabel_hb_clean_id( $row['id'] ?? '' );
                if ( '' === $id || in_array( $id, $row_ids, true ) ) {
                        $id    = 'r-' . ( $i + 1 );
                        $attempt = 1;
                        while ( in_array( $id, $row_ids, true ) ) {
                                $id = 'r-' . ( $i + 1 ) . '-' . $attempt;
                                $attempt++;
                        }
                }
                $row_ids[] = $id;

                $settings_in = isset( $row['settings'] ) && is_array( $row['settings'] ) ? $row['settings'] : array();
                $zones_in    = isset( $row['zones'] ) && is_array( $row['zones'] ) ? $row['zones'] : array();

                $rows_out[] = array(
                        'id'       => $id,
                        'settings' => array(
                                'bg'          => kabel_hb_clean_color( $settings_in['bg'] ?? '' ),
                                'textColor'   => kabel_hb_clean_color( $settings_in['textColor'] ?? '' ),
                                'padY'        => kabel_hb_clamp_num( $settings_in['padY'] ?? 16, 0, 96, 16 ),
                                'borderBottom' => (bool) ( $settings_in['borderBottom'] ?? false ),
                                'sticky'      => (bool) ( $settings_in['sticky'] ?? false ),
                                'transparent' => (bool) ( $settings_in['transparent'] ?? false ),
                        ),
                        'zones'    => array(
                                kabel_hb_clean_zone( $zones_in[0] ?? array(), $elements ),
                                kabel_hb_clean_zone( $zones_in[1] ?? array(), $elements ),
                                kabel_hb_clean_zone( $zones_in[2] ?? array(), $elements ),
                        ),
                );
        }

        /* 3. Mobile (bar zones, drawer, direction, breakpoint). */
        $mobile_in = isset( $model['mobile'] ) && is_array( $model['mobile'] ) ? $model['mobile'] : array();
        $m_zones_in = isset( $mobile_in['zones'] ) && is_array( $mobile_in['zones'] ) ? $mobile_in['zones'] : array();
        $mobile_out = array(
                'zones'        => array(
                        kabel_hb_clean_zone( $m_zones_in[0] ?? array(), $elements ),
                        kabel_hb_clean_zone( $m_zones_in[1] ?? array(), $elements ),
                        kabel_hb_clean_zone( $m_zones_in[2] ?? array(), $elements ),
                ),
                'offcanvas'    => kabel_hb_clean_zone( $mobile_in['offcanvas'] ?? array(), $elements ),
                'offcanvasDir' => kabel_hb_clean_enum( $mobile_in['offcanvasDir'] ?? '', array( 'right', 'left' ), 'right' ),
                'trigger'      => kabel_hb_clean_enum( $mobile_in['trigger'] ?? '', array( 'right', 'left' ), 'right' ),
                'breakpoint'   => kabel_hb_clamp_num( $mobile_in['breakpoint'] ?? 780, 480, 1280, 780 ),
        );

        /* 4. Drop elements no placement references anymore. */
        $used = array();
        foreach ( $rows_out as $row ) {
                foreach ( $row['zones'] as $zone ) {
                        $used = array_merge( $used, $zone );
                }
        }
        foreach ( $mobile_out['zones'] as $zone ) {
                $used = array_merge( $used, $zone );
        }
        $used = array_merge( $used, $mobile_out['offcanvas'] );
        if ( ! empty( $used ) ) {
                $elements = array_intersect_key( $elements, array_flip( $used ) );
        } else {
                $elements = array();
        }

        $clean['rows']     = $rows_out;
        $clean['mobile']   = $mobile_out;
        $clean['elements'] = $elements;
        return $clean;
}

/* ---------------------------------------------------------------------
 * Icons (inline SVG, simplified public glyphs)
 * ------------------------------------------------------------------- */

/**
 * The icon library: 's' entries render stroked, 'f' entries filled.
 *
 * @return array<string, array<string,string>>
 */
function kabel_hb_icons(): array {
        return array(
                'search'    => array( 's' => '<circle cx="11" cy="11" r="7"/><path d="m16.5 16.5 4 4"/>' ),
                'close'     => array( 's' => '<path d="M6 6l12 12M18 6L6 18"/>' ),
                'chevron'   => array( 's' => '<path d="m6 9 6 6 6-6"/>' ),
                'user'      => array( 's' => '<circle cx="12" cy="8" r="4"/><path d="M4.5 20a7.5 7.5 0 0 1 15 0"/>' ),
                'phone'     => array( 's' => '<path d="M5 4h4l1.5 4.5L8 10.5a12 12 0 0 0 5.5 5.5l2-2.5 4.5 1.5v4a1.5 1.5 0 0 1-1.6 1.5A16.5 16.5 0 0 1 3.5 5.6 1.5 1.5 0 0 1 5 4z"/>' ),
                'email'     => array( 's' => '<rect x="3" y="5" width="18" height="14" rx="1"/><path d="m3 7 9 6 9-6"/>' ),
                'pin'       => array( 's' => '<path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.6"/>' ),
                'cart'      => array( 's' => '<path d="M3 4h2.5l2.2 11.5a1.5 1.5 0 0 0 1.5 1.2h7.6a1.5 1.5 0 0 0 1.5-1.2L20 8H6"/><circle cx="10" cy="20.2" r="1.4"/><circle cx="17" cy="20.2" r="1.4"/>' ),
                'whatsapp'  => array( 'f' => '<path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 2a8 8 0 1 1-4.1 14.9l-.3-.2-2.9.8.8-2.8-.2-.3A8 8 0 0 1 12 4zM8.9 8.2c-.2 0-.5 0-.7.3-.2.3-.9.9-.9 2.1s.9 2.5 1 2.6c.1.2 1.8 2.9 4.5 3.9 2.2.9 2.7.7 3.1.7.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.1-1.2 0-.1-.2-.2-.4-.3l-1.6-.8c-.2-.1-.4-.1-.5.1l-.7.9c-.1.2-.3.2-.5.1-.6-.2-1.3-.6-1.9-1.1-.5-.5-1-1.1-1.3-1.6-.1-.2 0-.4.1-.5l.5-.6c.1-.2.1-.3 0-.5l-.7-1.7c-.1-.3-.2-.3-.4-.3z"/>' ),
                'facebook'  => array( 'f' => '<path d="M13.4 21v-8.2h2.8l.4-3.2h-3.2V7.5c0-.9.3-1.6 1.6-1.6h1.7V3.1C16.3 3 15.3 3 14.2 3c-2.9 0-4.7 1.7-4.7 4.8v1.8H6.7v3.2h2.8V21h3.9z"/>' ),
                'instagram' => array( 's' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4.2"/><circle cx="17.2" cy="6.8" r="1" fill="currentColor" stroke="none"/>' ),
                'linkedin'  => array( 'f' => '<path d="M4.8 3.5a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM3 9h3.6v11.5H3zM9.5 9H13v1.6h.1c.5-.9 1.7-1.9 3.5-1.9 3.7 0 4.4 2.4 4.4 5.6v6.2h-3.6v-5.5c0-1.3 0-3-1.9-3s-2.2 1.4-2.2 2.9v5.6H9.5z"/>' ),
                'youtube'   => array( 'f' => '<path d="M22 12s0-3.3-.4-4.8a2.5 2.5 0 0 0-1.8-1.8C18.3 5 12 5 12 5s-6.3 0-7.8.4A2.5 2.5 0 0 0 2.4 7.2C2 8.7 2 12 2 12s0 3.3.4 4.8a2.5 2.5 0 0 0 1.8 1.8c1.5.4 7.8.4 7.8.4s6.3 0 7.8-.4a2.5 2.5 0 0 0 1.8-1.8c.4-1.5.4-4.8.4-4.8zM10 15.2V8.8l5.2 3.2z"/>' ),
                'x'         => array( 'f' => '<path d="M17.8 3h3l-6.6 7.6L22 21h-6.1l-4.8-6.3L5.6 21h-3l7.1-8.1L2 3h6.3l4.3 5.7L17.8 3zm-1.1 16.1h1.7L7.4 4.7H5.6l11.1 14.4z"/>' ),
                'tiktok'    => array( 'f' => '<path d="M16.6 3c.3 1.8 1.5 3.2 3.4 3.4v3c-1.3 0-2.5-.4-3.4-1.1v6.3c0 3.1-2.5 5.4-5.5 5.4a5.4 5.4 0 0 1 0-10.8c.3 0 .7 0 1 .1v3.1a2.4 2.4 0 1 0 1.6 2.3V3h2.9z"/>' ),
        );
}

/**
 * Render one inline SVG icon (aria-hidden).
 */
function kabel_hb_icon( string $name ): string {
        $icons = kabel_hb_icons();
        if ( ! isset( $icons[ $name ] ) ) {
                return '';
        }
        $icon = $icons[ $name ];
        if ( isset( $icon['s'] ) ) {
                return '<svg class="kbhb-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $icon['s'] . '</svg>';
        }
        return '<svg class="kbhb-svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">' . $icon['f'] . '</svg>';
}

/* ---------------------------------------------------------------------
 * Menu walker (sub indicators + drawer accordion toggles)
 * ------------------------------------------------------------------- */

if ( class_exists( 'Walker_Nav_Menu' ) ) {
        /**
         * Nav-menu walker for the builder menu element.
         *
         * Desktop: appends the ▾ indicator to parent links (subIndicator).
         * Drawer: emits a real toggle button (data-kbhb-sub) after parent links so
         * submenus behave as an accessible accordion; the submenu <ul> carries the
         * matching id for aria-controls.
         */
        class Kabel_HB_Menu_Walker extends Walker_Nav_Menu {

                /** @var bool Drawer (accordion) mode. */
                private $hb_drawer = false;

                /** @var bool Append ▾ indicators to parent links. */
                private $hb_indicator = true;

                /** @var string Unique prefix for submenu ids. */
                private $hb_uniq = '';

                /** @var string aria-controls id for the submenu currently being opened. */
                private $hb_sub_id = '';

                public function __construct( bool $drawer = false, bool $indicator = true ) {
                        $this->hb_drawer    = $drawer;
                        $this->hb_indicator = $indicator;
                        $this->hb_uniq      = wp_unique_id( 'kbhb-sm-' );
                }

                public function start_lvl( &$output, $depth = 0, $args = null ) {
                        $indent  = str_repeat( "\t", (int) $depth );
                        $atts    = '' !== $this->hb_sub_id ? ' id="' . esc_attr( $this->hb_sub_id ) . '"' : '';
                        $output .= "\n{$indent}<ul class=\"kbhb-menu__sub\"{$atts} data-kbhb-submenu>\n";
                }

                public function end_lvl( &$output, $depth = 0, $args = null ) {
                        $indent  = str_repeat( "\t", (int) $depth );
                        $output .= "{$indent}</ul>\n";
                }

                public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
                        $indent = (int) $depth ? str_repeat( "\t", (int) $depth ) : '';

                        $classes      = empty( $item->classes ) ? array() : (array) $item->classes;
                        $classes[]    = 'kbhb-menu__item';
                        $has_children = in_array( 'menu-item-has-children', $classes, true );

                        if ( $has_children ) {
                                $this->hb_sub_id = $this->hb_uniq . '-' . (int) ( $item->db_id ?: $item->ID );
                        }

                        $class_names = implode( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
                        $output     .= $indent . '<li class="' . esc_attr( $class_names ) . '">';

                        $atts = array(
                                'title' => ! empty( $item->attr_title ) ? $item->attr_title : '',
                        );
                        if ( ! empty( $item->target ) ) {
                                $atts['target'] = $item->target;
                                if ( '_blank' === $item->target && empty( $item->xfn ) ) {
                                        $atts['rel'] = 'noopener';
                                }
                        }
                        if ( ! empty( $item->xfn ) ) {
                                $atts['rel'] = $item->xfn;
                        }
                        if ( ! empty( $item->url ) ) {
                                $atts['href'] = $item->url;
                        }
                        if ( in_array( 'current-menu-item', $classes, true ) || ! empty( $item->current ) ) {
                                $atts['aria-current'] = 'page';
                        }

                        $atts       = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );
                        $attributes = '';
                        foreach ( $atts as $attr => $value ) {
                                if ( '' !== $value && null !== $value ) {
                                        $attributes .= ' ' . $attr . '="' . esc_attr( $value ) . '"';
                                }
                        }

                        $title = apply_filters( 'the_title', $item->title, $item->ID );
                        $title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

                        $indicator = ( $has_children && $this->hb_indicator && ! $this->hb_drawer )
                                ? '<span class="kbhb-menu__ind" aria-hidden="true">&#9662;</span>'
                                : '';

                        $before      = isset( $args->before ) ? $args->before : '';
                        $after       = isset( $args->after ) ? $args->after : '';
                        $link_before = isset( $args->link_before ) ? $args->link_before : '';
                        $link_after  = isset( $args->link_after ) ? $args->link_after : '';

                        $item_output = $before . '<a' . $attributes . '>' . $link_before . $title . $indicator . $link_after . '</a>';

                        if ( $has_children && $this->hb_drawer ) {
                                $item_output .= '<button class="kbhb-menu__toggle" type="button" data-kbhb-sub aria-expanded="false" aria-controls="' . esc_attr( $this->hb_sub_id ) . '">'
                                        . '<span class="screen-reader-text">' . esc_html__( 'Toggle submenu', 'kabel' ) . '</span>'
                                        . kabel_hb_icon( 'chevron' )
                                        . '</button>';
                        }

                        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
                }

                public function end_el( &$output, $item, $depth = 0, $args = null ) {
                        $output .= "</li>\n";
                }
        }
}

/**
 * Build the walker for a placement (or '' when the class is unavailable).
 */
function kabel_hb_menu_walker( string $placement, bool $sub_indicator ) {
        if ( ! class_exists( 'Kabel_HB_Menu_Walker' ) ) {
                return '';
        }
        return new Kabel_HB_Menu_Walker( 'drawer' === $placement, $sub_indicator );
}

/* ---------------------------------------------------------------------
 * Rendering
 * ------------------------------------------------------------------- */

/**
 * Inline style vars for a row / the mobile bar.
 *
 * Empty colors are omitted so the hb.css fallbacks apply.
 *
 * @param array $settings Row settings (already sanitized).
 * @param int   $min_padY Lower padY bound (mobile bar clamps to >= 12).
 * @return string 'style="--kbhb-bg:…;--kbhb-fg:…;--kbhb-pady:…px"'.
 */
function kabel_hb_row_style( array $settings, int $min_padY = 0 ): string {
        $style = '';
        $bg    = kabel_hb_clean_color( $settings['bg'] ?? '' );
        $fg    = kabel_hb_clean_color( $settings['textColor'] ?? '' );
        if ( '' !== $bg ) {
                $style .= '--kbhb-bg:' . $bg . ';';
        }
        if ( '' !== $fg ) {
                $style .= '--kbhb-fg:' . $fg . ';';
        }
        $pad_y = (int) ( $settings['padY'] ?? 16 );
        if ( $pad_y < $min_padY ) {
                $pad_y = $min_padY;
        }
        $pad_y = max( 0, min( 96, $pad_y ) );
        $style .= '--kbhb-pady:' . $pad_y . 'px;';
        return '' === $style ? '' : ' style="' . esc_attr( $style ) . '"';
}

/**
 * Render one zone's elements.
 *
 * @param array  $zone     Element ids.
 * @param array  $elements Elements map.
 * @param string $placement 'desktop' | 'mobile' | 'drawer'.
 * @return string
 */
function kabel_hb_render_zone( array $zone, array $elements, string $placement ): string {
        $out = '';
        foreach ( $zone as $id ) {
                $id = (string) $id;
                if ( ! isset( $elements[ $id ] ) || ! is_array( $elements[ $id ] ) ) {
                        continue;
                }
                $out .= kabel_hb_render_element( $id, $elements[ $id ], $placement );
        }
        return $out;
}

/**
 * Render the builder header for the stored model ('' when off).
 */
function kabel_hb_render(): string {
        $model = kabel_hb_get();
        if ( null === $model ) {
                return '';
        }
        return kabel_hb_render_model( $model );
}

/**
 * Render the builder header for ANY (already sanitized) model.
 *
 * Used by kabel_hb_render() for the stored option and by the Customizer
 * render endpoint for live unsaved models. The root div carries a
 * `data-kbhb-hash` (md5 of the canonical JSON) so the preview bridge can
 * skip no-op DOM swaps.
 *
 * @param array $model Sanitized model.
 * @return string
 */
function kabel_hb_render_model( array $model ): string {
        $rows     = ( isset( $model['rows'] ) && is_array( $model['rows'] ) ) ? array_values( $model['rows'] ) : array();
        $mobile   = ( isset( $model['mobile'] ) && is_array( $model['mobile'] ) ) ? $model['mobile'] : array();
        $elements = ( isset( $model['elements'] ) && is_array( $model['elements'] ) ) ? $model['elements'] : array();
        if ( empty( $rows ) ) {
                return '';
        }

        $zone_classes = array( 'left', 'center', 'right' );

        /* ---- Desktop rows ---- */
        $any_sticky = false;
        $rows_html  = '';
        foreach ( $rows as $row ) {
                if ( ! is_array( $row ) ) {
                        continue;
                }
                $settings = isset( $row['settings'] ) && is_array( $row['settings'] ) ? $row['settings'] : array();
                $sticky      = ! empty( $settings['sticky'] );
                $transparent = ! empty( $settings['transparent'] );
                $any_sticky  = $any_sticky || $sticky;

                $class = 'kbhb__row';
                if ( $sticky ) {
                        $class .= ' kbhb__row--sticky';
                }
                if ( $transparent ) {
                        $class .= ' kbhb__row--transparent';
                }
                if ( ! empty( $settings['borderBottom'] ) ) {
                        $class .= ' kbhb__row--border';
                }

                $zones_html = '';
                for ( $z = 0; $z < 3; $z++ ) {
                        $zone = isset( $row['zones'] ) && is_array( $row['zones'] ) && isset( $row['zones'][ $z ] ) && is_array( $row['zones'][ $z ] ) ? $row['zones'][ $z ] : array();
                        $zones_html .= '<div class="kbhb__zone kbhb__zone--' . $zone_classes[ $z ] . '">' . kabel_hb_render_zone( $zone, $elements, 'desktop' ) . '</div>';
                }

                $rows_html .= '<div class="' . $class . '"' . kabel_hb_row_style( $settings ) . '>'
                        . '<div class="kbhb__row-inner container">' . $zones_html . '</div>'
                        . '</div>';
        }
        if ( '' === $rows_html ) {
                return '';
        }

        /* ---- Mobile bar + drawer ---- */
        $first_settings = isset( $rows[0]['settings'] ) && is_array( $rows[0]['settings'] ) ? $rows[0]['settings'] : array();
        $m_zones_in     = isset( $mobile['zones'] ) && is_array( $mobile['zones'] ) ? $mobile['zones'] : array();
        $offcanvas      = isset( $mobile['offcanvas'] ) && is_array( $mobile['offcanvas'] ) ? $mobile['offcanvas'] : array();
        $trigger_side   = kabel_hb_clean_enum( $mobile['trigger'] ?? '', array( 'right', 'left' ), 'right' );
        $drawer_dir     = kabel_hb_clean_enum( $mobile['offcanvasDir'] ?? '', array( 'right', 'left' ), 'right' );
        $breakpoint     = kabel_hb_clamp_num( $mobile['breakpoint'] ?? 780, 480, 1280, 780 );

        $drawer_body = kabel_hb_render_zone( $offcanvas, $elements, 'drawer' );

        $trigger_html = '';
        if ( '' !== $drawer_body ) {
                $trigger_html = '<button class="kbhb__trigger" type="button" data-kbhb-trigger aria-expanded="false" aria-controls="kbhb-drawer">'
                        . '<span></span><span></span><span></span>'
                        . '<span class="screen-reader-text">' . esc_html__( 'Menu', 'kabel' ) . '</span>'
                        . '</button>';
        }

        $m_zones = array( '', '', '' );
        for ( $z = 0; $z < 3; $z++ ) {
                $zone          = isset( $m_zones_in[ $z ] ) && is_array( $m_zones_in[ $z ] ) ? $m_zones_in[ $z ] : array();
                $m_zones[ $z ] = '<div class="kbhb__zone kbhb__zone--' . $zone_classes[ $z ] . '">' . kabel_hb_render_zone( $zone, $elements, 'mobile' ) . '</div>';
        }
        if ( '' !== $trigger_html ) {
                /* Edge placement: burger outermost-left or outermost-right. */
                if ( 'left' === $trigger_side ) {
                        $prefix   = '<div class="kbhb__zone kbhb__zone--left">';
                        $m_zones[0] = $prefix . $trigger_html . substr( $m_zones[0], strlen( $prefix ) );
                } else {
                        $m_zones[2] = substr( $m_zones[2], 0, -6 ) . $trigger_html . '</div>';
                }
        }

        $mobile_html = '<div class="kbhb__mobile">'
                . '<div class="kbhb__mrow"' . kabel_hb_row_style( $first_settings, 12 ) . '>'
                . '<div class="kbhb__row-inner container">' . implode( '', $m_zones ) . '</div>'
                . '</div>';

        if ( '' !== $drawer_body ) {
                $mobile_html .= '<div class="kbhb__scrim" data-kbhb-scrim hidden></div>'
                        . '<aside class="kbhb__drawer kbhb__drawer--' . $drawer_dir . '" id="kbhb-drawer" data-kbhb-drawer hidden aria-label="' . esc_attr__( 'Site menu', 'kabel' ) . '">'
                        . '<div class="kbhb__drawer-head">'
                        . '<button class="kbhb__drawer-close" type="button" data-kbhb-drawer-close aria-label="' . esc_attr__( 'Close menu', 'kabel' ) . '">' . kabel_hb_icon( 'close' ) . '</button>'
                        . '</div>'
                        . '<div class="kbhb__drawer-body">' . $drawer_body . '</div>'
                        . '</aside>';
        }
        $mobile_html .= '</div>';

        /* ---- Search modal (only when a search element exists) ---- */
        $has_search   = false;
        $placeholder  = 'Search…';
        foreach ( $elements as $el ) {
                if ( is_array( $el ) && 'search' === ( $el['type'] ?? '' ) ) {
                        $has_search  = true;
                        $s           = isset( $el['settings'] ) && is_array( $el['settings'] ) ? $el['settings'] : array();
                        $placeholder = (string) ( $s['placeholder'] ?? '' );
                        if ( '' === wp_strip_all_tags( $placeholder ) ) {
                                $placeholder = 'Search…';
                        }
                        break;
                }
        }
        $search_html = '';
        if ( $has_search ) {
                $search_html = '<div class="kbhb__search" data-kbhb-search hidden>'
                        . '<button class="kbhb__search-close" type="button" data-kbhb-search-close aria-label="' . esc_attr__( 'Close search', 'kabel' ) . '">' . kabel_hb_icon( 'close' ) . '</button>'
                        . '<form class="kbhb__search-form" action="' . esc_url( home_url( '/' ) ) . '" method="get" role="search">'
                        . '<input class="kbhb__search-input" type="search" name="s" placeholder="' . esc_attr( $placeholder ) . '" aria-label="' . esc_attr__( 'Search', 'kabel' ) . '">'
                        . '<button class="kbhb__search-submit" type="submit">' . kabel_hb_icon( 'search' ) . '<span class="screen-reader-text">' . esc_html__( 'Search', 'kabel' ) . '</span></button>'
                        . '</form>'
                        . '</div>';
        }

        /* ---- Assemble ---- */
        $root_class = 'kbhb' . ( $any_sticky ? ' kbhb--sticky' : '' );
        return '<div class="' . $root_class . '" data-kbhb data-breakpoint="' . esc_attr( (string) $breakpoint ) . '" data-kbhb-hash="' . esc_attr( kabel_hb_model_hash( $model ) ) . '">'
                . '<div class="kbhb__desktop">' . $rows_html . '</div>'
                . $mobile_html
                . $search_html
                . '</div>';
}

/**
 * A stable fingerprint of the canonical model JSON — printed as
 * data-kbhb-hash and returned by the render endpoint so the preview bridge
 * can detect "nothing actually changed" without swapping the DOM.
 */
function kabel_hb_model_hash( array $model ): string {
        return md5( (string) wp_json_encode( $model ) );
}

/**
 * The full front-end header element for the stored model (header.php hook).
 *
 * The <header> wrapper is owned here (not in the template) so the Customizer
 * render endpoint can return the exact same element the theme prints.
 */
function kabel_hb_render_wrapper(): string {
        $inner = kabel_hb_render();
        return '' === $inner ? '' : '<header class="site-header site-header--builder" data-kb-header>' . $inner . '</header>';
}

/**
 * The full front-end header element for an arbitrary sanitized model
 * (Customizer render endpoint).
 */
function kabel_hb_render_wrapper_for_model( array $model ): string {
        $inner = kabel_hb_render_model( $model );
        return '' === $inner ? '' : '<header class="site-header site-header--builder" data-kb-header>' . $inner . '</header>';
}

/**
 * The dynamic breakpoint media queries for a model (model-dependent values,
 * so they cannot live in hb.css). Shared by the enqueue gate and the render
 * endpoint (the preview bridge swaps the inline style in place).
 *
 * @param array $model Sanitized model.
 * @return string
 */
function kabel_hb_inline_css( array $model ): string {
        $bp       = kabel_hb_clamp_num( ( isset( $model['mobile'] ) && is_array( $model['mobile'] ) ) ? ( $model['mobile']['breakpoint'] ?? 780 ) : 780, 480, 1280, 780 );
        $bp_below = number_format( $bp - 0.02, 2, '.', '' );
        return '@media (max-width:' . $bp_below . 'px){.kbhb .kbhb__mobile{display:block}.kbhb .kbhb__desktop{display:none}}'
                . '@media (min-width:' . $bp . 'px){.kbhb .kbhb__mobile{display:none}.kbhb .kbhb__desktop{display:block}}';
}

/**
 * Render one element, wrapped in its placement shell.
 *
 * Ids carry the position suffix: desktop `kbhb-el-{id}`, mobile `kbhb-el-{id}-m`,
 * drawer `kbhb-el-{id}-d`.
 *
 * @param string $id        Element id.
 * @param array  $el        Element data (type + settings).
 * @param string $placement 'desktop' | 'mobile' | 'drawer'.
 * @return string '' when the element renders nothing.
 */
function kabel_hb_render_element( string $id, array $el, string $placement ): string {
        $type = (string) ( $el['type'] ?? '' );
        if ( ! in_array( $type, kabel_hb_element_types(), true ) ) {
                return '';
        }

        $settings = isset( $el['settings'] ) && is_array( $el['settings'] ) ? $el['settings'] : array();
        $s        = array_merge( kabel_hb_element_defaults( $type ), $settings );

        $inner = '';
        switch ( $type ) {
                case 'logo':
                        $inner = kabel_hb_render_logo( $s );
                        break;
                case 'menu':
                        $inner = kabel_hb_render_menu( $s, $placement );
                        break;
                case 'button':
                        $inner = kabel_hb_render_button( $s );
                        break;
                case 'search':
                        $inner = kabel_hb_render_search( $s, $placement );
                        break;
                case 'social':
                        $inner = kabel_hb_render_social( $s );
                        break;
                case 'html':
                        $inner = kabel_hb_render_html( $s );
                        break;
                case 'contact':
                        $inner = kabel_hb_render_contact( $s );
                        break;
                case 'text':
                        $inner = kabel_hb_render_text( $s );
                        break;
                case 'lang':
                        $inner = kabel_hb_render_lang( $s );
                        break;
                case 'account':
                        $inner = kabel_hb_render_account( $s );
                        break;
                case 'cart':
                        $inner = kabel_hb_render_cart( $s );
                        break;
        }
        if ( '' === trim( $inner ) ) {
                return '';
        }

        $suffix = 'desktop' === $placement ? '' : ( 'mobile' === $placement ? '-m' : '-d' );
        $class  = 'kbhb__el kbhb__el--' . $type;
        if ( 'drawer' === $placement ) {
                $class .= ' kbhb__el--drawer';
        }
        return '<div id="kbhb-el-' . esc_attr( $id . $suffix ) . '" class="' . esc_attr( $class ) . '">' . $inner . '</div>';
}

/**
 * logo — site logo / wordmark text / custom image.
 */
function kabel_hb_render_logo( array $s ): string {
        $site_name = get_bloginfo( 'name' );
        $inner     = '';

        $source = kabel_hb_clean_enum( $s['source'] ?? '', array( 'site', 'text', 'image' ), 'site' );

        if ( 'site' === $source ) {
                if ( has_custom_logo() ) {
                        $logo_id = (int) get_theme_mod( 'custom_logo' );
                        $image   = $logo_id > 0 ? wp_get_attachment_image( $logo_id, 'full', false, array( 'class' => 'kbhb-logo__img', 'alt' => $site_name ) ) : '';
                        if ( '' !== $image ) {
                                $inner = $image;
                        }
                }
                if ( '' === $inner ) {
                        $inner = '<span class="kbhb-logo__name">' . esc_html( $site_name ) . '</span>';
                }
        } elseif ( 'image' === $source ) {
                $img_url = esc_url( (string) ( $s['imgUrl'] ?? '' ) );
                if ( '' !== $img_url ) {
                        $width = kabel_hb_clamp_num( $s['imgWidth'] ?? 140, 40, 400, 140 );
                        $inner = '<img class="kbhb-logo__img" src="' . $img_url . '" style="--kbhb-logo-w:' . $width . 'px" alt="' . esc_attr( $site_name ) . '">';
                }
                if ( '' === $inner ) {
                        $inner = '<span class="kbhb-logo__name">' . esc_html( $site_name ) . '</span>';
                }
        } else {
                $text = wp_strip_all_tags( (string) ( $s['text'] ?? '' ) );
                $inner = '<span class="kbhb-logo__name">' . esc_html( '' !== $text ? $text : $site_name ) . '</span>';
                if ( ! empty( $s['taglineShow'] ) ) {
                        $tagline = wp_strip_all_tags( (string) ( $s['tagline'] ?? '' ) );
                        if ( '' === $tagline ) {
                                $tagline = get_bloginfo( 'description' );
                        }
                        if ( '' !== $tagline ) {
                                $inner .= '<span class="kbhb-logo__tag">' . esc_html( $tagline ) . '</span>';
                        }
                }
        }

        return '<a class="kbhb-logo" href="' . esc_url( home_url( '/' ) ) . '" aria-label="' . esc_attr( $site_name ) . '" rel="home">' . $inner . '</a>';
}

/**
 * menu — wp_nav_menu with the builder walker.
 */
function kabel_hb_render_menu( array $s, string $placement ): string {
        $menu_id = (int) ( $s['menuId'] ?? 0 );
        if ( 0 === $menu_id ) {
                /*
                 * Auto: prefer the menu assigned to the theme's 'primary'
                 * location, then any location-assigned menu, then the first
                 * menu by term_id. wp_get_nav_menus() sorts by NAME, which
                 * would hand back "Footer — Company" on this very site —
                 * locations first is the semantically right fallback chain.
                 */
                $locations = get_nav_menu_locations();
                if ( ! empty( $locations['primary'] ) && is_nav_menu( (int) $locations['primary'] ) ) {
                        $menu_id = (int) $locations['primary'];
                } else {
                        foreach ( (array) $locations as $loc_menu_id ) {
                                if ( $loc_menu_id && is_nav_menu( (int) $loc_menu_id ) ) {
                                        $menu_id = (int) $loc_menu_id;
                                        break;
                                }
                        }
                }
                if ( 0 === $menu_id ) {
                        $menus = wp_get_nav_menus( array( 'orderby' => 'term_id' ) );
                        if ( empty( $menus ) ) {
                                return '';
                        }
                        $first   = reset( $menus );
                        $menu_id = is_object( $first ) ? (int) $first->term_id : (int) ( is_array( $first ) && isset( $first['term_id'] ) ? $first['term_id'] : 0 );
                        if ( 0 === $menu_id ) {
                                return '';
                        }
                }
        }

        $menu = wp_nav_menu( array(
                'menu'        => $menu_id,
                'container'   => false,
                'menu_class'  => 'kbhb-menu',
                'depth'       => 2,
                'fallback_cb' => false,
                'echo'        => false,
                'walker'      => kabel_hb_menu_walker( $placement, ! empty( $s['subIndicator'] ) ),
        ) );
        if ( '' === $menu || null === $menu || false === $menu ) {
                return '';
        }

        $style = '--kbhb-menu-fs:' . kabel_hb_clamp_num( $s['fontSize'] ?? 15, 10, 32, 15 ) . 'px;'
                . '--kbhb-menu-gap:' . kabel_hb_clamp_num( $s['gap'] ?? 28, 0, 96, 28 ) . 'px;';
        $color = kabel_hb_clean_color( $s['color'] ?? '' );
        $hover = kabel_hb_clean_color( $s['hoverColor'] ?? '' );
        if ( '' !== $color ) {
                $style .= '--kbhb-menu-color:' . $color . ';';
        }
        if ( '' !== $hover ) {
                $style .= '--kbhb-menu-hover:' . $hover . ';';
        }

        $align = kabel_hb_clean_enum( $s['align'] ?? '', array( 'left', 'center', 'right' ), 'left' );
        $mode  = kabel_hb_clean_enum( $s['style'] ?? '', array( 'plain', 'underline', 'pill' ), 'plain' );

        return '<nav class="kbhb-nav kbhb-nav--' . $mode . ' kbhb-nav--align-' . $align . '" aria-label="' . esc_attr__( 'Primary navigation', 'kabel' ) . '" style="' . esc_attr( $style ) . '">'
                . $menu
                . '</nav>';
}

/**
 * button — five variants, two sizes, optional _blank.
 */
function kabel_hb_render_button( array $s ): string {
        $text = wp_strip_all_tags( (string) ( $s['text'] ?? '' ) );
        if ( '' === $text ) {
                $text = 'Contact';
        }
        $url = esc_url( (string) ( $s['url'] ?? '' ) );
        if ( '' === $url ) {
                $url = esc_url( home_url( '/' ) );
        }

        $variant = kabel_hb_clean_enum( $s['variant'] ?? '', array( 'gold', 'outline', 'ghost', 'dark', 'light' ), 'gold' );
        $size    = kabel_hb_clean_enum( $s['size'] ?? '', array( 's', 'm' ), 'm' );
        $target  = ! empty( $s['target'] ) ? ' target="_blank" rel="noopener"' : '';

        return '<a class="kbhb-btn kbhb-btn--' . $variant . ' kbhb-btn--' . $size . '" href="' . $url . '"' . $target . '>'
                . esc_html( $text )
                . '</a>';
}

/**
 * search — icon button (opens the modal) or an inline field. Field style
 * degrades to the icon on mobile / drawer placements.
 */
function kabel_hb_render_search( array $s, string $placement ): string {
        $style = kabel_hb_clean_enum( $s['style'] ?? '', array( 'icon', 'field' ), 'icon' );
        $placeholder = wp_strip_all_tags( (string) ( $s['placeholder'] ?? '' ) );
        if ( '' === $placeholder ) {
                $placeholder = 'Search…';
        }

        if ( 'field' === $style && 'desktop' === $placement ) {
                $width = kabel_hb_clamp_num( $s['fieldWidth'] ?? 180, 120, 420, 180 );
                return '<form class="kbhb-searchfield" action="' . esc_url( home_url( '/' ) ) . '" method="get" role="search" style="--kbhb-field-w:' . $width . 'px">'
                        . '<input type="search" name="s" placeholder="' . esc_attr( $placeholder ) . '" aria-label="' . esc_attr__( 'Search', 'kabel' ) . '">'
                        . '<button type="submit">' . kabel_hb_icon( 'search' ) . '<span class="screen-reader-text">' . esc_html__( 'Search', 'kabel' ) . '</span></button>'
                        . '</form>';
        }

        return '<button class="kbhb-searchbtn" type="button" data-kbhb-search-open aria-haspopup="dialog">'
                . kabel_hb_icon( 'search' )
                . '<span class="screen-reader-text">' . esc_html__( 'Search', 'kabel' ) . '</span>'
                . '</button>';
}

/**
 * social — 9 inline SVG icons; email → mailto:, phone → tel:.
 */
function kabel_hb_render_social( array $s ): string {
        $items = isset( $s['items'] ) && is_array( $s['items'] ) ? $s['items'] : array();
        $html  = '';
        foreach ( $items as $item ) {
                if ( ! is_array( $item ) ) {
                        continue;
                }
                $icon = (string) ( $item['icon'] ?? '' );
                $url  = esc_url( (string) ( $item['url'] ?? '' ) );
                if ( '' === $url ) {
                        continue;
                }

                if ( 'email' === $icon ) {
                        $address = ltrim( $url, '/' );
                        if ( 0 !== strpos( $address, 'mailto:' ) ) {
                                $address = 'mailto:' . str_replace( 'mailto%3A', '', $address );
                        }
                        $href  = esc_url( $address );
                        $extra = '';
                } elseif ( 'phone' === $icon ) {
                        $number = preg_replace( '/[^0-9+]/', '', html_entity_decode( $url, ENT_QUOTES ) );
                        $href   = esc_url( 'tel:' . $number );
                        $extra  = '';
                } else {
                        $href  = $url;
                        $extra = ' target="_blank" rel="noopener"';
                }
                if ( '' === $href ) {
                        continue;
                }

                $label = ucfirst( $icon );
                $html .= '<a class="kbhb-social__link kbhb-social__link--' . esc_attr( $icon ) . '" href="' . $href . '" aria-label="' . esc_attr( $label ) . '"' . $extra . '>'
                        . kabel_hb_icon( $icon )
                        . '</a>';
        }
        if ( '' === $html ) {
                return '';
        }

        $style = '--kbhb-social-size:' . kabel_hb_clamp_num( $s['size'] ?? 18, 12, 32, 18 ) . 'px;'
                . '--kbhb-social-gap:' . kabel_hb_clamp_num( $s['gap'] ?? 10, 4, 40, 10 ) . 'px;';
        return '<div class="kbhb-social" style="' . esc_attr( $style ) . '">' . $html . '</div>';
}

/**
 * html — stored sanitized at write time; output as-is.
 */
function kabel_hb_render_html( array $s ): string {
        $content = trim( (string) ( $s['content'] ?? '' ) );
        if ( '' === $content ) {
                return '';
        }
        return '<div class="kbhb-html">' . $content . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- sanitized on write (kabel_hb_sanitize).
}

/**
 * contact — inline phone / email / address with small icons.
 */
function kabel_hb_render_contact( array $s ): string {
        $html = '';

        if ( ! empty( $s['showPhone'] ) ) {
                $phone = wp_strip_all_tags( (string) ( $s['phone'] ?? '' ) );
                if ( '' !== $phone ) {
                        $number = preg_replace( '/[^0-9+]/', '', $phone );
                        $html  .= '<a class="kbhb-contact__item" href="' . esc_url( 'tel:' . $number ) . '">' . kabel_hb_icon( 'phone' ) . '<span>' . esc_html( $phone ) . '</span></a>';
                }
        }
        if ( ! empty( $s['showEmail'] ) ) {
                $email = wp_strip_all_tags( (string) ( $s['email'] ?? '' ) );
                if ( '' !== $email && false !== strpos( $email, '@' ) ) {
                        $html .= '<a class="kbhb-contact__item" href="' . esc_url( 'mailto:' . $email ) . '">' . kabel_hb_icon( 'email' ) . '<span>' . esc_html( $email ) . '</span></a>';
                }
        }
        if ( ! empty( $s['showAddress'] ) ) {
                $address = wp_strip_all_tags( (string) ( $s['address'] ?? '' ) );
                if ( '' !== $address ) {
                        $html .= '<span class="kbhb-contact__item kbhb-contact__item--address">' . kabel_hb_icon( 'pin' ) . '<span>' . esc_html( $address ) . '</span></span>';
                }
        }
        if ( '' === $html ) {
                return '';
        }
        return '<div class="kbhb-contact">' . $html . '</div>';
}

/**
 * text — kses'd markup, nl2br, size + color.
 */
function kabel_hb_render_text( array $s ): string {
        $content = trim( (string) ( $s['content'] ?? '' ) );
        if ( '' === $content ) {
                return '';
        }
        $style = 'font-size:' . kabel_hb_clamp_num( $s['size'] ?? 14, 10, 40, 14 ) . 'px;';
        $color = kabel_hb_clean_color( $s['color'] ?? '' );
        if ( '' !== $color ) {
                $style .= 'color:' . $color . ';';
        }
        return '<div class="kbhb-text" style="' . esc_attr( $style ) . '">' . nl2br( wp_kses_post( $content ) ) . '</div>';
}

/**
 * lang — capture the theme switcher (it echoes).
 */
function kabel_hb_render_lang( array $s ): string {
        if ( ! function_exists( 'kabel_language_switcher' ) ) {
                return '';
        }
        ob_start();
        kabel_language_switcher();
        $out = trim( (string) ob_get_clean() );
        if ( '' === $out ) {
                return '';
        }
        return '<div class="kbhb-lang">' . $out . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside kabel_language_switcher().
}

/**
 * account — profile link when logged in, login link otherwise.
 */
function kabel_hb_render_account( array $s ): string {
        $url = esc_url( (string) ( $s['url'] ?? '' ) );
        if ( '' === $url ) {
                $url = is_user_logged_in()
                        ? esc_url( admin_url( 'profile.php' ) )
                        : esc_url( wp_login_url( home_url( '/' ) ) );
        }
        $label = wp_strip_all_tags( (string) ( $s['loginText'] ?? '' ) );
        if ( '' === $label ) {
                $label = 'Login';
        }
        return '<a class="kbhb-account" href="' . $url . '">' . kabel_hb_icon( 'user' ) . '<span>' . esc_html( $label ) . '</span></a>';
}

/**
 * cart — WooCommerce cart link + count badge ('' without WooCommerce).
 */
function kabel_hb_render_cart( array $s ): string {
        if ( ! class_exists( 'WooCommerce' ) ) {
                return '';
        }
        $url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
        $count = 0;
        if ( function_exists( 'WC' ) && null !== WC() && isset( WC()->cart ) && method_exists( WC()->cart, 'get_cart_contents_count' ) ) {
                $count = (int) WC()->cart->get_cart_contents_count();
        }
        $badge = ! empty( $s['showCount'] )
                ? '<span class="kbhb-cart__count' . ( 0 === $count ? ' is-empty' : '' ) . '">' . esc_html( (string) $count ) . '</span>'
                : '';
        return '<a class="kbhb-cart" href="' . esc_url( $url ) . '">'
                . kabel_hb_icon( 'cart' )
                . $badge
                . '<span class="screen-reader-text">' . esc_html__( 'Cart', 'kabel' ) . '</span>'
                . '</a>';
}

/* ---------------------------------------------------------------------
 * Front assets
 * ------------------------------------------------------------------- */

/**
 * Front-end assets — when the builder will actually render, and always
 * inside the Customizer preview (so a cold-start build swaps into a page
 * that already carries hb.css + hb.js). The breakpoint media queries are
 * model-dependent, so PHP injects them inline (hb.css only carries the
 * desktop-first base states); the preview bridge keeps the inline style in
 * sync with unsaved models.
 */
function kabel_hb_enqueue(): void {
        $active = kabel_hb_active();
        if ( ! $active && ! is_customize_preview() ) {
                return;
        }

        $css_path = KABEL_DIR . '/assets/css/hb.css';
        $css_ver  = file_exists( $css_path ) ? (string) filemtime( $css_path ) : KABEL_VERSION;
        wp_enqueue_style( 'kabel-hb', KABEL_URI . '/assets/css/hb.css', array(), $css_ver );

        $js_path = KABEL_DIR . '/assets/js/hb.js';
        $js_ver  = file_exists( $js_path ) ? (string) filemtime( $js_path ) : KABEL_VERSION;
        wp_enqueue_script( 'kabel-hb-js', KABEL_URI . '/assets/js/hb.js', array(), $js_ver, true );

        if ( $active ) {
                /* Dynamic breakpoint: below {bp} show the mobile bar and hide
                 * desktop; from {bp} up the reverse. bp itself belongs to the
                 * desktop range. */
                wp_add_inline_style( 'kabel-hb', kabel_hb_inline_css( kabel_hb_get() ) );
        }
}
add_action( 'wp_enqueue_scripts', 'kabel_hb_enqueue', 20 );

/**
 * Serve hb.js as a native ES module.
 */
function kabel_hb_module_tag( $tag, $handle, $src ) {
        if ( 'kabel-hb-js' === $handle && false === strpos( $tag, 'type="module"' ) ) {
                $tag = str_replace( ' src=', ' type="module" src=', $tag );
        }
        return $tag;
}
add_filter( 'script_loader_tag', 'kabel_hb_module_tag', 10, 3 );

/* ---------------------------------------------------------------------
 * REST API — kabel/v1/header-builder/render (the Customizer preview bridge)
 * ------------------------------------------------------------------- */

/**
 * English UI strings for the Customizer app. Keyed by the English UI string
 * the React t() falls back through, so a translated site localizes any key
 * missing from the app's own zh.js dictionary.
 *
 * @return array<string,string>
 */
function kabel_hb_l10n(): array {
        return array(
                'Header Builder' => __( 'Header Builder', 'kabel' ),
                'Builder active' => __( 'Builder active', 'kabel' ),
                'Classic header active' => __( 'Classic header active', 'kabel' ),
                'Which header source is live on the site' => __( 'Which header source is live on the site', 'kabel' ),
                'Undo'     => __( 'Undo', 'kabel' ),
                'Redo'     => __( 'Redo', 'kabel' ),
                'Desktop'  => __( 'Desktop', 'kabel' ),
                'Mobile'   => __( 'Mobile', 'kabel' ),
                'Elements' => __( 'Elements', 'kabel' ),
                'Settings' => __( 'Settings', 'kabel' ),
                'Structure' => __( 'Structure', 'kabel' ),
                'Add Row'  => __( 'Add Row', 'kabel' ),
                'Row settings' => __( 'Row settings', 'kabel' ),
                'Mobile settings' => __( 'Mobile settings', 'kabel' ),
                'Delete row' => __( 'Delete row', 'kabel' ),
                'Delete this row?' => __( 'Delete this row?', 'kabel' ),
                'The row and its element placements will be removed. Elements used elsewhere stay.' => __( 'The row and its element placements will be removed. Elements used elsewhere stay.', 'kabel' ),
                'Delete'   => __( 'Delete', 'kabel' ),
                'Cancel'   => __( 'Cancel', 'kabel' ),
                'Confirm'  => __( 'Confirm', 'kabel' ),
                'Requires WooCommerce' => __( 'Requires WooCommerce', 'kabel' ),
                'Media library is not available.' => __( 'Media library is not available.', 'kabel' ),
                'Select an element, a row, or mobile settings to edit.' => __( 'Select an element, a row, or mobile settings to edit.', 'kabel' ),
                'Drag an element onto the structure, or click to add it.' => __( 'Drag an element onto the structure, or click to add it.', 'kabel' ),
                'Site Logo' => __( 'Site Logo', 'kabel' ),
                'Menu'      => __( 'Menu', 'kabel' ),
                'Button'    => __( 'Button', 'kabel' ),
                'Search'    => __( 'Search', 'kabel' ),
                'Social Icons' => __( 'Social Icons', 'kabel' ),
                'Custom HTML' => __( 'Custom HTML', 'kabel' ),
                'Contact Info' => __( 'Contact Info', 'kabel' ),
                'Text'      => __( 'Text', 'kabel' ),
                'Language Switcher' => __( 'Language Switcher', 'kabel' ),
                'Account'   => __( 'Account', 'kabel' ),
                'Cart'      => __( 'Cart', 'kabel' ),
        );
}

/**
 * Register the render route (POST only — the customizer pane app reads its
 * initial model from KabelHBData and saving goes through Publish).
 */
function kabel_hb_rest(): void {
        register_rest_route( 'kabel/v1', '/header-builder/render', array(
                'methods'             => 'POST',
                'callback'            => 'kabel_hb_rest_render',
                'permission_callback' => 'kabel_hb_rest_permission',
        ) );
}
add_action( 'rest_api_init', 'kabel_hb_rest' );

/**
 * REST permission: edit_theme_options, else a 403.
 *
 * @return true|WP_Error
 */
function kabel_hb_rest_permission() {
        if ( current_user_can( 'edit_theme_options' ) ) {
                return true;
        }
        return new WP_Error( 'kabel_hb_forbidden', __( 'You are not allowed to manage the header builder.', 'kabel' ), array( 'status' => 403 ) );
}

/**
 * POST {model} → {ok, html, inlineCss, hash} — render any (sanitized here)
 * model exactly like the front end would. `html` is null when the model has
 * no rows (the preview falls back to a full preview refresh then).
 */
function kabel_hb_rest_render( WP_REST_Request $request ) {
        $model = $request->get_param( 'model' );
        if ( ! is_array( $model ) ) {
                return new WP_Error( 'kabel_hb_bad_model', __( 'Invalid model.', 'kabel' ), array( 'status' => 400 ) );
        }

        $clean = kabel_hb_sanitize( $model );
        $html  = kabel_hb_render_wrapper_for_model( $clean );
        if ( '' === $html ) {
                return array(
                        'ok'        => true,
                        'html'      => null,
                        'inlineCss' => '',
                        'hash'      => '',
                );
        }
        return array(
                'ok'        => true,
                'html'      => $html,
                'inlineCss' => kabel_hb_inline_css( $clean ),
                'hash'      => kabel_hb_model_hash( $clean ),
        );
}

/* ---------------------------------------------------------------------
 * Admin bar — "Edit Header" deep-links into the Customizer section
 * ------------------------------------------------------------------- */

/**
 * Runs after site-parts' admin-bar node (priority 80): the "Edit Header"
 * node is repointed (or created) at the Customizer with the builder section
 * autofocused — the Blocksy-style precise entry point.
 *
 * @param WP_Admin_Bar $admin_bar The admin bar.
 */
function kabel_hb_admin_bar( WP_Admin_Bar $admin_bar ): void {
        if ( ! current_user_can( 'edit_theme_options' ) ) {
                return;
        }

        $builder_url = admin_url( 'customize.php' ) . '?autofocus[section]=kabel_hb_builder';

        $existing = $admin_bar->get_node( 'kabel-edit-header' );
        $admin_bar->add_node( array(
                'id'     => 'kabel-edit-header',
                'title'  => $existing ? $existing->title : __( 'Edit Header', 'kabel' ),
                'href'   => $builder_url,
                'parent' => false,
        ) );
}
add_action( 'admin_bar_menu', 'kabel_hb_admin_bar', 85 );
