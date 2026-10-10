/**
 * kbl/spacer — static save.
 *
 * v4: when a uid exists the responsive attributes (height / mt / mb with
 * their T / M variants) ride the per-instance <style> tag appended as a
 * SIBLING after the spacer; the legacy inline height is dropped (it would
 * beat every media-query override). Legacy content without a uid saves
 * byte-identically to v3.
 */
import { el, Fr } from '@wordpress/element';
import { useBlockProps } from '@wordpress/block-editor';
import { sanitizeClass, cx, toStyle, applyVisibility, applySpacing, spacingPairs } from '../../src/util.js';
import { uidClass, respStyleEl, v4Strip } from '../../src/kit.js';

import {
        RESP_SPEC,
        STRIP_KEYS,
} from './edit.js';

export function Save( { attributes } ) {
                const { height, anchor } = attributes;
                const legacy = v4Strip( attributes, STRIP_KEYS );
                return el(
                        Fr,
                        null,
                        el( 'div', {
                                ...useBlockProps.save( {
                                        className: cx( 'kbl3-spacer', uidClass( attributes ), ...applyVisibility( attributes ), ...applySpacing( legacy ), sanitizeClass( attributes.customClass ) ),
                                        /* v4: with a uid the height rides the per-instance sheet. */
                                        style: toStyle( [ [ 'height', attributes.uid ? null : `${ height ?? 48 }px` ], ...spacingPairs( legacy ) ] ),
                                        id: anchor || undefined,
                                } ),
                                'aria-hidden': true,
                        } ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
