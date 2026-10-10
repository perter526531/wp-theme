/**
 * kbl/card — static save.
 *
 * v4: when a uid exists the responsive attributes (padding / mt / mb with
 * their T / M variants) ride the per-instance <style> tag appended as a
 * SIBLING after the card — never inside it (the title / text html sources
 * would swallow the style markup). The sheet emits real properties: the
 * margins on the uid root, the padding on the .kbl3-card__body descendant;
 * the inline body padding is dropped (it would beat every media query).
 * Legacy content without a uid saves byte-identically to v3.
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import {
        useBlockProps,
        RichText,
} from '@wordpress/block-editor';
import { cx } from '../../src/util.js';
import { uidClass, respStyleEl, v4Strip } from '../../src/kit.js';

import {
        cardClass,
        cardStyle,
        cardBodyStyle,
        RESP_SPEC,
        STRIP_KEYS,
} from './edit.js';

export function Save( { attributes } ) {
                const a = attributes;
                const legacy = v4Strip( attributes, STRIP_KEYS );

                const bodyStyle = cardBodyStyle( legacy );
                const btnClass = cx(
                        'kbl3-btn',
                        `kbl3-btn--${ a.btnStyle === 'outline' ? 'outline' : 'gold' }`,
                        'kbl3-btn--s',
                        'kbl3-card__btn'
                );

                return el(
                        Fr,
                        null,
                        el(
                                'div',
                                useBlockProps.save( { className: cardClass( legacy ), style: cardStyle( legacy ), id: a.anchor || undefined } ),
                                a.mediaURL
                                        ? el(
                                                        'figure',
                                                        { className: 'kbl3-card__media' },
                                                        el( 'img', { className: 'kbl3-card__img', src: a.mediaURL, alt: '' } )
                                          )
                                        : null,
                                el(
                                        'div',
                                        { className: 'kbl3-card__body', style: bodyStyle },
                                        el( RichText.Content, {
                                                tagName: 'h3',
                                                className: 'kbl3-card__title',
                                                value: a.title,
                                        } ),
                                        el( RichText.Content, {
                                                tagName: 'p',
                                                className: 'kbl3-card__text',
                                                value: a.text,
                                        } ),
                                        a.btnLabel
                                                ? el(
                                                                'a',
                                                                {
                                                                                className: btnClass,
                                                                                ...( a.btnURL ? { href: a.btnURL } : {} ),
                                                                },
                                                                el( RichText.Content, { value: a.btnLabel } )
                                                  )
                                                : null
                                )
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
