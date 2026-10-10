<?php
/**
 * Primary navigation fallback + the Products mega menu.
 *
 * Until a menu is assigned the fallback renders the confirmed IA v3 items,
 * with the Products item upgraded to a mega-menu panel (series links in two
 * columns + a solution-finder shortcut). Custom menus get the same upgrade
 * automatically: any top-level item linking to /products/ gains the
 * `menu-item-mega` class (main.css + main.js do the rest).
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

/**
 * Static fallback matching the confirmed IA v3 (7 items) + mega menu.
 */
function dazhen_nav_fallback(): void {
        $items = array(
                '/solutions/' => __( 'Solutions', 'dazhen' ),
                '/about-us/'  => __( 'About Us', 'dazhen' ),
                '/services/'  => __( 'Services', 'dazhen' ),
                '/blog/'      => __( 'Blog', 'dazhen' ),
                '/contact/'   => __( 'Contact Us', 'dazhen' ),
        );

        echo '<ul class="site-nav__list">';
        echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'dazhen' ) . '</a></li>';

        // Products — mega menu panel.
        $archive = get_post_type_archive_link( 'product' ) ?: home_url( '/products/' );
        echo '<li class="menu-item-has-children menu-item-mega">';
        echo '<a href="' . esc_url( $archive ) . '">' . esc_html__( 'Products', 'dazhen' ) . '</a>';
        dazhen_mega_menu_panel();
        echo '</li>';

        foreach ( $items as $path => $label ) {
                echo '<li><a href="' . esc_url( home_url( $path ) ) . '">' . esc_html( $label ) . '</a></li>';
        }
        echo '</ul>';
}

/**
 * The mega menu panel: product series in two columns + shortcuts.
 */
function dazhen_mega_menu_panel(): void {
        $terms = get_terms( array(
                'taxonomy'   => 'product_series',
                'hide_empty' => false,
                'number'     => 10,
                'orderby'    => 'name',
        ) );
        ?>
        <div class="mega-panel" aria-label="<?php esc_attr_e( 'Product series', 'dazhen' ); ?>">
                <div class="mega-panel__cols">
                        <?php
                        $chunks = array( array(), array() );
                        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                                $i = 0;
                                foreach ( $terms as $term ) {
                                        $chunks[ $i % 2 ][] = $term;
                                        $i++;
                                }
                        }
                        foreach ( $chunks as $chunk ) :
                                ?>
                                <ul class="mega-panel__list">
                                        <?php foreach ( $chunk as $term ) : ?>
                                                <li>
                                                        <a href="<?php echo esc_url( get_term_link( $term ) ); ?>">
                                                                <span class="mega-panel__name"><?php echo esc_html( $term->name ); ?></span>
                                                                <?php if ( $term->count ) : ?>
                                                                        <span class="mega-panel__count"><?php echo esc_html( (string) $term->count ); ?></span>
                                                                <?php endif; ?>
                                                        </a>
                                                </li>
                                        <?php endforeach; ?>
                                </ul>
                        <?php endforeach; ?>
                </div>
                <div class="mega-panel__foot">
                        <a class="mega-panel__all" href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ?: home_url( '/products/' ) ); ?>">
                                <?php esc_html_e( 'Browse the full catalog →', 'dazhen' ); ?>
                        </a>
                        <a class="mega-panel__wizard" href="<?php echo esc_url( ( get_post_type_archive_link( 'product' ) ?: home_url( '/products/' ) ) . '#solution-finder' ); ?>">
                                <?php esc_html_e( 'Not sure which model? Try the Solution Finder →', 'dazhen' ); ?>
                        </a>
                </div>
        </div>
        <?php
}

/**
 * Promote custom-menu Products items to mega items.
 */
function dazhen_promote_mega_menu_items( array $items, $args = array() ): array {
        // The filter receives $args as an object when a real menu is assigned
        // (wp_nav_menu()) and as an array on the fallback path — accept both.
        $location = is_object( $args ) && isset( $args->theme_location )
                ? $args->theme_location
                : ( is_array( $args ) ? ( $args['theme_location'] ?? '' ) : '' );
        if ( 'primary' !== $location ) {
                return $items;
        }
        $archive_path = wp_parse_url( (string) ( get_post_type_archive_link( 'product' ) ?: '' ), PHP_URL_PATH );

        foreach ( $items as $item ) {
                if ( ! ( $item instanceof WP_Post ) ) {
                        continue;
                }
                $path = wp_parse_url( (string) $item->url, PHP_URL_PATH );
                if ( $archive_path && rtrim( (string) $path, '/' ) === rtrim( $archive_path, '/' ) ) {
                        $item->classes[] = 'menu-item-mega';
                }
        }
        return $items;
}
add_filter( 'wp_nav_menu_objects', 'dazhen_promote_mega_menu_items', 10, 2 );

/**
 * Attach the mega panel to promoted items in ASSIGNED menus.
 *
 * The fallback nav (dazhen_nav_fallback) prints the panel inline, but when a
 * real menu is assigned (e.g. by the site bootstrap) the markup has to be
 * appended through the walker — the CSS keys on .menu-item-mega:hover
 * .mega-panel inside the <li>.
 */
function dazhen_mega_menu_item_output( $output, $item, $depth = 0, $args = null ) {
        $location = is_object( $args ) && isset( $args->theme_location )
                ? $args->theme_location
                : ( is_array( $args ) ? ( $args['theme_location'] ?? '' ) : '' );
        if ( 'primary' !== $location || 0 !== (int) $depth ) {
                return $output;
        }
        if ( ! in_array( 'menu-item-mega', (array) ( $item->classes ?? array() ), true ) ) {
                return $output;
        }
        ob_start();
        dazhen_mega_menu_panel();
        return $output . (string) ob_get_clean();
}
add_filter( 'walker_nav_menu_start_el', 'dazhen_mega_menu_item_output', 10, 4 );

/**
 * Footer products fallback: list the product series taxonomy terms.
 */
function dazhen_footer_products_fallback(): void {
        $terms = get_terms( array(
                'taxonomy'   => 'product_series',
                'hide_empty' => false,
                'number'     => 10,
        ) );
        echo '<ul class="site-footer__list">';
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                foreach ( $terms as $term ) {
                        printf(
                                '<li><a href="%s">%s</a></li>',
                                esc_url( get_term_link( $term ) ),
                                esc_html( $term->name )
                        );
                }
        } else {
                echo '<li><a href="' . esc_url( get_post_type_archive_link( 'product' ) ?: home_url( '/products/' ) ) . '">' . esc_html__( 'All products', 'dazhen' ) . '</a></li>';
        }
        echo '</ul>';
}

/**
 * Footer company links fallback.
 */
function dazhen_footer_company_fallback(): void {
        $items = array(
                '/solutions/' => __( 'Solutions & Cases', 'dazhen' ),
                '/about-us/'  => __( 'About Us', 'dazhen' ),
                '/services/'  => __( 'OEM / ODM Services', 'dazhen' ),
                '/blog/'      => __( 'News', 'dazhen' ),
                '/contact/'   => __( 'Contact Us', 'dazhen' ),
        );
        echo '<ul class="site-footer__list">';
        foreach ( $items as $path => $label ) {
                echo '<li><a href="' . esc_url( home_url( $path ) ) . '">' . esc_html( $label ) . '</a></li>';
        }
        echo '</ul>';
}
