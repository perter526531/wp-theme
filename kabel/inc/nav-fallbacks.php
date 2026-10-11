<?php
/**
 * Primary navigation fallback + the Products mega menu.
 *
 * Until a menu is assigned the fallback renders the original site's
 * navigation (HOME / ABOUT / PRODUCTS / COLORS / PROJECTS / PROJECT
 * PROCUREMENT / DISTRIBUTOR / NEWS / CONTACT with their dropdowns), with
 * the Products item upgraded to a mega-menu panel (product family links
 * in two columns + shortcuts). Custom menus get the same upgrade
 * automatically: any top-level item linking to /products/ gains the
 * `menu-item-mega` class (main.css + main.js do the rest).
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Resolve a project-type archive and keep the fallback usable before terms exist.
 */
function kabel_project_type_url( string $slug ): string {
	$term = get_term_by( 'slug', $slug, 'project_type' );
	if ( $term && ! is_wp_error( $term ) ) {
		$url = get_term_link( $term );
		if ( ! is_wp_error( $url ) ) {
			return (string) $url;
		}
	}

	return home_url( '/projects/type/' . $slug . '/' );
}

/**
 * The fallback navigation tree (labels kept verbatim from the live site).
 *
 * @return array<int, array{url:string,label:string,children?:array<int,array{url:string,label:string}}}>
 */
function kabel_nav_fallback_tree(): array {
	$products = (string) ( get_post_type_archive_link( 'product' ) ?: home_url( '/products/' ) );
	$projects = (string) ( get_post_type_archive_link( 'projects' ) ?: home_url( '/projects/' ) );

	return array(
		array(
			'url'   => home_url( '/' ),
			'label' => __( 'HOME', 'kabel' ),
		),
		array(
			'url'      => home_url( '/about/' ),
			'label'    => __( 'ABOUT', 'kabel' ),
			'children' => array(
				array( 'url' => home_url( '/about/' ), 'label' => __( 'About Us', 'kabel' ) ),
				array( 'url' => home_url( '/eco-friendly/' ), 'label' => __( 'ECO Friendly', 'kabel' ) ),
				array( 'url' => home_url( '/designer/' ), 'label' => __( 'Designer', 'kabel' ) ),
			),
		),
		array(
			'url'   => $products,
			'label' => __( 'PRODUCTS', 'kabel' ),
			'mega'  => true,
		),
		array(
			'url'      => '#',
			'label'    => __( 'COLORS', 'kabel' ),
			'children' => array(
				array( 'url' => home_url( '/morandi-colors/' ), 'label' => __( 'Morandi Colors', 'kabel' ) ),
				array( 'url' => home_url( '/van-gogh-color/' ), 'label' => __( 'Van Gogh Color', 'kabel' ) ),
				array( 'url' => home_url( '/macaron-colors/' ), 'label' => __( 'Macaron colors', 'kabel' ) ),
				array( 'url' => home_url( '/museum-collection-colors/' ), 'label' => __( 'Museum Collection Colors', 'kabel' ) ),
			),
		),
		array(
			'url'      => $projects,
			'label'    => __( 'PROJECTS', 'kabel' ),
			'children' => array(
				array( 'url' => kabel_project_type_url( 'home-renovation-projects' ), 'label' => __( 'Home Renovation Projects', 'kabel' ) ),
				array( 'url' => kabel_project_type_url( 'engineering-projects' ), 'label' => __( 'Engineering Projects', 'kabel' ) ),
			),
		),
		array(
			'url'      => '#',
			'label'    => __( 'PROJECT PROCUREMENT', 'kabel' ),
			'children' => array(
				array( 'url' => home_url( '/project-procurement/' ), 'label' => __( 'Project Procurement', 'kabel' ) ),
				array( 'url' => home_url( '/customize/' ), 'label' => __( 'Customize', 'kabel' ) ),
			),
		),
		array(
			'url'   => home_url( '/distributor/' ),
			'label' => __( 'DISTRIBUTOR', 'kabel' ),
		),
		array(
			'url'      => home_url( '/blog/' ),
			'label'    => __( 'NEWS', 'kabel' ),
			'children' => array(
				array( 'url' => home_url( '/category/company-news/' ), 'label' => __( 'Company News', 'kabel' ) ),
				array( 'url' => home_url( '/category/industry-news/' ), 'label' => __( 'Industry News', 'kabel' ) ),
				array( 'url' => home_url( '/category/knowledge/' ), 'label' => __( 'Knowledge', 'kabel' ) ),
				array( 'url' => home_url( '/faq/' ), 'label' => __( 'Faq', 'kabel' ) ),
			),
		),
		array(
			'url'   => home_url( '/contact/' ),
			'label' => __( 'CONTACT', 'kabel' ),
		),
	);
}

/**
 * Static fallback: the site's navigation hierarchy with dropdowns.
 */
function kabel_nav_fallback(): void {
	echo '<ul class="site-nav__list">';

	foreach ( kabel_nav_fallback_tree() as $item ) {
		$has_children = ! empty( $item['children'] );
		$classes      = array( 'menu-item' );

		if ( $has_children || ! empty( $item['mega'] ) ) {
			$classes[] = 'menu-item-has-children';
		}
		if ( ! empty( $item['mega'] ) ) {
			$classes[] = 'menu-item-mega';
		}

		echo '<li class="' . esc_attr( implode( ' ', $classes ) ) . '">';
		echo '<a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a>';

		if ( ! empty( $item['mega'] ) ) {
			kabel_mega_menu_panel();
		} elseif ( $has_children ) {
			echo '<ul class="sub-menu">';
			foreach ( $item['children'] as $child ) {
				echo '<li class="menu-item"><a href="' . esc_url( $child['url'] ) . '">' . esc_html( $child['label'] ) . '</a></li>';
			}
			echo '</ul>';
		}

		echo '</li>';
	}

	echo '</ul>';
}

/**
 * The mega menu panel: the six paint families in two columns + shortcuts.
 */
function kabel_mega_menu_panel(): void {
	$terms = get_terms( array(
		'taxonomy'   => 'product_series',
		'hide_empty' => false,
		'number'     => 12,
		'orderby'    => 'name',
	) );
	?>
	<div class="mega-panel" aria-label="<?php esc_attr_e( 'Paint families', 'kabel' ); ?>">
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
			<a class="mega-panel__all" href="<?php echo esc_url( ( get_post_type_archive_link( 'product' ) ?: home_url( '/products/' ) ) ); ?>">
				<?php esc_html_e( 'View all paint series →', 'kabel' ); ?>
			</a>
			<a class="mega-panel__wizard" href="<?php echo esc_url( ( get_post_type_archive_link( 'product' ) ?: home_url( '/products/' ) ) . '#solution-finder' ); ?>">
				<?php esc_html_e( 'Not sure which finish? Try the selector →', 'kabel' ); ?>
			</a>
		</div>
	</div>
	<?php
}

/**
 * Promote custom-menu Products items to mega items.
 */
function kabel_promote_mega_menu_items( array $items, $args = array() ): array {
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
add_filter( 'wp_nav_menu_objects', 'kabel_promote_mega_menu_items', 10, 2 );

/**
 * Attach the mega panel to promoted items in ASSIGNED menus.
 *
 * The fallback nav (kabel_nav_fallback) prints the panel inline, but when a
 * real menu is assigned (e.g. by the site bootstrap) the markup has to be
 * appended through the walker — the CSS keys on .menu-item-mega:hover
 * .mega-panel inside the <li>.
 */
function kabel_mega_menu_item_output( $output, $item, $depth = 0, $args = null ) {
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
	kabel_mega_menu_panel();
	return $output . (string) ob_get_clean();
}
add_filter( 'walker_nav_menu_start_el', 'kabel_mega_menu_item_output', 10, 4 );

/**
 * Footer products fallback: list the paint family (product_series) terms.
 */
function kabel_footer_products_fallback(): void {
	$terms = get_terms( array(
		'taxonomy'   => 'product_series',
		'hide_empty' => false,
		'number'     => 12,
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
		echo '<li><a href="' . esc_url( get_post_type_archive_link( 'product' ) ?: home_url( '/products/' ) ) . '">' . esc_html__( 'All paint series', 'kabel' ) . '</a></li>';
	}
	echo '</ul>';
}

/**
 * Footer company links fallback (the footer menu column).
 */
function kabel_footer_company_fallback(): void {
	$projects = (string) ( get_post_type_archive_link( 'projects' ) ?: home_url( '/projects/' ) );
	$items    = array(
		home_url( '/about/' )          => __( 'ABOUT', 'kabel' ),
		home_url( '/morandi-colors/' ) => __( 'Colors', 'kabel' ),
		$projects                      => __( 'PROJECTS', 'kabel' ),
		home_url( '/project-procurement/' ) => __( 'Procurement', 'kabel' ),
		home_url( '/customize/' )      => __( 'customize', 'kabel' ),
		home_url( '/distributor/' )    => __( 'DISTRIBUTOR', 'kabel' ),
		home_url( '/blog/' )           => __( 'NEWS', 'kabel' ),
		home_url( '/contact/' )        => __( 'CONTACT', 'kabel' ),
	);
	echo '<ul class="site-footer__list">';
	foreach ( $items as $path => $label ) {
		echo '<li><a href="' . esc_url( $path ) . '">' . esc_html( $label ) . '</a></li>';
	}
	echo '</ul>';
}
