/**
 * Stackable-style column width kit — shared by kbl/columns + kbl/column.
 *
 * Width model (ported from Stackable's src/components/columns-width-control):
 * the row's widths are shares that sum to ~100 once any column has been
 * touched. Changing one column hands the difference to its neighbor —
 * exactly like dragging a divider — and columns that were never touched stay
 * "auto" (undefined) and flex equally. Published markup is unchanged: each
 * column still saves `--kbl3-w` + `.kbl3-col--w`, so old pages and the PHP
 * emitter twin keep working byte-identically.
 *
 * Editor-only sugar lives here too: the layout picker icons, the presets
 * row, the proportional divider slider and the per-column number inputs.
 */
import { el, useRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Row } from './inspector.js';

/* ------------------------------------------------------------------ */
/* Width math (Stackable columns-width-control/util.js)                */
/* ------------------------------------------------------------------ */

export const COL_MIN = 10; /* inspector slider / input minimum share */
export const COL_MIN_CANVAS = 5; /* canvas drag minimum (Stackable uses 5%) */

/** Snap points while dragging: common fractions, or tens with Shift. */
export const SNAP_PCTS = [ 25, 33.333, 50, 66.667, 75, 100 ];
export const SNAP_TENS = [ 10, 20, 30, 40, 50, 60, 70, 80, 90, 100 ];

export const round1 = ( n ) => Math.round( n * 10 ) / 10;

export const columnWidthSum = ( widths ) =>
        widths.reduce( ( sum, w ) => sum + ( parseFloat( w ) || 0 ), 0 );

/** True when no column has a width yet — a pure "auto" row. */
export const allAuto = ( widths ) => widths.every( ( w ) => w == null || w === '' );

/**
 * Effective widths for display: autos flex equally, set values pass through.
 * Used by the slider + inputs so the UI always shows a full 100 split.
 */
export function effectiveWidths( widths ) {
        const even = round1( 100 / widths.length );
        return widths.map( ( w ) => ( w == null || w === '' ? even : parseFloat( w ) ) );
}

/**
 * Materialize a row: unset columns split the remainder of 100 equally, and
 * the last column absorbs any rounding residue so the row sums to exactly
 * 100. An all-auto row returns even shares without touching the autos.
 */
export function materializeRow( widths ) {
        const n = widths.length;
        const set = widths.filter( ( w ) => w != null && w !== '' );
        if ( ! set.length ) {
                return widths.map( () => round1( 100 / n ) );
        }
        const used = set.reduce( ( sum, w ) => sum + parseFloat( w ), 0 );
        const unsetCount = n - set.length;
        const share = unsetCount > 0 ? ( 100 - used ) / unsetCount : 0;
        const arr = widths.map( ( w ) => ( w == null || w === '' ? share : parseFloat( w ) ) );
        const diff = 100 - columnWidthSum( arr );
        if ( Math.abs( diff ) > 0.001 ) {
                arr[ n - 1 ] = parseFloat( arr[ n - 1 ] ) + diff;
        }
        return arr.map( round1 );
}

/**
 * Stackable fixColumnWidths: enforce every column >= min by borrowing from
 * neighbors (last pass right-to-left keeps the sum intact).
 */
export function fixColumnWidths( widths, min = COL_MIN ) {
        const cols = widths.map( ( w ) => parseFloat( w ) || 0 );
        for ( let i = 0; i < cols.length - 1; i++ ) {
                if ( cols[ i ] < min ) {
                        const extra = min - cols[ i ];
                        cols[ i ] = min;
                        cols[ i + 1 ] -= extra;
                }
        }
        for ( let i = cols.length - 1; i >= 1; i-- ) {
                if ( cols[ i ] < min ) {
                        const extra = min - cols[ i ];
                        cols[ i ] = min;
                        cols[ i - 1 ] -= extra;
                }
        }
        return cols.map( round1 );
}

/**
 * Apply a new width to column i: the adjacent column (next, or previous for
 * the last column) absorbs the difference, clamped so both keep >= min.
 * Ported from Stackable's ResizableTooltip.onTooltipChange.
 */
export function setColumnWidth( widths, i, value, min = COL_MIN_CANVAS ) {
        const arr = materializeRow( widths );
        const neighbor = i < arr.length - 1 ? i + 1 : i - 1;
        if ( neighbor < 0 ) {
                return arr;
        }
        const room = arr[ i ] + arr[ neighbor ];
        const v = round1( Math.min( Math.max( value, min ), Math.max( min, room - min ) ) );
        arr[ i ] = v;
        arr[ neighbor ] = round1( room - v );
        /* Dump any rounding residue into the neighbor so the row sums to 100. */
        const diff = 100 - columnWidthSum( arr );
        if ( Math.abs( diff ) > 0.001 ) {
                arr[ neighbor ] = round1( arr[ neighbor ] + diff );
        }
        return arr;
}

/** Snap a dragged width to the common fractions (or tens with Shift). */
export function snapWidth( value, shiftKey, gap = 1.2 ) {
        const snaps = shiftKey ? SNAP_TENS : SNAP_PCTS;
        for ( const s of snaps ) {
                if ( Math.abs( value - s ) < gap ) {
                        return s;
                }
        }
        return value;
}

/* ------------------------------------------------------------------ */
/* Layout presets + picker (Stackable columns/variations.js)           */
/* ------------------------------------------------------------------ */

/**
 * The seven insert-time layouts, mirroring Stackable's variation picker.
 * `widths: null` means "leave every column auto" (an equal split) — only
 * asymmetric layouts need explicit shares.
 */
export function columnLayouts() {
        return [
                {
                        name: 'one',
                        title: '100',
                        description: __( 'One column', 'kabel-blocks' ),
                        widths: null,
                },
                {
                        name: 'two-equal',
                        title: '50 / 50',
                        description: __( 'Two columns; equal split', 'kabel-blocks' ),
                        widths: null,
                        isDefault: true,
                },
                {
                        name: 'two-30-70',
                        title: '30 / 70',
                        description: __( 'Two columns; one-third, two-thirds split', 'kabel-blocks' ),
                        widths: [ 30, 70 ],
                },
                {
                        name: 'two-70-30',
                        title: '70 / 30',
                        description: __( 'Two columns; two-thirds, one-third split', 'kabel-blocks' ),
                        widths: [ 70, 30 ],
                },
                {
                        name: 'three-equal',
                        title: '33 / 33 / 33',
                        description: __( 'Three columns; equal split', 'kabel-blocks' ),
                        widths: null,
                },
                {
                        name: 'three-25-50-25',
                        title: '25 / 50 / 25',
                        description: __( 'Three columns; wide center column', 'kabel-blocks' ),
                        widths: [ 25, 50, 25 ],
                },
                {
                        name: 'four-equal',
                        title: '25 / 25 / 25 / 25',
                        description: __( 'Four columns; equal split', 'kabel-blocks' ),
                        widths: null,
                },
        ];
}

/** innerBlocks spec for a layout (variation picker + registerBlockVariation). */
export function layoutBlocks( layout ) {
        const count = layout.widths ? layout.widths.length : ( layout.name === 'one' ? 1 : layout.name === 'three-equal' ? 3 : layout.name.startsWith( 'four' ) ? 4 : 2 );
        return Array.from( { length: count }, ( _, i ) => [
                'kbl/column',
                layout.widths ? { width: layout.widths[ i ] } : {},
        ] );
}

/** Tiny proportional SVG used by the picker + preset buttons. */
export function layoutIcon( widths, size ) {
        const px = size || 46;
        const n = widths.length;
        const gap = 3;
        const inner = px - 6;
        const rects = [];
        let x = 3;
        for ( let i = 0; i < n; i++ ) {
                const w = ( widths[ i ] / 100 ) * ( inner - gap * ( n - 1 ) );
                rects.push(
                        el( 'rect', {
                                key: i,
                                x: round1( x ),
                                y: px * 0.31,
                                width: Math.max( 1, round1( w ) ),
                                height: round1( px * 0.38 ),
                                rx: 1.5,
                                fill: 'currentColor',
                                opacity: 0.9,
                        } )
                );
                x += w + gap;
        }
        return el(
                'svg',
                {
                        viewBox: `0 0 ${ px } ${ px }`,
                        width: px,
                        height: px,
                        xmlns: 'http://www.w3.org/2000/svg',
                        'aria-hidden': 'true',
                        focusable: 'false',
                },
                el( 'rect', {
                        x: 1.25,
                        y: px * 0.25,
                        width: px - 2.5,
                        height: px * 0.5,
                        rx: 3,
                        fill: 'none',
                        stroke: 'currentColor',
                        strokeWidth: 1.5,
                        opacity: 0.45,
                } ),
                rects
        );
}

/* ------------------------------------------------------------------ */
/* Inspector: <ColumnWidthsControl count={n} values={[..]} onChange />  */
/* ------------------------------------------------------------------ */

/** Preset thumbnails per column count (Stackable COLUMN_PRESET_OPTIONS). */
const PRESETS = {
        2: [
                { widths: [ 50, 50 ] },
                { widths: [ 70, 30 ] },
                { widths: [ 30, 70 ] },
        ],
        3: [
                { widths: [ 33.3, 33.3, 33.4 ] },
                { widths: [ 25, 50, 25 ] },
                { widths: [ 50, 25, 25 ] },
                { widths: [ 25, 25, 50 ] },
        ],
        4: [
                { widths: [ 25, 25, 25, 25 ] },
                { widths: [ 40, 20, 20, 20 ] },
                { widths: [ 20, 20, 20, 40 ] },
        ],
};

const presetTitle = ( widths ) => widths.map( ( w ) => String( Math.round( w ) ) ).join( ' / ' );

export function ColumnWidthsControl( { count, values, onChange } ) {
        if ( ! count || count < 2 ) {
                return null;
        }
        /* Display only: an all-auto row shows its even split; a touched row is
         * passed through RAW — transient typing states (sum ≠ 100) must survive
         * untouched, normalization happens on blur like Stackable's inputs. */
        const eff = allAuto( values )
                ? values.map( () => round1( 100 / values.length ) )
                : values.map( ( w ) => ( w == null || w === '' ? '' : parseFloat( w ) ) );
        const matches = ( widths ) =>
                widths.length === count && widths.every( ( w, i ) => Math.abs( w - eff[ i ] ) < 0.6 );
        return el(
                Row,
                { label: __( 'Column widths', 'kabel-blocks' ) },
                el(
                        'div',
                        { className: 'kbl3-colw' },
                        PRESETS[ count ]
                                ? el(
                                        'div',
                                        { className: 'kbl3-colw-presets' },
                                        PRESETS[ count ].map( ( p ) =>
                                                el(
                                                        'button',
                                                        {
                                                                key: presetTitle( p.widths ),
                                                                type: 'button',
                                                                className: 'kbl3-colw-preset' + ( matches( p.widths ) ? ' is-active' : '' ),
                                                                title: presetTitle( p.widths ),
                                                                'aria-label': __( 'Column layout preset', 'kabel-blocks' ) + ' ' + presetTitle( p.widths ),
                                                                onClick: () => onChange( p.widths ),
                                                        },
                                                        p.widths.map( ( w, i ) =>
                                                                el( 'span', { key: i, className: 'kbl3-colw-box', style: { flex: `${ w } 1 0` } } )
                                                        )
                                                )
                                        )
                                )
                                : null,
                        count <= 7 ? el( RatioSlider, { value: eff, onChange } ) : null,
                        el( WidthInputs, { value: eff, onChange } ),
                        el(
                                'p',
                                { className: 'kbl3-colw-hint' },
                                __( 'Drag the dividers or type exact numbers — e.g. 40 + 40 + 20.', 'kabel-blocks' )
                        )
                )
        );
}

/* Proportional bar with draggable dividers between neighboring columns. */
function RatioSlider( { value, onChange } ) {
        const drag = useRef( null );

        const onDown = ( e, i ) => {
                e.preventDefault();
                e.stopPropagation();
                try {
                        e.currentTarget.setPointerCapture( e.pointerId );
                } catch ( err ) {
                        return;
                }
                const bar = e.currentTarget.closest( '.kbl3-colw-bar' );
                if ( ! bar || ! bar.clientWidth ) {
                        return;
                }
                drag.current = {
                        id: e.pointerId,
                        i,
                        startX: e.clientX,
                        barW: bar.clientWidth,
                        start: value.map( ( w ) => parseFloat( w ) ),
                };
        };
        const onMove = ( e ) => {
                const d = drag.current;
                if ( ! d || d.id !== e.pointerId ) {
                        return;
                }
                const delta = ( ( e.clientX - d.startX ) / d.barW ) * 100;
                const room = d.start[ d.i ] + d.start[ d.i + 1 ];
                let a = d.start[ d.i ] + delta;
                a = Math.min( Math.max( a, COL_MIN ), room - COL_MIN );
                a = snapWidth( a, e.shiftKey );
                a = round1( a );
                const arr = d.start.map( ( w ) => w );
                arr[ d.i ] = a;
                arr[ d.i + 1 ] = round1( room - a );
                onChange( arr );
        };
        const onUp = ( e ) => {
                const d = drag.current;
                if ( ! d ) {
                        return;
                }
                drag.current = null;
                try {
                        if ( e.currentTarget.hasPointerCapture && e.currentTarget.hasPointerCapture( e.pointerId ) ) {
                                e.currentTarget.releasePointerCapture( e.pointerId );
                        }
                } catch ( err ) {
                        /* Already released. */
                }
        };

        let cumulative = 0;
        const dividers = value.slice( 0, -1 ).map( ( w, i ) => {
                cumulative += parseFloat( w );
                return el( 'span', {
                        key: 'div' + i,
                        className: 'kbl3-colw-div',
                        'data-testid': 'kbl-colw-div',
                        style: { left: cumulative + '%' },
                        onPointerDown: ( e ) => onDown( e, i ),
                        onPointerMove: onMove,
                        onPointerUp: onUp,
                        onPointerCancel: onUp,
                        role: 'separator',
                        'aria-orientation': 'vertical',
                        tabIndex: -1,
                } );
        } );

        return el(
                'div',
                { className: 'kbl3-colw-bar' },
                value.map( ( w, i ) =>
                        el(
                                'span',
                                { key: i, className: 'kbl3-colw-seg', style: { flex: `${ parseFloat( w ) } 1 0` } },
                                el( 'b', null, String( Math.round( parseFloat( w ) ) ) )
                        )
                ),
                dividers
        );
}

/* One number input per column; the grid mirrors the real proportions. */
function WidthInputs( { value, onChange } ) {
        const style = {
                gridTemplateColumns: value
                        .map( ( w ) => `${ ( ( parseFloat( w ) / 100 ) * value.length ).toFixed( 2 ) }fr` )
                        .join( ' ' ),
        };
        return el(
                'div',
                { className: 'kbl3-colw-inputs' + ( value.length > 4 ? ' is-many' : '' ), style },
                value.map( ( w, i ) =>
                        el( 'input', {
                                key: i,
                                type: 'number',
                                step: 'any',
                                min: 0,
                                max: 100,
                                className: 'kbl3-colw-input',
                                'data-testid': 'kbl-colw-input',
                                'data-col': i,
                                value: w,
                                'aria-label': __( 'Column width', 'kabel-blocks' ) + ' ' + ( i + 1 ),
                                /* Transient update — normalization happens on blur, exactly
                                 * like Stackable's ColumnsInputs. */
                                onChange: ( e ) => {
                                        const v = parseFloat( e.target.value );
                                        const arr = value.map( ( x ) => parseFloat( x ) );
                                        arr[ i ] = isNaN( v ) ? '' : Math.round( v * 1000 ) / 1000;
                                        onChange( arr );
                                },
                                onBlur: () => {
                                        const arr = value.map( ( w2 ) =>
                                                w2 === '' ? round1( 100 / value.length ) : Math.min( 100, Math.max( 0, parseFloat( w2 ) ) )
                                        );
                                        const diff = 100 - columnWidthSum( arr );
                                        const j = i < arr.length - 1 ? i + 1 : i - 1;
                                        if ( j >= 0 ) {
                                                arr[ j ] = round1( arr[ j ] + diff );
                                        }
                                        onChange( fixColumnWidths( arr ) );
                                },
                        } )
                )
        );
}

/* ------------------------------------------------------------------ */
/* Inspector: <ColumnSortControl count={n} onMove={ from, to => … } /> */
/* ------------------------------------------------------------------ */

export function ColumnSortControl( { count, onMove } ) {
        if ( ! count || count < 2 ) {
                return null;
        }
        const chip = ( i ) =>
                el(
                        'span',
                        { key: i, className: 'kbl3-colsort__chip' },
                        el( 'button', {
                                type: 'button',
                                className: 'kbl3-colsort__btn',
                                disabled: i === 0,
                                title: __( 'Move left', 'kabel-blocks' ),
                                'aria-label': __( 'Move left', 'kabel-blocks' ) + ' ' + ( i + 1 ),
                                onClick: () => onMove( i, i - 1 ),
                        }, '‹' ),
                        el( 'b', null, String( i + 1 ) ),
                        el( 'button', {
                                type: 'button',
                                className: 'kbl3-colsort__btn',
                                disabled: i === count - 1,
                                title: __( 'Move right', 'kabel-blocks' ),
                                'aria-label': __( 'Move right', 'kabel-blocks' ) + ' ' + ( i + 1 ),
                                onClick: () => onMove( i, i + 1 ),
                        }, '›' )
                );
        return el(
                Row,
                { label: __( 'Column order', 'kabel-blocks' ) },
                el( 'div', { className: 'kbl3-colsort' }, Array.from( { length: count }, ( _, i ) => chip( i ) ) )
        );
}
