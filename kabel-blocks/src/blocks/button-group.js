/**
 * kbl/button-group — a horizontal row of kbl/button blocks.
 */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { register } from '../register.js';
import {
        useBlockProps,
        useInnerBlocksProps,
        InnerBlocks,
} from '@wordpress/block-editor';
import { cx, toStyle, applyVisibility, applySpacing, spacingPairs } from '../util.js';
import { KblInspector, RangeRow, AlignButtons, SpacingPanel, VisibilityPanel } from '../inspector.js';

register( 'kbl/button-group', {
        apiVersion: 3,
        title: __( 'Button row (Kabel)', 'kabel-blocks' ),
        description: __(
                'Several buttons side by side — add or remove them freely.',
                'kabel-blocks'
        ),
        category: 'kabel',
        keywords: [ 'kabel', 'buttons', 'cta row', '按钮组', '多个按钮'],
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
                gap: { type: 'number', default: 14 },
                align: { type: 'string' },
                hideD: { type: 'boolean' },
                hideT: { type: 'boolean' },
                hideM: { type: 'boolean' },
                mt: { type: 'number' },
                mb: { type: 'number' },
                mtM: { type: 'number' },
                mbM: { type: 'number' },
        },

        edit( { attributes, setAttributes } ) {
                const { anchor, gap, align } = attributes;

                const style = toStyle( [
                        [ '--kbl3-btn-gap', `${ gap ?? 14 }px` ],
                        [ 'justify-content', align === 'center' ? 'center' : align === 'right' ? 'flex-end' : null ],
                        ...spacingPairs( attributes ),
                ] );

                const blockProps = useBlockProps( {
                        className: cx( 'kbl3-btnrow', ...applyVisibility( attributes ), ...applySpacing( attributes ) ),
                        style,
                        id: anchor || undefined,
                } );
                const innerProps = useInnerBlocksProps( blockProps, {
                        allowedBlocks: [ 'kbl/button' ],
                        template: [ [ 'kbl/button' ] ],
                        templateLock: false,
                        orientation: 'horizontal',
                        renderAppender: InnerBlocks.ButtonBlockAppender,
                } );

                return el(
                        Fr,
                        null,
                        el(
                                KblInspector,
                                {
                                        layout: el(
                                                'div',
                                                null,
                                                el( RangeRow, {
                                                        label: __( 'Gap', 'kabel-blocks' ),
                                                        value: gap,
                                                        onChange: ( v ) => setAttributes( { gap: v ?? 14 } ),
                                                        min: 0,
                                                        max: 48,
                                                } ),
                                                el( AlignButtons, {
                                                        value: align,
                                                        onChange: ( v ) => setAttributes( { align: v } ),
                                                } )
                                        ),
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
                        el( 'div', innerProps )
                );
        },

        save( { attributes } ) {
                const { anchor, gap, align } = attributes;

                const style = toStyle( [
                        [ '--kbl3-btn-gap', `${ gap ?? 14 }px` ],
                        [ 'justify-content', align === 'center' ? 'center' : align === 'right' ? 'flex-end' : null ],
                        ...spacingPairs( attributes ),
                ] );

                return el(
                        'div',
                        useBlockProps.save( {
                                className: cx( 'kbl3-btnrow', ...applyVisibility( attributes ), ...applySpacing( attributes ) ),
                                style,
                                id: anchor || undefined,
                        } ),
                        el( InnerBlocks.Content )
                );
        },
} );
