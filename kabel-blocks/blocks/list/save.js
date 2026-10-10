/**
 * kbl/list — static save.
 *
 * v4: when a uid exists the responsive attributes (fontSize / mt / mb with
 * their T / M variants) ride the per-instance <style> tag appended as a
 * SIBLING after the <ul> — never inside it (the values html source would
 * swallow the style markup). Legacy content without a uid saves
 * byte-identically to v3.
 */
/* eslint-disable -- generated split (v4 migration); hand-tuned afterwards. */
import { el, Fr } from '@wordpress/element';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { sanitizeClass, cx, toStyle, applyVisibility, applySpacing, spacingPairs } from '../../src/util.js';
import { uidClass, respStyleEl, v4Strip } from '../../src/kit.js';

import {
        RESP_SPEC,
        STRIP_KEYS,
} from './edit.js';

export function Save( { attributes } ) {
                const { values, marker, color, anchor } = attributes;
                const legacy = v4Strip( attributes, STRIP_KEYS );

                const className = cx( 'kbl3-list', `kbl3-list--${ marker }`, uidClass( attributes ), ...applyVisibility( attributes ), ...applySpacing( legacy ), sanitizeClass( attributes.customClass ) );
                const style = toStyle( [ [ 'color', color ], ...spacingPairs( legacy ) ] );

                return el(
                        Fr,
                        null,
                        el(
                                'ul',
                                useBlockProps.save( { className, style, id: anchor || undefined } ),
                                el( RichText.Content, { value: values, multiline: 'li' } )
                        ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
