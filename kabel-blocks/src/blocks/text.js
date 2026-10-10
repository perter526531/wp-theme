/**
 * kbl/text — inline-editable paragraph.
 */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { register } from '../register.js';
import {
        useBlockProps,
        RichText,
        BlockControls,
        AlignmentToolbar,
} from '@wordpress/block-editor';
import { cx, toStyle, applyVisibility, applySpacing, spacingPairs, typographyPairs } from '../util.js';
import {
        KblInspector,
        SegButtons,
        ColorsPanel,
        ToggleRow,
        TypographyPanel,
        SpacingPanel,
        VisibilityPanel,
} from '../inspector.js';

register( 'kbl/text', {
        apiVersion: 3,
        title: __( 'Text (Kabel)', 'kabel-blocks' ),
        description: __(
                'Inline-editable paragraph — click and type right on the page.',
                'kabel-blocks'
        ),
        category: 'kabel',
        keywords: [ 'kabel', 'text', 'paragraph', '段落', '段落', '正文', '文本'],
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
                text: {
                        type: 'string',
                        source: 'html',
                        selector: 'p',
                },
                size: { type: 'string', default: 'm' },
                align: { type: 'string' },
                color: { type: 'string' },
                lead: { type: 'boolean' },
                fontSize: { type: 'number' },
                fontSizeT: { type: 'number' },
                fontSizeM: { type: 'number' },
                fontWeight: { type: 'number' },
                lineHeight: { type: 'number' },
                letterSpacing: { type: 'number' },
                textTransform: { type: 'string' },
                hideD: { type: 'boolean' },
                hideT: { type: 'boolean' },
                hideM: { type: 'boolean' },
                mt: { type: 'number' },
                mb: { type: 'number' },
                mtM: { type: 'number' },
                mbM: { type: 'number' },
        },

        edit( { attributes, setAttributes } ) {
                const { text, size, align, color, lead } = attributes;

                const className = cx( 'kbl3-text', `kbl3-text--${ size }`, lead && 'is-lead', ...applyVisibility( attributes ), ...applySpacing( attributes ) );
                const style = toStyle( [
                        [ 'text-align', align ],
                        [ 'color', color ],
                        ...typographyPairs( attributes ),
                        ...spacingPairs( attributes ),
                ] );

                return el(
                        Fr,
                        null,
                        el(
                                BlockControls,
                                null,
                                el( AlignmentToolbar, {
                                        value: align,
                                        onChange: ( v ) => setAttributes( { align: v } ),
                                } )
                        ),
                        el(
                                KblInspector,
                                {
                                        layout: el( SegButtons, {
                                                label: __( 'Size', 'kabel-blocks' ),
                                                value: size,
                                                onChange: ( v ) => setAttributes( { size: v } ),
                                                options: [
                                                        { value: 's', label: 'S' },
                                                        { value: 'm', label: 'M' },
                                                        { value: 'l', label: 'L' },
                                                ],
                                        } ),
                                        style: el( 'div', null,
                                                el( ColorsPanel, {
                                                        colors: [
                                                                {
                                                                        label: __( 'Text color', 'kabel-blocks' ),
                                                                        value: color,
                                                                        onChange: ( v ) => setAttributes( { color: v } ),
                                                                        isText: true,
                                                                },
                                                        ],
                                                } ),
                                                el( TypographyPanel, {
                                                        attrs: attributes,
                                                        setAttrs: setAttributes,
                                                } )
                                        ),
                                        advanced: el( 'div', null,
                                                el( ToggleRow, {
                                                        label: __( 'Lead paragraph', 'kabel-blocks' ),
                                                        checked: !! lead,
                                                        onChange: ( v ) => setAttributes( { lead: v } ),
                                                        help: __( 'Larger, slightly muted intro copy.', 'kabel-blocks' ),
                                                } ),
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
                                tagName: 'p',
                                value: text,
                                onChange: ( v ) => setAttributes( { text: v } ),
                                placeholder: __( 'Write text…', 'kabel-blocks' ),
                                allowedFormats: [ 'core/bold', 'core/italic', 'core/link' ],
                                identifier: 'text',
                                ...useBlockProps( { className, style, id: attributes.anchor || undefined } ),
                        } )
                );
        },

        save( { attributes } ) {
                const { text, size, align, color, lead, anchor } = attributes;

                const className = cx( 'kbl3-text', `kbl3-text--${ size }`, lead && 'is-lead', ...applyVisibility( attributes ), ...applySpacing( attributes ) );
                const style = toStyle( [
                        [ 'text-align', align ],
                        [ 'color', color ],
                        ...typographyPairs( attributes ),
                        ...spacingPairs( attributes ),
                ] );

                return el(
                        'p',
                        useBlockProps.save( { className, style, id: anchor || undefined } ),
                        el( RichText.Content, { value: text } )
                );
        },
} );
