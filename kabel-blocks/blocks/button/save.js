/**
 * kbl/button — static save.
 *
 * v4: when a uid exists the responsive attributes (fontSize / padX / mt / mb
 * with their T / M variants) ride the per-instance <style> tag appended as a
 * SIBLING after the wrap — never inside it (the text attribute's html source
 * on the <a> would swallow the style markup). The uid class sits on the root
 * .kbl3-btnwrap; the legacy pair helpers on the <a> received the stripped
 * copy so the old --kbl3-* inline variables and utility classes are not
 * emitted for them. Legacy content without a uid saves byte-identically
 * to v3.
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import {
        useBlockProps,
        RichText,
} from '@wordpress/block-editor';
import { sanitizeClass, cx, toStyle, applyVisibility, applySpacing, spacingPairs, typographyPairs } from '../../src/util.js';
import { uidClass, respStyleEl, v4Strip } from '../../src/kit.js';

import {
        VARIANT_OPTIONS,
        RESP_SPEC,
        STRIP_KEYS,
} from './edit.js';

export function Save( { attributes } ) {
                const {
                        text, url, newTab, rfq, rfqProduct, variant, size, radius, align, full,
                        bgColor, textColor, padY, borderWidth, borderColor, borderStyle, anchor,
                } = attributes;
                const legacy = v4Strip( attributes, STRIP_KEYS );

                const btnClass = cx(
                        'kbl3-btn',
                        `kbl3-btn--${ variant }`,
                        `kbl3-btn--${ size }`,
                        full && 'kbl3-btn--full',
                        rfq && ! url && 'kb-rfq-open',
                        attributes.hoverLift && 'kbl3-hover--lift',
                        attributes.hoverBg && 'kbl3-has-hover-bg',
                        attributes.hoverText && 'kbl3-has-hover-tx',
                        ...applyVisibility( attributes ),
                        ...applySpacing( legacy ),
                        sanitizeClass( attributes.customClass )
                );
                const btnStyle = toStyle( [
                        [ 'border-radius', radius ? `${ radius }px` : null ],
                        [ '--kbl3-btn-px', legacy.padX != null ? `${ legacy.padX }px` : null ],
                        [ '--kbl3-btn-py', legacy.padY != null ? `${ legacy.padY }px` : null ],
                        [ 'border', borderColor && borderWidth ? `${ borderWidth }px ${ borderStyle || 'solid' } ${ borderColor }` : null ],
                        [ 'background-color', bgColor ],
                        [ 'color', textColor ],
                        ...typographyPairs( legacy, { lh: false, ls: false, tt: false } ),
                        [ '--kbl3-h-bg', attributes.hoverBg ],
                        [ '--kbl3-h-tx', attributes.hoverText ],
                        ...spacingPairs( legacy ),
                ] );

                const linkProps = url
                        ? { href: url, ...( newTab ? { target: '_blank', rel: 'noopener noreferrer' } : {} ) }
                        : rfq
                                ? { href: '#kb-rfq', ...( rfqProduct ? { 'data-kb-product': rfqProduct } : {} ) }
                                : {};

                return el(
                        Fr,
                        null,
                        el(
                                'div',
                                useBlockProps.save( {
                                        className: cx( 'kbl3-btnwrap', uidClass( attributes ) ),
                                        style: toStyle( [ [ 'text-align', align ] ] ),
                                        id: anchor || undefined,
                                } ),
                                el(
                                        'a',
                                        { className: btnClass, style: btnStyle, ...linkProps },
                                        el( RichText.Content, { value: text } )
                                )
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
