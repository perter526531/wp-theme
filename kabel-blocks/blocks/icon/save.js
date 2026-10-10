/**
 * kbl/icon — static save.
 *
 * v4: when a uid exists the responsive attributes (size / mt / mb with
 * their T / M variants) ride the per-instance <style> tag appended as a
 * SIBLING after the span; the legacy inline --kbl3-icon-size is dropped (it
 * would beat every media-query override). Legacy content without a uid
 * saves byte-identically to v3.
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import { useBlockProps } from '@wordpress/block-editor';
import { sanitizeClass, cx, toStyle, iconSvg, applyVisibility, applySpacing, spacingPairs } from '../../src/util.js';
import { uidClass, respStyleEl, v4Strip } from '../../src/kit.js';

import {
        RESP_SPEC,
        STRIP_KEYS,
} from './edit.js';

export function Save( { attributes } ) {
                const { anchor, name, size, color, shape, shapeBg } = attributes;
                const legacy = v4Strip( attributes, STRIP_KEYS );

                const className = cx(
                        'kbl3-icon',
                        shape !== 'none' && `kbl3-icon--${ shape }`,
                        uidClass( attributes ),
                        ...applyVisibility( attributes ),
                        ...applySpacing( legacy ),
                        sanitizeClass( attributes.customClass )
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
                                'span',
                                useBlockProps.save( { className, style, id: anchor || undefined } ),
                                iconSvg( name )
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
