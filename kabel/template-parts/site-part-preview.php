<?php
/**
 * Site-part preview renderer.
 *
 * "Site Header" / "Site Footer" are private PAGES that hold the block markup
 * the theme injects around every front-end view. Before this template they
 * rendered like normal pages — the site header ABOVE the same blocks as the
 * page body, so the header appeared twice (two nav menus, two contact bars).
 *
 * This template takes over the view (kabel_site_part_preview_template) and
 * shows the part ONCE inside a neutral harness: an explanation bar, the part
 * blocks on a neutral stage, and — for the header — a placeholder where page
 * content would sit on a real page. Nothing else from the site chrome.
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

$kabel_part_post = get_queried_object();
if ( ! $kabel_part_post instanceof WP_Post ) {
        /* Defensive: never render a broken shell. */
        get_header();
        echo '<main id="main" class="site-main"></main>';
        get_footer();
        return;
}

$kabel_part_which = 'header';
foreach ( kabel_site_part_defs() as $kabel_key => $kabel_def ) {
        if ( $kabel_part_post->post_name === $kabel_def['slug'] ) {
                $kabel_part_which = $kabel_key;
                break;
        }
}
$kabel_is_header  = 'header' === $kabel_part_which;
$kabel_part_label = $kabel_is_header
        ? __( 'Site Header preview', 'kabel' )
        : __( 'Site Footer preview', 'kabel' );
$kabel_edit_url   = get_edit_post_link( $kabel_part_post->ID );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<?php wp_head(); ?>
</head>
<body <?php body_class( 'kabel-part-preview-body' ); ?>>
<?php wp_body_open(); ?>

<div class="kabel-part-preview<?php echo $kabel_is_header ? ' kabel-part-preview--header' : ' kabel-part-preview--footer'; ?>">

        <div class="kabel-part-preview__bar" role="note">
                <span class="kabel-part-preview__tag">
                        <?php if ( $kabel_is_header ) : ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><path d="M3 6h18M4.5 6v7.5M19.5 6v7.5M3 13.5h18M12 13.5V21m0 0h-3m3 0h3"/></svg>
                        <?php else : ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><path d="M3 21h18M4 17h16M5 10h14a1 1 0 0 1 1 1v6H4v-6a1 1 0 0 1 1-1zM8 10V6a4 4 0 0 1 8 0v4"/></svg>
                        <?php endif; ?>
                        <?php echo esc_html( $kabel_part_label ); ?>
                </span>
                <span class="kabel-part-preview__text">
                        <?php esc_html_e( 'This private page stores the header/footer blocks — visitors never see it directly. The block editor is the place to make changes.', 'kabel' ); ?>
                </span>
                <?php if ( $kabel_edit_url ) : ?>
                        <a class="kabel-part-preview__edit" href="<?php echo esc_url( $kabel_edit_url ); ?>">
                                <?php esc_html_e( 'Edit in the block editor', 'kabel' ); ?>
                        </a>
                <?php endif; ?>
        </div>

        <div class="kabel-part-preview__stage">
                <?php the_content(); ?>
        </div>

        <?php if ( $kabel_is_header ) : ?>
                <div class="kabel-part-preview__hint" aria-hidden="true">
                        <?php esc_html_e( '· page content appears here on real pages ·', 'kabel' ); ?>
                </div>
        <?php endif; ?>

</div>

<?php wp_footer(); ?>
</body>
</html>
