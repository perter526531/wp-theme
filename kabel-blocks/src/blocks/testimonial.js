/**
 * kbl/testimonial — one customer review: bronze stars, italic quote and an
 * avatar line. Everything is inline-editable.
 */
import { el, Fr } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { register } from '../register.js';
import { useBlockProps, RichText, BlockControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton } from '@wordpress/components';
import { cx, toStyle, iconSvg, applyVisibility, applySpacing, spacingPairs, applyAnim, animDelayPairs, typographyPairs } from '../util.js';
import {
        KblInspector,
        RangeRow,
        ColorsPanel,
        AlignButtons,
        TypographyPanel,
        SpacingPanel,
        VisibilityPanel,
        AnimPanel,
} from '../inspector.js';

register( 'kbl/testimonial', {
        apiVersion: 3,
        title: __( 'Testimonial (Kabel)', 'kabel-blocks' ),
        description: __(
                'Customer review with bronze stars, an italic quote and an avatar line.',
                'kabel-blocks'
        ),
        category: 'kabel',
        keywords: [ 'kabel', 'testimonial', 'review', 'quote', '评价', '客户评价', '客户评价', '评价', '感言'],
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
                quote: {
                        type: 'string',
                        source: 'html',
                        selector: '.kbl3-testi__quote',
                        default:
                                'The matte finish is exactly what we hoped for — two years on, the walls still look freshly painted.',
                },
                name: {
                        type: 'string',
                        source: 'html',
                        selector: '.kbl3-testi__name',
                        default: 'Daniel Whitmore',
                },
                role: {
                        type: 'string',
                        source: 'html',
                        selector: '.kbl3-testi__role',
                        default: 'Homeowner, Brighton',
                },
                avatarId: { type: 'number' },
                avatarURL: { type: 'string' },
                rating: { type: 'number', default: 5 },
                align: { type: 'string', default: 'center' },
                color: { type: 'string' },
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
                const rating = clampRating( a.rating );

                const className = testiClass( a );
                const style = testiStyle( a );

                return el(
                        Fr,
                        null,
                        el(
                                BlockControls,
                                null,
                                el(
                                        ToolbarGroup,
                                null,
                                el(
                                        MediaUploadCheck,
                                        null,
                                        el( MediaUpload, {
                                                onSelect: ( media ) =>
                                                        setAttributes( { avatarId: media.id, avatarURL: media.url } ),
                                                allowedTypes: [ 'image' ],
                                                value: a.avatarId,
                                                render: ( { open } ) =>
                                                        el( ToolbarButton, {
                                                                icon: 'format-image',
                                                                title: a.avatarURL
                                                                        ? __( 'Replace avatar', 'kabel-blocks' )
                                                                        : __( 'Add avatar', 'kabel-blocks' ),
                                                                onClick: open,
                                                        } ),
                                        } )
                                ),
                                a.avatarURL
                                        ? el( ToolbarButton, {
                                                        icon: 'no',
                                                        title: __( 'Remove avatar', 'kabel-blocks' ),
                                                        onClick: () =>
                                                                setAttributes( { avatarId: undefined, avatarURL: undefined } ),
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
                                                el( RangeRow, {
                                                        label: __( 'Star rating', 'kabel-blocks' ),
                                                        value: rating,
                                                        onChange: ( v ) => setAttributes( { rating: v == null ? 5 : v } ),
                                                        min: 0,
                                                        max: 5,
                                                } ),
                                                el( AlignButtons, {
                                                        label: __( 'Alignment', 'kabel-blocks' ),
                                                        value: a.align,
                                                        onChange: ( v ) => setAttributes( { align: v || 'center' } ),
                                                } )
                                        ),
                                        style: el(
                                                'div',
                                                null,
                                                el( ColorsPanel, {
                                                        colors: [
                                                                {
                                                                        label: __( 'Quote color', 'kabel-blocks' ),
                                                                        value: a.color,
                                                                        onChange: ( v ) => setAttributes( { color: v } ),
                                                                        isText: true,
                                                                },
                                                        ],
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
                                        advanced: el( 'div', null,
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
                                'figure',
                                useBlockProps( { className, style, id: a.anchor || undefined } ),
                                rating > 0
                                        ? el(
                                                        'div',
                                                        {
                                                                        className: 'kbl3-testi__stars',
                                                                        'aria-label': sprintf(
                                                                                        /* translators: %d: star rating (0-5). */
                                                                                        __( 'Rated %d out of 5', 'kabel-blocks' ),
                                                                                        rating
                                                                        ),
                                                        },
                                                        ...starNodes( rating )
                                          )
                                        : null,
                                el( RichText, {
                                        tagName: 'blockquote',
                                        className: 'kbl3-testi__quote',
                                        value: a.quote,
                                        onChange: ( v ) => setAttributes( { quote: v } ),
                                        placeholder: __( 'What did the customer say…', 'kabel-blocks' ),
                                        allowedFormats: [ 'core/bold', 'core/italic' ],
                                        identifier: 'quote',
                                } ),
                                el(
                                        'figcaption',
                                        { className: 'kbl3-testi__who' },
                                        a.avatarURL
                                                ? el( MediaUpload, {
                                                                onSelect: ( media ) =>
                                                                        setAttributes( { avatarId: media.id, avatarURL: media.url } ),
                                                                allowedTypes: [ 'image' ],
                                                                value: a.avatarId,
                                                                render: () =>
                                                                        el( 'img', {
                                                                                className: 'kbl3-testi__avatar',
                                                                                src: a.avatarURL,
                                                                                alt: '',
                                                                        } ),
                                                  } )
                                                : null,
                                        el( RichText, {
                                                tagName: 'span',
                                                className: 'kbl3-testi__name',
                                                value: a.name,
                                                onChange: ( v ) => setAttributes( { name: v } ),
                                                placeholder: __( 'Customer name…', 'kabel-blocks' ),
                                                allowedFormats: [ 'core/bold', 'core/italic' ],
                                                identifier: 'name',
                                        } ),
                                        el( RichText, {
                                                tagName: 'span',
                                                className: 'kbl3-testi__role',
                                                value: a.role,
                                                onChange: ( v ) => setAttributes( { role: v } ),
                                                placeholder: __( 'Role or city…', 'kabel-blocks' ),
                                                allowedFormats: [ 'core/bold', 'core/italic' ],
                                                identifier: 'role',
                                        } )
                                )
                        )
                );
        },

        save( { attributes } ) {
                const a = attributes;
                const rating = clampRating( a.rating );

                return el(
                        'figure',
                        useBlockProps.save( { className: testiClass( a ), style: testiStyle( a ), id: a.anchor || undefined } ),
                        rating > 0
                                ? el(
                                                'div',
                                                {
                                                                className: 'kbl3-testi__stars',
                                                                'aria-label': sprintf(
                                                                                /* translators: %d: star rating (0-5). */
                                                                                __( 'Rated %d out of 5', 'kabel-blocks' ),
                                                                                rating
                                                                ),
                                                },
                                                ...starNodes( rating )
                                  )
                                : null,
                        el( RichText.Content, {
                                tagName: 'blockquote',
                                className: 'kbl3-testi__quote',
                                value: a.quote,
                        } ),
                        el(
                                'figcaption',
                                { className: 'kbl3-testi__who' },
                                a.avatarURL
                                        ? el( 'img', { className: 'kbl3-testi__avatar', src: a.avatarURL, alt: '' } )
                                        : null,
                                el( RichText.Content, {
                                        tagName: 'span',
                                        className: 'kbl3-testi__name',
                                        value: a.name,
                                } ),
                                el( RichText.Content, {
                                        tagName: 'span',
                                        className: 'kbl3-testi__role',
                                        value: a.role,
                                } )
                        )
                );
        },
} );

/** 0-5 whole stars. PHP twin: kbl3_rating(). */
function clampRating( value ) {
        return Math.max( 0, Math.min( 5, Math.round( Number( value ?? 5 ) ) ) );
}

/** Star icon nodes (spread as children — no list keys needed for markup). */
function starNodes( rating ) {
        const out = [];
        for ( let i = 0; i < rating; i++ ) {
                out.push( iconSvg( 'star' ) );
        }
        return out;
}

/** Shared by edit + save. PHP twin: kbl3_testimonial_class(). */
function testiClass( a ) {
        return cx(
                'kbl3-testi',
                a.align === 'center' && 'is-center',
                a.align === 'right' && 'is-right',
                ...applyAnim( a ),
                ...applyVisibility( a ),
                ...applySpacing( a )
        );
}

/** Shared by edit + save. PHP twin: kbl3_testimonial_style(). */
function testiStyle( a ) {
        return toStyle( [
                [ 'color', a.color ],
                ...typographyPairs( a, { fw: false, lh: false, ls: false, tt: false } ),
                ...animDelayPairs( a ),
                ...spacingPairs( a ),
        ] );
}
