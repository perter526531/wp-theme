/**
 * kbl/image — image with caption, pixel/percent width, aspect ratio,
 * radius, optional link. On-canvas drag-to-resize (core image pattern).
 */
import { el, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { register } from '../register.js';
import {
        useBlockProps,
        RichText,
        MediaUpload,
        MediaUploadCheck,
        MediaPlaceholder,
        BlockControls,
        AlignmentToolbar,
} from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton, ResizableBox } from '@wordpress/components';
import { cx, toStyle, applyVisibility, applySpacing, spacingPairs, applyAnim, animDelayPairs } from '../util.js';
import {
        KblInspector,
        RangeRow,
        SegButtons,
        AlignButtons,
        TextRow,
        SpacingPanel,
        VisibilityPanel,
        AnimPanel,
} from '../inspector.js';

const RATIO_OPTIONS = [
        { value: '', label: __( 'Original', 'kabel-blocks' ) },
        { value: '1-1', label: '1:1' },
        { value: '4-3', label: '4:3' },
        { value: '3-4', label: '3:4' },
        { value: '16-9', label: '16:9' },
        { value: '9-16', label: '9:16' },
        { value: 'custom', label: __( 'Custom', 'kabel-blocks' ) },
];

/** Shared by edit + save. PHP twin: kbl3_image() img pairs. */
function ratioPairs( a ) {
        if ( 'custom' === a.ratio ) {
                return [
                        [ 'width', a.widthPx ? `${ a.widthPx }px` : null ],
                        [ 'height', a.heightPx ? `${ a.heightPx }px` : null ],
                ];
        }
        if ( a.ratio ) {
                return [
                        [ 'aspect-ratio', String( a.ratio ).replace( '-', ' / ' ) ],
                        [ 'object-fit', 'cover' ],
                        [ 'width', '100%' ],
                ];
        }
        return [];
}

register( 'kbl/image', {
        apiVersion: 3,
        title: __( 'Image (Kabel)', 'kabel-blocks' ),
        description: __(
                'Image with drag-to-resize width, aspect ratio, corner radius and optional caption + link.',
                'kabel-blocks'
        ),
        category: 'kabel',
        keywords: [ 'kabel', 'image', 'photo', '图片', '图像', '调整大小' ],
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
                id: { type: 'number' },
                url: {
                        type: 'string',
                        source: 'attribute',
                        selector: 'img',
                        attribute: 'src',
                },
                alt: {
                        type: 'string',
                        source: 'attribute',
                        selector: 'img',
                        attribute: 'alt',
                        default: '',
                },
                caption: {
                        type: 'string',
                        source: 'html',
                        selector: 'figcaption',
                },
                width: { type: 'number' },
                widthPx: { type: 'number' },
                heightPx: { type: 'number' },
                ratio: { type: 'string' },
                radius: { type: 'number' },
                align: { type: 'string' },
                linkURL: { type: 'string' },
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

        edit( { attributes, setAttributes, isSelected } ) {
                const { anchor, id, url, alt, caption, width, widthPx, heightPx, ratio, radius, align, linkURL } = attributes;
                const [ tmpWidth, setTmpWidth ] = useState( null );
                const boxWidth = tmpWidth != null ? tmpWidth : widthPx;

                const figureStyle = toStyle( [
                        [ 'width', widthPx ? `${ widthPx }px` : ( width ? `${ width }%` : null ) ],
                        [ 'margin-left', align === 'center' || align === 'right' ? 'auto' : null ],
                        [ 'margin-right', align === 'center' ? 'auto' : null ],
                        ...animDelayPairs( attributes ),
                        ...spacingPairs( attributes ),
                ] );
                const imgStyle = toStyle( [
                        [ 'border-radius', radius ? `${ radius }px` : null ],
                        /* Editor-only fill so the img tracks the resize box;
                         * ratio pairs already carry their own width rule. */
                        ...( boxWidth && ! ratio ? [ [ 'width', '100%' ] ] : [] ),
                        ...ratioPairs( attributes ),
                ] );

                const inspector = el(
                        KblInspector,
                        {
                                layout: el(
                                        'div',
                                        null,
                                        el( RangeRow, {
                                                label: __( 'Width (px)', 'kabel-blocks' ),
                                                value: widthPx,
                                                onChange: ( v ) => setAttributes( { widthPx: v == null ? undefined : v } ),
                                                min: 40,
                                                max: 1920,
                                                step: 5,
                                                help: __( 'Drag the handles on the canvas, or set an exact width here. Wins over the % width while set.', 'kabel-blocks' ),
                                        } ),
                                        el( RangeRow, {
                                                label: __( 'Width (%)', 'kabel-blocks' ),
                                                value: width,
                                                onChange: ( v ) => setAttributes( { width: v } ),
                                                min: 10,
                                                max: 100,
                                                unit: '%',
                                                help: widthPx ?
                                                        __( 'Applies only after the px width above is reset.', 'kabel-blocks' ) :
                                                        undefined,
                                        } ),
                                        el( SegButtons, {
                                                label: __( 'Aspect ratio', 'kabel-blocks' ),
                                                value: ratio || '',
                                                onChange: ( v ) => setAttributes( { ratio: v || undefined } ),
                                                options: RATIO_OPTIONS,
                                        } ),
                                        'custom' === ratio ?
                                                el( RangeRow, {
                                                        label: __( 'Height (px)', 'kabel-blocks' ),
                                                        value: heightPx,
                                                        onChange: ( v ) => setAttributes( { heightPx: v == null ? undefined : v } ),
                                                        min: 40,
                                                        max: 2160,
                                                        step: 5,
                                                        help: __( 'Custom size = the px width above plus this height.', 'kabel-blocks' ),
                                                } ) :
                                                null,
                                        el( AlignButtons, {
                                                value: align,
                                                onChange: ( v ) => setAttributes( { align: v } ),
                                        } )
                                ),
                                style: el( 'div', null,
                                        el( RangeRow, {
                                                label: __( 'Corner radius', 'kabel-blocks' ),
                                                value: radius,
                                                onChange: ( v ) => setAttributes( { radius: v } ),
                                                min: 0,
                                                max: 40,
                                        } ),
                                        el( AnimPanel, {
                                                attrs: attributes,
                                                setAttrs: setAttributes,
                                        } )
                                ),
                                advanced: el( 'div', null,
                                        el( TextRow, {
                                                label: __( 'Alt text', 'kabel-blocks' ),
                                                value: alt,
                                                onChange: ( v ) => setAttributes( { alt: v } ),
                                        } ),
                                        el( TextRow, {
                                                label: __( 'Link (optional)', 'kabel-blocks' ),
                                                value: linkURL,
                                                onChange: ( v ) => setAttributes( { linkURL: v } ),
                                                help: __( 'Opens the image as a link.', 'kabel-blocks' ),
                                        } ),
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
                );

                const controls = url
                        ? el(
                                BlockControls,
                                null,
                                el( AlignmentToolbar, {
                                        value: align,
                                        onChange: ( v ) => setAttributes( { align: v } ),
                                } ),
                                el(
                                        ToolbarGroup,
                                        null,
                                        el( MediaUploadCheck, null,
                                                el( MediaUpload, {
                                                        onSelect: ( media ) =>
                                                                setAttributes( { url: media.url, id: media.id, alt: media.alt || '' } ),
                                                        allowedTypes: [ 'image' ],
                                                        value: id,
                                                        render: ( { open } ) =>
                                                                el( ToolbarButton, {
                                                                        icon: 'edit',
                                                                        title: __( 'Replace image', 'kabel-blocks' ),
                                                                        onClick: open,
                                                                } ),
                                                } )
                                        )
                                )
                        )
                        : null;

                if ( ! url ) {
                        return el(
                                'div',
                                { ...useBlockProps() },
                                inspector,
                                controls,
                                el( MediaPlaceholder, {
                                        icon: 'format-image',
                                        label: __( 'Image', 'kabel-blocks' ),
                                        instructions: __( 'Upload or pick an image from the library.', 'kabel-blocks' ),
                                        onSelect: ( media ) =>
                                                setAttributes( { url: media.url, id: media.id, alt: media.alt || '' } ),
                                        accept: 'image/*',
                                        allowedTypes: [ 'image' ],
                                } )
                        );
                }

                /* On-canvas resize: core-image pattern — side handles, tmp
                 * state during the drag, attribute commit on release. */
                const img = el( 'img', {
                        className: 'kbl3-img',
                        src: url,
                        alt: alt || '',
                        style: imgStyle,
                } );

                const resizable = el(
                        ResizableBox,
                        {
                                className: 'kbl3-fig__resize',
                                size: boxWidth ? { width: boxWidth } : undefined,
                                showHandle: isSelected,
                                minWidth: 40,
                                maxWidth: 1920,
                                enable: {
                                        left: align === 'center' || align === 'right',
                                        right: true,
                                },
                                onResize: ( event, direction, elt ) => {
                                        const w = Math.round( elt.offsetWidth );
                                        if ( w >= 40 ) {
                                                setTmpWidth( Math.min( 1920, w ) );
                                        }
                                },
                                onResizeStop: () => {
                                        if ( tmpWidth != null && tmpWidth >= 40 ) {
                                                setAttributes( { widthPx: tmpWidth, width: undefined } );
                                        }
                                        setTmpWidth( null );
                                },
                        },
                        img
                );

                return el(
                        'figure',
                        useBlockProps( {
                                className: cx( 'kbl3-fig', widthPx ? 'kbl3-fig--px' : '', ...applyAnim( attributes ), ...applyVisibility( attributes ), ...applySpacing( attributes ) ),
                                style: figureStyle,
                                id: anchor || undefined,
                        } ),
                        inspector,
                        controls,
                        linkURL ? el( 'a', { className: 'kbl3-fig__link', href: linkURL }, resizable ) : resizable,
                        ( caption || isSelected ) ?
                                el( RichText, {
                                        tagName: 'figcaption',
                                        className: 'kbl3-figcap',
                                        value: caption,
                                        onChange: ( v ) => setAttributes( { caption: v } ),
                                        placeholder: __( 'Write a caption…', 'kabel-blocks' ),
                                        identifier: 'caption',
                                } ) :
                                null
                );
        },

        save( { attributes } ) {
                const { anchor, url, alt, caption, width, widthPx, radius, align, linkURL } = attributes;

                const figureStyle = toStyle( [
                        [ 'width', widthPx ? `${ widthPx }px` : ( width ? `${ width }%` : null ) ],
                        [ 'margin-left', align === 'center' || align === 'right' ? 'auto' : null ],
                        [ 'margin-right', align === 'center' ? 'auto' : null ],
                        ...animDelayPairs( attributes ),
                        ...spacingPairs( attributes ),
                ] );
                const imgStyle = toStyle( [
                        [ 'border-radius', radius ? `${ radius }px` : null ],
                        ...ratioPairs( attributes ),
                ] );

                const img = el( 'img', {
                        className: 'kbl3-img',
                        src: url,
                        alt: alt || '',
                        style: imgStyle,
                } );

                return el(
                        'figure',
                        useBlockProps.save( {
                                className: cx( 'kbl3-fig', widthPx ? 'kbl3-fig--px' : '', ...applyAnim( attributes ), ...applyVisibility( attributes ), ...applySpacing( attributes ) ),
                                style: figureStyle,
                                id: anchor || undefined,
                        } ),
                        linkURL ? el( 'a', { className: 'kbl3-fig__link', href: linkURL }, img ) : img,
                        caption ?
                                el( RichText.Content, {
                                        tagName: 'figcaption',
                                        className: 'kbl3-figcap',
                                        value: caption,
                                } ) :
                                null
                );
        },
} );
