<?php
/**
 * Dazhen theme bootstrap.
 *
 * This file only loads the modules in inc/. All logic lives in the
 * single-responsibility modules required below.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

define( 'DAZHEN_VERSION', '0.4.2' );
define( 'DAZHEN_DIR', get_template_directory() );
define( 'DAZHEN_URI', get_template_directory_uri() );

require_once DAZHEN_DIR . '/inc/setup.php';
require_once DAZHEN_DIR . '/inc/enqueue.php';
require_once DAZHEN_DIR . '/inc/post-types.php';
require_once DAZHEN_DIR . '/inc/meta-fields.php';
require_once DAZHEN_DIR . '/inc/metabox-ui.php';
require_once DAZHEN_DIR . '/inc/inquiry.php';
require_once DAZHEN_DIR . '/inc/inquiry-admin.php';
require_once DAZHEN_DIR . '/inc/i18n-compat.php';
require_once DAZHEN_DIR . '/inc/nav-fallbacks.php';
require_once DAZHEN_DIR . '/inc/template-tags.php';
require_once DAZHEN_DIR . '/inc/customizer.php';
require_once DAZHEN_DIR . '/inc/editor.php';
require_once DAZHEN_DIR . '/inc/patterns.php';
require_once DAZHEN_DIR . '/inc/rfq.php';
require_once DAZHEN_DIR . '/inc/plugin-guide.php';
