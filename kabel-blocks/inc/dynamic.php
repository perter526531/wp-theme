<?php
/**
 * Kabel Blocks v3 — dynamic blocks (server-rendered).
 *
 * kbl/inquiry-form — the live inquiry form. With the Kabel theme active it
 *   reuses the theme's inquiry system (kabel_form_inquiry_part); standalone
 *   it renders the plugin's compact REST-backed form (inc/leads.php).
 * kbl/post-grid — live product / project / post grid.
 * kbl/nav-menu — live header navigation from a WordPress menu (one nesting
 *   level, deeper items flatten into the second).
 * kbl/search — the site search form (GET to the home URL with ?s=).
 *
 * All are registered server-side with render_callback + the shared v3
 * editor script; their save() returns null (nothing stored to validate).
 *
 * @package Kabel_Blocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the dynamic blocks (client side registers the same names from
 * assets/js/blocks.js — attributes must stay in sync with src/blocks/*).
 */
function kbl3_register_dynamic_blocks(): void {
        if ( ! function_exists( 'register_block_type' ) ) {
                return;
        }

        register_block_type( 'kbl/inquiry-form', array(
                'api_version'     => 3,
                'editor_script'   => 'kabel-blocks-v3',
                'style'           => 'kabel-blocks-v3',
                'attributes'      => array(
                        'title'       => array( 'type' => 'string', 'default' => 'CONTACT US' ),
                        'sub'         => array( 'type' => 'string', 'default' => 'Global Service · Local Response' ),
                        'email'       => array( 'type' => 'string' ),
                        'buttonLabel' => array( 'type' => 'string', 'default' => 'Send Inquiry' ),
                        'hideD'       => array( 'type' => 'boolean' ),
                        'hideT'       => array( 'type' => 'boolean' ),
                        'hideM'       => array( 'type' => 'boolean' ),
                        'mt'          => array( 'type' => 'number' ),
                        'mb'          => array( 'type' => 'number' ),
                        'mtM'         => array( 'type' => 'number' ),
                        'mbM'         => array( 'type' => 'number' ),
                ),
                'render_callback' => 'kbl3_render_inquiry_form',
        ) );

        register_block_type( 'kbl/post-grid', array(
                'api_version'     => 3,
                'editor_script'   => 'kabel-blocks-v3',
                'style'           => 'kabel-blocks-v3',
                'attributes'      => array(
                        'postType'    => array( 'type' => 'string', 'default' => 'product' ),
                        'columns'     => array( 'type' => 'number', 'default' => 3 ),
                        'count'       => array( 'type' => 'number', 'default' => 6 ),
                        'showExcerpt' => array( 'type' => 'boolean', 'default' => true ),
                        'btnLabel'    => array( 'type' => 'string', 'default' => 'View All' ),
                        'btnURL'      => array( 'type' => 'string' ),
                        'hideD'       => array( 'type' => 'boolean' ),
                        'hideT'       => array( 'type' => 'boolean' ),
                        'hideM'       => array( 'type' => 'boolean' ),
                        'mt'          => array( 'type' => 'number' ),
                        'mb'          => array( 'type' => 'number' ),
                        'mtM'         => array( 'type' => 'number' ),
                        'mbM'         => array( 'type' => 'number' ),
                ),
                'render_callback' => 'kbl3_render_post_grid',
        ) );

        register_block_type( 'kbl/nav-menu', array(
                'api_version'     => 3,
                'editor_script'   => 'kabel-blocks-v3',
                'style'           => 'kabel-blocks-v3',
                'attributes'      => array(
                        'anchor'        => array( 'type' => 'string', 'source' => 'attribute', 'attribute' => 'id', 'selector' => '*' ),
                        'menu'          => array( 'type' => 'number' ),
                        'fontSize'      => array( 'type' => 'number' ),
                        'fontSizeT'     => array( 'type' => 'number' ),
                        'fontSizeM'     => array( 'type' => 'number' ),
                        'fontWeight'    => array( 'type' => 'number' ),
                        'lineHeight'    => array( 'type' => 'number' ),
                        'letterSpacing' => array( 'type' => 'number' ),
                        'textTransform' => array( 'type' => 'string' ),
                        'color'         => array( 'type' => 'string' ),
                        'hoverColor'    => array( 'type' => 'string' ),
                        'bgColor'       => array( 'type' => 'string' ),
                        'gap'           => array( 'type' => 'number', 'default' => 28 ),
                        'padX'          => array( 'type' => 'number', 'default' => 4 ),
                        'padY'          => array( 'type' => 'number', 'default' => 8 ),
                        'hideD'         => array( 'type' => 'boolean' ),
                        'hideT'         => array( 'type' => 'boolean' ),
                        'hideM'         => array( 'type' => 'boolean' ),
                        'mt'            => array( 'type' => 'number' ),
                        'mb'            => array( 'type' => 'number' ),
                        'mtM'           => array( 'type' => 'number' ),
                        'mbM'           => array( 'type' => 'number' ),
                ),
                'render_callback' => 'kbl3_render_nav_menu',
        ) );

        register_block_type( 'kbl/search', array(
                'api_version'     => 3,
                'editor_script'   => 'kabel-blocks-v3',
                'style'           => 'kabel-blocks-v3',
                'attributes'      => array(
                        'anchor'      => array( 'type' => 'string', 'source' => 'attribute', 'attribute' => 'id', 'selector' => '*' ),
                        'placeholder' => array( 'type' => 'string', 'default' => 'Search…' ),
                        'width'       => array( 'type' => 'number', 'default' => 220 ),
                        'radius'      => array( 'type' => 'number', 'default' => 8 ),
                        'inputBg'     => array( 'type' => 'string' ),
                        'textColor'   => array( 'type' => 'string' ),
                        'borderColor' => array( 'type' => 'string' ),
                        'iconColor'   => array( 'type' => 'string' ),
                        'hideD'       => array( 'type' => 'boolean' ),
                        'hideT'       => array( 'type' => 'boolean' ),
                        'hideM'       => array( 'type' => 'boolean' ),
                        'mt'          => array( 'type' => 'number' ),
                        'mb'          => array( 'type' => 'number' ),
                        'mtM'         => array( 'type' => 'number' ),
                        'mbM'         => array( 'type' => 'number' ),
                ),
                'render_callback' => 'kbl3_render_search',
        ) );
}
add_action( 'init', 'kbl3_register_dynamic_blocks', 25 );

/**
 * Render the inquiry form.
 */
function kbl3_render_inquiry_form( array $attributes ): string {
        $title       = isset( $attributes['title'] ) ? (string) $attributes['title'] : 'CONTACT US';
        $sub         = isset( $attributes['sub'] ) ? (string) $attributes['sub'] : 'Global Service · Local Response';
        $button      = isset( $attributes['buttonLabel'] ) ? (string) $attributes['buttonLabel'] : 'Send Inquiry';
        $email       = isset( $attributes['email'] ) ? (string) $attributes['email'] : '';

        $style = kbl3_style( kbl3_spacing_pairs( $attributes ) );
        $out = '<div class="' . esc_attr( kbl3_cx( 'kbl3-form', kbl3_visibility( $attributes ), kbl3_spacing_class( $attributes ) ) ) . '"' . ( '' !== $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . '>';

        if ( '' !== $title ) {
                $out .= '<h3 class="kbl3-h kbl3-h--l">' . esc_html( $title ) . '</h3>';
        }
        if ( '' !== $sub ) {
                $out .= '<p class="kbl3-text kbl3-text--m is-lead" style="text-align:center">' . esc_html( $sub ) . '</p>';
        }

        if ( function_exists( 'kabel_form_inquiry_part' ) ) {
                // Kabel theme: the full inquiry experience (points + styled form).
                $out .= kabel_form_inquiry_part( '', '' );
        } else {
                $out .= kbl3_standalone_form( $button, $email );
        }

        $out .= '</div>';
        return $out;
}

/**
 * The standalone REST-backed form (matches assets/js/kabel-form.js).
 */
function kbl3_standalone_form( string $button = 'Send Inquiry', string $email = '' ): string {
        $endpoint = esc_url_raw( rest_url( 'kabel-blocks/v1/lead' ) );

        $out  = '<form class="kbl-form" method="post" action="' . $endpoint . '" data-endpoint="' . $endpoint . '">';
        $out .= '<div class="kbl3-form__grid">';
        $out .= '<input type="text" name="person" required placeholder="' . esc_attr__( 'Your name', 'kabel-blocks' ) . '" />';
        $out .= '<input type="email" name="email" required placeholder="' . esc_attr__( 'Your email', 'kabel-blocks' ) . '" />';
        $out .= '<input type="text" name="country" placeholder="' . esc_attr__( 'Country / Company', 'kabel-blocks' ) . '" />';
        $out .= '</div>';
        $out .= '<input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;" aria-hidden="true" />';
        $out .= '<textarea name="message" rows="4" placeholder="' . esc_attr__( 'How can we help?', 'kabel-blocks' ) . '"></textarea>';
        $out .= '<div class="kbl3-btnwrap" style="text-align:center"><button type="submit" class="kbl3-btn kbl3-btn--gold kbl3-btn--m">' . esc_html( '' !== $button ? $button : __( 'Send Inquiry', 'kabel-blocks' ) ) . '</button></div>';
        $out .= '<p class="kbl3-form__status" data-kbl-form-note role="status" aria-live="polite"></p>';
        $out .= '</form>';

        return $out;
}

/**
 * Render the live post grid.
 */
function kbl3_render_post_grid( array $attributes ): string {
        $post_type    = isset( $attributes['postType'] ) ? (string) $attributes['postType'] : 'product';
        $columns      = isset( $attributes['columns'] ) ? max( 1, min( 4, (int) $attributes['columns'] ) ) : 3;
        $count        = isset( $attributes['count'] ) ? max( 1, min( 12, (int) $attributes['count'] ) ) : 6;
        $show_excerpt = ! isset( $attributes['showExcerpt'] ) || $attributes['showExcerpt'];
        $btn_label    = isset( $attributes['btnLabel'] ) ? (string) $attributes['btnLabel'] : 'View All';
        $btn_url      = isset( $attributes['btnURL'] ) ? (string) $attributes['btnURL'] : '';

        if ( ! post_type_exists( $post_type ) ) {
                return '';
        }

        $query = new WP_Query( array(
                'post_type'           => $post_type,
                'post_status'         => 'publish',
                'posts_per_page'      => $count,
                'orderby'             => 'date',
                'order'               => 'DESC',
                'ignore_sticky_posts' => true,
                'no_found_rows'       => true,
        ) );

        $out = '<div class="' . esc_attr( kbl3_cx( 'kbl3-pgrid', kbl3_visibility( $attributes ), kbl3_spacing_class( $attributes ) ) ) . '" style="' . esc_attr( kbl3_style( array_merge( array(
                array( '--kbl3-grid-cols', (string) $columns ),
        ), kbl3_spacing_pairs( $attributes ) ) ) ) . '">';

        if ( $query->have_posts() ) {
                $out .= '<div class="kbl3-pgrid__row">';
                foreach ( $query->posts as $post ) {
                        $out .= kbl3_post_card( $post, $show_excerpt );
                }
                $out .= '</div>';
        }

        if ( '' !== $btn_label ) {
                $out .= '<div class="kbl3-btnwrap" style="text-align:center">';
                $href = '' !== $btn_url ? $btn_url : ( 'product' === $post_type ? '/products/' : ( 'post' === $post_type ? '/blog/' : '/projects/' ) );
                $out .= '<a class="kbl3-btn kbl3-btn--outline kbl3-btn--m" href="' . esc_url( $href ) . '">' . esc_html( $btn_label ) . '</a>';
                $out .= '</div>';
        }

        $out .= '</div>';

        wp_reset_postdata();
        return $out;
}

/**
 * One grid card.
 */
function kbl3_post_card( WP_Post $post, bool $show_excerpt ): string {
        $title = get_the_title( $post );
        $url   = get_permalink( $post );

        $out = '<article class="kbl3-pcard">';
        if ( has_post_thumbnail( $post ) ) {
                $out .= get_the_post_thumbnail( $post, 'large', array( 'class' => 'kbl3-pcard__img' ) );
        } else {
                $out .= '<div class="kbl3-pcard__ph"></div>';
        }
        $out .= '<div class="kbl3-pcard__body">';
        $out .= '<h4 class="kbl3-pcard__title"><a href="' . esc_url( $url ) . '">' . esc_html( $title ) . '</a></h4>';
        if ( $show_excerpt ) {
                $excerpt = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( wp_strip_all_tags( $post->post_content ), 18, '…' );
                $out .= '<p class="kbl3-pcard__text">' . esc_html( $excerpt ) . '</p>';
        }
        $out .= '</div></article>';
        return $out;
}

/**
 * Resolve the menu id for one nav block, caching the AUTO resolution.
 *
 * Auto resolution (no explicit menu): prefer the menu assigned to the active
 * theme's "primary" location (Blocksy-style), then any assigned location,
 * then the menu NAMED "Primary Menu" (the seed menu — block themes like
 * Twenty Twenty-Five register no classic locations at all, so name is the
 * only handle), and only then the first menu. The result only depends on
 * DB state, so it is cached for the request.
 */
function kbl3_nav_menu_id_for( array $a ): int {
        static $auto = null;

        $menu_id = isset( $a['menu'] ) ? (int) $a['menu'] : 0;
        if ( $menu_id > 0 ) {
                return $menu_id;
        }
        if ( null !== $auto ) {
                return $auto;
        }

        $auto      = 0;
        $locations = function_exists( 'get_nav_menu_locations' ) ? get_nav_menu_locations() : array();
        if ( ! empty( $locations ) ) {
                $registered = function_exists( 'get_registered_nav_menus' ) ? get_registered_nav_menus() : array();
                $order      = array_unique( array_merge( array( 'primary' ), array_keys( $registered ) ) );
                foreach ( $order as $loc ) {
                        if ( ! empty( $locations[ $loc ] ) ) {
                                $loc_menu = wp_get_nav_menu_object( (int) $locations[ $loc ] );
                                if ( $loc_menu && ! is_wp_error( $loc_menu ) ) {
                                        $auto = (int) $loc_menu->term_id;
                                        return $auto;
                                }
                        }
                }
        }

        /* By name — the seed menu survives location-less themes and theme
         * switches. A deleted primary menu must NOT silently fall through to an
         * unrelated footer menu just because it sorts first alphabetically
         * ("Footer — Company" < "Primary Menu"). */
        foreach ( array( 'Primary Menu', 'primary-menu', 'primary', 'Main Menu' ) as $name ) {
                $named = wp_get_nav_menu_object( $name );
                if ( $named instanceof WP_Term && ! is_wp_error( $named ) ) {
                        $items = wp_get_nav_menu_items( $named );
                        if ( is_array( $items ) && array() !== $items ) {
                                $auto = (int) $named->term_id;
                                return $auto;
                        }
                }
        }

        if ( function_exists( 'wp_get_nav_menus' ) ) {
                /* Last resort: the first menu that is not obviously a footer
                 * utility menu, then any menu at all. */
                $menus = wp_get_nav_menus( array( 'orderby' => 'name' ) );
                if ( ! empty( $menus ) && ! is_wp_error( $menus ) ) {
                        $pick = null;
                        foreach ( $menus as $candidate ) {
                                if ( false === stripos( $candidate->name, 'footer' ) ) {
                                        $pick = $candidate;
                                        break;
                                }
                        }
                        $pick = $pick ?? $menus[0];
                        $auto = (int) $pick->term_id;
                }
        }

        return $auto;
}

/**
 * The context-marked menu items for one menu, cached per request.
 *
 * _wp_menu_item_classes_by_context() APPENDS to the item classes, so the
 * marked objects are cached (not re-marked) — a header and a footer nav
 * block reading the same menu share one query and one marking pass.
 *
 * @return array<int, object> Raw WP menu item objects, already marked.
 */
function kbl3_nav_items_for( int $menu_id ): array {
        static $marked = array();

        if ( $menu_id < 1 ) {
                return array();
        }
        if ( isset( $marked[ $menu_id ] ) ) {
                return $marked[ $menu_id ];
        }

        $menu_items = wp_get_nav_menu_items( $menu_id );
        if ( ! is_array( $menu_items ) ) {
                $menu_items = array();
        }
        if ( function_exists( '_wp_menu_item_classes_by_context' ) ) {
                _wp_menu_item_classes_by_context( $menu_items );
        }

        return $marked[ $menu_id ] = $menu_items;
}

/**
 * Render the nav menu (kbl/nav-menu).
 *
 * Reads a WordPress menu (selected id, or the first menu by name), marks the
 * current page via _wp_menu_item_classes_by_context() and renders one
 * nesting level — deeper menu items flatten into the second level. The
 * mobile collapse only exists under html.kb-js (front.js); without
 * JavaScript the list stays fully expanded.
 */
function kbl3_render_nav_menu( array $attributes ): string {
        $a          = $attributes;
        $menu_items = kbl3_nav_items_for( kbl3_nav_menu_id_for( $a ) );

        $gap  = isset( $a['gap'] ) ? (int) $a['gap'] : 28;
        $padx = isset( $a['padX'] ) ? (int) $a['padX'] : 4;
        $pady = isset( $a['padY'] ) ? (int) $a['padY'] : 8;

        $class = kbl3_cx( 'kbl3-nav', kbl3_visibility( $a ), kbl3_spacing_class( $a ) );
        $style = kbl3_style( array_merge( array(
                array( '--kbl3-nav-gap', $gap . 'px' ),
                array( '--kbl3-nav-pad-x', $padx . 'px' ),
                array( '--kbl3-nav-pad-y', $pady . 'px' ),
                array( '--kbl3-nav-color', $a['color'] ?? '' ),
                array( '--kbl3-nav-hover', $a['hoverColor'] ?? '' ),
                array( 'color', $a['color'] ?? '' ),
                array( 'background-color', $a['bgColor'] ?? '' ),
        ), kbl3_typography( $a ), kbl3_spacing_pairs( $a ) ) );
        $anchor = isset( $a['anchor'] ) ? (string) $a['anchor'] : '';

        $out = '<nav class="' . esc_attr( $class ) . '" style="' . esc_attr( $style ) . '"' . ( '' !== $anchor ? ' id="' . esc_attr( $anchor ) . '"' : '' ) . '>';
        $out .= '<button type="button" class="kbl3-nav__toggle" aria-expanded="false">'
                . '<span class="screen-reader-text">' . esc_html__( 'Menu', 'kabel-blocks' ) . '</span>'
                . '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="20" height="20" aria-hidden="true"><path d="M4 7h16"></path><path d="M4 12h16"></path><path d="M4 17h16"></path></svg>'
                . '</button>';
        $out .= '<ul class="kbl3-nav__list">';

        list( $tops, $sub ) = kbl3_nav_tree( $menu_items );
        foreach ( $tops as $top ) {
                $children = isset( $sub[ $top['id'] ] ) ? $sub[ $top['id'] ] : array();
                $has_sub  = ! empty( $children );

                $out .= '<li class="kbl3-nav__item' . ( $has_sub ? ' has-sub' : '' ) . ( $top['current'] ? ' is-current' : '' ) . '">'
                        . '<a class="kbl3-nav__link" href="' . esc_url( $top['url'] ) . '">' . esc_html( $top['title'] ) . '</a>';
                if ( $has_sub ) {
                        $out .= '<svg class="kbl3-nav__chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 9l6 6 6-6"></path></svg>';
                        $out .= '<ul class="kbl3-nav__sub">';
                        foreach ( $children as $child ) {
                                $out .= '<li class="kbl3-nav__item' . ( $child['current'] ? ' is-current' : '' ) . '">'
                                        . '<a class="kbl3-nav__link" href="' . esc_url( $child['url'] ) . '">' . esc_html( $child['title'] ) . '</a></li>';
                        }
                        $out .= '</ul>';
                }
                $out .= '</li>';
        }

        $out .= '</ul></nav>';
        return $out;
}

/**
 * Flatten menu item objects into one nesting level (deeper levels join the
 * second). JS twin: navTree() in blocks/nav-menu.js.
 *
 * @param WP_Post[] $items Menu item objects (context-tagged for current page).
 * @return array[] { Tops, children keyed by top id } — each entry is
 *                  array( id, title, url, parent, current ).
 */
function kbl3_nav_tree( array $items ): array {
        $list = array();
        foreach ( $items as $item ) {
                $list[] = array(
                        'id'      => (int) ( $item->db_id ?? $item->ID ),
                        'title'   => (string) $item->title,
                        'url'     => (string) $item->url,
                        'parent'  => (int) $item->menu_item_parent,
                        'current' => in_array( 'current-menu-item', (array) ( $item->classes ?? array() ), true ),
                );
        }

        $by_id = array();
        foreach ( $list as $it ) {
                $by_id[ $it['id'] ] = $it;
        }

        $tops = array();
        $sub  = array();
        foreach ( $list as $it ) {
                if ( ! $it['parent'] || ! isset( $by_id[ $it['parent'] ] ) || $it['parent'] === $it['id'] ) {
                        $tops[] = $it;
                        continue;
                }
                $anc   = $by_id[ $it['parent'] ];
                $guard = 0;
                while ( $anc['parent'] && isset( $by_id[ $anc['parent'] ] ) && $by_id[ $anc['parent'] ]['id'] !== $anc['id'] && $guard++ < 20 ) {
                        $anc = $by_id[ $anc['parent'] ];
                }
                if ( $anc['id'] === $it['id'] ) {
                        $tops[] = $it;
                        continue;
                }
                $sub[ $anc['id'] ][] = $it;
        }

        return array( $tops, $sub );
}

/**
 * Render the search box (kbl/search).
 *
 * A plain GET form to the home URL with name="s" — the native WordPress
 * search results page. The editor shows a faithful static replica.
 */
function kbl3_render_search( array $attributes ): string {
        $a           = $attributes;
        $width       = isset( $a['width'] ) ? (int) $a['width'] : 220;
        $radius      = isset( $a['radius'] ) ? (int) $a['radius'] : 8;
        $placeholder = isset( $a['placeholder'] ) ? (string) $a['placeholder'] : 'Search…';

        $class = kbl3_cx( 'kbl3-search', kbl3_visibility( $a ), kbl3_spacing_class( $a ) );
        $style = kbl3_style( array_merge( array(
                array( '--kbl3-search-w', $width . 'px' ),
                array( '--kbl3-search-r', $radius . 'px' ),
                array( '--kbl3-search-bg', $a['inputBg'] ?? '' ),
                array( '--kbl3-search-fg', $a['textColor'] ?? '' ),
                array( '--kbl3-search-bd', $a['borderColor'] ?? '' ),
                array( '--kbl3-search-ic', $a['iconColor'] ?? '' ),
        ), kbl3_spacing_pairs( $a ) ) );
        $anchor = isset( $a['anchor'] ) ? (string) $a['anchor'] : '';

        return '<form class="' . esc_attr( $class ) . '" role="search" method="get" action="' . esc_url( home_url( '/' ) ) . '"' . ( '' !== $anchor ? ' id="' . esc_attr( $anchor ) . '"' : '' ) . ' style="' . esc_attr( $style ) . '">'
                . '<input type="search" class="kbl3-search__input" name="s" placeholder="' . esc_attr( $placeholder ) . '" aria-label="' . esc_attr__( 'Search', 'kabel-blocks' ) . '">'
                . '<button type="submit" class="kbl3-search__btn" aria-label="' . esc_attr__( 'Search', 'kabel-blocks' ) . '">'
                . '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10.5 3.5a7 7 0 1 0 0 14 7 7 0 0 0 0-14z"></path><path d="M15.8 15.8L21 21"></path></svg>'
                . '</button>'
                . '</form>';
}
