<?php
/**
 * Kabel Tools — demo content reset + hex → global-color migration.
 *
 * Two standing maintenance jobs that used to be theme-only or missing:
 *
 *   1. Demo pages — the 14 blueprint pages this plugin can bootstrap.
 *      "Move them all to the Trash" for a clean hand-over (reading
 *      settings self-heal when the front/posts page is trashed; media
 *      and user content are never touched).
 *
 *   2. Hex → global color migration — pages saved before the Global
 *      Colors system (v3.9) carry raw hex values, so a brand re-color
 *      never reached them. The scanner lists every page whose content
 *      contains a hex that exactly matches one of the six slots; one
 *      click rewrites them to var(--kbl-gc-N) tokens (block attribute
 *      JSON values and inline style declarations only — visible prose
 *      is never rewritten), and each page gets a revision so the change
 *      is one-click undoable.
 *
 * Menu: Kabel → Tools (last in the Kabel group; the Layout Library's
 * self-referencing submenu must stay FIRST — see inc/layout-library.php).
 *
 * @package Kabel_Blocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the submenu.
 */
function kbl3_tools_menu(): void {
        add_submenu_page(
                'kabel-layout-library',
                __( 'Tools', 'kabel-blocks' ),
                __( 'Tools', 'kabel-blocks' ),
                'manage_options',
                'kabel-tools',
                'kbl3_tools_page'
        );
}
add_action( 'admin_menu', 'kbl3_tools_menu', 12 );

/* ---------------------------------------------------------------------
 * 1. Demo pages
 * ------------------------------------------------------------------- */

/**
 * The demo page slugs — straight from the bootstrap blueprint so the list
 * can never drift from what the plugin actually seeds.
 *
 * @return string[]
 */
function kbl3_tools_demo_slugs(): array {
        if ( function_exists( 'kbl_bootstrap_blueprint' ) ) {
                return array_keys( kbl_bootstrap_blueprint() );
        }
        return array();
}

/**
 * The demo pages currently live on this site (any status but Trash).
 *
 * @return array<int, WP_Post>
 */
function kbl3_tools_demo_pages(): array {
        $found = array();
        foreach ( kbl3_tools_demo_slugs() as $slug ) {
                $page = get_page_by_path( $slug );
                if ( $page instanceof WP_Post && 'page' === $page->post_type && 'trash' !== $page->post_status ) {
                        $found[] = $page;
                }
        }
        return $found;
}

/**
 * Trash every demo page (admin-post handler). Only ever wp_trash_post() —
 * permanent deletion is refused when the site has the trash disabled.
 */
function kbl3_tools_handle_trash_demo(): void {
        if ( ! current_user_can( 'edit_pages' ) ) {
                wp_safe_redirect( admin_url( 'admin.php?page=kabel-tools' ) );
                exit;
        }
        check_admin_referer( 'kbl3_trash_demo' );
        $back = admin_url( 'admin.php?page=kabel-tools' );

        /* With the trash disabled, wp_trash_post() would delete PERMANENTLY — refuse. */
        if ( defined( 'EMPTY_TRASH_DAYS' ) && 0 === (int) EMPTY_TRASH_DAYS ) {
                wp_safe_redirect( add_query_arg( 'kbl_demo_no_trash', '1', $back ) );
                exit;
        }

        $trashed_ids = array();
        foreach ( kbl3_tools_demo_slugs() as $slug ) {
                $page = get_page_by_path( $slug );
                if ( ! $page instanceof WP_Post || 'page' !== $page->post_type || 'trash' === $page->post_status ) {
                        continue; // Not on the site (or already in the Trash) — skip.
                }
                if ( wp_trash_post( $page->ID ) ) {
                        $trashed_ids[] = (int) $page->ID;
                }
        }

        /* Keep the reading settings sane when the front/posts page got trashed. */
        $front = (int) get_option( 'page_on_front' );
        if ( $front && in_array( $front, $trashed_ids, true ) ) {
                update_option( 'show_on_front', 'posts' );
                update_option( 'page_on_front', 0 );
        }
        $posts_page = (int) get_option( 'page_for_posts' );
        if ( $posts_page && in_array( $posts_page, $trashed_ids, true ) ) {
                update_option( 'page_for_posts', 0 );
        }

        if ( function_exists( 'kbl3_flush_page_caches' ) ) {
                kbl3_flush_page_caches( 'tools-trash-demo' );
        }

        wp_safe_redirect( add_query_arg( 'kbl_demo_trashed', count( $trashed_ids ), $back ) );
        exit;
}
add_action( 'admin_post_kbl3_trash_demo', 'kbl3_tools_handle_trash_demo' );

/* ---------------------------------------------------------------------
 * 2. Hex → global color migration
 * ------------------------------------------------------------------- */

/**
 * Build the replacement patterns for the six global slots.
 *
 * Two storage forms carry colors in kbl/* content, and ONLY these two are
 * rewritten — visible prose, URLs and file names are left alone:
 *
 *   a) Block attribute JSON:  "color":"#B39A74"   → "color":"var(--kbl-gc-1)"
 *   b) Inline style values:   color:#B39A74      → color:var(--kbl-gc-1)
 *      (also gradient stops:  #B39A74,#101828 — separators ( , : ; plus
 *      at most one space, matching how the emitter serializes)
 *
 * @return array<int, array{slot:array, json:string, css:string}>
 */
function kbl3_tools_hex_patterns(): array {
        $patterns = array();
        foreach ( kbl3_global_colors() as $i => $slot ) {
                $hex        = preg_quote( $slot['color'], '/' );
                $patterns[] = array(
                        'slot' => $slot,
                        'idx'  => $i + 1,
                        /* "…":"#B39A74" — quoted JSON attribute value. */
                        'json' => '/"' . $hex . '(?![0-9A-Fa-f])"/i',
                        /* :#B39A74 / ,#B39A74 / (#B39A74 / ; #B39A74 — CSS contexts, the
                         * optional single space captured so the replacement keeps it. */
                        'css'  => '/(?<=[(,:;])([ ]?)' . $hex . '(?![0-9A-Fa-f])/i',
                );
        }
        return $patterns;
}

/**
 * Count the migratable colors in one content string, per slot.
 *
 * @return array<int, int> slot number (1-based) => match count.
 */
function kbl3_tools_hex_counts( string $content ): array {
        $counts = array();
        if ( '' === $content ) {
                return $counts;
        }
        foreach ( kbl3_tools_hex_patterns() as $p ) {
                $n = (int) preg_match_all( $p['json'], $content ) + (int) preg_match_all( $p['css'], $content );
                if ( $n > 0 ) {
                        $counts[ $p['idx'] ] = $n;
                }
        }
        return $counts;
}

/**
 * Rewrite the migratable colors in one content string.
 *
 * @return array{content:string, replaced:int}
 */
function kbl3_tools_hex_replace( string $content ): array {
        $replaced = 0;
        foreach ( kbl3_tools_hex_patterns() as $p ) {
                $var = 'var(--kbl-gc-' . $p['idx'] . ')';

                $content = preg_replace( $p['json'], '"' . $var . '"', $content, -1, $n );
                $replaced += (int) $n;

                $content = preg_replace( $p['css'], '$1' . $var, $content, -1, $n );
                $replaced += (int) $n;
        }
        return array(
                'content'  => (string) $content,
                'replaced' => $replaced,
        );
}

/**
 * The pages (+ the site-part pages) whose content matches a global slot.
 *
 * One SQL query (OR LIKE over the six hexes, both cases) narrows the set
 * before the precise regex counting runs.
 *
 * @return array<int, array{post:WP_Post, counts:array<int,int>, total:int}>
 */
function kbl3_tools_hex_candidates(): array {
        global $wpdb;

        $colors = array();
        foreach ( kbl3_global_colors() as $slot ) {
                $colors[] = $slot['color'];
        }

        $like  = array();
        $params = array();
        foreach ( $colors as $color ) {
                $like[]   = 'post_content LIKE %s';
                $params[] = '%' . $wpdb->esc_like( $color ) . '%';
                $like[]   = 'post_content LIKE %s';
                $params[] = '%' . $wpdb->esc_like( strtolower( $color ) ) . '%';
        }

        $sql = 'SELECT ID FROM ' . $wpdb->posts . '
                WHERE post_type = \'page\'
                AND post_status IN (\'publish\', \'private\', \'draft\')
                AND (' . implode( ' OR ', $like ) . ')
                ORDER BY post_title ASC';

        $ids = $wpdb->get_col( $wpdb->prepare( $sql, $params ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- placeholders above.
        if ( ! is_array( $ids ) || array() === $ids ) {
                return array();
        }

        $candidates = array();
        foreach ( $ids as $id ) {
                $post = get_post( (int) $id );
                if ( ! $post instanceof WP_Post ) {
                        continue;
                }
                $counts = kbl3_tools_hex_counts( (string) $post->post_content );
                if ( array() === $counts ) {
                        continue; // LIKE hit a substring the precise patterns reject (URL etc.).
                }
                $candidates[] = array(
                        'post'   => $post,
                        'counts' => $counts,
                        'total'  => array_sum( $counts ),
                );
        }
        return $candidates;
}

/**
 * Apply the migration (admin-post handler). Selected pages only; each page
 * is saved through wp_update_post so a revision is kept (one-click undo in
 * the editor) and the normal save pipeline fires for cache plugins.
 */
function kbl3_tools_handle_migrate_hex(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
                wp_safe_redirect( admin_url() );
                exit;
        }
        check_admin_referer( 'kbl3_migrate_hex' );
        $back = admin_url( 'admin.php?page=kabel-tools' );

        $selected = isset( $_POST['kbl_migrate_pages'] ) && is_array( $_POST['kbl_migrate_pages'] )
                ? array_map( 'absint', wp_unslash( $_POST['kbl_migrate_pages'] ) ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- absint() per item.
                : array();

        $pages_done = 0;
        $colors     = 0;
        foreach ( $selected as $post_id ) {
                $post = get_post( $post_id );
                if ( ! $post instanceof WP_Post || ! current_user_can( 'edit_post', $post_id ) ) {
                        continue;
                }
                $result = kbl3_tools_hex_replace( (string) $post->post_content );
                if ( $result['replaced'] < 1 ) {
                        continue;
                }
                wp_update_post( array(
                        'ID'           => $post_id,
                        'post_content' => $result['content'],
                ), true );
                clean_post_cache( $post_id );
                $pages_done++;
                $colors += $result['replaced'];
        }

        if ( $pages_done && function_exists( 'kbl3_flush_page_caches' ) ) {
                kbl3_flush_page_caches( 'tools-hex-migration' );
        }

        wp_safe_redirect( add_query_arg( array(
                'kbl_hex_pages' => $pages_done,
                'kbl_hex_colors' => $colors,
        ), $back ) );
        exit;
}
add_action( 'admin_post_kbl3_migrate_hex', 'kbl3_tools_handle_migrate_hex' );

/* ---------------------------------------------------------------------
 * Screen
 * ------------------------------------------------------------------- */

/**
 * The Tools screen.
 */
function kbl3_tools_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
                return;
        }

        $demo_pages   = kbl3_tools_demo_pages();
        $candidates   = kbl3_tools_hex_candidates();
        $colors       = kbl3_global_colors();
        $trashed      = isset( $_GET['kbl_demo_trashed'] ) ? (int) $_GET['kbl_demo_trashed'] : -1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flag.
        $no_trash     = isset( $_GET['kbl_demo_no_trash'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flag.
        $hex_pages    = isset( $_GET['kbl_hex_pages'] ) ? (int) $_GET['kbl_hex_pages'] : -1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flag.
        $hex_colors   = isset( $_GET['kbl_hex_colors'] ) ? (int) $_GET['kbl_hex_colors'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flag.
        ?>
        <div class="wrap kbl-tools-wrap">
                <h1><?php esc_html_e( 'Kabel Tools', 'kabel-blocks' ); ?></h1>

                <?php if ( $trashed >= 0 ) : ?>
                        <div class="notice notice-success is-dismissible"><p>
                                <?php
                                if ( $trashed > 0 ) {
                                        printf(
                                                /* translators: %d: number of pages moved to the trash. */
                                                esc_html( _n( '%d demo page moved to the Trash.', '%d demo pages moved to the Trash.', $trashed, 'kabel-blocks' ) ),
                                                (int) $trashed
                                        );
                                } else {
                                        esc_html_e( 'No demo pages were found to move.', 'kabel-blocks' );
                                }
                                ?>
                        </p></div>
                <?php elseif ( $no_trash ) : ?>
                        <div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'This site has the Trash disabled (EMPTY_TRASH_DAYS = 0) — moving to the Trash would delete permanently, which this tool never does.', 'kabel-blocks' ); ?></p></div>
                <?php endif; ?>

                <?php if ( $hex_pages > 0 ) : ?>
                        <div class="notice notice-success is-dismissible"><p>
                                <?php
                                printf(
                                        /* translators: 1: number of colors migrated, 2: number of pages. */
                                        esc_html( _n( '%1$d color reference on %2$d page now follows the global palette.', '%1$d color references across %2$d pages now follow the global palette.', $hex_colors, 'kabel-blocks' ) ),
                                        (int) $hex_colors,
                                        (int) $hex_pages
                                );
                                ?>
                                <?php esc_html_e( 'Each page kept a revision — Revisions in the editor undoes this in one click.', 'kabel-blocks' ); ?>
                        </p></div>
                <?php elseif ( 0 === $hex_pages ) : ?>
                        <div class="notice notice-info is-dismissible"><p><?php esc_html_e( 'Nothing to migrate on the selected pages.', 'kabel-blocks' ); ?></p></div>
                <?php endif; ?>

                <!-- ----------------------------------------------------------- -->
                <section class="kbl-tools-section">
                        <h2><?php esc_html_e( 'Hex colors → global palette', 'kabel-blocks' ); ?></h2>
                        <p class="kbl-tools-lead">
                                <?php esc_html_e( 'Pages saved before the Global Colors system store raw hex values — a palette change never reached them. This tool rewrites every exact match of a slot color into its var(--kbl-gc-N) token, in block attributes and inline styles only (never in visible text, URLs or file names). Every migrated page keeps a revision for one-click undo.', 'kabel-blocks' ); ?>
                        </p>

                                <div class="kbl-tools-slots">
                                        <?php foreach ( $colors as $i => $gc ) : ?>
                                                <span class="kbl-tools-swatch">
                                                        <span class="kbl-tools-chip" style="background:<?php echo esc_attr( $gc['color'] ); ?>"></span>
                                                        <strong><?php echo esc_html( $gc['name'] ); ?></strong>
                                                        <code><?php echo esc_html( $gc['color'] ); ?> → var(--kbl-gc-<?php echo (int) ( $i + 1 ); ?>)</code>
                                                </span>
                                        <?php endforeach; ?>
                                </div>

                        <?php if ( array() === $candidates ) : ?>
                                <p><em><?php esc_html_e( 'No pages contain a raw slot color — everything already follows the global palette.', 'kabel-blocks' ); ?></em></p>
                        <?php else : ?>
                                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
                                        onsubmit="return confirm('<?php echo esc_js( __( 'Rewrite the raw hex values on the selected pages into global color tokens?', 'kabel-blocks' ) ); ?>');">
                                        <input type="hidden" name="action" value="kbl3_migrate_hex">
                                        <?php wp_nonce_field( 'kbl3_migrate_hex' ); ?>
                                        <table class="widefat striped kbl-tools-table">
                                                <thead>
                                                        <tr>
                                                                <td class="check-column"><input type="checkbox" id="kbl-tools-select-all" checked></td>
                                                                <th scope="col"><?php esc_html_e( 'Page', 'kabel-blocks' ); ?></th>
                                                                <th scope="col"><?php esc_html_e( 'Matches', 'kabel-blocks' ); ?></th>
                                                        </tr>
                                                </thead>
                                                <tbody>
                                                        <?php foreach ( $candidates as $row ) : ?>
                                                                <tr>
                                                                        <th scope="row" class="check-column">
                                                                                <input type="checkbox" name="kbl_migrate_pages[]" value="<?php echo (int) $row['post']->ID; ?>" checked>
                                                                        </th>
                                                                        <td>
                                                                                <a href="<?php echo esc_url( get_edit_post_link( $row['post']->ID ) ); ?>"><?php echo esc_html( get_the_title( $row['post'] ) ); ?></a>
                                                                                <?php if ( 'private' === $row['post']->post_status ) : ?>
                                                                                        <code><?php echo esc_html( $row['post']->post_name ); ?></code>
                                                                                <?php endif; ?>
                                                                        </td>
                                                                        <td>
                                                                                <?php
                                                                                $parts = array();
                                                                                foreach ( $row['counts'] as $slot_idx => $n ) {
                                                                                        $parts[] = sprintf(
                                                                                                '%1$s × <code>var(--kbl-gc-%2$d)</code>',
                                                                                                (int) $n,
                                                                                                (int) $slot_idx
                                                                                        );
                                                                                }
                                                                                echo implode( ', ', $parts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from ints above.
                                                                                ?>
                                                                        </td>
                                                                </tr>
                                                        <?php endforeach; ?>
                                                </tbody>
                                        </table>
                                        <p>
                                                <?php submit_button( __( 'Migrate selected pages', 'kabel-blocks' ), 'primary', 'kbl_migrate_submit', false ); ?>
                                        </p>
                                </form>
                        <?php endif; ?>
                </section>

                <!-- ----------------------------------------------------------- -->
                <section class="kbl-tools-section kbl-tools-danger">
                        <h2><?php esc_html_e( 'Demo content', 'kabel-blocks' ); ?></h2>
                        <p class="kbl-tools-lead">
                                <?php esc_html_e( 'The demo pages this plugin can seed are listed below. Moving them to the Trash is the clean way to hand an empty site to a client — media, menus and the site header/footer parts are never touched, and the reading settings self-heal when the front page is trashed. Restore any page from Appearance-like Trash screens at any time.', 'kabel-blocks' ); ?>
                        </p>

                        <?php if ( array() === $demo_pages ) : ?>
                                <p><em><?php esc_html_e( 'No demo pages found on this site.', 'kabel-blocks' ); ?></em></p>
                        <?php else : ?>
                                <table class="widefat striped kbl-tools-table kbl-tools-demo">
                                        <thead>
                                                <tr>
                                                        <th scope="col"><?php esc_html_e( 'Demo page', 'kabel-blocks' ); ?></th>
                                                        <th scope="col"><?php esc_html_e( 'Slug', 'kabel-blocks' ); ?></th>
                                                </tr>
                                        </thead>
                                        <tbody>
                                                <?php foreach ( $demo_pages as $page ) : ?>
                                                        <tr>
                                                                <td>
                                                                        <a href="<?php echo esc_url( get_edit_post_link( $page->ID ) ); ?>"><?php echo esc_html( get_the_title( $page ) ); ?></a>
                                                                        <?php if ( 'private' === $page->post_status ) : ?>
                                                                                <code><?php echo esc_html__( 'private', 'kabel-blocks' ); ?></code>
                                                                        <?php endif; ?>
                                                                </td>
                                                                <td><code><?php echo esc_html( $page->post_name ); ?></code></td>
                                                        </tr>
                                                <?php endforeach; ?>
                                        </tbody>
                                </table>
                                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
                                        onsubmit="return confirm('<?php echo esc_js( __( 'Move every demo page to the Trash? Menus keep their links until you edit them.', 'kabel-blocks' ) ); ?>');">
                                        <input type="hidden" name="action" value="kbl3_trash_demo">
                                        <?php wp_nonce_field( 'kbl3_trash_demo' ); ?>
                                        <p>
                                                <button type="submit" class="button button-link-delete"><?php esc_html_e( 'Move all demo pages to the Trash', 'kabel-blocks' ); ?></button>
                                        </p>
                                </form>
                        <?php endif; ?>
                </section>
        </div>

        <style>
                .kbl-tools-lead { max-width: 760px; font-size: 14px; color: #3c434a; }
                .kbl-tools-section { margin: 24px 0 40px; }
                .kbl-tools-slots { display: flex; flex-wrap: wrap; gap: 8px 18px; margin: 12px 0 16px; }
                .kbl-tools-swatch { display: inline-flex; align-items: center; gap: 8px; }
                .kbl-tools-chip { display: inline-block; width: 22px; height: 22px; border: 1px solid #8c8f94; border-radius: 3px; }
                .kbl-tools-table { max-width: 860px; }
                .kbl-tools-table code { background: #f0f0f1; padding: 2px 6px; border-radius: 3px; }
                .kbl-tools-danger { border-top: 1px solid #dcdcde; padding-top: 20px; }
        </style>
        <script>
        jQuery( function( $ ) {
                var all = $( '#kbl-tools-select-all' );
                if ( all.length ) {
                        all.on( 'change', function() {
                                $( this ).closest( 'table' ).find( 'tbody .check-column input' ).prop( 'checked', this.checked );
                        } );
                }
        } );
        </script>
        <?php
}
