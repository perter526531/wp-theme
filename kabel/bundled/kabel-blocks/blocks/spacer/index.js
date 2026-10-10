/**
 * kbl/spacer — registration glue.
 *
 * block.json is the single source of truth: PHP registers this very file's
 * metadata via register_block_type( $dir ) and the editor imports it here.
 */
import metadata from './block.json';
import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { Edit } from './edit.js';
import { Save } from './save.js';

registerBlockType( metadata.name, {
	...metadata,
	title: __( metadata.title, 'kabel-blocks' ),
	description: __( metadata.description, 'kabel-blocks' ),
	edit: Edit,
	save: Save,
} );
