<?php
/**
 * Page-cache purging for the editable site chrome.
 *
 * The Site Header / Site Footer parts live as private pages whose content is
 * the site-wide chrome. Two of our own write paths bypass wp_insert_post()
 * on purpose (importer-style $wpdb writes so block comments survive KSES):
 *
 *   - kabel_handle_apply_header_layout()  (Appearance → Header & Footer)
 *   - kabel_ensure_site_parts()           (initial seeding / re-seeding)
 *
 * Page-cache plugins listen for save_post to invalidate their caches — a
 * $wpdb->update never fires it, so after "Apply layout" the editor showed the
 * new header (logged in, cache bypassed) while anonymous visitors kept being
 * served the stale cached page ("I switched the layout but the site still
 * shows the old one"). This module flushes every mainstream cache layer at
 * the exact moments the chrome changes, and fires save_post-equivalent
 * cleanup for the REST saves of the two private pages as a belt-and-braces.
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Flush the page caches of every caching plugin we can detect.
 *
 * Guarded by a per-request static: a single admin request can hit several
 * write paths (admin_init seeding + layout POST), and LiteSpeed in
 * particular is not cheap to purge repeatedly. Always safe to call — every
 * branch checks the integration exists before touching it.
 *
 * @param string $reason Short slug for the kabel_purged_page_caches action.
 */
function kabel_flush_page_caches( string $reason = '' ): void {
	static $done = array();
	if ( isset( $done[ $reason ] ) ) {
		return;
	}
	$done[ $reason ] = true;

	/*
	 * LiteSpeed Cache (also what Hostinger ships). The official
	 * third-party-integration hook; falls back to the Purge class when the
	 * action has no listeners.
	 */
	if ( defined( 'LSCWP_V' ) || class_exists( '\LiteSpeed\Cache' ) || class_exists( 'LiteSpeed_Cache' ) ) {
		do_action( 'litespeed_purge_all' );
		if ( class_exists( '\LiteSpeed\Purge' ) && method_exists( '\LiteSpeed\Purge', 'purge_all' ) ) {
			\LiteSpeed\Purge::purge_all();
		} elseif ( class_exists( 'LiteSpeed_Cache' ) && method_exists( 'LiteSpeed_Cache', 'purge_all' ) ) {
			LiteSpeed_Cache::purge_all();
		}
	}

	/* WP Super Cache. */
	if ( function_exists( 'wp_cache_clear_cache' ) ) {
		wp_cache_clear_cache();
	}

	/* W3 Total Cache. */
	if ( function_exists( 'w3tc_flush_all' ) ) {
		w3tc_flush_all();
	}

	/* WP Rocket. */
	if ( function_exists( 'rocket_clean_domain' ) ) {
		rocket_clean_domain();
	}

	/* SiteGround Optimizer. */
	if ( function_exists( 'sg_cachepress_purge_cache' ) ) {
		sg_cachepress_purge_cache();
	}

	/* Cache Enabler. */
	if ( function_exists( 'cache_enabler_clear_cache' ) ) {
		cache_enabler_clear_cache();
	}

	/* WordPress object cache (nav/menu/options fragments). */
	if ( function_exists( 'wp_cache_flush' ) ) {
		wp_cache_flush();
	}

	/**
	 * Fires after Kabel flushed every cache layer it knows about — host-level
	 * or exotic setups can hook their own purge here.
	 *
	 * @param string $reason What changed: 'header_layout', 'site_parts', 'part_saved'.
	 */
	do_action( 'kabel_purged_page_caches', $reason );
}

/**
 * Purge after a layout is applied (called from the admin-post handler).
 */
function kabel_flush_after_layout_apply(): void {
	kabel_flush_page_caches( 'header_layout' );
}

/**
 * Purge after the site-part pages are seeded/reseeded.
 */
function kabel_flush_after_site_parts_seed(): void {
	kabel_flush_page_caches( 'site_parts' );
}

/**
 * Belt-and-braces for editor saves: REST updates of the two private part
 * pages fire save_post, and most cache plugins already react — but not all
 * react to PRIVATE posts (nothing public references them, so some caches
 * treat the save as a no-op). Purge again, deduplicated per request.
 *
 * @param int     $post_id Saved post ID.
 * @param WP_Post $post    The post.
 */
function kabel_flush_on_part_save( $post_id, $post ): void {
	if ( ! $post instanceof WP_Post || 'page' !== $post->post_type ) {
		return;
	}
	if ( ! in_array( $post->post_name, array( 'kabel-header', 'kabel-footer' ), true ) ) {
		return;
	}
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	kabel_flush_page_caches( 'part_saved' );
}
add_action( 'save_post_page', 'kabel_flush_on_part_save', 20, 2 );

/**
 * Purge once after a theme switch (chrome + assets change wholesale).
 */
function kabel_flush_after_switch_theme(): void {
	kabel_flush_page_caches( 'theme_switch' );
}
add_action( 'after_switch_theme', 'kabel_flush_after_switch_theme', 30 );
