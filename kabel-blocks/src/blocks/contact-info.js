/**
 * kbl/contact-info — phone / email / address row with icons.
 * The classic header top-bar element: each item is an icon + a label with
 * an optional link (tel:, mailto:, wa.me …).
 */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { register } from '../register.js';
import { useBlockProps } from '@wordpress/block-editor';
import { PanelBody, Button } from '@wordpress/components';
import { cx, toStyle, iconSvg, ICON_NAMES, applyVisibility, applySpacing, spacingPairs } from '../util.js';
import {
        KblInspector,
        SelectRow,
        RangeRow,
        TextRow,
        ColorsPanel,
        SpacingPanel,
        VisibilityPanel,
} from '../inspector.js';

const DEFAULT_ITEMS = [
        { icon: 'phone', label: '+86 180 3320 5148', url: 'https://wa.me/8618033205148' },
        { icon: 'mail', label: 'kabelpaints@gmail.com', url: 'mailto:kabelpaints@gmail.com' },
        { icon: 'map-pin', label: 'Foshan · Guangdong · China', url: '' },
];

register( 'kbl/contact-info', {
        apiVersion: 3,
        title: __( 'Contact info (Kabel)', 'kabel-blocks' ),
        description: __(
                'Phone / email / address row with icons for the header top bar.',
                'kabel-blocks'
        ),
        category: 'kabel',
        keywords: [ 'kabel', 'contact', 'phone', 'email', 'address', '联系方式', '电话', '地址'],
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
                items: { type: 'array', default: DEFAULT_ITEMS },
                color: { type: 'string' },
                iconColor: { type: 'string' },
                gap: { type: 'number', default: 20 },
                fontSize: { type: 'number', default: 13 },
                hideD: { type: 'boolean' },
                hideT: { type: 'boolean' },
                hideM: { type: 'boolean' },
                mt: { type: 'number' },
                mb: { type: 'number' },
                mtM: { type: 'number' },
                mbM: { type: 'number' },
        },

        edit( { attributes, setAttributes } ) {
                const a = attributes;
                const items = a.items == null ? DEFAULT_ITEMS : a.items;

                const className = cx( 'kbl3-contact', ...applyVisibility( a ), ...applySpacing( a ) );
                const style = contactStyle( a );

                const updateItem = ( i, patch ) => {
                        setAttributes( {
                                items: items.map( ( item, j ) => ( j === i ? { ...item, ...patch } : item ) ),
                        } );
                };
                const removeItem = ( i ) => {
                        setAttributes( { items: items.filter( ( _, j ) => j !== i ) } );
                };
                const moveItem = ( i, dir ) => {
                        const j = i + dir;
                        if ( j < 0 || j >= items.length ) {
                                return;
                        }
                        const next = items.slice();
                        const [ moved ] = next.splice( i, 1 );
                        next.splice( j, 0, moved );
                        setAttributes( { items: next } );
                };

                const itemsEditor = el(
                        PanelBody,
                        { title: __( 'Contact items', 'kabel-blocks' ), initialOpen: true },
                        items.map( ( item, i ) =>
                                el(
                                        'div',
                                        { className: 'kbl3-item-row', key: i },
                                        el( SelectRow, {
                                                label: __( 'Icon', 'kabel-blocks' ),
                                                value: item.icon,
                                                onChange: ( v ) => updateItem( i, { icon: v } ),
                                                options: ICON_NAMES.map( ( n ) => ( { value: n, label: n } ) ),
                                        } ),
                                        el( TextRow, {
                                                label: __( 'Text', 'kabel-blocks' ),
                                                value: item.label,
                                                onChange: ( v ) => updateItem( i, { label: v } ),
                                        } ),
                                        el( TextRow, {
                                                label: __( 'Link (optional)', 'kabel-blocks' ),
                                                value: item.url,
                                                onChange: ( v ) => updateItem( i, { url: v } ),
                                                placeholder: 'https://…',
                                        } ),
                                        el(
                                                'div',
                                                { className: 'kbl3-item-row__btns' },
                                                el( Button, {
                                                        size: 'small',
                                                        variant: 'secondary',
                                                        disabled: i === 0,
                                                        onClick: () => moveItem( i, -1 ),
                                                        label: __( 'Move up', 'kabel-blocks' ),
                                                        icon: 'arrow-up',
                                                } ),
                                                el( Button, {
                                                        size: 'small',
                                                        variant: 'secondary',
                                                        disabled: i === items.length - 1,
                                                        onClick: () => moveItem( i, 1 ),
                                                        label: __( 'Move down', 'kabel-blocks' ),
                                                        icon: 'arrow-down',
                                                } ),
                                                el( Button, {
                                                        size: 'small',
                                                        variant: 'secondary',
                                                        isDestructive: true,
                                                        onClick: () => removeItem( i ),
                                                        label: __( 'Remove item', 'kabel-blocks' ),
                                                        icon: 'no',
                                                } )
                                        )
                                )
                        ),
                        el(
                                Button,
                                {
                                        variant: 'secondary',
                                        size: 'small',
                                        onClick: () =>
                                                setAttributes( {
                                                        items: [
                                                                ...items,
                                                                { icon: 'phone', label: '', url: '' },
                                                        ],
                                                } ),
                                },
                                __( 'Add item', 'kabel-blocks' )
                        )
                );

                return el(
                        Fr,
                        null,
                        el(
                                KblInspector,
                                {
                                        layout: el( 'div', null,
                                                el( RangeRow, {
                                                        label: __( 'Item gap (px)', 'kabel-blocks' ),
                                                        value: a.gap,
                                                        initial: 20,
                                                        onChange: ( v ) => setAttributes( { gap: v } ),
                                                        min: 8,
                                                        max: 64,
                                                } ),
                                                itemsEditor
                                        ),
                                        style: el( 'div', null,
                                                el( RangeRow, {
                                                        label: __( 'Font size (px)', 'kabel-blocks' ),
                                                        value: a.fontSize,
                                                        initial: 13,
                                                        onChange: ( v ) => setAttributes( { fontSize: v } ),
                                                        min: 10,
                                                        max: 24,
                                                } ),
                                                el( ColorsPanel, {
                                                        colors: [
                                                                {
                                                                        label: __( 'Text color', 'kabel-blocks' ),
                                                                        value: a.color,
                                                                        onChange: ( v ) => setAttributes( { color: v } ),
                                                                        isText: true,
                                                                },
                                                                {
                                                                        label: __( 'Icon color', 'kabel-blocks' ),
                                                                        value: a.iconColor,
                                                                        onChange: ( v ) => setAttributes( { iconColor: v } ),
                                                                },
                                                        ],
                                                } )
                                        ),
                                        advanced: el( 'div', null,
                                                el( SpacingPanel, {
                                                        attrs: a,
                                                        setAttrs: setAttributes,
                                                } ),
                                                el( VisibilityPanel, {
                                                        attrs: a,
                                                        setAttrs: setAttributes,
                                                } )
                                        ),
                                }
                        ),
                        el(
                                'ul',
                                useBlockProps( { className, style, id: a.anchor || undefined } ),
                                items.map( ( item, i ) =>
                                        el(
                                                'li',
                                                { className: 'kbl3-contact__item', key: i },
                                                el( 'span', { className: 'kbl3-contact__icon' }, iconSvg( item.icon ) ),
                                                item.url
                                                        ? el( 'a', { className: 'kbl3-contact__link', href: item.url }, item.label )
                                                        : el( 'span', { className: 'kbl3-contact__link' }, item.label )
                                        )
                                )
                        )
                );
        },

        save( { attributes } ) {
                const a = attributes;
                const items = a.items == null ? DEFAULT_ITEMS : a.items;

                return el(
                        'ul',
                        useBlockProps.save( {
                                className: cx( 'kbl3-contact', ...applyVisibility( a ), ...applySpacing( a ) ),
                                style: contactStyle( a ),
                                id: a.anchor || undefined,
                        } ),
                        items.map( ( item, i ) =>
                                el(
                                        'li',
                                        { className: 'kbl3-contact__item', key: i },
                                        el( 'span', { className: 'kbl3-contact__icon' }, iconSvg( item.icon ) ),
                                        item.url
                                                ? el( 'a', { className: 'kbl3-contact__link', href: item.url }, item.label )
                                                : el( 'span', { className: 'kbl3-contact__link' }, item.label )
                                )
                        )
                );
        },
} );

/** Shared by edit + save. PHP twin: kbl3_contact_info(). */
function contactStyle( a ) {
        return toStyle( [
                [ '--kbl3-contact-fg', a.color ],
                [ '--kbl3-contact-ic', a.iconColor ],
                [ '--kbl3-contact-gap', ( a.gap ?? 20 ) + 'px' ],
                [ '--kbl3-fs', ( a.fontSize ?? 13 ) + 'px' ],
                ...spacingPairs( a ),
        ] );
}
