<?php
/**
 * Generic archive — news categories/tags/dates/authors + projects archive.
 *
 * News archives get the design treatment: category filter chips (links,
 * no JS), article cards and the friendly no-results empty state. The
 * projects archive (and the two project categories) keep the card grid
 * with their own two-class filter.
 *
 * @package Kabel
 */

defined( 'ABSPATH' ) || exit;

get_header();

$is_blog_archive = is_category() || is_tag() || is_date() || is_author();

/* The projects archive + the two project reference categories. */
$project_type_slugs    = array( 'home-renovation-projects', 'engineering-projects' );
$queried_project_type  = is_tax( 'project_type' ) ? get_queried_object() : null;
$in_project_type       = $queried_project_type instanceof WP_Term
	&& in_array( $queried_project_type->slug, $project_type_slugs, true );
$is_projects_archive   = is_post_type_archive( 'projects' ) || $in_project_type;

if ( $is_blog_archive && ! $in_project_category ) {
	$archive_title = ( is_category() || is_tag() )
		? (string) single_term_title( '', false )
		: wp_strip_all_tags( (string) get_the_archive_title() );
} elseif ( $is_projects_archive ) {
	$archive_title = $in_project_category
		? (string) single_term_title( '', false )
		: (string) post_type_archive_title( '', false );
} else {
	$archive_title = wp_strip_all_tags( (string) get_the_archive_title() );
}
?>
<header class="zone-a page-hero">
	<div class="container">
		<?php kabel_breadcrumbs(); ?>
		<?php
		if ( $is_projects_archive ) {
			$archive_eyebrow = __( 'PROJECTS', 'kabel' );
		} elseif ( $is_blog_archive ) {
			$archive_eyebrow = __( 'NEWS', 'kabel' );
		} else {
			$archive_eyebrow = __( 'Kabel', 'kabel' );
		}
		?>
		<p class="eyebrow eyebrow--a"><?php echo esc_html( $archive_eyebrow ); ?></p>
		<h1 class="page-hero__title"><?php echo esc_html( $archive_title ); ?></h1>
		<?php if ( $is_projects_archive ) : ?>
			<p class="page-hero__sub"><?php esc_html_e( 'Home renovations and engineering projects finished with Kabel decorative coatings.', 'kabel' ); ?></p>
		<?php elseif ( $is_blog_archive ) : ?>
			<p class="page-hero__sub"><?php esc_html_e( 'Company news, industry updates and plain-language paint knowledge from the Kabel team.', 'kabel' ); ?></p>
		<?php endif; ?>
	</div>
</header>

<section class="zone-b section">
	<?php if ( $is_blog_archive && ! $in_project_category ) { kabel_sidebar_open(); } ?>
	<div class="container">
		<?php if ( $is_projects_archive ) : ?>
			<nav class="blog-toolbar" aria-label="<?php esc_attr_e( 'Filter projects by class', 'kabel' ); ?>">
				<a class="blog-chip<?php echo $is_projects_archive && ! $in_project_category ? ' is-active' : ''; ?>" href="<?php echo esc_url( (string) get_post_type_archive_link( 'projects' ) ); ?>"<?php echo $is_projects_archive && ! $in_project_category ? ' aria-current="page"' : ''; ?>>
					<?php esc_html_e( 'All', 'kabel' ); ?>
				</a>
				<?php foreach ( $project_type_slugs as $project_type_slug ) : ?>
					<?php
					$project_type = get_term_by( 'slug', $project_type_slug, 'project_type' );
					if ( ! $project_type || is_wp_error( $project_type ) ) {
						continue;
					}
					$is_active = $queried_project_type instanceof WP_Term && (int) $queried_project_type->term_id === (int) $project_type->term_id;
					$url = get_term_link( $project_type );
					if ( is_wp_error( $url ) ) {
						continue;
					}
					?>
					<a class="blog-chip<?php echo $is_active ? ' is-active' : ''; ?>" href="<?php echo esc_url( $url ); ?>"<?php echo $is_active ? ' aria-current="page"' : ''; ?>>
						<?php echo esc_html( $project_type->name ); ?>
					</a>
				<?php endforeach; ?>
			</nav>
		<?php elseif ( $is_blog_archive ) : ?>
			<?php get_template_part( 'template-parts/blog-toolbar' ); ?>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<div class="grid grid--3">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/post-card' );
				endwhile;
				?>
			</div>
			<?php kabel_pagination(); ?>
		<?php elseif ( $is_projects_archive ) : ?>
			<div class="no-results">
				<span class="no-results__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-6h6v6"/></svg>
				</span>
				<h2 class="no-results__title"><?php esc_html_e( 'No projects in this class yet', 'kabel' ); ?></h2>
				<p class="no-results__text"><?php esc_html_e( 'New project references are on their way. In the meantime, the paint series pages show the finishes behind these spaces.', 'kabel' ); ?></p>
				<a class="btn btn--outline-teal no-results__back" href="<?php echo esc_url( (string) get_post_type_archive_link( 'product' ) ); ?>"><?php esc_html_e( 'Explore paint series', 'kabel' ); ?></a>
			</div>
		<?php elseif ( $is_blog_archive ) : ?>
			<div class="no-results">
				<span class="no-results__icon" aria-hidden="true">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/></svg>
				</span>
				<h2 class="no-results__title"><?php esc_html_e( 'No articles in this category yet', 'kabel' ); ?></h2>
				<p class="no-results__text"><?php esc_html_e( 'New pieces for this category are in the pipeline. In the meantime, company news covers showroom openings and brand milestones, and knowledge collects practical painting guidance.', 'kabel' ); ?></p>
				<?php
				$posts_page = (int) get_option( 'page_for_posts' );
				$back_url   = $posts_page ? (string) get_permalink( $posts_page ) : home_url( '/' );
				?>
				<a class="btn btn--outline-teal no-results__back" href="<?php echo esc_url( $back_url ); ?>"><?php esc_html_e( 'Back to all articles', 'kabel' ); ?></a>
			</div>
		<?php else : ?>
			<p class="empty-state"><?php esc_html_e( 'Nothing published here yet.', 'kabel' ); ?></p>
		<?php endif; ?>
	</div>
	<?php if ( $is_blog_archive && ! $in_project_category ) { get_sidebar(); kabel_sidebar_close(); } ?>
</section>

<?php
get_footer();
