<?php
/**
 * Footer.
 *
 * v12: when the private "Site Footer" page (kabel-footer) has block content,
 * it replaces the built-in footer grid — editable with the block editor.
 * The floating to-top button and inquiry-list bar stay put either way.
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

$kabel_footer_blocks = kabel_site_part_render( 'footer' );
?>
</main>

<?php if ( $kabel_footer_blocks ) : ?>
<footer class="site-footer site-footer--blocks">
        <div class="container site-footer__blocks-inner">
                <?php
                /* Block markup rendered through the the_content filter chain. */
                echo $kabel_footer_blocks;
                ?>
        </div>
</footer>
<?php else : ?>
<footer class="site-footer">
        <div class="container site-footer__grid">
                <div class="site-footer__brand">
                        <?php kabel_site_identity(); ?>
                        <address class="site-footer__address">
                                <p><?php kabel_address(); ?></p>
                                <p><a href="<?php echo kabel_phone_url(); ?>"><?php kabel_phone(); ?></a></p>
                                <p><a href="<?php echo kabel_email_url(); ?>"><?php kabel_email(); ?></a></p>
                        </address>
                </div>

                <nav class="site-footer__nav" aria-label="<?php esc_attr_e( 'Product series', 'kabel' ); ?>">
                        <h3><?php esc_html_e( 'Products', 'kabel' ); ?></h3>
                        <?php
                        wp_nav_menu( array(
                                'theme_location' => 'footer_products',
                                'container'      => false,
                                'menu_class'     => 'site-footer__list',
                                'depth'          => 1,
                                'fallback_cb'    => 'kabel_footer_products_fallback',
                        ) );
                        ?>
                </nav>

                <nav class="site-footer__nav" aria-label="<?php esc_attr_e( 'Solutions and company', 'kabel' ); ?>">
                        <h3><?php esc_html_e( 'Solutions & Company', 'kabel' ); ?></h3>
                        <?php
                        wp_nav_menu( array(
                                'theme_location' => 'footer_company',
                                'container'      => false,
                                'menu_class'     => 'site-footer__list',
                                'depth'          => 1,
                                'fallback_cb'    => 'kabel_footer_company_fallback',
                        ) );
                        ?>
                </nav>

                <div class="site-footer__compliance">
                        <h3><?php esc_html_e( 'Contact', 'kabel' ); ?></h3>
                        <ul>
                                <li><a href="mailto:<?php echo esc_attr( kabel_get_option( 'kabel_email', 'kabelpaints@gmail.com' ) ); ?>"><?php echo esc_html( kabel_get_option( 'kabel_email', 'kabelpaints@gmail.com' ) ); ?></a></li>
                                <li><?php esc_html_e( 'WhatsApp: +86 18033205148', 'kabel' ); ?></li>
                                <li><?php esc_html_e( 'HQ: Druento, Torino, Italy', 'kabel' ); ?></li>
                                <li><?php esc_html_e( 'Branch: Foshan, Guangdong, China', 'kabel' ); ?></li>
                        </ul>
                </div>
        </div>

        <div class="container site-footer__bottom">
                <p>
                        <?php
                        printf(
                                /* translators: %s: year */
                                esc_html__( '© %s Kabel Technology. All rights reserved.', 'kabel' ),
                                esc_html( (string) gmdate( 'Y' ) )
                        );
                        ?>
                </p>
        </div>
</footer>
<?php endif; ?>

<button type="button" class="to-top" data-kb-to-top aria-label="<?php esc_attr_e( 'Back to top', 'kabel' ); ?>">
        <span aria-hidden="true">&uarr;</span>
</button>

<div class="kb-listbar" data-kb-listbar hidden>
        <button type="button" class="kb-listbar__main" data-kb-list-open>
                <svg class="kb-listbar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M4 6h10M4 12h10M4 18h10"></path>
                        <path d="M18 9l2.5 3L18 15"></path>
                </svg>
                <span data-kb-list-label><?php esc_html_e( 'Inquiry list', 'kabel' ); ?></span>
                <span class="kb-listbar__count" data-kb-list-count>0</span>
        </button>
        <button type="button" class="kb-listbar__clear" data-kb-list-clear aria-label="<?php esc_attr_e( 'Clear inquiry list', 'kabel' ); ?>">
                <span aria-hidden="true">&times;</span>
        </button>
</div>

<?php wp_footer(); ?>
</body>
</html>
