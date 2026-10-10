/**
 * kbl/button-group — a horizontal row of kbl/button blocks.
 *
 * v4 (Stackable-style responsive layer): the gap and the top / bottom
 * margins are per-device attributes (gap / gapT / gapM, mt / mb with T / M
 * variants) riding the per-instance <style> tag. The group only manages
 * layout, so no typography rides the sheet — each child button owns its
 * font. The gap keeps its --kbl3-btn-gap consumption chain (the sheet takes
 * over the inline slot); the margins go --kbl-mt / --kbl-mb. The Advanced
 * tab gains the per-instance custom CSS box and the HTML anchor
 * (AdvancedPanelV2). Legacy content without a uid keeps the exact v3 output.
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
        useBlockProps,
        useInnerBlocksProps,
        InnerBlocks,
} from '@wordpress/block-editor';
import { sanitizeClass, cx, toStyle, applyVisibility, applySpacing, spacingPairs } from '../../src/util.js';
import {
        KblInspector,
        RangeRow,
        AlignButtons,
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
        { a: 'gap', p: '--kbl3-btn-gap', u: 'px' },
        { a: 'mt', p: '--kbl-mt', u: 'px' },
        { a: 'mb', p: '--kbl-mb', u: 'px' },
];
/* Keys the legacy emission must drop once a uid exists. PHP twin:
 * kbl4_strip() inside kbl3_button_group(). */
export const STRIP_KEYS = [ 'gap', 'gapT', 'gapM', 'mt', 'mtT', 'mtM', 'mb', 'mbT', 'mbM' ];

export function Edit( { attributes, setAttributes, clientId, isSelected } ) {
                const { anchor, gap, align } = attributes;

                useUid( attributes, setAttributes, clientId, isSelected );
                const legacy = v4Strip( attributes, STRIP_KEYS );

                const style = toStyle( [
                        [ '--kbl3-btn-gap', attributes.uid ? null : `${ gap ?? 14 }px` ],
                        [ 'justify-content', align === 'center' ? 'center' : align === 'right' ? 'flex-end' : null ],
                        ...spacingPairs( legacy ),
                ] );

                const blockProps = useBlockProps( {
                        className: cx( 'kbl3-btnrow', uidClass( attributes ), ...applyVisibility( attributes ), ...applySpacing( legacy ) ),
                        style,
                        id: anchor || undefined,
                } );
                const innerProps = useInnerBlocksProps( blockProps, {
                        allowedBlocks: [ 'kbl/button' ],
                        template: [ [ 'kbl/button' ] ],
                        templateLock: false,
                        orientation: 'horizontal',
                        renderAppender: InnerBlocks.ButtonBlockAppender,
                } );

                return el(
                        Fr,
                        null,
                        el(
                                KblInspector,
                                {
                                        attrs: attributes,
                                        setAttrs: setAttributes,
                                        v4: true,
                                        layout: el(
                                                'div',
                                                null,
                                                el( Resp, { prop: 'gap', attrs: attributes, setAttrs: setAttributes },
                                                        el( RangeRow, {
                                                                label: __( 'Gap', 'kabel-blocks' ),
                                                                min: 0,
                                                                max: 48,
                                                        } ) ),
                                                el( AlignButtons, {
                                                        value: align,
                                                        onChange: ( v ) => setAttributes( { align: v } ),
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
                        el( 'div', innerProps ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
