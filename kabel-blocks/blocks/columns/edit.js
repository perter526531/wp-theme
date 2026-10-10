/**
 * kbl/columns — a row of 1–8 columns (the "5-in-a-row" primitive).
 *
 * v11 ports Stackable's column workflow wholesale:
 *  - Inserting the block shows a layout picker (100 / 50-50 / 30-70 / …)
 *    instead of silently creating two blank columns.
 *  - The sidebar exposes the full Stackable width kit: preset thumbnails,
 *    a proportional divider slider and one number input per column. Widths
 *    are shares that always sum to ~100 — changing one column hands the
 *    difference to its neighbor, exactly like dragging a divider.
 *  - A sort control reorders columns; adding or removing a column resets
 *    the row to an even split (Stackable's reset-on-count-change).
 * Each column is still a free container: nest any block combination inside.
 *
 * v4 (Stackable-style responsive layer): the column gap and the margins
 * are per-device attributes (gap / gapT / gapM, mt / mtT / mtM, …) riding
 * the per-instance <style> tag. The headline feature is the per-device
 * "columns per row" (colsT / colsM): below desktop the row re-wraps into
 * that many equal columns via a flex-basis calc that already accounts for
 * the (responsive) gap. On desktop the count stays the number of child
 * column blocks (the Resp group shows a hint instead of a control).
 * Margins are emitted as REAL properties — a container must never leak a
 * --kbl-mt custom property into the headings and texts nested inside.
 * Legacy content without a uid keeps the exact v3 output.
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
        useBlockProps,
        useInnerBlocksProps,
        InnerBlocks,
        BlockControls,
} from '@wordpress/block-editor';
import { useDispatch, useSelect } from '@wordpress/data';
import { PanelBody, ToolbarGroup, ToolbarButton } from '@wordpress/components';
import { sanitizeClass, cx, toStyle, applyVisibility, applySpacing, spacingPairs } from '../../src/util.js';
import {
        KblInspector,
        RangeRow,
        SegButtons,
        ToggleRow,
        SpacingPanel,
        VisibilityPanel,
        Resp,
        useUid,
        uidClass,
        respStyleEl,
        v4Strip,
} from '../../src/kit.js';
import {
        ColumnWidthsControl,
        ColumnSortControl,
        columnLayouts,
        layoutBlocks,
        layoutIcon,
} from '../../src/columns-kit.js';

/* The insert-time layouts (Stackable variations). Equal splits leave every
 * column "auto"; asymmetric ones carry explicit shares. */
export const LAYOUTS = columnLayouts().map( ( layout ) => ( {
        ...layout,
        icon: layoutIcon( layout.widths || [ 50, 50 ] ),
        blocks: layoutBlocks( layout ),
} ) );

/* Which attributes ride the per-instance <style> tag (respCss spec).
 * gap is the legacy --kbl3-gap variable (the wrap calc and the gap chain
 * both read it, at every breakpoint). colsT / colsM set the per-row wrap
 * basis on the CHILD columns (sel rules); the mobile entry also flips the
 * row direction back to row (class-qualified so it beats the stacking and
 * reverse rules at equal specificity). mt / mb are real properties so
 * they cannot inherit into nested content. */
export const RESP_SPEC = [
        { a: 'gap', p: '--kbl3-gap', u: 'px' },
        { a: 'mt', p: 'margin-top', u: 'px' },
        { a: 'mb', p: 'margin-bottom', u: 'px' },
        { a: 'cols', p: 'flex-basis', calc: 'cols', sel: ' > .kbl3-col', decls: 'flex-grow:0;flex-shrink:1', only: 't' },
        { a: 'cols', p: 'flex-direction', raw: 'row', sel: '.kbl3-columns', only: 'm' },
        { a: 'cols', p: 'flex-basis', calc: 'cols', sel: ' > .kbl3-col', decls: 'flex-grow:0;flex-shrink:1', only: 'm' },
];
/* Keys the legacy emission must drop once a uid exists (the inline
 * --kbl3-gap would beat every media-query override). colsT / colsM have no
 * legacy emission — listed for symmetry with the spec. PHP twin:
 * kbl4_strip() inside kbl3_columns(). */
export const STRIP_KEYS = [ 'gap', 'gapT', 'gapM', 'colsT', 'colsM', 'mt', 'mtT', 'mtM', 'mb', 'mbT', 'mbM' ];

export function Edit( { attributes, setAttributes, clientId, isSelected } ) {
                const { anchor, vGap, vAlign, collapse, reverse } = attributes;

                useUid( attributes, setAttributes, clientId, isSelected );
                const legacy = v4Strip( attributes, STRIP_KEYS );

                const editorSelect = useSelect( ( select ) => select( 'core/block-editor' ), [] );
                const children = useSelect(
                        ( select ) => select( 'core/block-editor' ).getBlockOrder( clientId ),
                        [ clientId ]
                );
                /* Live width shares of every child column (null = auto). */
                const childWidths = useSelect(
                        ( select ) => {
                                const { getBlockOrder, getBlock } = select( 'core/block-editor' );
                                return getBlockOrder( clientId ).map( ( id ) => {
                                        const b = getBlock( id );
                                        return b && b.attributes ? b.attributes.width : null;
                                } );
                        },
                        [ clientId ]
                );
                const { insertBlocks, removeBlocks, updateBlockAttributes, moveBlockToPosition, replaceInnerBlocks } =
                        useDispatch( 'core/block-editor' );

                /* Write a full width array to the child columns (only the
                 * columns whose share actually changed, so a slider drag
                 * dispatches two updates — not eight). */
                const applyWidths = ( arr ) => {
                        children.forEach( ( id, i ) => {
                                const next = arr[ i ];
                                const cur = childWidths[ i ];
                                const nextVal = next == null || next === '' ? undefined : next;
                                const curVal = cur == null || cur === '' ? undefined : cur;
                                if ( nextVal !== curVal ) {
                                        updateBlockAttributes( [ id ], { width: nextVal } );
                                }
                        } );
                };

                /* Stackable reset semantics: whenever the column count
                 * changes, every share goes back to "auto" (an even split). */
                const resetWidths = () => {
                        const order = editorSelect.getBlockOrder( clientId );
                        order.forEach( ( id ) => updateBlockAttributes( [ id ], { width: undefined } ) );
                };

                const setCount = ( n ) => {
                        const current = children.length;
                        if ( n === current ) {
                                return;
                        }
                        if ( n > current ) {
                                const additions = [];
                                for ( let i = current; i < n; i++ ) {
                                        additions.push( createBlock( 'kbl/column' ) );
                                }
                                /* updateSelection=false: keep the row selected so the
                                 * width kit stays in the sidebar (Stackable does the same). */
                                insertBlocks( additions, current, clientId, false );
                        } else {
                                removeBlocks( children.slice( n ) );
                        }
                        resetWidths();
                };

                /* One click restores a perfectly even row (all-auto). */
                const equalize = () => resetWidths();

                const className = cx(
                        'kbl3-columns',
                        vAlign === 'center' && 'kbl3-valign--center',
                        vAlign === 'bottom' && 'kbl3-valign--bottom',
                        ! collapse && 'kbl3-keep-row',
                        reverse && 'kbl3-reverse',
                        uidClass( attributes ),
                        ...applyVisibility( attributes ),
                        ...applySpacing( legacy )
                );
                const style = toStyle( [
                        /* v4: with a uid the gap rides the per-instance sheet — an
                         * inline custom property would beat every media override. */
                        [ '--kbl3-gap', attributes.uid ? null : `${ attributes.gap ?? 24 }px` ],
                        [ '--kbl3-vgap', vGap != null ? `${ vGap }px` : null ],
                        ...spacingPairs( legacy ),
                ] );

                const blockProps = useBlockProps( { className, style, id: anchor || undefined } );
                /* Hooks must run unconditionally — the layout-picker early
                 * return below would otherwise change the hook count between
                 * renders once a layout gets picked. */
                const innerProps = useInnerBlocksProps( blockProps, {
                        allowedBlocks: [ 'kbl/column' ],
                        templateLock: false,
                        orientation: 'horizontal',
                        renderAppender: InnerBlocks.ButtonBlockAppender,
                } );

                /* Empty row → Stackable-style layout picker instead of two
                 * silent blank columns. Picking a layout inserts its columns
                 * (asymmetric layouts carry their exact shares). */
                if ( ! children.length ) {
                        return el(
                                Fr,
                                null,
                                el(
                                        'div',
                                        blockProps,
                                        el( 'div', { className: 'kbl3-colpick' },
                                                el( 'div', { className: 'kbl3-colpick__title' },
                                                        __( 'Pick a columns layout', 'kabel-blocks' ) ),
                                                el( 'div', { className: 'kbl3-colpick__grid' },
                                                        LAYOUTS.map( ( layout ) =>
                                                                el( 'button', {
                                                                        key: layout.name,
                                                                        type: 'button',
                                                                        className: 'kbl3-colpick__item',
                                                                        'data-testid': 'kbl-colpick-item',
                                                                        'data-layout': layout.name,
                                                                        title: layout.description,
                                                                        onClick: () => {
                                                                                replaceInnerBlocks(
                                                                                        clientId,
                                                                                        layout.blocks.map( ( b ) => createBlock( b[ 0 ], b[ 1 ] || {} ) )
                                                                                );
                                                                        },
                                                                },
                                                                el( 'span', { className: 'kbl3-colpick__icon' }, layout.icon ),
                                                                el( 'span', { className: 'kbl3-colpick__label' }, layout.title )
                                                                )
                                                        )
                                                )
                                        )
                                ),
                                respStyleEl( attributes, RESP_SPEC )
                        );
                }

                return el(
                        Fr,
                        null,
                        el(
                                BlockControls,
                                null,
                                el(
                                        ToolbarGroup,
                                        { label: __( 'Columns', 'kabel-blocks' ) },
                                        el( ToolbarButton, {
                                                icon: 'plus-alt2',
                                                title: __( 'Add column', 'kabel-blocks' ),
                                                onClick: () => {
                                                        insertBlocks( createBlock( 'kbl/column' ), children.length, clientId, false );
                                                        resetWidths();
                                                },
                                        } ),
                                        el( ToolbarButton, {
                                                icon: 'editor-table',
                                                title: __( 'Equalize column widths', 'kabel-blocks' ),
                                                onClick: equalize,
                                        } )
                                )
                        ),
                        el(
                                KblInspector,
                                {
                                        attrs: attributes,
                                        setAttrs: setAttributes,
                                        v4: true,
                                        layout: el(
                                                PanelBody,
                                                { initialOpen: true },
                                                el( SegButtons, {
                                                        label: __( 'Column count', 'kabel-blocks' ),
                                                        value: children.length,
                                                        onChange: setCount,
                                                        options: [ 1, 2, 3, 4, 5, 6, 7, 8 ].map( ( n ) => ( {
                                                                value: n,
                                                                label: String( n ),
                                                        } ) ),
                                                } ),
                                                el( Resp, {
                                                        prop: 'cols',
                                                        attrs: attributes,
                                                        setAttrs: setAttributes,
                                                        desktopHint: __( 'On desktop the column count is the number of child column blocks — add or remove columns in the list.', 'kabel-blocks' ),
                                                },
                                                        el( RangeRow, {
                                                                label: __( 'Columns per row', 'kabel-blocks' ),
                                                                min: 1,
                                                                max: 8,
                                                                step: 1,
                                                                unit: '',
                                                        } ) ),
                                                el( ColumnWidthsControl, {
                                                        count: children.length,
                                                        values: childWidths,
                                                        onChange: applyWidths,
                                                } ),
                                                el( ColumnSortControl, {
                                                        count: children.length,
                                                        onMove: ( from, to ) => {
                                                                if ( children[ from ] && to >= 0 && to < children.length ) {
                                                                        moveBlockToPosition( children[ from ], clientId, clientId, to );
                                                                }
                                                        },
                                                } ),
                                                el( SegButtons, {
                                                        label: __( 'Vertical alignment', 'kabel-blocks' ),
                                                        value: vAlign,
                                                        onChange: ( v ) => setAttributes( { vAlign: v } ),
                                                        options: [
                                                                { value: 'top', label: __( 'Top', 'kabel-blocks' ) },
                                                                { value: 'center', label: __( 'Middle', 'kabel-blocks' ) },
                                                                { value: 'bottom', label: __( 'Bottom', 'kabel-blocks' ) },
                                                        ],
                                                } ),
                                                el( Resp, { prop: 'gap', attrs: attributes, setAttrs: setAttributes },
                                                        el( RangeRow, {
                                                                label: __( 'Column gap', 'kabel-blocks' ),
                                                                min: 0,
                                                                max: 80,
                                                        } ) ),
                                                el( RangeRow, {
                                                        label: __( 'Row gap when stacked', 'kabel-blocks' ),
                                                        value: vGap,
                                                        onChange: ( v ) => setAttributes( { vGap: v } ),
                                                        min: 0,
                                                        max: 120,
                                                        help: __( 'Vertical breathing room between rows.', 'kabel-blocks' ),
                                                } ),
                                                el( ToggleRow, {
                                                        label: __( 'Stack on mobile', 'kabel-blocks' ),
                                                        checked: collapse,
                                                        onChange: ( v ) => setAttributes( { collapse: v } ),
                                                        help: collapse
                                                                ? __( 'Columns stack vertically on small screens.', 'kabel-blocks' )
                                                                : __( 'Columns stay side-by-side on small screens.', 'kabel-blocks' ),
                                                } ),
                                                el( ToggleRow, {
                                                        label: __( 'Reverse column order on mobile', 'kabel-blocks' ),
                                                        checked: !! reverse,
                                                        onChange: ( v ) => setAttributes( { reverse: v || undefined } ),
                                                        help: __( 'Only applies while the columns are stacked on small screens.', 'kabel-blocks' ),
                                                } )
                                        ),
                                        advanced: el( 'div', null,
                                                el( SpacingPanel, {
                                                        attrs: attributes,
                                                        setAttrs: setAttributes,
                                                        v4: true,
                                                } ),
                                                el( VisibilityPanel, {
                                                        attrs: attributes,
                                                        setAttrs: setAttributes,
                                                } )
                                        ),
                                }
                        ),
                        el( 'div', innerProps ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
