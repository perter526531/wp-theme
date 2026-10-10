/**
 * kbl/accordion — one FAQ entry. Renders <details>/<summary> on the front end
 * (no JS needed); the editor shows a styled replica with a chevron toggle.
 */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { register } from '../register.js';
import { useBlockProps, RichText, BlockControls } from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton } from '@wordpress/components';
import { cx, toStyle, applyVisibility, applySpacing, spacingPairs } from '../util.js';
import { KblInspector, ColorsPanel, RangeRow, SpacingPanel, VisibilityPanel } from '../inspector.js';

register( 'kbl/accordion', {
        apiVersion: 3,
        title: __( 'FAQ item (Kabel)', 'kabel-blocks' ),
        description: __(
                'Question + collapsible answer. No JS — native details/summary.',
                'kabel-blocks'
        ),
        category: 'kabel',
        keywords: [ 'kabel', 'accordion', 'faq', 'question', '问答', '手风琴', '问答', '折叠'],
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
                q: {
                        type: 'string',
                        source: 'html',
                        selector: 'summary',
                },
                a: {
                        type: 'string',
                        source: 'html',
                        selector: '.kbl3-acc__a',
                },
                open: { type: 'boolean' },
                bgColor: { type: 'string' },
                radius: { type: 'number' },
                hideD: { type: 'boolean' },
                hideT: { type: 'boolean' },
                hideM: { type: 'boolean' },
                mt: { type: 'number' },
                mb: { type: 'number' },
                mtM: { type: 'number' },
                mbM: { type: 'number' },
        },

        edit( { attributes, setAttributes, clientId } ) {
                const { q, a, open, bgColor, radius } = attributes;

                const className = cx( 'kbl3-acc', open && 'is-open', ...applyVisibility( attributes ), ...applySpacing( attributes ) );
                const style = toStyle( [
                        [ 'background-color', bgColor ],
                        [ 'border-radius', radius ? `${ radius }px` : null ],
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
                                        el( ToolbarButton, {
                                                icon: open ? 'arrow-up' : 'arrow-down',
                                                title: open ? __( 'Close by default', 'kabel-blocks' ) : __( 'Open by default', 'kabel-blocks' ),
                                                onClick: () => setAttributes( { open: ! open } ),
                                        } )
                                )
                        ),
                        el(
                                KblInspector,
                                {
                                        layout: null,
                                        style: el( 'div', null,
                                                el( ColorsPanel, {
                                                        colors: [
                                                                {
                                                                        label: __( 'Background', 'kabel-blocks' ),
                                                                        value: bgColor,
                                                                        onChange: ( v ) => setAttributes( { bgColor: v } ),
                                                                        isBackground: true,
                                                                },
                                                        ],
                                                } ),
                                                el( RangeRow, {
                                                        label: __( 'Corner radius', 'kabel-blocks' ),
                                                        value: radius,
                                                        onChange: ( v ) => setAttributes( { radius: v } ),
                                                        min: 0,
                                                        max: 24,
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
                                useBlockProps( { className, style, id: attributes.anchor || undefined } ),
                                el(
                                        'div',
                                        { className: 'kbl3-acc__qrow' },
                                        el( RichText, {
                                                tagName: 'div',
                                                className: 'kbl3-acc__q',
                                                value: q,
                                                onChange: ( v ) => setAttributes( { q: v } ),
                                                placeholder: __( 'Write the question…', 'kabel-blocks' ),
                                                allowedFormats: [ 'core/bold', 'core/italic' ],
                                                identifier: 'q',
                                        } ),
                                        el(
                                                'button',
                                                {
                                                        type: 'button',
                                                        className: 'kbl3-acc__chev',
                                                        'aria-expanded': open ? 'true' : 'false',
                                                        onClick: () => setAttributes( { open: ! open } ),
                                                },
                                                el( 'span', { className: 'dashicons dashicons-arrow-down-alt2' } )
                                        )
                                ),
                                open ?
                                        el( RichText, {
                                                tagName: 'div',
                                                className: 'kbl3-acc__a',
                                                value: a,
                                                onChange: ( v ) => setAttributes( { a: v } ),
                                                placeholder: __( 'Write the answer…', 'kabel-blocks' ),
                                                allowedFormats: [ 'core/bold', 'core/italic', 'core/link' ],
                                                identifier: 'a',
                                        } ) :
                                        null
                        )
                );
        },

        save( { attributes } ) {
                const { q, a, open, bgColor, radius, anchor } = attributes;

                const className = cx( 'kbl3-acc', open && 'is-open', ...applyVisibility( attributes ), ...applySpacing( attributes ) );
                const style = toStyle( [
                        [ 'background-color', bgColor ],
                        [ 'border-radius', radius ? `${ radius }px` : null ],
                        ...spacingPairs( attributes ),
                ] );

                return el(
                        'details',
                        {
                                ...useBlockProps.save( { className, style, id: anchor || undefined } ),
                                ...( open ? { open: true } : {} ),
                        },
                        el(
                                'summary',
                                { className: 'kbl3-acc__q' },
                                el( RichText.Content, { value: q } )
                        ),
                        el(
                                'div',
                                { className: 'kbl3-acc__a' },
                                el( RichText.Content, { value: a } )
                        )
                );
        },
} );
