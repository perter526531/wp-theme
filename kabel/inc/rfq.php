<?php
/**
 * RFQ popup — the consultation call-to-action modal.
 *
 * B2B rule: every conversion button can popup the consultation form
 * instead of navigating away. Any element with the class `kb-rfq-open`
 * opens the modal (main.js §5); an optional `data-kb-product="…"` value
 * pre-fills the hidden product field (#kb_product).
 *
 * Renders once in the footer via template-parts/modal-rfq.php.
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Print a consultation button that opens the RFQ modal.
 *
 * @param string $product Pre-filled product / context name (e.g. "Velvet Texture").
 * @param string $label   Button label (defaults to the Customizer CTA text).
 * @param string $class   Extra classes.
 */
function kabel_rfq_button( string $product = '', string $label = '', string $class = 'btn btn--cta' ): void {
	if ( '' === $label ) {
		$label = kabel_cta_text();
	}
	printf(
		'<a class="%1$s kb-rfq-open" href="#kb-rfq" data-kb-product="%2$s">%3$s</a>',
		esc_attr( $class ),
		esc_attr( $product ),
		esc_html( $label )
	);
}

/**
 * Render the modal once in the footer.
 */
function kabel_rfq_modal_in_footer(): void {
	if ( ! kabel_rfq_modal_needed() ) {
		return;
	}
	get_template_part( 'template-parts/modal-rfq' );
}
add_action( 'wp_footer', 'kabel_rfq_modal_in_footer' );

/**
 * Does the current response need the modal at all?
 *
 * Yes on product pages, the contact page and anywhere a `.kb-rfq-open`
 * element might be printed (block pages with our CTA / inquiry blocks).
 * Search / 404 skip it.
 */
function kabel_rfq_modal_needed(): bool {
	if ( is_search() || is_404() ) {
		return false;
	}
	return true;
}
