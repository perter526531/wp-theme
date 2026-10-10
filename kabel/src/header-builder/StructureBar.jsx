/**
 * Header Builder — bottom structure bar (Blocksy style, dark).
 *
 * Desktop: one card per row (label + gear/up/down/delete + three zones).
 * Mobile:  compact three-zone strip + drawer panel + trigger/direction/
 *          breakpoint inline controls.
 *
 * Zones are drop targets for palette cards (kind:'new') and chips
 * (kind:'move'); dropping onto a chip inserts before it, dropping onto the
 * zone itself appends — which also covers same-zone reordering.
 */
import { el, useState } from '@wordpress/element';
import { t } from './zh.js';
import { Icon, IconButton } from './ui.jsx';
import { Segmented, NumberInput } from './controls.jsx';
import { ELEMENTS, MAX_ROWS, updateMobile, addRow, moveRow } from './model.js';

const ZONE_LABELS = [ 'Left', 'Center', 'Right' ];

export function StructureBar( {
        model,
        setModel,
        device,
        selectedId,
        onSelect,
        onOpenElements,
        onDropPayload,
        onRequestRowDelete,
        onRemoveChip,
        onMobileSettings,
        drag,
        setDrag,
} ) {
        const [ over, setOver ] = useState( null );

        const setOverIfChanged = ( next ) => {
                setOver( ( prev ) =>
                        prev && prev.key === next.key && prev.beforeId === next.beforeId ? prev : next
                );
        };

        const chipFromEvent = ( e ) => {
                if ( ! e.target || ! e.target.closest ) {
                        return null;
                }
                const chipEl = e.target.closest( '[data-khb-chip]' );
                return chipEl ? chipEl.getAttribute( 'data-khb-chip' ) : null;
        };

        const onZoneDragOver = ( e, key ) => {
                e.preventDefault();
                if ( e.dataTransfer ) {
                        e.dataTransfer.dropEffect = drag && drag.kind === 'new' ? 'copy' : 'move';
                }
                setOverIfChanged( { key, beforeId: chipFromEvent( e ) } );
        };

        const onZoneDragLeave = ( e, key ) => {
                if ( ! e.currentTarget.contains( e.relatedTarget ) ) {
                        setOver( ( prev ) => ( prev && prev.key === key ? null : prev ) );
                }
        };

        const onZoneDrop = ( e, loc, key ) => {
                e.preventDefault();
                setOver( null );
                const raw = e.dataTransfer.getData( 'application/x-kabel-hb' );
                if ( ! raw ) {
                        return;
                }
                let payload;
                try {
                        payload = JSON.parse( raw );
                } catch ( err ) {
                        return;
                }
                onDropPayload( payload, loc, chipFromEvent( e ) );
        };

        const zoneProps = ( loc, key ) => ( {
                onDragOver: ( e ) => onZoneDragOver( e, key ),
                onDragLeave: ( e ) => onZoneDragLeave( e, key ),
                onDrop: ( e ) => onZoneDrop( e, loc, key ),
        } );

        const chipProps = {
                model,
                selectedId,
                onSelect,
                setDrag,
        };

        /* Chip removal is owned by App so the selection state stays in sync. */
        const removeChip = onRemoveChip;

        return el(
                'section',
                {
                        className: 'khb-structure',
                        'aria-label': t( 'Structure' ),
                },
                device === 'desktop'
                        ? el( DesktopRows, {
                                model,
                                zoneProps,
                                chipProps,
                                over,
                                onOpenElements,
                                onRequestRowDelete,
                                onSelect,
                                removeChip,
                                setModel,
                          } )
                        : el( MobileBar, {
                                model,
                                zoneProps,
                                chipProps,
                                over,
                                onOpenElements,
                                onMobileSettings,
                                removeChip,
                                setModel,
                          } )
        );
}

/* ------------------------------------------------------------------ */

function Zone( { label, list, loc, keyName, over, zoneProps, chipProps, onOpenElements, removeChip, vertical } ) {
        const isOver = over && over.key === keyName;
        return el(
                'div',
                Object.assign(
                        {
                                className:
                                        'khb-zone' +
                                        ( isOver ? ' is-over' : '' ) +
                                        ( vertical ? ' is-vertical' : '' ),
                                'data-zone': keyName,
                                role: 'list',
                                'aria-label': label,
                        },
                        zoneProps( loc, keyName )
                ),
                el( 'span', { className: 'khb-zone__label' }, label ),
                list.map( ( id ) =>
                        el( Chip, Object.assign( { key: id, id, from: loc, onRemove: removeChip }, chipProps ) )
                ),
                el(
                        'button',
                        {
                                type: 'button',
                                className: 'khb-zone__add',
                                'aria-label': t( 'Browse elements' ),
                                title: t( 'Browse elements' ),
                                onClick: onOpenElements,
                        },
                        el( Icon, { name: 'plus', size: 13 } )
                )
        );
}

function Chip( { id, model, from, selectedId, onSelect, onRemove, setDrag } ) {
        const def = model.elements[ id ];
        const meta = def ? ELEMENTS[ def.type ] : null;
        const label = meta ? t( meta.label ) : t( 'Unknown element' );
        return el(
                'div',
                {
                        className: 'khb-chip' + ( selectedId === id ? ' is-selected' : '' ),
                        draggable: true,
                        'data-khb-chip': id,
                        role: 'button',
                        tabIndex: 0,
                        title: label,
                        onClick: () => onSelect( id ),
                        onKeyDown: ( e ) => {
                                if ( e.key === 'Enter' || e.key === ' ' ) {
                                        e.preventDefault();
                                        onSelect( id );
                                }
                        },
                        onDragStart: ( e ) => {
                                e.dataTransfer.setData(
                                        'application/x-kabel-hb',
                                        JSON.stringify( { kind: 'move', id, from } )
                                );
                                e.dataTransfer.effectAllowed = 'copyMove';
                                setDrag( { kind: 'move', id, from } );
                        },
                        onDragEnd: () => setDrag( null ),
                },
                el( Icon, { name: meta ? meta.icon : 'alert', size: 15 } ),
                el( 'span', { className: 'khb-chip__name' }, label ),
                el(
                        'button',
                        {
                                type: 'button',
                                className: 'khb-chip__x',
                                'aria-label': t( 'Remove from this zone' ),
                                title: t( 'Remove from this zone' ),
                                onClick: ( e ) => {
                                        e.stopPropagation();
                                        onRemove( id, from );
                                },
                        },
                        el( Icon, { name: 'close', size: 11 } )
                )
        );
}

/* ------------------------------------------------------------------ */

function DesktopRows( {
        model,
        zoneProps,
        chipProps,
        over,
        onOpenElements,
        onRequestRowDelete,
        onSelect,
        removeChip,
        setModel,
} ) {
        return el(
                'div',
                { className: 'khb-rows' },
                model.rows.map( ( row, i ) =>
                        el(
                                'div',
                                { className: 'khb-row', key: row.id },
                                el(
                                        'div',
                                        { className: 'khb-row__head' },
                                        el( 'span', { className: 'khb-row__label' }, t( 'Row {n}' ).replace( '{n}', String( i + 1 ) ) ),
                                        el(
                                                'span',
                                                { className: 'khb-row__tools' },
                                                el( IconButton, {
                                                        icon: 'gear',
                                                        label: t( 'Row settings' ),
                                                        tone: 'dark',
                                                        onClick: () => onSelect( 'row:' + row.id ),
                                                } ),
                                                el( IconButton, {
                                                        icon: 'chevronUp',
                                                        label: t( 'Move up' ),
                                                        tone: 'dark',
                                                        disabled: i === 0,
                                                        onClick: () => setModel( ( m ) => moveRow( m, row.id, -1 ) ),
                                                } ),
                                                el( IconButton, {
                                                        icon: 'chevronDown',
                                                        label: t( 'Move down' ),
                                                        tone: 'dark',
                                                        disabled: i === model.rows.length - 1,
                                                        onClick: () => setModel( ( m ) => moveRow( m, row.id, 1 ) ),
                                                } ),
                                                el( IconButton, {
                                                        icon: 'trash',
                                                        label: t( 'Delete row' ),
                                                        tone: 'danger',
                                                        onClick: () => onRequestRowDelete( row.id ),
                                                } )
                                        )
                                ),
                                el(
                                        'div',
                                        { className: 'khb-zones' },
                                        [ 0, 1, 2 ].map( ( z ) =>
                                                el( Zone, {
                                                        key: row.id + ':' + z,
                                                        keyName: 'desktop:' + row.id + ':' + z,
                                                        label: t( ZONE_LABELS[ z ] ),
                                                        list: row.zones[ z ],
                                                        loc: { area: 'desktop', rowId: row.id, zone: z },
                                                        over,
                                                        zoneProps,
                                                        chipProps,
                                                        onOpenElements,
                                                        removeChip,
                                                } )
                                        )
                                )
                        )
                ),
                el(
                        'button',
                        {
                                type: 'button',
                                className: 'khb-addrow',
                                disabled: model.rows.length >= MAX_ROWS,
                                title: model.rows.length >= MAX_ROWS ? t( 'Maximum of 3 rows reached.' ) : '',
                                onClick: () => setModel( ( m ) => addRow( m ) ),
                        },
                        el( Icon, { name: 'plus', size: 14 } ),
                        el( 'span', null, t( 'Add Row' ) )
                )
        );
}

/* ------------------------------------------------------------------ */

function MobileBar( {
        model,
        zoneProps,
        chipProps,
        over,
        onOpenElements,
        onMobileSettings,
        removeChip,
        setModel,
} ) {
        const m = model.mobile;
        const upd = ( field ) => ( value ) =>
                setModel( ( mm ) => updateMobile( mm, { [ field ]: value } ), 'set:mobile:' + field );
        return el(
                'div',
                { className: 'khb-mob' },
                el(
                        'div',
                        { className: 'khb-mob__col' },
                        el(
                                'div',
                                { className: 'khb-mob__head' },
                                el( 'span', null, t( 'Mobile Header' ) ),
                                el( IconButton, {
                                        icon: 'gear',
                                        label: t( 'Mobile settings' ),
                                        tone: 'dark',
                                        onClick: onMobileSettings,
                                } )
                        ),
                        el(
                                'div',
                                { className: 'khb-zones' },
                                [ 0, 1, 2 ].map( ( z ) =>
                                        el( Zone, {
                                                key: 'mobile:' + z,
                                                keyName: 'mobile:' + z,
                                                label: t( ZONE_LABELS[ z ] ),
                                                list: m.zones[ z ],
                                                loc: { area: 'mobile', zone: z },
                                                over,
                                                zoneProps,
                                                chipProps,
                                                onOpenElements,
                                                removeChip,
                                        } )
                                )
                        )
                ),
                el(
                        'div',
                        { className: 'khb-mob__col' },
                        el( 'div', { className: 'khb-mob__head' }, el( 'span', null, t( 'Drawer panel (opens via hamburger)' ) ) ),
                        el(
                                Zone,
                                {
                                        keyName: 'drawer',
                                        label: t( 'Drawer elements' ),
                                        list: m.offcanvas,
                                        loc: { area: 'drawer' },
                                        over,
                                        zoneProps,
                                        chipProps,
                                        onOpenElements,
                                        removeChip,
                                        vertical: true,
                                }
                        )
                ),
                el(
                        'div',
                        { className: 'khb-mob__col khb-mob__col--opts' },
                        el(
                                'div',
                                { className: 'khb-mini' },
                                el( 'span', { className: 'khb-mini__label' }, t( 'Hamburger side' ) ),
                                el( Segmented, {
                                        small: true,
                                        value: m.trigger,
                                        onChange: upd( 'trigger' ),
                                        ariaLabel: t( 'Hamburger side' ),
                                        options: [
                                                { value: 'left', label: t( 'Left' ) },
                                                { value: 'right', label: t( 'Right' ) },
                                        ],
                                } )
                        ),
                        el(
                                'div',
                                { className: 'khb-mini' },
                                el( 'span', { className: 'khb-mini__label' }, t( 'Drawer side' ) ),
                                el( Segmented, {
                                        small: true,
                                        value: m.offcanvasDir,
                                        onChange: upd( 'offcanvasDir' ),
                                        ariaLabel: t( 'Drawer side' ),
                                        options: [
                                                { value: 'left', label: t( 'Left' ) },
                                                { value: 'right', label: t( 'Right' ) },
                                        ],
                                } )
                        ),
                        el(
                                'div',
                                { className: 'khb-mini' },
                                el( 'span', { className: 'khb-mini__label' }, t( 'Breakpoint' ) ),
                                el(
                                        'div',
                                        { className: 'khb-rangerow' },
                                        el( 'input', {
                                                className: 'khb-range',
                                                type: 'range',
                                                min: 480,
                                                max: 1280,
                                                step: 10,
                                                value: m.breakpoint,
                                                'aria-label': t( 'Breakpoint' ),
                                                onChange: ( e ) => upd( 'breakpoint' )( Number( e.target.value ) ),
                                        } ),
                                        el( NumberInput, {
                                                value: m.breakpoint,
                                                min: 480,
                                                max: 1280,
                                                step: 10,
                                                unit: 'px',
                                                ariaLabel: t( 'Breakpoint' ),
                                                onCommit: upd( 'breakpoint' ),
                                        } )
                                )
                        )
                )
        );
}
