/**
 * kbl/heading — inline-editable title (H1–H6) with size/align/color.
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
import { ToolbarGroup, ToolbarButton } from '@wordpress/components';
import { cx, toStyle, applyVisibility, applySpacing, spacingPairs, applyAnim, typographyPairs } from '../util.js';
import {
        KblInspector,
        SegButtons,
        ColorsPanel,
        ToggleRow,
        TypographyPanel,
        SpacingPanel,
        VisibilityPanel,
        AnimPanel,
} from '../inspector.js';

const SIZE_OPTIONS = [
        { value: 's', label: 'S' },
        { value: 'm', label: 'M' },
        { value: 'l', label: 'L' },
        { value: 'xl', label: 'XL' },
];

register( 'kbl/heading', {
        apiVersion: 3,
        title: __( 'Heading (Kabel)', 'kabel-blocks' ),
        description: __(
                'Inline-editable title with brand sizes — click and type right on the page.',
                'kabel-blocks'
        ),
        category: 'kabel',
        keywords: [ 'kabel', 'heading', 'title', '标题', '大标题', '眉标'],
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
                        selector: 'h1,h2,h3,h4,h5,h6',
                },
                level: { type: 'number', default: 2 },
                size: { type: 'string', default: 'm' },
                align: { type: 'string' },
                color: { type: 'string' },
                eyebrow: { type: 'boolean' },
                fontSize: { type: 'number' },
                fontSizeT: { type: 'number' },
                fontSizeM: { type: 'number' },
                fontWeight: { type: 'number' },
                lineHeight: { type: 'number' },
                letterSpacing: { type: 'number' },
                textTransform: { type: 'string' },
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
                const { text, level, size, align, color, eyebrow } = attributes;
                const Tag = `h${ level }`;

                const className = cx(
                        'kbl3-h',
                        `kbl3-h--${ size }`,
                        eyebrow && 'is-eyebrow',
                        ...applyAnim( attributes ),
                        ...applyVisibility( attributes ),
                        ...applySpacing( attributes )
                );
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
                                el(
                                        ToolbarGroup,
                                        { isCollapsed: false },
                                        [ 1, 2, 3, 4, 5, 6 ].map( ( l ) =>
                                                el( ToolbarButton, {
                                                        key: l,
                                                        isActive: level === l,
                                                        onClick: () => setAttributes( { level: l } ),
                                                }, `H${ l }` )
                                        )
                                ),
                                el( AlignmentToolbar, {
                                        value: align,
                                        onChange: ( v ) => setAttributes( { align: v } ),
                                } )
                        ),
                        el(
                                KblInspector,
                                {
                                        layout: el(
                                                'div',
                                                null,
                                                el( SegButtons, {
                                                        label: __( 'Level', 'kabel-blocks' ),
                                                        value: level,
                                                        onChange: ( v ) => setAttributes( { level: Number( v ) } ),
                                                        options: [ 1, 2, 3, 4, 5, 6 ].map( ( l ) => ( { value: l, label: `H${ l }` } ) ),
                                                } ),
                                                el( SegButtons, {
                                                        label: __( 'Size', 'kabel-blocks' ),
                                                        value: size,
                                                        onChange: ( v ) => setAttributes( { size: v } ),
                                                        options: SIZE_OPTIONS,
                                                } ),
                                                el( 'p', { className: 'kbl3-panel-hint' }, __( 'S/M/L/XL are brand presets — set any px value in Style → Typography.', 'kabel-blocks' ) )
                                        ),
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
                                                } ),
                                                el( AnimPanel, {
                                                        attrs: attributes,
                                                        setAttrs: setAttributes,
                                                } )
                                        ),
                                        advanced: el( 'div', null,
                                                el( ToggleRow, {
                                                        label: __( 'Eyebrow style', 'kabel-blocks' ),
                                                        checked: !! eyebrow,
                                                        onChange: ( v ) => setAttributes( { eyebrow: v } ),
                                                        help: __( 'Small bronze uppercase intro line above sections.', 'kabel-blocks' ),
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
                                tagName: Tag,
                                value: text,
                                onChange: ( v ) => setAttributes( { text: v } ),
                                placeholder: __( 'Write a heading…', 'kabel-blocks' ),
                                allowedFormats: [ 'core/bold', 'core/italic' ],
                                identifier: 'text',
                                ...useBlockProps( { className, style, id: attributes.anchor || undefined } ),
                        } )
                );
        },

        save( { attributes } ) {
                const { text, level, size, align, color, eyebrow, anchor } = attributes;
                const Tag = `h${ level }`;

                const className = cx(
                        'kbl3-h',
                        `kbl3-h--${ size }`,
                        eyebrow && 'is-eyebrow',
                        ...applyAnim( attributes ),
                        ...applyVisibility( attributes ),
                        ...applySpacing( attributes )
                );
                const style = toStyle( [
                        [ 'text-align', align ],
                        [ 'color', color ],
                        ...typographyPairs( attributes ),
                        ...spacingPairs( attributes ),
                ] );

                return el(
                        Tag,
                        useBlockProps.save( { className, style, id: anchor || undefined } ),
                        el( RichText.Content, { value: text } )
                );
        },
} );
