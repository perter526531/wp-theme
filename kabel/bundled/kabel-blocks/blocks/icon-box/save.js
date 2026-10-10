/**
 * kbl/icon-box — static save.
 *
 * v4: when a uid exists the responsive attributes (iconSize / mt / mb with
 * their T / M variants) ride the per-instance <style> tag appended as a
 * SIBLING after the card — never inside it (the title / text html sources
 * would swallow the style markup). The --kbl-is variable is consumed by the
 * child .kbl3-ibox__icon svg rule; the legacy inline --kbl3-ibox-ic is
 * dropped from the stripped copy. Legacy content without a uid saves
 * byte-identically to v3.
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import {
        useBlockProps,
        RichText,
} from '@wordpress/block-editor';
import {
        cx,
        iconSvg,
        applyAnim,
        applyVisibility,
        applySpacing,
        sanitizeClass,
} from '../../src/util.js';
import { uidClass, respStyleEl, v4Strip } from '../../src/kit.js';

import {
        iconStyle,
        boxStyle,
        RESP_SPEC,
        STRIP_KEYS,
} from './edit.js';

export function Save( { attributes } ) {
                const a = attributes;
                const legacy = v4Strip( attributes, STRIP_KEYS );

                const className = cx(
                        'kbl3-ibox',
                        a.layout === 'h' && 'kbl3-ibox--h',
                        a.layout === 'hr' && 'kbl3-ibox--hr',
                        a.iconValign === 'center' && 'kbl3-ibox--mid',
                        a.align === 'center' && 'is-center',
                        a.hoverLift && 'kbl3-hover--lift',
                        uidClass( attributes ),
                        ...applyAnim( a ),
                        ...applyVisibility( a ),
                        ...applySpacing( legacy ),
                        sanitizeClass( a.customClass )
                );

                return el(
                        Fr,
                        null,
                        el(
                                'div',
                                useBlockProps.save( { className, style: boxStyle( legacy ), id: a.anchor || undefined } ),
                                el(
                                        'span',
                                        {
                                                className: cx( 'kbl3-icon', 'kbl3-ibox__icon', a.iconBg && 'kbl3-ibox__icon--chip' ),
                                                style: iconStyle( a ),
                                        },
                                        iconSvg( a.iconName )
                                ),
                                el(
                                        'div',
                                        { className: 'kbl3-ibox__body' },
                                        el( RichText.Content, {
                                                tagName: 'h4',
                                                className: 'kbl3-ibox__title',
                                                value: a.title,
                                        } ),
                                        el( RichText.Content, {
                                                tagName: 'p',
                                                className: 'kbl3-ibox__text',
                                                value: a.text,
                                        } )
                                )
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
