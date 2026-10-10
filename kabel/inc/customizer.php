<?php
/**
 * Customizer settings — contact info, CTA text, inquiry email forwarding.
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Theme option getter with defaults.
 *
 * @param string $key     Option name.
 * @param mixed  $default Default value.
 * @return mixed
 */
function kabel_get_option( string $key, $default = '' ) {
        return get_theme_mod( $key, $default );
}

/**
 * Sanitize a comma-separated email list.
 */
function kabel_sanitize_email_list( string $input ): string {
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
function kabel_customize_register( WP_Customize_Manager $wp_customize ): void {

        $wp_customize->add_section( 'kabel_contact', array(
                'title'       => __( 'Contact & Inquiry Emails', 'kabel' ),
                'priority'    => 35,
                'description' => __( 'Shown in the header/footer and used as inquiry notification recipients.', 'kabel' ),
        ) );

        $fields = array(
                'kabel_phone'    => array(
                        'label'   => __( 'Phone number', 'kabel' ),
                        'default' => '+86 15989993891',
                        'sanitize' => 'sanitize_text_field',
                        'type'    => 'text',
                ),
                'kabel_email'    => array(
                        'label'   => __( 'Public email', 'kabel' ),
                        'default' => 'kabelpaints@gmail.com',
                        'sanitize' => 'sanitize_email',
                        'type'    => 'email',
                ),
                'kabel_address'  => array(
                        'label'   => __( 'Address', 'kabel' ),
                        'default' => __( 'Kabel Headquarters: Druento, Torino, Italy · Kabel Branch: Foshan, Guangdong, China', 'kabel' ),
                        'sanitize' => 'sanitize_text_field',
                        'type'    => 'text',
                ),
                'kabel_working_hours' => array(
                        'label'       => __( 'Working hours / time zone (Contact page promise line)', 'kabel' ),
                        'default'     => __( 'Time zone: GMT+8 · China Standard Time', 'kabel' ),
                        'sanitize'    => 'sanitize_text_field',
                        'type'        => 'text',
                        'description' => __( 'Shown as a bullet next to the Contact inquiry form, e.g. “Time zone: GMT+8 · China Standard Time”.', 'kabel' ),
                ),
                'kabel_cta_text' => array(
                        'label'   => __( 'Header button text', 'kabel' ),
                        'default' => __( 'Consult Now', 'kabel' ),
                        'sanitize' => 'sanitize_text_field',
                        'type'    => 'text',
                ),
                'kabel_hero_eyebrow' => array(
                        'label'   => __( 'Hero eyebrow (small line above the homepage headline)', 'kabel' ),
                        'default' => 'GLOBAL HIGH-END DECORATIVE PAINT BRAND',
                        'sanitize' => 'sanitize_text_field',
                        'type'    => 'text',
                ),
                'kabel_inquiry_emails' => array(
                        'label'       => __( 'Inquiry forwarding emails', 'kabel' ),
                        'default'     => '',
                        'sanitize'    => 'kabel_sanitize_email_list',
                        'type'        => 'text',
                        'description' => __( 'Comma-separated. Every inquiry is emailed here (plus the site admin). Reply-To is set to the customer.', 'kabel' ),
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
                        'section'     => 'kabel_contact',
                        'type'        => $field['type'],
                ) );
        }

        /* Brand accent colors — Blocksy-style Customizer color control.
         * Recolors every bronze accent (eyebrows, icons, dividers, hover
         * highlights, focus rings) without touching a single block. */
        $wp_customize->add_section( 'kabel_brand', array(
                'title'       => __( 'Brand Accent Colors', 'kabel' ),
                'priority'    => 30,
                'description' => __( 'Recolors the bronze accents site-wide — eyebrows, icons, buttons, hover highlights and focus rings. Pick "Default" to keep the antique bronze.', 'kabel' ),
        ) );

        $accents = array(
                'kabel_accent'        => array(
                        'label'   => __( 'Accent color', 'kabel' ),
                        'default' => '#B39A74',
                ),
                'kabel_accent_bright' => array(
                        'label'       => __( 'Accent bright (hover)', 'kabel' ),
                        'default'     => '#D3BC97',
                        'description' => __( 'Lighter shade used on hover states and highlights.', 'kabel' ),
                ),
        );

        foreach ( $accents as $key => $accent ) {
                $wp_customize->add_setting( $key, array(
                        'default'           => $accent['default'],
                        'sanitize_callback' => 'sanitize_hex_color',
                ) );
                $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $key, array(
                        'label'       => $accent['label'],
                        'description' => $accent['description'] ?? '',
                        'section'     => 'kabel_brand',
                ) ) );
        }
}
add_action( 'customize_register', 'kabel_customize_register' );

/**
 * Print the accent overrides when the admin picked a non-default color.
 *
 * Every accent slot in main.css mirrors the bronze, so one pick recolors
 * the whole site. Soft tints are derived with color-mix.
 */
function kabel_accent_css(): void {
        $accent  = kabel_get_option( 'kabel_accent', '#B39A74' );
        $bright  = kabel_get_option( 'kabel_accent_bright', '#D3BC97' );
        $accent  = sanitize_hex_color( $accent );
        $bright  = sanitize_hex_color( $bright );
        if ( ! $accent && ! $bright ) {
                return;
        }
        $a = $accent ? $accent : '#B39A74';
        $b = $bright ? $bright : '#D3BC97';
        if ( '#b39a74' === strtolower( $a ) && '#d3bc97' === strtolower( $b ) ) {
                return;
        }
        $css = ':root{--kb-bronze:' . $a . ';--kb-bronze-bright:' . $b . ';--kb-cyan:' . $a . ';--kb-cyan-deep:' . $b . ';--kb-amber:' . $a . ';--kb-amber-hover:' . $b . ';--kb-teal:' . $a
                . ';--kb-bronze-soft:color-mix(in srgb,' . $a . ' 45%,transparent);--kb-teal-soft:color-mix(in srgb,' . $a . ' 12%,transparent)}';
        echo '<style id="kabel-accent">' . $css . '</style>' . "\n";
}
add_action( 'wp_head', 'kabel_accent_css', 20 );
