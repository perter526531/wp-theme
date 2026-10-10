<?php
/**
 * Meta field registration (sanitization + auth callbacks).
 *
 * Stored with the "_dz_" prefix so they are hidden from the default
 * custom-fields UI — the theme's own metabox UI (metabox-ui.php) is the
 * only editing surface. Storage layout stays ACF-compatible for a
 * smooth future migration if ever needed.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register all meta fields with proper sanitize/auth callbacks.
 */
function dazhen_register_meta_fields() {

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

        /* ---------- Product ---------- */
        $product_fields = array(
                '_dz_product_model'   => $text_sanitizer,   // e.g. 18E-2
                '_dz_product_tagline' => $text_sanitizer,   // One-line positioning statement.
                '_dz_product_badge'   => $key_sanitizer,    // new | hot | '' (NEW/HOT corner badge).
                '_dz_product_bullets' => $textarea_san,     // One selling point per line.
                '_dz_product_specs'   => 'dazhen_sanitize_specs',  // Repeater: group/name/value/unit rows.
                '_dz_product_gallery' => 'dazhen_sanitize_id_list', // Attachment IDs, comma separated.
        );
        foreach ( $product_fields as $key => $sanitizer ) {
                register_post_meta( 'product', $key, array_merge( $auth, array( 'sanitize_callback' => $sanitizer ) ) );
        }

        /* ---------- Case study ---------- */
        $case_fields = array(
                '_dz_case_country'     => $text_sanitizer,  // e.g. United Kingdom
                '_dz_case_flag'        => $text_sanitizer,  // Emoji flag, e.g. 🇬🇧
                '_dz_case_client_type' => $key_sanitizer,   // distributor | hotel | education | medical | office | ecommerce
                '_dz_case_order'       => $text_sanitizer,  // e.g. 1,500 units
                '_dz_case_need'        => $textarea_san,    // Requirement
                '_dz_case_challenge'   => $textarea_san,    // Challenge
                '_dz_case_feedback'    => $textarea_san,    // Client feedback quote
                '_dz_case_products'    => 'dazhen_sanitize_id_list', // Related product IDs.
        );
        foreach ( $case_fields as $key => $sanitizer ) {
                register_post_meta( 'case_study', $key, array_merge( $auth, array( 'sanitize_callback' => $sanitizer ) ) );
        }

        /* ---------- Solution (scenario landing page) ---------- */
        $solution_fields = array(
                '_dz_sol_icon'     => $key_sanitizer,  // Icon key: home | office | edu | hotel
                '_dz_sol_pains'    => $textarea_san,   // Pain points, one per line.
                '_dz_sol_summary'  => $textarea_san,   // Scenario intro shown in cards.
                '_dz_sol_products' => 'dazhen_sanitize_id_list', // Recommended product IDs.
        );
        foreach ( $solution_fields as $key => $sanitizer ) {
                register_post_meta( 'solution', $key, array_merge( $auth, array( 'sanitize_callback' => $sanitizer ) ) );
        }

        /* ---------- Inquiry ---------- */
        $inquiry_fields = array(
                '_dz_inq_company'  => $text_sanitizer,
                '_dz_inq_person'   => $text_sanitizer,
                '_dz_inq_phone'    => $text_sanitizer,
                '_dz_inq_email'    => $email_sanitizer,
                '_dz_inq_country'  => $text_sanitizer,
                '_dz_inq_product'  => $text_sanitizer,
                '_dz_inq_source'   => $text_sanitizer,  // URL of the page the form was submitted from.
                '_dz_inq_ip'       => $text_sanitizer,
                '_dz_inq_ua'       => $text_sanitizer,
                '_dz_inq_status'   => $key_sanitizer,   // new | following | won
        );
        foreach ( $inquiry_fields as $key => $sanitizer ) {
                register_post_meta( 'dazhen_inquiry', $key, array_merge( $auth, array( 'sanitize_callback' => $sanitizer ) ) );
        }
}
add_action( 'init', 'dazhen_register_meta_fields' );

/**
 * Sanitize spec repeater rows (group / name / value / unit).
 *
 * @param mixed $value Raw input.
 * @return array[] Clean rows.
 */
function dazhen_sanitize_specs( $value ): array {
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
function dazhen_sanitize_id_list( $value ): string {
        if ( is_array( $value ) ) {
                $ids = array_map( 'absint', $value );
        } else {
                $ids = array_map( 'absint', explode( ',', (string) $value ) );
        }
        $ids = array_filter( array_unique( $ids ) );
        return implode( ',', $ids );
}
