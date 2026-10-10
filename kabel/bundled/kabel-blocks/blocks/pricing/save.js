/**
 * kbl/pricing — static save.
 *
 * v4: when a uid exists the responsive attributes (fontSize / mt / mb with
 * their T / M variants) ride the per-instance <style> tag appended as a
 * SIBLING after the card — never inside it (the planName / price / period
 * html sources would swallow the style markup). The font size keeps the
 * --kbl3-fs chain (the shared CSS consumes it at every breakpoint); the
 * inline variables are dropped from the stripped copy. Legacy content
 * without a uid saves byte-identically to v3.
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useBlockProps, RichText, BlockControls } from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton, PanelBody, Button } from '@wordpress/components';
import { sanitizeClass, cx, toStyle, shadowValue, applyVisibility, applySpacing, spacingPairs, applyAnim, animDelayPairs, typographyPairs } from '../../src/util.js';
import { uidClass, respStyleEl, v4Strip } from '../../src/kit.js';

import {
        DEFAULT_FEATURES,
        priceClass,
        priceStyle,
        RESP_SPEC,
        STRIP_KEYS,
} from './edit.js';

export function Save( { attributes } ) {
                const a = attributes;
                const features = a.features || [];
                const legacy = v4Strip( attributes, STRIP_KEYS );

                return el(
                        Fr,
                        null,
                        el(
                                'div',
                                useBlockProps.save( { className: priceClass( legacy ), style: priceStyle( legacy ), id: a.anchor || undefined } ),
                                a.featured
                                        ? el( 'span', { className: 'kbl3-price__badge' }, __( 'Most popular', 'kabel-blocks' ) )
                                        : null,
                                el( RichText.Content, {
                                        tagName: 'h3',
                                        className: 'kbl3-price__plan',
                                        value: a.planName,
                                } ),
                                el(
                                        'div',
                                        { className: 'kbl3-price__amount' },
                                        el( RichText.Content, {
                                                tagName: 'span',
                                                className: 'kbl3-price__num',
                                                value: a.price,
                                        } ),
                                        el( RichText.Content, {
                                                tagName: 'span',
                                                className: 'kbl3-price__period',
                                                value: a.period,
                                        } )
                                ),
                                el(
                                        'ul',
                                        { className: 'kbl3-list kbl3-list--check kbl3-price__features' },
                                        features.map( ( feature, i ) => el( 'li', { key: i }, feature ) )
                                ),
                                a.btnLabel
                                        ? el(
                                                        'a',
                                                        {
                                                                        className: 'kbl3-btn kbl3-btn--gold kbl3-btn--s kbl3-price__btn',
                                                                        ...( a.btnURL ? { href: a.btnURL } : {} ),
                                                        },
                                                        el( RichText.Content, { value: a.btnLabel } )
                                          )
                                        : null,
                                el( RichText.Content, {
                                        tagName: 'p',
                                        className: 'kbl3-price__foot',
                                        value: a.footnote,
                                } )
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
