/**
 * kbl/tabs — static save.
 *
 * v4: when a uid exists the responsive attributes (fontSize / mt / mb with
 * their T / M variants) ride the per-instance <style> tag appended as a
 * SIBLING after the tabs root — never inside it (the item html sources
 * would swallow the style markup). The font size keeps the --kbl3-fs chain
 * (the shared CSS consumes it at every breakpoint); the inline variables
 * are dropped from the stripped copy. Legacy content without a uid saves
 * byte-identically to v3.
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr, useState } from '@wordpress/element';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { sanitizeClass, cx, toStyle, applyVisibility, applySpacing, spacingPairs, applyAnim, animDelayPairs, typographyPairs } from '../../src/util.js';
import { uidClass, respStyleEl, v4Strip } from '../../src/kit.js';

import {
        DEFAULT_ITEMS,
        RESP_SPEC,
        STRIP_KEYS,
} from './edit.js';

export function Save( { attributes } ) {
                const a = attributes;
                const items = a.items || [];
                const legacy = v4Strip( attributes, STRIP_KEYS );

                const className = cx( 'kbl3-tabs', uidClass( attributes ), ...applyAnim( a ), ...applyVisibility( a ), ...applySpacing( legacy ), sanitizeClass( a.customClass ) );
                const style = toStyle( [
                        [ '--kbl3-tabs-accent', a.accentColor ],
                        ...typographyPairs( legacy, { fw: false, lh: false, ls: false, tt: false } ),
                        ...animDelayPairs( legacy ),
                        ...spacingPairs( legacy ),
                ] );

                return el(
                        Fr,
                        null,
                        el(
                                'div',
                                {
                                        ...useBlockProps.save( { className, style, id: a.anchor || undefined } ),
                                        'data-kbl-tabs': '',
                                },
                                el(
                                        'div',
                                        { className: 'kbl3-tabs__nav', role: 'tablist' },
                                        items.map( ( item, i ) =>
                                                el(
                                                        'button',
                                                        {
                                                                key: i,
                                                                className: cx( 'kbl3-tabs__tab', i === 0 && 'is-active' ),
                                                                role: 'tab',
                                                                'aria-selected': i === 0 ? 'true' : 'false',
                                                                type: 'button',
                                                        },
                                                        el( RichText.Content, {
                                                                tagName: 'span',
                                                                className: 'kbl3-tabs__label',
                                                                value: item.title,
                                                        } )
                                                )
                                        )
                                ),
                                items.map( ( item, i ) =>
                                        el(
                                                'div',
                                                {
                                                        key: 'p' + i,
                                                        className: cx( 'kbl3-tabs__panel', i === 0 && 'is-active' ),
                                                        role: 'tabpanel',
                                                },
                                                el( RichText.Content, { value: item.content } )
                                        )
                                )
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
