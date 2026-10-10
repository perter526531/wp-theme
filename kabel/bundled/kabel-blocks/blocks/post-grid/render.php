<?php
/**
 * kbl/post-grid — server render template.
 *
 * Loaded by WordPress for every instance of the block (block.json
 * "render": "file:./render.php"): the block attributes are in $attributes,
 * the saved content in $content (always empty — the editor save is null)
 * and the block instance in $block.
 *
 * Renders the latest products / projects / posts as a live card grid.
 *
 * @package Kabel_Blocks
 */

defined( 'ABSPATH' ) || exit;

$a = ( isset( $attributes ) && is_array( $attributes ) ) ? $attributes : array();

$post_type    = isset( $a['postType'] ) ? (string) $a['postType'] : 'product';
$columns      = isset( $a['columns'] ) ? max( 1, min( 4, (int) $a['columns'] ) ) : 3;
$count        = isset( $a['count'] ) ? max( 1, min( 12, (int) $a['count'] ) ) : 6;
$show_excerpt = ! isset( $a['showExcerpt'] ) || $a['showExcerpt'];
$btn_label    = isset( $a['btnLabel'] ) ? (string) $a['btnLabel'] : 'View All';
$btn_url      = isset( $a['btnURL'] ) ? (string) $a['btnURL'] : '';

/* v4 responsive layer (JS twin: RESP_SPEC / STRIP_KEYS in
 * blocks/post-grid/edit.js). Once a uid exists the columns / mt / mb
 * triples ride the per-instance <style> tag: the inline --kbl3-grid-cols
 * variable and the margin utilities are dropped (they would beat the media
 * queries) and the resolved column count is fed back so the desktop sheet
 * bucket always carries it — the column-count override rules only fire
 * when columnsT / columnsM are set and beat the shared tablet cap /
 * hardcoded mobile rules by specificity (team-block pattern). */
$kbl_spec = array(
        array( 'a' => 'columns', 'p' => '--kbl3-grid-cols' ),
        array( 'a' => 'columns', 'p' => 'grid-template-columns', 'raw' => 'repeat(var(--kbl3-grid-cols,2),minmax(0,1fr))', 'sel' => ' .kbl3-pgrid__row', 'only' => 't' ),
        array( 'a' => 'columns', 'p' => 'grid-template-columns', 'raw' => 'repeat(var(--kbl3-grid-cols,1),minmax(0,1fr))', 'sel' => ' .kbl3-pgrid__row', 'only' => 'm' ),
        array( 'a' => 'mt', 'p' => 'margin-top', 'u' => 'px' ),
        array( 'a' => 'mb', 'p' => 'margin-bottom', 'u' => 'px' ),
);
$legacy = kbl4_strip( $a, array( 'columns', 'columnsT', 'columnsM', 'mt', 'mtT', 'mtM', 'mb', 'mbT', 'mbM' ) );
if ( ! empty( $a['uid'] ) ) {
        $a['columns'] = $columns; /* desktop sheet bucket fallback */
}

if ( ! post_type_exists( $post_type ) ) {
        return; // Nothing to render for an unknown post type.
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

/* v3 (no uid) keeps the inline --kbl3-grid-cols on the root — byte-parity
 * with the stored markup; with a uid the variable rides the sheet. */
$kbl_inline = array();
if ( empty( $a['uid'] ) ) {
        $kbl_inline[] = array( '--kbl3-grid-cols', (string) $columns );
}
$kbl_root_style = kbl3_style( array_merge( $kbl_inline, kbl3_spacing_pairs( $legacy ) ) );

$out = '<div class="' . esc_attr( kbl3_cx( 'kbl3-pgrid', kbl4_uid_class( $a ), kbl3_visibility( $a ), kbl3_spacing_class( $legacy ), kbl3_custom_class( $a ) ) ) . '"' . ( '' !== $kbl_root_style ? ' style="' . esc_attr( $kbl_root_style ) . '"' : '' ) . ( ! empty( $a['anchor'] ) ? ' id="' . esc_attr( $a['anchor'] ) . '"' : '' ) . '>';

if ( $query->have_posts() ) {
        $out .= '<div class="kbl3-pgrid__row">';
        foreach ( $query->posts as $kbl_post ) {
                $out .= '<article class="kbl3-pcard">';
                if ( has_post_thumbnail( $kbl_post ) ) {
                        $out .= get_the_post_thumbnail( $kbl_post, 'large', array( 'class' => 'kbl3-pcard__img' ) );
                } else {
                        $out .= '<div class="kbl3-pcard__ph"></div>';
                }
                $out .= '<div class="kbl3-pcard__body">';
                $out .= '<h4 class="kbl3-pcard__title"><a href="' . esc_url( get_permalink( $kbl_post ) ) . '">' . esc_html( get_the_title( $kbl_post ) ) . '</a></h4>';
                if ( $show_excerpt ) {
                        $kbl_excerpt = has_excerpt( $kbl_post ) ? get_the_excerpt( $kbl_post ) : wp_trim_words( wp_strip_all_tags( $kbl_post->post_content ), 18, '…' );
                        $out .= '<p class="kbl3-pcard__text">' . esc_html( $kbl_excerpt ) . '</p>';
                }
                $out .= '</div></article>';
        }
        $out .= '</div>';
}

if ( '' !== $btn_label ) {
        $out .= '<div class="kbl3-btnwrap" style="text-align:center">';
        $kbl_href = '' !== $btn_url ? $btn_url : ( 'product' === $post_type ? '/products/' : ( 'post' === $post_type ? '/blog/' : '/projects/' ) );
        $out .= '<a class="kbl3-btn kbl3-btn--outline kbl3-btn--m" href="' . esc_url( $kbl_href ) . '">' . esc_html( $btn_label ) . '</a>';
        $out .= '</div>';
}

$out .= '</div>';

/* v4: the per-instance <style> (responsive columns / margins + scoped
 * custom CSS) rides as a SIBLING after the root, exactly like the
 * static-block saves. */
$kbl_v4css = kbl4_style_inner( $a, $kbl_spec );
if ( '' !== $kbl_v4css ) {
        $out .= '<style>' . $kbl_v4css . '</style>';
}

wp_reset_postdata();

echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled from escaped fragments above.
