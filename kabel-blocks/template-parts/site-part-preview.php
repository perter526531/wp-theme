<?php
/**
 * Site-part preview renderer (plugin mode).
 *
 * "Site Header" / "Site Footer" are private PAGES that hold the block
 * markup the plugin injects around every front-end view (without the
 * Kabel theme). Rendered as normal pages they showed the site chrome
 * ABOVE the same blocks as the page body — every element doubled.
 *
 * This template takes over the view (kbl3_site_part_preview_template)
 * and shows the part ONCE inside a neutral harness: an explanation bar,
 * the part blocks on a neutral stage, and — for the header — a
 * placeholder where page content would sit on a real page. Nothing else
 * from the site chrome.
 *
 * @package Kabel_Blocks
 */

defined( 'ABSPATH' ) || exit;

$kbl_part_post = get_queried_object();
if ( ! $kbl_part_post instanceof WP_Post ) {
	/* Defensive: never render a broken shell. */
	get_header();
	echo '<main id="main" class="site-main"></main>';
	get_footer();
	return;
}

$kbl_part_which = 'header';
foreach ( kbl3_site_part_defs() as $kbl_key => $kbl_def ) {
	if ( $kbl_part_post->post_name === $kbl_def['slug'] ) {
		$kbl_part_which = $kbl_key;
		break;
	}
}
$kbl_is_header  = 'header' === $kbl_part_which;
$kbl_part_label = $kbl_is_header
	? __( 'Site Header preview', 'kabel-blocks' )
	: __( 'Site Footer preview', 'kabel-blocks' );
$kbl_edit_url   = get_edit_post_link( $kbl_part_post->ID );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<?php wp_head(); ?>
</head>
<body <?php body_class( 'kbl3-part-preview-body' ); ?>>
<?php wp_body_open(); ?>

<div class="kbl3-part-preview<?php echo $kbl_is_header ? ' kbl3-part-preview--header' : ' kbl3-part-preview--footer'; ?>">

	<div class="kbl3-part-preview__bar" role="note">
		<span class="kbl3-part-preview__tag">
			<?php if ( $kbl_is_header ) : ?>
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><path d="M3 6h18M4.5 6v7.5M19.5 6v7.5M3 13.5h18M12 13.5V21m0 0h-3m3 0h3"/></svg>
			<?php else : ?>
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><path d="M3 21h18M4 17h16M5 10h14a1 1 0 0 1 1 1v6H4v-6a1 1 0 0 1 1-1zM8 10V6a4 4 0 0 1 8 0v4"/></svg>
			<?php endif; ?>
			<?php echo esc_html( $kbl_part_label ); ?>
		</span>
		<span class="kbl3-part-preview__text">
			<?php esc_html_e( 'This private page stores the header/footer blocks — visitors never see it directly. The block editor is the place to make changes.', 'kabel-blocks' ); ?>
		</span>
		<?php if ( $kbl_edit_url ) : ?>
			<a class="kbl3-part-preview__edit" href="<?php echo esc_url( $kbl_edit_url ); ?>">
				<?php esc_html_e( 'Edit in the block editor', 'kabel-blocks' ); ?>
			</a>
		<?php endif; ?>
	</div>

	<div class="kbl3-part-preview__stage">
		<?php the_content(); ?>
	</div>

	<?php if ( $kbl_is_header ) : ?>
		<div class="kbl3-part-preview__hint" aria-hidden="true">
			<?php esc_html_e( '· page content appears here on real pages ·', 'kabel-blocks' ); ?>
		</div>
	<?php endif; ?>

</div>

<?php wp_footer(); ?>
</body>
</html>
