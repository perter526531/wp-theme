/**
 * kbl/icon — a curated stroke icon with size, color and optional shape.
 *
 * v4 (Stackable-style responsive layer): the icon size and the top / bottom
 * margins are per-device attributes (size / sizeT / sizeM, mt / mb with T / M
 * variants) riding the per-instance <style> tag (uid class + --kbl-is /
 * --kbl-mt / --kbl-mb custom properties). The static rules consume the
 * variables with the v3 --kbl3-icon-size chain as fallback. The Advanced tab
 * gains the per-instance custom CSS box and the HTML anchor
 * (AdvancedPanelV2). Legacy content without a uid keeps the exact v3 output.
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { sanitizeClass, cx, toStyle, iconSvg, applyVisibility, applySpacing, spacingPairs } from '../../src/util.js';
import {
        KblInspector,
        RangeRow,
        SegButtons,
        ColorsPanel,
        IconPicker,
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
        { a: 'size', p: '--kbl-is', u: 'px' },
        { a: 'mt', p: '--kbl-mt', u: 'px' },
        { a: 'mb', p: '--kbl-mb', u: 'px' },
];
/* Keys the legacy emission must drop once a uid exists. PHP twin:
 * kbl4_strip() inside kbl3_icon(). */
export const STRIP_KEYS = [ 'size', 'sizeT', 'sizeM', 'mt', 'mtT', 'mtM', 'mb', 'mbT', 'mbM' ];

export function Edit( { attributes, setAttributes, clientId, isSelected } ) {
                const { anchor, name, size, color, shape, shapeBg } = attributes;

                useUid( attributes, setAttributes, clientId, isSelected );
                const legacy = v4Strip( attributes, STRIP_KEYS );

                const className = cx(
                        'kbl3-icon',
                        shape !== 'none' && `kbl3-icon--${ shape }`,
                        uidClass( attributes ),
                        ...applyVisibility( attributes ),
                        ...applySpacing( legacy )
                );
                const style = toStyle( [
                        /* v4: with a uid the size rides the per-instance sheet. */
                        [ '--kbl3-icon-size', attributes.uid ? null : `${ size ?? 48 }px` ],
                        [ 'color', color ],
                        [ 'background-color', shape !== 'none' ? shapeBg : null ],
                        ...spacingPairs( legacy ),
                ] );

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
                                                el( IconPicker, {
                                                        value: name,
                                                        onChange: ( v ) => setAttributes( { name: v } ),
                                                } ),
                                                el( Resp, { prop: 'size', attrs: attributes, setAttrs: setAttributes },
                                                        el( RangeRow, {
                                                                label: __( 'Size', 'kabel-blocks' ),
                                                                min: 16,
                                                                max: 128,
                                                        } ) ),
                                                el( SegButtons, {
                                                        label: __( 'Shape', 'kabel-blocks' ),
                                                        value: shape,
                                                        onChange: ( v ) => setAttributes( { shape: v } ),
                                                        options: [
                                                                { value: 'none', label: __( 'None', 'kabel-blocks' ) },
                                                                { value: 'circle', label: __( 'Circle', 'kabel-blocks' ) },
                                                                { value: 'square', label: __( 'Square', 'kabel-blocks' ) },
                                                        ],
                                                } )
                                        ),
                                        style: el( ColorsPanel, {
                                                colors: [
                                                        {
                                                                label: __( 'Icon color', 'kabel-blocks' ),
                                                                value: color,
                                                                onChange: ( v ) => setAttributes( { color: v } ),
                                                                isText: true,
                                                        },
                                                        ...( shape !== 'none' ? [ {
                                                                label: __( 'Shape background', 'kabel-blocks' ),
                                                                value: shapeBg,
                                                                onChange: ( v ) => setAttributes( { shapeBg: v } ),
                                                                isBackground: true,
                                                        } ] : [] ),
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
                        el(
                                'span',
                                useBlockProps( { className, style, id: anchor || undefined } ),
                                iconSvg( name )
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
