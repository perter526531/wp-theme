/**
 * kbl/column — one free container inside a columns row.
 *
 * v11 ports Stackable's canvas interaction:
 *  - Both edges are drag handles (right unless last, left unless first);
 *    dragging moves the boundary with the neighbor — the neighbor absorbs
 *    the difference so the row always sums to ~100.
 *  - While dragging, widths snap to the common fractions (25 / 33.3 / 50 /
 *    66.7 / 75 / 100) — or to every 10 with Shift held.
 *  - The width badge at the column corner is clickable: it opens a small
 *    numeric input (Enter applies, Esc closes, Tab hops to the next column),
 *    mirroring Stackable's ResizableTooltip popup.
 *
 * The width control keeps the "share of the row" model: setting one column
 * materializes its unset siblings, then the row always sums to ~100
 * (40 + 40 + 20 style). Background color or image, padding, radius, shadow,
 * border, text color and content alignment are unchanged.
 */
import { el, Fr, useRef, useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { register } from '../register.js';
import {
        useBlockProps,
        useInnerBlocksProps,
        InnerBlocks,
        BlockControls,
} from '@wordpress/block-editor';
import { useSelect, useDispatch } from '@wordpress/data';
import { ToolbarGroup, ToolbarButton } from '@wordpress/components';
import {
        cx,
        toStyle,
        bgUrl,
        SHADOWS,
        applyVisibility,
        applySpacing,
        spacingPairs,
        applyAnim,
        animDelayPairs,
        gradientValue,
} from '../util.js';
import {
        KblInspector,
        RangeRow,
        SegButtons,
        ColorsPanel,
        BgImageControl,
        BorderPanel,
        SpacingPanel,
        VisibilityPanel,
        AnimPanel,
        GradientPanel,
} from '../inspector.js';
import {
        setColumnWidth,
        materializeRow,
        round1,
        snapWidth,
        COL_MIN_CANVAS,
} from '../columns-kit.js';

register( 'kbl/column', {
        apiVersion: 3,
        title: __( 'Column (Kabel)', 'kabel-blocks' ),
        description: __(
                'A single column — free container with background, padding, radius and shadow.',
                'kabel-blocks'
        ),
        category: 'kabel',
        parent: [ 'kbl/columns' ],
        keywords: [ 'kabel', 'column', 'card', 'container', '列', '栏' ],
        supports: {
                anchor: true,
                customClassName: true,
        },
        attributes: {
                anchor: {
                        type: 'string',
                        source: 'attribute',
                        attribute: 'id',
                        selector: '*',
                },
                bgColor: { type: 'string' },
                textColor: { type: 'string' },
                bgURL: { type: 'string' },
                bgId: { type: 'number' },
                overlay: { type: 'number', default: 45 },
                padding: { type: 'number' },
                radius: { type: 'number' },
                shadow: { type: 'string' },
                borderWidth: { type: 'number' },
                borderColor: { type: 'string' },
                borderStyle: { type: 'string' },
                contentAlign: { type: 'string', default: 'left' },
                width: { type: 'number' },
                gFrom: { type: 'string' },
                gTo: { type: 'string' },
                gAngle: { type: 'number', default: 135 },
                anim: { type: 'string' },
                animDelay: { type: 'number' },
                hideD: { type: 'boolean' },
                hideT: { type: 'boolean' },
                hideM: { type: 'boolean' },
                mt: { type: 'number' },
                mb: { type: 'number' },
                mtM: { type: 'number' },
                mbM: { type: 'number' },
        },

        edit( { attributes, setAttributes, clientId } ) {
                const {
                        anchor, bgColor, textColor, bgURL, bgId, overlay,
                        padding, radius, shadow, borderWidth, borderColor, contentAlign, width,
                } = attributes;

                /* Sibling columns of the direct kbl/columns parent — used by
                 * the width materializer, the drag handles and the badge. */
                const siblings = useSelect(
                        ( select ) => {
                                const { getBlockParents, getBlockOrder, getBlock } = select( 'core/block-editor' );
                                const parents = getBlockParents( clientId, true );
                                for ( let i = 0; i < parents.length; i++ ) {
                                        const order = getBlockOrder( parents[ i ] );
                                        if ( order && order.indexOf( clientId ) !== -1 ) {
                                                return order.map( ( id ) => {
                                                        const b = getBlock( id );
                                                        return {
                                                                id,
                                                                width: b && b.attributes ? b.attributes.width : undefined,
                                                        };
                                                } );
                                        }
                                }
                                return [];
                        },
                        [ clientId ]
                );
                const { updateBlockAttributes, removeBlock } = useDispatch( 'core/block-editor' );

                const myIndex = siblings.findIndex( ( s ) => s.id === clientId );
                const isLast = myIndex === siblings.length - 1;
                const isFirst = myIndex === 0;

                /* Write a full width array to every sibling (only the ones
                 * whose share changed — usually this column + one neighbor). */
                const writeWidths = ( arr ) => {
                        siblings.forEach( ( s, i ) => {
                                const next = arr[ i ];
                                const cur = s.width;
                                const nextVal = next == null || next === '' ? undefined : next;
                                const curVal = cur == null || cur === '' ? undefined : cur;
                                if ( nextVal !== curVal ) {
                                        updateBlockAttributes( [ s.id ], { width: nextVal } );
                                }
                        } );
                };

                /* Stackable semantics: setting a width adjusts this column
                 * and its neighbor (which absorbs the difference). Resetting
                 * clears the WHOLE row back to "auto" (an even split). */
                const applyWidth = ( v ) => {
                        if ( v == null || v === '' ) {
                                siblings.forEach( ( s ) => {
                                        if ( s.width != null ) {
                                                updateBlockAttributes( [ s.id ], { width: undefined } );
                                        }
                                } );
                                return;
                        }
                        /* Materialize the CURRENT shares (no target injection —
                         * injecting first would break the sum and dump the
                         * residue on the last column instead of the neighbor). */
                        const eff = materializeRow( siblings.map( ( s ) => s.width ) );
                        writeWidths( setColumnWidth( eff, myIndex, v ) );
                };

                const className = cx(
                        'kbl3-col',
                        width != null && 'kbl3-col--w',
                        contentAlign === 'center' && 'is-center',
                        contentAlign === 'right' && 'is-right',
                        bgURL && 'has-bgimg',
                        ...applyAnim( attributes ),
                        ...applyVisibility( attributes ),
                        ...applySpacing( attributes )
                );
                const style = colStyle( attributes );

                /* ---------------- Canvas drag handles ----------------
                 * Both edges (right unless last, left unless first). Dragging
                 * a boundary moves the width between this column and the
                 * neighbor on that side; snapping + min 5% like Stackable. */
                const drag = useRef( null );
                const startResize = ( e, dir ) => {
                        e.preventDefault();
                        const handle = e.currentTarget;
                        const colEl = handle.closest && handle.closest( '.kbl3-col' ) ? handle.closest( '.kbl3-col' ) : handle.parentElement;
                        const rowEl = colEl && colEl.closest ? colEl.closest( '.kbl3-columns' ) : null;
                        if ( ! colEl || ! rowEl || ! rowEl.clientWidth ) {
                                return;
                        }
                        const neighborIndex = dir === 'r' ? myIndex + 1 : myIndex - 1;
                        const neighbor = siblings[ neighborIndex ];
                        if ( ! neighbor ) {
                                return;
                        }
                        /* Materialize unset siblings once, before dragging, so
                         * ratios are stable while the pointer moves — and use
                         * the MATERIALIZED SHARES as the drag's start point.
                         * Reading clientWidth right after the write would race
                         * the re-render and measure the stale layout. */
                        const shares = materializeRow( siblings.map( ( s ) => s.width ) );
                        writeWidths( shares );
                        try {
                                handle.setPointerCapture( e.pointerId );
                        } catch ( err ) {
                                return;
                        }
                        /* Δpx converts to Δshare on the CONTENT width (row
                         * minus gaps) — Stackable's parentWidth. Using the raw
                         * row width would deflate the pair's room by the gap
                         * share and shrink the row sum on every drag. */
                        const gapPx = parseFloat( getComputedStyle( rowEl ).columnGap ) || 0;
                        const contentW = rowEl.clientWidth - gapPx * ( siblings.length - 1 );
                        if ( contentW <= 0 ) {
                                return;
                        }
                        drag.current = {
                                pointerId: e.pointerId,
                                handle,
                                dir,
                                neighborId: neighbor.id,
                                startX: e.clientX,
                                rowW: contentW,
                                selfPct: shares[ myIndex ],
                                nbPct: shares[ neighborIndex ],
                        };
                };
                const moveResize = ( e ) => {
                        const d = drag.current;
                        if ( ! d || d.pointerId !== e.pointerId ) {
                                return;
                        }
                        let delta = ( ( e.clientX - d.startX ) / d.rowW ) * 100;
                        if ( d.dir === 'l' ) {
                                delta = -delta; /* dragging the left edge leftward grows this column */
                        }
                        const room = d.selfPct + d.nbPct;
                        let next = d.selfPct + delta;
                        next = Math.min( Math.max( next, COL_MIN_CANVAS ), room - COL_MIN_CANVAS );
                        next = snapWidth( next, e.shiftKey );
                        next = round1( next );
                        const nb = round1( room - next );
                        setAttributes( { width: next } );
                        updateBlockAttributes( [ d.neighborId ], { width: nb } );
                };
                const endResize = ( e ) => {
                        const d = drag.current;
                        if ( ! d ) {
                                return;
                        }
                        drag.current = null;
                        try {
                                if ( d.handle.hasPointerCapture && d.handle.hasPointerCapture( e.pointerId ) ) {
                                        d.handle.releasePointerCapture( e.pointerId );
                                }
                        } catch ( err ) {
                                /* Pointer already released — nothing to do. */
                        }
                };

                /* ---------------- Clickable width badge ----------------
                 * Clicking the corner badge opens a numeric popup (Stackable's
                 * ResizableTooltip). Tab hops to the next column's badge. */
                const [ editing, setEditing ] = useState( false );
                const badgeRef = useRef( null );
                useEffect( () => {
                        const badge = badgeRef.current;
                        if ( ! badge || typeof badge.addEventListener !== 'function' ) {
                                return undefined;
                        }
                        const open = () => setEditing( true );
                        badge.addEventListener( 'kbl:openw', open );
                        return () => badge.removeEventListener( 'kbl:openw', open );
                }, [] );

                const openNextBadge = () => {
                        const badge = badgeRef.current;
                        const rowEl = badge && badge.closest ? badge.closest( '.kbl3-columns' ) : null;
                        if ( ! rowEl || typeof window.CustomEvent !== 'function' ) {
                                setEditing( false );
                                return;
                        }
                        const badges = rowEl.querySelectorAll( '.kbl3-colbadge' );
                        const next = badges.length ? badges[ ( myIndex + 1 ) % badges.length ] : null;
                        if ( next ) {
                                next.dispatchEvent( new window.CustomEvent( 'kbl:openw' ) );
                        }
                        setEditing( false );
                };
                const commitPopup = ( raw ) => {
                        const v = parseFloat( raw );
                        setEditing( false );
                        if ( ! isNaN( v ) ) {
                                applyWidth( Math.min( 100, Math.max( 0, v ) ) );
                        }
                };

                const blockProps = useBlockProps( { className, style, id: anchor || undefined } );
                const innerProps = useInnerBlocksProps( blockProps, {
                        renderAppender: InnerBlocks.ButtonBlockAppender,
                } );

                return el(
                        Fr,
                        null,
                        el(
                                BlockControls,
                                null,
                                el(
                                        ToolbarGroup,
                                        null,
                                        el( ToolbarButton, {
                                                icon: 'trash',
                                                title: __( 'Remove column', 'kabel-blocks' ),
                                                disabled: siblings.length <= 1,
                                                onClick: () => removeBlock( clientId ),
                                        } )
                                )
                        ),
                        el(
                                KblInspector,
                                {
                                        layout: el( 'div', null,
                                                el(
                                                        SegButtons,
                                                        {
                                                                label: __( 'Content alignment', 'kabel-blocks' ),
                                                                value: contentAlign,
                                                                onChange: ( v ) => setAttributes( { contentAlign: v } ),
                                                                options: [
                                                                        { value: 'left', label: __( 'Left', 'kabel-blocks' ) },
                                                                        { value: 'center', label: __( 'Center', 'kabel-blocks' ) },
                                                                        { value: 'right', label: __( 'Right', 'kabel-blocks' ) },
                                                                ],
                                                        }
                                                ),
                                                el( RangeRow, {
                                                        label: __( 'Column width', 'kabel-blocks' ),
                                                        value: width,
                                                        onChange: applyWidth,
                                                        min: 5,
                                                        max: 95,
                                                        step: 0.5,
                                                        unit: '%',
                                                        help: __(
                                                                'Column width is a share of the row; the neighbor column absorbs the difference so the row always sums to 100.',
                                                                'kabel-blocks'
                                                        ),
                                                } )
                                        ),
                                        style: el( 'div', null,
                                                el( ColorsPanel, {
                                                        colors: [
                                                                {
                                                                        label: __( 'Background color', 'kabel-blocks' ),
                                                                        value: bgColor,
                                                                        onChange: ( v ) => setAttributes( { bgColor: v } ),
                                                                        isBackground: true,
                                                                },
                                                                {
                                                                        label: __( 'Text color', 'kabel-blocks' ),
                                                                        value: textColor,
                                                                        onChange: ( v ) => setAttributes( { textColor: v } ),
                                                                        isText: true,
                                                                },
                                                        ],
                                                } ),
                                                el( BorderPanel, {
                                                        radius,
                                                        onRadius: ( v ) => setAttributes( { radius: v } ),
                                                        shadow,
                                                        onShadow: ( v ) => setAttributes( { shadow: v } ),
                                                        borderWidth,
                                                        onBorderWidth: ( v ) => setAttributes( { borderWidth: v } ),
                                                        borderColor,
                                                        onBorderColor: ( v ) => setAttributes( { borderColor: v } ),
                                                        borderStyle: attributes.borderStyle,
                                                        onBorderStyle: ( v ) => setAttributes( { borderStyle: v } ),
                                                } ),
                                                el( GradientPanel, {
                                                        attrs: attributes,
                                                        setAttrs: setAttributes,
                                                } ),
                                                el( AnimPanel, {
                                                        attrs: attributes,
                                                        setAttrs: setAttributes,
                                                } )
                                        ),
                                        advanced: el(
                                                'div',
                                                null,
                                                el( BgImageControl, {
                                                        url: bgURL,
                                                        id: bgId,
                                                        onSelect: ( { url, id } ) => setAttributes( { bgURL: url, bgId: id } ),
                                                        onRemove: () => setAttributes( { bgURL: undefined, bgId: undefined } ),
                                                        overlay,
                                                        onOverlay: ( v ) => setAttributes( { overlay: v ?? 45 } ),
                                                } ),
                                                el( RangeRow, {
                                                        label: __( 'Inner padding', 'kabel-blocks' ),
                                                        value: padding,
                                                        onChange: ( v ) => setAttributes( { padding: v } ),
                                                        min: 0,
                                                        max: 96,
                                                } ),
                                                el( SpacingPanel, {
                                                        attrs: attributes,
                                                        setAttrs: setAttributes,
                                                } ),
                                                el( VisibilityPanel, {
                                                        attrs: attributes,
                                                        setAttrs: setAttributes,
                                                } )
                                        ),
                                }
                        ),
                        el(
                                'div',
                                innerProps,
                                innerProps.children,
                                el( 'span', {
                                        className: 'kbl3-colbadge',
                                        'data-testid': 'kbl-colbadge',
                                        'data-col': myIndex,
                                        ref: badgeRef,
                                        role: 'button',
                                        tabIndex: 0,
                                        title: __( 'Click to type an exact width', 'kabel-blocks' ),
                                        onMouseDown: ( e ) => {
                                                e.preventDefault();
                                                e.stopPropagation();
                                                setEditing( true );
                                        },
                                        onKeyDown: ( e ) => {
                                                if ( e.key === 'Enter' || e.key === ' ' ) {
                                                        e.preventDefault();
                                                        setEditing( true );
                                                }
                                        },
                                },
                                width != null ? `${ width }%` : __( 'Auto', 'kabel-blocks' ) ),
                                editing ? el( 'div', {
                                        className: 'kbl3-colw-pop',
                                        'data-testid': 'kbl-colw-pop',
                                        onMouseDown: ( e ) => e.stopPropagation(),
                                },
                                        el( 'label', null, __( 'Column width', 'kabel-blocks' ) ),
                                        el( 'input', {
                                                type: 'number',
                                                step: 'any',
                                                min: 0,
                                                max: 100,
                                                autoFocus: true,
                                                defaultValue: width != null ? width : '',
                                                'aria-label': __( 'Column width', 'kabel-blocks' ),
                                                onKeyDown: ( e ) => {
                                                        if ( e.key === 'Enter' ) {
                                                                commitPopup( e.currentTarget.value );
                                                        } else if ( e.key === 'Escape' ) {
                                                                setEditing( false );
                                                        } else if ( e.key === 'Tab' ) {
                                                                e.preventDefault();
                                                                openNextBadge();
                                                        }
                                                },
                                                onBlur: ( e ) => commitPopup( e.currentTarget.value ),
                                        } ),
                                        el( 'span', { className: 'kbl3-colw-pop__hint' },
                                                __( 'Enter to apply · Esc to close · Tab for next column', 'kabel-blocks' ) )
                                ) : null,
                                ! isLast ? el( 'div', {
                                        className: 'kbl3-colresize',
                                        'data-testid': 'kbl-colresize',
                                        onPointerDown: ( e ) => startResize( e, 'r' ),
                                        onPointerMove: moveResize,
                                        onPointerUp: endResize,
                                        onPointerCancel: endResize,
                                } ) : null,
                                ! isFirst ? el( 'div', {
                                        className: 'kbl3-colresize kbl3-colresize--l',
                                        'data-testid': 'kbl-colresize-l',
                                        onPointerDown: ( e ) => startResize( e, 'l' ),
                                        onPointerMove: moveResize,
                                        onPointerUp: endResize,
                                        onPointerCancel: endResize,
                                } ) : null
                        )
                );
        },

        save( { attributes } ) {
                const { anchor, bgURL, contentAlign, width } = attributes;
                const className = cx(
                        'kbl3-col',
                        width != null && 'kbl3-col--w',
                        contentAlign === 'center' && 'is-center',
                        contentAlign === 'right' && 'is-right',
                        bgURL && 'has-bgimg',
                        ...applyAnim( attributes ),
                        ...applyVisibility( attributes ),
                        ...applySpacing( attributes )
                );
                return el(
                        'div',
                        useBlockProps.save( { className, style: colStyle( attributes ), id: anchor || undefined } ),
                        el( InnerBlocks.Content )
                );
        },
} );

/** Shared by edit + save. PHP twin: kbl3_col_style(). */
function colStyle( attributes ) {
        const {
                bgColor, textColor, bgURL, overlay, padding, radius, width,
                shadow, borderWidth, borderColor, borderStyle,
        } = attributes;
        const grad = gradientValue( attributes );
        return toStyle( [
                [ 'background-color', grad ? null : bgColor ],
                [ 'color', textColor ],
                [ 'background-image', bgUrl( bgURL ) || grad ],
                [ '--kbl3-ov', bgURL ? String( ( overlay ?? 45 ) / 100 ) : null ],
                [ '--kbl3-w', width != null ? String( width ) : null ],
                [ 'padding', padding != null ? `${ padding }px` : null ],
                [ 'border-radius', radius ? `${ radius }px` : null ],
                [ 'box-shadow', shadow ? SHADOWS[ shadow ] || null : null ],
                [ 'border', borderColor && borderWidth ? `${ borderWidth }px ${ borderStyle || 'solid' } ${ borderColor }` : null ],
                ...animDelayPairs( attributes ),
                ...spacingPairs( attributes ),
        ] );
}
