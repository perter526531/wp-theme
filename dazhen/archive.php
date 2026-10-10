<?php
/**
 * Generic archive — blog categories/tags/dates/authors + case-study archive.
 *
 * Blog archives get the design treatment: category filter chips (links,
 * no JS), article cards and the friendly no-results empty state. Other
 * archives (case studies) keep the generic card grid.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

get_header();

$is_blog_archive = is_category() || is_tag() || is_date() || is_author();
$is_case_archive = is_post_type_archive( 'case_study' ) || is_tax( 'case_region' );

if ( $is_blog_archive ) {
        $archive_title = ( is_category() || is_tag() )
                ? (string) single_term_title( '', false )
                : wp_strip_all_tags( (string) get_the_archive_title() );
} elseif ( $is_case_archive ) {
        $archive_title = is_tax( 'case_region' )
                ? (string) single_term_title( '', false )
                : (string) post_type_archive_title( '', false );
} else {
        $archive_title = wp_strip_all_tags( (string) get_the_archive_title() );
}
?>

<header class="zone-a page-hero">
        <div class="container">
                <?php dazhen_breadcrumbs(); ?>
                <?php
                if ( $is_blog_archive ) {
                        $archive_eyebrow = __( 'News & Insights', 'dazhen' );
                } elseif ( $is_case_archive ) {
                        $archive_eyebrow = __( 'Field References', 'dazhen' );
                } else {
                        $archive_eyebrow = __( 'Insights', 'dazhen' );
                }
                ?>
                <p class="eyebrow eyebrow--a"><?php echo esc_html( $archive_eyebrow ); ?></p>
                <h1 class="page-hero__title"><?php echo esc_html( $archive_title ); ?></h1>
                <?php if ( $is_blog_archive ) : ?>
                        <p class="page-hero__sub"><?php esc_html_e( 'Program milestones, trade-show recaps and plain-language technology notes from the Dayawan team.', 'dazhen' ); ?></p>
                <?php elseif ( $is_case_archive ) : ?>
                        <p class="page-hero__sub"><?php esc_html_e( 'Reference deployments across regions — filtered by market where our water systems run today.', 'dazhen' ); ?></p>
                <?php endif; ?>
        </div>
</header>

<section class="zone-b section">
        <?php if ( $is_blog_archive ) { dazhen_sidebar_open(); } ?>
        <div class="container">
                <?php if ( $is_blog_archive ) : ?>
                        <?php get_template_part( 'template-parts/blog-toolbar' ); ?>
                <?php elseif ( $is_case_archive ) : ?>
                        <?php get_template_part( 'template-parts/case-toolbar' ); ?>
                <?php endif; ?>

                <?php if ( have_posts() ) : ?>
                        <div class="grid grid--3">
                                <?php
                                while ( have_posts() ) :
                                        the_post();
                                        if ( 'case_study' === get_post_type() ) {
                                                dazhen_case_card( get_the_ID() );
                                        } else {
                                                get_template_part( 'template-parts/post-card' );
                                        }
                                endwhile;
                                ?>
                        </div>
                        <?php dazhen_pagination(); ?>
                <?php elseif ( $is_blog_archive ) : ?>
                        <div class="no-results">
                                <span class="no-results__icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/></svg>
                                </span>
                                <h2 class="no-results__title"><?php esc_html_e( 'No articles in this category yet', 'dazhen' ); ?></h2>
                                <p class="no-results__text"><?php esc_html_e( 'New pieces for this category are in the pipeline. In the meantime, company news covers program milestones, trade-show recaps and technology notes.', 'dazhen' ); ?></p>
                                <?php
                                $posts_page = (int) get_option( 'page_for_posts' );
                                $back_url   = $posts_page ? (string) get_permalink( $posts_page ) : home_url( '/' );
                                ?>
                                <a class="btn btn--outline-teal no-results__back" href="<?php echo esc_url( $back_url ); ?>"><?php esc_html_e( 'Back to all articles', 'dazhen' ); ?></a>
                        </div>
                <?php else : ?>
                        <p class="empty-state"><?php esc_html_e( 'Nothing published here yet.', 'dazhen' ); ?></p>
                <?php endif; ?>
        </div>
        <?php if ( $is_blog_archive ) { get_sidebar(); dazhen_sidebar_close(); } ?>
</section>

<?php
get_footer();
