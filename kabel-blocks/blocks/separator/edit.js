/**
 * kbl/separator — the bronze hairline.
 *
 * v4 (Stackable-style responsive layer): the width and the top / bottom
 * margins are per-device attributes (width / widthT / widthM, mt / mb
 * with T / M variants) riding the per-instance <style> tag (uid class +
 * --kbl-w / --kbl-mt / --kbl-mb custom properties), NOT the legacy inline
 * width (an inline style would beat every media-query override). The
 * static .kbl3-sep rule consumes the variables with the v3 defaults as
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
        ColorsPanel,
        SegButtons,
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
        { a: 'width', p: '--kbl-w', u: 'px' },
        { a: 'mt', p: '--kbl-mt', u: 'px' },
        { a: 'mb', p: '--kbl-mb', u: 'px' },
];
/* Keys the legacy emission must drop once a uid exists. PHP twin:
 * kbl4_strip() inside kbl3_separator(). */
export const STRIP_KEYS = [ 'width', 'widthT', 'widthM', 'mt', 'mtT', 'mtM', 'mb', 'mbT', 'mbM' ];

/** Shared by edit + save (imported by save.js). PHP twin: kbl3_separator().
 * Receives the v4-stripped copy for the spacing emission. */
export function sepProps( attributes ) {
        const { align, color, anchor } = attributes;
        const legacy = v4Strip( attributes, STRIP_KEYS );
        return {
                className: cx( 'kbl3-sep', align === 'center' && 'is-center', uidClass( attributes ), ...applyVisibility( attributes ), ...applySpacing( legacy ), sanitizeClass( legacy.customClass ) ),
                style: toStyle( [
                        /* v4: with a uid the width rides the per-instance sheet. */
                        [ 'width', attributes.uid ? null : `${ attributes.width ?? 96 }px` ],
                        [ 'border-color', color ],
                        ...spacingPairs( legacy ),
                ] ),
                id: anchor || undefined,
        };
}

export function Edit( { attributes, setAttributes, clientId, isSelected } ) {
                const { align, color } = attributes;

                useUid( attributes, setAttributes, clientId, isSelected );

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
                                                el( Resp, { prop: 'width', attrs: attributes, setAttrs: setAttributes },
                                                        el( RangeRow, {
                                                                label: __( 'Width', 'kabel-blocks' ),
                                                                min: 24,
                                                                max: 480,
                                                        } ) ),
                                                el( SegButtons, {
                                                        label: __( 'Alignment', 'kabel-blocks' ),
                                                        value: align,
                                                        onChange: ( v ) => setAttributes( { align: v } ),
                                                        options: [
                                                                { value: 'left', label: __( 'Left', 'kabel-blocks' ) },
                                                                { value: 'center', label: __( 'Center', 'kabel-blocks' ) },
                                                        ],
                                                } )
                                        ),
                                        style: el( ColorsPanel, {
                                                colors: [
                                                        {
                                                                label: __( 'Line color', 'kabel-blocks' ),
                                                                value: color,
                                                                onChange: ( v ) => setAttributes( { color: v } ),
                                                        },
                                                ],
                                        } ),
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
                        el( 'hr', useBlockProps( sepProps( attributes ) ) ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
