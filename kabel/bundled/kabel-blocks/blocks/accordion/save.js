/**
 * kbl/accordion — static save.
 *
 * v4: when a uid exists the responsive attributes (radius / mt / mb with
 * their T / M variants) ride the per-instance <style> tag appended as a
 * SIBLING after the <details> — never inside it (the q / a html sources
 * would swallow the style markup). Legacy content without a uid saves
 * byte-identically to v3.
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { sanitizeClass, cx, toStyle, applyVisibility, applySpacing, spacingPairs } from '../../src/util.js';
import { uidClass, respStyleEl, v4Strip } from '../../src/kit.js';

import {
        RESP_SPEC,
        STRIP_KEYS,
} from './edit.js';

export function Save( { attributes } ) {
                const { q, a, open, bgColor, anchor } = attributes;
                const legacy = v4Strip( attributes, STRIP_KEYS );

                const className = cx( 'kbl3-acc', open && 'is-open', uidClass( attributes ), ...applyVisibility( attributes ), ...applySpacing( legacy ), sanitizeClass( attributes.customClass ) );
                const style = toStyle( [
                        [ 'background-color', bgColor ],
                        [ 'border-radius', legacy.radius ? `${ legacy.radius }px` : null ],
                        ...spacingPairs( legacy ),
                ] );

                return el(
                        Fr,
                        null,
                        el(
                                'details',
                                {
                                        ...useBlockProps.save( { className, style, id: anchor || undefined } ),
                                        ...( open ? { open: true } : {} ),
                                },
                                el(
                                        'summary',
                                        { className: 'kbl3-acc__q' },
                                        el( RichText.Content, { value: q } )
                                ),
                                el(
                                        'div',
                                        { className: 'kbl3-acc__a' },
                                        el( RichText.Content, { value: a } )
                                )
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
