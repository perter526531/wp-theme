/**
 * kbl/heading — inline-editable title (H1–H6) with size/align/color and
 * independent block alignment (box position) vs text alignment (lines).
 *
 * v4 (Stackable-style responsive layer): font size and the top / bottom
 * margins are per-device attributes (desktop / tablet ≤1024px / mobile
 * ≤780px). They ride the shared <Resp> switch in the inspector and the
 * per-instance <style> tag (uid class + --kbl-* custom properties), NOT
 * the legacy inline --kbl3-* variables — see src/kit.js. The Advanced tab
 * gains the per-instance custom CSS box and the HTML anchor
 * (AdvancedPanelV2). Legacy content without a uid keeps the exact v3
 * output (v4Strip passes the attributes through untouched).
 */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
        useBlockProps,
        RichText,
        BlockControls,
        AlignmentToolbar,
} from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton } from '@wordpress/components';
import { sanitizeClass, cx, toStyle, applyVisibility, applySpacing, spacingPairs, applyAnim, typographyPairs, boxPairs } from '../../src/util.js';
import {
        KblInspector,
        SegButtons,
        ColorsPanel,
        ToggleRow,
        TypographyPanel,
        SpacingPanel,
        VisibilityPanel,
        AnimPanel,
        RangeRow,
        Resp,
        useUid,
        uidClass,
        respStyleEl,
        v4Strip,
} from '../../src/kit.js';

export const SIZE_OPTIONS = [
        { value: 's', label: 'S' },
        { value: 'm', label: 'M' },
        { value: 'l', label: 'L' },
        { value: 'xl', label: 'XL' },
];

/* Which attributes ride the per-instance <style> tag (respCss spec). */
export const RESP_SPEC = [
        { a: 'fontSize', p: '--kbl-fs', u: 'px' },
        { a: 'mt', p: '--kbl-mt', u: 'px' },
        { a: 'mb', p: '--kbl-mb', u: 'px' },
];
/* The same keys the legacy pair helpers must stop emitting once a uid
 * exists (they moved to the responsive sheet). Keep in sync with RESP_SPEC
 * plus the v3 mobile variants. PHP twin: kbl4_strip(). */
export const STRIP_KEYS = [ 'fontSize', 'fontSizeT', 'fontSizeM', 'mt', 'mtT', 'mtM', 'mb', 'mbT', 'mbM' ];

export function Edit( { attributes, setAttributes, clientId, isSelected } ) {
        const { text, level, size, align, color, eyebrow } = attributes;
        const Tag = `h${ level }`;
        useUid( attributes, setAttributes, clientId, isSelected );
        const legacy = v4Strip( attributes, STRIP_KEYS );

        const className = cx(
                'kbl3-h',
                `kbl3-h--${ size }`,
                eyebrow && 'is-eyebrow',
                uidClass( attributes ),
                ...applyAnim( attributes ),
                ...applyVisibility( attributes ),
                ...applySpacing( legacy )
        );
        const style = toStyle( [
                [ 'text-align', align ],
                [ 'color', color ],
                ...boxPairs( legacy ),
                ...typographyPairs( legacy ),
                ...spacingPairs( legacy ),
        ] );

        return el(
                Fr,
                null,
                el(
                        BlockControls,
                        null,
                        el(
                                ToolbarGroup,
                                null,
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
                                attrs: attributes,
                                setAttrs: setAttributes,
                                v4: true,
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
                                        el( SegButtons, {
                                                label: __( 'Block alignment', 'kabel-blocks' ),
                                                value: attributes.blockAlign,
                                                onChange: ( v ) => setAttributes( { blockAlign: v } ),
                                                options: [
                                                        { value: 'left', label: __( 'Left', 'kabel-blocks' ) },
                                                        { value: 'center', label: __( 'Center', 'kabel-blocks' ) },
                                                        { value: 'right', label: __( 'Right', 'kabel-blocks' ) },
                                                ],
                                        } ),
                                        attributes.blockAlign ? el( RangeRow, {
                                                label: __( 'Block width (px)', 'kabel-blocks' ),
                                                value: attributes.contentWidth,
                                                onChange: ( v ) => setAttributes( { contentWidth: v } ),
                                                min: 240,
                                                max: 1290,
                                                step: 10,
                                        } ) : null,
                                        el( 'p', { className: 'kbl3-panel-hint' }, __( 'Block alignment moves the whole text box; text alignment above only affects the lines inside.', 'kabel-blocks' ) )
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
                                                v4: true,
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
                                                v4: true,
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
                } ),
                respStyleEl( attributes, RESP_SPEC )
        );
}
