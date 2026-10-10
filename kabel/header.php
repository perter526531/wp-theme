<?php
/**
 * Header.
 *
 * v12: when the private "Site Header" page (kabel-header) has block content,
 * it replaces the built-in header — the owner edits it with the block editor
 * (Appearance → Header & Footer, or the admin-bar shortcuts). Empty or missing
 * page keeps the classic designed header below untouched.
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

$kabel_header_blocks = kabel_site_part_render( 'header' );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'kabel' ); ?></a>

<?php if ( $kabel_header_blocks ) : ?>
<header class="site-header site-header--blocks" data-kb-header>
        <div class="container site-header__blocks-inner">
                <?php
                /* Block markup. Everything passed through the the_content filter
                   chain, where the blocks' own saving rules escaped the content. */
                echo $kabel_header_blocks;
                ?>
        </div>
</header>
<?php else : ?>
<header class="site-header" data-kb-header>
        <div class="container site-header__inner">
                <div class="site-branding"><?php kabel_site_identity(); ?></div>

                <nav class="site-nav" id="primary-nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'kabel' ); ?>">
                        <?php
                        wp_nav_menu( array(
                                'theme_location' => 'primary',
                                'container'      => false,
                                'menu_class'     => 'site-nav__list',
                                'depth'          => 2,
                                'fallback_cb'    => 'kabel_nav_fallback',
                        ) );
                        ?>
                </nav>

                <div class="site-header__actions">
                        <div class="lang-switch">
                                <?php kabel_language_switcher(); ?>
                        </div>
                        <a class="btn btn--cta site-header__cta" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
                                <?php echo kabel_cta_text(); ?>
                        </a>
                        <button class="nav-toggle" type="button" data-kb-nav-toggle aria-expanded="false" aria-controls="primary-nav">
                                <span></span><span></span><span></span>
                                <span class="screen-reader-text"><?php esc_html_e( 'Menu', 'kabel' ); ?></span>
                        </button>
                </div>
        </div>
</header>
<?php endif; ?>

<main id="main" class="site-main">
