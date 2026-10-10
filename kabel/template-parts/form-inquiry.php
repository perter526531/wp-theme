<?php
/**
 * Unified inquiry form template part.
 *
 * Usage: get_template_part( 'template-parts/form-inquiry', null, array( 'product' => '18E-2', 'message' => '...prefill...' ) );
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args( $args ?? array(), array(
        'product' => '',
        'message' => '',
        'heading' => __( 'Send an inquiry', 'kabel' ),
        'sub'     => __( 'Our sales engineers reply within one business day.', 'kabel' ),
) );
?>
<section class="zone-b section inquiry-section" id="inquiry">
        <div class="container inquiry-section__grid">
                <div>
                        <?php
                        kabel_section_head( array(
                                'zone'    => 'b',
                                'eyebrow' => __( 'Request a Consultation', 'kabel' ),
                                'title'   => $args['heading'],
                                'sub'     => $args['sub'],
                        ) );
                        ?>
                        <ul class="inquiry-section__points">
                                <li><?php esc_html_e( 'Effect samples on request', 'kabel' ); ?></li>
								<li><?php esc_html_e( 'Custom colors and finishes', 'kabel' ); ?></li>
                                <li><?php esc_html_e( 'Samples and specs on request', 'kabel' ); ?></li>
                        </ul>
                </div>
                <?php kabel_render_inquiry_form( array( 'product' => $args['product'], 'message' => $args['message'] ) ); ?>
        </div>
</section>
