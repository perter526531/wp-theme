/**
 * Kabel Blocks v3 — shared utilities.
 *
 * CRITICAL: every helper here that produces markup (style pairs, SVG icons,
 * class fragments) has a byte-exact twin in the PHP emitter
 * (kabel-blocks/inc/emitter.php). Changing one side without the other breaks
 * block validation in the editor. Keep them in sync.
 */
import { el } from '@wordpress/element';

/* ------------------------------------------------------------------ */
/* Palette (black & antique-bronze, mirrors the Kabel theme)           */
/* ------------------------------------------------------------------ */

export const PALETTE = [
        { name: 'Bronze', slug: 'bronze', color: '#B39A74' },
        { name: 'Bronze bright', slug: 'bronze-bright', color: '#D3BC97' },
        { name: 'Near black', slug: 'deep', color: '#0B0B0D' },
        { name: 'Navy black', slug: 'paper', color: '#101828' },
        { name: 'Paper alt', slug: 'paper-alt', color: '#151E33' },
        { name: 'Navy', slug: 'navy', color: '#222E5E' },
        { name: 'White', slug: 'white', color: '#FFFFFF' },
];

/* Box-shadow presets. Keys are stored in attributes. */
export const SHADOWS = {
        soft: '0 2px 14px rgba(0, 0, 0, 0.18)',
        medium: '0 12px 32px rgba(0, 0, 0, 0.30)',
        strong: '0 26px 64px rgba(0, 0, 0, 0.45)',
};

export const SHADOW_OPTIONS = [
        { value: '', label: 'None' },
        { value: 'soft', label: 'Soft' },
        { value: 'medium', label: 'Medium' },
        { value: 'strong', label: 'Strong' },
];

/* ------------------------------------------------------------------ */
/* Deterministic class + style builders                                */
/* ------------------------------------------------------------------ */

/** Join truthy class fragments with single spaces. */
export function cx( ...parts ) {
        return parts.filter( ( p ) => typeof p === 'string' && p !== '' ).join( ' ' );
}

/**
 * Turn [[css-prop, value]] pairs into a React style object.
 * Skips empty values; kebab-case keys are camelized, `--vars` pass through.
 * PHP twin: kbl3_style().
 */
export function toStyle( pairs ) {
        const style = {};
        for ( const [ key, value ] of pairs ) {
                if ( value === undefined || value === null || value === '' ) {
                        continue;
                }
                if ( key.startsWith( '--' ) ) {
                        style[ key ] = value;
                        continue;
                }
                const camel = key.replace( /-([a-z])/g, ( _, c ) => c.toUpperCase() );
                style[ camel ] = value;
        }
        return style;
}

/** CSS background-image value for a URL (single-quoted). */
export function bgUrl( url ) {
        return url ? `url('${ url }')` : null;
}

/* ------------------------------------------------------------------ */
/* Shared capability helpers.                                          */
/*                                                                     */
/* Each of these has a byte-exact twin in inc/emitter.php — the class  */
/* fragment order ([block classes] -> hover -> anim -> visibility ->    */
/* spacing) and the style pair order ([block pairs] -> typography ->    */
/* hover -> anim delay -> spacing) are part of the block validation     */
/* contract.                                                            */
/* ------------------------------------------------------------------ */

/**
 * Device visibility class fragments ('kbl3-hide-d' etc.).
 * PHP twin: kbl3_visibility().
 */
export function applyVisibility( attributes ) {
        const out = [];
        if ( attributes.hideD ) { out.push( 'kbl3-hide-d' ); }
        if ( attributes.hideT ) { out.push( 'kbl3-hide-t' ); }
        if ( attributes.hideM ) { out.push( 'kbl3-hide-m' ); }
        return out;
}

/**
 * Margin + padding spacing class fragments. PHP twin: kbl3_spacing_class().
 *
 * Contract: fragments are appended AFTER applyVisibility() in every block
 * class list (order: block classes -> hover -> anim -> visibility ->
 * spacing). Order inside: mt, mb, mr, ml, mtM, mbM. The matching
 * --kbl3-mt/--kbl3-mb/--kbl3-mr/--kbl3-ml/--kbl3-mt-m/--kbl3-mb-m style
 * pairs are always emitted together with the class (see spacingPairs), so a
 * class without its variable can never reach the DOM. Padding (pt/pr/pb/pl)
 * is inline-only (no class) — see spacingPairs().
 */
export function applySpacing( a ) {
        const out = [];
        if ( a.mt != null ) { out.push( 'kbl3-mt' ); }
        if ( a.mb != null ) { out.push( 'kbl3-mb' ); }
        if ( a.mr != null ) { out.push( 'kbl3-mr' ); }
        if ( a.ml != null ) { out.push( 'kbl3-ml' ); }
        if ( a.mtM != null ) { out.push( 'kbl3-mt-m' ); }
        if ( a.mbM != null ) { out.push( 'kbl3-mb-m' ); }
        return out;
}

/**
 * Margin + padding style pairs. PHP twin: kbl3_spacing_pairs().
 *
 * Contract: pairs are appended at the VERY END of every block style pair
 * list (after animDelayPairs). Unset values are null and get filtered by
 * toStyle(), so a block without spacing attributes emits zero output and
 * existing pages keep validating. Order: mt, mb, mr, ml, mtM, mbM, then
 * padding-top, padding-right, padding-bottom, padding-left as direct
 * inline styles (inline beats every class padding, so an explicit padding
 * always wins over a block's default skin).
 */
export function spacingPairs( a ) {
        return [
                [ '--kbl3-mt', a.mt != null ? `${ a.mt }px` : null ],
                [ '--kbl3-mb', a.mb != null ? `${ a.mb }px` : null ],
                [ '--kbl3-mr', a.mr != null ? `${ a.mr }px` : null ],
                [ '--kbl3-ml', a.ml != null ? `${ a.ml }px` : null ],
                [ '--kbl3-mt-m', a.mtM != null ? `${ a.mtM }px` : null ],
                [ '--kbl3-mb-m', a.mbM != null ? `${ a.mbM }px` : null ],
                [ 'padding-top', a.pt != null ? `${ a.pt }px` : null ],
                [ 'padding-right', a.pr != null ? `${ a.pr }px` : null ],
                [ 'padding-bottom', a.pb != null ? `${ a.pb }px` : null ],
                [ 'padding-left', a.pl != null ? `${ a.pl }px` : null ],
        ];
}

/**
 * Block-level box alignment pairs (max-width + auto margins). PHP twin:
 * kbl3_box_pairs(). Lets a text block sit left / center / right inside its
 * container INDEPENDENTLY of the text-align inside it — e.g. a centered
 * block whose lines stay left-aligned. Emitted BEFORE typographyPairs so an
 * explicit margin in spacingPairs() can still override the auto margin.
 */
export function boxPairs( a ) {
        return [
                [ 'max-width', a.contentWidth ? `${ a.contentWidth }px` : null ],
                [ 'margin-left', ( a.blockAlign === 'center' || a.blockAlign === 'right' ) ? 'auto' : null ],
                [ 'margin-right', a.blockAlign === 'center' ? 'auto' : null ],
        ];
}

/**
 * Typography style pairs (font size per device, weight, line height,
 * letter spacing, letter case). `include` lets each block expose only the
 * controls that make sense for it. PHP twin: kbl3_typography().
 */
export function typographyPairs( attributes, include = {} ) {
        const { fs = true, fw = true, lh = true, ls = true, tt = true } = include;
        const pairs = [];
        if ( fs ) {
                if ( attributes.fontSize != null ) { pairs.push( [ '--kbl3-fs', attributes.fontSize + 'px' ] ); }
                if ( attributes.fontSizeT != null ) { pairs.push( [ '--kbl3-fs-t', attributes.fontSizeT + 'px' ] ); }
                if ( attributes.fontSizeM != null ) { pairs.push( [ '--kbl3-fs-m', attributes.fontSizeM + 'px' ] ); }
        }
        if ( fw && attributes.fontWeight != null ) { pairs.push( [ '--kbl3-fw', String( attributes.fontWeight ) ] ); }
        if ( lh && attributes.lineHeight != null ) { pairs.push( [ '--kbl3-lh', String( attributes.lineHeight ) ] ); }
        if ( ls && attributes.letterSpacing != null ) { pairs.push( [ '--kbl3-ls', attributes.letterSpacing + 'px' ] ); }
        if ( tt && attributes.textTransform ) { pairs.push( [ 'text-transform', attributes.textTransform ] ); }
        return pairs;
}

/**
 * Entrance-animation class fragments. PHP twin: kbl3_anim().
 */
export function applyAnim( attributes ) {
        if ( ! attributes.anim ) {
                return [];
        }
        return [ 'kbl3-anim', `kbl3-anim--${ attributes.anim }` ];
}

/**
 * Animation-delay style pair (only with an animation set and delay > 0).
 * PHP twin: kbl3_anim_delay().
 */
export function animDelayPairs( attributes ) {
        if ( ! attributes.anim || ! ( attributes.animDelay > 0 ) ) {
                return [];
        }
        return [ [ '--kbl3-anim-d', attributes.animDelay + 'ms' ] ];
}

/**
 * Linear-gradient value for section/column/icon-box backgrounds.
 * Priority: bgURL image > gradient > solid color — a background image
 * disables the gradient. PHP twin: kbl3_gradient().
 */
export function gradientValue( attributes ) {
        if ( attributes.bgURL || ! attributes.gFrom || ! attributes.gTo ) {
                return null;
        }
        return `linear-gradient(${ attributes.gAngle ?? 135 }deg, ${ attributes.gFrom }, ${ attributes.gTo })`;
}

/* ------------------------------------------------------------------ */
/* Section shape dividers.                                             */
/* viewBox 0 0 1440 90, filled from the bottom edge.                   */
/* PHP twin: kbl3_divider() / kbl3_divider_paths().                    */
/* ------------------------------------------------------------------ */

export const DIVIDER_PATHS = {
        wave: 'M0,90 L0,60 C240,20 480,100 720,60 C960,20 1200,100 1440,60 L1440,90 Z',
        tilt: 'M0,90 L0,30 L1440,75 L1440,90 Z',
        arc: 'M0,90 L0,45 C360,0 1080,0 1440,45 L1440,90 Z',
};

/**
 * One divider SVG (top or bottom edge of a section). Attribute order is
 * part of the validation contract. PHP twin: kbl3_divider().
 */
export function dividerSvg( pos, shape, color ) {
        return el(
                'svg',
                {
                        className: cx( 'kbl3-divider', `kbl3-divider--${ pos }` ),
                        viewBox: '0 0 1440 90',
                        preserveAspectRatio: 'none',
                        'aria-hidden': true,
                        focusable: 'false',
                        style: toStyle( [ [ 'color', color ] ] ),
                },
                el( 'path', { fill: 'currentColor', d: DIVIDER_PATHS[ shape ] } )
        );
}

/* ------------------------------------------------------------------ */
/* Icon set — 24×24 stroke icons, currentColor.                        */
/* PHP twin: kbl3_icon_svg() / kbl3_icons().                          */
/* ------------------------------------------------------------------ */

export const ICONS = {
        'paint-roller': [
                'M3.5 5.5a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1h-10a1 1 0 0 1-1-1z',
                'M15.5 6.5h3a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H11',
                'M11 11.5v2',
                'M9.5 13.5h3v5a1 1 0 0 1-1 1h-1a1 1 0 0 1-1-1z',
        ],
        brush: [
                'M18.5 3.5l2 2-9.5 9.5-2-2z',
                'M9 13l2 2c-1 2.5-3 4-6 4 .5-3 2-5 4-6z',
        ],
        droplet: [
                'M12 3.5c3.5 4 6 7.2 6 10.2A6 6 0 0 1 6 13.7c0-3 2.5-6.2 6-10.2z',
        ],
        palette: [
                'M12 3a9 9 0 0 0 0 18c1.5 0 2-.8 2-1.8 0-1.4-1.6-1.7-1.6-3 0-1 .8-1.7 2-1.7h2.1A4.5 4.5 0 0 0 21 10c0-4-4-7-9-7z',
                'M7.5 10.5h.01',
                'M11 7.5h.01',
                'M15.5 8.5h.01',
        ],
        spray: [
                'M9 9.5h6V20a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1z',
                'M10.5 9.5V8a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1.5',
                'M12 7V5.5',
                'M16.5 4h.01',
                'M19 5.5h.01',
                'M18 8h.01',
                'M16.5 11h.01',
                'M19 9.5h.01',
        ],
        bucket: [
                'M4 8.5h13l-1.2 9.2a2 2 0 0 1-2 1.8H7.2a2 2 0 0 1-2-1.8z',
                'M4 8.5l2.5-4h8l2.5 4',
                'M19 3.5c1 1.2 1.5 2 1.5 2.8a1.5 1.5 0 0 1-3 0c0-.8.5-1.6 1.5-2.8z',
        ],
        layers: [
                'M12 3.5 20.5 8 12 12.5 3.5 8z',
                'M4.5 12.5 12 16.5l7.5-4',
                'M4.5 16.5 12 20.5l7.5-4',
        ],
        texture: [
                'M3.5 8.5c2.5-2.2 5-2.2 7.5 0s5 2.2 7.5 0',
                'M3.5 13c2.5-2.2 5-2.2 7.5 0s5 2.2 7.5 0',
                'M3.5 17.5c2.5-2.2 5-2.2 7.5 0s5 2.2 7.5 0',
        ],
        sparkles: [
                'M12 4l1.7 4.3L18 10l-4.3 1.7L12 16l-1.7-4.3L6 10l4.3-1.7z',
                'M18.5 15l.8 2 2 .8-2 .8-.8 2-.8-2-2-.8 2-.8z',
        ],
        'shield-check': [
                'M12 3.5l7 2.5v5c0 4.4-3 7.6-7 9.5-4-1.9-7-5.1-7-9.5V6z',
                'M9 11.5l2.2 2.2 4-4',
        ],
        leaf: [
                'M5 19C4 10 10 4 19.5 4.5 20 14 14 20 5 19z',
                'M6.5 17.5C9 12.5 12.5 9 17.5 6.5',
        ],
        globe: [
                'M12 3.5a8.5 8.5 0 1 0 0 17 8.5 8.5 0 0 0 0-17z',
                'M3.5 12h17',
                'M12 3.5c2.5 2.3 3.8 5.2 3.8 8.5s-1.3 6.2-3.8 8.5c-2.5-2.3-3.8-5.2-3.8-8.5s1.3-6.2 3.8-8.5z',
        ],
        'chevron-down': [
                'M6 9.5l6 6 6-6',
        ],
        award: [
                'M8.5 14.5 7 21l5-2.5L17 21l-1.5-6.5',
                'M12 3.5a5 5 0 1 1 0 10 5 5 0 0 1 0-10z',
        ],
        star: [
                'M12 4l2.4 4.9 5.4.8-3.9 3.8.9 5.4-4.8-2.5-4.8 2.5.9-5.4L4.2 9.7l5.4-.8z',
        ],
        heart: [
                'M12 20s-7.5-4.6-9.3-9A5 5 0 0 1 12 6.5 5 5 0 0 1 21.3 11c-1.8 4.4-9.3 9-9.3 9z',
        ],
        home: [
                'M4.5 10.5 12 4l7.5 6.5',
                'M6.5 9.5V20h11V9.5',
                'M10 20v-5.5h4V20',
        ],
        building: [
                'M4.5 20.5V5.5l8-2v17',
                'M12.5 20.5V10l7 2v8.5',
                'M7.5 8.5h.01',
                'M7.5 12h.01',
                'M7.5 15.5h.01',
                'M15.5 14h.01',
                'M15.5 17h.01',
                'M3 20.5h18',
        ],
        truck: [
                'M2.5 6.5h11v9h-11z',
                'M13.5 9.5h4l3 3v3h-7z',
                'M6.5 18.5a1.8 1.8 0 1 0 0-3.6 1.8 1.8 0 0 0 0 3.6z',
                'M16.5 18.5a1.8 1.8 0 1 0 0-3.6 1.8 1.8 0 0 0 0 3.6z',
        ],
        clock: [
                'M12 3.5a8.5 8.5 0 1 0 0 17 8.5 8.5 0 0 0 0-17z',
                'M12 7.5V12l3 2.5',
        ],
        phone: [
                'M21 16.5v3a1.5 1.5 0 0 1-1.7 1.5A19.5 19.5 0 0 1 3 4.7 1.5 1.5 0 0 1 4.5 3h3A1.5 1.5 0 0 1 9 4.3c.1 1 .4 2 .7 2.9a1.5 1.5 0 0 1-.3 1.6L7.9 9.3a15.5 15.5 0 0 0 7.8 7.8l.5-1.5a1.5 1.5 0 0 1 1.6-.3c.9.3 1.9.6 2.9.7a1.5 1.5 0 0 1 1.3 1.5z',
        ],
        mail: [
                'M3.5 6h17a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1h-17a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1z',
                'M3.5 7l8.5 6 8.5-6',
        ],
        'map-pin': [
                'M12 21s-6.5-5.5-6.5-11a6.5 6.5 0 0 1 13 0c0 5.5-6.5 11-6.5 11z',
                'M12 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5z',
        ],
        user: [
                'M12 4a4 4 0 1 0 0 8 4 4 0 0 0 0-8z',
                'M4.5 20.5c.8-3.8 3.9-6 7.5-6s6.7 2.2 7.5 6',
        ],
        users: [
                'M9 4.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7z',
                'M2.5 20c.7-3.3 3.3-5.2 6.5-5.2s5.8 1.9 6.5 5.2',
                'M16 5.2a3.5 3.5 0 0 1 0 6.6',
                'M17.8 14.9c1.9.7 3.3 2.3 3.7 4.6',
        ],
        check: [
                'M4.5 12.5l5 5 10-11',
        ],
        'arrow-right': [
                'M4.5 12h15',
                'M13.5 6l6 6-6 6',
        ],
        ruler: [
                'M3.5 14.5 14.5 3.5l6 6L9.5 20.5z',
                'M7 11l2 2',
                'M10 8l2 2',
                'M13 5l2 2',
        ],
        sun: [
                'M12 8.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7z',
                'M12 2.5v2',
                'M12 19.5v2',
                'M2.5 12h2',
                'M19.5 12h2',
                'M5 5l1.5 1.5',
                'M17.5 17.5 19 19',
                'M19 5l-1.5 1.5',
                'M6.5 17.5 5 19',
        ],
        flame: [
                'M12 3.5s1 2.5 1 4.5c0 1.5-1 2.5-2 2.5S9 9.5 9 8c-1.5 1.5-3 3.8-3 6.5a6 6 0 0 0 12 0c0-4.5-3.5-8-6-11z',
                'M12 20.5a3 3 0 0 1-3-3c0-1.8 1.5-3.2 3-5 1.5 1.8 3 3.2 3 5a3 3 0 0 1-3 3z',
        ],
        search: [
                'M10.5 4a6.5 6.5 0 1 0 0 13 6.5 6.5 0 0 0 0-13z',
                'M15.5 15.5 21 21',
        ],
        gear: [
                'M12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6z',
                'M12 2.5l1.2 2.6 2.8-.5.5 2.8 2.6 1.2-1.4 2.5 1.4 2.5-2.6 1.2-.5 2.8-2.8-.5L12 21.5l-1.2-2.6-2.8.5-.5-2.8-2.6-1.2L6.3 12 4.9 9.5l2.6-1.2.5-2.8 2.8.5z',
        ],
};

export const ICON_NAMES = Object.keys( ICONS );

/**
 * Render an icon as inline SVG. PHP twin: kbl3_icon_svg().
 * Attribute order is part of the validation contract.
 */
export function iconSvg( name ) {
        const paths = ICONS[ name ] || ICONS.check;
        return el(
                'svg',
                {
                        viewBox: '0 0 24 24',
                        fill: 'none',
                        stroke: 'currentColor',
                        strokeWidth: 1.7,
                        strokeLinecap: 'round',
                        strokeLinejoin: 'round',
                        'aria-hidden': true,
                },
                paths.map( ( d, i ) => el( 'path', { key: i, d } ) )
        );
}

/* ------------------------------------------------------------------ */
/* Misc                                                               */
/* ------------------------------------------------------------------ */

/** Alignment option lists (shared by toolbar + inspector). */
export const ALIGN_OPTIONS = [
        { value: 'left', label: 'Left' },
        { value: 'center', label: 'Center' },
        { value: 'right', label: 'Right' },
];
