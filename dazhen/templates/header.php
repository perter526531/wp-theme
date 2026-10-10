<?php
/**
 * Header.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;
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
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'dazhen' ); ?></a>

<header class="site-header" data-dz-header>
	<div class="container site-header__inner">
		<div class="site-branding"><?php dazhen_site_identity(); ?></div>

		<nav class="site-nav" id="primary-nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'dazhen' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'site-nav__list',
				'depth'          => 2,
				'fallback_cb'    => 'dazhen_nav_fallback',
			) );
			?>
		</nav>

		<div class="site-header__actions">
			<div class="lang-switch">
				<?php dazhen_language_switcher(); ?>
			</div>
			<a class="btn btn--cta site-header__cta" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
				<?php echo dazhen_cta_text(); ?>
			</a>
			<button class="nav-toggle" type="button" data-dz-nav-toggle aria-expanded="false" aria-controls="primary-nav">
				<span></span><span></span><span></span>
				<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'dazhen' ); ?></span>
			</button>
		</div>
	</div>
</header>

<main id="main" class="site-main">
