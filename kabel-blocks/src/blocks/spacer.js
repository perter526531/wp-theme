/**
 * kbl/spacer — vertical gap with height control.
 */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { register } from '../register.js';
import { useBlockProps } from '@wordpress/block-editor';
import { cx, toStyle, applyVisibility, applySpacing, spacingPairs } from '../util.js';
import { KblInspector, RangeRow, SpacingPanel, VisibilityPanel } from '../inspector.js';

register( 'kbl/spacer', {
        apiVersion: 3,
        title: __( 'Spacer (Kabel)', 'kabel-blocks' ),
        description: __( 'Vertical breathing room between rows.', 'kabel-blocks' ),
        category: 'kabel',
        keywords: [ 'kabel', 'spacer', 'gap', '间距', '占位', '留白', '空白'],
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
                height: { type: 'number', default: 48 },
                hideD: { type: 'boolean' },
                hideT: { type: 'boolean' },
                hideM: { type: 'boolean' },
                mt: { type: 'number' },
                mb: { type: 'number' },
                mtM: { type: 'number' },
                mbM: { type: 'number' },
        },

        edit( { attributes, setAttributes, isSelected } ) {
                const { height } = attributes;

                return el(
                        Fr,
                        null,
                        el(
                                KblInspector,
                                {
                                        layout: el( RangeRow, {
                                                label: __( 'Height', 'kabel-blocks' ),
                                                value: height,
                                                onChange: ( v ) => setAttributes( { height: v ?? 48 } ),
                                                min: 8,
                                                max: 240,
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
                                'div',
                                {
                                        ...useBlockProps( {
                                                className: cx( 'kbl3-spacer', isSelected && 'is-selected', ...applyVisibility( attributes ), ...applySpacing( attributes ) ),
                                                style: toStyle( [ [ 'height', `${ height ?? 48 }px` ], ...spacingPairs( attributes ) ] ),
                                                'aria-hidden': true,
                                        } ),
                                },
                                isSelected ? el( 'span', { className: 'kbl3-spacer__label' }, `${ height ?? 48 }px` ) : null
                        )
                );
        },

        save( { attributes } ) {
                const { height, anchor } = attributes;
                return el( 'div', {
                        ...useBlockProps.save( {
                                className: cx( 'kbl3-spacer', ...applyVisibility( attributes ), ...applySpacing( attributes ) ),
                                style: toStyle( [ [ 'height', `${ height ?? 48 }px` ], ...spacingPairs( attributes ) ] ),
                                id: anchor || undefined,
                        } ),
                        'aria-hidden': true,
                } );
        },
} );
