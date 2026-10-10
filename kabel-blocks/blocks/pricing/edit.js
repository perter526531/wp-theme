/**
 * kbl/pricing — one service/package price card: plan name, big bronze price,
 * check-marked feature list and a CTA. Optional "featured" treatment draws
 * a bronze outline and a badge.
 *
 * v4 (Stackable-style responsive layer): the price-number font size and the
 * top / bottom margins are per-device attributes (fontSize / fontSizeT /
 * fontSizeM, mt / mb with T / M variants) riding the per-instance <style>
 * tag. The font size keeps its --kbl3-fs variable chain — the shared CSS
 * already consumes it at every breakpoint (the tablet / mobile rules fall
 * back to --kbl3-fs, which the per-instance sheet re-scopes per media
 * query); the inline variables are dropped once a uid exists so the
 * media-query overrides can win. The Advanced tab gains the per-instance
 * custom CSS box and the HTML anchor (AdvancedPanelV2). Legacy content
 * without a uid keeps the exact v3 output. The feature list is untouched
 * repeater data.
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, BlockControls } from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton, PanelBody, Button } from '@wordpress/components';
import { sanitizeClass, cx, toStyle, shadowValue, applyVisibility, applySpacing, spacingPairs, applyAnim, animDelayPairs, typographyPairs } from '../../src/util.js';
import {
        KblInspector,
        ToggleRow,
        TextRow,
        ColorsPanel,
        BorderPanel,
        TypographyPanel,
        SpacingPanel,
        VisibilityPanel,
        AnimPanel,
        useUid,
        uidClass,
        respStyleEl,
        v4Strip,
} from '../../src/kit.js';

export const DEFAULT_FEATURES = [
        'Color consultation included',
        'Two-coat premium application',
        '5-year finish warranty',
        'Free touch-up kit',
];

/* Which attributes ride the per-instance <style> tag (respCss spec). */
export const RESP_SPEC = [
        { a: 'fontSize', p: '--kbl3-fs', u: 'px' },
        { a: 'mt', p: 'margin-top', u: 'px' },
        { a: 'mb', p: 'margin-bottom', u: 'px' },
];
/* Keys the legacy emission must drop once a uid exists (the inline
 * --kbl3-fs / --kbl3-m* variables would beat every media-query override).
 * PHP twin: kbl4_strip() inside kbl3_pricing(). */
export const STRIP_KEYS = [ 'fontSize', 'fontSizeT', 'fontSizeM', 'mt', 'mtT', 'mtM', 'mb', 'mbT', 'mbM' ];

/** Shared by edit + save (imported by save.js). PHP twin: the class list
 * in kbl3_pricing(). Takes the v4-stripped copy — the uid survives the strip
 * so the per-instance class rides in the same slot on both sides. */
export function priceClass( a ) {
        return cx(
                'kbl3-price',
                a.featured && 'is-featured',
                uidClass( a ),
                ...applyAnim( a ),
                ...applyVisibility( a ),
                ...applySpacing( a ),
                sanitizeClass( a.customClass )
        );
}

/** Shared by edit + save (imported by save.js). PHP twin: kbl3_price_style(). */
export function priceStyle( a ) {
        return toStyle( [
                [ 'background-color', a.bgColor ],
                [ 'border-radius', a.radius ? `${ a.radius }px` : null ],
                [ 'box-shadow', shadowValue( a ) ],
                ...typographyPairs( a, { fw: false, lh: false, ls: false, tt: false } ),
                ...animDelayPairs( a ),
                ...spacingPairs( a ),
        ] );
}

export function Edit( { attributes, setAttributes, clientId, isSelected } ) {
                const a = attributes;
                const features = a.features && a.features.length ? a.features : [];

                useUid( attributes, setAttributes, clientId, isSelected );
                const legacy = v4Strip( attributes, STRIP_KEYS );

                const className = priceClass( legacy );
                const style = priceStyle( legacy );

                const updateFeature = ( i, v ) => {
                        setAttributes( { features: features.map( ( f, j ) => ( j === i ? v : f ) ) } );
                };
                const removeFeature = ( i ) => {
                        setAttributes( { features: features.filter( ( _, j ) => j !== i ) } );
                };
                const moveFeature = ( i, dir ) => {
                        const j = i + dir;
                        if ( j < 0 || j >= features.length ) {
                                return;
                        }
                        const next = features.slice();
                        const [ moved ] = next.splice( i, 1 );
                        next.splice( j, 0, moved );
                        setAttributes( { features: next } );
                };

                const featuresEditor = el(
                        PanelBody,
                        { title: __( 'Features', 'kabel-blocks' ), initialOpen: true },
                        features.map( ( feature, i ) =>
                                el(
                                        'div',
                                        { className: 'kbl3-item-row', key: i },
                                        el( TextRow, {
                                                label: __( 'Feature', 'kabel-blocks' ) + ' ' + ( i + 1 ),
                                                value: feature,
                                                onChange: ( v ) => updateFeature( i, v ),
                                        } ),
                                        el(
                                                'div',
                                                { className: 'kbl3-item-row__btns' },
                                                el( Button, {
                                                        size: 'small',
                                                        variant: 'secondary',
                                                        disabled: i === 0,
                                                        onClick: () => moveFeature( i, -1 ),
                                                        label: __( 'Move up', 'kabel-blocks' ),
                                                        icon: 'arrow-up',
                                                } ),
                                                el( Button, {
                                                        size: 'small',
                                                        variant: 'secondary',
                                                        disabled: i === features.length - 1,
                                                        onClick: () => moveFeature( i, 1 ),
                                                        label: __( 'Move down', 'kabel-blocks' ),
                                                        icon: 'arrow-down',
                                                } ),
                                                el( Button, {
                                                        size: 'small',
                                                        variant: 'secondary',
                                                        isDestructive: true,
                                                        onClick: () => removeFeature( i ),
                                                        label: __( 'Remove feature', 'kabel-blocks' ),
                                                        icon: 'no',
                                                } )
                                        )
                                )
                        ),
                        el(
                                Button,
                                {
                                        variant: 'secondary',
                                        size: 'small',
                                        onClick: () => setAttributes( { features: [ ...features, '' ] } ),
                                },
                                __( 'Add feature', 'kabel-blocks' )
                        )
                );

                return el(
                        Fr,
                        null,
                        el(
                                BlockControls,
                                null,
                                el(
                                        ToolbarGroup,
                                        null,
                                        el( ToolbarButton, {
                                                icon: 'star-filled',
                                                title: a.featured
                                                        ? __( 'Remove featured highlight', 'kabel-blocks' )
                                                        : __( 'Mark as featured', 'kabel-blocks' ),
                                                isActive: !! a.featured,
                                                onClick: () => setAttributes( { featured: ! a.featured } ),
                                        } )
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
                                                el( ToggleRow, {
                                                        label: __( 'Featured package', 'kabel-blocks' ),
                                                        checked: !! a.featured,
                                                        onChange: ( v ) => setAttributes( { featured: v } ),
                                                        help: __(
                                                                'Bronze outline plus a "Most popular" badge.',
                                                                'kabel-blocks'
                                                        ),
                                                } ),
                                                featuresEditor
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
                                                } ),
                                                el( TypographyPanel, {
                                                        attrs: a,
                                                        setAttrs: setAttributes,
                                                        include: { fw: false, lh: false, ls: false, tt: false },
                                                        v4: true,
                                                } ),
                                                el( AnimPanel, {
                                                        attrs: a,
                                                        setAttrs: setAttributes,
                                                } )
                                        ),
                                        advanced: el(
                                                'div',
                                                null,
                                                el( TextRow, {
                                                        label: __( 'Button link', 'kabel-blocks' ),
                                                        value: a.btnURL,
                                                        onChange: ( v ) => setAttributes( { btnURL: v } ),
                                                        placeholder: '/contact/',
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
                                a.featured
                                        ? el( 'span', { className: 'kbl3-price__badge' }, __( 'Most popular', 'kabel-blocks' ) )
                                        : null,
                                el( RichText, {
                                        tagName: 'h3',
                                        className: 'kbl3-price__plan',
                                        value: a.planName,
                                        onChange: ( v ) => setAttributes( { planName: v } ),
                                        placeholder: __( 'Plan name…', 'kabel-blocks' ),
                                        allowedFormats: [ 'core/bold', 'core/italic' ],
                                        identifier: 'planName',
                                } ),
                                el(
                                        'div',
                                        { className: 'kbl3-price__amount' },
                                        el( RichText, {
                                                tagName: 'span',
                                                className: 'kbl3-price__num',
                                                value: a.price,
                                                onChange: ( v ) => setAttributes( { price: v } ),
                                                placeholder: '38',
                                                allowedFormats: [],
                                                identifier: 'price',
                                        } ),
                                        el( RichText, {
                                                tagName: 'span',
                                                className: 'kbl3-price__period',
                                                value: a.period,
                                                onChange: ( v ) => setAttributes( { period: v } ),
                                                placeholder: '/ 5L',
                                                allowedFormats: [],
                                                identifier: 'period',
                                        } )
                                ),
                                el(
                                        'ul',
                                        { className: 'kbl3-list kbl3-list--check kbl3-price__features' },
                                        features.map( ( feature, i ) => el( 'li', { key: i }, feature ) )
                                ),
                                el( RichText, {
                                        tagName: 'a',
                                        className: 'kbl3-btn kbl3-btn--gold kbl3-btn--s kbl3-price__btn',
                                        value: a.btnLabel,
                                        onChange: ( v ) => setAttributes( { btnLabel: v } ),
                                        placeholder: __( 'Button label…', 'kabel-blocks' ),
                                        allowedFormats: [ 'core/bold', 'core/italic' ],
                                        href: a.btnURL || undefined,
                                        identifier: 'btnLabel',
                                } ),
                                el( RichText, {
                                        tagName: 'p',
                                        className: 'kbl3-price__foot',
                                        value: a.footnote,
                                        onChange: ( v ) => setAttributes( { footnote: v } ),
                                        placeholder: __( 'Footnote (optional)…', 'kabel-blocks' ),
                                        allowedFormats: [ 'core/bold', 'core/italic' ],
                                        identifier: 'footnote',
                                } )
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
