<?php
/**
 * Template Name: Solutions Overview
 * Template Post Type: page
 *
 * The /solutions/ static page: four scenario entries + global delivery map +
 * recent success cases with region filtering.
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


get_header();

$solutions_query = new WP_Query( array(
	'post_type'      => 'solution',
	'post_status'    => 'publish',
	'posts_per_page' => 4,
	'orderby'        => 'menu_order date',
	'order'          => 'ASC',
	'no_found_rows'  => true,
) );
$cases_query = new WP_Query( array(
	'post_type'      => 'case_study',
	'post_status'    => 'publish',
	'posts_per_page' => 6,
	'no_found_rows'  => true,
) );
$regions = get_terms( array(
	'taxonomy'   => 'case_region',
	'hide_empty' => true,
) );
?>

<header class="zone-a page-hero">
	<div class="container">
		<?php dazhen_breadcrumbs(); ?>
		<p class="eyebrow eyebrow--a"><?php esc_html_e( 'Solutions & Success Cases', 'dazhen' ); ?></p>
		<h1 class="page-hero__title"><?php the_title(); ?></h1>
		<?php if ( has_excerpt() ) : ?>
			<p class="page-hero__sub"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt() ) ); ?></p>
		<?php else : ?>
			<p class="page-hero__sub"><?php esc_html_e( 'Scenario-matched lineups and the deployments that proved them — from family kitchens to hotel engineering.', 'dazhen' ); ?></p>
		<?php endif; ?>
	</div>
</header>

<?php /* ---------- Scenario entries ---------- */ ?>
<section class="zone-b section">
	<div class="container">
		<?php
		dazhen_section_head( array(
			'zone'    => 'b',
			'eyebrow' => __( 'By Scenario', 'dazhen' ),
			'title'   => __( 'Four scenarios, one engineering platform', 'dazhen' ),
		) );
		?>
		<?php if ( $solutions_query->have_posts() ) : ?>
			<div class="grid grid--4">
				<?php
				while ( $solutions_query->have_posts() ) :
					$solutions_query->the_post();
					$icon    = get_post_meta( get_the_ID(), '_dz_sol_icon', true );
					$summary = get_post_meta( get_the_ID(), '_dz_sol_summary', true );
					?>
					<article class="card-solution" data-dz-reveal>
						<span class="card-solution__icon card-solution__icon--<?php echo esc_attr( $icon ? $icon : 'office' ); ?>" aria-hidden="true"></span>
						<h3><?php the_title(); ?></h3>
						<?php if ( $summary ) : ?>
							<p><?php echo esc_html( $summary ); ?></p>
						<?php endif; ?>
						<a class="card-solution__link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Explore solution →', 'dazhen' ); ?></a>
					</article>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>
		<?php else : ?>
			<p class="empty-state"><?php esc_html_e( 'Scenario landing pages are being prepared — the delivery map and cases below are already live.', 'dazhen' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php /* ---------- Global delivery map ---------- */ ?>
<?php get_template_part( 'template-parts/delivery-map' ); ?>

<?php /* ---------- Success case library ---------- */ ?>
<?php if ( $cases_query->have_posts() ) : ?>
<section class="zone-b section">
	<div class="container">
		<?php
		dazhen_section_head( array(
			'zone'    => 'b',
			'eyebrow' => __( 'Success Cases', 'dazhen' ),
			'title'   => __( 'Proven in real deployments', 'dazhen' ),
		) );
		if ( ! empty( $regions ) && ! is_wp_error( $regions ) ) :
			?>
			<ul class="chip-row chip-row--filter">
				<li class="chip"><a href="<?php echo esc_url( get_post_type_archive_link( 'case_study' ) ); ?>"><?php esc_html_e( 'All regions', 'dazhen' ); ?></a></li>
				<?php foreach ( $regions as $region ) : ?>
					<li class="chip"><a href="<?php echo esc_url( get_term_link( $region ) ); ?>"><?php echo esc_html( $region->name ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		<div class="grid grid--3">
			<?php
			while ( $cases_query->have_posts() ) :
				$cases_query->the_post();
				dazhen_case_card( get_the_ID() );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
		<p class="section-foot">
			<a class="btn btn--outline-teal" href="<?php echo esc_url( get_post_type_archive_link( 'case_study' ) ); ?>"><?php esc_html_e( 'Browse all cases', 'dazhen' ); ?></a>
		</p>
	</div>
</section>
<?php endif; ?>

<?php /* ---------- Optional page content (editor blocks below the modules) ---------- */ ?>
<?php if ( get_the_content() ) : ?>
<section class="zone-b zone-b--alt section section--narrow">
	<div class="container">
		<div class="prose"><?php the_content(); ?></div>
	</div>
</section>
<?php endif; ?>

<?php
get_template_part( 'template-parts/cta-inline' );
get_footer();
