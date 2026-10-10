<?php
/**
 * Template Name: Contact
 * Template Post Type: page
 *
 * The /contact/ page: slogan hero, three contact info cards (customizer-
 * backed), and the two-column inquiry section — pitch/promises on the left,
 * the unified 7-field inquiry form on the right, privacy fine print below.
 *
 * The form is the existing inquiry system (inc/inquiry.php): its 7 fields
 * already match the confirmed design one-to-one (name → Contact person,
 * subject → Product of interest), so no field extension is needed —
 * honeypot, nonce and rate limiting are inherited unchanged.
 *
 * Filters:
 * - dazhen_contact_slogan : hero headline string.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

/*
 * Block takeover: editor content (Dazhen Blocks kit or anything else)
 * renders full-canvas as blocks; an empty page keeps this designed template.
 */
if ( dazhen_render_block_page() ) {
	return;
}


/**
 * Return the inline line-icon SVG for a Contact info card.
 *
 * @param string $key Icon key.
 * @return string SVG markup (empty string for unknown keys).
 */
function dazhen_contact_icon( string $key ): string {
	$icons = array(
		'phone' => '<path d="M5.5 4h3l1.7 4.2-2 1.6a12.5 12.5 0 0 0 6 6l1.6-2L20 15.5v3a2 2 0 0 1-2.2 2A15.8 15.8 0 0 1 3.5 6.2 2 2 0 0 1 5.5 4Z"/>',
		'mail'  => '<rect x="3.5" y="5.5" width="17" height="13" rx="2"/><path d="m4.5 7.5 7.5 6 7.5-6"/>',
		'pin'   => '<path d="M12 21.5S5.5 15.6 5.5 10.8a6.5 6.5 0 0 1 13 0c0 4.8-6.5 10.7-6.5 10.7Z"/><circle cx="12" cy="10.5" r="2.4"/>',
	);
	return isset( $icons[ $key ] )
		? '<svg class="contact-card__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $icons[ $key ] . '</svg>'
		: '';
}

get_header();

while ( have_posts() ) :
	the_post();

	$slogan = apply_filters(
		'dazhen_contact_slogan',
		__( 'Let’s talk about your program.', 'dazhen' )
	);

	$phone          = (string) dazhen_get_option( 'dazhen_phone', '+86 180 2699 3135' );
	$email          = (string) dazhen_get_option( 'dazhen_email', '2046772003@qq.com' );
	$address        = (string) dazhen_get_option( 'dazhen_address', __( 'Dayawan, Huizhou City, Guangdong Province, China', 'dazhen' ) );
	$working_hours  = (string) dazhen_get_option( 'dazhen_working_hours', __( 'Time zone: GMT+8 · China Standard Time', 'dazhen' ) );
	$phone_url      = 'tel:' . preg_replace( '/[^0-9+]/', '', $phone );
	$whatsapp_url   = 'https://wa.me/' . preg_replace( '/[^0-9]/', '', $phone );
	?>

	<header class="zone-a page-hero contact-hero">
		<div class="container">
			<?php dazhen_breadcrumbs(); ?>
			<p class="eyebrow eyebrow--a"><?php esc_html_e( 'Contact us', 'dazhen' ); ?></p>
			<h1 class="page-hero__title"><?php echo esc_html( $slogan ); ?></h1>
			<p class="page-hero__sub"><?php esc_html_e( 'A question, a spec or a full program brief — send it over. A sales engineer replies within one business day.', 'dazhen' ); ?></p>
		</div>
	</header>

	<?php /* ---------- Info cards (customizer-backed) ---------- */ ?>
	<section class="zone-b section">
		<div class="container">
			<?php
			dazhen_section_head(
				array(
					'zone'    => 'b',
					'eyebrow' => __( 'Reach us', 'dazhen' ),
					'title'   => __( 'Talk to the team', 'dazhen' ),
					'sub'     => __( 'Direct lines, no ticket queues — your message reaches a person who can answer it.', 'dazhen' ),
				)
			);
			?>
			<div class="grid grid--3 contact-cards">
				<article class="contact-card" data-dz-reveal>
					<span class="contact-card__icon-wrap">
						<?php echo dazhen_contact_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG markup. ?>
					</span>
					<h3 class="contact-card__title"><?php esc_html_e( 'Phone / WhatsApp', 'dazhen' ); ?></h3>
					<p class="contact-card__value"><a href="<?php echo esc_url( $phone_url ); ?>"><?php echo esc_html( $phone ); ?></a></p>
					<p class="contact-card__note"><?php esc_html_e( 'For voice calls and WhatsApp messages.', 'dazhen' ); ?></p>
				</article>
				<article class="contact-card" data-dz-reveal>
					<span class="contact-card__icon-wrap">
						<?php echo dazhen_contact_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG markup. ?>
					</span>
					<h3 class="contact-card__title"><?php esc_html_e( 'Email', 'dazhen' ); ?></h3>
					<p class="contact-card__value"><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></p>
					<p class="contact-card__note"><?php esc_html_e( 'Best for specs, drawings and volume details.', 'dazhen' ); ?></p>
				</article>
				<article class="contact-card" data-dz-reveal>
					<span class="contact-card__icon-wrap">
						<?php echo dazhen_contact_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG markup. ?>
					</span>
					<h3 class="contact-card__title"><?php esc_html_e( 'Factory', 'dazhen' ); ?></h3>
					<p class="contact-card__value"><?php echo esc_html( $address ); ?></p>
					<p class="contact-card__note"><?php esc_html_e( 'Manufacturing and R&D on one site.', 'dazhen' ); ?></p>
				</article>
			</div>
		</div>
	</section>

	<?php /* ---------- Inquiry form: pitch + promises (left), unified 7-field form (right) ---------- */ ?>
	<section class="zone-b zone-b--alt section contact-form-section" id="inquiry">
		<div class="container contact-form-section__grid">
			<div class="contact-pitch">
				<?php
				dazhen_section_head(
					array(
						'zone'    => 'b',
						'eyebrow' => __( 'Inquiry form', 'dazhen' ),
						'title'   => __( 'Send us the details', 'dazhen' ),
					)
				);
				?>
				<p class="contact-pitch__text"><?php esc_html_e( 'Whether it’s a standard model, a rebrand or full ODM development — send the details and a sales engineer will map your program to the right platform, with costs and a realistic timeline.', 'dazhen' ); ?></p>
				<ul class="contact-pitch__points">
					<li><?php esc_html_e( 'Reply within one business day', 'dazhen' ); ?></li>
					<li><?php esc_html_e( 'Working languages: English & Chinese', 'dazhen' ); ?></li>
					<li><?php echo esc_html( $working_hours ); ?></li>
				</ul>
				<p class="contact-pitch__alt">
					<?php
					printf(
						/* translators: 1: email address link, 2: phone number link */
						esc_html__( 'Prefer email? Write to %1$s or message %2$s on WhatsApp.', 'dazhen' ),
						'<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>',
						'<a href="' . esc_url( $whatsapp_url ) . '">' . esc_html( $phone ) . '</a>'
					);
					?>
				</p>
			</div>

			<?php dazhen_render_inquiry_form(); ?>
		</div>

		<div class="container">
			<div class="contact-fineprint">
				<p>
					<svg class="contact-fineprint__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/><path d="M8.5 12l2.5 2.5 4.5-4.5"/></svg>
					<?php esc_html_e( 'This form is protected by a hidden honeypot field and rate limiting — invisible to real users, effective against bots.', 'dazhen' ); ?>
				</p>
				<p>
					<svg class="contact-fineprint__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/><path d="M8.5 12l2.5 2.5 4.5-4.5"/></svg>
					<?php esc_html_e( 'Your data is used only to respond to this inquiry — never sold, never shared, removed on request.', 'dazhen' ); ?>
				</p>
			</div>
		</div>
	</section>

	<?php
endwhile;

get_footer();
