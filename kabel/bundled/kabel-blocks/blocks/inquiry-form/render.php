<?php
/**
 * kbl/inquiry-form — server render template.
 *
 * Loaded by WordPress for every instance of the block (block.json
 * "render": "file:./render.php"): the block attributes are in $attributes,
 * the saved content in $content (always empty — the editor save is null)
 * and the block instance in $block.
 *
 * With the Kabel theme active the form reuses the theme's inquiry system
 * (kabel_form_inquiry_part()); standalone it renders the compact
 * REST-backed form (twin of assets/js/kabel-form.js, endpoint from
 * inc/leads.php).
 *
 * @package Kabel_Blocks
 */

defined( 'ABSPATH' ) || exit;

$a = ( isset( $attributes ) && is_array( $attributes ) ) ? $attributes : array();

$title  = isset( $a['title'] ) ? (string) $a['title'] : 'CONTACT US';
$sub    = isset( $a['sub'] ) ? (string) $a['sub'] : 'Global Service · Local Response';
$button = isset( $a['buttonLabel'] ) ? (string) $a['buttonLabel'] : 'Send Inquiry';
$email  = isset( $a['email'] ) ? (string) $a['email'] : '';

/* v4 responsive layer (JS twin: RESP_SPEC / STRIP_KEYS in
 * blocks/inquiry-form/edit.js). Once a uid exists the mt / mb triples ride
 * the per-instance <style> tag and the legacy pair helpers get the stripped
 * copy (the inline margin utilities would beat the media queries). Class
 * order mirrors the editor cx(). */
$kbl_spec = array(
        array( 'a' => 'mt', 'p' => 'margin-top', 'u' => 'px' ),
        array( 'a' => 'mb', 'p' => 'margin-bottom', 'u' => 'px' ),
);
$legacy    = kbl4_strip( $a, array( 'mt', 'mtT', 'mtM', 'mb', 'mbT', 'mbM' ) );

$kbl_style = kbl3_style( kbl3_spacing_pairs( $legacy ) );

$out  = '<div class="' . esc_attr( kbl3_cx( 'kbl3-form', kbl4_uid_class( $a ), kbl3_visibility( $a ), kbl3_spacing_class( $legacy ), kbl3_custom_class( $a ) ) ) . '"' . ( '' !== $kbl_style ? ' style="' . esc_attr( $kbl_style ) . '"' : '' ) . ( ! empty( $a['anchor'] ) ? ' id="' . esc_attr( $a['anchor'] ) . '"' : '' ) . '>';

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
        // Standalone: the compact REST-backed form (matches assets/js/kabel-form.js).
        $kbl_endpoint = esc_url_raw( rest_url( 'kabel-blocks/v1/lead' ) );

        $out .= '<form class="kbl-form" method="post" action="' . $kbl_endpoint . '" data-endpoint="' . $kbl_endpoint . '">';
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
}

$out .= '</div>';

/* v4: the per-instance <style> (responsive margins + scoped custom CSS)
 * rides as a SIBLING after the root, exactly like the static-block saves. */
$kbl_v4css = kbl4_style_inner( $a, $kbl_spec );
if ( '' !== $kbl_v4css ) {
        $out .= '<style>' . $kbl_v4css . '</style>';
}

echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled from escaped fragments above.
