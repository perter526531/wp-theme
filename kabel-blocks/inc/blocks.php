<?php
/**
 * Kabel Blocks v4 — the block registration layer.
 *
 * Since v4 every block lives in its own blocks/{slug}/ folder and its
 * block.json is the single source of truth for BOTH sides:
 *
 *   - PHP: this file registers every blocks/{slug}/block.json through
 *     register_block_type( $dir ) on init. Static blocks need nothing else;
 *     the dynamic pair (inquiry-form / post-grid) carries
 *     "render": "file:./render.php" in its metadata, which WordPress wires
 *     to a render callback automatically (template signature:
 *     $attributes / $content / $block).
 *   - JS: blocks/{slug}/index.js imports the same block.json and registers
 *     edit/save client-side (assets/js/blocks.js).
 *
 * The shared script/style handles (kabel-blocks-v3 / kabel-blocks-v3-editor)
 * referenced by the metadata are registered by inc/assets.php on init 15.
 * Legacy v1/v2 names (kabel-blocks/*) keep their own registration in
 * inc/engine.php — no overlap with the kbl/* namespace.
 *
 * @package Kabel_Blocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register every blocks/{slug}/ block.json metadata as a block type.
 *
 * Runs at init 20 (after the asset handles at init 15 — block.json's
 * "editorScript"/"style" fields reference them by handle).
 */
function kabel_blocks_register_block_types(): void {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	$metadata_files = glob( KABEL_BLOCKS_DIR . 'blocks/*/block.json' );
	if ( ! is_array( $metadata_files ) || array() === $metadata_files ) {
		return;
	}

	sort( $metadata_files ); // Deterministic order: accordion → video.

	foreach ( $metadata_files as $metadata_file ) {
		/*
		 * register_block_type() accepts the block folder and reads its
		 * block.json. A folder without valid metadata returns a WP_Error
		 * (with a _doing_it_wrong notice) — never fatal, so a broken
		 * folder degrades to "block missing" instead of taking the
		 * site down.
		 */
		register_block_type( dirname( $metadata_file ) );
	}
}
add_action( 'init', 'kabel_blocks_register_block_types', 20 );
