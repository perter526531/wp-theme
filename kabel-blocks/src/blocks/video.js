/**
 * kbl/video — cover image + bronze play button. On click, assets/js/front.js
 * swaps the cover for the embedded player (YouTube / Vimeo / direct file).
 * Without JavaScript the cover simply stays in place.
 */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { register } from '../register.js';
import { useBlockProps, MediaUpload, MediaUploadCheck, BlockControls } from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton } from '@wordpress/components';
import { cx, toStyle, SHADOWS, applyVisibility, applySpacing, spacingPairs, applyAnim, animDelayPairs } from '../util.js';
import {
        KblInspector,
        TextRow,
        BorderPanel,
        SpacingPanel,
        VisibilityPanel,
        AnimPanel,
} from '../inspector.js';

register( 'kbl/video', {
        apiVersion: 3,
        title: __( 'Video (Kabel)', 'kabel-blocks' ),
        description: __(
                'Cover image with a bronze play button — plays YouTube, Vimeo or MP4 links on click.',
                'kabel-blocks'
        ),
        category: 'kabel',
        keywords: [ 'kabel', 'video', 'youtube', 'vimeo', '视频', '视频', '播放'],
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
                url: { type: 'string' },
                coverId: { type: 'number' },
                coverURL: { type: 'string' },
                radius: { type: 'number' },
                shadow: { type: 'string' },
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

                const className = videoClass( a );
                const style = videoStyle( a );
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
                                                } )
                                        ),
                                        style: el(
                                                'div',
                                                null,
                                                el( BorderPanel, {
                                                        radius: a.radius,
                                                        onRadius: ( v ) => setAttributes( { radius: v } ),
                                                        shadow: a.shadow,
                                                        onShadow: ( v ) => setAttributes( { shadow: v } ),
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
                        )
                );
        },

        save( { attributes } ) {
                const a = attributes;

                const data = JSON.stringify( { provider: videoProvider( a.url ), src: a.url || '' } );

                return el(
                        'div',
                        {
                                ...useBlockProps.save( { className: videoClass( a ), style: videoStyle( a ), id: a.anchor || undefined } ),
                                'data-kbl-video': data,
                        },
                        el(
                                'button',
                                {
                                                className: 'kbl3-video__cover',
                                                type: 'button',
                                                'aria-label': __( 'Play video', 'kabel-blocks' ),
                                },
                                a.coverURL
                                        ? el( 'img', { className: 'kbl3-video__img', src: a.coverURL, alt: '' } )
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
                );
        },
} );

/**
 * Provider bucket for the front-end player. PHP twin: kbl3_video_provider().
 */
function videoProvider( url ) {
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

/** Shared by edit + save. PHP twin: kbl3_video_class(). */
function videoClass( a ) {
        return cx( 'kbl3-video', ...applyAnim( a ), ...applyVisibility( a ), ...applySpacing( a ) );
}

/** Shared by edit + save. PHP twin: kbl3_video_style(). */
function videoStyle( a ) {
        return toStyle( [
                [ 'border-radius', a.radius ? `${ a.radius }px` : null ],
                [ 'box-shadow', a.shadow ? SHADOWS[ a.shadow ] || null : null ],
                ...animDelayPairs( a ),
                ...spacingPairs( a ),
        ] );
}
