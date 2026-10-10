/**
 * kbl/columns — registration glue.
 *
 * block.json is the single source of truth: PHP registers this very file's
 * metadata via register_block_type( $dir ) and the editor imports it here.
 */
import metadata from './block.json';
import { __ } from '@wordpress/i18n';
import { registerBlockType, registerBlockVariation } from '@wordpress/blocks';
import { Edit } from './edit.js';
import { Save } from './save.js';
import { LAYOUTS } from './edit.js';

registerBlockType( metadata.name, {
	...metadata,
	title: __( metadata.title, 'kabel-blocks' ),
	description: __( metadata.description, 'kabel-blocks' ),
	edit: Edit,
	save: Save,
} );

/* Register the insert-time variations AFTER the block type exists —
 * @wordpress/blocks' registerBlockVariation no-ops with a console error
 * when the parent block has not been registered yet. */
for ( const layout of LAYOUTS ) {
	if ( typeof registerBlockVariation === 'function' ) {
		registerBlockVariation( metadata.name, {
			name: layout.name,
			title: layout.title,
			description: layout.description,
			icon: layout.icon,
			scope: [ 'block' ],
			isDefault: !! layout.isDefault,
			innerBlocks: layout.blocks,
		} );
	}
}
