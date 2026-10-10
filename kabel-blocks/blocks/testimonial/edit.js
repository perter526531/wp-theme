/**
 * kbl/testimonial — one customer review: bronze stars, italic quote and an
 * avatar line. Everything is inline-editable.
 *
 * v4 (Stackable-style responsive layer, icon-box structure): the quote font
 * size and the top / bottom margins are per-device attributes (fontSize /
 * fontSizeT / fontSizeM, mt / mb with T / M variants) riding the
 * per-instance <style> tag. The testimonial has no --kbl-* consumption
 * chains in the shared stylesheet yet, so the sheet emits REAL properties:
 * the font size on the .kbl3-testi__quote descendant (specificity 0,2,0
 * beats the --kbl3-fs chain and its 1080/781 media zones, so unset
 * overrides follow the desktop value), the margins on the uid root (source
 * order beats the .kbl3-testi skin). The Advanced tab gains the
 * per-instance custom CSS box and the HTML anchor (AdvancedPanelV2).
 * Legacy content without a uid keeps the exact v3 output.
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { useBlockProps, RichText, BlockControls, MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton } from '@wordpress/components';
import { sanitizeClass, cx, toStyle, iconSvg, applyVisibility, applySpacing, spacingPairs, applyAnim, animDelayPairs, typographyPairs } from '../../src/util.js';
import {
        KblInspector,
        RangeRow,
        ColorsPanel,
        AlignButtons,
        TypographyPanel,
        SpacingPanel,
        VisibilityPanel,
        AnimPanel,
        Resp,
        useUid,
        uidClass,
        respStyleEl,
        v4Strip,
} from '../../src/kit.js';

/** 0-5 whole stars. PHP twin: kbl3_rating(). */
export function clampRating( value ) {
        return Math.max( 0, Math.min( 5, Math.round( Number( value ?? 5 ) ) ) );
}

/** Star icon nodes (spread as children — no list keys needed for markup). */
export function starNodes( rating ) {
        const out = [];
        for ( let i = 0; i < rating; i++ ) {
                out.push( iconSvg( 'star' ) );
        }
        return out;
}

/* Which attributes ride the per-instance <style> tag (respCss spec). The
 * font-size rule targets the quote descendant (sel), the margins the root.
 * PHP twin: the $spec array inside kbl3_testimonial(). */
export const RESP_SPEC = [
        { a: 'fontSize', p: 'font-size', u: 'px', sel: ' .kbl3-testi__quote' },
        { a: 'mt', p: 'margin-top', u: 'px' },
        { a: 'mb', p: 'margin-bottom', u: 'px' },
];
/* Keys the legacy emission must drop once a uid exists (the inline
 * --kbl3-fs / --kbl3-fs-t / --kbl3-fs-m variables would beat every
 * media-query override). PHP twin: kbl4_strip() inside kbl3_testimonial(). */
export const STRIP_KEYS = [ 'fontSize', 'fontSizeT', 'fontSizeM', 'mt', 'mtT', 'mtM', 'mb', 'mbT', 'mbM' ];

/** Shared by edit + save (imported by save.js). PHP twin: the $class cx()
 * inside kbl3_testimonial(). Receives the v4-stripped copy (uid kept). */
export function testiClass( a ) {
        return cx(
                'kbl3-testi',
                a.align === 'center' && 'is-center',
                a.align === 'right' && 'is-right',
                uidClass( a ),
                ...applyAnim( a ),
                ...applyVisibility( a ),
                ...applySpacing( a ),
                sanitizeClass( a.customClass )
        );
}

/** Shared by edit + save (imported by save.js). PHP twin:
 * kbl3_testimonial_style(). Receives the v4-stripped copy. */
export function testiStyle( a ) {
        return toStyle( [
                [ 'color', a.color ],
                ...typographyPairs( a, { fw: false, lh: false, ls: false, tt: false } ),
                ...animDelayPairs( a ),
                ...spacingPairs( a ),
        ] );
}

export function Edit( { attributes, setAttributes, clientId, isSelected } ) {
                const a = attributes;
                const rating = clampRating( a.rating );

                useUid( attributes, setAttributes, clientId, isSelected );
                const legacy = v4Strip( attributes, STRIP_KEYS );

                const className = testiClass( legacy );
                const style = testiStyle( legacy );

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
                                        attrs: attributes,
                                        setAttrs: setAttributes,
                                        v4: true,
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
                                                        v4: true,
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
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
