/**
 * kbl/accordion — one FAQ entry. Renders <details>/<summary> on the front end
 * (no JS needed); the editor shows a styled replica with a chevron toggle.
 *
 * v4 (Stackable-style responsive layer): the corner radius and the top /
 * bottom margins are per-device attributes (radius / radiusT / radiusM, mt /
 * mb with T / M variants) riding the per-instance <style> tag. The radius is
 * a REAL property on the uid class (no static rule consumes a --kbl-radius
 * variable), the margins are real properties too (the .kbl3-acc margin skin
 * has no --kbl-mt chain — the later per-instance sheet wins by source
 * order). The Advanced tab gains the per-instance custom CSS box and the
 * HTML anchor (AdvancedPanelV2). Legacy content without a uid keeps the
 * exact v3 output.
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, BlockControls } from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton } from '@wordpress/components';
import { sanitizeClass, cx, toStyle, applyVisibility, applySpacing, spacingPairs } from '../../src/util.js';
import {
        KblInspector,
        ColorsPanel,
        RangeRow,
        SpacingPanel,
        VisibilityPanel,
        Resp,
        useUid,
        uidClass,
        respStyleEl,
        v4Strip,
} from '../../src/kit.js';

/* Which attributes ride the per-instance <style> tag (respCss spec). */
export const RESP_SPEC = [
        { a: 'radius', p: 'border-radius', u: 'px' },
        { a: 'mt', p: 'margin-top', u: 'px' },
        { a: 'mb', p: 'margin-bottom', u: 'px' },
];
/* Keys the legacy emission must drop once a uid exists (the inline radius
 * and the --kbl3-m* variables would beat every media-query override). PHP
 * twin: kbl4_strip() inside kbl3_accordion(). */
export const STRIP_KEYS = [ 'radius', 'radiusT', 'radiusM', 'mt', 'mtT', 'mtM', 'mb', 'mbT', 'mbM' ];

export function Edit( { attributes, setAttributes, clientId, isSelected } ) {
                const { q, a, open, bgColor } = attributes;

                useUid( attributes, setAttributes, clientId, isSelected );
                const legacy = v4Strip( attributes, STRIP_KEYS );

                const className = cx( 'kbl3-acc', open && 'is-open', uidClass( attributes ), ...applyVisibility( attributes ), ...applySpacing( legacy ) );
                const style = toStyle( [
                        [ 'background-color', bgColor ],
                        [ 'border-radius', legacy.radius ? `${ legacy.radius }px` : null ],
                        ...spacingPairs( legacy ),
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
                                        attrs: attributes,
                                        setAttrs: setAttributes,
                                        v4: true,
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
                                                el( Resp, { prop: 'radius', attrs: attributes, setAttrs: setAttributes },
                                                        el( RangeRow, {
                                                                label: __( 'Corner radius', 'kabel-blocks' ),
                                                                min: 0,
                                                                max: 24,
                                                        } ) )
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
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
