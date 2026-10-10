/**
 * Kabel Blocks v4 — the shared editor kit (single entry for every block).
 *
 * Layered on top of src/inspector.js (the 3-tab Layout/Style/Advanced
 * shell + control set) and src/util.js (the byte-exact PHP-twin helpers).
 * This module adds the Stackable-style responsive v2 layer:
 *
 *  - DeviceContext: a module-level store shared by every Resp group in the
 *    inspector (page-tab scoped memory — picking "tablet" keeps every
 *    responsive control in tablet mode until switched back).
 *  - <Resp>: wraps any row control and reads/writes the device-suffixed
 *    attribute: {prop} (desktop) / {prop}T (tablet ≤1080px) /
 *    {prop}M (mobile ≤781px).
 *  - useUid(): per-instance unique id. Generated on mount, regenerated when
 *    a duplicate uid is spotted in the page AND this block is selected
 *    (Stackable's approach — the user is looking at the copy they edit).
 *  - respCss()/uidClass()/respStyleEl(): the responsive output. EVERY
 *    breakpoint value — desktop included — rides the per-instance
 *    <style> tag emitted as the last child of the saved markup:
 *      .kbl-u-x{--kbl-mt:24px;--kbl-mb:12px}
 *      @media (max-width:1080px){.kbl-u-x{--kbl-mt:12px}}
 *      @media (max-width:781px){.kbl-u-x{--kbl-mt:8px}}
 *    The desktop value deliberately stays OUT of the inline style attr:
 *    an inline custom property beats every media-query rule, so the
 *    tablet/mobile overrides could never win (the reason Stackable v3
 *    moved desktop values into the generated sheet too). The static CSS
 *    consumes the variables from the block base rules (e.g.
 *    `.kbl3-h{margin-top:var(--kbl-mt,0)}`) which sit BEFORE the legacy
 *    `.kbl3-mt` utility classes — old content keeps rendering through the
 *    later class rules, new content (uid set, legacy classes no longer
 *    emitted) reads the variables. PHP twin: kbl4_resp_css().
 *  - scopeCss(): the Advanced-tab custom CSS escaper — every selector is
 *    prefixed with `.kbl-u-{uid} ` (recursing through @media/@supports,
 *    leaving @keyframes untouched). PHP twin: kbl4_scope_css().
 *
 * Legacy v3 content (no uid attribute) never matches any of this: it keeps
 * rendering through the unchanged --kbl3-* class/variable system.
 */
import { el, useState, useEffect, cloneElement } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { TextareaControl, TextControl } from '@wordpress/components';

/* Single entry for the whole control set (v3 shell + everything above). */
export * from './inspector.js';

/* ------------------------------------------------------------------ */
/* Device context — shared store with a context-shaped API            */
/* ------------------------------------------------------------------ */

let currentDevice = 'd';
const listeners = new Set();

export const DeviceContext = {
        /** Current device: 'd' | 't' | 'm'. */
        get: () => currentDevice,
        /** Switch every Resp group at once. */
        set( device ) {
                if ( device === currentDevice ) {
                        return;
                }
                currentDevice = device;
                listeners.forEach( ( fn ) => fn() );
        },
        /** React-style subscription (useDevice consumes it). */
        subscribe( fn ) {
                listeners.add( fn );
                return () => listeners.delete( fn );
        },
};

/** Hook: [ device, setDevice ] shared across every Resp group. */
export function useDevice() {
        const [ device, setDevice ] = useState( currentDevice );
        useEffect( () => DeviceContext.subscribe( () => setDevice( currentDevice ) ), [] );
        return [ device, DeviceContext.set ];
}

const DEVICE_OPTIONS = [
        { value: 'd', dash: 'dashicons-desktop', label: __( 'Desktop', 'kabel-blocks' ) },
        { value: 't', dash: 'dashicons-tablet', label: __( 'Tablet', 'kabel-blocks' ) },
        { value: 'm', dash: 'dashicons-smartphone', label: __( 'Mobile', 'kabel-blocks' ) },
];

/* The three device buttons (shared chrome of every Resp group). */
function DeviceButtons( { device, onSwitch } ) {
        return el(
                'div',
                { className: 'kbl-resp__switch', role: 'group', 'aria-label': __( 'Responsive mode', 'kabel-blocks' ) },
                DEVICE_OPTIONS.map( ( d ) =>
                        el(
                                'button',
                                {
                                        key: d.value,
                                        type: 'button',
                                        className: 'kbl-resp__dev' + ( device === d.value ? ' is-active' : '' ),
                                        'aria-pressed': device === d.value,
                                        title: d.label,
                                        onClick: () => onSwitch( d.value ),
                                },
                                el( 'span', { className: 'dashicons ' + d.dash, 'aria-hidden': true } ),
                                el( 'span', { className: 'screen-reader-text' }, d.label )
                        )
                )
        );
}

/* ------------------------------------------------------------------ */
/* <Resp> — the responsive control wrapper                             */
/* ------------------------------------------------------------------ */

/**
 * <Resp prop="fontSize" attrs={ attributes } setAttrs={ setAttributes }>
 *         { el( RangeRow, { label: …, min: 8, max: 200 } ) }
 * </Resp>
 *
 * Clones the child control with value/onChange bound to the suffixed
 * attribute of the active device (fontSize / fontSizeT / fontSizeM). The
 * child must accept { value, onChange } — every Row control does.
 *
 * `desktopHint`: for props that only exist below desktop (e.g. the columns
 * per-row count — the desktop row is the child count itself), renders the
 * hint instead of the control while the desktop device is active.
 */
export function Resp( { prop, attrs, setAttrs, children, showFollows = true, desktopHint = '' } ) {
        const [ device, setDevice ] = useDevice();
        const key = device === 't' ? prop + 'T' : device === 'm' ? prop + 'M' : prop;
        const value = attrs ? attrs[ key ] : undefined;
        const onChange = ( v ) => setAttrs( { [ key ]: v === null ? undefined : v } );
        const child = cloneElement( children, { value, onChange } );
        const follows = showFollows && device !== 'd' && ( ! attrs || attrs[ key ] == null );
        return el(
                'div',
                { className: 'kbl-resp' },
                el( DeviceButtons, { device, onSwitch: setDevice } ),
                follows ? el( 'span', { className: 'kbl-resp__follows' }, __( 'Follows desktop', 'kabel-blocks' ) ) : null,
                desktopHint && device === 'd'
                        ? el( 'p', { className: 'kbl-resp__desktop-hint' }, desktopHint )
                        : child
        );
}

/* ------------------------------------------------------------------ */
/* uid — per-instance class + duplicate guard                          */
/* ------------------------------------------------------------------ */

/** Fresh uid: 'k' + 6 chars of base36 (k + [a-z0-9]{6}). */
export function newUid() {
        return 'k' + Math.random().toString( 36 ).slice( 2, 8 );
}

/**
 * Mount hook for every v4 block: generates the uid once, then watches the
 * page for duplicates (copies made through the block duplicator keep the
 * same uid). A duplicate regenerates ONLY on the selected copy — both
 * copies never ping-pong (Stackable's rule).
 */
export function useUid( attributes, setAttributes, clientId, isSelected ) {
        useEffect( () => {
                if ( ! attributes.uid ) {
                        setAttributes( { uid: newUid() } );
                }
                /* eslint-disable-next-line react-hooks/exhaustive-deps */
        }, [] );

        const isDuplicate = useSelect(
                ( select ) => {
                        if ( ! attributes.uid || ! isSelected ) {
                                return false;
                        }
                        const { getBlocks } = select( 'core/block-editor' );
                        let count = 0;
                        const walk = ( list ) => {
                                for ( const block of list ) {
                                        if ( block && block.attributes && block.attributes.uid === attributes.uid ) {
                                                count++;
                                        }
                                        if ( block && block.innerBlocks && block.innerBlocks.length ) {
                                                walk( block.innerBlocks );
                                        }
                                }
                        };
                        walk( getBlocks() || [] );
                        return count > 1;
                },
                [ attributes.uid, isSelected ]
        );

        useEffect( () => {
                if ( isDuplicate ) {
                        setAttributes( { uid: newUid() } );
                }
                /* eslint-disable-next-line react-hooks/exhaustive-deps */
        }, [ isDuplicate ] );
}

/** The per-instance class fragment ('kbl-u-xxxx' or ''). */
export function uidClass( attributes ) {
        return attributes && attributes.uid ? 'kbl-u-' + attributes.uid : '';
}

/**
 * Copy of `attributes` with the listed keys removed. v4 blocks hand this
 * copy to the legacy pair helpers (applySpacing / spacingPairs /
 * typographyPairs / boxPairs) so the props that moved to the responsive
 * per-instance <style> tag stop emitting their old --kbl3-* inline
 * variables and utility classes — otherwise both systems would fire and
 * the legacy utility class (later in the stylesheet) would win, killing
 * the tablet/mobile overrides. Without a uid the copy is the original
 * (legacy content keeps the full v3 path). PHP twin: kbl4_strip().
 */
export function v4Strip( attributes, keys ) {
        if ( ! attributes || ! attributes.uid ) {
                return attributes;
        }
        const out = { ...attributes };
        for ( const k of keys || [] ) {
                delete out[ k ];
        }
        return out;
}

/* ------------------------------------------------------------------ */
/* Responsive output — the per-instance <style> tag                   */
/* ------------------------------------------------------------------ */

/**
 * Build the responsive CSS for one block instance. PHP twin: kbl4_resp_css().
 *
 * spec entries — one per responsive attribute triple ({a} / {a}T / {a}M):
 *   { a: 'mt', p: '--kbl-mt', u: 'px' }
 *       a   base attribute key; the tablet/mobile keys are aT / aM.
 *       p   CSS property emitted (custom property or a real one).
 *       u   unit appended to numeric values (omit for raw numbers).
 *   Extra fields:
 *       sel    selector suffix — the rule targets `.kbl-u-x{sel}` instead
 *              of the root (used when the value styles a descendant, e.g.
 *              the columns flex-basis). Gets its own rule per breakpoint.
 *       decls  extra declarations glued into the same sel rule
 *              ('display:flex;flex-direction:column').
 *       raw    fixed value ('row') — emitted wherever the attr is set,
 *              the attribute only gates WHERE it appears.
 *       calc   special formatter id ('cols' → the per-row wrap basis).
 *       only   restrict the entry to one bucket: 'd' | 't' | 'm'.
 *       d      desktop default emitted into the base rule when the
 *              attribute is unset — a string ('24px', 'auto') or
 *              { key, map, fallback } to read a preset-dependent default
 *              (section padding follows the pad preset). Defaults keep
 *              same-type nesting honest: an inner v4 block always states
 *              its own value, so it never inherits an ancestor's.
 *
 * Output shape (only filled buckets are emitted):
 *   .kbl-u-x{--kbl-mt:24px;--kbl-mb:12px}
 *   @media (max-width:1080px){.kbl-u-x{--kbl-mt:12px}}
 *   @media (max-width:781px){.kbl-u-x{--kbl-mt:8px}}
 * Media queries override the base rule purely by source order — no
 * !important anywhere (the desktop value lives in the same sheet, not in
 * an inline style). Returns null without a uid or without any value.
 */
export function respCss( attributes, spec ) {
        if ( ! attributes || ! attributes.uid ) {
                return null;
        }
        const root = `.kbl-u-${ attributes.uid }`;
        const buckets = {
                d: { root: [], rules: [] },
                t: { root: [], rules: [] },
                m: { root: [], rules: [] },
        };
        for ( const entry of spec || [] ) {
                const targets = entry.only === 'd' ? [ [ 'd', '' ] ]
                        : entry.only === 't' ? [ [ 't', 'T' ] ]
                        : entry.only === 'm' ? [ [ 'm', 'M' ] ]
                        : [ [ 'd', '' ], [ 't', 'T' ], [ 'm', 'M' ] ];
                for ( const [ bucket, suffix ] of targets ) {
                        const raw = attributes[ entry.a + suffix ];
                        if ( raw == null ) {
                                continue;
                        }
                        const decl = `${ entry.p }:${ entryValue( entry, raw ) }`;
                        if ( entry.sel ) {
                                buckets[ bucket ].rules.push(
                                        `${ root }${ entry.sel }{${ entry.decls ? entry.decls + ';' : '' }${ decl }}`
                                );
                        } else {
                                buckets[ bucket ].root.push( decl );
                        }
                }
                if ( entry.d != null && ! entry.only && ! entry.sel && attributes[ entry.a ] == null ) {
                        const def = typeof entry.d === 'string'
                                ? entry.d
                                : ( entry.d.map[ attributes[ entry.d.key ] ] || entry.d.fallback );
                        buckets.d.root.push( `${ entry.p }:${ def }` );
                }
        }
        const parts = [];
        if ( buckets.d.root.length ) {
                parts.push( `${ root }{${ buckets.d.root.join( ';' ) }}` );
        }
        parts.push( ...buckets.d.rules );
        for ( const [ bucket, query ] of [ [ 't', '(max-width:1080px)' ], [ 'm', '(max-width:781px)' ] ] ) {
                const b = buckets[ bucket ];
                if ( ! b.root.length && ! b.rules.length ) {
                        continue;
                }
                let inner = '';
                if ( b.root.length ) {
                        inner += `${ root }{${ b.root.join( ';' ) }}`;
                }
                inner += b.rules.join( '' );
                parts.push( `@media ${ query }{${ inner }}` );
        }
        return parts.length ? parts.join( '\n' ) : null;
}

/** Value of one spec entry for one raw attribute value. */
function entryValue( entry, raw ) {
        if ( entry.raw != null ) {
                return entry.raw;
        }
        if ( entry.calc === 'cols' ) {
                return `calc((100% - ${ raw - 1 } * var(--kbl-colgap, var(--kbl3-gap, 24px))) / ${ raw })`;
        }
        return String( raw ) + ( entry.u == null ? '' : entry.u );
}

/**
 * The shared <style> element for one instance (responsive overrides +
 * scoped custom CSS). Returns null when there is nothing to emit — legacy
 * content without a uid stays byte-identical. React leaves <style>
 * children unescaped, so the PHP twin can emit the same bytes raw.
 */
export function respStyleEl( attributes, spec ) {
        const parts = [];
        const responsive = respCss( attributes, spec );
        if ( responsive ) {
                parts.push( responsive );
        }
        if ( attributes && attributes.uid && attributes.cssText ) {
                parts.push( scopeCss( attributes.cssText, attributes.uid ) );
        }
        if ( ! parts.length ) {
                return null;
        }
        return el( 'style', null, parts.join( '\n' ) );
}

/* ------------------------------------------------------------------ */
/* Custom CSS scoper (Advanced tab)                                    */
/* ------------------------------------------------------------------ */

/**
 * Prefix every selector of a user stylesheet with `.kbl-u-{uid} `.
 *
 * A deliberately small CSS walker: handles nested @media/@supports
 * (recursively) and leaves @keyframes untouched (their inner selectors
 * are percentage steps, not element selectors). `</` is escaped so the
 * payload can never break out of the <style> element, and @import /
 * @charset statements are dropped — they are invalid inside a scoped
 * block anyway.
 */
export function scopeCss( css, uid ) {
        const safe = String( css )
                .replace( /\/\*[\s\S]*?\*\//g, '' ) /* comments */
                .replace( /<\/\s*(style|script)/gi, '\\3C $1' ) /* no early </style> */
                .trim();
        if ( ! safe ) {
                return '';
        }
        const prefix = `.kbl-u-${ uid } `;
        return scopeBlock( safe );

        function scopeBlock( text ) {
                let out = '';
                let i = 0;
                const n = text.length;
                while ( i < n ) {
                        /* Find the next top-level `{`. */
                        const open = text.indexOf( '{', i );
                        if ( open === -1 ) {
                                break;
                        }
                        const rawSelector = text.slice( i, open ).trim();
                        /* Find the matching close brace. */
                        let depth = 1;
                        let j = open + 1;
                        while ( j < n && depth > 0 ) {
                                if ( text[ j ] === '{' ) {
                                        depth++;
                                } else if ( text[ j ] === '}' ) {
                                        depth--;
                                }
                                j++;
                        }
                        const body = text.slice( open + 1, j - 1 ).trim();
                        i = j;

                        if ( ! rawSelector ) {
                                continue;
                        }
                        const at = rawSelector.startsWith( '@' );
                        if ( at && /^@(media|supports|layer|container)\b/i.test( rawSelector ) ) {
                                /* Nested group rule: recurse into the body. */
                                const inner = scopeBlock( body );
                                if ( inner ) {
                                        out += `${ rawSelector }{${ inner }}`;
                                }
                        } else if ( at && /^@keyframes\b/i.test( rawSelector ) ) {
                                /* Keyframe steps are not element selectors — keep verbatim. */
                                out += `${ rawSelector }{${ body }}`;
                        } else if ( at ) {
                                /* @import / @charset / @font-face …: drop (unscopable). */
                        } else {
                                const scoped = splitSelectors( rawSelector )
                                        .map( ( sel ) => prefix + sel.trim() )
                                        .filter( Boolean )
                                        .join( ',' );
                                if ( scoped && body ) {
                                        out += `${ scoped }{${ body }}`;
                                }
                        }
                }
                return out;
        }

        /* Split a selector list on commas that sit outside parens/brackets. */
        function splitSelectors( selectorList ) {
                const parts = [];
                let depth = 0;
                let current = '';
                for ( const ch of selectorList ) {
                        if ( ch === '(' || ch === '[' ) {
                                depth++;
                        } else if ( ch === ')' || ch === ']' ) {
                                depth = Math.max( 0, depth - 1 );
                        }
                        if ( ch === ',' && depth === 0 ) {
                                parts.push( current );
                                current = '';
                        } else {
                                current += ch;
                        }
                }
                if ( current.trim() ) {
                        parts.push( current );
                }
                return parts.filter( ( p ) => p.trim() );
        }
}

/* ------------------------------------------------------------------ */
/* Advanced tab v2 — custom CSS + anchor                               */
/* ------------------------------------------------------------------ */

/**
 * <AdvancedPanelV2 attrs setAttrs /> — appended by KblInspector for v4
 * blocks (they pass v4: true). Extends the v1 AdvancedPanel pair
 * (custom class / z-index / overflow / transform, still appended) with
 * the per-instance custom CSS box and the HTML anchor.
 */
export function AdvancedPanelV2( { attrs, setAttrs } ) {
        return el(
                'div',
                { className: 'kbl-adv-v2' },
                el( TextareaControl, {
                        label: __( 'Custom CSS', 'kabel-blocks' ),
                        value: attrs.cssText || '',
                        onChange: ( v ) => setAttrs( { cssText: v || undefined } ),
                        rows: 6,
                        placeholder: '.kbl3-h {\n  color: #B39A74;\n}',
                        help: __( 'Scoped to this block only — every selector is prefixed automatically.', 'kabel-blocks' ),
                } ),
                el( 'p', { className: 'kbl3-panel-hint' }, __( 'Selectors are prefixed with this block’s unique class; @media blocks are supported.', 'kabel-blocks' ) ),
                el( TextControl, {
                        label: __( 'HTML anchor', 'kabel-blocks' ),
                        value: attrs.anchor || '',
                        placeholder: __( 'my-section', 'kabel-blocks' ),
                        onChange: ( v ) => setAttrs( { anchor: v.replace( /[^A-Za-z0-9_-]/g, '-' ) || undefined } ),
                        help: __( 'Used as the id attribute — link to it with #anchor.', 'kabel-blocks' ),
                } )
        );
}
