/**
 * Header Builder — collapsible element library (Customizer sidebar block).
 *
 * Eleven element cards, clickable or draggable into the structure bar zones;
 * the cart card stays disabled without WooCommerce. The block collapses so
 * the contextual settings below take its place (Blocksy flow).
 */
import { el } from '@wordpress/element';
import { t } from './zh.js';
import { Icon } from './ui.jsx';
import { ELEMENT_TYPES, ELEMENTS } from './model.js';

export function Palette( { open, onToggle, onAdd, onDragStart, onDragEnd, woocommerce } ) {
        return el(
                'section',
                { className: 'khb-block' + ( open ? ' is-open' : '' ), 'aria-label': t( 'Elements' ) },
                el(
                        'button',
                        {
                                type: 'button',
                                className: 'khb-block__head',
                                onClick: onToggle,
                                'aria-expanded': open,
                        },
                        el( 'span', { className: 'khb-block__title' }, t( 'Elements' ) ),
                        el( 'span', { className: 'khb-block__chev', 'aria-hidden': true }, el( Icon, { name: 'chevronDown', size: 16 } ) )
                ),
                open
                        ? el(
                                        'div',
                                        { className: 'khb-block__body' },
                                        el( ElementLibrary, { onAdd, onDragStart, onDragEnd, woocommerce } )
                        )
                        : null
        );
}

function ElementLibrary( { onAdd, onDragStart, onDragEnd, woocommerce } ) {
        return el(
                'div',
                null,
                el( 'p', { className: 'khb-side__hint' }, t( 'Drag an element onto the structure, or click to add it.' ) ),
                el(
                        'div',
                        { className: 'khb-cards' },
                        ELEMENT_TYPES.map( ( type ) => {
                                const meta = ELEMENTS[ type ];
                                const disabled = type === 'cart' && ! woocommerce;
                                return el(
                                        'div',
                                        {
                                                key: type,
                                                className: 'khb-card' + ( disabled ? ' is-disabled' : '' ),
                                                draggable: ! disabled,
                                                role: 'button',
                                                tabIndex: disabled ? -1 : 0,
                                                'aria-disabled': disabled || undefined,
                                                'aria-label': t( meta.label ),
                                                onClick: () => {
                                                        if ( ! disabled ) {
                                                                onAdd( type );
                                                        }
                                                },
                                                onKeyDown: ( e ) => {
                                                        if ( ! disabled && ( e.key === 'Enter' || e.key === ' ' ) ) {
                                                                e.preventDefault();
                                                                onAdd( type );
                                                        }
                                                },
                                                onDragStart: ( e ) => {
                                                        if ( disabled ) {
                                                                e.preventDefault();
                                                                return;
                                                        }
                                                        onDragStart( e, type );
                                                },
                                                onDragEnd,
                                        },
                                        el( Icon, { name: meta.icon, size: 20 } ),
                                        el( 'span', { className: 'khb-card__name' }, t( meta.label ) ),
                                        disabled
                                                ? el( 'span', { className: 'khb-card__note' }, t( 'Requires WooCommerce' ) )
                                                : null
                                );
                        } )
                )
        );
}
