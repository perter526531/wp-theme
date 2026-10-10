<?php
/**
 * The blog sidebar.
 *
 * Widgets appear next to blog posts, archives and search results — but only
 * when the Blog Sidebar has widgets. An empty sidebar keeps the full-width
 * layout (the templates call dazhen_has_blog_sidebar() before wrapping).
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_active_sidebar( 'blog-sidebar' ) ) {
	return;
}
?>

<aside class="blog-sidebar site-secondary" role="complementary" aria-label="<?php esc_attr_e( 'Blog sidebar', 'dazhen' ); ?>">
	<?php dynamic_sidebar( 'blog-sidebar' ); ?>
</aside>
