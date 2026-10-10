/**
 * kbl/button — inline-editable CTA button with variants, size, radius.
 * Can open the Kabel RFQ popup (kb-rfq-open) instead of following a link.
 */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { register } from '../register.js';
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
import { cx, toStyle, applyVisibility, applySpacing, spacingPairs, typographyPairs } from '../util.js';
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
} from '../inspector.js';

const VARIANT_OPTIONS = [
        { value: 'gold', label: __( 'Bronze', 'kabel-blocks' ) },
        { value: 'solid', label: __( 'Navy', 'kabel-blocks' ) },
        { value: 'outline', label: __( 'Outline', 'kabel-blocks' ) },
        { value: 'ghost', label: __( 'Ghost', 'kabel-blocks' ) },
        { value: 'light', label: __( 'Light', 'kabel-blocks' ) },
];

register( 'kbl/button', {
        apiVersion: 3,
        title: __( 'Button (Kabel)', 'kabel-blocks' ),
        description: __(
                'Bronze/navy/outlined call-to-action with inline-editable label.',
                'kabel-blocks'
        ),
        category: 'kabel',
        keywords: [ 'kabel', 'button', 'cta', 'link', '按钮', '按钮', '行动按钮'],
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
                        selector: 'a',
                },
                url: { type: 'string' },
                newTab: { type: 'boolean' },
                rfq: { type: 'boolean' },
                rfqProduct: { type: 'string' },
                variant: { type: 'string', default: 'gold' },
                size: { type: 'string', default: 'm' },
                radius: { type: 'number' },
                padX: { type: 'number' },
                padY: { type: 'number' },
                borderWidth: { type: 'number' },
                borderColor: { type: 'string' },
                borderStyle: { type: 'string' },
                align: { type: 'string' },
                full: { type: 'boolean' },
                bgColor: { type: 'string' },
                textColor: { type: 'string' },
                fontSize: { type: 'number' },
                fontSizeT: { type: 'number' },
                fontSizeM: { type: 'number' },
                fontWeight: { type: 'number' },
                hoverBg: { type: 'string' },
                hoverText: { type: 'string' },
                hoverLift: { type: 'boolean' },
                hideD: { type: 'boolean' },
                hideT: { type: 'boolean' },
                hideM: { type: 'boolean' },
                mt: { type: 'number' },
                mb: { type: 'number' },
                mtM: { type: 'number' },
                mbM: { type: 'number' },
        },

        edit( { attributes, setAttributes } ) {
                const {
                        text, url, newTab, rfq, rfqProduct, variant, size, radius, align, full,
                        bgColor, textColor, padX, padY, borderWidth, borderColor, borderStyle,
                } = attributes;

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
                        ...applySpacing( attributes )
                );
                const btnStyle = toStyle( [
                        [ 'border-radius', radius ? `${ radius }px` : null ],
                        [ '--kbl3-btn-px', padX != null ? `${ padX }px` : null ],
                        [ '--kbl3-btn-py', padY != null ? `${ padY }px` : null ],
                        [ 'border', borderColor && borderWidth ? `${ borderWidth }px ${ borderStyle || 'solid' } ${ borderColor }` : null ],
                        [ 'background-color', bgColor ],
                        [ 'color', textColor ],
                        ...typographyPairs( attributes, { lh: false, ls: false, tt: false } ),
                        [ '--kbl3-h-bg', attributes.hoverBg ],
                        [ '--kbl3-h-tx', attributes.hoverText ],
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
                                                el( RangeRow, {
                                                        label: __( 'Horizontal padding', 'kabel-blocks' ),
                                                        value: padX,
                                                        onChange: ( v ) => setAttributes( { padX: v } ),
                                                        min: 4,
                                                        max: 80,
                                                } ),
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
                                        className: 'kbl3-btnwrap',
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
                        )
                );
        },

        save( { attributes } ) {
                const {
                        text, url, newTab, rfq, rfqProduct, variant, size, radius, align, full,
                        bgColor, textColor, padX, padY, borderWidth, borderColor, borderStyle, anchor,
                } = attributes;

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
                        ...applySpacing( attributes )
                );
                const btnStyle = toStyle( [
                        [ 'border-radius', radius ? `${ radius }px` : null ],
                        [ '--kbl3-btn-px', padX != null ? `${ padX }px` : null ],
                        [ '--kbl3-btn-py', padY != null ? `${ padY }px` : null ],
                        [ 'border', borderColor && borderWidth ? `${ borderWidth }px ${ borderStyle || 'solid' } ${ borderColor }` : null ],
                        [ 'background-color', bgColor ],
                        [ 'color', textColor ],
                        ...typographyPairs( attributes, { lh: false, ls: false, tt: false } ),
                        [ '--kbl3-h-bg', attributes.hoverBg ],
                        [ '--kbl3-h-tx', attributes.hoverText ],
                        ...spacingPairs( attributes ),
                ] );

                const linkProps = url
                        ? { href: url, ...( newTab ? { target: '_blank', rel: 'noopener noreferrer' } : {} ) }
                        : rfq
                                ? { href: '#kb-rfq', ...( rfqProduct ? { 'data-kb-product': rfqProduct } : {} ) }
                                : {};

                return el(
                        'div',
                        useBlockProps.save( {
                                className: 'kbl3-btnwrap',
                                style: toStyle( [ [ 'text-align', align ] ] ),
                                id: anchor || undefined,
                        } ),
                        el(
                                'a',
                                { className: btnClass, style: btnStyle, ...linkProps },
                                el( RichText.Content, { value: text } )
                        )
                );
        },
} );
