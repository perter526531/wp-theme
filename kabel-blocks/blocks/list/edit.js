/**
 * kbl/list — bullet list with bronze markers (check / dash / dot).
 *
 * v4 (Stackable-style responsive layer): the font size and the top / bottom
 * margins are per-device attributes (fontSize / fontSizeT / fontSizeM, mt /
 * mb with T / M variants) riding the per-instance <style> tag (uid class +
 * --kbl-fs / --kbl-mt / --kbl-mb custom properties on the <ul>). The font
 * size control is new in v4 — legacy content without a uid keeps the exact
 * v3 output (no inline font emission ever existed for it). The Advanced tab
 * gains the per-instance custom CSS box and the HTML anchor
 * (AdvancedPanelV2).
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { sanitizeClass, cx, toStyle, applyVisibility, applySpacing, spacingPairs } from '../../src/util.js';
import {
        KblInspector,
        SegButtons,
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
        { a: 'fontSize', p: '--kbl-fs', u: 'px' },
        { a: 'mt', p: '--kbl-mt', u: 'px' },
        { a: 'mb', p: '--kbl-mb', u: 'px' },
];
/* Keys the legacy emission must drop once a uid exists. fontSize never had
 * a v3 inline emission (it is new in v4); it is listed for parity with the
 * other typography blocks. PHP twin: kbl4_strip() inside kbl3_list(). */
export const STRIP_KEYS = [ 'fontSize', 'fontSizeT', 'fontSizeM', 'mt', 'mtT', 'mtM', 'mb', 'mbT', 'mbM' ];

export function Edit( { attributes, setAttributes, clientId, isSelected } ) {
                const { values, marker, color } = attributes;

                useUid( attributes, setAttributes, clientId, isSelected );
                const legacy = v4Strip( attributes, STRIP_KEYS );

                const className = cx( 'kbl3-list', `kbl3-list--${ marker }`, uidClass( attributes ), ...applyVisibility( attributes ), ...applySpacing( legacy ) );
                const style = toStyle( [ [ 'color', color ], ...spacingPairs( legacy ) ] );

                return el(
                        Fr,
                        null,
                        el(
                                KblInspector,
                                {
                                        attrs: attributes,
                                        setAttrs: setAttributes,
                                        v4: true,
                                        layout: el( SegButtons, {
                                                label: __( 'Marker', 'kabel-blocks' ),
                                                value: marker,
                                                onChange: ( v ) => setAttributes( { marker: v } ),
                                                options: [
                                                        { value: 'check', label: __( 'Check', 'kabel-blocks' ) },
                                                        { value: 'dash', label: __( 'Dash', 'kabel-blocks' ) },
                                                        { value: 'dot', label: __( 'Dot', 'kabel-blocks' ) },
                                                        { value: 'none', label: __( 'None', 'kabel-blocks' ) },
                                                ],
                                        } ),
                                        style: el( 'div', null,
                                                el( ColorsPanel, {
                                                        colors: [
                                                                {
                                                                        label: __( 'Text color', 'kabel-blocks' ),
                                                                        value: color,
                                                                        onChange: ( v ) => setAttributes( { color: v } ),
                                                                        isText: true,
                                                                },
                                                        ],
                                                } ),
                                                el( Resp, { prop: 'fontSize', attrs: attributes, setAttrs: setAttributes },
                                                        el( RangeRow, {
                                                                label: __( 'Font size (px)', 'kabel-blocks' ),
                                                                min: 8,
                                                                max: 72,
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
                        el( RichText, {
                                tagName: 'ul',
                                multiline: 'li',
                                value: values,
                                onChange: ( v ) => setAttributes( { values: v } ),
                                placeholder: __( 'Add a list item…', 'kabel-blocks' ),
                                allowedFormats: [ 'core/bold', 'core/italic' ],
                                identifier: 'values',
                                ...useBlockProps( { className, style, id: attributes.anchor || undefined } ),
                        } ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
