<?php
/**
 * Footer.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<footer class="site-footer">
	<div class="container site-footer__grid">
		<div class="site-footer__brand">
			<?php dazhen_site_identity(); ?>
			<address class="site-footer__address">
				<p><?php dazhen_address(); ?></p>
				<p><a href="<?php echo dazhen_phone_url(); ?>"><?php dazhen_phone(); ?></a></p>
				<p><a href="<?php echo dazhen_email_url(); ?>"><?php dazhen_email(); ?></a></p>
			</address>
		</div>

		<nav class="site-footer__nav" aria-label="<?php esc_attr_e( 'Product series', 'dazhen' ); ?>">
			<h3><?php esc_html_e( 'Products', 'dazhen' ); ?></h3>
			<?php
			wp_nav_menu( array(
				'theme_location' => 'footer_products',
				'container'      => false,
				'menu_class'     => 'site-footer__list',
				'depth'          => 1,
				'fallback_cb'    => 'dazhen_footer_products_fallback',
			) );
			?>
		</nav>

		<nav class="site-footer__nav" aria-label="<?php esc_attr_e( 'Solutions and company', 'dazhen' ); ?>">
			<h3><?php esc_html_e( 'Solutions & Company', 'dazhen' ); ?></h3>
			<?php
			wp_nav_menu( array(
				'theme_location' => 'footer_company',
				'container'      => false,
				'menu_class'     => 'site-footer__list',
				'depth'          => 1,
				'fallback_cb'    => 'dazhen_footer_company_fallback',
			) );
			?>
		</nav>

		<div class="site-footer__compliance">
			<h3><?php esc_html_e( 'Compliance', 'dazhen' ); ?></h3>
			<ul>
				<li><?php esc_html_e( 'ISO 9001', 'dazhen' ); ?></li>
				<li><?php esc_html_e( 'BSCI', 'dazhen' ); ?></li>
				<li><?php esc_html_e( 'Food-Grade Materials', 'dazhen' ); ?></li>
			</ul>
		</div>
	</div>

	<div class="container site-footer__bottom">
		<p>
			<?php
			printf(
				/* translators: %s: year */
				esc_html__( '© %s Dazhen Technology. All rights reserved.', 'dazhen' ),
				esc_html( (string) gmdate( 'Y' ) )
			);
			?>
		</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
