<?php
/**
 * RFQ popup — the quote-call-to-action modal.
 *
 * B2B rule: every conversion button can popup the inquiry form instead of
 * navigating away. Any element with the class `dz-rfq-open` opens the modal
 * (main.js §5); an optional `data-dz-product="…"` value pre-fills the
 * product field.
 *
 * Renders once in the footer via template-parts/modal-rfq.php.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

/**
 * Print a quote button that opens the RFQ modal.
 *
 * @param string $product Pre-filled product / context name.
 * @param string $label   Button label (defaults to the Customizer CTA text).
 * @param string $class   Extra classes.
 */
function dazhen_rfq_button( string $product = '', string $label = '', string $class = 'btn btn--cta' ): void {
	if ( '' === $label ) {
		$label = dazhen_cta_text();
	}
	printf(
		'<a class="%1$s dz-rfq-open" href="#dz-rfq" data-dz-product="%2$s">%3$s</a>',
		esc_attr( $class ),
		esc_attr( $product ),
		esc_html( $label )
	);
}

/**
 * Render the modal once in the footer.
 */
function dazhen_rfq_modal_in_footer(): void {
	if ( ! dazhen_rfq_modal_needed() ) {
		return;
	}
	get_template_part( 'template-parts/modal-rfq' );
}
add_action( 'wp_footer', 'dazhen_rfq_modal_in_footer' );

/**
 * Does the current response need the modal at all?
 *
 * Yes on product pages, the contact page and anywhere a `.dz-rfq-open`
 * element might be printed (block pages with our CTA / inquiry blocks).
 * Search / 404 skip it.
 */
function dazhen_rfq_modal_needed(): bool {
	if ( is_search() || is_404() ) {
		return false;
	}
	return true;
}
