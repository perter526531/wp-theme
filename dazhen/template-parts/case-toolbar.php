<?php
/**
 * Case region filter chips — no-JS, WordPress-native.
 *
 * Mirrors blog-toolbar.php for the success-case archive: each chip links
 * to a case_region term archive; the current region is highlighted
 * server-side. Regions stay visible even when empty.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

$regions = get_terms( array(
	'taxonomy'   => 'case_region',
	'hide_empty' => false,
) );
if ( is_wp_error( $regions ) || empty( $regions ) ) {
	return;
}

$all_url   = (string) get_post_type_archive_link( 'case_study' );
$active_id = is_tax( 'case_region' ) ? (int) get_queried_object_id() : 0;
$all_count = (int) wp_count_posts( 'case_study' )->publish;
?>
<nav class="blog-toolbar" aria-label="<?php esc_attr_e( 'Filter cases by region', 'dazhen' ); ?>">
	<a class="blog-chip<?php echo 0 === $active_id ? ' is-active' : ''; ?>" href="<?php echo esc_url( $all_url ); ?>"<?php echo 0 === $active_id ? ' aria-current="page"' : ''; ?>>
		<?php esc_html_e( 'All', 'dazhen' ); ?>
		<span class="blog-chip__count"><?php echo esc_html( (string) $all_count ); ?></span>
	</a>
	<?php foreach ( $regions as $region ) : ?>
		<?php $is_active = (int) $region->term_id === $active_id; ?>
		<a class="blog-chip<?php echo $is_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_term_link( $region ) ); ?>"<?php echo $is_active ? ' aria-current="page"' : ''; ?>>
			<?php echo esc_html( $region->name ); ?>
			<span class="blog-chip__count"><?php echo esc_html( (string) $region->count ); ?></span>
		</a>
	<?php endforeach; ?>
</nav>
