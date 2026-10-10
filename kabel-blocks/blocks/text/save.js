/**
 * kbl/text — static save.
 *
 * v4: when a uid exists the responsive attributes (fontSize / mt / mb
 * with their T / M variants) ride the per-instance <style> tag appended
 * as the last child; the legacy pair helpers received the stripped copy
 * so the old --kbl3-* inline variables and utility classes are not
 * emitted for them (they would win the cascade and kill the media
 * queries). Legacy content without a uid saves byte-identically to v3.
 */
import { el, Fr } from '@wordpress/element';
import {
        useBlockProps,
        RichText,
} from '@wordpress/block-editor';
import { sanitizeClass, cx, toStyle, applyVisibility, applySpacing, spacingPairs, typographyPairs, boxPairs } from '../../src/util.js';
import { uidClass, respStyleEl, v4Strip } from '../../src/kit.js';

import {
        RESP_SPEC,
        STRIP_KEYS,
} from './edit.js';

export function Save( { attributes } ) {
                const { text, size, align, color, lead, anchor } = attributes;
                const legacy = v4Strip( attributes, STRIP_KEYS );

                const className = cx( 'kbl3-text', `kbl3-text--${ size }`, lead && 'is-lead', uidClass( attributes ), ...applyVisibility( attributes ), ...applySpacing( legacy ), sanitizeClass( attributes.customClass ) );
                const style = toStyle( [
                        [ 'text-align', align ],
                        [ 'color', color ],
                        ...boxPairs( legacy ),
                        ...typographyPairs( legacy ),
                        ...spacingPairs( legacy ),
                ] );

                /* The per-instance <style> rides as a SIBLING after the paragraph —
                 * never inside it: the text attribute's html source (selector p)
                 * would otherwise swallow the style markup into the stored text. */
                return el(
                        Fr,
                        null,
                        el(
                                'p',
                                useBlockProps.save( { className, style, id: anchor || undefined } ),
                                el( RichText.Content, { value: text } )
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
