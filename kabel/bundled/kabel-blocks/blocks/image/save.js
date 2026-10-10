/**
 * kbl/image — static save.
 *
 * v4: when a uid exists the responsive attributes (width % / mt / mb with
 * their T / M variants) ride the per-instance <style> tag appended as a
 * SIBLING after the figure — never inside it (the caption's html source
 * would swallow the style markup). The inline percent width is dropped; the
 * pixel width (widthPx) keeps its inline slot so custom pixel widths still
 * win. Legacy content without a uid saves byte-identically to v3.
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import {
        useBlockProps,
        RichText,
} from '@wordpress/block-editor';
import { sanitizeClass, cx, toStyle, applyVisibility, applySpacing, spacingPairs, applyAnim, animDelayPairs } from '../../src/util.js';
import { uidClass, respStyleEl, v4Strip } from '../../src/kit.js';

import {
        RESP_SPEC,
        STRIP_KEYS,
} from './edit.js';

export function Save( { attributes } ) {
                const { anchor, url, alt, caption, width, widthPx, heightPx, ratio, radius, align, linkURL } = attributes;
                const legacy = v4Strip( attributes, STRIP_KEYS );

                const figureStyle = toStyle( [
                        /* v4: widthPx keeps its inline slot (a custom pixel width
                         * still wins); the percent width rides the sheet. */
                        [ 'width', widthPx ? `${ widthPx }px` : ( attributes.uid ? null : ( width ? `${ width }%` : null ) ) ],
                        [ 'margin-left', align === 'center' || align === 'right' ? 'auto' : null ],
                        [ 'margin-right', align === 'center' ? 'auto' : null ],
                        ...animDelayPairs( attributes ),
                        ...spacingPairs( legacy ),
                ] );
                const imgPairs = [
                        [ 'border-radius', radius ? `${ radius }px` : null ],
                ];
                if ( ratio === 'custom' ) {
                        imgPairs.push( [ 'width', widthPx ? `${ widthPx }px` : null ] );
                        imgPairs.push( [ 'height', heightPx ? `${ heightPx }px` : null ] );
                } else if ( ratio ) {
                        imgPairs.push( [ 'aspect-ratio', ratio.replace( '-', ' / ' ) ] );
                        imgPairs.push( [ 'object-fit', 'cover' ] );
                        imgPairs.push( [ 'width', '100%' ] );
                }
                const imgStyle = toStyle( imgPairs );

                const img = el( 'img', {
                        className: 'kbl3-img',
                        src: url,
                        alt: alt || '',
                        style: imgStyle,
                } );

                return el(
                        Fr,
                        null,
                        el(
                                'figure',
                                useBlockProps.save( {
                                        className: cx( 'kbl3-fig', widthPx ? 'kbl3-fig--px' : '', uidClass( attributes ), ...applyAnim( attributes ), ...applyVisibility( attributes ), ...applySpacing( legacy ), sanitizeClass( attributes.customClass ) ),
                                        style: figureStyle,
                                        id: anchor || undefined,
                                } ),
                                linkURL ? el( 'a', { className: 'kbl3-fig__link', href: linkURL }, img ) : img,
                                caption ?
                                        el( RichText.Content, {
                                                tagName: 'figcaption',
                                                className: 'kbl3-figcap',
                                                value: caption,
                                        } ) :
                                        null
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
