<?php
/**
 * Standalone inquiry fallback — only loaded when the Kabel theme is absent.
 *
 * The theme ships the full 7-field inquiry system (CPT inbox + email). This
 * compact module gives the plugin its own honest minimum on any other site:
 * a lead form block, a REST endpoint, and a simple Leads inbox.
 *
 * @package Kabel_Blocks
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the lead CPT + its admin menu.
 */
function kbl_leads_boot(): void {
	register_post_type( 'kbl_lead', array(
		'labels'        => array(
			'name'          => __( 'Leads', 'kabel-blocks' ),
			'singular_name' => __( 'Lead', 'kabel-blocks' ),
			'menu_name'     => __( 'Leads · 询盘', 'kabel-blocks' ),
			'not_found'     => __( 'No leads yet — they appear here the moment someone submits your inquiry form.', 'kabel-blocks' ),
		),
		'public'        => false,
		'show_ui'       => true,
		'menu_icon'     => 'dashicons-email-alt',
		'menu_position' => 26,
		'supports'      => array( 'title' ),
		'capability_type' => 'post',
	) );

	add_action( 'add_meta_boxes', static function (): void {
		add_meta_box(
			'kbl_lead_fields',
			__( 'Lead details', 'kabel-blocks' ),
			static function ( WP_Post $post ): void {
				$keys = kbl_lead_keys();
				echo '<table class="form-table"><tbody>';
				foreach ( $keys as $key => $label ) {
					$value = (string) get_post_meta( $post->ID, '_kbl_' . $key, true );
					echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td>' . esc_html( $value ) . '</td></tr>';
				}
				echo '</tbody></table>';
			},
			'kbl_lead',
			'normal',
			'high'
		);
	} );
}
add_action( 'init', 'kbl_leads_boot', 5 );

/**
 * The lead field keys (order matters).
 *
 * @return array<string,string>
 */
function kbl_lead_keys(): array {
	return array(
		'person'  => __( 'Contact person', 'kabel-blocks' ),
		'email'   => __( 'Email', 'kabel-blocks' ),
		'company' => __( 'Company', 'kabel-blocks' ),
		'phone'   => __( 'Phone / WhatsApp', 'kabel-blocks' ),
		'country' => __( 'Country', 'kabel-blocks' ),
		'product' => __( 'Product interest', 'kabel-blocks' ),
		'message' => __( 'Message', 'kabel-blocks' ),
	);
}

/**
 * Register the REST endpoint.
 */
function kbl_leads_rest(): void {
	register_rest_route( 'kabel-blocks/v1', '/lead', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true', // Public form; honeypot below.
		'args'                => array(
			'person'  => array( 'type' => 'string', 'required' => true ),
			'email'   => array( 'type' => 'string', 'required' => true ),
			'company' => array( 'type' => 'string' ),
			'phone'   => array( 'type' => 'string' ),
			'country' => array( 'type' => 'string' ),
			'product' => array( 'type' => 'string' ),
			'message' => array( 'type' => 'string' ),
		),
		'callback'            => 'kbl_leads_handle',
	) );
}
add_action( 'rest_api_init', 'kbl_leads_rest' );

/**
 * Limit the public endpoint to three accepted submissions per IP per minute.
 *
 * The rate-limit key is a one-way hash, so the transient does not expose a
 * visitor address in the options table.
 */
function kbl_leads_allow_submission( string $ip ): bool {
	$key  = 'kbl_lead_rl_' . md5( '' !== $ip ? $ip : 'unknown' );
	$hits = (int) get_transient( $key );

	if ( $hits >= 3 ) {
		return false;
	}

	set_transient( $key, $hits + 1, MINUTE_IN_SECONDS );
	return true;
}

/**
 * Handle a lead submission.
 */
function kbl_leads_handle( WP_REST_Request $request ) {
	// Honeypot — bots fill every field they see.
	if ( '' !== trim( (string) $request->get_param( 'website' ) ) ) {
		return array( 'ok' => true ); // Pretend success, store nothing.
	}

	$data = array(
		'person'  => sanitize_text_field( (string) $request->get_param( 'person' ) ),
		'email'   => sanitize_email( (string) $request->get_param( 'email' ) ),
		'company' => sanitize_text_field( (string) $request->get_param( 'company' ) ),
		'phone'   => sanitize_text_field( (string) $request->get_param( 'phone' ) ),
		'country' => sanitize_text_field( (string) $request->get_param( 'country' ) ),
		'product' => sanitize_text_field( (string) $request->get_param( 'product' ) ),
		'message' => sanitize_textarea_field( (string) $request->get_param( 'message' ) ),
	);

	if ( '' === $data['person'] || '' === $data['message'] || ! is_email( $data['email'] ) ) {
		return new WP_Error( 'kbl_invalid', __( 'Please fill in your name, a valid email and a message.', 'kabel-blocks' ), array( 'status' => 400 ) );
	}

	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	if ( ! kbl_leads_allow_submission( $ip ) ) {
		return new WP_Error( 'kbl_rate_limited', __( 'Too many requests. Please wait a minute and try again.', 'kabel-blocks' ), array( 'status' => 429 ) );
	}

	$post_id = wp_insert_post( array(
		'post_type'   => 'kbl_lead',
		'post_status' => 'private',
		'post_title'  => $data['person'] . ( '' !== $data['company'] ? ' — ' . $data['company'] : '' ),
	), true );

	if ( is_wp_error( $post_id ) ) {
		return new WP_Error( 'kbl_store', __( 'Could not store the lead. Please try again.', 'kabel-blocks' ), array( 'status' => 500 ) );
	}

	foreach ( $data as $key => $value ) {
		update_post_meta( $post_id, '_kbl_' . $key, $value );
	}
	update_post_meta( $post_id, '_kbl_ip', $ip );

	// Email the admin (best effort — sites without SMTP simply log).
	$to      = get_option( 'admin_email' );
	$subject = sprintf( '[%s] New lead: %s', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $data['person'] );
	$body    = '';
	foreach ( kbl_lead_keys() as $key => $label ) {
		$body .= $label . ': ' . $data[ $key ] . "\n";
	}
	$body .= 'IP: ' . $ip . "\n";
	wp_mail( $to, $subject, $body, array( 'Reply-To: ' . $data['person'] . ' <' . $data['email'] . '>' ) );

	return array( 'ok' => true );
}

/**
 * The compact standalone form (used by the inquiry block without the theme).
 */
function kbl_leads_form( string $product = '', string $message = '' ): string {
	$id = wp_unique_id( 'kbl-form-' );

	$html  = '<form class="kbl-form" id="' . esc_attr( $id ) . '" novalidate>';
	$html .= '<div class="kbl-form__grid">';
	$html .= '<p class="kbl-form__row"><label for="' . esc_attr( $id . '-person' ) . '">' . esc_html__( 'Your name *', 'kabel-blocks' ) . '</label>';
	$html .= '<input id="' . esc_attr( $id . '-person' ) . '" name="person" type="text" required autocomplete="name" /></p>';
	$html .= '<p class="kbl-form__row"><label for="' . esc_attr( $id . '-email' ) . '">' . esc_html__( 'Email *', 'kabel-blocks' ) . '</label>';
	$html .= '<input id="' . esc_attr( $id . '-email' ) . '" name="email" type="email" required autocomplete="email" /></p>';
	$html .= '<p class="kbl-form__row"><label for="' . esc_attr( $id . '-company' ) . '">' . esc_html__( 'Company', 'kabel-blocks' ) . '</label>';
	$html .= '<input id="' . esc_attr( $id . '-company' ) . '" name="company" type="text" autocomplete="organization" /></p>';
	$html .= '<p class="kbl-form__row"><label for="' . esc_attr( $id . '-phone' ) . '">' . esc_html__( 'Phone / WhatsApp', 'kabel-blocks' ) . '</label>';
	$html .= '<input id="' . esc_attr( $id . '-phone' ) . '" name="phone" type="text" autocomplete="tel" /></p>';
	$html .= '</div>';
	$html .= '<p class="kbl-form__row"><label for="' . esc_attr( $id . '-message' ) . '">' . esc_html__( 'What do you need? *', 'kabel-blocks' ) . '</label>';
	$html .= '<textarea id="' . esc_attr( $id . '-message' ) . '" name="message" rows="5" required>' . esc_textarea( $message ) . '</textarea></p>';

	// Product interest (read-only pre-fill line).
	$html .= '<input type="hidden" name="product" value="' . esc_attr( $product ) . '" />';

	// Honeypot.
	$html .= '<p class="kbl-hp" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off" /></label></p>';

	$html .= '<p class="kbl-form__actions">';
	$html .= '<button class="kbl-btn kbl-btn--cta" type="submit">' . esc_html__( 'Send inquiry', 'kabel-blocks' ) . '</button>';
	$html .= '<span class="kbl-form__note" data-kbl-form-note aria-live="polite"></span>';
	$html .= '</p>';
	$html .= '</form>';

	return $html;
}
