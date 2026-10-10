<?php
/**
 * RFQ modal — popup consultation form.
 *
 * Opened by any .kb-rfq-open element (main.js §5). The form is the same
 * unified consultation form (same handler, same CPT, same email),
 * submitted via fetch so the visitor never leaves the page. The product
 * context travels in the hidden #kb_product field, pre-filled from the
 * opener's data-kb-product attribute.
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="kb-rfq" id="kb-rfq" hidden>
	<div class="kb-rfq__backdrop" data-kb-rfq-close></div>
	<div class="kb-rfq__dialog" role="dialog" aria-modal="true" aria-labelledby="kb-rfq-title">
		<button class="kb-rfq__close" type="button" data-kb-rfq-close>
			<span aria-hidden="true">&times;</span>
			<span class="screen-reader-text"><?php esc_html_e( 'Close consultation form', 'kabel' ); ?></span>
		</button>

		<div class="kb-rfq__intro">
			<p class="eyebrow eyebrow--a"><?php esc_html_e( 'Kabel Art Paint — from Druento', 'kabel' ); ?></p>
			<h2 id="kb-rfq-title"><?php esc_html_e( 'Request a Consultation', 'kabel' ); ?></h2>
			<p class="kb-rfq__sub"><?php esc_html_e( 'Tell us about your space, your project or your market — our paint consultants reply within one business day.', 'kabel' ); ?></p>
		</div>

		<div class="kb-rfq__body" data-kb-rfq-body>
			<?php kabel_render_inquiry_form( array( 'source' => 'RFQ modal' ) ); ?>
		</div>

		<div class="kb-rfq__success" data-kb-rfq-success hidden>
			<h3><?php esc_html_e( 'Thank you — inquiry received.', 'kabel' ); ?></h3>
			<p><?php esc_html_e( 'Our paint consultants will reply within one business day. Meanwhile, feel free to keep exploring our paint series.', 'kabel' ); ?></p>
			<p><button class="btn btn--outline-teal" type="button" data-kb-rfq-close><?php esc_html_e( 'Keep browsing', 'kabel' ); ?></button></p>
		</div>
	</div>
</div>
