<?php
/**
 * Kabel theme bootstrap.
 *
 * This file only loads the modules in inc/. All logic lives in the
 * single-responsibility modules required below.
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

define( 'KABEL_VERSION', '1.6.6' );
define( 'KABEL_DIR', get_template_directory() );
define( 'KABEL_URI', get_template_directory_uri() );

require_once KABEL_DIR . '/inc/setup.php';
require_once KABEL_DIR . '/inc/enqueue.php';
require_once KABEL_DIR . '/inc/post-types.php';
require_once KABEL_DIR . '/inc/meta-fields.php';
require_once KABEL_DIR . '/inc/metabox-ui.php';
require_once KABEL_DIR . '/inc/inquiry.php';
require_once KABEL_DIR . '/inc/inquiry-admin.php';
require_once KABEL_DIR . '/inc/i18n-compat.php';
require_once KABEL_DIR . '/inc/nav-fallbacks.php';
require_once KABEL_DIR . '/inc/template-tags.php';
require_once KABEL_DIR . '/inc/customizer.php';
require_once KABEL_DIR . '/inc/editor.php';
require_once KABEL_DIR . '/inc/patterns.php';
require_once KABEL_DIR . '/inc/rfq.php';
require_once KABEL_DIR . '/inc/plugin-guide.php';
require_once KABEL_DIR . '/inc/blocks-fallback.php';
require_once KABEL_DIR . '/inc/cache-purge.php';
require_once KABEL_DIR . '/inc/site-parts.php';
