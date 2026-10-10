<?php
/**
 * Template tags — cards, breadcrumbs, pagination, contact helpers, specs.
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Escaped contact info getters (customizer-backed).
 */
function kabel_phone(): string {
        return esc_html( (string) kabel_get_option( 'kabel_phone', '+86 180 2699 3135' ) );
}
function kabel_phone_url(): string {
        return esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', (string) kabel_get_option( 'kabel_phone', '+86 180 2699 3135' ) ) );
}
function kabel_email(): string {
        return esc_html( (string) kabel_get_option( 'kabel_email', '2046772003@qq.com' ) );
}
function kabel_email_url(): string {
        return esc_url( 'mailto:' . (string) kabel_get_option( 'kabel_email', '2046772003@qq.com' ) );
}
function kabel_address(): string {
        return esc_html( (string) kabel_get_option( 'kabel_address', 'Kabel Headquarters: Druento, Torino, Italy · Kabel Branch: Foshan, Guangdong, China' ) );
}
function kabel_cta_text(): string {
        return esc_html( (string) kabel_get_option( 'kabel_cta_text', __( 'Consult Now', 'kabel' ) ) );
}

/**
 * Site identity: custom logo or wordmark.
 */
function kabel_site_identity(): void {
        if ( has_custom_logo() ) {
                the_custom_logo();
                return;
        }
        printf(
                '<a class="site-wordmark" href="%s" rel="home"><span class="site-wordmark__name">KABEL</span><span class="site-wordmark__tag">%s</span></a>',
                esc_url( home_url( '/' ) ),
                esc_html__( 'Art Paint · Druento', 'kabel' )
        );
}

/**
 * Section heading block (A/B aware).
 *
 * @param array $args {eyebrow, title, sub, zone: 'a'|'b', center: bool}
 */
function kabel_section_head( array $args ): void {
        $zone   = ( $args['zone'] ?? 'b' ) === 'a' ? 'a' : 'b';
        $center = ! empty( $args['center'] );
        printf(
                '<div class="section-head section-head--%1$s%2$s"><p class="eyebrow eyebrow--%1$s">%3$s</p><h2 class="section-head__title">%4$s</h2>%5$s</div>',
                esc_attr( $zone ),
                $center ? ' section-head--center' : '',
                esc_html( $args['eyebrow'] ?? '' ),
                esc_html( $args['title'] ?? '' ),
                ! empty( $args['sub'] )
                        ? '<p class="section-head__sub">' . esc_html( $args['sub'] ) . '</p>'
                        : ''
        );
}

/**
 * Product spec rows grouped by "group" for the structured spec table.
 *
 * @return array<string, array<int, array{name:string,value:string,unit:string}>>
 */
function kabel_get_specs_grouped( int $post_id ): array {
        $raw     = get_post_meta( $post_id, '_kb_product_specs', true );
        $grouped = array();
        if ( ! is_array( $raw ) ) {
                return $grouped;
        }
        foreach ( $raw as $row ) {
                if ( ! is_array( $row ) || empty( $row['name'] ) ) {
                        continue;
                }
                $group = $row['group'] ?: __( 'General', 'kabel' );
                $grouped[ $group ][] = array(
                        'name'  => (string) $row['name'],
                        'value' => (string) ( $row['value'] ?? '' ),
                        'unit'  => (string) ( $row['unit'] ?? '' ),
                );
        }
        return $grouped;
}

/**
 * Product card (used on home, archives, related products).
 */
function kabel_product_card( int $post_id ): void {
        $model  = get_post_meta( $post_id, '_kb_product_model', true );
        $badge  = get_post_meta( $post_id, '_kb_product_badge', true );
        $series = get_the_term_list( $post_id, 'product_series', '', ', ' );

        /* One family per product card — skip the series line when it just repeats the title. */
        if ( $series && wp_strip_all_tags( (string) $series ) === wp_strip_all_tags( (string) ( $model ?: get_the_title( $post_id ) ) ) ) {
                $series = '';
        }
        ?>
        <article class="card-product" data-kb-reveal>
                <button type="button" class="card-product__add" data-kb-add-list="<?php echo esc_attr( $model ? $model : get_the_title( $post_id ) ); ?>">
                        <span aria-hidden="true">+</span>
                        <span class="screen-reader-text"><?php echo esc_html( sprintf( __( 'Add %s to inquiry list', 'kabel' ), $model ? $model : get_the_title( $post_id ) ) ); ?></span>
                </button>
                <a class="card-product__link" href="<?php echo esc_url( (string) get_permalink( $post_id ) ); ?>">
                        <span class="card-product__media">
                                <?php
                                if ( has_post_thumbnail( $post_id ) ) {
                                        echo get_the_post_thumbnail( $post_id, 'kabel-card', array( 'alt' => esc_attr( (string) get_the_title( $post_id ) ) ) );
                                } else {
                                        echo '<span class="card-product__placeholder" aria-hidden="true">DZ</span>';
                                }
                                if ( $badge ) :
                                        ?>
                                        <span class="card-product__badge"><?php echo esc_html( strtoupper( $badge ) ); ?></span>
                                <?php endif; ?>
                        </span>
                        <span class="card-product__info">
                                <strong class="card-product__model"><?php echo esc_html( $model ? $model : get_the_title( $post_id ) ); ?></strong>
                                <span class="card-product__series"><?php echo wp_kses_post( $series ? $series : '&nbsp;' ); ?></span>
                                <span class="card-product__inquire"><?php esc_html_e( 'Consult →', 'kabel' ); ?></span>
                        </span>
                </a>
        </article>
        <?php
}

/**
 * Case card (region badge + title + metrics).
 */
function kabel_case_card( int $post_id ): void {
        $country = get_post_meta( $post_id, '_kb_case_country', true );
        $flag    = get_post_meta( $post_id, '_kb_case_flag', true );
        $order   = get_post_meta( $post_id, '_kb_case_order', true );
        $types   = array(
                'distributor' => __( 'Distributor', 'kabel' ),
                'hotel'       => __( 'Hotel', 'kabel' ),
                'education'   => __( 'Education', 'kabel' ),
                'medical'     => __( 'Medical', 'kabel' ),
                'office'      => __( 'Office', 'kabel' ),
                'ecommerce'   => __( 'E-commerce', 'kabel' ),
        );
        $type_key = get_post_meta( $post_id, '_kb_case_client_type', true );
        ?>
        <article class="card-case" data-kb-reveal>
                <a href="<?php echo esc_url( (string) get_permalink( $post_id ) ); ?>">
                        <?php if ( $country ) : ?>
                                <span class="card-case__region"><?php echo esc_html( trim( $flag . ' ' . $country ) ); ?></span>
                        <?php endif; ?>
                        <h3 class="card-case__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
                        <p class="card-case__excerpt"><?php echo esc_html( wp_trim_words( (string) get_the_excerpt( $post_id ), 18 ) ); ?></p>
                        <span class="card-case__meta">
                                <?php if ( isset( $types[ $type_key ] ) ) : ?>
                                        <span class="chip"><?php echo esc_html( $types[ $type_key ] ); ?></span>
                                <?php endif; ?>
                                <?php if ( $order ) : ?>
                                        <span class="chip"><?php echo esc_html( $order ); ?></span>
                                <?php endif; ?>
                        </span>
                </a>
        </article>
        <?php
}

/**
 * Post card (news).
 */
function kabel_post_card( int $post_id ): void {
        ?>
        <article class="card-post" data-kb-reveal>
                <a href="<?php echo esc_url( (string) get_permalink( $post_id ) ); ?>">
                        <span class="card-post__media">
                                <?php
                                if ( has_post_thumbnail( $post_id ) ) {
                                        echo get_the_post_thumbnail( $post_id, 'kabel-card', array( 'alt' => esc_attr( (string) get_the_title( $post_id ) ) ) );
                                } else {
                                        echo '<span class="card-post__placeholder" aria-hidden="true"></span>';
                                }
                                ?>
                        </span>
                        <span class="card-post__info">
                                <time class="card-post__date" datetime="<?php echo esc_attr( get_the_date( 'c', $post_id ) ); ?>"><?php echo esc_html( get_the_date( '', $post_id ) ); ?></time>
                                <h3 class="card-post__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
                                <span class="card-post__more"><?php esc_html_e( 'Read more →', 'kabel' ); ?></span>
                        </span>
                </a>
        </article>
        <?php
}

/**
 * Simple breadcrumbs.
 */
function kabel_breadcrumbs(): void {
        if ( is_front_page() ) {
                return;
        }
        echo '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'kabel' ) . '"><ol>';
        echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'kabel' ) . '</a></li>';

        if ( is_singular() ) {
                $post_type = get_post_type();
                if ( 'post' === $post_type ) {
                        echo '<li><a href="' . esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/' ) ) . '">' . esc_html__( 'News', 'kabel' ) . '</a></li>';
                } elseif ( 'product' === $post_type ) {
                        echo '<li><a href="' . esc_url( get_post_type_archive_link( 'product' ) ) . '">' . esc_html__( 'Products', 'kabel' ) . '</a></li>';
                } elseif ( 'projects' === $post_type ) {
                        echo '<li><a href="' . esc_url( get_post_type_archive_link( 'projects' ) ) . '">' . esc_html__( 'Projects', 'kabel' ) . '</a></li>';
                }
                echo '<li aria-current="page">' . esc_html( wp_trim_words( get_the_title(), 8 ) ) . '</li>';
        } elseif ( is_home() ) {
                echo '<li aria-current="page">' . esc_html__( 'News', 'kabel' ) . '</li>';
        } elseif ( is_archive() ) {
                echo '<li aria-current="page">' . esc_html( wp_strip_all_tags( (string) get_the_archive_title() ) ) . '</li>';
        } elseif ( is_search() ) {
                echo '<li aria-current="page">' . esc_html__( 'Search results', 'kabel' ) . '</li>';
        }

        echo '</ol></nav>';
}

/**
 * Pagination wrapper.
 */
function kabel_pagination(): void {
        the_posts_pagination( array(
                'mid_size'  => 2,
                'prev_text' => '← ' . __( 'Previous', 'kabel' ),
                'next_text' => __( 'Next', 'kabel' ) . ' →',
        ) );
}

/**
 * Estimated reading time in minutes (word count / 200, minimum 1).
 *
 * @param int $post_id Post ID.
 * @return int Minutes.
 */
function kabel_reading_time( int $post_id ): int {
        $words = str_word_count( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ) );
        return max( 1, (int) ceil( $words / 200 ) );
}

/**
 * Query products (shared helper).
 */
function kabel_get_products( array $args = array() ): WP_Query {
        return new WP_Query( wp_parse_args( $args, array(
                'post_type'      => 'product',
                'post_status'    => 'publish',
                'posts_per_page' => 6,
                'orderby'        => 'menu_order date',
                'order'          => 'ASC',
        ) ) );
}

/* ---------------------------------------------------------------------
 * Block-page takeover + plugin bridges (v0.4)
 * ------------------------------------------------------------------- */

/**
 * The block takeover rule.
 *
 * A page (or static front page) whose editor content is non-empty renders
 * full-canvas as blocks — the Kabel Blocks kit or anything else. An empty
 * page keeps the built-in designed template. Call at the very top of page
 * templates; returns true when it rendered the page.
 */
function kabel_render_block_page(): bool {
        $post = get_post();
        if ( ! $post instanceof WP_Post || ! in_array( $post->post_type, array( 'page', 'post' ), true ) ) {
                return false;
        }
        if ( '' === trim( (string) $post->post_content ) ) {
                return false;
        }

        get_header();
        ?>
        <div class="kabel-block-page">
                <?php
                while ( have_posts() ) :
                        the_post();
                        the_content();
                endwhile;
                ?>
        </div>
        <?php
        get_footer();
        return true;
}

/**
 * Bridge for the Kabel Blocks "inquiry" block: the theme's unified form,
 * without its own <section> wrapper (the block provides the shell).
 *
 * v12: returns the form body only. The old two-column wrapper
 * (.inquiry-section__grid--block) leaned on main.css grid rules that are
 * scoped to the classic section layout, so inside a block page the form sat
 * pushed right / crooked. The block's own .kbl3-form shell centers the form
 * (max-width:720px; margin-inline:auto), matching the editor replica.
 *
 * @param string $product Pre-filled product name.
 * @param string $message Pre-filled opening message.
 */
function kabel_form_inquiry_part( string $product = '', string $message = '' ): string {
        ob_start();
        kabel_render_inquiry_form( array(
                'product' => $product,
                'message' => $message,
                'source'  => is_singular() ? (string) get_permalink() : home_url( '/' ),
        ) );
        return (string) ob_get_clean();
}

/**
 * Does the blog sidebar have widgets? (Empty sidebar keeps full-width layout.)
 */
function kabel_has_blog_sidebar(): bool {
        if ( ! is_active_sidebar( 'blog-sidebar' ) ) {
                return false;
        }
        // Only in blog contexts — pages and products stay full width.
        return is_home() || is_archive() || is_single() || is_search();
}

/**
 * Open the with-sidebar layout wrapper.
 */
function kabel_sidebar_open(): void {
        if ( kabel_has_blog_sidebar() ) {
                echo '<div class="with-sidebar">';
        }
}

/**
 * Close the with-sidebar layout wrapper.
 */
function kabel_sidebar_close(): void {
        if ( kabel_has_blog_sidebar() ) {
                echo '</div>';
        }
}
