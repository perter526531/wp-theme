/**
 * kbl/list — bullet list with bronze markers (check / dash / dot).
 */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { register } from '../register.js';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { cx, toStyle, applyVisibility, applySpacing, spacingPairs } from '../util.js';
import { KblInspector, SegButtons, ColorsPanel, SpacingPanel, VisibilityPanel } from '../inspector.js';

register( 'kbl/list', {
        apiVersion: 3,
        title: __( 'List (Kabel)', 'kabel-blocks' ),
        description: __(
                'Bullet list with bronze check / dash / dot markers.',
                'kabel-blocks'
        ),
        category: 'kabel',
        keywords: [ 'kabel', 'list', 'bullets', '列表', '列表', '清单'],
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
                values: {
                        type: 'string',
                        source: 'html',
                        selector: 'ul',
                        multiline: 'li',
                },
                marker: { type: 'string', default: 'check' },
                color: { type: 'string' },
                hideD: { type: 'boolean' },
                hideT: { type: 'boolean' },
                hideM: { type: 'boolean' },
                mt: { type: 'number' },
                mb: { type: 'number' },
                mtM: { type: 'number' },
                mbM: { type: 'number' },
        },

        edit( { attributes, setAttributes } ) {
                const { values, marker, color } = attributes;

                const className = cx( 'kbl3-list', `kbl3-list--${ marker }`, ...applyVisibility( attributes ), ...applySpacing( attributes ) );
                const style = toStyle( [ [ 'color', color ], ...spacingPairs( attributes ) ] );

                return el(
                        Fr,
                        null,
                        el(
                                KblInspector,
                                {
                                        layout: el( SegButtons, {
                                                label: __( 'Marker', 'kabel-blocks' ),
                                                value: marker,
                                                onChange: ( v ) => setAttributes( { marker: v } ),
                                                options: [
                                                        { value: 'check', label: __( 'Check', 'kabel-blocks' ) },
                                                        { value: 'dash', label: __( 'Dash', 'kabel-blocks' ) },
                                                        { value: 'dot', label: __( 'Dot', 'kabel-blocks' ) },
                                                        { value: 'none', label: __( 'None', 'kabel-blocks' ) },
                                                ],
                                        } ),
                                        style: el( ColorsPanel, {
                                                colors: [
                                                        {
                                                                label: __( 'Text color', 'kabel-blocks' ),
                                                                value: color,
                                                                onChange: ( v ) => setAttributes( { color: v } ),
                                                                isText: true,
                                                        },
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
                        el( RichText, {
                                tagName: 'ul',
                                multiline: 'li',
                                value: values,
                                onChange: ( v ) => setAttributes( { values: v } ),
                                placeholder: __( 'Add a list item…', 'kabel-blocks' ),
                                allowedFormats: [ 'core/bold', 'core/italic' ],
                                identifier: 'values',
                                ...useBlockProps( { className, style, id: attributes.anchor || undefined } ),
                        } )
                );
        },

        save( { attributes } ) {
                const { values, marker, color, anchor } = attributes;

                const className = cx( 'kbl3-list', `kbl3-list--${ marker }`, ...applyVisibility( attributes ), ...applySpacing( attributes ) );
                const style = toStyle( [ [ 'color', color ], ...spacingPairs( attributes ) ] );

                return el(
                        'ul',
                        useBlockProps.save( { className, style, id: anchor || undefined } ),
                        el( RichText.Content, { value: values, multiline: 'li' } )
                );
        },
} );
