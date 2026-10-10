/**
 * kbl/image — image with caption, width (%/px), aspect-ratio presets,
 * radius and optional link.
 *
 * v4 (Stackable-style responsive layer): the percent width and the top /
 * bottom margins are per-device attributes (width / widthT / widthM, mt / mb
 * with T / M variants) riding the per-instance <style> tag (--kbl-w /
 * --kbl-mt / --kbl-mb on the figure). The pixel width (widthPx) keeps its
 * inline slot — a custom pixel width still wins over the percent chain, in
 * the editor and on the front end alike. The Advanced tab gains the
 * per-instance custom CSS box and the HTML anchor (AdvancedPanelV2).
 * Legacy content without a uid keeps the exact v3 output.
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
        useBlockProps,
        RichText,
        MediaUpload,
        MediaUploadCheck,
        MediaPlaceholder,
        BlockControls,
        AlignmentToolbar,
} from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton } from '@wordpress/components';
import { sanitizeClass, cx, toStyle, applyVisibility, applySpacing, spacingPairs, applyAnim, animDelayPairs } from '../../src/util.js';
import {
        KblInspector,
        RangeRow,
        SelectRow,
        AlignButtons,
        TextRow,
        SpacingPanel,
        VisibilityPanel,
        AnimPanel,
        Resp,
        useUid,
        uidClass,
        respStyleEl,
        v4Strip,
} from '../../src/kit.js';

/* Which attributes ride the per-instance <style> tag (respCss spec). */
export const RESP_SPEC = [
        { a: 'width', p: '--kbl-w', u: '%' },
        { a: 'mt', p: '--kbl-mt', u: 'px' },
        { a: 'mb', p: '--kbl-mb', u: 'px' },
];
/* Keys the legacy emission must drop once a uid exists. widthPx is NOT
 * stripped — the custom pixel width keeps its inline slot. PHP twin:
 * kbl4_strip() inside kbl3_image(). */
export const STRIP_KEYS = [ 'width', 'widthT', 'widthM', 'mt', 'mtT', 'mtM', 'mb', 'mbT', 'mbM' ];

export function Edit( { attributes, setAttributes, clientId, isSelected } ) {
                const { anchor, id, url, alt, caption, width, widthPx, heightPx, ratio, radius, align, linkURL } = attributes;

                useUid( attributes, setAttributes, clientId, isSelected );
                const legacy = v4Strip( attributes, STRIP_KEYS );

                const figureStyle = toStyle( [
                        /* v4: widthPx keeps its inline slot (a custom pixel width
                         * still wins); the percent width rides the sheet. */
                        [ 'width', widthPx ? `${ widthPx }px` : ( attributes.uid ? null : ( width ? `${ width }%` : null ) ) ],
                        [ 'margin-left', align === 'center' || align === 'right' ? 'auto' : null ],
                        [ 'margin-right', align === 'center' ? 'auto' : null ],
                        ...animDelayPairs( attributes ),
                        ...spacingPairs( legacy ),
                ] );
                const imgPairs = [
                        [ 'border-radius', radius ? `${ radius }px` : null ],
                ];
                if ( ratio === 'custom' ) {
                        imgPairs.push( [ 'width', widthPx ? `${ widthPx }px` : null ] );
                        imgPairs.push( [ 'height', heightPx ? `${ heightPx }px` : null ] );
                } else if ( ratio ) {
                        imgPairs.push( [ 'aspect-ratio', ratio.replace( '-', ' / ' ) ] );
                        imgPairs.push( [ 'object-fit', 'cover' ] );
                        imgPairs.push( [ 'width', '100%' ] );
                }
                const imgStyle = toStyle( imgPairs );

                const inspector = el(
                        KblInspector,
                        {
                                attrs: attributes,
                                setAttrs: setAttributes,
                                v4: true,
                                layout: el(
                                        'div',
                                        null,
                                        el( SelectRow, {
                                                label: __( 'Image ratio', 'kabel-blocks' ),
                                                value: ratio || '',
                                                options: [
                                                        { value: '', label: __( 'Auto', 'kabel-blocks' ) },
                                                        { value: '1-1', label: '1:1' },
                                                        { value: '4-3', label: '4:3' },
                                                        { value: '3-4', label: '3:4' },
                                                        { value: '16-9', label: '16:9' },
                                                        { value: '9-16', label: '9:16' },
                                                        { value: 'custom', label: __( 'Custom', 'kabel-blocks' ) },
                                                ],
                                                onChange: ( v ) => setAttributes( { ratio: v || undefined } ),
                                        } ),
                                        el( Resp, { prop: 'width', attrs: attributes, setAttrs: setAttributes },
                                                el( RangeRow, {
                                                        label: __( 'Width (%)', 'kabel-blocks' ),
                                                        min: 10,
                                                        max: 100,
                                                        unit: '%',
                                                } ) ),
                                        el( RangeRow, {
                                                label: __( 'Width (px)', 'kabel-blocks' ),
                                                value: widthPx,
                                                onChange: ( v ) => setAttributes( { widthPx: v } ),
                                                min: 40,
                                                max: 1920,
                                                step: 10,
                                                help: __( 'Custom width wins over the preset above.', 'kabel-blocks' ),
                                        } ),
                                        ratio === 'custom' ? el( RangeRow, {
                                                label: __( 'Height (px)', 'kabel-blocks' ),
                                                value: heightPx,
                                                onChange: ( v ) => setAttributes( { heightPx: v } ),
                                                min: 40,
                                                max: 1920,
                                                step: 10,
                                        } ) : null,
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
                                                v4: true,
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

                const img = el( 'img', {
                        className: 'kbl3-img',
                        src: url,
                        alt: alt || '',
                        style: imgStyle,
                } );

                return el(
                        Fr,
                        null,
                        el(
                                'figure',
                                useBlockProps( {
                                        className: cx( 'kbl3-fig', widthPx ? 'kbl3-fig--px' : '', uidClass( attributes ), ...applyAnim( attributes ), ...applyVisibility( attributes ), ...applySpacing( legacy ) ),
                                        style: figureStyle,
                                        id: anchor || undefined,
                                } ),
                                inspector,
                                controls,
                                linkURL ? el( 'a', { className: 'kbl3-fig__link', href: linkURL }, img ) : img,
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
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
