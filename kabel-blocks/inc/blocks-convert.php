<?php
/**
 * Block specs + renderers — the conversion group.
 *
 * products, cta, inquiry, contact, section (universal container), cta-quote.
 *
 * Required from inside kbl_specs() — $register is provided by the engine.
 *
 * v7: every spec carries an `edit` tree — a declarative node layout the JS
 * runtime renders ON the canvas with inline RichText editing (see
 * docs/kabel-v7-inline-editing.md). Panels keep style controls only.
 *
 * Live-query / shortcode blocks cannot re-run their PHP at edit time, so
 * their canvas side is a styled `static` replica (products grid, inquiry
 * form); the section block needs no tree at all (InnerBlocks runtime path).
 *
 * @package Kabel_Blocks
 */

defined( 'ABSPATH' ) || exit;

/* =====================================================================
 * Products — the live catalog grid
 * =================================================================== */

/*
 * Canvas replica of the live products grid (the editor cannot re-run the
 * WP_Query). Mirrors the standalone card shape styled by kabel-editor.css
 * (.kbl-product-card) with the three real Kabel series as stand-ins.
 */
$kabel_products_preview = '';
foreach ( array(
        array( 'Velvet Texture', 'Velvet Texture', 'Luxury velvet matte finish' ),
        array( 'Lime Plaster', 'Lime Plaster', 'Breathable mineral plaster' ),
        array( 'Microcement', 'Microcement', 'Seamless wall & floor integration' ),
) as $kabel_pcard ) {
        $kabel_products_preview .= '<article class="kbl-product-card"><span class="kbl-ph" aria-hidden="true"></span>';
        $kabel_products_preview .= '<div class="kbl-product-card__body">';
        $kabel_products_preview .= '<p class="kbl-product-card__series">' . esc_html( $kabel_pcard[0] ) . '</p>';
        $kabel_products_preview .= '<h3 class="kbl-product-card__title">' . esc_html( $kabel_pcard[1] ) . '</h3>';
        $kabel_products_preview .= '<p class="kbl-product-card__sub">' . esc_html( $kabel_pcard[2] ) . '</p>';
        $kabel_products_preview .= '</div></article>';
}
$kabel_products_preview = '<div class="kbl-products">' . $kabel_products_preview . '</div>';

$register( array(
        'name'        => kbl_name( 'products' ),
        'title'       => __( 'Products Grid', 'kabel-blocks' ),
        'description' => __( 'A live grid of your product catalog — new products appear automatically. With the Kabel theme active each card uses the theme\'s product card design.', 'kabel-blocks' ),
        'icon'        => 'screenoptions',
        'keywords'    => array( 'products', 'catalog', 'grid', 'shop' ),
        'attributes'  => array_merge( kbl_common_attrs(), array(
                'eyebrow'  => array( 'type' => 'string', 'default' => '' ),
                'title'    => array( 'type' => 'string', 'default' => '' ),
                'sub'      => array( 'type' => 'string', 'default' => '' ),
                'count'    => array( 'type' => 'number', 'default' => 6 ),
                'columns'  => array( 'type' => 'number', 'default' => 3 ),
                'series'   => array( 'type' => 'string', 'default' => '' ),
                'orderby'  => array( 'type' => 'string', 'default' => 'menu_order' ),
                'ctaLabel' => array( 'type' => 'string', 'default' => '' ),
                'ctaUrl'   => array( 'type' => 'string', 'default' => '' ),
        ) ),
        'edit'        => array(
                'tree'  => array(
                        array( 'static' => $kabel_products_preview ),
                        array( 'wrap' => array( 'p', 'section-foot kbl-section-foot' ), 'nodes' => array(
                                array( 'buttons' => array(
                                        array( 'label' => 'ctaLabel', 'class' => 'btn btn--outline-teal', 'placeholder' => __( 'View all products', 'kabel-blocks' ) ),
                                ) ),
                        ) ),
                        array( 'static' => '<div class="kbl-hint">' . esc_html__( 'This grid is live — it fills itself with your published products. How many, columns, series and order are set in the sidebar; the button label above is edited right here.', 'kabel-blocks' ) . '</div>' ),
                ),
        ),
        'panels'      => array_merge(
                array(
                        array(
                                'title'       => __( 'Products settings', 'kabel-blocks' ),
                                'initialOpen' => true,
                                'fields'      => array(
                                        array( 'attr' => 'count', 'label' => __( 'How many products', 'kabel-blocks' ), 'type' => 'range', 'min' => 1, 'max' => 12 ),
                                        array( 'attr' => 'columns', 'label' => __( 'Columns', 'kabel-blocks' ), 'type' => 'range', 'min' => 2, 'max' => 4 ),
                                        array( 'attr' => 'series', 'label' => __( 'Product series slug (optional)', 'kabel-blocks' ), 'type' => 'text', 'help' => __( 'Filter the grid to one series, e.g. velvet-texture. Leave empty to show every series.', 'kabel-blocks' ) ),
                                        array(
                                                'attr'    => 'orderby',
                                                'label'   => __( 'Order by', 'kabel-blocks' ),
                                                'type'    => 'select',
                                                'options' => array(
                                                        array( 'value' => 'menu_order', 'label' => __( 'Menu order (curated)', 'kabel-blocks' ) ),
                                                        array( 'value' => 'date', 'label' => __( 'Newest first', 'kabel-blocks' ) ),
                                                        array( 'value' => 'title', 'label' => __( 'Title A–Z', 'kabel-blocks' ) ),
                                                        array( 'value' => 'rand', 'label' => __( 'Random', 'kabel-blocks' ) ),
                                                ),
                                        ),
                                        array( 'attr' => 'ctaUrl', 'label' => __( 'Button under the grid — link', 'kabel-blocks' ), 'type' => 'text', 'help' => __( 'The button label is edited on the canvas.', 'kabel-blocks' ) ),
                                ),
                        ),
                ),
                kbl_common_panels_tail()
        ),
        'render'      => static function ( array $a ): string {
                if ( ! post_type_exists( 'product' ) ) {
                        if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
                                return '<div class="kbl-hint">' . esc_html__( 'No "product" catalog on this site yet. Activate the Kabel theme (it registers the product post type), then this grid fills itself.', 'kabel-blocks' ) . '</div>';
                        }
                        return '';
                }

                $count = max( 1, min( 12, (int) $a['count'] ) );
                $args  = array(
                        'post_type'           => 'product',
                        'post_status'         => 'publish',
                        'posts_per_page'      => $count,
                        'no_found_rows'       => true,
                        'ignore_sticky_posts' => true,
                );

                switch ( $a['orderby'] ) {
                        case 'date':
                                $args['orderby'] = 'date';
                                break;
                        case 'title':
                                $args['orderby'] = 'title';
                                $args['order']   = 'ASC';
                                break;
                        case 'rand':
                                $args['orderby'] = 'rand';
                                break;
                        default:
                                $args['orderby'] = 'menu_order date';
                                $args['order']   = 'ASC';
                }

                $series = sanitize_title( (string) $a['series'] );
                if ( '' !== $series && taxonomy_exists( 'product_series' ) ) {
                        $term = get_term_by( 'slug', $series, 'product_series' );
                        if ( $term && ! is_wp_error( $term ) ) {
                                $args['tax_query'] = array(
                                        array(
                                                'taxonomy' => 'product_series',
                                                'field'    => 'term_id',
                                                'terms'    => array( $term->term_id ),
                                        ),
                                );
                        }
                }

                $query = new WP_Query( $args );
                if ( ! $query->have_posts() ) {
                        if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
                                return '<div class="kbl-hint">' . esc_html__( 'No published products match yet — add products in the admin, or relax the series filter.', 'kabel-blocks' ) . '</div>';
                        }
                        return '';
                }

                $theme  = kabel_blocks_theme_active();
                $body   = kbl_grid_open( $a['columns'], 'kbl-products' );
                while ( $query->have_posts() ) :
                        $query->the_post();
                        if ( $theme && function_exists( 'kabel_product_card' ) ) {
                                ob_start();
                                kabel_product_card( get_the_ID() );
                                $body .= (string) ob_get_clean();
                        } else {
                                $body .= kbl_standalone_product_card( get_the_ID() );
                        }
                endwhile;
                wp_reset_postdata();
                $body .= '</div>';

                if ( '' !== kbl_text( $a['ctaLabel'] ) ) {
                        $url  = '' !== kbl_url( $a['ctaUrl'] )
                                ? kbl_url( $a['ctaUrl'] )
                                : (string) ( get_post_type_archive_link( 'product' ) ?: home_url( '/products/' ) );
                        $body .= '<p class="section-foot kbl-section-foot">' . kbl_button_html( $a['ctaLabel'], $url, '', 'outline' ) . '</p>';
                }

                return kbl_shell( kbl_name( 'products' ), $a, $body );
        },
) );

/**
 * Standalone product card (Kabel theme NOT active).
 */
function kbl_standalone_product_card( int $post_id ): string {
        $series = get_the_term_list( $post_id, 'product_series', '', ', ' );
        $html  = '<article class="kbl-card kbl-pcard">';
        $html .= '<a href="' . esc_url( (string) get_permalink( $post_id ) ) . '">';
        $html .= '<span class="kbl-pcard__media">';
        if ( has_post_thumbnail( $post_id ) ) {
                $html .= (string) get_the_post_thumbnail( $post_id, 'medium_large', array( 'alt' => esc_attr( (string) get_the_title( $post_id ) ) ) );
        } else {
                $html .= '<span class="kbl-ph kbl-pcard__placeholder" aria-hidden="true"></span>';
        }
        $html .= '</span><span class="kbl-pcard__info">';
        $html .= '<strong>' . esc_html( get_the_title( $post_id ) ) . '</strong>';
        if ( $series && ! is_wp_error( $series ) ) {
                $html .= '<span class="kbl-pcard__terms">' . wp_kses_post( $series ) . '</span>';
        }
        $html .= '<span class="kbl-pcard__more">' . esc_html__( 'View details →', 'kabel-blocks' ) . '</span>';
        $html .= '</span></a></article>';
        return $html;
}

/* =====================================================================
 * CTA — the conversion banner
 * =================================================================== */

$register( array(
        'name'        => kbl_name( 'cta' ),
        'title'       => __( 'CTA Banner', 'kabel-blocks' ),
        'description' => __( 'The conversion banner: headline, promise line and the amber quote button (opens the inquiry popup with the Kabel theme).', 'kabel-blocks' ),
        'icon'        => 'megaphone',
        'keywords'    => array( 'cta', 'call to action', 'quote', 'conversion' ),
        'attributes'  => array_merge( kbl_common_attrs(), array(
                'zone'    => array( 'type' => 'string', 'default' => 'a' ),
                'pad'     => array( 'type' => 'string', 'default' => 'spacious' ),
                'eyebrow' => array( 'type' => 'string', 'default' => '' ),
                'title'   => array( 'type' => 'string', 'default' => '' ),
                'sub'     => array( 'type' => 'string', 'default' => '' ),
                'btnLabel'   => array( 'type' => 'string', 'default' => '' ),
                'btnProduct' => array( 'type' => 'string', 'default' => '' ),
                'btn2Label'  => array( 'type' => 'string', 'default' => '' ),
                'btn2Url'    => array( 'type' => 'string', 'default' => '' ),
        ) ),
        'edit'        => array(
                'extra' => 'kbl-cta',
                'head'  => false, /* The banner renders its own heading inside the card. */
                'tree'  => array(
                        array( 'wrap' => array( 'div', 'kbl-cta-card' ), 'nodes' => array(
                                array( 'head' => true ),
                                array( 'wrap' => array( 'div', 'kbl-cta-card__actions' ), 'nodes' => array(
                                        array( 'buttons' => array(
                                                array( 'label' => 'btnLabel', 'class' => 'btn btn--cta', 'placeholder' => __( 'Consult Now', 'kabel-blocks' ) ),
                                                array( 'label' => 'btn2Label', 'class' => 'btn btn--ghost', 'placeholder' => __( 'Secondary', 'kabel-blocks' ) ),
                                        ) ),
                                ) ),
                        ) ),
                ),
        ),
        'panels'      => array_merge(
                array(
                        array(
                                'title'       => __( 'Button links', 'kabel-blocks' ),
                                'initialOpen' => false,
                                'fields'      => array(
                                        array( 'attr' => 'btnProduct', 'label' => __( 'Pre-filled product (optional)', 'kabel-blocks' ), 'type' => 'text' ),
                                        array( 'attr' => 'btn2Url', 'label' => __( 'Secondary button — link', 'kabel-blocks' ), 'type' => 'text' ),
                                ),
                        ),
                ),
                kbl_common_panels_tail()
        ),
        'render'      => static function ( array $a ): string {
                $head = kbl_head_html( $a, 'a' );

                $buttons  = kbl_button_html( '' !== kbl_text( $a['btnLabel'] ) ? $a['btnLabel'] : __( 'Consult Now', 'kabel-blocks' ), '', $a['btnProduct'], 'cta' );
                if ( '' !== kbl_text( $a['btn2Label'] ) ) {
                        $buttons .= kbl_button_html( $a['btn2Label'], $a['btn2Url'], '', 'ghost' );
                }

                $body = '<div class="kbl-cta-card">' . $head . '<div class="kbl-cta-card__actions">' . $buttons . '</div></div>';

                return kbl_shell( kbl_name( 'cta' ), $a, $body, 'kbl-cta', '' );
        },
) );

/* =====================================================================
 * Inquiry — the lead form section
 * =================================================================== */

/*
 * Canvas replica of the inquiry form (the real form is rendered at runtime
 * by the theme / plugin — the labels mirror the theme's 7 inquiry fields).
 */
$kabel_inquiry_replica  = '<div class="kbl-form-replica">';
$kabel_inquiry_replica .= '<div class="kbl-field-row"><div><label>' . esc_html__( 'Name', 'kabel-blocks' ) . ' *</label><div class="kbl-input-replica">' . esc_html__( 'Your name', 'kabel-blocks' ) . '</div></div>';
$kabel_inquiry_replica .= '<div><label>' . esc_html__( 'Email', 'kabel-blocks' ) . ' *</label><div class="kbl-input-replica">name@company.com</div></div></div>';
$kabel_inquiry_replica .= '<div class="kbl-field-row"><div><label>' . esc_html__( 'WhatsApp or Phone', 'kabel-blocks' ) . '</label><div class="kbl-input-replica">+00 000 000 000</div></div>';
$kabel_inquiry_replica .= '<div><label>' . esc_html__( 'Company', 'kabel-blocks' ) . '</label><div class="kbl-input-replica">' . esc_html__( 'Company name', 'kabel-blocks' ) . '</div></div></div>';
$kabel_inquiry_replica .= '<div class="kbl-field-row"><div><label>' . esc_html__( 'Country', 'kabel-blocks' ) . '</label><div class="kbl-input-replica">' . esc_html__( 'Country', 'kabel-blocks' ) . '</div></div>';
$kabel_inquiry_replica .= '<div><label>' . esc_html__( 'Project Type', 'kabel-blocks' ) . '</label><div class="kbl-input-replica">' . esc_html__( 'Select a project type', 'kabel-blocks' ) . '</div></div></div>';
$kabel_inquiry_replica .= '<div class="kbl-field-row kbl-field-row--single"><div><label>' . esc_html__( 'Message', 'kabel-blocks' ) . ' *</label><textarea class="kbl-input-replica" rows="4">' . esc_html__( 'How can we help?', 'kabel-blocks' ) . '</textarea></div></div>';
$kabel_inquiry_replica .= '<div class="kbl-field-row kbl-field-row--single"><span class="btn btn--cta">' . esc_html__( 'Send Inquiry', 'kabel-blocks' ) . '</span></div>';
$kabel_inquiry_replica .= '</div>';
$kabel_inquiry_replica .= '<p class="kbl-form-note">' . esc_html__( 'Live form — visitors fill it in on the page. Submissions land in Inquiries and forward by email; the prefills are set in the sidebar.', 'kabel-blocks' ) . '</p>';

$register( array(
        'name'        => kbl_name( 'inquiry' ),
        'title'       => __( 'Inquiry Form', 'kabel-blocks' ),
        'description' => __( 'The B2B lead form. With the Kabel theme active it renders the theme\'s 7-field inquiry form (submissions land in Inquiries + email). Standalone it renders the plugin\'s own compact form.', 'kabel-blocks' ),
        'icon'        => 'email-alt',
        'keywords'    => array( 'inquiry', 'form', 'contact', 'lead', 'inquiry form' ),
        'attributes'  => array_merge( kbl_common_attrs(), array(
                'eyebrow'  => array( 'type' => 'string', 'default' => '' ),
                'title'    => array( 'type' => 'string', 'default' => '' ),
                'sub'      => array( 'type' => 'string', 'default' => '' ),
                'product'  => array( 'type' => 'string', 'default' => '' ),
                'message'  => array( 'type' => 'string', 'default' => '' ),
        ) ),
        'edit'        => array(
                'extra' => 'kbl-inquiry-section',
                'tree'  => array(
                        array( 'wrap' => array( 'div', 'kbl-inquiry' ), 'nodes' => array(
                                array( 'static' => $kabel_inquiry_replica ),
                        ) ),
                ),
        ),
        'panels'      => array_merge(
                array(
                        array(
                                'title'       => __( 'Form', 'kabel-blocks' ),
                                'initialOpen' => true,
                                'fields'      => array(
                                        array( 'attr' => 'product', 'label' => __( 'Pre-filled product (optional)', 'kabel-blocks' ), 'type' => 'text' ),
                                        array( 'attr' => 'message', 'label' => __( 'Pre-filled opening message (optional)', 'kabel-blocks' ), 'type' => 'textarea', 'help' => __( 'Machine value: seeds the visitor\'s message box — not rendered as page copy.', 'kabel-blocks' ) ),
                                ),
                        ),
                ),
                kbl_common_panels_tail()
        ),
        'render'      => static function ( array $a ): string {
                $head = kbl_head_html( $a, $a['zone'] ?? 'b' );

                if ( kabel_blocks_theme_active() && function_exists( 'kabel_form_inquiry_part' ) ) {
                        $form = kabel_form_inquiry_part( $a['product'], $a['message'] );
                } else {
                        $form = kbl_leads_form( $a['product'], $a['message'] );
                }

                $body = '<div class="kbl-inquiry">' . $form . '</div>';

                return kbl_shell( kbl_name( 'inquiry' ), $a, $body, 'kbl-inquiry-section', $head );
        },
) );

/* =====================================================================
 * Contact — info cards
 * =================================================================== */

$register( array(
        'name'        => kbl_name( 'contact' ),
        'title'       => __( 'Contact Cards', 'kabel-blocks' ),
        'description' => __( 'Phone, email, address and working-hours cards — with the Kabel theme these read your Customizer contact settings by default.', 'kabel-blocks' ),
        'icon'        => 'phone',
        'keywords'    => array( 'contact', 'info', 'address', 'phone' ),
        'attributes'  => array_merge( kbl_common_attrs(), array(
                'eyebrow' => array( 'type' => 'string', 'default' => '' ),
                'title'   => array( 'type' => 'string', 'default' => '' ),
                'sub'     => array( 'type' => 'string', 'default' => '' ),
                'columns' => array( 'type' => 'number', 'default' => 3 ),
                'items'   => array(
                        'type'    => 'array',
                        'default' => array(),
                        'items'   => array( 'type' => 'object' ),
                ),
        ) ),
        'edit'        => array(
                'tree'  => array(
                        array(
                                'items'     => 'items',
                                'classAttr' => 'columns',
                                'classBase' => 'grid grid--',
                                'itemClass' => 'kbl-contact__card',
                                'addLabel'  => __( 'Add a card', 'kabel-blocks' ),
                                'item'      => array(
                                        /* Decorative icon replica — per-item icon picks went away with the sidebar repeater. */
                                        array( 'static' => '<span class="kbl-contact__icon">' . kbl_icon( 'pin' ) . '</span>' ),
                                        array( 'wrap' => array( 'div', 'kbl-contact__copy' ), 'nodes' => array(
                                                array( 'text' => 'title', 'tag' => 'h3', 'placeholder' => __( 'Card title', 'kabel-blocks' ) ),
                                                array( 'rich' => 'text', 'tag' => 'p', 'placeholder' => __( 'Contact details', 'kabel-blocks' ) ),
                                        ) ),
                                ),
                        ),
                ),
        ),
        'panels'      => array_merge(
                array(
                        array(
                                'title'       => __( 'Cards', 'kabel-blocks' ),
                                'initialOpen' => false,
                                'fields'      => array(
                                        array( 'attr' => 'columns', 'label' => __( 'Columns', 'kabel-blocks' ), 'type' => 'range', 'min' => 2, 'max' => 4 ),
                                ),
                        ),
                ),
                kbl_common_panels_tail()
        ),
        'render'      => static function ( array $a ): string {
                $items = kbl_items( $a['items'], array( 'icon', 'title', 'text' ) );
                if ( ! $items ) {
                        return '';
                }

                $body = kbl_grid_open( $a['columns'], 'kbl-contact' );
                foreach ( $items as $item ) {
                        $body .= '<article class="kbl-contact__card">';
                        $body .= '<span class="kbl-contact__icon">' . kbl_icon( $item['icon'] ?: 'pin' ) . '</span>'; /* '' → pin: matches the canvas replica's icon. */
                        $body .= '<div class="kbl-contact__copy">';
                        $body .= $item['title'] ? '<h3>' . kbl_text( $item['title'] ) . '</h3>' : '';
                        $body .= $item['text'] ? '<p>' . kbl_rich( $item['text'] ) . '</p>' : '';
                        $body .= '</div></article>';
                }
                $body .= '</div>';

                return kbl_shell( kbl_name( 'contact' ), $a, $body );
        },
) );

/* =====================================================================
 * Section — the universal container (InnerBlocks)
 * =================================================================== */

$register( array(
        'name'        => kbl_name( 'section' ),
        'title'       => __( 'Section (container)', 'kabel-blocks' ),
        'description' => __( 'The universal container: pick the background and padding, then drop ANY blocks inside — Kabel blocks, core blocks, Stackable, anything.', 'kabel-blocks' ),
        'icon'        => 'editor-table',
        'keywords'    => array( 'section', 'container', 'wrapper', 'zone' ),
        'inner'       => true,
        'supports'    => array(
                'html'  => false,
                'align' => array( 'wide', 'full' ),
        ),
        'attributes'  => array_merge( kbl_common_attrs(), array(
                'width' => array(
                        'type'    => 'string',
                        'default' => 'default',
                ),
        ) ),
        /* v7: no edit tree — the runtime renders the zone shell + open InnerBlocks
           for spec.inner blocks. head=>false mirrors the render callback, which
           emits no section head of its own. */
        'edit'        => array(
                'extra' => 'kbl-section',
                'head'  => false,
        ),
        'panels'      => array_merge(
                array(
                        array(
                                'title'       => __( 'Container', 'kabel-blocks' ),
                                'initialOpen' => true,
                                'fields'      => array(
                                        array(
                                                'attr'    => 'width',
                                                'label'   => __( 'Content width', 'kabel-blocks' ),
                                                'type'    => 'select',
                                                'options' => array(
                                                        array( 'value' => 'default', 'label' => __( 'Default', 'kabel-blocks' ) ),
                                                        array( 'value' => 'narrow', 'label' => __( 'Narrow (reading)', 'kabel-blocks' ) ),
                                                        array( 'value' => 'full', 'label' => __( 'Full width', 'kabel-blocks' ) ),
                                                ),
                                        ),
                                ),
                        ),
                ),
                kbl_common_panels_tail()
        ),
        'render'      => static function ( array $a, string $content = '' ): string {
                $container = 'narrow' === $a['width'] ? 'container container--narrow' : 'container';

                return sprintf(
                        '<section class="%1$s" data-kbl="%2$s"><div class="%3$s">%4$s</div></section>',
                        esc_attr( kbl_zone_classes( $a, 'kbl-section' ) ),
                        esc_attr( kbl_name( 'section' ) ),
                        esc_attr( $container ),
                        $content
                );
        },
) );

/* =====================================================================
 * CTA quote — the inline button (small, flow-friendly)
 * =================================================================== */

$register( array(
        'name'        => kbl_name( 'cta-quote' ),
        'title'       => __( 'Quote Button', 'kabel-blocks' ),
        'description' => __( 'The conversion button anywhere you need it — opens the inquiry popup with the Kabel theme, or links anywhere on other themes.', 'kabel-blocks' ),
        'icon'        => 'button',
        'keywords'    => array( 'quote', 'button', 'inquiry', 'cta' ),
        'supports'    => array(
                'html'  => false,
                'align' => array( 'center', 'wide', 'full' ),
        ),
        'attributes'  => array(
                'text'    => array( 'type' => 'string', 'default' => '' ),
                'product' => array( 'type' => 'string', 'default' => '' ),
                'url'     => array( 'type' => 'string', 'default' => '' ),
                'style'   => array( 'type' => 'string', 'default' => 'cta' ),
                'align'   => array( 'type' => 'string', 'default' => '' ),
        ),
        'edit'        => array(
                'head'  => false, /* Flow-level button — no section head, no heading attrs. */
                'tree'  => array(
                        array( 'wrap' => array( 'div', 'kbl-cta-row' ), 'nodes' => array(
                                array( 'switch' => 'style', 'default' => 'cta', 'cases' => array(
                                        'cta'     => array(
                                                array( 'buttons' => array( array( 'label' => 'text', 'class' => 'btn btn--cta', 'placeholder' => __( 'Consult Now', 'kabel-blocks' ) ) ) ),
                                        ),
                                        'outline' => array(
                                                array( 'buttons' => array( array( 'label' => 'text', 'class' => 'btn btn--outline-teal', 'placeholder' => __( 'Consult Now', 'kabel-blocks' ) ) ) ),
                                        ),
                                ) ),
                        ) ),
                ),
        ),
        'panels'      => array(
                array(
                        'title'       => __( 'Quote button', 'kabel-blocks' ),
                        'initialOpen' => true,
                        'fields'      => array(
                                array( 'attr' => 'product', 'label' => __( 'Pre-filled product (optional)', 'kabel-blocks' ), 'type' => 'text' ),
                                array( 'attr' => 'url', 'label' => __( 'Link URL (optional)', 'kabel-blocks' ), 'type' => 'text', 'help' => __( 'With the Kabel theme and this empty, the button opens the inquiry popup.', 'kabel-blocks' ) ),
                                array(
                                        'attr'    => 'style',
                                        'label'   => __( 'Style', 'kabel-blocks' ),
                                        'type'    => 'select',
                                        'options' => array(
                                                array( 'value' => 'cta', 'label' => __( 'Amber (primary)', 'kabel-blocks' ) ),
                                                array( 'value' => 'outline', 'label' => __( 'Outline (secondary)', 'kabel-blocks' ) ),
                                        ),
                                ),
                        ),
                ),
        ),
        'render'      => static function ( array $a ): string {
                $text  = '' !== kbl_text( $a['text'] ) ? $a['text'] : __( 'Consult Now', 'kabel-blocks' );
                $style = 'outline' === $a['style'] ? 'outline' : 'cta';

                $wrap = 'kbl-cta-row';
                if ( '' !== (string) $a['align'] ) {
                        $wrap .= ' align' . sanitize_html_class( (string) $a['align'] );
                }

                return sprintf(
                        '<div class="%1$s">%2$s</div>',
                        esc_attr( $wrap ),
                        kbl_button_html( $text, $a['url'], $a['product'], $style )
                );
        },
) );
