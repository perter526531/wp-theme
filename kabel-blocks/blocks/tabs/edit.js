/**
 * kbl/tabs — tabbed content. Tab titles and the active panel are editable
 * right on the canvas; the front-end switcher lives in assets/js/front.js
 * (no JS = every panel stays visible, nothing is hidden).
 *
 * v4 (Stackable-style responsive layer): the panel font size and the top /
 * bottom margins are per-device attributes (fontSize / fontSizeT /
 * fontSizeM, mt / mb with T / M variants) riding the per-instance <style>
 * tag. The font size keeps its --kbl3-fs variable chain — the shared CSS
 * already consumes it at every breakpoint (the tablet / mobile rules fall
 * back to --kbl3-fs, which the per-instance sheet re-scopes per media
 * query); the inline variables are dropped once a uid exists so the
 * media-query overrides can win. The Advanced tab gains the per-instance
 * custom CSS box and the HTML anchor (AdvancedPanelV2). Legacy content
 * without a uid keeps the exact v3 output. The tab items (titles /
 * contents) are untouched repeater data.
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { PanelBody, Button } from '@wordpress/components';
import { sanitizeClass, cx, toStyle, applyVisibility, applySpacing, spacingPairs, applyAnim, animDelayPairs, typographyPairs } from '../../src/util.js';
import {
        KblInspector,
        ColorsPanel,
        TextRow,
        TypographyPanel,
        SpacingPanel,
        VisibilityPanel,
        AnimPanel,
        Resp,
        useUid,
        uidClass,
        respStyleEl,
        v4Strip,
} from '../../src/kit.js';

export const DEFAULT_ITEMS = [
        {
                title: 'Interior',
                content: 'Velvet-matte and scrubbable — made for living rooms, bedrooms and hallways.',
        },
        {
                title: 'Exterior',
                content: 'Weather-shield acrylics that keep facade color true through sun and rain.',
        },
];

/* Which attributes ride the per-instance <style> tag (respCss spec). */
export const RESP_SPEC = [
        { a: 'fontSize', p: '--kbl3-fs', u: 'px' },
        { a: 'mt', p: 'margin-top', u: 'px' },
        { a: 'mb', p: 'margin-bottom', u: 'px' },
];
/* Keys the legacy emission must drop once a uid exists (the inline
 * --kbl3-fs / --kbl3-m* variables would beat every media-query override).
 * PHP twin: kbl4_strip() inside kbl3_tabs(). */
export const STRIP_KEYS = [ 'fontSize', 'fontSizeT', 'fontSizeM', 'mt', 'mtT', 'mtM', 'mb', 'mbT', 'mbM' ];

export function Edit( { attributes, setAttributes, clientId, isSelected } ) {
                const a = attributes;
                const items = a.items && a.items.length ? a.items : DEFAULT_ITEMS;
                const [ active, setActive ] = useState( 0 );
                const current = Math.min( active, items.length - 1 );

                useUid( attributes, setAttributes, clientId, isSelected );
                const legacy = v4Strip( attributes, STRIP_KEYS );

                const className = cx( 'kbl3-tabs', uidClass( attributes ), ...applyAnim( a ), ...applyVisibility( a ), ...applySpacing( legacy ) );
                const style = toStyle( [
                        [ '--kbl3-tabs-accent', a.accentColor ],
                        ...typographyPairs( legacy, { fw: false, lh: false, ls: false, tt: false } ),
                        ...animDelayPairs( legacy ),
                        ...spacingPairs( legacy ),
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
                                        attrs: attributes,
                                        setAttrs: setAttributes,
                                        v4: true,
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
                                                        v4: true,
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
                                                        v4: true,
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
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
