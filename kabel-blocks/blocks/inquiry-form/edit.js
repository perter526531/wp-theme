/**
 * kbl/inquiry-form — server-rendered inquiry form (fresh nonce per view).
 *
 * Dynamic block: save() returns null; PHP renders the real form and handles
 * the submission (see render.php in this folder). The editor shows a faithful
 * replica.
 *
 * v4 (Stackable-style responsive layer): the top / bottom margins are
 * per-device attributes (mt / mb with T / M variants) riding the
 * per-instance <style> tag (uid class + real margin properties on the root —
 * no --kbl-mt consumer exists for .kbl3-form). The Advanced tab gains the
 * per-instance custom CSS box and the HTML anchor (AdvancedPanelV2). Legacy
 * content without a uid keeps the exact v3 output (v4Strip passes the
 * attributes through untouched).
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { Disabled } from '@wordpress/components';
import { cx, toStyle, applyVisibility, applySpacing, spacingPairs } from '../../src/util.js';
import { KblInspector, TextRow, SpacingPanel, VisibilityPanel, useUid, uidClass, respStyleEl, v4Strip } from '../../src/kit.js';

/* Which attributes ride the per-instance <style> tag (respCss spec).
 * Margins are real properties: the shared CSS has no --kbl-mt consumer for
 * .kbl3-form, and a real property on the per-instance sheet wins over the
 * legacy inline path by source order anyway. PHP twin: the $spec in
 * blocks/inquiry-form/render.php. */
export const RESP_SPEC = [
        { a: 'mt', p: 'margin-top', u: 'px' },
        { a: 'mb', p: 'margin-bottom', u: 'px' },
];
/* Keys the legacy emission must drop once a uid exists (the inline margin
 * utilities would beat every media-query override — trap ⑥). PHP twin:
 * kbl4_strip() in render.php. */
export const STRIP_KEYS = [ 'mt', 'mtT', 'mtM', 'mb', 'mbT', 'mbM' ];

export function Edit( { attributes, setAttributes, clientId, isSelected } ) {
                const { title, sub, email, buttonLabel } = attributes;

                useUid( attributes, setAttributes, clientId, isSelected );
                const legacy = v4Strip( attributes, STRIP_KEYS );

                return el(
                                Fr,
                                null,
                                el(
                                                KblInspector,
                                                {
                                                                attrs: attributes,
                                                                setAttrs: setAttributes,
                                                                v4: true,
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
                                                                                                v4: true,
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
                                                                className: cx( 'kbl3-form', uidClass( attributes ), ...applyVisibility( attributes ), ...applySpacing( legacy ) ),
                                                                style: toStyle( spacingPairs( legacy ) ),
                                                                id: attributes.anchor || undefined,
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
                                ),
                                /* v4 preview: the per-instance <style> rides as a SIBLING so the
                                 * editor replica shows the responsive margins live. */
                                respStyleEl( attributes, RESP_SPEC )
                );
}
