/**
 * kbl/logo — site logo with width control and an optional link.
 * Header element: pairs with kbl/nav-menu, kbl/search and friends.
 */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { register } from '../register.js';
import {
        useBlockProps,
        MediaUpload,
        MediaUploadCheck,
        MediaPlaceholder,
        BlockControls,
} from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton } from '@wordpress/components';
import { cx, toStyle, applyVisibility, applySpacing, spacingPairs } from '../util.js';
import { KblInspector, RangeRow, TextRow, SpacingPanel, VisibilityPanel } from '../inspector.js';

register( 'kbl/logo', {
        apiVersion: 3,
        title: __( 'Logo (Kabel)', 'kabel-blocks' ),
        description: __(
                'Site logo with width control and an optional link — the header element.',
                'kabel-blocks'
        ),
        category: 'kabel',
        keywords: [ 'kabel', 'logo', 'brand', 'Logo', '品牌', '站点标志'],
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
                url: { type: 'string' },
                alt: { type: 'string', default: '' },
                width: { type: 'number', default: 120 },
                linkURL: { type: 'string' },
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
                const { url, alt, linkURL } = a;

                const className = logoClass( a );
                const style = logoStyle( a );

                const inspector = el(
                        KblInspector,
                        {
                                layout: el(
                                        'div',
                                        null,
                                        el( RangeRow, {
                                                label: __( 'Logo width (px)', 'kabel-blocks' ),
                                                value: a.width,
                                                initial: 120,
                                                onChange: ( v ) => setAttributes( { width: v } ),
                                                min: 40,
                                                max: 320,
                                                step: 2,
                                        } ),
                                        el(
                                                'p',
                                                { className: 'kbl3-panel-hint' },
                                                __( 'Use the toolbar "Replace image" button to swap the logo.', 'kabel-blocks' )
                                        )
                                ),
                                style: null,
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
                                                placeholder: '/',
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
                );

                const controls = url
                        ? el(
                                BlockControls,
                                null,
                                el(
                                        ToolbarGroup,
                                        null,
                                        el( MediaUploadCheck, null,
                                                el( MediaUpload, {
                                                        onSelect: ( media ) =>
                                                                setAttributes( { url: media.url, id: media.id, alt: media.alt || '' } ),
                                                        allowedTypes: [ 'image' ],
                                                        value: a.id,
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
                                        label: __( 'Logo', 'kabel-blocks' ),
                                        instructions: __( 'Upload or pick an image from the library.', 'kabel-blocks' ),
                                        onSelect: ( media ) =>
                                                setAttributes( { url: media.url, id: media.id, alt: media.alt || '' } ),
                                        accept: 'image/*',
                                        allowedTypes: [ 'image' ],
                                } )
                        );
                }

                const img = el( 'img', {
                        className: 'kbl3-logo__img',
                        src: url,
                        alt: alt || '',
                } );

                return el(
                        Fr,
                        null,
                        inspector,
                        controls,
                        el(
                                'figure',
                                useBlockProps( {
                                        className,
                                        style,
                                        id: a.anchor || undefined,
                                } ),
                                linkURL ? el( 'a', { className: 'kbl3-logo__link', href: linkURL }, img ) : img
                        )
                );
        },

        save( { attributes } ) {
                const a = attributes;

                const img = a.url
                        ? el( 'img', {
                                className: 'kbl3-logo__img',
                                src: a.url,
                                alt: a.alt || '',
                          } )
                        : null;

                return el(
                        'figure',
                        useBlockProps.save( {
                                className: logoClass( a ),
                                style: logoStyle( a ),
                                id: a.anchor || undefined,
                        } ),
                        a.linkURL && img ? el( 'a', { className: 'kbl3-logo__link', href: a.linkURL }, img ) : img
                );
        },
} );

/** Shared by edit + save. PHP twin: kbl3_logo(). */
function logoClass( a ) {
        return cx(
                'kbl3-logo',
                ...applyVisibility( a ),
                ...applySpacing( a )
        );
}

/** Shared by edit + save. PHP twin: kbl3_logo(). */
function logoStyle( a ) {
        return toStyle( [
                [ '--kbl3-logo-w', ( a.width ?? 120 ) + 'px' ],
                ...spacingPairs( a ),
        ] );
}
