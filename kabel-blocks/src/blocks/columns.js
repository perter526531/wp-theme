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
 */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { createBlock, registerBlockVariation } from '@wordpress/blocks';
import { register } from '../register.js';
import {
        useBlockProps,
        useInnerBlocksProps,
        InnerBlocks,
        BlockControls,
} from '@wordpress/block-editor';
import { useDispatch, useSelect } from '@wordpress/data';
import { PanelBody, ToolbarGroup, ToolbarButton } from '@wordpress/components';
import { cx, toStyle, applyVisibility, applySpacing, spacingPairs } from '../util.js';
import { KblInspector, RangeRow, SegButtons, ToggleRow, SpacingPanel, VisibilityPanel } from '../inspector.js';
import {
        ColumnWidthsControl,
        ColumnSortControl,
        columnLayouts,
        layoutBlocks,
        layoutIcon,
} from '../columns-kit.js';

/* The insert-time layouts (Stackable variations). Equal splits leave every
 * column "auto"; asymmetric ones carry explicit shares. */
const LAYOUTS = columnLayouts().map( ( layout ) => ( {
        ...layout,
        icon: layoutIcon( layout.widths || [ 50, 50 ] ),
        blocks: layoutBlocks( layout ),
} ) );

register( 'kbl/columns', {
        apiVersion: 3,
        title: __( 'Columns (Kabel)', 'kabel-blocks' ),
        description: __(
                'Add a row of columns — pick a layout, then add, remove, reorder or resize columns freely.',
                'kabel-blocks'
        ),
        category: 'kabel',
        keywords: [ 'kabel', 'columns', 'row', 'grid', 'layout', '列行', '分栏', '多列' ],
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
                gap: { type: 'number', default: 24 },
                vGap: { type: 'number' },
                vAlign: { type: 'string', default: 'top' },
                collapse: { type: 'boolean', default: true },
                reverse: { type: 'boolean' },
                hideD: { type: 'boolean' },
                hideT: { type: 'boolean' },
                hideM: { type: 'boolean' },
                mt: { type: 'number' },
                mb: { type: 'number' },
                mtM: { type: 'number' },
                mbM: { type: 'number' },
        },

        edit( { attributes, setAttributes, clientId } ) {
                const { anchor, gap, vGap, vAlign, collapse, reverse } = attributes;

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
                        ...applyVisibility( attributes ),
                        ...applySpacing( attributes )
                );
                const style = toStyle( [
                        [ '--kbl3-gap', `${ gap ?? 24 }px` ],
                        [ '--kbl3-vgap', vGap != null ? `${ vGap }px` : null ],
                        ...spacingPairs( attributes ),
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
                                                el( RangeRow, {
                                                        label: __( 'Column gap', 'kabel-blocks' ),
                                                        value: gap,
                                                        onChange: ( v ) => setAttributes( { gap: v ?? 24 } ),
                                                        min: 0,
                                                        max: 80,
                                                } ),
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
                                                } ),
                                                el( VisibilityPanel, {
                                                        attrs: attributes,
                                                        setAttrs: setAttributes,
                                                } )
                                        ),
                                }
                        ),
                        el( 'div', innerProps )
                );
        },

        save( { attributes } ) {
                const { anchor, gap, vGap, vAlign, collapse, reverse } = attributes;

                const className = cx(
                        'kbl3-columns',
                        vAlign === 'center' && 'kbl3-valign--center',
                        vAlign === 'bottom' && 'kbl3-valign--bottom',
                        ! collapse && 'kbl3-keep-row',
                        reverse && 'kbl3-reverse',
                        ...applyVisibility( attributes ),
                        ...applySpacing( attributes )
                );
                const style = toStyle( [
                        [ '--kbl3-gap', `${ gap ?? 24 }px` ],
                        [ '--kbl3-vgap', vGap != null ? `${ vGap }px` : null ],
                        ...spacingPairs( attributes ),
                ] );

                return el(
                        'div',
                        useBlockProps.save( { className, style, id: anchor || undefined } ),
                        el( InnerBlocks.Content )
                );
        },
} );

/* Register the insert-time variations AFTER the block type exists —
 * @wordpress/blocks' registerBlockVariation no-ops with a console error
 * when the parent block has not been registered yet. */
for ( const layout of LAYOUTS ) {
        if ( typeof registerBlockVariation === 'function' ) {
                registerBlockVariation( 'kbl/columns', {
                        name: layout.name,
                        title: layout.title,
                        description: layout.description,
                        icon: layout.icon,
                        scope: [ 'block' ],
                        isDefault: !! layout.isDefault,
                        innerBlocks: layout.blocks,
                } );
        }
}
