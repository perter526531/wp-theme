<?php
/**
 * Theme-side render fallback for the Kabel Blocks engine.
 *
 * A visitor (or a non-technical owner) who installs ONLY the theme used to
 * get blank pages: the Kabel pages are built from `<!-- wp:kabel-blocks/…
 * -->` markup, and without the plugin no block type is registered, so the
 * block comments render as nothing.
 *
 * This module makes that failure mode impossible. When the real plugin is
 * absent, the theme boots the RENDER LAYER from the copy bundled at
 * `bundled/kabel-blocks/` — just the interpreter (inc/engine.php), which
 * registers every block type on init and holds all render helpers. The
 * admin/editor modules (layouts, patterns, seeder, bootstrap, assets) stay
 * unloaded: they belong to the real plugin, and the admin notice from
 * plugin-guide.php offers a one-click activation instead.
 *
 * Damage control: every file this boot path can ever require is checked for
 * existence BEFORE anything is loaded. A require of a missing file is an
 * uncatchable E_COMPILE_ERROR (no try/catch can intercept it), so the
 * pre-check is the real shield — a broken or partial bundled copy makes this
 * whole function a no-op and the site degrades to raw block comments
 * (unstyled text), never a white screen.
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Is the theme currently substituting its bundled copy of the engine?
 *
 * True only when the bundled render layer actually booted in this request.
 * While it is true the front end is fully rendered, but `kbl_specs()` and
 * friends come from the theme's copy — "safe mode", not the real plugin.
 */
function kabel_blocks_safe_mode(): bool {
	return defined( 'KABEL_BLOCKS_FALLBACK' ) && KABEL_BLOCKS_FALLBACK;
}

/**
 * Boot the bundled render layer when the real plugin is absent.
 *
 * Runs at after_setup_theme priority 20: the plugin boots at priority 5,
 * so by the time we get here we know for certain whether the real engine
 * is loaded.
 */
function kabel_blocks_fallback_boot(): void {
	if ( function_exists( 'kbl_specs' ) ) {
		return; // The real Kabel Blocks plugin is active — nothing to do.
	}

	$dir = KABEL_DIR . '/bundled/kabel-blocks/';
	if ( ! is_dir( $dir ) ) {
		return; // No bundled copy — nothing we can do.
	}

	/*
	 * Point the engine at the bundled copy. Guarded: whenever the real
	 * plugin is loaded, IT owns these constants (its bootstrap defines
	 * them at include time, before this runs).
	 */
	if ( ! defined( 'KABEL_BLOCKS_DIR' ) ) {
		define( 'KABEL_BLOCKS_DIR', $dir );
	}
	if ( ! defined( 'KABEL_BLOCKS_URI' ) ) {
		define( 'KABEL_BLOCKS_URI', KABEL_URI . '/bundled/kabel-blocks/' );
	}

	/*
	 * Pre-flight EVERY file this boot can require. kbl_specs() lazily
	 * pulls the three spec files from KABEL_BLOCKS_DIR, so those are
	 * checked against the constant (not $dir) — exactly the paths the
	 * engine will hit. A missing OR empty file aborts the fallback
	 * before any require can throw an uncatchable compile error.
	 */
	$needed = array(
		$dir . 'inc/engine.php',
		KABEL_BLOCKS_DIR . 'inc/blocks-content.php',
		KABEL_BLOCKS_DIR . 'inc/blocks-proof.php',
		KABEL_BLOCKS_DIR . 'inc/blocks-convert.php',
	);
	foreach ( $needed as $file ) {
		if ( ! is_file( $file ) || ! is_readable( $file ) || 0 === filesize( $file ) ) {
			return; // Damaged bundled copy — degrade quietly.
		}
	}

	try {
		require_once $dir . 'inc/engine.php';
	} catch ( \Throwable $e ) {
		return; // A broken bundled engine must never take the site down.
	}

	if ( ! function_exists( 'kbl_specs' ) || ! function_exists( 'kbl_register_blocks' ) ) {
		return; // The bundled copy is not the engine we expect.
	}

	try {
		/*
		 * Prime the spec registry NOW, inside the shield. kbl_specs()
		 * requires the three spec files, so a parse error in any of them
		 * surfaces here instead of fataling later on init.
		 */
		kbl_specs();
	} catch ( \Throwable $e ) {
		// Disarm the engine's own init hook — kbl_register_blocks() would
		// re-run kbl_specs() and hit the same broken file again.
		remove_action( 'init', 'kbl_register_blocks', 20 );
		return;
	}

	if ( empty( kbl_specs() ) ) {
		remove_action( 'init', 'kbl_register_blocks', 20 );
		return; // Specs registered nothing — treat as damaged copy.
	}

	define( 'KABEL_BLOCKS_FALLBACK', true );
}
add_action( 'after_setup_theme', 'kabel_blocks_fallback_boot', 20 );
