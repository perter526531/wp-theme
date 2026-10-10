/**
 * kbl/testimonial — static save.
 *
 * v4: when a uid exists the responsive attributes (fontSize / mt / mb with
 * their T / M variants) ride the per-instance <style> tag appended as a
 * SIBLING after the figure — never inside it (the quote / name / role html
 * sources would swallow the style markup). The sheet emits real
 * properties: the font size on the .kbl3-testi__quote descendant, the
 * margins on the uid root; the legacy inline --kbl3-fs* variables are
 * dropped from the stripped copy. Legacy content without a uid saves
 * byte-identically to v3.
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { sanitizeClass, cx, toStyle, iconSvg, applyVisibility, applySpacing, spacingPairs, applyAnim, animDelayPairs, typographyPairs } from '../../src/util.js';
import { uidClass, respStyleEl, v4Strip } from '../../src/kit.js';

import {
        clampRating,
        starNodes,
        testiClass,
        testiStyle,
        RESP_SPEC,
        STRIP_KEYS,
} from './edit.js';

export function Save( { attributes } ) {
                const a = attributes;
                const rating = clampRating( a.rating );
                const legacy = v4Strip( attributes, STRIP_KEYS );

                return el(
                        Fr,
                        null,
                        el(
                                'figure',
                                useBlockProps.save( { className: testiClass( legacy ), style: testiStyle( legacy ), id: a.anchor || undefined } ),
                                rating > 0
                                                ? el(
                                                                'div',
                                                                {
                                                                                className: 'kbl3-testi__stars',
                                                                                'aria-label': sprintf(
                                                                                                /* translators: %d: star rating (0-5). */
                                                                                                __( 'Rated %d out of 5', 'kabel-blocks' ),
                                                                                                rating
                                                                                ),
                                                                },
                                                                ...starNodes( rating )
                                                  )
                                                : null,
                                el( RichText.Content, {
                                        tagName: 'blockquote',
                                        className: 'kbl3-testi__quote',
                                        value: a.quote,
                                } ),
                                el(
                                        'figcaption',
                                        { className: 'kbl3-testi__who' },
                                        a.avatarURL
                                                ? el( 'img', { className: 'kbl3-testi__avatar', src: a.avatarURL, alt: '' } )
                                                : null,
                                        el( RichText.Content, {
                                                tagName: 'span',
                                                className: 'kbl3-testi__name',
                                                value: a.name,
                                        } ),
                                        el( RichText.Content, {
                                                tagName: 'span',
                                                className: 'kbl3-testi__role',
                                                value: a.role,
                                        } )
                                )
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
