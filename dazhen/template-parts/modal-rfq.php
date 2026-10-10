<?php
/**
 * RFQ modal — popup inquiry form.
 *
 * Opened by any .dz-rfq-open element (main.js §5). The form is the same
 * unified inquiry form (same handler, same CPT, same email), submitted via
 * fetch so the visitor never leaves the page.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="dz-rfq" id="dz-rfq" hidden>
	<div class="dz-rfq__backdrop" data-dz-rfq-close></div>
	<div class="dz-rfq__dialog" role="dialog" aria-modal="true" aria-labelledby="dz-rfq-title">
		<button class="dz-rfq__close" type="button" data-dz-rfq-close>
			<span aria-hidden="true">&times;</span>
			<span class="screen-reader-text"><?php esc_html_e( 'Close inquiry form', 'dazhen' ); ?></span>
		</button>

		<div class="dz-rfq__intro">
			<p class="eyebrow eyebrow--a"><?php esc_html_e( 'Request for quote', 'dazhen' ); ?></p>
			<h2 id="dz-rfq-title"><?php esc_html_e( 'Tell us what you need', 'dazhen' ); ?></h2>
			<p class="dz-rfq__sub"><?php esc_html_e( 'Model, quantity, target price — the more you share, the sharper our first quote. Reply within one business day.', 'dazhen' ); ?></p>
		</div>

		<div class="dz-rfq__body" data-dz-rfq-body>
			<?php dazhen_render_inquiry_form( array( 'source' => 'RFQ modal' ) ); ?>
		</div>

		<div class="dz-rfq__success" data-dz-rfq-success hidden>
			<h3><?php esc_html_e( 'Thank you — inquiry received.', 'dazhen' ); ?></h3>
			<p><?php esc_html_e( 'Our sales team will reply within one business day. Meanwhile, feel free to keep browsing our products.', 'dazhen' ); ?></p>
			<p><button class="btn btn--outline-teal" type="button" data-dz-rfq-close><?php esc_html_e( 'Keep browsing', 'dazhen' ); ?></button></p>
		</div>
	</div>
</div>
