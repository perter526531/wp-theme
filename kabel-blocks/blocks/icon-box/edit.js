/**
 * kbl/icon-box — icon + title + text composite, fully inline-editable.
 * The classic "feature card" primitive (icon-and-copy column filler).
 *
 * v4 (Stackable-style responsive layer): the icon size and the top / bottom
 * margins are per-device attributes (iconSize / iconSizeT / iconSizeM,
 * mt / mb with T / M variants) riding the per-instance <style> tag. The
 * --kbl-is variable is set on the ROOT and consumed by the child
 * .kbl3-ibox__icon svg rule (custom properties inherit down). The Advanced
 * tab gains the per-instance custom CSS box and the HTML anchor
 * (AdvancedPanelV2). Legacy content without a uid keeps the exact v3
 * output.
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
        useBlockProps,
        RichText,
        BlockControls,
        AlignmentToolbar,
} from '@wordpress/block-editor';
import {
        cx,
        toStyle,
        iconSvg,
        shadowValue,
        applyVisibility,
        applySpacing,
        spacingPairs,
        applyAnim,
        animDelayPairs,
        typographyPairs,
        gradientValue,
        sanitizeClass,
} from '../../src/util.js';
import {
        KblInspector,
        SegButtons,
        RangeRow,
        ColorsPanel,
        BorderPanel,
        IconPicker,
        AlignButtons,
        TypographyPanel,
        SpacingPanel,
        VisibilityPanel,
        AnimPanel,
        GradientPanel,
        HoverPanel,
        Resp,
        useUid,
        uidClass,
        respStyleEl,
        v4Strip,
} from '../../src/kit.js';

/* Which attributes ride the per-instance <style> tag (respCss spec). */
export const RESP_SPEC = [
        { a: 'iconSize', p: '--kbl-is', u: 'px' },
        { a: 'mt', p: '--kbl-mt', u: 'px' },
        { a: 'mb', p: '--kbl-mb', u: 'px' },
];
/* Keys the legacy emission must drop once a uid exists. The title font
 * size stays on the v3 --kbl3-fs chain (TypographyPanel keeps its own
 * device switch). PHP twin: kbl4_strip() inside kbl3_icon_box(). */
export const STRIP_KEYS = [ 'iconSize', 'iconSizeT', 'iconSizeM', 'mt', 'mtT', 'mtM', 'mb', 'mbT', 'mbM' ];

/** Shared by edit + save (imported by save.js). PHP twin: kbl3_ibox_icon_style(). */
export function iconStyle( a ) {
        return toStyle( [
                [ 'color', a.iconColor ],
                [ 'background-color', a.iconBg ],
                [ 'padding', a.iconBg && a.iconPad != null ? `${ a.iconPad }px` : null ],
                [ 'border-radius', a.iconBg && a.iconRadius != null ? `${ a.iconRadius }px` : null ],
        ] );
}

/** Shared by edit + save (imported by save.js). PHP twin: kbl3_ibox_style(). */
export function boxStyle( a ) {
        const grad = gradientValue( a );
        return toStyle( [
                [ 'background-color', grad ? null : a.bgColor ],
                [ 'background-image', grad ],
                [ '--kbl3-ibox-ic', a.iconSize != null ? `${ a.iconSize }px` : null ],
                [ 'padding', a.padding != null ? `${ a.padding }px` : null ],
                [ 'border-radius', a.radius ? `${ a.radius }px` : null ],
                [ 'box-shadow', shadowValue( a ) ],
                [ 'border', a.borderColor && a.borderWidth ? `${ a.borderWidth }px ${ a.borderStyle || 'solid' } ${ a.borderColor }` : null ],
                ...typographyPairs( a, { fw: false, lh: false, ls: false, tt: false } ),
                ...animDelayPairs( a ),
                ...spacingPairs( a ),
        ] );
}

export function Edit( { attributes, setAttributes, clientId, isSelected } ) {
                const a = attributes;

                useUid( attributes, setAttributes, clientId, isSelected );
                const legacy = v4Strip( attributes, STRIP_KEYS );

                const className = cx(
                        'kbl3-ibox',
                        a.layout === 'h' && 'kbl3-ibox--h',
                        a.layout === 'hr' && 'kbl3-ibox--hr',
                        a.iconValign === 'center' && 'kbl3-ibox--mid',
                        a.align === 'center' && 'is-center',
                        a.hoverLift && 'kbl3-hover--lift',
                        uidClass( attributes ),
                        ...applyAnim( a ),
                        ...applyVisibility( a ),
                        ...applySpacing( legacy )
                );
                const style = boxStyle( legacy );

                return el(
                        Fr,
                        null,
                        el(
                                BlockControls,
                                null,
                                el( AlignmentToolbar, {
                                        value: a.align,
                                        onChange: ( v ) => setAttributes( { align: v || 'left' } ),
                                } )
                        ),
                        el(
                                KblInspector,
                                {
                                        attrs: attributes,
                                        setAttrs: setAttributes,
                                        v4: true,
                                        layout: el( 'div', null,
                                                el( IconPicker, {
                                                        value: a.iconName,
                                                        onChange: ( v ) => setAttributes( { iconName: v } ),
                                                } ),
                                                el( SegButtons, {
                                                        label: __( 'Icon position', 'kabel-blocks' ),
                                                        value: a.layout,
                                                        onChange: ( v ) => setAttributes( { layout: v } ),
                                                        options: [
                                                                { value: 'v', label: __( 'Top', 'kabel-blocks' ) },
                                                                { value: 'h', label: __( 'Left', 'kabel-blocks' ) },
                                                                { value: 'hr', label: __( 'Right', 'kabel-blocks' ) },
                                                        ],
                                                } ),
                                                a.layout !== 'v' ? el( SegButtons, {
                                                        label: __( 'Icon vertical alignment', 'kabel-blocks' ),
                                                        value: a.iconValign || 'top',
                                                        onChange: ( v ) => setAttributes( { iconValign: v === 'top' ? undefined : v } ),
                                                        options: [
                                                                { value: 'top', label: __( 'Top', 'kabel-blocks' ) },
                                                                { value: 'center', label: __( 'Center', 'kabel-blocks' ) },
                                                        ],
                                                } ) : null,
                                                el( AlignButtons, {
                                                        label: __( 'Content alignment', 'kabel-blocks' ),
                                                        value: a.align === 'right' ? 'right' : a.align,
                                                        onChange: ( v ) => setAttributes( { align: v || 'left' } ),
                                                } ),
                                                el( Resp, { prop: 'iconSize', attrs: attributes, setAttrs: setAttributes },
                                                        el( RangeRow, {
                                                                label: __( 'Icon size', 'kabel-blocks' ),
                                                                min: 16,
                                                                max: 96,
                                                        } ) ),
                                                a.iconBg ? el( RangeRow, {
                                                        label: __( 'Icon background padding', 'kabel-blocks' ),
                                                        value: a.iconPad,
                                                        onChange: ( v ) => setAttributes( { iconPad: v } ),
                                                        min: 0,
                                                        max: 48,
                                                } ) : null,
                                                a.iconBg ? el( RangeRow, {
                                                        label: __( 'Icon background radius', 'kabel-blocks' ),
                                                        value: a.iconRadius,
                                                        onChange: ( v ) => setAttributes( { iconRadius: v } ),
                                                        min: 0,
                                                        max: 40,
                                                } ) : null
                                        ),
                                        style: el( 'div', null,
                                                el( ColorsPanel, {
                                                        colors: [
                                                                {
                                                                        label: __( 'Card background', 'kabel-blocks' ),
                                                                        value: a.bgColor,
                                                                        onChange: ( v ) => setAttributes( { bgColor: v } ),
                                                                        isBackground: true,
                                                                },
                                                                {
                                                                        label: __( 'Icon color', 'kabel-blocks' ),
                                                                        value: a.iconColor,
                                                                        onChange: ( v ) => setAttributes( { iconColor: v } ),
                                                                },
                                                                {
                                                                        label: __( 'Icon background', 'kabel-blocks' ),
                                                                        value: a.iconBg,
                                                                        onChange: ( v ) => setAttributes( { iconBg: v } ),
                                                                },
                                                        ],
                                                } ),
                                                el( BorderPanel, {
                                                        radius: a.radius,
                                                        onRadius: ( v ) => setAttributes( { radius: v } ),
                                                        shadow: a.shadow,
                                                        onShadow: ( v ) => setAttributes( { shadow: v } ),
                                                        shadowX: a.shadowX,
                                                        onShadowX: ( v ) => setAttributes( { shadowX: v } ),
                                                        shadowY: a.shadowY,
                                                        onShadowY: ( v ) => setAttributes( { shadowY: v } ),
                                                        shadowB: a.shadowB,
                                                        onShadowB: ( v ) => setAttributes( { shadowB: v } ),
                                                        shadowS: a.shadowS,
                                                        onShadowS: ( v ) => setAttributes( { shadowS: v } ),
                                                        shadowColor: a.shadowColor,
                                                        onShadowColor: ( v ) => setAttributes( { shadowColor: v || undefined } ),
                                                        borderWidth: a.borderWidth,
                                                        onBorderWidth: ( v ) => setAttributes( { borderWidth: v } ),
                                                        borderColor: a.borderColor,
                                                        onBorderColor: ( v ) => setAttributes( { borderColor: v } ),
                                                        borderStyle: a.borderStyle,
                                                        onBorderStyle: ( v ) => setAttributes( { borderStyle: v } ),
                                                } ),
                                                el( GradientPanel, {
                                                        attrs: a,
                                                        setAttrs: setAttributes,
                                                } ),
                                                el( TypographyPanel, {
                                                        attrs: a,
                                                        setAttrs: setAttributes,
                                                        include: { fw: false, lh: false, ls: false, tt: false },
                                                } ),
                                                el( AnimPanel, {
                                                        attrs: a,
                                                        setAttrs: setAttributes,
                                                } ),
                                                el( HoverPanel, {
                                                        attrs: a,
                                                        setAttrs: setAttributes,
                                                        include: { colors: false, lift: true },
                                                } )
                                        ),
                                        advanced: el( 'div', null,
                                                el( RangeRow, {
                                                        label: __( 'Inner padding', 'kabel-blocks' ),
                                                        value: a.padding,
                                                        onChange: ( v ) => setAttributes( { padding: v ?? 24 } ),
                                                        min: 0,
                                                        max: 96,
                                                } ),
                                                el( SpacingPanel, {
                                                        attrs: a,
                                                        setAttrs: setAttributes,
                                                        v4: true,
                                                } ),
                                                el( VisibilityPanel, {
                                                        attrs: a,
                                                        setAttrs: setAttributes,
                                                } )
                                        ),
                                }
                        ),
                        el(
                                'div',
                                useBlockProps( { className, style, id: a.anchor || undefined } ),
                                el(
                                        'span',
                                        {
                                                className: cx( 'kbl3-icon', 'kbl3-ibox__icon', a.iconBg && 'kbl3-ibox__icon--chip' ),
                                                style: iconStyle( a ),
                                        },
                                        iconSvg( a.iconName )
                                ),
                                el(
                                        'div',
                                        { className: 'kbl3-ibox__body' },
                                        el( RichText, {
                                                tagName: 'h4',
                                                className: 'kbl3-ibox__title',
                                                value: a.title,
                                                onChange: ( v ) => setAttributes( { title: v } ),
                                                placeholder: __( 'Feature title…', 'kabel-blocks' ),
                                                allowedFormats: [ 'core/bold', 'core/italic' ],
                                                identifier: 'title',
                                        } ),
                                        el( RichText, {
                                                tagName: 'p',
                                                className: 'kbl3-ibox__text',
                                                value: a.text,
                                                onChange: ( v ) => setAttributes( { text: v } ),
                                                placeholder: __( 'Describe the feature…', 'kabel-blocks' ),
                                                allowedFormats: [ 'core/bold', 'core/italic' ],
                                                identifier: 'text',
                                        } )
                                )
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
