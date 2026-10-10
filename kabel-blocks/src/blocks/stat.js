/**
 * kbl/stat — big number + label (counters row primitive).
 */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { register } from '../register.js';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { cx, toStyle, applyVisibility, applySpacing, spacingPairs, applyAnim, typographyPairs } from '../util.js';
import {
        KblInspector,
        AlignButtons,
        ColorsPanel,
        TextRow,
        TypographyPanel,
        SpacingPanel,
        VisibilityPanel,
        AnimPanel,
} from '../inspector.js';

register( 'kbl/stat', {
        apiVersion: 3,
        title: __( 'Stat (Kabel)', 'kabel-blocks' ),
        description: __(
                'Big number with optional prefix/suffix and a label — the counters-row primitive.',
                'kabel-blocks'
        ),
        category: 'kabel',
        keywords: [ 'kabel', 'stat', 'counter', 'number', '数据', '数据', '数字', '统计'],
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
                pre: { type: 'string' },
                number: {
                        type: 'string',
                        source: 'html',
                        selector: '.kbl3-stat__numtext',
                },
                suf: { type: 'string' },
                label: {
                        type: 'string',
                        source: 'html',
                        selector: '.kbl3-stat__label',
                },
                align: { type: 'string', default: 'center' },
                color: { type: 'string' },
                fontSize: { type: 'number' },
                fontSizeT: { type: 'number' },
                fontSizeM: { type: 'number' },
                anim: { type: 'string' },
                animDelay: { type: 'number' },
                hideD: { type: 'boolean' },
                hideT: { type: 'boolean' },
                hideM: { type: 'boolean' },
                mt: { type: 'number' },
                mb: { type: 'number' },
                mtM: { type: 'number' },
                mbM: { type: 'number' },
        },

        edit( { attributes, setAttributes } ) {
                const { pre, number, suf, label, align, color } = attributes;

                const className = cx( 'kbl3-stat', align === 'center' && 'is-center', ...applyAnim( attributes ), ...applyVisibility( attributes ), ...applySpacing( attributes ) );
                const style = toStyle( [
                        [ 'color', color ],
                        ...typographyPairs( attributes, { fw: false, lh: false, ls: false, tt: false } ),
                        ...spacingPairs( attributes ),
                ] );

                return el(
                        Fr,
                        null,
                        el(
                                KblInspector,
                                {
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
                        )
                );
        },

        save( { attributes } ) {
                const { pre, number, suf, label, align, color, anchor } = attributes;

                const className = cx( 'kbl3-stat', align === 'center' && 'is-center', ...applyAnim( attributes ), ...applyVisibility( attributes ), ...applySpacing( attributes ) );
                const style = toStyle( [
                        [ 'color', color ],
                        ...typographyPairs( attributes, { fw: false, lh: false, ls: false, tt: false } ),
                        ...spacingPairs( attributes ),
                ] );

                return el(
                        'div',
                        useBlockProps.save( { className, style, id: anchor || undefined } ),
                        el(
                                'span',
                                { className: 'kbl3-stat__num' },
                                pre ? el( 'span', { className: 'kbl3-stat__pre' }, pre ) : null,
                                el( RichText.Content, {
                                        tagName: 'span',
                                        className: 'kbl3-stat__numtext',
                                        value: number,
                                } ),
                                suf ? el( 'span', { className: 'kbl3-stat__suf' }, suf ) : null
                        ),
                        el( RichText.Content, {
                                tagName: 'span',
                                className: 'kbl3-stat__label',
                                value: label,
                        } )
                );
        },
} );
