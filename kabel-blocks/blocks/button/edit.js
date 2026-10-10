/**
 * kbl/button — inline-editable CTA button with variants, size, radius.
 * Can open the Kabel RFQ popup (kb-rfq-open) instead of following a link.
 *
 * v4 (Stackable-style responsive layer): font size, horizontal padding and
 * the top / bottom margins are per-device attributes (fontSize / padX / mt /
 * mb with their T / M variants) riding the per-instance <style> tag. The uid
 * class sits on the ROOT .kbl3-btnwrap: the wrap consumes --kbl-mt /
 * --kbl-mb, the inner .kbl3-btn inherits --kbl-fs / --kbl-px. Legacy
 * content without a uid keeps the exact v3 output (inline --kbl3-* vars).
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
        useBlockProps,
        RichText,
        BlockControls,
        AlignmentToolbar,
        URLInput,
} from '@wordpress/block-editor';
import {
        ToolbarGroup,
        ToolbarButton,
        Dropdown,
        ToggleControl,
} from '@wordpress/components';
import { sanitizeClass, cx, toStyle, applyVisibility, applySpacing, spacingPairs, typographyPairs } from '../../src/util.js';
import {
        KblInspector,
        SegButtons,
        RangeRow,
        ColorsPanel,
        BorderPanel,
        ToggleRow,
        AlignButtons,
        TextRow,
        TypographyPanel,
        SpacingPanel,
        VisibilityPanel,
        HoverPanel,
        Resp,
        useUid,
        uidClass,
        respStyleEl,
        v4Strip,
} from '../../src/kit.js';

export const VARIANT_OPTIONS = [
        { value: 'gold', label: __( 'Bronze', 'kabel-blocks' ) },
        { value: 'solid', label: __( 'Navy', 'kabel-blocks' ) },
        { value: 'outline', label: __( 'Outline', 'kabel-blocks' ) },
        { value: 'ghost', label: __( 'Ghost', 'kabel-blocks' ) },
        { value: 'light', label: __( 'Light', 'kabel-blocks' ) },
];

/* Which attributes ride the per-instance <style> tag (respCss spec). */
export const RESP_SPEC = [
        { a: 'fontSize', p: '--kbl-fs', u: 'px' },
        { a: 'padX', p: '--kbl-px', u: 'px' },
        { a: 'mt', p: '--kbl-mt', u: 'px' },
        { a: 'mb', p: '--kbl-mb', u: 'px' },
];
/* The same keys the legacy pair helpers must stop emitting once a uid
 * exists (they moved to the responsive sheet). Keep in sync with RESP_SPEC
 * plus the v3 mobile variants. PHP twin: kbl4_strip() inside kbl3_button(). */
export const STRIP_KEYS = [ 'fontSize', 'fontSizeT', 'fontSizeM', 'padX', 'padXT', 'padXM', 'mt', 'mtT', 'mtM', 'mb', 'mbT', 'mbM' ];

export function Edit( { attributes, setAttributes, clientId, isSelected } ) {
                const {
                        text, url, newTab, rfq, rfqProduct, variant, size, radius, align, full,
                        bgColor, textColor, padX, padY, borderWidth, borderColor, borderStyle,
                } = attributes;

                useUid( attributes, setAttributes, clientId, isSelected );
                const legacy = v4Strip( attributes, STRIP_KEYS );

                const btnClass = cx(
                        'kbl3-btn',
                        `kbl3-btn--${ variant }`,
                        `kbl3-btn--${ size }`,
                        full && 'kbl3-btn--full',
                        rfq && ! url && 'kb-rfq-open',
                        attributes.hoverLift && 'kbl3-hover--lift',
                        attributes.hoverBg && 'kbl3-has-hover-bg',
                        attributes.hoverText && 'kbl3-has-hover-tx',
                        ...applyVisibility( attributes ),
                        ...applySpacing( legacy )
                );
                const btnStyle = toStyle( [
                        [ 'border-radius', radius ? `${ radius }px` : null ],
                        [ '--kbl3-btn-px', legacy.padX != null ? `${ legacy.padX }px` : null ],
                        [ '--kbl3-btn-py', legacy.padY != null ? `${ legacy.padY }px` : null ],
                        [ 'border', borderColor && borderWidth ? `${ borderWidth }px ${ borderStyle || 'solid' } ${ borderColor }` : null ],
                        [ 'background-color', bgColor ],
                        [ 'color', textColor ],
                        ...typographyPairs( legacy, { lh: false, ls: false, tt: false } ),
                        [ '--kbl3-h-bg', attributes.hoverBg ],
                        [ '--kbl3-h-tx', attributes.hoverText ],
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
                                        el( Dropdown, {
                                                renderToggle: ( { isOpen, onToggle } ) =>
                                                        el( ToolbarButton, {
                                                                icon: 'admin-links',
                                                                title: __( 'Link', 'kabel-blocks' ),
                                                                onClick: onToggle,
                                                                isActive: !! url,
                                                        } ),
                                                renderContent: () =>
                                                        el(
                                                                'div',
                                                                { className: 'kbl3-link-pop' },
                                                                el( URLInput, {
                                                                        value: url,
                                                                        onChange: ( v ) => setAttributes( { url: v, rfq: v ? false : rfq } ),
                                                                } ),
                                                                el( ToggleControl, {
                                                                        label: __( 'Open in new tab', 'kabel-blocks' ),
                                                                        checked: !! newTab,
                                                                        onChange: ( v ) => setAttributes( { newTab: v } ),
                                                                } )
                                                        ),
                                        } )
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
                                                        label: __( 'Variant', 'kabel-blocks' ),
                                                        value: variant,
                                                        onChange: ( v ) => setAttributes( { variant: v } ),
                                                        options: VARIANT_OPTIONS,
                                                } ),
                                                el( SegButtons, {
                                                        label: __( 'Size', 'kabel-blocks' ),
                                                        value: size,
                                                        onChange: ( v ) => setAttributes( { size: v } ),
                                                        options: [
                                                                { value: 's', label: 'S' },
                                                                { value: 'm', label: 'M' },
                                                                { value: 'l', label: 'L' },
                                                        ],
                                                } ),
                                                el( Resp, { prop: 'padX', attrs: attributes, setAttrs: setAttributes },
                                                        el( RangeRow, {
                                                                label: __( 'Horizontal padding', 'kabel-blocks' ),
                                                                min: 4,
                                                                max: 80,
                                                        } ) ),
                                                el( RangeRow, {
                                                        label: __( 'Vertical padding', 'kabel-blocks' ),
                                                        value: padY,
                                                        onChange: ( v ) => setAttributes( { padY: v } ),
                                                        min: 2,
                                                        max: 40,
                                                } ),
                                                el( AlignButtons, {
                                                        value: align,
                                                        onChange: ( v ) => setAttributes( { align: v } ),
                                                } ),
                                                el( ToggleRow, {
                                                        label: __( 'Full width', 'kabel-blocks' ),
                                                        checked: !! full,
                                                        onChange: ( v ) => setAttributes( { full: v } ),
                                                } ),
                                                el( ToggleRow, {
                                                        label: __( 'Open inquiry popup', 'kabel-blocks' ),
                                                        checked: !! rfq,
                                                        onChange: ( v ) => setAttributes( { rfq: v, url: v ? undefined : url } ),
                                                        help: __( 'Opens the Kabel inquiry popup instead of following a link.', 'kabel-blocks' ),
                                                } ),
                                                rfq ? el( TextRow, {
                                                        label: __( 'Popup context (optional)', 'kabel-blocks' ),
                                                        value: rfqProduct,
                                                        onChange: ( v ) => setAttributes( { rfqProduct: v } ),
                                                        help: __( 'Pre-fills which product the inquiry is about.', 'kabel-blocks' ),
                                                } ) : null
                                        ),
                                        style: el( 'div', null,
                                                el( BorderPanel, {
                                                        radius,
                                                        onRadius: ( v ) => setAttributes( { radius: v } ),
                                                        borderWidth,
                                                        onBorderWidth: ( v ) => setAttributes( { borderWidth: v } ),
                                                        borderColor,
                                                        onBorderColor: ( v ) => setAttributes( { borderColor: v } ),
                                                        borderStyle,
                                                        onBorderStyle: ( v ) => setAttributes( { borderStyle: v } ),
                                                } ),
                                                el( ColorsPanel, {
                                                        colors: [
                                                                {
                                                                        label: __( 'Background', 'kabel-blocks' ),
                                                                        value: bgColor,
                                                                        onChange: ( v ) => setAttributes( { bgColor: v } ),
                                                                        isBackground: true,
                                                                },
                                                                {
                                                                        label: __( 'Label color', 'kabel-blocks' ),
                                                                        value: textColor,
                                                                        onChange: ( v ) => setAttributes( { textColor: v } ),
                                                                        isText: true,
                                                                },
                                                        ],
                                                } ),
                                                el( TypographyPanel, {
                                                        attrs: attributes,
                                                        setAttrs: setAttributes,
                                                        include: { lh: false, ls: false, tt: false },
                                                        v4: true,
                                                } ),
                                                el( HoverPanel, {
                                                        attrs: attributes,
                                                        setAttrs: setAttributes,
                                                } )
                                        ),
                                        advanced: el( 'div', null,
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
                        el(
                                'div',
                                useBlockProps( {
                                        className: cx( 'kbl3-btnwrap', uidClass( attributes ) ),
                                        style: toStyle( [ [ 'text-align', align ] ] ),
                                        id: attributes.anchor || undefined,
                                } ),
                                el( RichText, {
                                        tagName: 'a',
                                        value: text,
                                        onChange: ( v ) => setAttributes( { text: v } ),
                                        placeholder: __( 'Button label…', 'kabel-blocks' ),
                                        allowedFormats: [ 'core/bold', 'core/italic' ],
                                        className: btnClass,
                                        style: btnStyle,
                                        href: url || ( rfq ? '#kb-rfq' : undefined ),
                                        ...( rfq && ! url && rfqProduct ? { 'data-kb-product': rfqProduct } : {} ),
                                        identifier: 'text',
                                } )
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
