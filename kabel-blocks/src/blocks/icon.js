/**
 * kbl/icon — a curated stroke icon with size, color and optional shape.
 */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { register } from '../register.js';
import { useBlockProps } from '@wordpress/block-editor';
import { cx, toStyle, iconSvg, applyVisibility, applySpacing, spacingPairs } from '../util.js';
import {
        KblInspector,
        RangeRow,
        SegButtons,
        ColorsPanel,
        IconPicker,
        SpacingPanel,
        VisibilityPanel,
} from '../inspector.js';

register( 'kbl/icon', {
        apiVersion: 3,
        title: __( 'Icon (Kabel)', 'kabel-blocks' ),
        description: __(
                'Pick from 29 curated stroke icons — paint, plant, globe, service.',
                'kabel-blocks'
        ),
        category: 'kabel',
        keywords: [ 'kabel', 'icon', 'svg', '图标', '图标', '小图标'],
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
                name: { type: 'string', default: 'check' },
                size: { type: 'number', default: 48 },
                color: { type: 'string' },
                shape: { type: 'string', default: 'none' },
                shapeBg: { type: 'string' },
                hideD: { type: 'boolean' },
                hideT: { type: 'boolean' },
                hideM: { type: 'boolean' },
                mt: { type: 'number' },
                mb: { type: 'number' },
                mtM: { type: 'number' },
                mbM: { type: 'number' },
        },

        edit( { attributes, setAttributes } ) {
                const { anchor, name, size, color, shape, shapeBg } = attributes;

                const className = cx(
                        'kbl3-icon',
                        shape !== 'none' && `kbl3-icon--${ shape }`,
                        ...applyVisibility( attributes ),
                        ...applySpacing( attributes )
                );
                const style = toStyle( [
                        [ '--kbl3-icon-size', `${ size ?? 48 }px` ],
                        [ 'color', color ],
                        [ 'background-color', shape !== 'none' ? shapeBg : null ],
                        ...spacingPairs( attributes ),
                ] );

                return el(
                        Fr,
                        null,
                        el(
                                KblInspector,
                                {
                                        layout: el( 'div', null,
                                                el( IconPicker, {
                                                        value: name,
                                                        onChange: ( v ) => setAttributes( { name: v } ),
                                                } ),
                                                el( RangeRow, {
                                                        label: __( 'Size', 'kabel-blocks' ),
                                                        value: size,
                                                        onChange: ( v ) => setAttributes( { size: v ?? 48 } ),
                                                        min: 16,
                                                        max: 128,
                                                } ),
                                                el( SegButtons, {
                                                        label: __( 'Shape', 'kabel-blocks' ),
                                                        value: shape,
                                                        onChange: ( v ) => setAttributes( { shape: v } ),
                                                        options: [
                                                                { value: 'none', label: __( 'None', 'kabel-blocks' ) },
                                                                { value: 'circle', label: __( 'Circle', 'kabel-blocks' ) },
                                                                { value: 'square', label: __( 'Square', 'kabel-blocks' ) },
                                                        ],
                                                } )
                                        ),
                                        style: el( ColorsPanel, {
                                                colors: [
                                                        {
                                                                label: __( 'Icon color', 'kabel-blocks' ),
                                                                value: color,
                                                                onChange: ( v ) => setAttributes( { color: v } ),
                                                                isText: true,
                                                        },
                                                        ...( shape !== 'none' ? [ {
                                                                label: __( 'Shape background', 'kabel-blocks' ),
                                                                value: shapeBg,
                                                                onChange: ( v ) => setAttributes( { shapeBg: v } ),
                                                                isBackground: true,
                                                        } ] : [] ),
                                                ],
                                        } ),
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
                        el(
                                'span',
                                useBlockProps( { className, style, id: anchor || undefined } ),
                                iconSvg( name )
                        )
                );
        },

        save( { attributes } ) {
                const { anchor, name, size, color, shape, shapeBg } = attributes;

                const className = cx(
                        'kbl3-icon',
                        shape !== 'none' && `kbl3-icon--${ shape }`,
                        ...applyVisibility( attributes ),
                        ...applySpacing( attributes )
                );
                const style = toStyle( [
                        [ '--kbl3-icon-size', `${ size ?? 48 }px` ],
                        [ 'color', color ],
                        [ 'background-color', shape !== 'none' ? shapeBg : null ],
                        ...spacingPairs( attributes ),
                ] );

                return el(
                        'span',
                        useBlockProps.save( { className, style, id: anchor || undefined } ),
                        iconSvg( name )
                );
        },
} );
