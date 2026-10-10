/**
 * kbl/stat — big number + label (counters row primitive).
 *
 * v4 (Stackable-style responsive layer): the number font size and the top /
 * bottom margins are per-device attributes (fontSize / fontSizeT /
 * fontSizeM, mt / mb with T / M variants) riding the per-instance <style>
 * tag. The stat has no --kbl-* consumption chains in the shared stylesheet
 * yet, so the sheet emits REAL properties: the font size on the
 * .kbl3-stat__num descendant (specificity 0,2,0 beats the --kbl3-fs chain
 * and its 1080/781 media zones, so unset overrides follow the desktop
 * value), the margins on the uid root (source order beats the .kbl3-stat
 * skin). The Advanced tab gains the per-instance custom CSS box and the
 * HTML anchor (AdvancedPanelV2). Legacy content without a uid keeps the
 * exact v3 output.
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { sanitizeClass, cx, toStyle, applyVisibility, applySpacing, spacingPairs, applyAnim, typographyPairs } from '../../src/util.js';
import {
        KblInspector,
        AlignButtons,
        ColorsPanel,
        TextRow,
        TypographyPanel,
        SpacingPanel,
        VisibilityPanel,
        AnimPanel,
        Resp,
        useUid,
        uidClass,
        respStyleEl,
        v4Strip,
} from '../../src/kit.js';

/* Which attributes ride the per-instance <style> tag (respCss spec). The
 * font-size rule targets the big-number descendant (sel), the margins the
 * root. PHP twin: the $spec array inside kbl3_stat(). */
export const RESP_SPEC = [
        { a: 'fontSize', p: 'font-size', u: 'px', sel: ' .kbl3-stat__num' },
        { a: 'mt', p: 'margin-top', u: 'px' },
        { a: 'mb', p: 'margin-bottom', u: 'px' },
];
/* Keys the legacy emission must drop once a uid exists (the inline
 * --kbl3-fs / --kbl3-fs-t / --kbl3-fs-m variables would beat every
 * media-query override). PHP twin: kbl4_strip() inside kbl3_stat(). */
export const STRIP_KEYS = [ 'fontSize', 'fontSizeT', 'fontSizeM', 'mt', 'mtT', 'mtM', 'mb', 'mbT', 'mbM' ];

export function Edit( { attributes, setAttributes, clientId, isSelected } ) {
                const { pre, number, suf, label, align, color } = attributes;

                useUid( attributes, setAttributes, clientId, isSelected );
                const legacy = v4Strip( attributes, STRIP_KEYS );

                const className = cx( 'kbl3-stat', align === 'center' && 'is-center', uidClass( attributes ), ...applyAnim( attributes ), ...applyVisibility( attributes ), ...applySpacing( legacy ) );
                const style = toStyle( [
                        [ 'color', color ],
                        ...typographyPairs( legacy, { fw: false, lh: false, ls: false, tt: false } ),
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
                                        layout: el( AlignButtons, {
                                                label: __( 'Alignment', 'kabel-blocks' ),
                                                value: align,
                                                onChange: ( v ) => setAttributes( { align: v || 'center' } ),
                                        } ),
                                        style: el( 'div', null,
                                                el( ColorsPanel, {
                                                        colors: [
                                                                {
                                                                        label: __( 'Number color', 'kabel-blocks' ),
                                                                        value: color,
                                                                        onChange: ( v ) => setAttributes( { color: v } ),
                                                                },
                                                        ],
                                                } ),
                                                el( TypographyPanel, {
                                                        attrs: attributes,
                                                        setAttrs: setAttributes,
                                                        v4: true,
                                                        include: { fw: false, lh: false, ls: false, tt: false },
                                                } ),
                                                el( AnimPanel, {
                                                        attrs: attributes,
                                                        setAttrs: setAttributes,
                                                } )
                                        ),
                                        advanced: el( 'div', null,
                                                el( TextRow, {
                                                        label: __( 'Prefix', 'kabel-blocks' ),
                                                        value: pre,
                                                        onChange: ( v ) => setAttributes( { pre: v } ),
                                                        placeholder: '+',
                                                } ),
                                                el( TextRow, {
                                                        label: __( 'Suffix', 'kabel-blocks' ),
                                                        value: suf,
                                                        onChange: ( v ) => setAttributes( { suf: v } ),
                                                        placeholder: 'K+',
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
                        ),
                        el(
                                'div',
                                useBlockProps( { className, style, id: attributes.anchor || undefined } ),
                                el(
                                        'span',
                                        { className: 'kbl3-stat__num' },
                                        pre ? el( 'span', { className: 'kbl3-stat__pre' }, pre ) : null,
                                        el( RichText, {
                                                tagName: 'span',
                                                className: 'kbl3-stat__numtext',
                                                value: number,
                                                onChange: ( v ) => setAttributes( { number: v } ),
                                                placeholder: '128',
                                                allowedFormats: [],
                                                identifier: 'number',
                                        } ),
                                        suf ? el( 'span', { className: 'kbl3-stat__suf' }, suf ) : null
                                ),
                                el( RichText, {
                                        tagName: 'span',
                                        className: 'kbl3-stat__label',
                                        value: label,
                                        onChange: ( v ) => setAttributes( { label: v } ),
                                        placeholder: __( 'What it counts…', 'kabel-blocks' ),
                                        allowedFormats: [ 'core/bold', 'core/italic' ],
                                        identifier: 'label',
                                } )
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
