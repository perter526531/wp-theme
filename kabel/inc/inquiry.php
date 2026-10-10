<?php
/**
 * Inquiry system — front-end form, processing, storage, email forwarding.
 *
 * Decisions:
 *  - Built into the theme, zero third-party dependencies.
 *  - Unified consultation form: Name, Email, WhatsApp or Phone, Company,
 *    Country, Project Type (select) and Message — used on the RFQ popup,
 *    the contact page, product pages and the inquiry block.
 *  - The product context travels in a hidden, pre-fillable field
 *    (#kb_product) so any ".kb-rfq-open" element with data-kb-product
 *    can seed it.
 *  - Spam protection: nonce + honeypot + per-IP rate limiting.
 *  - Email forwarding: admin + configurable extra recipients,
 *    Reply-To set to the inquirer so "Reply" reaches the customer.
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field definitions (order matters — rendered in this order).
 *
 * @return array[] key => [label, required, type, options?]
 */
function kabel_inquiry_fields(): array {
	return array(
		'person'       => array( __( 'Name', 'kabel' ), true, 'text' ),
		'email'        => array( __( 'Email', 'kabel' ), true, 'email' ),
		'phone'        => array( __( 'WhatsApp or Phone', 'kabel' ), false, 'text' ),
		'company'      => array( __( 'Company', 'kabel' ), false, 'text' ),
		'country'      => array( __( 'Country', 'kabel' ), false, 'text' ),
		'project_type' => array( __( 'Project Type', 'kabel' ), false, 'select', array(
			''             => __( 'Select a project type', 'kabel' ),
			'home-renovation'    => __( 'Home Renovation', 'kabel' ),
			'engineering-project' => __( 'Engineering Project', 'kabel' ),
			'distributor'  => __( 'Distributor', 'kabel' ),
			'customization' => __( 'Customization', 'kabel' ),
			'other'        => __( 'Other', 'kabel' ),
		) ),
		'message'      => array( __( 'Message', 'kabel' ), true, 'textarea' ),
	);
}

/**
 * The Project Type labels (shared by the form, the email and the admin list).
 *
 * @return array<string,string>
 */
function kabel_inquiry_project_types(): array {
	$types = array();
	foreach ( kabel_inquiry_fields() as $field ) {
		if ( isset( $field[3] ) && is_array( $field[3] ) ) {
			$types = $field[3];
			break;
		}
	}
	return $types;
}

/**
 * Render the unified inquiry form.
 *
 * @param array $args {source: string, product: string, message: string}
 */
function kabel_render_inquiry_form( array $args = array() ): void {
	$source  = $args['source'] ?? ( is_singular() ? (string) get_permalink() : home_url( '/' ) );
	$product = $args['product'] ?? '';
	$message = $args['message'] ?? '';

	// Success state.
	if ( isset( $_GET['inquiry'] ) && 'success' === $_GET['inquiry'] ) {
		printf(
			'<div class="kb-form kb-form--success" role="status"><h3>%s</h3><p>%s</p><p><a class="btn btn--outline-teal" href="%s">%s</a></p></div>',
			esc_html__( 'Thank you — inquiry received.', 'kabel' ),
			esc_html__( 'Our paint consultants will reply within one business day. Meanwhile, feel free to keep exploring our paint series.', 'kabel' ),
			esc_url( home_url( '/products/' ) ),
			esc_html__( 'Explore paint series', 'kabel' )
		);
		return;
	}

	$error = isset( $_GET['inquiry'] ) && 'error' === $_GET['inquiry'];
	?>
	<form class="kb-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="inquiry-form">
		<?php if ( $error ) : ?>
			<div class="kb-form__notice" role="alert">
				<?php esc_html_e( 'Please fill in your name, a valid email and a short message, then try again.', 'kabel' ); ?>
			</div>
		<?php endif; ?>

		<input type="hidden" name="action" value="kabel_inquiry">
		<input type="hidden" name="kb_source" value="<?php echo esc_url( $source ); ?>">
		<?php wp_nonce_field( 'kabel_inquiry', 'kabel_inquiry_nonce' ); ?>

		<p class="kb-hp" aria-hidden="true">
			<label>Leave this field empty<input type="text" name="kb_website" value="" tabindex="-1" autocomplete="off"></label>
		</p>

		<div class="kb-form__grid">
			<?php foreach ( kabel_inquiry_fields() as $key => $field ) :
				list( $label, $required, $type ) = $field;
				$options = $field[3] ?? array();
				$name  = 'kb_' . $key;
				$value = '';
				if ( 'message' === $key && $message ) {
					$value = $message;
				}
				?>
				<p class="kb-form__field kb-form__field--<?php echo esc_attr( $key ); ?><?php echo in_array( $type, array( 'textarea', 'select' ), true ) ? ' kb-form__field--full' : ''; ?>">
					<label for="kb_<?php echo esc_attr( $key ); ?>">
						<?php echo esc_html( $label ); ?>
						<?php if ( $required ) : ?><span class="kb-form__req" aria-hidden="true">*</span><?php endif; ?>
					</label>
					<?php if ( 'textarea' === $type ) : ?>
						<textarea id="kb_<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $name ); ?>" rows="4" <?php echo $required ? 'required' : ''; ?>><?php echo esc_textarea( $value ); ?></textarea>
					<?php elseif ( 'select' === $type ) : ?>
						<select id="kb_<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $name ); ?>">
							<?php foreach ( $options as $opt_value => $opt_label ) : ?>
								<option value="<?php echo esc_attr( $opt_value ); ?>"><?php echo esc_html( $opt_label ); ?></option>
							<?php endforeach; ?>
						</select>
					<?php else : ?>
						<input type="<?php echo esc_attr( $type ); ?>" id="kb_<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" <?php echo $required ? 'required' : ''; ?>>
					<?php endif; ?>
				</p>
			<?php endforeach; ?>
		</div>

		<input type="hidden" name="kb_product" id="kb_product" value="<?php echo esc_attr( $product ); ?>">

		<p class="kb-form__submit">
			<button type="submit" class="btn btn--cta"><?php esc_html_e( 'Send Inquiry', 'kabel' ); ?></button>
			<span class="kb-form__privacy"><?php esc_html_e( 'Your data is used only to respond to this inquiry.', 'kabel' ); ?></span>
		</p>
	</form>
	<?php
}

/**
 * Handle the form submission (admin-post.php endpoint).
 */
function kabel_handle_inquiry(): void {
	$fail = static function (): void {
		wp_safe_redirect( add_query_arg( 'inquiry', 'error', wp_get_referer() ?: home_url( '/' ) ) . '#inquiry-form' );
		exit;
	};
	$done = static function (): void {
		wp_safe_redirect( add_query_arg( 'inquiry', 'success', wp_get_referer() ?: home_url( '/' ) ) . '#inquiry-form' );
		exit;
	};

	// Nonce.
	$nonce = $_POST['kabel_inquiry_nonce'] ?? '';
	if ( ! is_string( $nonce ) || ! wp_verify_nonce( $nonce, 'kabel_inquiry' ) ) {
		$fail();
	}

	// Honeypot: bots filling the hidden field are silently dropped.
	if ( ! empty( $_POST['kb_website'] ) ) {
		$done();
	}

	// Rate limit: max 3 submissions per IP per 60 seconds.
	$ip   = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key  = 'kabel_rl_' . md5( $ip );
	$hits = (int) get_transient( $key );
	if ( $hits >= 3 ) {
		wp_die(
			esc_html__( 'Too many requests. Please wait a minute and try again.', 'kabel' ),
			esc_html__( 'Rate limited', 'kabel' ),
			array( 'response' => 429 )
		);
	}
	set_transient( $key, $hits + 1, 60 );

	// Sanitize + collect.
	$project_type = isset( $_POST['kb_project_type'] ) ? sanitize_key( wp_unslash( $_POST['kb_project_type'] ) ) : '';
	if ( ! array_key_exists( $project_type, kabel_inquiry_project_types() ) ) {
		$project_type = '';
	}

	$data = array(
		'company'      => isset( $_POST['kb_company'] ) ? sanitize_text_field( wp_unslash( $_POST['kb_company'] ) ) : '',
		'person'       => isset( $_POST['kb_person'] ) ? sanitize_text_field( wp_unslash( $_POST['kb_person'] ) ) : '',
		'phone'        => isset( $_POST['kb_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['kb_phone'] ) ) : '',
		'email'        => isset( $_POST['kb_email'] ) ? sanitize_email( wp_unslash( $_POST['kb_email'] ) ) : '',
		'country'      => isset( $_POST['kb_country'] ) ? sanitize_text_field( wp_unslash( $_POST['kb_country'] ) ) : '',
		'project_type' => $project_type,
		'product'      => isset( $_POST['kb_product'] ) ? sanitize_text_field( wp_unslash( $_POST['kb_product'] ) ) : '',
		'message'      => isset( $_POST['kb_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['kb_message'] ) ) : '',
		'source'       => isset( $_POST['kb_source'] ) ? esc_url_raw( wp_unslash( $_POST['kb_source'] ) ) : '',
	);

	// Validate.
	if ( '' === $data['person'] || '' === $data['message'] || ! is_email( $data['email'] ) ) {
		$fail();
	}

	// Store.
	$subject = $data['company']
		? sprintf( '%s — %s', $data['company'], $data['person'] )
		: $data['person'];
	$post_id = wp_insert_post( array(
		'post_type'    => 'kabel_inquiry',
		'post_status'  => 'private',
		'post_title'   => $subject,
		'post_content' => $data['message'],
	), true );

	if ( is_wp_error( $post_id ) ) {
		$fail();
	}

	$meta = array(
		'_kb_inq_company'      => $data['company'],
		'_kb_inq_person'       => $data['person'],
		'_kb_inq_phone'        => $data['phone'],
		'_kb_inq_email'        => $data['email'],
		'_kb_inq_country'      => $data['country'],
		'_kb_inq_product'      => $data['product'],
		'_kb_inq_project_type' => $data['project_type'],
		'_kb_inq_source'       => $data['source'],
		'_kb_inq_ip'           => $ip,
		'_kb_inq_ua'           => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
		'_kb_inq_status'       => 'new',
	);
	foreach ( $meta as $k => $v ) {
		update_post_meta( $post_id, $k, $v );
	}

	// Email forwarding notification (never blocks the user experience).
	kabel_send_inquiry_notification( $post_id, $data );

	$done();
}
add_action( 'admin_post_nopriv_kabel_inquiry', 'kabel_handle_inquiry' );
add_action( 'admin_post_kabel_inquiry', 'kabel_handle_inquiry' );

/**
 * Send the inquiry forwarding notification email.
 *
 * Recipients: site admin + theme-setting forwarding addresses.
 * Reply-To: the inquirer — replying in any mail client reaches the customer.
 *
 * @param int   $post_id Inquiry post ID.
 * @param array $data    Sanitized field data.
 */
function kabel_send_inquiry_notification( int $post_id, array $data ): void {
	$to = array( (string) get_option( 'admin_email' ) );

	$extra = kabel_get_option( 'kabel_inquiry_emails', '' );
	foreach ( explode( ',', (string) $extra ) as $email ) {
		$email = sanitize_email( trim( $email ) );
		if ( is_email( $email ) ) {
			$to[] = $email;
		}
	}
	$to = array_values( array_unique( $to ) );

	$subject = sprintf(
		'[%s] New inquiry #%d — %s',
		wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
		$post_id,
		$data['company'] ? $data['company'] : $data['person']
	);

	$project_types = kabel_inquiry_project_types();
	$rows          = array(
		__( 'Name', 'kabel' )            => $data['person'],
		__( 'Company', 'kabel' )         => $data['company'],
		__( 'WhatsApp or Phone', 'kabel' ) => $data['phone'],
		__( 'Email', 'kabel' )           => $data['email'],
		__( 'Country', 'kabel' )         => $data['country'],
		__( 'Project Type', 'kabel' )    => $project_types[ $data['project_type'] ] ?? '',
		__( 'Product of interest', 'kabel' ) => $data['product'],
	);

	$body  = '<html><body style="font-family:Arial,Helvetica,sans-serif;color:#1C2B33;max-width:560px;margin:0 auto;">';
	$body .= '<h2 style="color:#062A33;font-size:18px;margin:0 0 4px;">' . esc_html__( 'New website inquiry', 'kabel' ) . '</h2>';
	$body .= '<p style="color:#5B6B73;font-size:13px;margin:0 0 16px;">' . esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ) . '</p>';
	$body .= '<table cellpadding="8" cellspacing="0" style="border-collapse:collapse;font-size:14px;width:100%;">';
	foreach ( $rows as $label => $value ) {
		$body .= sprintf(
			'<tr><td style="background:#F6FAFB;border:1px solid #E2ECEE;width:36%%;font-weight:bold;">%s</td><td style="border:1px solid #E2ECEE;">%s</td></tr>',
			esc_html( $label ),
			esc_html( $value )
		);
	}
	$body .= sprintf(
		'<tr><td style="background:#F6FAFB;border:1px solid #E2ECEE;font-weight:bold;">%s</td><td style="border:1px solid #E2ECEE;">%s</td></tr>',
		esc_html__( 'Message', 'kabel' ),
		nl2br( esc_html( $data['message'] ) )
	);
	$body .= '</table>';
	$body .= '<p style="font-size:13px;margin:16px 0 0;">';
	$body .= sprintf(
		'<a href="%s" style="background:#FFB454;color:#062A33;padding:10px 18px;border-radius:99px;text-decoration:none;font-weight:bold;">%s</a>',
		esc_url( admin_url( 'post.php?post=' . $post_id . '&action=edit' ) ),
		esc_html__( 'View in admin', 'kabel' )
	);
	$body .= sprintf( ' &nbsp;<span style="color:#5B6B73;">%s</span>', esc_html__( 'Source page:', 'kabel' ) . ' ' . esc_html( $data['source'] ) );
	$body .= '</p>';
	$body .= '<p style="color:#5B6B73;font-size:12px;margin:16px 0 0;">' . esc_html__( 'Tip: use "Reply" in your mail client — it goes straight to the customer.', 'kabel' ) . '</p>';
	$body .= '</body></html>';

	$headers = array(
		'Content-Type: text/html; charset=UTF-8',
		sprintf( 'Reply-To: %s <%s>', $data['person'], $data['email'] ),
	);

	$sent = wp_mail( $to, $subject, $body, $headers );
	if ( ! $sent ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( sprintf( 'Kabel theme: inquiry notification email failed for inquiry #%d.', $post_id ) );
	}
}
