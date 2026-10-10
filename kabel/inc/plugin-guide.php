<?php
/**
 * Appearance → Kabel Setup — the companion guide page.
 *
 * One place for the owner to see what ships with the theme, create the
 * ready-made Kabel pages in one click, install the bundled Kabel Blocks
 * plugin, and grab the recommended free plugins (translation, SMTP).
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

/**
 * After activating the theme, land the owner on this setup page once.
 */
function kabel_guide_activation_redirect(): void {
        set_transient( 'kabel_goto_setup', 1, 5 * MINUTE_IN_SECONDS );
}
add_action( 'after_switch_theme', 'kabel_guide_activation_redirect' );

/**
 * Perform that redirect on a safe admin screen (never inside the customizer
 * or an async request).
 */
function kabel_guide_maybe_redirect(): void {
        if ( wp_doing_ajax() || ! current_user_can( 'edit_theme_options' ) ) {
                return;
        }
        if ( ! get_transient( 'kabel_goto_setup' ) ) {
                return;
        }

        $safe = array( 'index.php', 'plugins.php', 'themes.php', 'update.php', 'edit.php', 'upload.php', 'post.php', 'post-new.php' );
        if ( ! in_array( $GLOBALS['pagenow'] ?? '', $safe, true ) ) {
                return; // Keep the transient — try again on the next safe screen.
        }

        delete_transient( 'kabel_goto_setup' );

        if ( isset( $_GET['page'] ) && 'kabel-setup' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                return;
        }

        wp_safe_redirect( admin_url( 'themes.php?page=kabel-setup' ) );
        exit;
}
add_action( 'admin_init', 'kabel_guide_maybe_redirect', 2 );

/**
 * Register the page.
 */
function kabel_guide_menu(): void {
        add_theme_page(
                __( 'Kabel Setup', 'kabel' ),
                __( 'Kabel Setup', 'kabel' ),
                'edit_theme_options',
                'kabel-setup',
                'kabel_guide_render'
        );
}
add_action( 'admin_menu', 'kabel_guide_menu' );

/**
 * Is the Kabel Blocks plugin active?
 *
 * True only for the REAL plugin. While the theme's render fallback
 * (inc/blocks-fallback.php) is substituting the bundled engine, kbl_specs()
 * exists too — that is "safe mode", not the plugin, and the UI must keep
 * offering the one-click activation.
 */
function kabel_blocks_plugin_active(): bool {
        return function_exists( 'kbl_specs' ) && ! kabel_blocks_safe_mode();
}

/**
 * The active Kabel Blocks version, or '' when not loaded.
 */
function kabel_blocks_plugin_version(): string {
        return defined( 'KABEL_BLOCKS_VERSION' ) ? (string) KABEL_BLOCKS_VERSION : '';
}

/**
 * Is the loaded plugin new enough to create the ready-made pages?
 *
 * Capability double-check: an old copy can still expose the block engine
 * (kbl_specs) WITHOUT the site initializer (kbl_bootstrap_missing), and
 * the "Create the pages" button would then hit an unregistered admin-post
 * action. Only when both exist is the button safe to render.
 */
function kabel_blocks_plugin_is_current(): bool {
        return function_exists( 'kbl_specs' ) && function_exists( 'kbl_bootstrap_missing' );
}

/**
 * Version of the engine copy bundled inside this theme ('' when absent).
 *
 * The active plugin can lag behind the theme — e.g. only kabel.zip was
 * replaced while the installed kabel-blocks plugin kept its old version.
 * Comparing the two is what turns that silent feature gap ("the language
 * source dropdown never showed up") into a visible one-click update.
 */
function kabel_blocks_bundled_version(): string {
        static $v = null;
        if ( null === $v ) {
                $v    = '';
                $file = KABEL_DIR . '/bundled/kabel-blocks/kabel-blocks.php';
                if ( is_readable( $file ) ) {
                        $data = get_file_data( $file, array( 'Version' => 'Version' ) );
                        $v    = isset( $data['Version'] ) ? trim( (string) $data['Version'] ) : '';
                }
        }
        return $v;
}

/**
 * Is the ACTIVE engine at least as new as the bundled copy?
 *
 * An older-but-current engine still renders every saved block (is_current
 * covers the hard floor) — it just misses the newest blocks and features:
 * the GTranslate language source, the header layout seeds and the
 * responsive header font sizes.
 */
function kabel_blocks_plugin_is_latest(): bool {
        $bundled = kabel_blocks_bundled_version();
        if ( '' === $bundled ) {
                return true; // Nothing bundled to compare against.
        }
        $active = kabel_blocks_plugin_version();
        if ( '' === $active ) {
                return false; // Engine missing entirely.
        }
        return version_compare( $active, $bundled, '>=' );
}

/**
 * Is the bundled plugin copy present in the theme?
 */
function kabel_bundled_blocks_present(): bool {
        return is_dir( KABEL_DIR . '/bundled/kabel-blocks' ) && file_exists( KABEL_DIR . '/bundled/kabel-blocks/kabel-blocks.php' );
}

/**
 * The full Kabel page map shown on the setup card.
 *
 * @return array<int, string>
 */
function kabel_guide_page_list(): array {
        return array(
                __( 'Home', 'kabel' ),
                __( 'About Us', 'kabel' ),
                __( 'ECO Friendly', 'kabel' ),
                __( 'Designer', 'kabel' ),
                __( 'Morandi Colors', 'kabel' ),
                __( 'Van Gogh Color', 'kabel' ),
                __( 'Macaron colors', 'kabel' ),
                __( 'Museum Collection Colors', 'kabel' ),
                __( 'Project Procurement', 'kabel' ),
                __( 'Customize', 'kabel' ),
                __( 'Distributor', 'kabel' ),
                __( 'Blog', 'kabel' ),
                __( 'FAQ', 'kabel' ),
                __( 'Contact', 'kabel' ),
        );
}

/**
 * Render the page.
 */
function kabel_guide_render(): void {
        $plugin_active  = kabel_blocks_plugin_active();
        $plugin_current = kabel_blocks_plugin_is_current();
        $plugin_latest  = kabel_blocks_plugin_is_latest();
        $plugin_version = kabel_blocks_plugin_version();
        $bundled        = kabel_bundled_blocks_present();
        $bundled_ver    = kabel_blocks_bundled_version();
        $installed      = is_dir( WP_PLUGIN_DIR . '/kabel-blocks' );
        $pages_state    = $plugin_current ? kbl_bootstrap_state() : null;
        $pages_missing  = $plugin_current ? kbl_bootstrap_missing() : array();

        $install_url = wp_nonce_url(
                add_query_arg( 'action', 'kabel_install_bundled_blocks', admin_url( 'admin-post.php' ) ),
                'kabel_install_bundled_blocks'
        );
        $activate_url = wp_nonce_url(
                add_query_arg( 'action', 'kabel_activate_bundled', admin_url( 'admin-post.php' ) ),
                'kabel_activate_bundled'
        );
        $bootstrap_url = wp_nonce_url(
                add_query_arg( 'action', 'kabel_bootstrap', admin_url( 'admin-post.php' ) ),
                'kabel_bootstrap'
        );
        $trash_demo_url = wp_nonce_url(
                add_query_arg( 'action', 'kabel_trash_demo', admin_url( 'admin-post.php' ) ),
                'kabel_trash_demo'
        );
        $demo_pages = kabel_demo_pages_present();
        ?>
        <div class="wrap kabel-setup">
                <h1><?php esc_html_e( 'Kabel Setup', 'kabel' ); ?> <span class="kabel-setup__ver">v<?php echo esc_html( (string) KABEL_VERSION ); ?></span></h1>

                <?php if ( isset( $_GET['kabel_installed'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flag. ?>
                        <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Kabel Blocks installed and activated — every page is now drag-and-drop editable.', 'kabel' ); ?></p></div>
                <?php elseif ( isset( $_GET['kabel_install_failed'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
                        <div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'Could not copy the bundled plugin. Upload kabel-blocks.zip manually via Plugins → Add New.', 'kabel' ); ?></p></div>
                <?php endif; ?>

                <?php if ( isset( $_GET['kabel_demo_trashed'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flag. ?>
                        <?php $demo_trashed = max( 0, (int) $_GET['kabel_demo_trashed'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
                        <?php if ( $demo_trashed > 0 ) : ?>
                                <div class="notice notice-success is-dismissible"><p><?php
                                        echo esc_html( sprintf(
                                                /* translators: %s: number of pages. */
                                                _n( '%s demo page moved to Trash. Restore them from Pages → Trash if needed.', '%s demo pages moved to Trash. Restore them from Pages → Trash if needed.', $demo_trashed, 'kabel' ),
                                                number_format_i18n( $demo_trashed )
                                        ) );
                                ?></p></div>
                        <?php else : ?>
                                <div class="notice notice-info is-dismissible"><p><?php esc_html_e( 'No demo pages were found — nothing was moved to Trash.', 'kabel' ); ?></p></div>
                        <?php endif; ?>
                <?php elseif ( isset( $_GET['kabel_demo_no_trash'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flag. ?>
                        <div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'The Trash is disabled on this site (EMPTY_TRASH_DAYS is 0), so the demo pages cannot be moved to the Trash safely.', 'kabel' ); ?></p></div>
                <?php endif; ?>

                <h2><?php esc_html_e( '0 · Site pages — the complete Kabel site in one click', 'kabel' ); ?></h2>
                <p class="kabel-setup__lead"><?php esc_html_e( 'A fresh WordPress starts with no pages at all — every link in the header would 404. One click creates the fourteen Kabel pages pre-filled with their designed block layouts (plus the six paint families, the demo articles and projects, the navigation menus and the static front page).', 'kabel' ); ?></p>
                <table class="form-table" role="presentation">
                        <tr>
                                <th><?php esc_html_e( 'Kabel pages', 'kabel' ); ?></th>
                                <td>
                                        <?php if ( 'complete' === $pages_state ) : ?>
                                                <span class="kabel-setup__ok">✔</span> <?php esc_html_e( 'All pages exist:', 'kabel' ); ?>
                                                <?php echo esc_html( implode( ' · ', kabel_guide_page_list() ) ); ?>.
                                                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>"><?php esc_html_e( 'Edit them →', 'kabel' ); ?></a>
                                        <?php elseif ( kabel_blocks_safe_mode() ) : ?>
                                                <span style="color:#d63638;">⚠</span>
                                                <?php esc_html_e( 'The block engine is not active — the theme is currently rendering your pages from its built-in safe-mode copy.', 'kabel' ); ?>
                                                <p><a class="button button-primary" href="<?php echo esc_url( $activate_url ); ?>"><?php esc_html_e( 'Activate Kabel Blocks', 'kabel' ); ?></a></p>
                                                <p class="description"><?php esc_html_e( 'Your pages stay online either way; activating unlocks drag-and-drop editing, the ready-made pages and the one-click site initializer.', 'kabel' ); ?></p>
                                        <?php elseif ( $plugin_active && ! $plugin_current && current_user_can( 'install_plugins' ) ) : ?>
                                                <span style="color:#d63638;">⚠</span>
                                                <?php
                                                echo esc_html( sprintf(
                                                        /* translators: %s: the active plugin version, e.g. "1.0.0". */
                                                        __( 'The active Kabel Blocks plugin (v%s) is too old for the ready-made pages — this copy does not include the site initializer. Update it with one click:', 'kabel' ),
                                                        $plugin_version ? $plugin_version : '?'
                                                ) );
                                                ?>
                                                <p><a class="button button-primary" href="<?php echo esc_url( $install_url ); ?>"><?php esc_html_e( 'Update the block engine now', 'kabel' ); ?></a></p>
                                                <p class="description"><?php esc_html_e( 'Replaces the old copy with the version bundled in this theme — your pages and blocks are untouched.', 'kabel' ); ?></p>
                                        <?php elseif ( $plugin_current && current_user_can( 'edit_pages' ) && current_user_can( 'unfiltered_html' ) ) : ?>
                                                <span style="color:#d63638;">⚠</span>
                                                <?php
                                                echo esc_html( sprintf(
                                                        /* translators: %s: list of missing page titles. */
                                                        __( 'Missing: %s.', 'kabel' ),
                                                        implode( ', ', $pages_missing )
                                                ) );
                                                ?>
                                                <p><a class="button button-primary" href="<?php echo esc_url( $bootstrap_url ); ?>"><?php esc_html_e( 'Create the pages', 'kabel' ); ?></a></p>
                                                <p class="description"><?php esc_html_e( 'Only missing pages are created — existing content is never overwritten. Menus and the front page are set only when empty.', 'kabel' ); ?></p>
                                        <?php elseif ( $plugin_active ) : ?>
                                                <p class="description"><?php esc_html_e( 'Log in as an administrator to initialize the pages.', 'kabel' ); ?></p>
                                        <?php else : ?>
                                                <p class="description"><?php esc_html_e( 'Install the block engine below first — the ready-made pages ship with it. On a fresh site they are even created automatically right after activation.', 'kabel' ); ?></p>
                                        <?php endif; ?>
                                </td>
                        </tr>
                </table>

                <h2><?php esc_html_e( '1 · The block engine (companion plugin)', 'kabel' ); ?></h2>
                <p class="kabel-setup__lead"><?php esc_html_e( 'Kabel Blocks is the self-developed content-block kit that makes every page drag-and-drop editable. It ships inside this theme — install it with one click.', 'kabel' ); ?></p>

                <table class="form-table" role="presentation">
                        <tr>
                                <th>Kabel Blocks</th>
                                <td>
                                        <?php if ( $plugin_active && $plugin_current && $plugin_latest ) : ?>
                                                <span class="kabel-setup__ok">✔</span> <?php echo esc_html( sprintf( /* translators: %s: version number. */ __( 'Active — v%s · the Stackable-style block suite (free-nesting containers, inline editing), full-page layouts, insert patterns and the site initializer.', 'kabel' ), $plugin_version ) ); ?>
                                        <?php elseif ( $plugin_active && $plugin_current ) : ?>
                                                <span style="color:#b26a00;">⚠</span>
                                                <?php
                                                echo esc_html( sprintf(
                                                        /* translators: 1: the active plugin version, 2: the bundled version. */
                                                        __( 'Update available — the active engine (v%1$s) is older than the one bundled with this theme (v%2$s). The newest features (GTranslate language source, header layout presets, responsive header sizes) need the newer engine.', 'kabel' ),
                                                        $plugin_version ? $plugin_version : '?',
                                                        $bundled_ver ? $bundled_ver : '?'
                                                ) );
                                                ?>
                                                <?php if ( $bundled && current_user_can( 'install_plugins' ) ) : ?>
                                                        <p><a class="button button-primary" href="<?php echo esc_url( $install_url ); ?>"><?php esc_html_e( 'Update the block engine now', 'kabel' ); ?></a></p>
                                                        <p class="description"><?php esc_html_e( 'Replaces the old copy with the theme-bundled version — your pages and blocks are untouched.', 'kabel' ); ?></p>
                                                <?php endif; ?>
                                        <?php elseif ( $plugin_active ) : ?>
                                                <span style="color:#d63638;">⚠</span>
                                                <?php
                                                echo esc_html( sprintf(
                                                        /* translators: %s: the active plugin version, e.g. "1.0.0". */
                                                        __( 'Outdated copy active (v%s) — the page initializer and newest blocks are missing.', 'kabel' ),
                                                        $plugin_version ? $plugin_version : '?'
                                                ) );
                                                ?>
                                                <?php if ( $bundled && current_user_can( 'install_plugins' ) ) : ?>
                                                        <a class="button button-primary" href="<?php echo esc_url( $install_url ); ?>"><?php esc_html_e( 'Update to the bundled version', 'kabel' ); ?></a>
                                                <?php endif; ?>
                                        <?php elseif ( $bundled && current_user_can( 'install_plugins' ) ) : ?>
                                                <a class="button button-primary" href="<?php echo esc_url( $install_url ); ?>"><?php esc_html_e( 'Install & activate now', 'kabel' ); ?></a>
                                                <?php if ( $installed ) : ?>
                                                        <p class="description"><?php esc_html_e( 'A copy already exists in wp-content/plugins — clicking will replace it with the theme-bundled version.', 'kabel' ); ?></p>
                                                <?php endif; ?>
                                        <?php else : ?>
                                                <p class="description"><?php esc_html_e( 'The bundled copy was not found — download kabel-blocks.zip and install via Plugins → Add New.', 'kabel' ); ?></p>
                                        <?php endif; ?>
                                </td>
                        </tr>
                </table>

                <h3><?php esc_html_e( 'The block suite', 'kabel' ); ?></h3>
                <p class="description"><?php esc_html_e( 'Find them at the TOP of the block inserter under “Kabel Blocks” (or press “+”, type a keyword). Every text is click-to-edit right on the page; the sidebar carries Layout / Style / Advanced tabs — colors, alignment, corner radius, shadows.', 'kabel' ); ?></p>
                <ol class="kabel-setup__blocks">
                        <li><strong>Section</strong> — <?php esc_html_e( 'full-width band: dark / navy / white tone, background image + overlay, padding rhythm, narrow or full-bleed content', 'kabel' ); ?></li>
                        <li><strong>Columns</strong> — <?php esc_html_e( 'a row of 1–6 columns with live count control, gap and vertical alignment', 'kabel' ); ?></li>
                        <li><strong>Column</strong> — <?php esc_html_e( 'free container: background color or image, padding, radius, shadow, border — the card look', 'kabel' ); ?></li>
                        <li><strong>Heading / Text</strong> — <?php esc_html_e( 'inline-editable title and paragraph with sizes and eyebrow style', 'kabel' ); ?></li>
                        <li><strong>Button + Button row</strong> — <?php esc_html_e( 'bronze / navy / outline / ghost buttons, optional inquiry-popup trigger', 'kabel' ); ?></li>
                        <li><strong>Image</strong> — <?php esc_html_e( 'width, radius, caption, link', 'kabel' ); ?></li>
                        <li><strong>Icon / Icon box</strong> — <?php esc_html_e( '31 curated stroke icons; icon + title + text cards in one block', 'kabel' ); ?></li>
                        <li><strong>List</strong> — <?php esc_html_e( 'bronze check / dash / dot bullets', 'kabel' ); ?></li>
                        <li><strong>Stat</strong> — <?php esc_html_e( 'big number + label with prefix / suffix', 'kabel' ); ?></li>
                        <li><strong>FAQ item</strong> — <?php esc_html_e( 'question + collapsible answer (native, no JS)', 'kabel' ); ?></li>
                        <li><strong>Spacer / Separator</strong> — <?php esc_html_e( 'vertical gap and the bronze hairline', 'kabel' ); ?></li>
                        <li><strong>Inquiry form</strong> — <?php esc_html_e( 'the consultation form (submissions land in Inquiries + email)', 'kabel' ); ?></li>
                        <li><strong>Post grid</strong> — <?php esc_html_e( 'live products / projects / posts grid — new entries appear automatically', 'kabel' ); ?></li>
                </ol>

                <h3><?php esc_html_e( 'Ready-made pages', 'kabel' ); ?></h3>
                <p class="description"><?php esc_html_e( 'In the editor inserter open “Patterns → Kabel Pages” for full-page starting points (home, about, colors, procurement, distributor…). The Pages list also offers one-click filling of empty pages.', 'kabel' ); ?></p>

                <h2><?php esc_html_e( '2 · Recommended free plugins', 'kabel' ); ?></h2>
                <p class="kabel-setup__lead"><?php esc_html_e( 'Optional but recommended — all free and compatible with the theme.', 'kabel' ); ?></p>
                <table class="widefat striped kabel-setup__plugins">
                        <thead>
                                <tr><th><?php esc_html_e( 'Plugin', 'kabel' ); ?></th><th><?php esc_html_e( 'Why', 'kabel' ); ?></th><th></th></tr>
                        </thead>
                        <tbody>
                                <?php foreach ( kabel_guide_recommended_plugins() as $slug => $info ) : ?>
                                        <tr>
                                                <td><strong><?php echo esc_html( $info['name'] ); ?></strong></td>
                                                <td><?php echo esc_html( $info['why'] ); ?></td>
                                                <td class="kabel-setup__action">
                                                        <?php
                                                        $active = kabel_guide_plugin_is_active( $info['active'] );
                                                        if ( $active ) {
                                                                echo '<span style="color:#00a32a;">✔ ' . esc_html__( 'Active', 'kabel' ) . '</span>';
                                                        } else {
                                                                echo '<a class="button" href="' . esc_url( wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $slug ), 'install-plugin_' . $slug ) ) . '">' . esc_html__( 'Install', 'kabel' ) . '</a>';
                                                        }
                                                        ?>
                                                </td>
                                        </tr>
                                <?php endforeach; ?>
                        </tbody>
                </table>

                <h2><?php esc_html_e( '3 · Migrating from the Hostinger template site?', 'kabel' ); ?></h2>
                <p class="kabel-setup__lead"><?php esc_html_e( 'This theme replaces the whole template stack you started with. Nothing from the list below is needed any more — every layout, form and slider is now built from Kabel blocks.', 'kabel' ); ?></p>
                <table class="form-table" role="presentation">
                        <tr>
                                <th><?php esc_html_e( 'Safe to remove', 'kabel' ); ?></th>
                                <td>
                                        <p class="description" style="margin-top:0;">
                                                <?php esc_html_e( 'Deactivate and delete these plugins after switching to Kabel — they are no longer used and only slow the site down:', 'kabel' ); ?>
                                                <strong>Blocksy</strong> <?php esc_html_e( '(and Blocksy Companion)', 'kabel' ); ?>,
                                                <strong>Stackable</strong>, <strong>Smart Slider 3</strong>, <strong>WPForms Lite</strong>,
                                                <strong>WooCommerce</strong><?php esc_html_e( ', plus the Hostinger helper plugins (Hostinger / Hostinger Tools / Easy Updates / LiteSpeed Cache — keep LiteSpeed only if your host asks you to).', 'kabel' ); ?>
                                        </p>
                                        <p class="description">
                                                <?php esc_html_e( 'Deleting a plugin never deletes your media library — every image the demo content references (and your three product videos) stay right where they are, and the Kabel initializer re-uses them automatically instead of re-uploading.', 'kabel' ); ?>
                                        </p>
                                </td>
                        </tr>
                </table>

                <h2><?php esc_html_e( '4 · Your brand facts', 'kabel' ); ?></h2>
                <p class="kabel-setup__lead"><?php esc_html_e( 'The contact details below ship as the site defaults. Update them any time under Appearance → Customize → Contact & CTA — the header, footer, contact page and every email pull from there.', 'kabel' ); ?></p>
                <table class="form-table" role="presentation">
                        <tr>
                                <th><?php esc_html_e( 'Contact', 'kabel' ); ?></th>
                                <td>
                                        <?php echo esc_html( sprintf( '%s %s', __( 'Headquarters:', 'kabel' ), __( 'Druento, Torino, Italy', 'kabel' ) ) ); ?><br>
                                        <?php echo esc_html( sprintf( '%s %s', __( 'Branch:', 'kabel' ), __( 'Foshan, Guangdong, China', 'kabel' ) ) ); ?><br>
                                        <?php echo esc_html( sprintf( '%s +86 15989993891', __( 'Phone:', 'kabel' ) ) ); ?><br>
                                        <?php echo esc_html( sprintf( '%s +86 18033205148', __( 'WhatsApp:', 'kabel' ) ) ); ?><br>
                                        <?php echo esc_html( sprintf( '%s kabelpaints@gmail.com', __( 'Email:', 'kabel' ) ) ); ?>
                                </td>
                        </tr>
                        <tr>
                                <th><?php esc_html_e( 'Social', 'kabel' ); ?></th>
                                <td>
                                        <a href="https://www.facebook.com/profile.php?id=61575873133832" target="_blank" rel="noopener noreferrer">Facebook</a> ·
                                        <a href="https://www.instagram.com/kabelpaints/" target="_blank" rel="noopener noreferrer">Instagram</a> ·
                                        <a href="https://www.youtube.com/@kabelpaint/videos" target="_blank" rel="noopener noreferrer">YouTube</a> ·
                                        <a href="https://www.tiktok.com/@kabel.paint" target="_blank" rel="noopener noreferrer">TikTok</a>
                                </td>
                        </tr>
                </table>

                <h2><?php esc_html_e( '5 · Where to edit what', 'kabel' ); ?></h2>
                <ul class="ul-disc">
                        <li><?php esc_html_e( 'Pages / homepage: open the page → blocks (or fill from the Pages list). An empty page falls back to its designed template.', 'kabel' ); ?></li>
                        <li><?php esc_html_e( 'Paint families: Paint Series → Add New (tagline, selling points, specification table, gallery, family).', 'kabel' ); ?></li>
                        <li><?php esc_html_e( 'Project references: Projects → Add New — categorize as Home Renovation Projects or Engineering Projects.', 'kabel' ); ?></li>
                        <li><?php esc_html_e( 'Inquiries: the Inquiries inbox — consultation popup and form submissions land there (CSV export included).', 'kabel' ); ?></li>
                        <li><?php esc_html_e( 'Header contact info / CTA text / footer: Appearance → Customize.', 'kabel' ); ?></li>
                        <li><?php esc_html_e( 'Blog sidebar: Appearance → Widgets → Blog Sidebar (empty = full-width).', 'kabel' ); ?></li>
                </ul>

                <h2><?php esc_html_e( '6 · Tools', 'kabel' ); ?></h2>
                <p class="kabel-setup__lead"><?php esc_html_e( 'Housekeeping utilities — the counterpart to the one-click site initializer at the top of this page.', 'kabel' ); ?></p>
                <table class="form-table" role="presentation">
                        <tr>
                                <th><?php esc_html_e( 'Reset demo content', 'kabel' ); ?></th>
                                <td>
                                        <?php if ( array() !== $demo_pages ) : ?>
                                                <p class="description" style="margin-top:0;"><?php esc_html_e( 'The ready-made Kabel pages currently on this site:', 'kabel' ); ?></p>
                                                <ul class="kabel-setup__demo-pages">
                                                        <?php foreach ( $demo_pages as $demo_page ) : ?>
                                                                <li>
                                                                        <a href="<?php echo esc_url( admin_url( 'post.php?post=' . $demo_page->ID . '&action=edit' ) ); ?>"><?php echo esc_html( get_the_title( $demo_page ) ); ?></a>
                                                                        <code><?php echo esc_html( $demo_page->post_name ); ?></code>
                                                                </li>
                                                        <?php endforeach; ?>
                                                </ul>
                                                <?php if ( current_user_can( 'edit_pages' ) ) : ?>
                                                        <p><a class="button kabel-setup__danger" href="<?php echo esc_url( $trash_demo_url ); ?>"><?php esc_html_e( 'Move all demo pages to Trash', 'kabel' ); ?></a></p>
                                                <?php endif; ?>
                                                <p class="description"><?php esc_html_e( 'Moves the pages listed above into the Trash — restore any of them from Pages → Trash. Your own pages, posts, products, projects and media library are never touched.', 'kabel' ); ?></p>
                                        <?php else : ?>
                                                <p class="description"><?php esc_html_e( 'No demo pages found.', 'kabel' ); ?></p>
                                        <?php endif; ?>
                                </td>
                        </tr>
                </table>
        </div>
        <?php
}

/**
 * The recommended-plugin table rows.
 */
function kabel_guide_recommended_plugins(): array {
        return array(
                'wp-mail-smtp' => array(
                        'name'   => 'WP Mail SMTP',
                        'why'    => __( 'Deliver inquiry emails reliably (without it, most hosts block outgoing mail).', 'kabel' ),
                        'active' => 'wp-mail-smtp/wp_mail_smtp.php',
                ),
                'polylang'     => array(
                        'name'   => 'Polylang',
                        'why'    => __( 'Human-managed translations for strategic markets — the header switcher picks it up automatically.', 'kabel' ),
                        'active' => 'polylang/polylang.php',
                ),
                'gtranslate'   => array(
                        'name'   => 'GTranslate',
                        'why'    => __( 'Instant machine translation (100+ languages) — the header shows the same styled switcher.', 'kabel' ),
                        'active' => 'gtranslate/gtranslate.php',
                ),
        );
}

/**
 * Plugin-active helper (also accepts a callable for custom checks).
 *
 * @param string|callable $check Plugin basename or callable.
 */
function kabel_guide_plugin_is_active( $check ): bool {
        if ( is_callable( $check ) && is_string( $check ) && function_exists( $check ) ) {
                return (bool) call_user_func( $check );
        }
        if ( function_exists( 'is_plugin_active' ) && is_string( $check ) ) {
                return is_plugin_active( $check );
        }
        return false;
}

/**
 * Install the bundled Kabel Blocks plugin (copy + activate).
 */
function kabel_guide_install_bundled(): void {
        if ( ! current_user_can( 'install_plugins' ) ) {
                wp_safe_redirect( admin_url( 'themes.php?page=kabel-setup&kabel_install_failed=1' ) );
                exit;
        }
        check_admin_referer( 'kabel_install_bundled_blocks' );

        if ( ! kabel_bundled_blocks_present() ) {
                wp_safe_redirect( admin_url( 'themes.php?page=kabel-setup&kabel_install_failed=1' ) );
                exit;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $dest = WP_PLUGIN_DIR . '/kabel-blocks';

        // Replace an existing copy (theme copy is the source of truth).
        if ( is_dir( $dest ) ) {
                if ( ! kabel_rrmdir( $dest ) ) {
                        wp_safe_redirect( admin_url( 'themes.php?page=kabel-setup&kabel_install_failed=1' ) );
                        exit;
                }
        }

        // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- checked below.
        $copied = @mkdir( WP_PLUGIN_DIR . '/kabel-blocks', 0755, true ) &&
                kabel_rcopy( KABEL_DIR . '/bundled/kabel-blocks', $dest );

        if ( ! $copied ) {
                wp_safe_redirect( admin_url( 'themes.php?page=kabel-setup&kabel_install_failed=1' ) );
                exit;
        }

        $activated = activate_plugin( 'kabel-blocks/kabel-blocks.php' );
        if ( is_wp_error( $activated ) ) {
                wp_safe_redirect( admin_url( 'themes.php?page=kabel-setup&kabel_install_failed=1' ) );
                exit;
        }

        wp_safe_redirect( admin_url( 'themes.php?page=kabel-setup&kabel_installed=1' ) );
        exit;
}
add_action( 'admin_post_kabel_install_bundled_blocks', 'kabel_guide_install_bundled' );

/**
 * Persistent "safe mode" admin notice with a one-click plugin activation.
 *
 * Shows on every admin screen (for users who can activate plugins) for as
 * long as the theme renders through its bundled engine instead of the real
 * Kabel Blocks plugin. Not dismissible — the only way out is activating
 * the plugin (or switching the theme).
 */
function kabel_blocks_safe_mode_notice(): void {
        if ( ! current_user_can( 'activate_plugins' ) ) {
                return;
        }

        // Success flag from kabel_handle_activate_bundled().
        if ( isset( $_GET['kabel_activated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flag.
                printf(
                        '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
                        esc_html__( 'Kabel Blocks is active — every page is now drag-and-drop editable, and safe mode is off.', 'kabel' )
                );
                return;
        }

        if ( kabel_blocks_plugin_active() ) {
                return;
        }

        // Failure flag from kabel_handle_activate_bundled() — the manual path.
        if ( isset( $_GET['kabel_activate_failed'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flag.
                printf(
                        '<div class="notice notice-error"><p>%s</p></div>',
                        esc_html__( 'Activating Kabel Blocks failed. Install it manually: Plugins → Add New → Upload the kabel-blocks.zip that ships with the theme, then Activate.', 'kabel' )
                );
        }

        if ( ! kabel_bundled_blocks_present() ) {
                printf(
                        '<div class="notice notice-error"><p><strong>%s</strong> %s</p></div>',
                        esc_html__( 'Kabel theme is running in safe mode.', 'kabel' ),
                        esc_html__( 'Pages render, but editing needs the Kabel Blocks plugin — and the bundled copy inside the theme is missing. Install it via Plugins → Add New → Upload (kabel-blocks.zip), then Activate.', 'kabel' )
                );
                return;
        }

        $url = wp_nonce_url(
                add_query_arg( 'action', 'kabel_activate_bundled', admin_url( 'admin-post.php' ) ),
                'kabel_activate_bundled'
        );

        printf(
                '<div class="notice notice-warning"><p><strong>%s</strong> %s</p><p><a class="button button-primary" href="%s">%s</a></p></div>',
                esc_html__( 'Kabel theme is running in safe mode.', 'kabel' ),
                esc_html__( 'Pages render, but full editing needs the Kabel Blocks plugin. It ships with the theme; activate it with one click.', 'kabel' ),
                esc_url( $url ),
                esc_html__( 'Activate Kabel Blocks', 'kabel' )
        );
}
add_action( 'admin_notices', 'kabel_blocks_safe_mode_notice' );

/**
 * One-click activation of the bundled Kabel Blocks plugin.
 *
 * admin-post.php?action=kabel_activate_bundled. Copies the theme's bundled
 * plugin into wp-content/plugins ONLY when it is not already installed,
 * then activates it and redirects back. Plain-PHP recursive copy — no
 * shell calls, no WP_Filesystem dependency. Works even while the theme's
 * render fallback is booted in this very request (the plugin bootstrap
 * guards its constants, and the engine owns every function it defines).
 */
function kabel_handle_activate_bundled(): void {
        if ( ! current_user_can( 'activate_plugins' ) ) {
                wp_safe_redirect( add_query_arg( 'kabel_activate_failed', '1', admin_url( 'themes.php?page=kabel-setup' ) ) );
                exit;
        }

        check_admin_referer( 'kabel_activate_bundled' );

        $fail = static function (): void {
                wp_safe_redirect( add_query_arg( 'kabel_activate_failed', '1', admin_url( 'themes.php?page=kabel-setup' ) ) );
                exit;
        };

        if ( ! function_exists( 'activate_plugin' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if ( ! function_exists( 'activate_plugin' ) || ! defined( 'WP_PLUGIN_DIR' ) ) {
                $fail();
        }

        $dest = WP_PLUGIN_DIR . '/kabel-blocks';

        // Copy only when the plugin is not installed yet.
        if ( ! is_dir( $dest ) ) {
                if ( ! kabel_bundled_blocks_present() || ! function_exists( 'kabel_rcopy' ) ) {
                        $fail();
                }

                // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- result checked.
                $copied = @mkdir( $dest, 0755, true ) && kabel_rcopy( KABEL_DIR . '/bundled/kabel-blocks', $dest );
                if ( ! $copied ) {
                        $fail();
                }
        }

        if ( ! file_exists( $dest . '/kabel-blocks.php' ) ) {
                $fail();
        }

        /*
         * Same pre-flight as the render fallback: never activate a copy we
         * can prove is incomplete (a broken engine would white-screen the
         * site on the next request). Damaged bundle → manual path notice.
         */
        foreach ( array( 'inc/engine.php', 'inc/blocks-content.php', 'inc/blocks-proof.php', 'inc/blocks-convert.php' ) as $part ) {
                if ( ! is_file( $dest . '/' . $part ) || 0 === (int) filesize( $dest . '/' . $part ) ) {
                        $fail();
                }
        }

        try {
                $activated = activate_plugin( 'kabel-blocks/kabel-blocks.php' );
        } catch ( \Throwable $e ) {
                $activated = new WP_Error( 'kabel_blocks_activation_threw', (string) $e->getMessage() );
        }
        if ( is_wp_error( $activated ) ) {
                $fail();
        }

        $back = wp_get_referer();
        if ( ! $back ) {
                $back = admin_url( 'themes.php?page=kabel-setup' );
        }

        wp_safe_redirect( add_query_arg( 'kabel_activated', '1', $back ) );
        exit;
}
add_action( 'admin_post_kabel_activate_bundled', 'kabel_handle_activate_bundled' );

/**
 * Demo page slugs — the page blueprint of the one-click site initializer.
 *
 * Source of truth: kabel-blocks/inc/bootstrap.php → kbl_bootstrap_blueprint()
 * (the fourteen pages kbl_run_bootstrap() creates). Hardcoded on purpose so
 * the Tools box works even without the plugin — keep this list in sync when
 * the plugin blueprint changes.
 *
 * @return array<int, string>
 */
function kabel_demo_page_slugs(): array {
        return array(
                'home',
                'about',
                'eco-friendly',
                'designer',
                'morandi-colors',
                'van-gogh-color',
                'macaron-colors',
                'museum-collection-colors',
                'project-procurement',
                'customize',
                'distributor',
                'blog',
                'faq',
                'contact',
        );
}

/**
 * The demo pages currently live on this site (any status but Trash).
 *
 * @return WP_Post[]
 */
function kabel_demo_pages_present(): array {
        $found = array();
        foreach ( kabel_demo_page_slugs() as $slug ) {
                $page = get_page_by_path( $slug );
                if ( $page instanceof WP_Post && 'page' === $page->post_type && 'trash' !== $page->post_status ) {
                        $found[] = $page;
                }
        }
        return $found;
}

/**
 * Move every demo page to the Trash — the Elementor-style "reset" exit.
 *
 * admin-post.php?action=kabel_trash_demo. Pages are TRASHED, never hard
 * deleted (wp_trash_post only), so the owner can restore any of them from
 * Pages → Trash. Already-trashed or missing slugs are skipped. When the
 * static front page / posts page is among the trashed pages, the reading
 * settings are reset so the site never points at a trashed page.
 */
function kabel_handle_trash_demo(): void {
        if ( ! current_user_can( 'edit_pages' ) ) {
                wp_safe_redirect( admin_url( 'themes.php?page=kabel-setup' ) );
                exit;
        }

        check_admin_referer( 'kabel_trash_demo' );

        /* With the trash disabled, wp_trash_post() would delete PERMANENTLY — refuse. */
        if ( defined( 'EMPTY_TRASH_DAYS' ) && 0 === (int) EMPTY_TRASH_DAYS ) {
                wp_safe_redirect( add_query_arg( 'kabel_demo_no_trash', '1', admin_url( 'themes.php?page=kabel-setup' ) ) );
                exit;
        }

        $trashed_ids = array();
        foreach ( kabel_demo_page_slugs() as $slug ) {
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

        wp_safe_redirect( add_query_arg( 'kabel_demo_trashed', count( $trashed_ids ), admin_url( 'themes.php?page=kabel-setup' ) ) );
        exit;
}
add_action( 'admin_post_kabel_trash_demo', 'kabel_handle_trash_demo' );

/**
 * Recursive copy.
 */
function kabel_rcopy( string $src, string $dst ): bool {
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
                        $ok = kabel_rcopy( $s, $d ) && $ok;
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
function kabel_rrmdir( string $dir ): bool {
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
                        kabel_rrmdir( $path );
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
function kabel_guide_admin_css(): void {
        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( ! $screen || 'appearance_page_kabel-setup' !== $screen->id ) {
                return;
        }
        echo '<style>
        .kabel-setup__ver { font-size: 0.6em; color: #757575; font-weight: 600; vertical-align: middle; }
        .kabel-setup__ok { color: #00a32a; font-weight: 700; }
        .kabel-setup__lead { max-width: 720px; font-size: 14px; }
        .kabel-setup__note { color: #757575; }
        .kabel-setup__blocks { columns: 2; max-width: 860px; margin-top: 8px; }
        .kabel-setup__plugins { max-width: 860px; }
        .kabel-setup__action { text-align: right; }
        .kabel-setup__demo-pages { margin: 0 0 14px; max-width: 640px; }
        .kabel-setup__demo-pages li { margin: 6px 0; }
        .kabel-setup__demo-pages code { margin-left: 10px; padding: 2px 8px; background: #f0f0f1; color: #50575e; }
        .kabel-setup__danger { color: #b32d2e !important; border-color: #b32d2e !important; }
        .kabel-setup__danger:hover { background: #b32d2e !important; border-color: #b32d2e !important; color: #fff !important; }
        @media (max-width: 782px) { .kabel-setup__blocks { columns: 1; } }
        </style>';
}
add_action( 'admin_head', 'kabel_guide_admin_css' );
