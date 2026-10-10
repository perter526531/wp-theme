/**
 * kbl/video — static save.
 *
 * v4: when a uid exists the responsive attributes (width / mt / mb with
 * their T / M variants) ride the per-instance <style> tag appended as a
 * SIBLING after the cover wrapper. The sheet emits real properties on the
 * uid root (the width beats the .kbl3-video width:100% skin by source
 * order). Legacy content without a uid saves byte-identically to v3 — the
 * width control never had a v3 inline emission.
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { uidClass, respStyleEl, v4Strip } from '../../src/kit.js';

import {
        videoProvider,
        videoClass,
        videoStyle,
        RESP_SPEC,
        STRIP_KEYS,
} from './edit.js';

export function Save( { attributes } ) {
                const a = attributes;
                const legacy = v4Strip( attributes, STRIP_KEYS );

                const data = JSON.stringify( { provider: videoProvider( a.url ), src: a.url || '' } );

                return el(
                        Fr,
                        null,
                        el(
                                'div',
                                {
                                        ...useBlockProps.save( { className: videoClass( legacy ), style: videoStyle( legacy ), id: a.anchor || undefined } ),
                                        'data-kbl-video': data,
                                },
                                el(
                                        'button',
                                        {
                                                className: 'kbl3-video__cover',
                                                type: 'button',
                                                'aria-label': __( 'Play video', 'kabel-blocks' ),
                                        },
                                        a.coverURL
                                                ? el( 'img', { className: 'kbl3-video__img', src: a.coverURL, alt: '' } )
                                                : null,
                                        el(
                                                'span',
                                                { className: 'kbl3-video__play' },
                                                el(
                                                        'svg',
                                                        { viewBox: '0 0 24 24', fill: 'currentColor', 'aria-hidden': true },
                                                        el( 'path', { d: 'M8 5.5v13l11-6.5z' } )
                                                )
                                        )
                                )
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
