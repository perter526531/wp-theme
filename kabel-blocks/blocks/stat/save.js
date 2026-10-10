/**
 * kbl/stat — static save.
 *
 * v4: when a uid exists the responsive attributes (fontSize / mt / mb with
 * their T / M variants) ride the per-instance <style> tag appended as a
 * SIBLING after the stat — never inside it (the number / label html sources
 * would swallow the style markup). The sheet emits real properties: the
 * font size on the .kbl3-stat__num descendant, the margins on the uid
 * root; the legacy inline --kbl3-fs* variables are dropped from the
 * stripped copy. Legacy content without a uid saves byte-identically to
 * v3.
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { sanitizeClass, cx, toStyle, applyVisibility, applySpacing, spacingPairs, applyAnim, typographyPairs } from '../../src/util.js';
import { uidClass, respStyleEl, v4Strip } from '../../src/kit.js';

import {
        RESP_SPEC,
        STRIP_KEYS,
} from './edit.js';

export function Save( { attributes } ) {
                const { pre, number, suf, label, align, color, anchor } = attributes;
                const legacy = v4Strip( attributes, STRIP_KEYS );

                const className = cx( 'kbl3-stat', align === 'center' && 'is-center', uidClass( attributes ), ...applyAnim( attributes ), ...applyVisibility( attributes ), ...applySpacing( legacy ), sanitizeClass( attributes.customClass ) );
                const style = toStyle( [
                        [ 'color', color ],
                        ...typographyPairs( legacy, { fw: false, lh: false, ls: false, tt: false } ),
                        ...spacingPairs( legacy ),
                ] );

                return el(
                        Fr,
                        null,
                        el(
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
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
