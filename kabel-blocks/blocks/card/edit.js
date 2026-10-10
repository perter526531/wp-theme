/**
 * kbl/card — the universal content card: cover image, title, text and an
 * optional CTA button in one dark bronze card. Fully inline-editable.
 *
 * v4 (Stackable-style responsive layer): the inner padding and the top /
 * bottom margins are per-device attributes (padding / paddingT / paddingM,
 * mt / mb with T / M variants) riding the per-instance <style> tag. The
 * card has no --kbl-* consumption chains in the shared stylesheet yet, so
 * the sheet emits REAL properties: margins on the uid root (source order
 * beats the .kbl3-card skin), the padding on the .kbl3-card__body
 * descendant (specificity 0,2,0 beats every skin rule). The title font
 * size stays on the v3 --kbl3-fs chain (TypographyPanel keeps its own
 * device switch — same decision as icon-box). The Advanced tab gains the
 * per-instance custom CSS box and the HTML anchor (AdvancedPanelV2).
 * Legacy content without a uid keeps the exact v3 output.
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
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
        AlignButtons,
        ToggleRow,
        TextRow,
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

/* Which attributes ride the per-instance <style> tag (respCss spec). The
 * padding rule targets the body descendant (sel), the margins the root. */
export const RESP_SPEC = [
        { a: 'padding', p: 'padding', u: 'px', sel: ' .kbl3-card__body' },
        { a: 'mt', p: 'margin-top', u: 'px' },
        { a: 'mb', p: 'margin-bottom', u: 'px' },
];
/* Keys the legacy emission must drop once a uid exists (the inline body
 * padding would beat every media-query override). The title font size
 * stays on the v3 --kbl3-fs chain. PHP twin: kbl4_strip() inside
 * kbl3_card(). */
export const STRIP_KEYS = [ 'padding', 'paddingT', 'paddingM', 'mt', 'mtT', 'mtM', 'mb', 'mbT', 'mbM' ];

/** Shared by edit + save (imported by save.js). PHP twin: kbl3_card().
 * Receives the v4-stripped attribute copy (v4Strip keeps the uid). */
export function cardClass( a ) {
        return cx(
                'kbl3-card',
                a.align === 'center' && 'is-center',
                a.align === 'right' && 'is-right',
                a.hoverLift && 'kbl3-hover--lift',
                uidClass( a ),
                ...applyAnim( a ),
                ...applyVisibility( a ),
                ...applySpacing( a ),
                sanitizeClass( a.customClass )
        );
}

/** Shared by edit + save (imported by save.js). PHP twin: kbl3_card_style().
 * Receives the v4-stripped attribute copy. */
export function cardStyle( a ) {
        const grad = gradientValue( a );
        return toStyle( [
                [ 'background-color', grad ? null : a.bgColor ],
                [ 'background-image', grad ],
                [ 'color', a.textColor ],
                [ 'border-radius', a.radius ? `${ a.radius }px` : null ],
                [ 'box-shadow', shadowValue( a ) ],
                [ 'border', a.borderColor && a.borderWidth ? `${ a.borderWidth }px ${ a.borderStyle || 'solid' } ${ a.borderColor }` : null ],
                ...typographyPairs( a, { fw: false, lh: false, ls: false, tt: false } ),
                ...animDelayPairs( a ),
                ...spacingPairs( a ),
        ] );
}

/** The body padding inline pair, computed from the v4-stripped copy — with
 * a uid the padding rides the per-instance sheet instead. PHP twin: the
 * $body_style branch inside kbl3_card(). */
export function cardBodyStyle( legacy ) {
        return toStyle( [
                [ 'padding', legacy.padding != null ? `${ legacy.padding }px` : null ],
        ] );
}

export function Edit( { attributes, setAttributes, clientId, isSelected } ) {
                const a = attributes;

                useUid( attributes, setAttributes, clientId, isSelected );
                const legacy = v4Strip( attributes, STRIP_KEYS );

                const className = cardClass( legacy );
                const style = cardStyle( legacy );
                const bodyStyle = cardBodyStyle( legacy );
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
                                        attrs: attributes,
                                        setAttrs: setAttributes,
                                        v4: true,
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
                                        advanced: el(
                                                'div',
                                                null,
                                                el( Resp, { prop: 'padding', attrs: attributes, setAttrs: setAttributes },
                                                        el( RangeRow, {
                                                                label: __( 'Inner padding', 'kabel-blocks' ),
                                                                min: 0,
                                                                max: 96,
                                                        } ) ),
                                                el( TextRow, {
                                                        label: __( 'Button link', 'kabel-blocks' ),
                                                        value: a.btnURL,
                                                        onChange: ( v ) => setAttributes( { btnURL: v } ),
                                                        placeholder: '/colors/',
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
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
