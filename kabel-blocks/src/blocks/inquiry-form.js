/**
 * kbl/inquiry-form — server-rendered inquiry form (fresh nonce per view).
 *
 * Dynamic block: save() returns null; PHP renders the real form and handles
 * the submission (see inc/dynamic.php). The editor shows a faithful replica.
 */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { register } from '../register.js';
import { useBlockProps } from '@wordpress/block-editor';
import { Disabled } from '@wordpress/components';
import { cx, toStyle, applyVisibility, applySpacing, spacingPairs } from '../util.js';
import { KblInspector, TextRow, SpacingPanel, VisibilityPanel } from '../inspector.js';

register( 'kbl/inquiry-form', {
        apiVersion: 3,
        title: __( 'Inquiry form (Kabel)', 'kabel-blocks' ),
        description: __(
                'The Kabel inquiry form — submissions land in the dashboard.',
                'kabel-blocks'
        ),
        category: 'kabel',
        keywords: [ 'kabel', 'form', 'inquiry', 'contact', '询盘', '询盘', '表单', '留言'],
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
                title: { type: 'string', default: 'CONTACT US' },
                sub: { type: 'string', default: 'Global Service · Local Response' },
                email: { type: 'string' },
                buttonLabel: { type: 'string', default: 'Send Inquiry' },
                hideD: { type: 'boolean' },
                hideT: { type: 'boolean' },
                hideM: { type: 'boolean' },
                mt: { type: 'number' },
                mb: { type: 'number' },
                mtM: { type: 'number' },
                mbM: { type: 'number' },
        },

        edit( { attributes, setAttributes } ) {
                const { title, sub, email, buttonLabel } = attributes;

                return el(
                        Fr,
                        null,
                        el(
                                KblInspector,
                                {
                                        layout: el( 'div', null,
                                                el( TextRow, {
                                                        label: __( 'Title', 'kabel-blocks' ),
                                                        value: title,
                                                        onChange: ( v ) => setAttributes( { title: v } ),
                                                } ),
                                                el( TextRow, {
                                                        label: __( 'Subtitle', 'kabel-blocks' ),
                                                        value: sub,
                                                        onChange: ( v ) => setAttributes( { sub: v } ),
                                                } ),
                                                el( TextRow, {
                                                        label: __( 'Recipient email', 'kabel-blocks' ),
                                                        value: email,
                                                        onChange: ( v ) => setAttributes( { email: v } ),
                                                        help: __( 'Leave empty to use the site administration email.', 'kabel-blocks' ),
                                                } ),
                                                el( TextRow, {
                                                        label: __( 'Button label', 'kabel-blocks' ),
                                                        value: buttonLabel,
                                                        onChange: ( v ) => setAttributes( { buttonLabel: v } ),
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
                                useBlockProps( {
                                        className: cx( 'kbl3-form', ...applyVisibility( attributes ), ...applySpacing( attributes ) ),
                                        style: toStyle( spacingPairs( attributes ) ),
                                } ),
                                el(
                                        Disabled,
                                        null,
                                        el(
                                                'form',
                                                { className: 'kbl3-form__replica', onSubmit: ( e ) => e.preventDefault() },
                                                el( 'h3', { className: 'kbl3-h kbl3-h--m' }, title || 'CONTACT US' ),
                                                sub ? el( 'p', { className: 'kbl3-text kbl3-text--m is-lead' }, sub ) : null,
                                                el( 'div', { className: 'kbl3-form__grid' },
                                                        el( 'input', { type: 'text', placeholder: __( 'Your name', 'kabel-blocks' ), disabled: true } ),
                                                        el( 'input', { type: 'email', placeholder: __( 'Your email', 'kabel-blocks' ), disabled: true } ),
                                                        el( 'input', { type: 'text', placeholder: __( 'Country / Company', 'kabel-blocks' ), disabled: true } )
                                                ),
                                                el( 'textarea', { rows: 3, placeholder: __( 'How can we help?', 'kabel-blocks' ), disabled: true } ),
                                                el( 'button', { type: 'button', className: 'kbl3-btn kbl3-btn--gold kbl3-btn--m' }, buttonLabel || 'Send Inquiry' ),
                                                el( 'p', { className: 'kbl3-form__hint' }, __( 'Live form — renders on the published page.', 'kabel-blocks' ) )
                                        )
                                )
                        )
                );
        },

        save() {
                return null;
        },
} );
