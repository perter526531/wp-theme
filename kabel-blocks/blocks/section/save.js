/**
 * kbl/section — static save.
 *
 * v4: when a uid exists the responsive attributes (pt / pb / minH / mt /
 * mb with their T / M variants, plus the migrating padTop / padBottom and
 * the mobile padM) ride the per-instance <style> tag appended as a
 * SIBLING after the section — never inside it (the InnerBlocks content
 * would swallow it). The legacy pair helpers received the stripped copy so
 * the old inline paddings and utility classes are not emitted for them.
 * Legacy content without a uid saves byte-identically to v3.
 */
import { el, Fr } from '@wordpress/element';
import {
        useBlockProps,
        useInnerBlocksProps,
        InnerBlocks,
} from '@wordpress/block-editor';
import {
        dividerSvg,
        toStyle,
} from '../../src/util.js';
import { respStyleEl, v4Strip } from '../../src/kit.js';

import {
        containerClass,
        sectionClass,
        sectionStyle,
        RESP_SPEC,
        STRIP_KEYS,
        sectionRespAttrs,
} from './edit.js';

export function Save( { attributes } ) {
                const { anchor, width, widthPx, divTop, divBottom, divColor } = attributes;
                const legacy = v4Strip( attributes, STRIP_KEYS );
                const resp = sectionRespAttrs( attributes );

                return el(
                        Fr,
                        null,
                        el(
                                'section',
                                useBlockProps.save( { className: sectionClass( legacy ), style: sectionStyle( legacy ), id: anchor || undefined } ),
                                divTop ? dividerSvg( 'top', divTop, divColor ) : null,
                                el(
                                        'div',
                                        {
                                                className: containerClass( width, widthPx ),
                                                style: widthPx != null ? toStyle( [ [ '--kbl3-cw', `${ widthPx }px` ] ] ) : undefined,
                                        },
                                        el( InnerBlocks.Content )
                                ),
                                divBottom ? dividerSvg( 'bottom', divBottom, divColor ) : null
                        ),
                        respStyleEl( resp, RESP_SPEC )
                );
        }
