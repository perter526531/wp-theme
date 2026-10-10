/**
 * kbl/icon-box — icon + title + text composite, fully inline-editable.
 * The classic "feature card" primitive (icon-and-copy column filler).
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
import {
        cx,
        toStyle,
        iconSvg,
        SHADOWS,
        applyVisibility,
        applySpacing,
        spacingPairs,
        applyAnim,
        animDelayPairs,
        typographyPairs,
        gradientValue,
} from '../util.js';
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
} from '../inspector.js';

register( 'kbl/icon-box', {
        apiVersion: 3,
        title: __( 'Icon box (Kabel)', 'kabel-blocks' ),
        description: __(
                'Icon + heading + paragraph in one card — edit every part inline.',
                'kabel-blocks'
        ),
        category: 'kabel',
        keywords: [ 'kabel', 'icon box', 'feature', 'card', '图标', '图标框', '特性' ],
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
                iconName: { type: 'string', default: 'check' },
                iconColor: { type: 'string' },
                iconBg: { type: 'string' },
                iconPad: { type: 'number' },
                iconRadius: { type: 'number' },
                iconValign: { type: 'string' },
                title: {
                        type: 'string',
                        source: 'html',
                        selector: '.kbl3-ibox__title',
                },
                text: {
                        type: 'string',
                        source: 'html',
                        selector: '.kbl3-ibox__text',
                },
                layout: { type: 'string', default: 'v' },
                align: { type: 'string', default: 'left' },
                iconSize: { type: 'number' },
                bgColor: { type: 'string' },
                padding: { type: 'number', default: 24 },
                radius: { type: 'number' },
                shadow: { type: 'string' },
                borderWidth: { type: 'number' },
                borderColor: { type: 'string' },
                borderStyle: { type: 'string' },
                fontSize: { type: 'number' },
                fontSizeT: { type: 'number' },
                fontSizeM: { type: 'number' },
                gFrom: { type: 'string' },
                gTo: { type: 'string' },
                gAngle: { type: 'number', default: 135 },
                hoverLift: { type: 'boolean' },
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
                const a = attributes;

                const className = cx(
                        'kbl3-ibox',
                        a.layout === 'h' && 'kbl3-ibox--h',
                        a.layout === 'hr' && 'kbl3-ibox--hr',
                        a.iconValign === 'center' && 'kbl3-ibox--mid',
                        a.align === 'center' && 'is-center',
                        a.hoverLift && 'kbl3-hover--lift',
                        ...applyAnim( a ),
                        ...applyVisibility( a ),
                        ...applySpacing( a )
                );
                const style = boxStyle( a );

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
                                                el( RangeRow, {
                                                        label: __( 'Icon size', 'kabel-blocks' ),
                                                        value: a.iconSize,
                                                        onChange: ( v ) => setAttributes( { iconSize: v } ),
                                                        min: 16,
                                                        max: 96,
                                                } ),
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
                        )
                );
        },

        save( { attributes } ) {
                const a = attributes;

                const className = cx(
                        'kbl3-ibox',
                        a.layout === 'h' && 'kbl3-ibox--h',
                        a.layout === 'hr' && 'kbl3-ibox--hr',
                        a.iconValign === 'center' && 'kbl3-ibox--mid',
                        a.align === 'center' && 'is-center',
                        a.hoverLift && 'kbl3-hover--lift',
                        ...applyAnim( a ),
                        ...applyVisibility( a ),
                        ...applySpacing( a )
                );

                return el(
                        'div',
                        useBlockProps.save( { className, style: boxStyle( a ), id: a.anchor || undefined } ),
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
                                el( RichText.Content, {
                                        tagName: 'h4',
                                        className: 'kbl3-ibox__title',
                                        value: a.title,
                                } ),
                                el( RichText.Content, {
                                        tagName: 'p',
                                        className: 'kbl3-ibox__text',
                                        value: a.text,
                                } )
                        )
                );
        },
} );

/** Shared by edit + save. PHP twin: kbl3_ibox_icon_style(). */
function iconStyle( a ) {
        return toStyle( [
                [ 'color', a.iconColor ],
                [ 'background-color', a.iconBg ],
                [ 'padding', a.iconBg && a.iconPad != null ? `${ a.iconPad }px` : null ],
                [ 'border-radius', a.iconBg && a.iconRadius != null ? `${ a.iconRadius }px` : null ],
        ] );
}

/** Shared by edit + save. PHP twin: kbl3_ibox_style(). */
function boxStyle( a ) {
        const grad = gradientValue( a );
        return toStyle( [
                [ 'background-color', grad ? null : a.bgColor ],
                [ 'background-image', grad ],
                [ '--kbl3-ibox-ic', a.iconSize != null ? `${ a.iconSize }px` : null ],
                [ 'padding', a.padding != null ? `${ a.padding }px` : null ],
                [ 'border-radius', a.radius ? `${ a.radius }px` : null ],
                [ 'box-shadow', a.shadow ? SHADOWS[ a.shadow ] || null : null ],
                [ 'border', a.borderColor && a.borderWidth ? `${ a.borderWidth }px ${ a.borderStyle || 'solid' } ${ a.borderColor }` : null ],
                ...typographyPairs( a, { fw: false, lh: false, ls: false, tt: false } ),
                ...animDelayPairs( a ),
                ...spacingPairs( a ),
        ] );
}
