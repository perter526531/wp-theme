/**
 * kbl/tabs — tabbed content. Tab titles and the active panel are editable
 * right on the canvas; the front-end switcher lives in assets/js/front.js
 * (no JS = every panel stays visible, nothing is hidden).
 */
import { el, Fr, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { register } from '../register.js';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { PanelBody, Button } from '@wordpress/components';
import { cx, toStyle, applyVisibility, applySpacing, spacingPairs, applyAnim, animDelayPairs, typographyPairs } from '../util.js';
import {
        KblInspector,
        ColorsPanel,
        TextRow,
        TypographyPanel,
        SpacingPanel,
        VisibilityPanel,
        AnimPanel,
} from '../inspector.js';

const DEFAULT_ITEMS = [
        {
                title: 'Interior',
                content: 'Velvet-matte and scrubbable — made for living rooms, bedrooms and hallways.',
        },
        {
                title: 'Exterior',
                content: 'Weather-shield acrylics that keep facade color true through sun and rain.',
        },
];

register( 'kbl/tabs', {
        apiVersion: 3,
        title: __( 'Tabs (Kabel)', 'kabel-blocks' ),
        description: __(
                'Tabbed content with a bronze underline indicator — panels switch on click.',
                'kabel-blocks'
        ),
        category: 'kabel',
        keywords: [ 'kabel', 'tabs', 'tab', '选项卡', '标签页', '选项卡', '标签页', '切换'],
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
                accentColor: { type: 'string' },
                fontSize: { type: 'number' },
                fontSizeT: { type: 'number' },
                fontSizeM: { type: 'number' },
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

        edit( { attributes, setAttributes } ) {
                const a = attributes;
                const items = a.items && a.items.length ? a.items : DEFAULT_ITEMS;
                const [ active, setActive ] = useState( 0 );
                const current = Math.min( active, items.length - 1 );

                const className = cx( 'kbl3-tabs', ...applyAnim( a ), ...applyVisibility( a ), ...applySpacing( a ) );
                const style = toStyle( [
                        [ '--kbl3-tabs-accent', a.accentColor ],
                        ...typographyPairs( a, { fw: false, lh: false, ls: false, tt: false } ),
                        ...animDelayPairs( a ),
                        ...spacingPairs( a ),
                ] );

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
                        { title: __( 'Tabs', 'kabel-blocks' ), initialOpen: true },
                        items.map( ( item, i ) =>
                                el(
                                        'div',
                                        { className: 'kbl3-item-row', key: i },
                                        el( TextRow, {
                                                label: __( 'Tab title', 'kabel-blocks' ) + ' ' + ( i + 1 ),
                                                value: item.title,
                                                onChange: ( v ) => updateItem( i, { title: v } ),
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
                                                        disabled: items.length <= 1,
                                                        onClick: () => removeItem( i ),
                                                        label: __( 'Remove tab', 'kabel-blocks' ),
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
                                                setAttributes( { items: [ ...items, { title: '', content: '' } ] } ),
                                },
                                __( 'Add tab', 'kabel-blocks' )
                        )
                );

                return el(
                        Fr,
                        null,
                        el(
                                KblInspector,
                                {
                                        layout: itemsEditor,
                                        style: el(
                                                'div',
                                                null,
                                                el( ColorsPanel, {
                                                        colors: [
                                                                {
                                                                        label: __( 'Active tab color', 'kabel-blocks' ),
                                                                        value: a.accentColor,
                                                                        onChange: ( v ) => setAttributes( { accentColor: v } ),
                                                                },
                                                        ],
                                                } ),
                                                el( TypographyPanel, {
                                                        attrs: a,
                                                        setAttrs: setAttributes,
                                                        include: { fw: false, lh: false, ls: false, tt: false },
                                                } ),
                                                el( AnimPanel, {
                                                        attrs: a,
                                                        setAttrs: setAttributes,
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
                                'div',
                                useBlockProps( { className, style, id: a.anchor || undefined } ),
                                el(
                                        'div',
                                        { className: 'kbl3-tabs__nav', role: 'tablist' },
                                        items.map( ( item, i ) =>
                                                el(
                                                        'button',
                                                        {
                                                                key: i,
                                                                type: 'button',
                                                                role: 'tab',
                                                                className: cx( 'kbl3-tabs__tab', i === current && 'is-active' ),
                                                                'aria-selected': i === current ? 'true' : 'false',
                                                                onClick: ( e ) => {
                                                                        if ( e.target && e.target.closest && e.target.closest( '[contenteditable]' ) ) {
                                                                                return;
                                                                        }
                                                                        setActive( i );
                                                                },
                                                        },
                                                        el( RichText, {
                                                                tagName: 'span',
                                                                className: 'kbl3-tabs__label',
                                                                value: item.title,
                                                                onChange: ( v ) => updateItem( i, { title: v } ),
                                                                placeholder: __( 'Tab title…', 'kabel-blocks' ),
                                                                allowedFormats: [ 'core/bold', 'core/italic' ],
                                                                identifier: 'title-' + i,
                                                        } )
                                                )
                                        )
                                ),
                                items.map( ( item, i ) =>
                                        el( RichText, {
                                                key: 'p' + i,
                                                tagName: 'div',
                                                role: 'tabpanel',
                                                className: cx( 'kbl3-tabs__panel', i === current && 'is-active' ),
                                                value: item.content,
                                                onChange: ( v ) => updateItem( i, { content: v } ),
                                                placeholder: __( 'Tab content…', 'kabel-blocks' ),
                                                allowedFormats: [ 'core/bold', 'core/italic' ],
                                                identifier: 'content-' + i,
                                        } )
                                )
                        )
                );
        },

        save( { attributes } ) {
                const a = attributes;
                const items = a.items || [];

                const className = cx( 'kbl3-tabs', ...applyAnim( a ), ...applyVisibility( a ), ...applySpacing( a ) );
                const style = toStyle( [
                        [ '--kbl3-tabs-accent', a.accentColor ],
                        ...typographyPairs( a, { fw: false, lh: false, ls: false, tt: false } ),
                        ...animDelayPairs( a ),
                        ...spacingPairs( a ),
                ] );

                return el(
                        'div',
                        {
                                ...useBlockProps.save( { className, style, id: a.anchor || undefined } ),
                                'data-kbl-tabs': '',
                        },
                        el(
                                'div',
                                { className: 'kbl3-tabs__nav', role: 'tablist' },
                                items.map( ( item, i ) =>
                                        el(
                                                'button',
                                                {
                                                        key: i,
                                                        className: cx( 'kbl3-tabs__tab', i === 0 && 'is-active' ),
                                                        role: 'tab',
                                                        'aria-selected': i === 0 ? 'true' : 'false',
                                                        type: 'button',
                                                },
                                                el( RichText.Content, {
                                                        tagName: 'span',
                                                        className: 'kbl3-tabs__label',
                                                        value: item.title,
                                                } )
                                        )
                                )
                        ),
                        items.map( ( item, i ) =>
                                el(
                                        'div',
                                        {
                                                key: 'p' + i,
                                                className: cx( 'kbl3-tabs__panel', i === 0 && 'is-active' ),
                                                role: 'tabpanel',
                                        },
                                        el( RichText.Content, { value: item.content } )
                                )
                        )
                );
        },
} );
