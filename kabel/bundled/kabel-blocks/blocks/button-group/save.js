/**
 * kbl/button-group — static save.
 *
 * v4: when a uid exists the responsive attributes (gap / mt / mb with their
 * T / M variants) ride the per-instance <style> tag appended as a SIBLING
 * after the row — never inside it (the InnerBlocks content would swallow
 * it). The inline --kbl3-btn-gap slot is dropped (it would beat every
 * media-query override). Legacy content without a uid saves byte-identically
 * to v3.
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
                const { anchor, gap, align } = attributes;
                const legacy = v4Strip( attributes, STRIP_KEYS );

                const style = toStyle( [
                        [ '--kbl3-btn-gap', attributes.uid ? null : `${ gap ?? 14 }px` ],
                        [ 'justify-content', align === 'center' ? 'center' : align === 'right' ? 'flex-end' : null ],
                        ...spacingPairs( legacy ),
                ] );

                return el(
                        Fr,
                        null,
                        el(
                                'div',
                                useBlockProps.save( {
                                        className: cx( 'kbl3-btnrow', uidClass( attributes ), ...applyVisibility( attributes ), ...applySpacing( legacy ), sanitizeClass( attributes.customClass ) ),
                                        style,
                                        id: anchor || undefined,
                                } ),
                                el( InnerBlocks.Content )
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
