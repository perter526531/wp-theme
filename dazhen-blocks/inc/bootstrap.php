<?php
/**
 * Site bootstrap — turn a fresh WordPress into a complete Dazhen site.
 *
 * Why this exists: the seeder only fills pages that ALREADY exist. A brand-new
 * WordPress has no About / Services / Solutions / Contact pages at all, so the
 * nav links 404 and the owner sees "an empty site". This module:
 *
 *   1. Auto-runs once on a truly fresh site (no Dazhen pages, hardly any
 *      content): creates the 7 core pages pre-filled with their block
 *      layouts, sets the static front page + posts page, and builds the
 *      navigation menus.
 *   2. On sites that already have content, a standing admin notice offers
 *      the same in one click — it only ever creates MISSING pages and never
 *      overwrites existing content.
 *
 * @package Dazhen_Blocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * The page blueprint: what a complete Dazhen site contains.
 *
 * @return array<string, array{title:string, layout:?string, template:string, menu:bool}>
 */
function dzb_bootstrap_blueprint(): array {
        return array(
                'home'      => array(
                        'title'    => __( 'Home', 'dazhen-blocks' ),
                        'layout'   => 'home',
                        'template' => 'default',
                        'menu'     => true,
                ),
                'about'     => array(
                        'title'    => __( 'About Us', 'dazhen-blocks' ),
                        'layout'   => 'about',
                        'template' => 'page-templates/about.php',
                        'menu'     => true,
                ),
                'services'  => array(
                        'title'    => __( 'Services', 'dazhen-blocks' ),
                        'layout'   => 'services',
                        'template' => 'page-templates/services.php',
                        'menu'     => true,
                ),
                'solutions' => array(
                        'title'    => __( 'Solutions', 'dazhen-blocks' ),
                        'layout'   => 'solutions',
                        'template' => 'page-templates/solutions-overview.php',
                        'menu'     => true,
                ),
                'contact'   => array(
                        'title'    => __( 'Contact', 'dazhen-blocks' ),
                        'layout'   => 'contact',
                        'template' => 'page-templates/contact.php',
                        'menu'     => true,
                ),
                'faq'       => array(
                        'title'    => __( 'FAQ', 'dazhen-blocks' ),
                        'layout'   => 'faq',
                        'template' => 'default',
                        'menu'     => false,
                ),
                'blog'      => array(
                        'title'    => __( 'Blog', 'dazhen-blocks' ),
                        'layout'   => null, // Posts page — WordPress ignores its content.
                        'template' => 'default',
                        'menu'     => true,
                ),
        );
}

/**
 * Does a published page with this slug exist?
 */
function dzb_page_exists( string $slug ): bool {
        $page = get_page_by_path( $slug );
        return $page instanceof WP_Post && 'publish' === $page->post_status;
}

/**
 * Which blueprint pages are missing?
 *
 * @return array<string, string> slug => title
 */
function dzb_bootstrap_missing(): array {
        $missing = array();
        foreach ( dzb_bootstrap_blueprint() as $slug => $page ) {
                if ( ! dzb_page_exists( $slug ) ) {
                        $missing[ $slug ] = $page['title'];
                }
        }
        return $missing;
}

/**
 * Site state: 'none' (no Dazhen pages at all), 'partial' or 'complete'.
 */
function dzb_bootstrap_state(): string {
        $missing = dzb_bootstrap_missing();
        if ( array() === $missing ) {
                return 'complete';
        }
        return count( $missing ) === count( dzb_bootstrap_blueprint() ) ? 'none' : 'partial';
}

/**
 * Is this a brand-new site where auto-bootstrap is safe?
 *
 * No Dazhen pages at all, at most two published pages (the WordPress
 * "Sample Page" era), and we have never bootstrapped here.
 */
function dzb_site_is_fresh(): bool {
        if ( get_option( 'dazhen_bootstrapped' ) ) {
                return false;
        }
        if ( 'none' !== dzb_bootstrap_state() ) {
                return false;
        }
        $pages = get_pages( array(
                'post_status' => 'publish',
                'number'      => 3,
        ) );
        return count( $pages ) <= 2;
}

/**
 * Run the bootstrap. Creates only MISSING pages — never overwrites.
 *
 * @return array{created:string[],front:?int,blog:?int,menus:string[]}
 */
function dzb_run_bootstrap(): array {
        $report  = array(
                'created' => array(),
                'front'   => null,
                'blog'    => null,
                'menus'   => array(),
        );
        $ids     = array();

        foreach ( dzb_bootstrap_blueprint() as $slug => $page ) {
                $existing = get_page_by_path( $slug );
                if ( $existing instanceof WP_Post && 'publish' === $existing->post_status ) {
                        $ids[ $slug ] = (int) $existing->ID;
                        continue;
                }

                /*
                 * Insert with EMPTY content (so nothing can strip the block markup
                 * on the way in), then write the layout through the importer-style
                 * $wpdb path used by dazhen_seed_page().
                 */
                $post_id = wp_insert_post( array(
                        'post_type'    => 'page',
                        'post_status'  => 'publish',
                        'post_title'   => $page['title'],
                        'post_name'    => $slug,
                        'post_content' => '',
                ) );

                if ( ! is_int( $post_id ) || $post_id <= 0 ) {
                        continue;
                }

                if ( null !== $page['layout'] ) {
                        dazhen_seed_page( $post_id, $page['layout'] );
                }

                if ( 'default' !== $page['template'] ) {
                        update_post_meta( $post_id, '_wp_page_template', $page['template'] );
                }

                $ids[ $slug ]         = $post_id;
                $report['created'][]  = $page['title'];
        }

        /* Reading + writing settings. */
        if ( isset( $ids['blog'] ) ) {
                $was_front = (int) get_option( 'page_on_front' );
                $front_ok  = 0 === $was_front;

                if ( ! $front_ok ) {
                        $front_page = get_post( $was_front );
                        $front_ok   = ! $front_page instanceof WP_Post
                                || in_array( $front_page->post_name, array( 'sample-page', 'home' ), true );
                }

                if ( $front_ok && isset( $ids['home'] ) ) {
                        update_option( 'show_on_front', 'page' );
                        update_option( 'page_on_front', $ids['home'] );
                        update_option( 'page_for_posts', $ids['blog'] );
                        $report['front'] = $ids['home'];
                        $report['blog']  = $ids['blog'];
                        flush_rewrite_rules();
                }
        }

        /* Navigation menus (only locations the active theme registers). */
        $menus = dzb_build_nav_menus( $ids );
        if ( $menus ) {
                $report['menus'] = $menus;
        }

        update_option( 'dazhen_bootstrapped', (string) time(), false );

        /**
         * Fires after a successful bootstrap run.
         *
         * @param array $report Summary: created page titles, front/blog ids, menus.
         */
        do_action( 'dazhen_blocks_bootstrapped', $report );

        return $report;
}

/**
 * Create + assign the primary and footer menus (safe: only fills fresh,
 * unassigned menus — a user-curated menu is never touched).
 *
 * @param array<string, int> $ids Map of blueprint slug => page ID.
 * @return string[] Names of the menus that were created/filled.
 */
function dzb_build_nav_menus( array $ids ): array {
        $made             = array();
        $assigned         = false;
        $menus_registered = get_registered_nav_menus(); // Standalone themes may not have these locations.

        if ( isset( $ids['home'], $menus_registered['primary'] ) && ! has_nav_menu( 'primary' ) ) {
                $menu_id = dzb_menu_fresh( 'Primary Menu' );
                if ( $menu_id ) {
                        $archive = get_post_type_archive_link( 'product' ) ?: home_url( '/products/' );

                        $items = array(
                                array( 'type' => 'custom', 'url' => home_url( '/' ), 'title' => __( 'Home', 'dazhen-blocks' ) ),
                                array( 'type' => 'custom', 'url' => $archive, 'title' => __( 'Products', 'dazhen-blocks' ) ),
                        );
                        foreach ( array( 'solutions', 'services', 'about', 'contact' ) as $slug ) {
                                if ( isset( $ids[ $slug ] ) ) {
                                        $items[] = array( 'type' => 'page', 'id' => $ids[ $slug ] );
                                }
                        }
                        if ( isset( $ids['blog'] ) ) {
                                $items[] = array( 'type' => 'page', 'id' => $ids['blog'] );
                        }
                        dzb_fill_menu( $menu_id, $items );

                        $made[]      = 'Primary Menu';
                        $locations   = get_theme_mod( 'nav_menu_locations', array() );
                        $locations['primary'] = $menu_id;
                        set_theme_mod( 'nav_menu_locations', $locations );
                        $assigned = true;
                }
        }

        if ( isset( $menus_registered['footer_company'] ) && ! has_nav_menu( 'footer_company' ) ) {
                $menu_id = dzb_menu_fresh( 'Footer — Company' );
                if ( $menu_id ) {
                        $items = array();
                        foreach ( array( 'solutions', 'services', 'about', 'faq', 'contact' ) as $slug ) {
                                if ( isset( $ids[ $slug ] ) ) {
                                        $items[] = array( 'type' => 'page', 'id' => $ids[ $slug ] );
                                }
                        }
                        dzb_fill_menu( $menu_id, $items );

                        $made[]      = 'Footer — Company';
                        $locations   = get_theme_mod( 'nav_menu_locations', array() );
                        $locations['footer_company'] = $menu_id;
                        set_theme_mod( 'nav_menu_locations', $locations );
                        $assigned = true;
                }
        }

        /*
         * Footer product-series menu: only when series terms already exist
         * (the theme fallback renders the list dynamically otherwise).
         */
        if ( isset( $menus_registered['footer_products'] ) && ! has_nav_menu( 'footer_products' ) && taxonomy_exists( 'product_series' ) ) {
                $terms = get_terms( array(
                        'taxonomy'   => 'product_series',
                        'hide_empty' => false,
                        'number'     => 20,
                ) );
                if ( ! is_wp_error( $terms ) && array() !== $terms ) {
                        $menu_id = dzb_menu_fresh( 'Footer — Product Series' );
                        if ( $menu_id ) {
                                $items = array();
                                foreach ( $terms as $term ) {
                                        $items[] = array(
                                                'type'  => 'custom',
                                                'url'   => get_term_link( $term ),
                                                'title' => $term->name,
                                        );
                                }
                                dzb_fill_menu( $menu_id, $items );

                                $made[]      = 'Footer — Product Series';
                                $locations   = get_theme_mod( 'nav_menu_locations', array() );
                                $locations['footer_products'] = $menu_id;
                                set_theme_mod( 'nav_menu_locations', $locations );
                                $assigned = true;
                        }
                }
        }

        if ( $assigned ) {
                // Menu changes need a clean slate for front-end menu fallbacks.
                wp_cache_delete( 'nav_menu_locations', 'options' );
        }

        return $made;
}

/**
 * Get a menu by name that is safe to fill: reuse an existing EMPTY one, or
 * create a fresh one. Returns the term ID (or 0).
 */
function dzb_menu_fresh( string $name ): int {
        $menu = wp_get_nav_menu_object( $name );
        if ( $menu instanceof WP_Term ) {
                $items = wp_get_nav_menu_items( $menu->term_id, array( 'update_post_term_cache' => false ) );
                return ( array() === $items ) ? (int) $menu->term_id : 0;
        }
        $created = wp_create_nav_menu( $name );
        return is_wp_error( $created ) ? 0 : (int) $created;
}

/**
 * Add items to a (fresh, empty) menu.
 *
 * @param array<int, array{type:string,id?:int,url?:string,title?:string}> $items
 */
function dzb_fill_menu( int $menu_id, array $items ): void {
        foreach ( $items as $item ) {
                $args = array( 'menu-item-status' => 'publish' );
                if ( 'page' === $item['type'] ) {
                        $args['menu-item-object-id'] = $item['id'];
                        $args['menu-item-object']    = 'page';
                        $args['menu-item-type']      = 'post_type';
                } else {
                        $args['menu-item-url']   = $item['url'];
                        $args['menu-item-title'] = $item['title'];
                        $args['menu-item-type']  = 'custom';
                }
                wp_update_nav_menu_item( $menu_id, 0, $args );
        }
}

/* ---------------------------------------------------------------------
 * Auto-run + notices
 * ------------------------------------------------------------------- */

/**
 * Auto-bootstrap a fresh site on the first admin visit (activation landing).
 */
function dzb_maybe_autorun(): void {
        if ( wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
                return;
        }
        if ( ! current_user_can( 'edit_pages' ) || ! current_user_can( 'unfiltered_html' ) ) {
                return;
        }
        if ( ! dzb_site_is_fresh() ) {
                return;
        }

        $GLOBALS['dzb_bootstrap_report'] = dzb_run_bootstrap();
}
add_action( 'admin_init', 'dzb_maybe_autorun', 1 );

/**
 * Success notice for the auto-run (same request) and the manual handler
 * (redirect flag), plus the standing "pages missing" helper.
 */
function dzb_bootstrap_notices(): void {
        if ( ! current_user_can( 'edit_pages' ) ) {
                return;
        }

        /* Manual-run capability failure (redirect back from admin-post.php). */
        if ( isset( $_GET['dazhen_error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flag.
                printf(
                        '<div class="notice notice-error is-dismissible"><p><strong>%s</strong> %s</p></div>',
                        esc_html__( 'The Dazhen pages could not be created.', 'dazhen-blocks' ),
                        esc_html__( 'Your account needs the Administrator role (page creation + unfiltered HTML). Log in as an administrator and try again.', 'dazhen-blocks' )
                );
                return;
        }

        /* Auto-run result (same request). */
        if ( isset( $GLOBALS['dzb_bootstrap_report'] ) ) {
                dzb_render_success_notice( (array) $GLOBALS['dzb_bootstrap_report'] );
                return;
        }

        /* Manual-run result (redirect back from admin-post.php). */
        if ( isset( $_GET['dazhen_bootstrapped'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flag.
                $report = json_decode( (string) get_transient( 'dzb_bootstrap_report' ), true );
                delete_transient( 'dzb_bootstrap_report' );
                if ( is_array( $report ) ) {
                        dzb_render_success_notice( $report );
                }
                return;
        }

        /* Standing helper while pages are missing. */
        if ( '1' === (string) get_option( 'dazhen_bootstrap_notice_off', '' ) ) {
                return;
        }
        $missing = dzb_bootstrap_missing();
        if ( array() === $missing ) {
                return;
        }

        $url = wp_nonce_url(
                add_query_arg( 'action', 'dazhen_bootstrap', admin_url( 'admin-post.php' ) ),
                'dazhen_bootstrap'
        );
        $dismiss = wp_nonce_url(
                add_query_arg( 'action', 'dazhen_dismiss_bootstrap_notice', admin_url( 'admin-post.php' ) ),
                'dazhen_dismiss_bootstrap_notice'
        );
        ?>
        <div class="notice notice-info dazhen-bootstrap-notice">
                <p>
                        <strong><?php echo esc_html( sprintf( _n( '%d Dazhen page is missing.', '%d Dazhen pages are missing.', count( $missing ), 'dazhen-blocks' ), count( $missing ) ) ); ?></strong>
                        <?php
                        echo esc_html( sprintf(
                                /* translators: %s: list of page titles, e.g. "Home, About Us". */
                                __( 'Missing: %s — create them pre-filled with their ready-made layouts?', 'dazhen-blocks' ),
                                implode( ', ', $missing )
                        ) );
                        ?>
                </p>
                <p>
                        <a class="button button-primary" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Create the pages · 一键初始化整站', 'dazhen-blocks' ); ?></a>
                        <a class="button" href="<?php echo esc_url( $dismiss ); ?>"><?php esc_html_e( 'Dismiss', 'dazhen-blocks' ); ?></a>
                </p>
        </div>
        <?php
}
add_action( 'admin_notices', 'dzb_bootstrap_notices' );

/**
 * Render the "site ready" success notice.
 */
function dzb_render_success_notice( array $report ): void {
        $created = isset( $report['created'] ) ? (array) $report['created'] : array();
        $menus   = isset( $report['menus'] ) ? (array) $report['menus'] : array();
        ?>
        <div class="notice notice-success is-dismissible">
                <p>
                        <strong><?php esc_html_e( 'Your Dazhen site is ready.', 'dazhen-blocks' ); ?></strong>
                        <?php
                        if ( array() !== $created ) {
                                echo ' ' . esc_html( sprintf(
                                        /* translators: %s: list of page titles. */
                                        __( 'Created: %s.', 'dazhen-blocks' ),
                                        implode( ', ', $created )
                                ) );
                        }
                        if ( array() !== $menus ) {
                                echo ' ' . esc_html( sprintf(
                                        /* translators: %s: list of menu names. */
                                        __( 'Navigation menus built: %s.', 'dazhen-blocks' ),
                                        implode( ', ', $menus )
                                ) );
                        }
                        ?>
                </p>
                <p>
                        <a class="button button-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Visit the site', 'dazhen-blocks' ); ?></a>
                        <a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>"><?php esc_html_e( 'Edit the pages', 'dazhen-blocks' ); ?></a>
                </p>
        </div>
        <?php
}

/**
 * Manual trigger: create the missing Dazhen pages now.
 */
function dzb_handle_bootstrap(): void {
        if ( ! current_user_can( 'edit_pages' ) || ! current_user_can( 'unfiltered_html' ) ) {
                wp_safe_redirect( add_query_arg( 'dazhen_error', '1', admin_url( 'edit.php?post_type=page' ) ) );
                exit;
        }
        check_admin_referer( 'dazhen_bootstrap' );

        $report  = dzb_run_bootstrap();
        set_transient( 'dzb_bootstrap_report', wp_json_encode( $report ), 60 );

        $back = wp_get_referer() ?: admin_url( 'edit.php?post_type=page' );
        wp_safe_redirect( add_query_arg( 'dazhen_bootstrapped', '1', $back ) );
        exit;
}
add_action( 'admin_post_dazhen_bootstrap', 'dzb_handle_bootstrap' );

/**
 * Logged-out click on "Create the pages": admin-post.php does NOT redirect
 * visitors to the login screen — without this, WordPress would render its
 * empty "Error" box (wp_die('', 400) for an unregistered nopriv action).
 * Send the visitor to login instead; after signing in they land on the Pages
 * list where the standing notice offers a fresh button.
 */
function dzb_handle_bootstrap_nopriv(): void {
        wp_safe_redirect( wp_login_url( admin_url( 'edit.php?post_type=page' ) ) );
        exit;
}
add_action( 'admin_post_nopriv_dazhen_bootstrap', 'dzb_handle_bootstrap_nopriv' );

/**
 * Dismiss the standing notice.
 */
function dzb_handle_dismiss_bootstrap_notice(): void {
        if ( ! current_user_can( 'edit_pages' ) ) {
                wp_safe_redirect( admin_url( 'edit.php?post_type=page' ) );
                exit;
        }
        check_admin_referer( 'dazhen_dismiss_bootstrap_notice' );

        update_option( 'dazhen_bootstrap_notice_off', '1', false );

        $back = wp_get_referer() ?: admin_url( 'edit.php?post_type=page' );
        wp_safe_redirect( remove_query_arg( 'dazhen_bootstrapped', $back ) );
        exit;
}
add_action( 'admin_post_dazhen_dismiss_bootstrap_notice', 'dzb_handle_dismiss_bootstrap_notice' );
add_action( 'admin_post_nopriv_dazhen_dismiss_bootstrap_notice', 'dzb_handle_bootstrap_nopriv' );
