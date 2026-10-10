/**
 * kbl/team — static save.
 *
 * v4: when a uid exists the responsive attributes (fontSize / columns /
 * mt / mb with their T / M variants) ride the per-instance <style> tag
 * appended as a SIBLING after the grid — never inside it (the name / role
 * html sources would swallow the style markup). The inline
 * --kbl3-team-cols variable is dropped from the stripped copy (it would
 * beat every media-query override). Legacy content without a uid saves
 * byte-identically to v3.
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import { useBlockProps, RichText, MediaUpload } from '@wordpress/block-editor';
import { PanelBody, Button } from '@wordpress/components';
import { sanitizeClass, cx, toStyle, iconSvg, applyVisibility, applySpacing, spacingPairs, applyAnim, animDelayPairs, typographyPairs } from '../../src/util.js';
import { uidClass, respStyleEl, v4Strip } from '../../src/kit.js';

import {
        DEFAULT_ITEMS,
        teamClass,
        teamStyle,
        RESP_SPEC,
        STRIP_KEYS,
} from './edit.js';

export function Save( { attributes } ) {
                const a = attributes;
                const items = a.items || [];
                const legacy = v4Strip( attributes, STRIP_KEYS );

                return el(
                        Fr,
                        null,
                        el(
                                'div',
                                useBlockProps.save( { className: teamClass( legacy, items ), style: teamStyle( legacy ), id: a.anchor || undefined } ),
                                items.map( ( item, i ) =>
                                        el(
                                                'div',
                                                { className: 'kbl3-team__card', key: i },
                                                el(
                                                        'div',
                                                        { className: 'kbl3-team__photo' },
                                                        item.photoURL
                                                                ? el( 'img', {
                                                                                className: 'kbl3-team__img',
                                                                                src: item.photoURL,
                                                                                alt: item.name || '',
                                                                  } )
                                                                : el(
                                                                                'span',
                                                                                {
                                                                                                className: 'kbl3-icon kbl3-team__phicon',
                                                                                                style: toStyle( [ [ '--kbl3-icon-size', '44px' ] ] ),
                                                                                },
                                                                                iconSvg( 'user' )
                                                                  )
                                                ),
                                                item.linkURL
                                                        ? el(
                                                                        'a',
                                                                        { className: 'kbl3-team__link', href: item.linkURL },
                                                                        el( RichText.Content, {
                                                                                tagName: 'h3',
                                                                                className: 'kbl3-team__name',
                                                                                value: item.name,
                                                                        } )
                                                          )
                                                        : el( RichText.Content, {
                                                                        tagName: 'h3',
                                                                        className: 'kbl3-team__name',
                                                                        value: item.name,
                                                          } ),
                                                el( RichText.Content, {
                                                        tagName: 'p',
                                                        className: 'kbl3-team__role',
                                                        value: item.role,
                                                } )
                                        )
                                )
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
