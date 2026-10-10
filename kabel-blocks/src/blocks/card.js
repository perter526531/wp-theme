/**
 * kbl/card — the universal content card: cover image, title, text and an
 * optional CTA button in one dark bronze card. Fully inline-editable.
 */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { register } from '../register.js';
import {
        useBlockProps,
        RichText,
        BlockControls,
        AlignmentToolbar,
        MediaUpload,
        MediaUploadCheck,
} from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton } from '@wordpress/components';
import {
        cx,
        toStyle,
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
        AlignButtons,
        ToggleRow,
        TextRow,
        TypographyPanel,
        SpacingPanel,
        VisibilityPanel,
        AnimPanel,
        GradientPanel,
        HoverPanel,
} from '../inspector.js';

register( 'kbl/card', {
        apiVersion: 3,
        title: __( 'Card (Kabel)', 'kabel-blocks' ),
        description: __(
                'Cover image, title, text and an optional button in one bronze-lined card.',
                'kabel-blocks'
        ),
        category: 'kabel',
        keywords: [ 'kabel', 'card', 'box', 'feature', '卡片', '内容卡', '卡片', '图文卡'],
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
                mediaId: { type: 'number' },
                mediaURL: { type: 'string' },
                title: {
                        type: 'string',
                        source: 'html',
                        selector: '.kbl3-card__title',
                        default: 'Wall Color Collection',
                },
                text: {
                        type: 'string',
                        source: 'html',
                        selector: '.kbl3-card__text',
                        default:
                                'Velvet-matte finish with ultra-low VOC — rich pigment that stays true for years.',
                },
                btnLabel: {
                        type: 'string',
                        source: 'html',
                        selector: 'a',
                },
                btnURL: { type: 'string' },
                btnStyle: { type: 'string', default: 'cta' },
                align: { type: 'string' },
                padding: { type: 'number', default: 24 },
                bgColor: { type: 'string' },
                textColor: { type: 'string' },
                radius: { type: 'number' },
                shadow: { type: 'string' },
                borderWidth: { type: 'number' },
                borderColor: { type: 'string' },
                borderStyle: { type: 'string' },
                gFrom: { type: 'string' },
                gTo: { type: 'string' },
                gAngle: { type: 'number', default: 135 },
                hoverLift: { type: 'boolean' },
                fontSize: { type: 'number' },
                fontSizeT: { type: 'number' },
                fontSizeM: { type: 'number' },
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

                const className = cardClass( a );
                const style = cardStyle( a );
                const bodyStyle = toStyle( [
                        [ 'padding', a.padding != null ? `${ a.padding }px` : null ],
                ] );
                const btnClass = cx(
                        'kbl3-btn',
                        `kbl3-btn--${ a.btnStyle === 'outline' ? 'outline' : 'gold' }`,
                        'kbl3-btn--s',
                        'kbl3-card__btn'
                );

                return el(
                        Fr,
                        null,
                        el(
                                BlockControls,
                                null,
                                el( AlignmentToolbar, {
                                        value: a.align,
                                        onChange: ( v ) => setAttributes( { align: v } ),
                                } ),
                                el(
                                        ToolbarGroup,
                                        null,
                                        el(
                                                MediaUploadCheck,
                                                null,
                                                el( MediaUpload, {
                                                        onSelect: ( media ) =>
                                                                setAttributes( { mediaId: media.id, mediaURL: media.url } ),
                                                        allowedTypes: [ 'image' ],
                                                        value: a.mediaId,
                                                        render: ( { open } ) =>
                                                                el( ToolbarButton, {
                                                                        icon: 'format-image',
                                                                        title: a.mediaURL
                                                                                ? __( 'Replace cover image', 'kabel-blocks' )
                                                                                : __( 'Add cover image', 'kabel-blocks' ),
                                                                        onClick: open,
                                                                } ),
                                                } )
                                        ),
                                        a.mediaURL
                                                ? el( ToolbarButton, {
                                                                icon: 'no',
                                                                title: __( 'Remove cover image', 'kabel-blocks' ),
                                                                onClick: () =>
                                                                        setAttributes( { mediaId: undefined, mediaURL: undefined } ),
                                                  } )
                                                : null
                                )
                        ),
                        el(
                                KblInspector,
                                {
                                        layout: el(
                                                'div',
                                                null,
                                                el( AlignButtons, {
                                                        label: __( 'Content alignment', 'kabel-blocks' ),
                                                        value: a.align || 'left',
                                                        onChange: ( v ) =>
                                                                setAttributes( { align: v === 'left' ? undefined : v } ),
                                                } ),
                                                el( SegButtons, {
                                                        label: __( 'Button style', 'kabel-blocks' ),
                                                        value: a.btnStyle,
                                                        onChange: ( v ) => setAttributes( { btnStyle: v } ),
                                                        options: [
                                                                { value: 'cta', label: __( 'Bronze', 'kabel-blocks' ) },
                                                                { value: 'outline', label: __( 'Outline', 'kabel-blocks' ) },
                                                        ],
                                                } )
                                        ),
                                        style: el(
                                                'div',
                                                null,
                                                el( ColorsPanel, {
                                                        colors: [
                                                                {
                                                                        label: __( 'Card background', 'kabel-blocks' ),
                                                                        value: a.bgColor,
                                                                        onChange: ( v ) => setAttributes( { bgColor: v } ),
                                                                        isBackground: true,
                                                                },
                                                                {
                                                                        label: __( 'Text color', 'kabel-blocks' ),
                                                                        value: a.textColor,
                                                                        onChange: ( v ) => setAttributes( { textColor: v } ),
                                                                        isText: true,
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
                                        advanced: el(
                                                'div',
                                                null,
                                                el( RangeRow, {
                                                        label: __( 'Inner padding', 'kabel-blocks' ),
                                                        value: a.padding,
                                                        onChange: ( v ) => setAttributes( { padding: v ?? 24 } ),
                                                        min: 0,
                                                        max: 96,
                                                } ),
                                                el( TextRow, {
                                                        label: __( 'Button link', 'kabel-blocks' ),
                                                        value: a.btnURL,
                                                        onChange: ( v ) => setAttributes( { btnURL: v } ),
                                                        placeholder: '/colors/',
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
                                        MediaUpload,
                                        {
                                                onSelect: ( media ) =>
                                                        setAttributes( { mediaId: media.id, mediaURL: media.url } ),
                                                allowedTypes: [ 'image' ],
                                                value: a.mediaId,
                                                render: ( { open } ) =>
                                                        el(
                                                                'figure',
                                                                { className: 'kbl3-card__media' },
                                                                a.mediaURL
                                                                        ? el( 'img', {
                                                                                        className: 'kbl3-card__img',
                                                                                        src: a.mediaURL,
                                                                                        alt: '',
                                                                                        onClick: open,
                                                                          } )
                                                                        : el(
                                                                                        'button',
                                                                                        {
                                                                                                        type: 'button',
                                                                                                        className: 'kbl3-card__add',
                                                                                                        onClick: open,
                                                                                        },
                                                                                        __( 'Add cover image', 'kabel-blocks' )
                                                                          )
                                                        ),
                                        }
                                ),
                                el(
                                        'div',
                                        { className: 'kbl3-card__body', style: bodyStyle },
                                        el( RichText, {
                                                tagName: 'h3',
                                                className: 'kbl3-card__title',
                                                value: a.title,
                                                onChange: ( v ) => setAttributes( { title: v } ),
                                                placeholder: __( 'Card title…', 'kabel-blocks' ),
                                                allowedFormats: [ 'core/bold', 'core/italic' ],
                                                identifier: 'title',
                                        } ),
                                        el( RichText, {
                                                tagName: 'p',
                                                className: 'kbl3-card__text',
                                                value: a.text,
                                                onChange: ( v ) => setAttributes( { text: v } ),
                                                placeholder: __( 'Describe the card…', 'kabel-blocks' ),
                                                allowedFormats: [ 'core/bold', 'core/italic' ],
                                                identifier: 'text',
                                        } ),
                                        el( RichText, {
                                                tagName: 'a',
                                                className: btnClass,
                                                value: a.btnLabel,
                                                onChange: ( v ) => setAttributes( { btnLabel: v } ),
                                                placeholder: __( 'Button label (optional)…', 'kabel-blocks' ),
                                                allowedFormats: [ 'core/bold', 'core/italic' ],
                                                href: a.btnURL || undefined,
                                                identifier: 'btnLabel',
                                        } )
                                )
                        )
                );
        },

        save( { attributes } ) {
                const a = attributes;

                const bodyStyle = toStyle( [
                        [ 'padding', a.padding != null ? `${ a.padding }px` : null ],
                ] );
                const btnClass = cx(
                        'kbl3-btn',
                        `kbl3-btn--${ a.btnStyle === 'outline' ? 'outline' : 'gold' }`,
                        'kbl3-btn--s',
                        'kbl3-card__btn'
                );

                return el(
                        'div',
                        useBlockProps.save( { className: cardClass( a ), style: cardStyle( a ), id: a.anchor || undefined } ),
                        a.mediaURL
                                ? el(
                                                'figure',
                                                { className: 'kbl3-card__media' },
                                                el( 'img', { className: 'kbl3-card__img', src: a.mediaURL, alt: '' } )
                                  )
                                : null,
                        el(
                                'div',
                                { className: 'kbl3-card__body', style: bodyStyle },
                                el( RichText.Content, {
                                        tagName: 'h3',
                                        className: 'kbl3-card__title',
                                        value: a.title,
                                } ),
                                el( RichText.Content, {
                                        tagName: 'p',
                                        className: 'kbl3-card__text',
                                        value: a.text,
                                } ),
                                a.btnLabel
                                        ? el(
                                                        'a',
                                                        {
                                                                        className: btnClass,
                                                                        ...( a.btnURL ? { href: a.btnURL } : {} ),
                                                        },
                                                        el( RichText.Content, { value: a.btnLabel } )
                                          )
                                        : null
                        )
                );
        },
} );

/** Shared by edit + save. PHP twin: kbl3_card_class(). */
function cardClass( a ) {
        return cx(
                'kbl3-card',
                a.align === 'center' && 'is-center',
                a.align === 'right' && 'is-right',
                a.hoverLift && 'kbl3-hover--lift',
                ...applyAnim( a ),
                ...applyVisibility( a ),
                ...applySpacing( a )
        );
}

/** Shared by edit + save. PHP twin: kbl3_card_style(). */
function cardStyle( a ) {
        const grad = gradientValue( a );
        return toStyle( [
                [ 'background-color', grad ? null : a.bgColor ],
                [ 'background-image', grad ],
                [ 'color', a.textColor ],
                [ 'border-radius', a.radius ? `${ a.radius }px` : null ],
                [ 'box-shadow', a.shadow ? SHADOWS[ a.shadow ] || null : null ],
                [ 'border', a.borderColor && a.borderWidth ? `${ a.borderWidth }px ${ a.borderStyle || 'solid' } ${ a.borderColor }` : null ],
                ...typographyPairs( a, { fw: false, lh: false, ls: false, tt: false } ),
                ...animDelayPairs( a ),
                ...spacingPairs( a ),
        ] );
}
