/**
 * kbl/separator — the bronze hairline.
 */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { register } from '../register.js';
import { useBlockProps } from '@wordpress/block-editor';
import { cx, toStyle, applyVisibility, applySpacing, spacingPairs } from '../util.js';
import { KblInspector, RangeRow, ColorsPanel, SegButtons, SpacingPanel, VisibilityPanel } from '../inspector.js';

register( 'kbl/separator', {
        apiVersion: 3,
        title: __( 'Separator (Kabel)', 'kabel-blocks' ),
        description: __( 'Bronze hairline divider with width and alignment.', 'kabel-blocks' ),
        category: 'kabel',
        keywords: [ 'kabel', 'separator', 'divider', 'line', '分隔线', '分隔线', '分割线'],
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
                width: { type: 'number', default: 96 },
                align: { type: 'string', default: 'left' },
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
                const { width, align, color } = attributes;

                return el(
                        Fr,
                        null,
                        el(
                                KblInspector,
                                {
                                        layout: el( 'div', null,
                                                el( RangeRow, {
                                                        label: __( 'Width', 'kabel-blocks' ),
                                                        value: width,
                                                        onChange: ( v ) => setAttributes( { width: v ?? 96 } ),
                                                        min: 24,
                                                        max: 480,
                                                } ),
                                                el( SegButtons, {
                                                        label: __( 'Alignment', 'kabel-blocks' ),
                                                        value: align,
                                                        onChange: ( v ) => setAttributes( { align: v } ),
                                                        options: [
                                                                { value: 'left', label: __( 'Left', 'kabel-blocks' ) },
                                                                { value: 'center', label: __( 'Center', 'kabel-blocks' ) },
                                                        ],
                                                } )
                                        ),
                                        style: el( ColorsPanel, {
                                                colors: [
                                                        {
                                                                label: __( 'Line color', 'kabel-blocks' ),
                                                                value: color,
                                                                onChange: ( v ) => setAttributes( { color: v } ),
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
                        el( 'hr', useBlockProps( sepProps( attributes ) ) )
                );
        },

        save( { attributes } ) {
                return el( 'hr', useBlockProps.save( sepProps( attributes ) ) );
        },
} );

/** Shared by edit + save. PHP twin: kbl3_sep(). */
function sepProps( attributes ) {
        const { width, align, color, anchor } = attributes;
        return {
                className: cx( 'kbl3-sep', align === 'center' && 'is-center', ...applyVisibility( attributes ), ...applySpacing( attributes ) ),
                style: toStyle( [
                        [ 'width', `${ width ?? 96 }px` ],
                        [ 'border-color', color ],
                        ...spacingPairs( attributes ),
                ] ),
                id: anchor || undefined,
        };
}
