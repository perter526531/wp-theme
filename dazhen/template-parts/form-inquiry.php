<?php
/**
 * Unified inquiry form template part.
 *
 * Usage: get_template_part( 'template-parts/form-inquiry', null, array( 'product' => '18E-2', 'message' => '...prefill...' ) );
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

$args = wp_parse_args( $args ?? array(), array(
        'product' => '',
        'message' => '',
        'heading' => __( 'Send an inquiry', 'dazhen' ),
        'sub'     => __( 'Our sales engineers reply within one business day.', 'dazhen' ),
) );
?>
<section class="zone-b section inquiry-section" id="inquiry">
        <div class="container inquiry-section__grid">
                <div>
                        <?php
                        dazhen_section_head( array(
                                'zone'    => 'b',
                                'eyebrow' => __( 'Get a Quote', 'dazhen' ),
                                'title'   => $args['heading'],
                                'sub'     => $args['sub'],
                        ) );
                        ?>
                        <ul class="inquiry-section__points">
                                <li><?php esc_html_e( 'MOQ from 100 units', 'dazhen' ); ?></li>
                                <li><?php esc_html_e( 'Customization cycle 4–6 weeks', 'dazhen' ); ?></li>
                                <li><?php esc_html_e( 'Samples and specs on request', 'dazhen' ); ?></li>
                        </ul>
                </div>
                <?php dazhen_render_inquiry_form( array( 'product' => $args['product'], 'message' => $args['message'] ) ); ?>
        </div>
</section>
