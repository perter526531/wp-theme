<?php
/**
 * Inquiry system — front-end form, processing, storage, email forwarding.
 *
 * Confirmed decisions (Phase 4):
 *  - Built into the theme, zero third-party dependencies.
 *  - Unified 7 fields, used on Services / Contact / product pages.
 *  - Spam protection: nonce + honeypot + per-IP rate limiting.
 *  - Email forwarding: admin + configurable extra recipients,
 *    Reply-To set to the inquirer so "Reply" reaches the customer.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field definitions (order matters — rendered in this order).
 *
 * @return array[] key => [label, required, type]
 */
function dazhen_inquiry_fields(): array {
        return array(
                'company' => array( __( 'Company name', 'dazhen' ), true, 'text' ),
                'person'  => array( __( 'Contact person', 'dazhen' ), true, 'text' ),
                'phone'   => array( __( 'Phone / WhatsApp', 'dazhen' ), false, 'text' ),
                'email'   => array( __( 'Email', 'dazhen' ), true, 'email' ),
                'country' => array( __( 'Country / Region', 'dazhen' ), false, 'text' ),
                'product' => array( __( 'Product of interest', 'dazhen' ), false, 'text' ),
                'message' => array( __( 'Message', 'dazhen' ), true, 'textarea' ),
        );
}

/**
 * Render the unified inquiry form.
 *
 * @param array $args {source: string, product: string, heading: string}
 */
function dazhen_render_inquiry_form( array $args = array() ): void {
        $source  = $args['source'] ?? ( is_singular() ? (string) get_permalink() : home_url( '/' ) );
        $product = $args['product'] ?? '';
        $message = $args['message'] ?? '';

        // Success state.
        if ( isset( $_GET['inquiry'] ) && 'success' === $_GET['inquiry'] ) {
                printf(
                        '<div class="dz-form dz-form--success" role="status"><h3>%s</h3><p>%s</p><p><a class="btn btn--outline-teal" href="%s">%s</a></p></div>',
                        esc_html__( 'Thank you — inquiry received.', 'dazhen' ),
                        esc_html__( 'Our sales team will reply within one business day. Meanwhile, feel free to keep browsing our products.', 'dazhen' ),
                        esc_url( home_url( '/products/' ) ),
                        esc_html__( 'Browse products', 'dazhen' )
                );
                return;
        }

        $error = isset( $_GET['inquiry'] ) && 'error' === $_GET['inquiry'];
        ?>
        <form class="dz-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="inquiry-form">
                <?php if ( $error ) : ?>
                        <div class="dz-form__notice" role="alert">
                                <?php esc_html_e( 'Please fill in all required fields (company, contact person, valid email, message) and try again.', 'dazhen' ); ?>
                        </div>
                <?php endif; ?>

                <input type="hidden" name="action" value="dazhen_inquiry">
                <input type="hidden" name="dz_source" value="<?php echo esc_url( $source ); ?>">
                <?php wp_nonce_field( 'dazhen_inquiry', 'dazhen_inquiry_nonce' ); ?>

                <p class="dz-hp" aria-hidden="true">
                        <label>Leave this field empty<input type="text" name="dz_website" value="" tabindex="-1" autocomplete="off"></label>
                </p>

                <div class="dz-form__grid">
                        <?php foreach ( dazhen_inquiry_fields() as $key => $field ) :
                                list( $label, $required, $type ) = $field;
                                $name  = 'dz_' . $key;
                                $value = '';
                                if ( 'product' === $key && $product ) {
                                        $value = $product;
                                }
                                if ( 'message' === $key && $message ) {
                                        $value = $message;
                                }
                                ?>
                                <p class="dz-form__field dz-form__field--<?php echo esc_attr( $key ); ?><?php echo 'message' === $key ? ' dz-form__field--full' : ''; ?>">
                                        <label for="dz_<?php echo esc_attr( $key ); ?>">
                                                <?php echo esc_html( $label ); ?>
                                                <?php if ( $required ) : ?><span class="dz-form__req" aria-hidden="true">*</span><?php endif; ?>
                                        </label>
                                        <?php if ( 'textarea' === $type ) : ?>
                                                <textarea id="dz_<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $name ); ?>" rows="5" <?php echo $required ? 'required' : ''; ?>><?php echo esc_textarea( $value ); ?></textarea>
                                        <?php else : ?>
                                                <input type="<?php echo esc_attr( $type ); ?>" id="dz_<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" <?php echo $required ? 'required' : ''; ?>>
                                        <?php endif; ?>
                                </p>
                        <?php endforeach; ?>
                </div>

                <p class="dz-form__submit">
                        <button type="submit" class="btn btn--cta"><?php esc_html_e( 'Send Inquiry', 'dazhen' ); ?></button>
                        <span class="dz-form__privacy"><?php esc_html_e( 'Your data is used only to respond to this inquiry.', 'dazhen' ); ?></span>
                </p>
        </form>
        <?php
}

/**
 * Handle the form submission (admin-post.php endpoint).
 */
function dazhen_handle_inquiry(): void {
        $fail = static function (): void {
                wp_safe_redirect( add_query_arg( 'inquiry', 'error', wp_get_referer() ?: home_url( '/' ) ) . '#inquiry-form' );
                exit;
        };
        $done = static function (): void {
                wp_safe_redirect( add_query_arg( 'inquiry', 'success', wp_get_referer() ?: home_url( '/' ) ) . '#inquiry-form' );
                exit;
        };

        // Nonce.
        $nonce = $_POST['dazhen_inquiry_nonce'] ?? '';
        if ( ! is_string( $nonce ) || ! wp_verify_nonce( $nonce, 'dazhen_inquiry' ) ) {
                $fail();
        }

        // Honeypot: bots filling the hidden field are silently dropped.
        if ( ! empty( $_POST['dz_website'] ) ) {
                $done();
        }

        // Rate limit: max 3 submissions per IP per 60 seconds.
        $ip   = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
        $key  = 'dazhen_rl_' . md5( $ip );
        $hits = (int) get_transient( $key );
        if ( $hits >= 3 ) {
                wp_die(
                        esc_html__( 'Too many requests. Please wait a minute and try again.', 'dazhen' ),
                        esc_html__( 'Rate limited', 'dazhen' ),
                        array( 'response' => 429 )
                );
        }
        set_transient( $key, $hits + 1, 60 );

        // Sanitize + collect.
        $data = array(
                'company' => isset( $_POST['dz_company'] ) ? sanitize_text_field( wp_unslash( $_POST['dz_company'] ) ) : '',
                'person'  => isset( $_POST['dz_person'] ) ? sanitize_text_field( wp_unslash( $_POST['dz_person'] ) ) : '',
                'phone'   => isset( $_POST['dz_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['dz_phone'] ) ) : '',
                'email'   => isset( $_POST['dz_email'] ) ? sanitize_email( wp_unslash( $_POST['dz_email'] ) ) : '',
                'country' => isset( $_POST['dz_country'] ) ? sanitize_text_field( wp_unslash( $_POST['dz_country'] ) ) : '',
                'product' => isset( $_POST['dz_product'] ) ? sanitize_text_field( wp_unslash( $_POST['dz_product'] ) ) : '',
                'message' => isset( $_POST['dz_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['dz_message'] ) ) : '',
                'source'  => isset( $_POST['dz_source'] ) ? esc_url_raw( wp_unslash( $_POST['dz_source'] ) ) : '',
        );

        // Validate.
        if ( '' === $data['company'] || '' === $data['person'] || '' === $data['message'] || ! is_email( $data['email'] ) ) {
                $fail();
        }

        // Store.
        $post_id = wp_insert_post( array(
                'post_type'    => 'dazhen_inquiry',
                'post_status'  => 'private',
                'post_title'   => sprintf( '%s — %s', $data['company'], $data['person'] ),
                'post_content' => $data['message'],
        ), true );

        if ( is_wp_error( $post_id ) ) {
                $fail();
        }

        $meta = array(
                '_dz_inq_company' => $data['company'],
                '_dz_inq_person'  => $data['person'],
                '_dz_inq_phone'   => $data['phone'],
                '_dz_inq_email'   => $data['email'],
                '_dz_inq_country' => $data['country'],
                '_dz_inq_product' => $data['product'],
                '_dz_inq_source'  => $data['source'],
                '_dz_inq_ip'      => $ip,
                '_dz_inq_ua'      => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
                '_dz_inq_status'  => 'new',
        );
        foreach ( $meta as $k => $v ) {
                update_post_meta( $post_id, $k, $v );
        }

        // Email forwarding notification (never blocks the user experience).
        dazhen_send_inquiry_notification( $post_id, $data );

        $done();
}
add_action( 'admin_post_nopriv_dazhen_inquiry', 'dazhen_handle_inquiry' );
add_action( 'admin_post_dazhen_inquiry', 'dazhen_handle_inquiry' );

/**
 * Send the inquiry forwarding notification email.
 *
 * Recipients: site admin + theme-setting forwarding addresses.
 * Reply-To: the inquirer — replying in any mail client reaches the customer.
 *
 * @param int   $post_id Inquiry post ID.
 * @param array $data    Sanitized field data.
 */
function dazhen_send_inquiry_notification( int $post_id, array $data ): void {
        $to = array( (string) get_option( 'admin_email' ) );

        $extra = dazhen_get_option( 'dazhen_inquiry_emails', '' );
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
                $data['company']
        );

        $rows = array(
                __( 'Company', 'dazhen' )      => $data['company'],
                __( 'Contact person', 'dazhen' ) => $data['person'],
                __( 'Phone / WhatsApp', 'dazhen' ) => $data['phone'],
                __( 'Email', 'dazhen' )        => $data['email'],
                __( 'Country / Region', 'dazhen' ) => $data['country'],
                __( 'Product of interest', 'dazhen' ) => $data['product'],
        );

        $body  = '<html><body style="font-family:Arial,Helvetica,sans-serif;color:#1C2B33;max-width:560px;margin:0 auto;">';
        $body .= '<h2 style="color:#062A33;font-size:18px;margin:0 0 4px;">' . esc_html__( 'New website inquiry', 'dazhen' ) . '</h2>';
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
                esc_html__( 'Message', 'dazhen' ),
                nl2br( esc_html( $data['message'] ) )
        );
        $body .= '</table>';
        $body .= '<p style="font-size:13px;margin:16px 0 0;">';
        $body .= sprintf(
                '<a href="%s" style="background:#FFB454;color:#062A33;padding:10px 18px;border-radius:99px;text-decoration:none;font-weight:bold;">%s</a>',
                esc_url( admin_url( 'post.php?post=' . $post_id . '&action=edit' ) ),
                esc_html__( 'View in admin', 'dazhen' )
        );
        $body .= sprintf( ' &nbsp;<span style="color:#5B6B73;">%s</span>', esc_html__( 'Source page:', 'dazhen' ) . ' ' . esc_html( $data['source'] ) );
        $body .= '</p>';
        $body .= '<p style="color:#5B6B73;font-size:12px;margin:16px 0 0;">' . esc_html__( 'Tip: use "Reply" in your mail client — it goes straight to the customer.', 'dazhen' ) . '</p>';
        $body .= '</body></html>';

        $headers = array(
                'Content-Type: text/html; charset=UTF-8',
                sprintf( 'Reply-To: %s <%s>', $data['person'], $data['email'] ),
        );

        $sent = wp_mail( $to, $subject, $body, $headers );
        if ( ! $sent ) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log( sprintf( 'Dazhen theme: inquiry notification email failed for inquiry #%d.', $post_id ) );
        }
}
