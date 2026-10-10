<?php
/**
 * Customizer settings — contact info, CTA text, inquiry email forwarding.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

/**
 * Theme option getter with defaults.
 *
 * @param string $key     Option name.
 * @param mixed  $default Default value.
 * @return mixed
 */
function dazhen_get_option( string $key, $default = '' ) {
        return get_theme_mod( $key, $default );
}

/**
 * Sanitize a comma-separated email list.
 */
function dazhen_sanitize_email_list( string $input ): string {
        $clean = array();
        foreach ( explode( ',', $input ) as $email ) {
                $email = sanitize_email( trim( $email ) );
                if ( is_email( $email ) ) {
                        $clean[] = $email;
                }
        }
        return implode( ', ', array_unique( $clean ) );
}

/**
 * Register customizer controls.
 */
function dazhen_customize_register( WP_Customize_Manager $wp_customize ): void {

        $wp_customize->add_section( 'dazhen_contact', array(
                'title'       => __( 'Contact & Inquiry Emails', 'dazhen' ),
                'priority'    => 35,
                'description' => __( 'Shown in the header/footer and used as inquiry notification recipients.', 'dazhen' ),
        ) );

        $fields = array(
                'dazhen_phone'    => array(
                        'label'   => __( 'Phone number', 'dazhen' ),
                        'default' => '+86 180 2699 3135',
                        'sanitize' => 'sanitize_text_field',
                        'type'    => 'text',
                ),
                'dazhen_email'    => array(
                        'label'   => __( 'Public email', 'dazhen' ),
                        'default' => '2046772003@qq.com',
                        'sanitize' => 'sanitize_email',
                        'type'    => 'email',
                ),
                'dazhen_address'  => array(
                        'label'   => __( 'Address', 'dazhen' ),
                        'default' => __( 'Dayawan, Huizhou City, Guangdong Province, China', 'dazhen' ),
                        'sanitize' => 'sanitize_text_field',
                        'type'    => 'text',
                ),
                'dazhen_working_hours' => array(
                        'label'       => __( 'Working hours / time zone (Contact page promise line)', 'dazhen' ),
                        'default'     => __( 'Time zone: GMT+8 · China Standard Time', 'dazhen' ),
                        'sanitize'    => 'sanitize_text_field',
                        'type'        => 'text',
                        'description' => __( 'Shown as a bullet next to the Contact inquiry form, e.g. “Time zone: GMT+8 · China Standard Time”.', 'dazhen' ),
                ),
                'dazhen_cta_text' => array(
                        'label'   => __( 'Header button text', 'dazhen' ),
                        'default' => __( 'Get a Quote', 'dazhen' ),
                        'sanitize' => 'sanitize_text_field',
                        'type'    => 'text',
                ),
                'dazhen_hero_eyebrow' => array(
                        'label'   => __( 'Hero eyebrow (small line above the homepage headline)', 'dazhen' ),
                        'default' => 'HIGH-END ICE & HEAT TECHNOLOGY SPECIALIST',
                        'sanitize' => 'sanitize_text_field',
                        'type'    => 'text',
                ),
                'dazhen_inquiry_emails' => array(
                        'label'       => __( 'Inquiry forwarding emails', 'dazhen' ),
                        'default'     => '',
                        'sanitize'    => 'dazhen_sanitize_email_list',
                        'type'        => 'text',
                        'description' => __( 'Comma-separated. Every inquiry is emailed here (plus the site admin). Reply-To is set to the customer.', 'dazhen' ),
                ),
        );

        foreach ( $fields as $key => $field ) {
                $wp_customize->add_setting( $key, array(
                        'default'           => $field['default'],
                        'sanitize_callback' => $field['sanitize'],
                ) );
                $wp_customize->add_control( $key, array(
                        'label'       => $field['label'],
                        'description' => $field['description'] ?? '',
                        'section'     => 'dazhen_contact',
                        'type'        => $field['type'],
                ) );
        }
}
add_action( 'customize_register', 'dazhen_customize_register' );
