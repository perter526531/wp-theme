/**
 * kbl/video — cover image + bronze play button. On click, assets/js/front.js
 * swaps the cover for the embedded player (YouTube / Vimeo / direct file).
 * Without JavaScript the cover simply stays in place.
 *
 * v4 (Stackable-style responsive layer, image pattern): the percent width
 * and the top / bottom margins are per-device attributes (width / widthT /
 * widthM, mt / mb with T / M variants) riding the per-instance <style>
 * tag. The width control is new in v4 — it never had a v3 inline emission,
 * so legacy content without a uid keeps the full-width .kbl3-video skin
 * and the exact v3 output. The sheet emits REAL properties (no --kbl-w
 * chain exists for the video yet): the width on the uid root (source order
 * beats the .kbl3-video width:100% skin), the margins likewise. The
 * Advanced tab gains the per-instance custom CSS box and the HTML anchor
 * (AdvancedPanelV2).
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useBlockProps, MediaUpload, MediaUploadCheck, BlockControls } from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton } from '@wordpress/components';
import { sanitizeClass, cx, toStyle, shadowValue, applyVisibility, applySpacing, spacingPairs, applyAnim, animDelayPairs } from '../../src/util.js';
import {
        KblInspector,
        TextRow,
        RangeRow,
        BorderPanel,
        SpacingPanel,
        VisibilityPanel,
        AnimPanel,
        Resp,
        useUid,
        uidClass,
        respStyleEl,
        v4Strip,
} from '../../src/kit.js';

/**
 * Provider bucket for the front-end player. PHP twin: kbl3_video_provider().
 */
export function videoProvider( url ) {
        if ( ! url ) {
                return 'file';
        }
        if ( /youtube\.com|youtu\.be/i.test( url ) ) {
                return 'youtube';
        }
        if ( /vimeo\.com/i.test( url ) ) {
                return 'vimeo';
        }
        return 'file';
}

/* Which attributes ride the per-instance <style> tag (respCss spec) — all
 * real properties on the uid root. PHP twin: the $spec array inside
 * kbl3_video(). */
export const RESP_SPEC = [
        { a: 'width', p: 'width', u: '%' },
        { a: 'mt', p: 'margin-top', u: 'px' },
        { a: 'mb', p: 'margin-bottom', u: 'px' },
];
/* Keys the legacy emission must drop once a uid exists. width never had a
 * v3 inline emission (the control is new in v4) — it is listed for parity
 * with the image block. PHP twin: kbl4_strip() inside kbl3_video(). */
export const STRIP_KEYS = [ 'width', 'widthT', 'widthM', 'mt', 'mtT', 'mtM', 'mb', 'mbT', 'mbM' ];

/** Shared by edit + save (imported by save.js). PHP twin: the $class cx()
 * inside kbl3_video(). Receives the v4-stripped copy (uid kept). */
export function videoClass( a ) {
        return cx( 'kbl3-video', uidClass( a ), ...applyAnim( a ), ...applyVisibility( a ), ...applySpacing( a ), sanitizeClass( a.customClass ) );
}

/** Shared by edit + save (imported by save.js). PHP twin: the $style
 * kbl3_style() call inside kbl3_video(). Receives the v4-stripped copy. */
export function videoStyle( a ) {
        return toStyle( [
                [ 'border-radius', a.radius ? `${ a.radius }px` : null ],
                [ 'box-shadow', shadowValue( a ) ],
                ...animDelayPairs( a ),
                ...spacingPairs( a ),
        ] );
}

export function Edit( { attributes, setAttributes, clientId, isSelected } ) {
                const a = attributes;

                useUid( attributes, setAttributes, clientId, isSelected );
                const legacy = v4Strip( attributes, STRIP_KEYS );

                const className = videoClass( legacy );
                const style = videoStyle( legacy );
                const data = JSON.stringify( { provider: videoProvider( a.url ), src: a.url || '' } );

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
                                                                setAttributes( { coverId: media.id, coverURL: media.url } ),
                                                        allowedTypes: [ 'image' ],
                                                        value: a.coverId,
                                                        render: ( { open } ) =>
                                                                el( ToolbarButton, {
                                                                        icon: 'format-image',
                                                                        title: a.coverURL
                                                                                ? __( 'Replace cover image', 'kabel-blocks' )
                                                                                : __( 'Add cover image', 'kabel-blocks' ),
                                                                        onClick: open,
                                                                } ),
                                                } )
                                        ),
                                        a.coverURL
                                                ? el( ToolbarButton, {
                                                                icon: 'no',
                                                                title: __( 'Remove cover image', 'kabel-blocks' ),
                                                                onClick: () =>
                                                                        setAttributes( { coverId: undefined, coverURL: undefined } ),
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
                                                el( TextRow, {
                                                        label: __( 'Video link', 'kabel-blocks' ),
                                                        value: a.url,
                                                        onChange: ( v ) => setAttributes( { url: v } ),
                                                        placeholder: 'https://www.youtube.com/watch?v=…',
                                                        help: __(
                                                                'Paste a YouTube or Vimeo link, or a direct .mp4 file link.',
                                                                'kabel-blocks'
                                                        ),
                                                } ),
                                                el( Resp, { prop: 'width', attrs: attributes, setAttrs: setAttributes },
                                                        el( RangeRow, {
                                                                label: __( 'Width (%)', 'kabel-blocks' ),
                                                                min: 10,
                                                                max: 100,
                                                                unit: '%',
                                                        } ) )
                                        ),
                                        style: el(
                                                'div',
                                                null,
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
                                'div',
                                {
                                        ...useBlockProps( { className, style, id: a.anchor || undefined } ),
                                        'data-kbl-video': data,
                                },
                                el(
                                        'button',
                                        {
                                                        className: 'kbl3-video__cover',
                                                        type: 'button',
                                                        'aria-label': __( 'Play video', 'kabel-blocks' ),
                                                        onClick: ( e ) => e.preventDefault(),
                                        },
                                        a.coverURL
                                                ? el( MediaUpload, {
                                                                onSelect: ( media ) =>
                                                                        setAttributes( { coverId: media.id, coverURL: media.url } ),
                                                                allowedTypes: [ 'image' ],
                                                                value: a.coverId,
                                                                render: ( { open } ) =>
                                                                        el( 'img', {
                                                                                        className: 'kbl3-video__img',
                                                                                        src: a.coverURL,
                                                                                        alt: '',
                                                                                        onClick: open,
                                                                        } ),
                                                  } )
                                                : null,
                                        el(
                                                        'span',
                                                        { className: 'kbl3-video__play' },
                                                        el(
                                                                        'svg',
                                                                        { viewBox: '0 0 24 24', fill: 'currentColor', 'aria-hidden': true },
                                                                        el( 'path', { d: 'M8 5.5v13l11-6.5z' } )
                                                        )
                                        )
                                )
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
