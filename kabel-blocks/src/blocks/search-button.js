/**
 * kbl/search-button — the header search trigger.
 *
 * A ghost/solid icon button that opens the site-wide search overlay. The
 * overlay itself is built by the front runtime (front.js) on first click —
 * no extra markup on the page until it is needed. data-home carries the
 * form action so the runtime never has to guess the site URL.
 *
 * PHP twin: kbl3_search_button() in inc/emitter.php.
 */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { cx, toStyle, applyVisibility, applySpacing, spacingPairs, iconSvg } from '../util.js';
import {
        KblInspector,
        SegButtons,
        TextRow,
        ToggleRow,
        ColorsPanel,
        SpacingPanel,
        VisibilityPanel,
} from '../inspector.js';

registerBlockType( 'kbl/search-button', {
        apiVersion: 2,
        title: __( 'Search button (Kabel)', 'kabel-blocks' ),
        description: __(
                'Icon button that opens the site search overlay — for headers and navigation bars.',
                'kabel-blocks'
        ),
        category: 'kabel',
        keywords: [ 'kabel', 'search', 'header', '搜索', '页眉', '搜索按钮' ],
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
                label: { type: 'string', default: 'Search' },
                showLabel: { type: 'boolean', default: true },
                variant: { type: 'string', default: 'ghost' },
                homeURL: { type: 'string' },
                textColor: { type: 'string' },
                bgColor: { type: 'string' },
                hoverColor: { type: 'string' },
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

                const className = cx(
                        'kbl3-searchbtn',
                        a.variant === 'solid' && 'kbl3-searchbtn--solid',
                        ...applyVisibility( a ),
                        ...applySpacing( a ),
                );
                const style = toStyle( [
                        [ 'color', a.textColor ],
                        [ '--kbl3-searchbtn-bg', a.bgColor ],
                        [ '--kbl3-searchbtn-hover', a.hoverColor ],
                        ...spacingPairs( a ),
                ] );

                return el(
                        Fr,
                        null,
                        el(
                                KblInspector,
                                {
                                        layout: el(
                                                'div',
                                                null,
                                                el( TextRow, {
                                                        label: __( 'Label', 'kabel-blocks' ),
                                                        value: a.label,
                                                        onChange: ( v ) => setAttributes( { label: v } ),
                                                } ),
                                                el( ToggleRow, {
                                                        label: __( 'Show label', 'kabel-blocks' ),
                                                        checked: a.showLabel !== false,
                                                        onChange: ( v ) => setAttributes( { showLabel: v } ),
                                                } ),
                                                el( SegButtons, {
                                                        label: __( 'Style', 'kabel-blocks' ),
                                                        value: a.variant ?? 'ghost',
                                                        onChange: ( v ) => setAttributes( { variant: v } ),
                                                        options: [
                                                                { value: 'ghost', label: __( 'Ghost', 'kabel-blocks' ) },
                                                                { value: 'solid', label: __( 'Solid', 'kabel-blocks' ) },
                                                        ],
                                                } )
                                        ),
                                        style: el( 'div', null,
                                                el( ColorsPanel, {
                                                        colors: [
                                                                {
                                                                        label: __( 'Text color', 'kabel-blocks' ),
                                                                        value: a.textColor,
                                                                        onChange: ( v ) => setAttributes( { textColor: v } ),
                                                                        isText: true,
                                                                },
                                                                {
                                                                        label: __( 'Background color', 'kabel-blocks' ),
                                                                        value: a.bgColor,
                                                                        onChange: ( v ) => setAttributes( { bgColor: v } ),
                                                                        isBackground: true,
                                                                },
                                                                {
                                                                        label: __( 'Hover color', 'kabel-blocks' ),
                                                                        value: a.hoverColor,
                                                                        onChange: ( v ) => setAttributes( { hoverColor: v } ),
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
                                'button',
                                {
                                        ...useBlockProps( { className, style, id: a.anchor || undefined } ),
                                        type: 'button',
                                        'data-kbl-search': '',
                                        'aria-controls': 'kbl-search-overlay',
                                },
                                el(
                                        'span',
                                        { className: 'kbl3-searchbtn__icon', 'aria-hidden': true },
                                        iconSvg( 'search' )
                                ),
                                a.showLabel !== false
                                        ? el( 'span', { className: 'kbl3-searchbtn__label' }, a.label )
                                        : null
                        )
                );
        },

        save( { attributes } ) {
                const a = attributes;

                const className = cx(
                        'kbl3-searchbtn',
                        a.variant === 'solid' && 'kbl3-searchbtn--solid',
                        ...applyVisibility( a ),
                        ...applySpacing( a ),
                );
                const style = toStyle( [
                        [ 'color', a.textColor ],
                        [ '--kbl3-searchbtn-bg', a.bgColor ],
                        [ '--kbl3-searchbtn-hover', a.hoverColor ],
                        ...spacingPairs( a ),
                ] );

                return el(
                        'button',
                        {
                                ...useBlockProps.save( { className, style, id: a.anchor || undefined } ),
                                type: 'button',
                                'data-kbl-search': '',
                                'aria-controls': 'kbl-search-overlay',
                                'data-home': a.homeURL || undefined,
                        },
                        el(
                                'span',
                                { className: 'kbl3-searchbtn__icon', 'aria-hidden': true },
                                iconSvg( 'search' )
                        ),
                        a.showLabel !== false
                                ? el( 'span', { className: 'kbl3-searchbtn__label' }, a.label )
                                : null
                );
        },
} );
