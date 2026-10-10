/**
 * kbl/pricing — one service/package price card: plan name, big bronze price,
 * check-marked feature list and a CTA. Optional "featured" treatment draws
 * a bronze outline and a badge.
 */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { register } from '../register.js';
import { useBlockProps, RichText, BlockControls } from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton, PanelBody, Button } from '@wordpress/components';
import { cx, toStyle, SHADOWS, applyVisibility, applySpacing, spacingPairs, applyAnim, animDelayPairs, typographyPairs } from '../util.js';
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
} from '../inspector.js';

const DEFAULT_FEATURES = [
        'Color consultation included',
        'Two-coat premium application',
        '5-year finish warranty',
        'Free touch-up kit',
];

register( 'kbl/pricing', {
        apiVersion: 3,
        title: __( 'Pricing (Kabel)', 'kabel-blocks' ),
        description: __(
                'Package price card with a big number, check-marked features and a CTA button.',
                'kabel-blocks'
        ),
        category: 'kabel',
        keywords: [ 'kabel', 'pricing', 'price', 'plan', '价格', '套餐', '定价', '价格', '套餐'],
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
                planName: {
                        type: 'string',
                        source: 'html',
                        selector: '.kbl3-price__plan',
                        default: 'Signature Package',
                },
                price: {
                        type: 'string',
                        source: 'html',
                        selector: '.kbl3-price__num',
                        default: '38',
                },
                period: {
                        type: 'string',
                        source: 'html',
                        selector: '.kbl3-price__period',
                        default: '/ 5L',
                },
                features: { type: 'array', default: DEFAULT_FEATURES },
                btnLabel: {
                        type: 'string',
                        source: 'html',
                        selector: 'a',
                        default: 'Request this package',
                },
                btnURL: { type: 'string' },
                featured: { type: 'boolean' },
                footnote: {
                        type: 'string',
                        source: 'html',
                        selector: '.kbl3-price__foot',
                },
                bgColor: { type: 'string' },
                radius: { type: 'number' },
                shadow: { type: 'string' },
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
                const features = a.features && a.features.length ? a.features : [];

                const className = priceClass( a );
                const style = priceStyle( a );

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
                                                } ),
                                                el( TypographyPanel, {
                                                        attrs: a,
                                                        setAttrs: setAttributes,
                                                        include: { fw: false, lh: false, ls: false, tt: false },
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
                        )
                );
        },

        save( { attributes } ) {
                const a = attributes;
                const features = a.features || [];

                return el(
                        'div',
                        useBlockProps.save( { className: priceClass( a ), style: priceStyle( a ), id: a.anchor || undefined } ),
                        a.featured
                                ? el( 'span', { className: 'kbl3-price__badge' }, __( 'Most popular', 'kabel-blocks' ) )
                                : null,
                        el( RichText.Content, {
                                tagName: 'h3',
                                className: 'kbl3-price__plan',
                                value: a.planName,
                        } ),
                        el(
                                'div',
                                { className: 'kbl3-price__amount' },
                                el( RichText.Content, {
                                        tagName: 'span',
                                        className: 'kbl3-price__num',
                                        value: a.price,
                                } ),
                                el( RichText.Content, {
                                        tagName: 'span',
                                        className: 'kbl3-price__period',
                                        value: a.period,
                                } )
                        ),
                        el(
                                'ul',
                                { className: 'kbl3-list kbl3-list--check kbl3-price__features' },
                                features.map( ( feature, i ) => el( 'li', { key: i }, feature ) )
                        ),
                        a.btnLabel
                                ? el(
                                                'a',
                                                {
                                                                className: 'kbl3-btn kbl3-btn--gold kbl3-btn--s kbl3-price__btn',
                                                                ...( a.btnURL ? { href: a.btnURL } : {} ),
                                                },
                                                el( RichText.Content, { value: a.btnLabel } )
                                  )
                                : null,
                        el( RichText.Content, {
                                tagName: 'p',
                                className: 'kbl3-price__foot',
                                value: a.footnote,
                        } )
                );
        },
} );

/** Shared by edit + save. PHP twin: kbl3_price_class(). */
function priceClass( a ) {
        return cx(
                'kbl3-price',
                a.featured && 'is-featured',
                ...applyAnim( a ),
                ...applyVisibility( a ),
                ...applySpacing( a )
        );
}

/** Shared by edit + save. PHP twin: kbl3_price_style(). */
function priceStyle( a ) {
        return toStyle( [
                [ 'background-color', a.bgColor ],
                [ 'border-radius', a.radius ? `${ a.radius }px` : null ],
                [ 'box-shadow', a.shadow ? SHADOWS[ a.shadow ] || null : null ],
                ...typographyPairs( a, { fw: false, lh: false, ls: false, tt: false } ),
                ...animDelayPairs( a ),
                ...spacingPairs( a ),
        ] );
}
