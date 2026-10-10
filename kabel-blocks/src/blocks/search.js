/**
 * kbl/search — header search box.
 *
 * Dynamic block: save() returns null; PHP renders the live form which
 * submits to the site search results (see inc/dynamic.php,
 * kbl3_render_search). The editor shows a faithful static replica.
 */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { register } from '../register.js';
import { useBlockProps } from '@wordpress/block-editor';
import { cx, toStyle, applyVisibility, applySpacing, spacingPairs } from '../util.js';
import { KblInspector, RangeRow, TextRow, ColorsPanel, SpacingPanel, VisibilityPanel } from '../inspector.js';

register( 'kbl/search', {
        apiVersion: 3,
        title: __( 'Search (Kabel)', 'kabel-blocks' ),
        description: __(
                'Header search box — submits to the site search results.',
                'kabel-blocks'
        ),
        category: 'kabel',
        keywords: [ 'kabel', 'search', 'form', 'header', '搜索', '搜索框'],
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
                placeholder: { type: 'string', default: 'Search…' },
                width: { type: 'number', default: 220 },
                radius: { type: 'number', default: 8 },
                inputBg: { type: 'string' },
                textColor: { type: 'string' },
                borderColor: { type: 'string' },
                iconColor: { type: 'string' },
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

                const className = cx( 'kbl3-search', ...applyVisibility( a ), ...applySpacing( a ) );
                const style = toStyle( [
                        [ '--kbl3-search-w', ( a.width ?? 220 ) + 'px' ],
                        [ '--kbl3-search-r', ( a.radius ?? 8 ) + 'px' ],
                        [ '--kbl3-search-bg', a.inputBg ],
                        [ '--kbl3-search-fg', a.textColor ],
                        [ '--kbl3-search-bd', a.borderColor ],
                        [ '--kbl3-search-ic', a.iconColor ],
                        ...spacingPairs( a ),
                ] );

                return el(
                        Fr,
                        null,
                        el(
                                KblInspector,
                                {
                                        layout: el( 'div', null,
                                                el( RangeRow, {
                                                        label: __( 'Search width (px)', 'kabel-blocks' ),
                                                        value: a.width,
                                                        initial: 220,
                                                        onChange: ( v ) => setAttributes( { width: v } ),
                                                        min: 120,
                                                        max: 480,
                                                } ),
                                                el( TextRow, {
                                                        label: __( 'Placeholder text', 'kabel-blocks' ),
                                                        value: a.placeholder,
                                                        onChange: ( v ) => setAttributes( { placeholder: v } ),
                                                        placeholder: 'Search…',
                                                } )
                                        ),
                                        style: el( 'div', null,
                                                el( RangeRow, {
                                                        label: __( 'Corner radius', 'kabel-blocks' ),
                                                        value: a.radius,
                                                        initial: 8,
                                                        onChange: ( v ) => setAttributes( { radius: v } ),
                                                        min: 0,
                                                        max: 40,
                                                } ),
                                                el( ColorsPanel, {
                                                        colors: [
                                                                {
                                                                        label: __( 'Input background', 'kabel-blocks' ),
                                                                        value: a.inputBg,
                                                                        onChange: ( v ) => setAttributes( { inputBg: v } ),
                                                                        isBackground: true,
                                                                },
                                                                {
                                                                        label: __( 'Text color', 'kabel-blocks' ),
                                                                        value: a.textColor,
                                                                        onChange: ( v ) => setAttributes( { textColor: v } ),
                                                                        isText: true,
                                                                },
                                                                {
                                                                        label: __( 'Border color', 'kabel-blocks' ),
                                                                        value: a.borderColor,
                                                                        onChange: ( v ) => setAttributes( { borderColor: v } ),
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
                                'form',
                                useBlockProps( {
                                        className,
                                        style,
                                        id: a.anchor || undefined,
                                        role: 'search',
                                        method: 'get',
                                        action: '#',
                                        onSubmit: ( e ) => e.preventDefault(),
                                } ),
                                el( 'input', {
                                        type: 'search',
                                        className: 'kbl3-search__input',
                                        name: 's',
                                        placeholder: a.placeholder ?? 'Search…',
                                        'aria-label': __( 'Search', 'kabel-blocks' ),
                                } ),
                                el(
                                        'button',
                                        {
                                                type: 'submit',
                                                className: 'kbl3-search__btn',
                                                'aria-label': __( 'Search', 'kabel-blocks' ),
                                        },
                                        el(
                                                'svg',
                                                {
                                                        viewBox: '0 0 24 24',
                                                        fill: 'none',
                                                        stroke: 'currentColor',
                                                        strokeWidth: 1.8,
                                                        'aria-hidden': true,
                                                },
                                                el( 'path', { key: 1, d: 'M10.5 3.5a7 7 0 1 0 0 14 7 7 0 0 0 0-14z' } ),
                                                el( 'path', { key: 2, d: 'M15.8 15.8L21 21' } )
                                        )
                                )
                        )
                );
        },

        save() {
                return null;
        },
} );
