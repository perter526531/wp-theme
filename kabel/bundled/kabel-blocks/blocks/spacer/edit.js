/**
 * kbl/spacer — vertical gap with height control.
 *
 * v4 (Stackable-style responsive layer): the height and the top / bottom
 * margins are per-device attributes (height / heightT / heightM, mt / mb
 * with T / M variants) riding the per-instance <style> tag (uid class +
 * --kbl-h / --kbl-mt / --kbl-mb custom properties), NOT the legacy inline
 * height (an inline style would beat every media-query override). The
 * static .kbl3-spacer rule consumes the variables with the v3 defaults as
 * fallbacks. The Advanced tab gains the per-instance custom CSS box and
 * the HTML anchor (AdvancedPanelV2). Legacy content without a uid keeps
 * the exact v3 output.
 */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { sanitizeClass, cx, toStyle, applyVisibility, applySpacing, spacingPairs } from '../../src/util.js';
import {
        KblInspector,
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
        { a: 'height', p: '--kbl-h', u: 'px' },
        { a: 'mt', p: '--kbl-mt', u: 'px' },
        { a: 'mb', p: '--kbl-mb', u: 'px' },
];
/* Keys the legacy emission must drop once a uid exists. PHP twin:
 * kbl4_strip() inside kbl3_spacer(). */
export const STRIP_KEYS = [ 'height', 'heightT', 'heightM', 'mt', 'mtT', 'mtM', 'mb', 'mbT', 'mbM' ];

export function Edit( { attributes, setAttributes, clientId, isSelected } ) {
                const { height } = attributes;

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
                                        layout: el( Resp, { prop: 'height', attrs: attributes, setAttrs: setAttributes },
                                                el( RangeRow, {
                                                        label: __( 'Height', 'kabel-blocks' ),
                                                        min: 8,
                                                        max: 240,
                                                } ) ),
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
                                {
                                        ...useBlockProps( {
                                                className: cx( 'kbl3-spacer', isSelected && 'is-selected', uidClass( attributes ), ...applyVisibility( attributes ), ...applySpacing( legacy ) ),
                                                /* v4: with a uid the height rides the per-instance sheet. */
                                                style: toStyle( [ [ 'height', attributes.uid ? null : `${ height ?? 48 }px` ], ...spacingPairs( legacy ) ] ),
                                                'aria-hidden': true,
                                        } ),
                                },
                                isSelected ? el( 'span', { className: 'kbl3-spacer__label' }, `${ height ?? 48 }px` ) : null
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
