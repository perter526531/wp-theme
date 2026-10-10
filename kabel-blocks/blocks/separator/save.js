/**
 * kbl/separator — static save.
 *
 * v4: when a uid exists the responsive attributes (width / mt / mb with
 * their T / M variants) ride the per-instance <style> tag appended as a
 * SIBLING after the <hr>; the legacy inline width is dropped (it would
 * beat every media-query override). Legacy content without a uid saves
 * byte-identically to v3.
 */
import { el, Fr } from '@wordpress/element';
import { useBlockProps } from '@wordpress/block-editor';

import {
        sepProps,
        RESP_SPEC,
} from './edit.js';
import { respStyleEl } from '../../src/kit.js';

export function Save( { attributes } ) {
                return el(
                        Fr,
                        null,
                        el( 'hr', useBlockProps.save( sepProps( attributes ) ) ),
                        respStyleEl( attributes, RESP_SPEC )
                );
        }
