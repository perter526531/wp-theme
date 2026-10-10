<?php
/**
 * Kabel Blocks v3 — one-time content migration.
 *
 * Sites that seeded pages with v1/v2 (spec-driven kabel-blocks/* markup)
 * upgrade to v3 like this:
 *
 *   1. Find every page still carrying legacy block comments.
 *   2. Regenerate the LEGACY markup for its layout and compare byte-for-byte
 *      with the stored content — equal means the user never touched it.
 *   3. Untouched pages are re-emitted with the new fully editable kbl/*
 *      blocks (same copy, same images, new editing experience).
 *   4. Edited pages are left exactly as they were — they keep rendering
 *      through the retained legacy engine — and the admin is told which.
 *
 * @package Kabel_Blocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Run the migration once per version bump.
 */
function kbl3_maybe_migrate(): void {
        if ( ! current_user_can( 'edit_pages' ) ) {
                return;
        }

        $stored = get_option( 'kabel_blocks_version', '' );
        if ( version_compare( $stored, KABEL_BLOCKS_VERSION, '>=' ) ) {
                return;
        }

        $stats = get_option( 'kbl3_migration_stats', null );
        if ( null === $stats ) {
                $stats = kbl3_run_migration();
                update_option( 'kbl3_migration_stats', $stats, false );
        }

        update_option( 'kabel_blocks_version', KABEL_BLOCKS_VERSION, true );

        if ( ! empty( $stats['migrated'] ) || ! empty( $stats['kept'] ) ) {
                add_action( 'admin_notices', static function () use ( $stats ): void {
                        if ( ! current_user_can( 'edit_pages' ) ) {
                                return;
                        }
                        $migrated = (int) $stats['migrated'];
                        $kept     = (int) $stats['kept'];
                        if ( 0 === $migrated && 0 === $kept ) {
                                return;
                        }
                        printf(
                                '<div class="notice notice-success is-dismissible"><p><strong>%s</strong> %s</p></div>',
                                esc_html__( 'Kabel Blocks 3.0 — the Stackable-style editor is here.', 'kabel-blocks' ),
                                esc_html( sprintf(
                                        /* translators: 1: migrated pages, 2: kept pages. */
                                        _n( '%1$d page was upgraded to the new free-editing blocks.', '%1$d pages were upgraded to the new free-editing blocks.', $migrated, 'kabel-blocks' ),
                                        $migrated
                                ) . ( $kept > 0 ? ' ' . sprintf(
                                        /* translators: %s: number of pages. */
                                        _n( '%s page you edited was left untouched (it still renders via the legacy engine).', '%s pages you edited were left untouched (they still render via the legacy engine).', $kept, 'kabel-blocks' ),
                                        $kept
                                ) : '' ) )
                        );
                } );
        }
}
add_action( 'admin_init', 'kbl3_maybe_migrate', 5 );

/**
 * The migration itself. Returns array{migrated:int, kept:int}.
 */
function kbl3_run_migration(): array {
        // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- shared-hosting time budget.
        @set_time_limit( 300 );

        $migrated = 0;
        $kept     = 0;

        $pages = get_posts( array(
                'post_type'      => 'page',
                'post_status'    => 'any',
                'posts_per_page' => 200,
        ) );

        if ( empty( $pages ) ) {
                return array( 'migrated' => 0, 'kept' => 0 );
        }

        /* Throttle filter: only generate thumbnails while sideloading (v1/v2 rule). */
        $throttle = static function ( array $sizes ): array {
                return array( 'thumbnail' => array( 160, 160, true ) );
        };
        add_filter( 'intermediate_image_sizes_advanced', $throttle );

        foreach ( $pages as $post ) {
                if ( false === strpos( (string) $post->post_content, 'wp:kabel-blocks/' ) ) {
                        continue;
                }
                $layout = kabel_match_page_layout( $post );
                if ( '' === $layout || null === kabel_get_block_layout( $layout ) ) {
                        continue;
                }

                // 1. What the OLD plugin would have written.
                kbl_asset_import_mode( true );
                kbl3_legacy_mode( true );
                $legacy = kabel_get_block_layout( $layout );
                kbl3_legacy_mode( false );
                kbl_asset_import_mode( false );

                if ( null === $legacy || $legacy !== $post->post_content ) {
                        // User-modified (or partially seeded): leave it alone.
                        if ( false !== strpos( (string) $post->post_content, 'wp:kabel-blocks/' ) ) {
                                $kept++;
                        }
                        continue;
                }

                // 2. Re-emit with the new blocks — media map is already warm.
                kbl_asset_import_mode( true );
                kbl3_legacy_mode( false );
                $markup = kabel_get_block_layout( $layout );
                kbl_asset_import_mode( false );

                if ( null === $markup || '' === $markup ) {
                        $kept++;
                        continue;
                }

                /*
                 * Raw column update (importer-style): wp_update_post() would run
                 * content filters that strip JSON escapes inside block comments.
                 */
                global $wpdb;
                $wpdb->update(
                        $wpdb->posts,
                        array( 'post_content' => $markup ),
                        array( 'ID' => $post->ID )
                );
                clean_post_cache( $post->ID );
                $migrated++;
        }

        remove_filter( 'intermediate_image_sizes_advanced', $throttle );

        return array( 'migrated' => $migrated, 'kept' => $kept );
}
