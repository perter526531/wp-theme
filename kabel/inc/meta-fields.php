<?php
/**
 * Meta field registration (sanitization + auth callbacks).
 *
 * Stored with the "_kb_" prefix so they are hidden from the default
 * custom-fields UI — the theme's own metabox UI (metabox-ui.php) is the
 * only editing surface. Storage layout stays ACF-compatible for a
 * smooth future migration if ever needed.
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register all meta fields with proper sanitize/auth callbacks.
 */
function kabel_register_meta_fields() {

	$bool_sanitizer  = static fn( $value ): bool => (bool) $value;
	$text_sanitizer  = static fn( $value ): string => sanitize_text_field( (string) $value );
	$textarea_san    = static fn( $value ): string => sanitize_textarea_field( (string) $value );
	$email_sanitizer = static fn( $value ): string => sanitize_email( (string) $value );
	$key_sanitizer   = static fn( $value ): string => preg_replace( '/[^a-z0-9_-]/', '', strtolower( (string) $value ) );

	$auth = array(
		'auth_callback' => static fn(): bool => current_user_can( 'edit_posts' ),
		'show_in_rest'  => false,
		'single'        => true,
		'type'          => 'string',
	);

	/* ---------- Paint series (product) ---------- */
	$product_fields = array(
		'_kb_product_model'   => $text_sanitizer,        // Optional display name (defaults to the post title).
		'_kb_product_tagline' => $text_sanitizer,         // One-line positioning statement.
		'_kb_product_badge'   => $key_sanitizer,          // new | hot | '' (NEW/HOT corner badge).
		'_kb_product_bullets' => $textarea_san,           // One selling point per line.
		'_kb_product_specs'   => 'kabel_sanitize_specs',  // Repeater: group/name/value/unit rows.
		'_kb_product_gallery' => 'kabel_sanitize_id_list', // Attachment IDs, comma separated.
	);
	foreach ( $product_fields as $key => $sanitizer ) {
		register_post_meta( 'product', $key, array_merge( $auth, array( 'sanitize_callback' => $sanitizer ) ) );
	}

	/* ---------- Project reference ---------- */
	$project_fields = array(
		'_kb_project_location' => $text_sanitizer,  // e.g. Singapore
		'_kb_project_summary'  => $textarea_san,    // One-line intro shown under the title.
	);
	foreach ( $project_fields as $key => $sanitizer ) {
		register_post_meta( 'projects', $key, array_merge( $auth, array( 'sanitize_callback' => $sanitizer ) ) );
	}

	/* ---------- Inquiry ---------- */
	$inquiry_fields = array(
		'_kb_inq_company'      => $text_sanitizer,
		'_kb_inq_person'       => $text_sanitizer,
		'_kb_inq_phone'        => $text_sanitizer,
		'_kb_inq_email'        => $email_sanitizer,
		'_kb_inq_country'      => $text_sanitizer,
		'_kb_inq_product'      => $text_sanitizer,  // Pre-filled product / context (e.g. "Velvet Texture").
		'_kb_inq_project_type' => $key_sanitizer,   // home-renovation | engineering-project | distributor | customization | other | ''
		'_kb_inq_source'       => $text_sanitizer,  // URL of the page the form was submitted from.
		'_kb_inq_ip'           => $text_sanitizer,
		'_kb_inq_ua'           => $text_sanitizer,
		'_kb_inq_status'       => $key_sanitizer,   // new | following | won
	);
	foreach ( $inquiry_fields as $key => $sanitizer ) {
		register_post_meta( 'kabel_inquiry', $key, array_merge( $auth, array( 'sanitize_callback' => $sanitizer ) ) );
	}
}
add_action( 'init', 'kabel_register_meta_fields' );

/**
 * Sanitize spec repeater rows (group / name / value / unit).
 *
 * @param mixed $value Raw input.
 * @return array[] Clean rows.
 */
function kabel_sanitize_specs( $value ): array {
	if ( ! is_array( $value ) ) {
		return array();
	}
	$clean = array();
	foreach ( $value as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$group = sanitize_text_field( (string) ( $row['group'] ?? '' ) );
		$name  = sanitize_text_field( (string) ( $row['name'] ?? '' ) );
		$value = sanitize_text_field( (string) ( $row['value'] ?? '' ) );
		$unit  = sanitize_text_field( (string) ( $row['unit'] ?? '' ) );
		if ( '' === $name && '' === $value ) {
			continue;
		}
		$clean[] = array(
			'group' => $group,
			'name'  => $name,
			'value' => $value,
			'unit'  => $unit,
		);
	}
	return $clean;
}

/**
 * Sanitize a comma-separated list of positive integers (IDs).
 *
 * @param mixed $value Raw input.
 * @return string Comma-separated IDs.
 */
function kabel_sanitize_id_list( $value ): string {
	if ( is_array( $value ) ) {
		$ids = array_map( 'absint', $value );
	} else {
		$ids = array_map( 'absint', explode( ',', (string) $value ) );
	}
	$ids = array_filter( array_unique( $ids ) );
	return implode( ',', $ids );
}
