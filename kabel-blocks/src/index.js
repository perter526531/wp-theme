/**
 * Kabel Blocks v3 — editor entry.
 *
 * Registers the "Kabel Blocks" collection (grouped at the top of the
 * inserter), then every block. Legacy v1/v2 blocks (kabel-blocks/*) keep
 * rendering through PHP but disappear from the inserter.
 */
import { el } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
        registerBlockCollection,
        getBlockType,
        unregisterBlockType,
} from '@wordpress/blocks';

import './util.js';
import './blocks/section.js';
import './blocks/columns.js';
import './blocks/column.js';
import './blocks/heading.js';
import './blocks/text.js';
import './blocks/button.js';
import './blocks/button-group.js';
import './blocks/image.js';
import './blocks/icon.js';
import './blocks/icon-box.js';
import './blocks/list.js';
import './blocks/spacer.js';
import './blocks/separator.js';
import './blocks/stat.js';
import './blocks/accordion.js';
import './blocks/card.js';
import './blocks/tabs.js';
import './blocks/testimonial.js';
import './blocks/video.js';
import './blocks/team.js';
import './blocks/pricing.js';
import './blocks/inquiry-form.js';
import './blocks/post-grid.js';
import './blocks/logo.js';
import './blocks/nav-menu.js';
import './blocks/search.js';
import './blocks/language-switcher.js';
import './blocks/contact-info.js';

/* Collection: groups every kbl/* block under one titled header in the
 * inserter's browse list. */
registerBlockCollection( 'kbl', {
        title: __( 'Kabel Blocks', 'kabel-blocks' ),
        icon: el(
                'svg',
                { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: 1.8 },
                el( 'path', { d: 'M4.5 4.5h7v7h-7z' } ),
                el( 'path', { d: 'M4.5 15.5h4v4h-4z' } ),
                el( 'path', { d: 'M13.5 4.5h6' } ),
                el( 'path', { d: 'M13.5 8.5h6' } )
        ),
} );

/* Legacy v1/v2 blocks: PHP keeps them rendering, but if any editor script
 * still client-registers them, remove them from the inserter. */
const LEGACY_BLOCKS = [
        'kabel-blocks/hero',
        'kabel-blocks/split',
        'kabel-blocks/features',
        'kabel-blocks/stats',
        'kabel-blocks/process',
        'kabel-blocks/milestones',
        'kabel-blocks/gallery',
        'kabel-blocks/logos',
        'kabel-blocks/testimonials',
        'kabel-blocks/faq',
        'kabel-blocks/products',
        'kabel-blocks/cases',
        'kabel-blocks/inquiry',
        'kabel-blocks/contact',
        'kabel-blocks/cta',
        'kabel-blocks/cta-quote',
        'kabel-blocks/section',
        'kabel-blocks/specs',
];

for ( const name of LEGACY_BLOCKS ) {
        try {
                if ( getBlockType( name ) ) {
                        unregisterBlockType( name );
                }
        } catch ( e ) {
                // Not registered — nothing to do.
        }
}
