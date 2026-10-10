/**
 * kbl/columns — static save.
 *
 * v4: when a uid exists the responsive attributes (gap / mt / mb with
 * their T / M variants, colsT / colsM per-row wraps) ride the per-instance
 * <style> tag appended as a SIBLING after the row — never inside it (the
 * InnerBlocks content would swallow it). The inline --kbl3-gap is dropped
 * (an inline custom property would beat every media-query override);
 * without a uid the v3 emission is byte-identical.
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import {
        useBlockProps,
        InnerBlocks,
} from '@wordpress/block-editor';
import { sanitizeClass, cx, toStyle, applyVisibility, applySpacing, spacingPairs } from '../../src/util.js';
import { uidClass, respStyleEl, v4Strip } from '../../src/kit.js';

import {
        RESP_SPEC,
        STRIP_KEYS,
} from './edit.js';

export function Save( { attributes } ) {
                const { anchor, vGap, vAlign, collapse, reverse } = attributes;
                const legacy = v4Strip( attributes, STRIP_KEYS );

                const className = cx(
                        'kbl3-columns',
                        vAlign === 'center' && 'kbl3-valign--center',
                        vAlign === 'bottom' && 'kbl3-valign--bottom',
                        ! collapse && 'kbl3-keep-row',
                        reverse && 'kbl3-reverse',
                        uidClass( attributes ),
                        ...applyVisibility( attributes ),
                        ...applySpacing( legacy ),
                        sanitizeClass( attributes.customClass )
                );
                const style = toStyle( [
                        /* v4: with a uid the gap rides the per-instance sheet. */
                        [ '--kbl3-gap', attributes.uid ? null : `${ attributes.gap ?? 24 }px` ],
                        [ '--kbl3-vgap', legacy.vGap != null ? `${ legacy.vGap }px` : null ],
                        ...spacingPairs( legacy ),
                ] );

                return el(
                        Fr,
                        null,
                        el(
                                'div',
                                useBlockProps.save( { className, style, id: anchor || undefined } ),
                                el( InnerBlocks.Content )
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
