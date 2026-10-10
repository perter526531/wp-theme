<?php
/**
 * Blog category filter chips — no-JS, WordPress-native.
 *
 * Each chip links to the category archive URL; the current category is
 * highlighted server-side. Used on the posts page (index.php) and blog
 * archives (archive.php). The default ("Uncategorized") category is
 * excluded; empty categories stay visible so their empty state is
 * reachable, matching the confirmed design.
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

$categories = get_categories( array(
	'hide_empty' => false,
	'exclude'    => (int) get_option( 'default_category' ),
) );
if ( empty( $categories ) ) {
	return;
}

$posts_page = (int) get_option( 'page_for_posts' );
$all_url    = $posts_page ? (string) get_permalink( $posts_page ) : home_url( '/' );
$active_id  = is_category() ? (int) get_queried_object_id() : 0;
$count      = wp_count_posts();
$all_count  = is_object( $count ) && isset( $count->publish ) ? (int) $count->publish : 0;
?>
<nav class="blog-toolbar" aria-label="<?php esc_attr_e( 'Filter articles by category', 'kabel' ); ?>">
	<a class="blog-chip<?php echo 0 === $active_id ? ' is-active' : ''; ?>" href="<?php echo esc_url( $all_url ); ?>"<?php echo 0 === $active_id ? ' aria-current="page"' : ''; ?>>
		<?php esc_html_e( 'All', 'kabel' ); ?>
		<span class="blog-chip__count"><?php echo esc_html( (string) $all_count ); ?></span>
	</a>
	<?php foreach ( $categories as $category ) : ?>
		<?php $is_active = (int) $category->term_id === $active_id; ?>
		<a class="blog-chip<?php echo $is_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_term_link( $category ) ); ?>"<?php echo $is_active ? ' aria-current="page"' : ''; ?>>
			<?php echo esc_html( $category->name ); ?>
			<span class="blog-chip__count"><?php echo esc_html( (string) $category->count ); ?></span>
		</a>
	<?php endforeach; ?>
</nav>
