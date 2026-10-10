<?php
/**
 * Appearance → Dazhen Setup — the companion guide page.
 *
 * One place for the owner to see what ships with the theme, install the
 * bundled Dazhen Blocks plugin in one click, and grab the recommended free
 * plugins (translation, editor add-ons, SMTP).
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

/**
 * After activating the theme, land the owner on this setup page once.
 */
function dazhen_guide_activation_redirect(): void {
        set_transient( 'dazhen_goto_setup', 1, 5 * MINUTE_IN_SECONDS );
}
add_action( 'after_switch_theme', 'dazhen_guide_activation_redirect' );

/**
 * Perform that redirect on a safe admin screen (never inside the customizer
 * or an async request).
 */
function dazhen_guide_maybe_redirect(): void {
        if ( wp_doing_ajax() || ! current_user_can( 'edit_theme_options' ) ) {
                return;
        }
        if ( ! get_transient( 'dazhen_goto_setup' ) ) {
                return;
        }

        $safe = array( 'index.php', 'plugins.php', 'themes.php', 'update.php', 'edit.php', 'upload.php', 'post.php', 'post-new.php' );
        if ( ! in_array( $GLOBALS['pagenow'] ?? '', $safe, true ) ) {
                return; // Keep the transient — try again on the next safe screen.
        }

        delete_transient( 'dazhen_goto_setup' );

        if ( isset( $_GET['page'] ) && 'dazhen-setup' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                return;
        }

        wp_safe_redirect( admin_url( 'themes.php?page=dazhen-setup' ) );
        exit;
}
add_action( 'admin_init', 'dazhen_guide_maybe_redirect', 2 );

/**
 * Register the page.
 */
function dazhen_guide_menu(): void {
        add_theme_page(
                __( 'Dazhen Setup', 'dazhen' ),
                __( 'Dazhen Setup', 'dazhen' ),
                'edit_theme_options',
                'dazhen-setup',
                'dazhen_guide_render'
        );
}
add_action( 'admin_menu', 'dazhen_guide_menu' );

/**
 * Is the Dazhen Blocks plugin active?
 */
function dazhen_blocks_plugin_active(): bool {
        return function_exists( 'dzb_specs' );
}

/**
 * The active Dazhen Blocks version, or '' when not loaded.
 */
function dazhen_blocks_plugin_version(): string {
        return defined( 'DAZHEN_BLOCKS_VERSION' ) ? (string) DAZHEN_BLOCKS_VERSION : '';
}

/**
 * Is the loaded plugin new enough to create the ready-made pages?
 *
 * The site initializer (bootstrap.php) shipped in v2.0.1 — older copies have
 * the block engine (dzb_specs) but NO page blueprint, so the "Create the
 * pages" button would 404 into WordPress' empty admin-post error screen.
 */
function dazhen_blocks_plugin_is_current(): bool {
        return function_exists( 'dzb_specs' ) && function_exists( 'dzb_bootstrap_missing' );
}

/**
 * Is the bundled plugin copy present in the theme?
 */
function dazhen_bundled_blocks_present(): bool {
        return is_dir( DAZHEN_DIR . '/bundled/dazhen-blocks' ) && file_exists( DAZHEN_DIR . '/bundled/dazhen-blocks/dazhen-blocks.php' );
}

/**
 * Render the page.
 */
function dazhen_guide_render(): void {
        $plugin_active  = dazhen_blocks_plugin_active();
        $plugin_current = dazhen_blocks_plugin_is_current();
        $plugin_version = dazhen_blocks_plugin_version();
        $bundled        = dazhen_bundled_blocks_present();
        $installed      = is_dir( WP_PLUGIN_DIR . '/dazhen-blocks' );
        $pages_state    = $plugin_current ? dzb_bootstrap_state() : null;
        $pages_missing  = $plugin_current ? dzb_bootstrap_missing() : array();

        $install_url = wp_nonce_url(
                add_query_arg( 'action', 'dazhen_install_bundled_blocks', admin_url( 'admin-post.php' ) ),
                'dazhen_install_bundled_blocks'
        );
        $bootstrap_url = wp_nonce_url(
                add_query_arg( 'action', 'dazhen_bootstrap', admin_url( 'admin-post.php' ) ),
                'dazhen_bootstrap'
        );
        ?>
        <div class="wrap dazhen-setup">
                <h1><?php esc_html_e( 'Dazhen Setup', 'dazhen' ); ?> <span class="dazhen-setup__ver">v<?php echo esc_html( (string) DAZHEN_VERSION ); ?></span></h1>

                <?php if ( isset( $_GET['dazhen_installed'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flag. ?>
                        <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Dazhen Blocks installed and activated — every page is now drag-and-drop editable.', 'dazhen' ); ?></p></div>
                <?php elseif ( isset( $_GET['dazhen_install_failed'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
                        <div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'Could not copy the bundled plugin. Upload dazhen-blocks.zip manually via Plugins → Add New.', 'dazhen' ); ?></p></div>
                <?php endif; ?>

                <h2><?php esc_html_e( '0 · Site pages — the complete site in one click', 'dazhen' ); ?></h2>
                <p class="dazhen-setup__lead"><?php esc_html_e( 'A fresh WordPress starts with no About / Services / Solutions / Contact pages — every link in the header would 404. Create the seven core pages pre-filled with their designed block layouts, plus the navigation menus and the static front page.', 'dazhen' ); ?></p>
                <table class="form-table" role="presentation">
                        <tr>
                                <th><?php esc_html_e( 'Dazhen pages', 'dazhen' ); ?></th>
                                <td>
                                        <?php if ( 'complete' === $pages_state ) : ?>
                                                <span class="dazhen-setup__ok">✔</span> <?php esc_html_e( 'All pages exist: Home, About Us, Services, Solutions, Contact, FAQ, Blog.', 'dazhen' ); ?>
                                                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>"><?php esc_html_e( 'Edit them →', 'dazhen' ); ?></a>
                                        <?php elseif ( $plugin_active && ! $plugin_current && current_user_can( 'install_plugins' ) ) : ?>
                                                <span style="color:#d63638;">⚠</span>
                                                <?php
                                                echo esc_html( sprintf(
                                                        /* translators: %s: the active plugin version, e.g. "2.0.0". */
                                                        __( 'The active Dazhen Blocks plugin (v%s) is too old for the ready-made pages — the site initializer shipped with v2.0.1. Update it with one click:', 'dazhen' ),
                                                        $plugin_version ? $plugin_version : '?'
                                                ) );
                                                ?>
                                                <p><a class="button button-primary" href="<?php echo esc_url( $install_url ); ?>"><?php esc_html_e( 'Update the block engine now', 'dazhen' ); ?></a></p>
                                                <p class="description"><?php esc_html_e( 'Replaces the old copy with the version bundled in this theme — your pages and blocks are untouched.', 'dazhen' ); ?></p>
                                        <?php elseif ( $plugin_current && current_user_can( 'edit_pages' ) && current_user_can( 'unfiltered_html' ) ) : ?>
                                                <span style="color:#d63638;">⚠</span>
                                                <?php
                                                echo esc_html( sprintf(
                                                        /* translators: %s: list of missing page titles. */
                                                        __( 'Missing: %s.', 'dazhen' ),
                                                        implode( ', ', $pages_missing )
                                                ) );
                                                ?>
                                                <p><a class="button button-primary" href="<?php echo esc_url( $bootstrap_url ); ?>"><?php esc_html_e( 'Create the pages · 一键初始化整站', 'dazhen' ); ?></a></p>
                                                <p class="description"><?php esc_html_e( 'Only missing pages are created — existing content is never overwritten. Menus and the front page are set only when empty.', 'dazhen' ); ?></p>
                                        <?php elseif ( $plugin_active ) : ?>
                                                <p class="description"><?php esc_html_e( 'Log in as an administrator to initialize the pages.', 'dazhen' ); ?></p>
                                        <?php else : ?>
                                                <p class="description"><?php esc_html_e( 'Install the block engine below first — the ready-made pages ship with it. On a fresh site they are even created automatically right after activation.', 'dazhen' ); ?></p>
                                        <?php endif; ?>
                                </td>
                        </tr>
                </table>

                <h2><?php esc_html_e( '1 · The block engine (companion plugin)', 'dazhen' ); ?></h2>
                <p class="dazhen-setup__lead"><?php esc_html_e( 'Dazhen Blocks is the self-developed content-block kit that makes every page drag-and-drop editable. It ships inside this theme — install it with one click.', 'dazhen' ); ?></p>

                <table class="form-table" role="presentation">
                        <tr>
                                <th>Dazhen Blocks</th>
                                <td>
                                        <?php if ( $plugin_active && $plugin_current ) : ?>
                                                <span class="dazhen-setup__ok">✔</span> <?php echo esc_html( sprintf( /* translators: %s: version number. */ __( 'Active — v%s · 17 content blocks, 8 page layouts, 18 patterns.', 'dazhen' ), $plugin_version ) ); ?>
                                        <?php elseif ( $plugin_active ) : ?>
                                                <span style="color:#d63638;">⚠</span>
                                                <?php
                                                echo esc_html( sprintf(
                                                        /* translators: %s: the active plugin version, e.g. "2.0.0". */
                                                        __( 'Outdated copy active (v%s) — the page initializer and newest blocks are missing.', 'dazhen' ),
                                                        $plugin_version ? $plugin_version : '?'
                                                ) );
                                                ?>
                                                <?php if ( $bundled && current_user_can( 'install_plugins' ) ) : ?>
                                                        <a class="button button-primary" href="<?php echo esc_url( $install_url ); ?>"><?php esc_html_e( 'Update to the bundled version', 'dazhen' ); ?></a>
                                                <?php endif; ?>
                                        <?php elseif ( $bundled && current_user_can( 'install_plugins' ) ) : ?>
                                                <a class="button button-primary" href="<?php echo esc_url( $install_url ); ?>"><?php esc_html_e( 'Install & activate now', 'dazhen' ); ?></a>
                                                <?php if ( $installed ) : ?>
                                                        <p class="description"><?php esc_html_e( 'A copy already exists in wp-content/plugins — clicking will replace it with the theme-bundled version.', 'dazhen' ); ?></p>
                                                <?php endif; ?>
                                        <?php else : ?>
                                                <p class="description"><?php esc_html_e( 'The bundled copy was not found — download dazhen-blocks.zip and install via Plugins → Add New.', 'dazhen' ); ?></p>
                                        <?php endif; ?>
                                </td>
                        </tr>
                </table>

                <h3><?php esc_html_e( 'The 17 blocks · 17 个内容块', 'dazhen' ); ?></h3>
                <p class="description"><?php esc_html_e( 'Find them in the block inserter under “Dazhen Blocks · 大振内容块”, or press “+”, type “dazhen”, pick, then edit everything from the right sidebar.', 'dazhen' ); ?></p>
                <ol class="dazhen-setup__blocks">
                        <li><strong>Hero 英雄区</strong> — <?php esc_html_e( 'page opener: split or centered, two buttons, image', 'dazhen' ); ?></li>
                        <li><strong>Text + Image 图文分栏</strong> — <?php esc_html_e( 'two-column story with checklist', 'dazhen' ); ?></li>
                        <li><strong>Feature Cards 卖点卡</strong> — <?php esc_html_e( 'icon card grid (12 icons)', 'dazhen' ); ?></li>
                        <li><strong>Stats 数据条</strong> — <?php esc_html_e( 'big numbers with labels', 'dazhen' ); ?></li>
                        <li><strong>Products 产品网格</strong> — <?php esc_html_e( 'live catalog grid, new products appear automatically', 'dazhen' ); ?></li>
                        <li><strong>Process 流程步骤</strong> — <?php esc_html_e( 'numbered journey / how-it-works', 'dazhen' ); ?></li>
                        <li><strong>Gallery 图墙</strong> — <?php esc_html_e( 'multi-image grid', 'dazhen' ); ?></li>
                        <li><strong>Testimonials 客户评价</strong> — <?php esc_html_e( 'client quotes with name and role', 'dazhen' ); ?></li>
                        <li><strong>Client Logos 客户墙</strong> — <?php esc_html_e( 'logo wall (image or text chips)', 'dazhen' ); ?></li>
                        <li><strong>Specs Table 参数表</strong> — <?php esc_html_e( 'two-column parameter table', 'dazhen' ); ?></li>
                        <li><strong>FAQ 常见问题</strong> — <?php esc_html_e( 'accordion questions and answers', 'dazhen' ); ?></li>
                        <li><strong>CTA Banner 行动号召</strong> — <?php esc_html_e( 'conversion banner with the amber quote button', 'dazhen' ); ?></li>
                        <li><strong>Inquiry 询盘表单</strong> — <?php esc_html_e( 'the lead form (submissions land in Inquiries + email)', 'dazhen' ); ?></li>
                        <li><strong>Contact Cards 联系卡</strong> — <?php esc_html_e( 'phone / email / address cards', 'dazhen' ); ?></li>
                        <li><strong>Milestones 时间线</strong> — <?php esc_html_e( 'year-by-year company story', 'dazhen' ); ?></li>
                        <li><strong>Section 万能容器</strong> — <?php esc_html_e( 'pick background + padding, drop ANY blocks inside (core, Stackable…)', 'dazhen' ); ?></li>
                        <li><strong>Quote Button 询盘按钮</strong> — <?php esc_html_e( 'inline conversion button', 'dazhen' ); ?></li>
                </ol>

                <h3><?php esc_html_e( 'Ready-made pages · 整页模板', 'dazhen' ); ?></h3>
                <p class="description"><?php esc_html_e( 'In the editor inserter open “Patterns → Dazhen Pages” for Home / About / Services / Solutions / Contact / FAQ / Campaign landing / Starter. The Pages list also offers one-click filling of empty pages.', 'dazhen' ); ?></p>

                <h2><?php esc_html_e( '2 · Recommended free plugins', 'dazhen' ); ?></h2>
                <p class="dazhen-setup__lead"><?php esc_html_e( 'Optional but recommended — all free and compatible with the theme.', 'dazhen' ); ?></p>
                <table class="widefat striped dazhen-setup__plugins">
                        <thead>
                                <tr><th><?php esc_html_e( 'Plugin', 'dazhen' ); ?></th><th><?php esc_html_e( 'Why', 'dazhen' ); ?></th><th></th></tr>
                        </thead>
                        <tbody>
                                <?php foreach ( dazhen_guide_recommended_plugins() as $slug => $info ) : ?>
                                        <tr>
                                                <td><strong><?php echo esc_html( $info['name'] ); ?></strong></td>
                                                <td><?php echo esc_html( $info['why'] ); ?></td>
                                                <td class="dazhen-setup__action">
                                                        <?php
                                                        $active = dazhen_guide_plugin_is_active( $info['active'] );
                                                        if ( $active ) {
                                                                echo '<span style="color:#00a32a;">✔ ' . esc_html__( 'Active', 'dazhen' ) . '</span>';
                                                        } else {
                                                                echo '<a class="button" href="' . esc_url( wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $slug ), 'install-plugin_' . $slug ) ) . '">' . esc_html__( 'Install', 'dazhen' ) . '</a>';
                                                        }
                                                        ?>
                                                </td>
                                        </tr>
                                <?php endforeach; ?>
                        </tbody>
                </table>

                <h2><?php esc_html_e( '3 · Where to edit what', 'dazhen' ); ?></h2>
                <ul class="ul-disc">
                        <li><?php esc_html_e( 'Pages / homepage: open the page → blocks (or fill from the Pages list). Empty page = the built-in designed template.', 'dazhen' ); ?></li>
                        <li><?php esc_html_e( 'Header contact info / CTA text / footer: Appearance → Customize.', 'dazhen' ); ?></li>
                        <li><?php esc_html_e( 'Products: Products → Add New (model, gallery, specs, related series).', 'dazhen' ); ?></li>
                        <li><?php esc_html_e( 'Inquiries: Inquiries inbox (menu) — RFQ popup + form submissions land there.', 'dazhen' ); ?></li>
                        <li><?php esc_html_e( 'Blog sidebar: Appearance → Widgets → Blog Sidebar (empty = full-width).', 'dazhen' ); ?></li>
                </ul>
        </div>
        <?php
}

/**
 * The recommended-plugin table rows.
 */
function dazhen_guide_recommended_plugins(): array {
        return array(
                'stackable-ultimate-gutenberg-blocks' => array(
                        'name'   => 'Stackable',
                        'why'    => __( 'Extra designer blocks — 100% compatible next to Dazhen Blocks.', 'dazhen' ),
                        'active' => 'stackable-ultimate-gutenberg-blocks/plugin.php',
                ),
                'gtranslate'                          => array(
                        'name'   => 'GTranslate',
                        'why'    => __( 'Instant machine translation (100+ languages) — the header switcher styles itself automatically.', 'dazhen' ),
                        'active' => 'gtranslate/gtranslate.php',
                ),
                'polylang'                            => array(
                        'name'   => 'Polylang',
                        'why'    => __( 'Human-managed translations for strategic markets — the header shows the A×B language dropdown.', 'dazhen' ),
                        'active' => 'polylang/polylang.php',
                ),
                'wp-mail-smtp'                        => array(
                        'name'   => 'WP Mail SMTP',
                        'why'    => __( 'Deliver inquiry emails reliably (without it, most hosts block outgoing mail).', 'dazhen' ),
                        'active' => 'wp-mail-smtp/wp_mail_smtp.php',
                ),
        );
}

/**
 * Plugin-active helper (also accepts a callable for custom checks).
 *
 * @param string|callable $check Plugin basename or callable.
 */
function dazhen_guide_plugin_is_active( $check ): bool {
        if ( is_callable( $check ) && is_string( $check ) && function_exists( $check ) ) {
                return (bool) call_user_func( $check );
        }
        if ( function_exists( 'is_plugin_active' ) && is_string( $check ) ) {
                return is_plugin_active( $check );
        }
        return false;
}

/**
 * Install the bundled Dazhen Blocks plugin (copy + activate).
 */
function dazhen_guide_install_bundled(): void {
        if ( ! current_user_can( 'install_plugins' ) ) {
                wp_safe_redirect( admin_url( 'themes.php?page=dazhen-setup&dazhen_install_failed=1' ) );
                exit;
        }
        check_admin_referer( 'dazhen_install_bundled_blocks' );

        if ( ! dazhen_bundled_blocks_present() ) {
                wp_safe_redirect( admin_url( 'themes.php?page=dazhen-setup&dazhen_install_failed=1' ) );
                exit;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $dest = WP_PLUGIN_DIR . '/dazhen-blocks';

        // Replace an existing copy (theme copy is the source of truth).
        if ( is_dir( $dest ) ) {
                if ( ! dazhen_rrmdir( $dest ) ) {
                        wp_safe_redirect( admin_url( 'themes.php?page=dazhen-setup&dazhen_install_failed=1' ) );
                        exit;
                }
        }

        // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- checked below.
        $copied = @mkdir( WP_PLUGIN_DIR . '/dazhen-blocks', 0755, true ) &&
                dazhen_rcopy( DAZHEN_DIR . '/bundled/dazhen-blocks', $dest );

        if ( ! $copied ) {
                wp_safe_redirect( admin_url( 'themes.php?page=dazhen-setup&dazhen_install_failed=1' ) );
                exit;
        }

        $activated = activate_plugin( 'dazhen-blocks/dazhen-blocks.php' );
        if ( is_wp_error( $activated ) ) {
                wp_safe_redirect( admin_url( 'themes.php?page=dazhen-setup&dazhen_install_failed=1' ) );
                exit;
        }

        wp_safe_redirect( admin_url( 'themes.php?page=dazhen-setup&dazhen_installed=1' ) );
        exit;
}
add_action( 'admin_post_dazhen_install_bundled_blocks', 'dazhen_guide_install_bundled' );

/**
 * Recursive copy.
 */
function dazhen_rcopy( string $src, string $dst ): bool {
        $dir = opendir( $src );
        if ( ! $dir ) {
                return false;
        }
        if ( ! is_dir( $dst ) && ! mkdir( $dst, 0755, true ) ) {
                closedir( $dir );
                return false;
        }
        $ok = true;
        while ( false !== ( $entry = readdir( $dir ) ) ) {
                if ( '.' === $entry || '..' === $entry ) {
                        continue;
                }
                $s = $src . '/' . $entry;
                $d = $dst . '/' . $entry;
                if ( is_dir( $s ) ) {
                        $ok = dazhen_rcopy( $s, $d ) && $ok;
                } else {
                        // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- result checked.
                        $ok = ( @copy( $s, $d ) !== false ) && $ok;
                }
        }
        closedir( $dir );
        return $ok;
}

/**
 * Recursive delete.
 */
function dazhen_rrmdir( string $dir ): bool {
        if ( ! is_dir( $dir ) ) {
                return true;
        }
        $items = scandir( $dir );
        foreach ( $items as $item ) {
                if ( '.' === $item || '..' === $item ) {
                        continue;
                }
                $path = $dir . '/' . $item;
                if ( is_dir( $path ) ) {
                        dazhen_rrmdir( $path );
                } else {
                        // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- best effort.
                        @unlink( $path );
                }
        }
        // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- result checked.
        return @rmdir( $dir );
}

/**
 * Tiny styling for the setup page.
 */
function dazhen_guide_admin_css(): void {
        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( ! $screen || 'appearance_page_dazhen-setup' !== $screen->id ) {
                return;
        }
        echo '<style>
        .dazhen-setup__ver { font-size: 0.6em; color: #757575; font-weight: 600; vertical-align: middle; }
        .dazhen-setup__ok { color: #00a32a; font-weight: 700; }
        .dazhen-setup__lead { max-width: 720px; font-size: 14px; }
        .dazhen-setup__blocks { columns: 2; max-width: 860px; margin-top: 8px; }
        .dazhen-setup__plugins { max-width: 860px; }
        .dazhen-setup__action { text-align: right; }
        @media (max-width: 782px) { .dazhen-setup__blocks { columns: 1; } }
        </style>';
}
add_action( 'admin_head', 'dazhen_guide_admin_css' );
