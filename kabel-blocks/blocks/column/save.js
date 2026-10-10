/**
 * kbl/column — static save.
 *
 * v4: no responsive attributes of its own — the uid class joins the list
 * after the preset classes, and the per-instance scoped custom CSS (when
 * any) rides as a SIBLING <style> after the column (never inside it: the
 * InnerBlocks content would swallow it). Without a uid the output is
 * byte-identical to v3.
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import {
        useBlockProps,
        InnerBlocks,
} from '@wordpress/block-editor';
import {
        cx,
        applyVisibility,
        applySpacing,
        applyAnim,
        sanitizeClass,
} from '../../src/util.js';
import { uidClass, respStyleEl } from '../../src/kit.js';

import {
        colStyle,
} from './edit.js';

export function Save( { attributes } ) {
                const { anchor, bgURL, contentAlign, width } = attributes;
                const className = cx(
                        'kbl3-col',
                        width != null && 'kbl3-col--w',
                        contentAlign === 'center' && 'is-center',
                        contentAlign === 'right' && 'is-right',
                        bgURL && 'has-bgimg',
                        uidClass( attributes ),
                        ...applyAnim( attributes ),
                        ...applyVisibility( attributes ),
                        ...applySpacing( attributes ),
                        sanitizeClass( attributes.customClass )
                );
                return el(
                        Fr,
                        null,
                        el(
                                'div',
                                useBlockProps.save( { className, style: colStyle( attributes ), id: anchor || undefined } ),
                                el( InnerBlocks.Content )
                        ),
                        respStyleEl( attributes, [] )
                );
        }
