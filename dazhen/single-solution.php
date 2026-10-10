<?php
/**
 * Single solution — scenario landing page.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$pains    = array_filter( array_map( 'trim', explode( "\n", (string) get_post_meta( get_the_ID(), '_dz_sol_pains', true ) ) ) );
	$products = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( get_the_ID(), '_dz_sol_products', true ) ) ) );
	?>

	<header class="zone-a page-hero">
		<div class="container">
			<?php dazhen_breadcrumbs(); ?>
			<p class="eyebrow eyebrow--a"><?php esc_html_e( 'Solution', 'dazhen' ); ?></p>
			<h1 class="page-hero__title"><?php echo esc_html( get_the_title() ); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p class="page-hero__sub"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</div>
	</header>

	<?php if ( $pains ) : ?>
	<section class="zone-b section section--narrow">
		<div class="container">
			<h2><?php esc_html_e( 'What this scenario struggles with', 'dazhen' ); ?></h2>
			<ul class="pain-list">
				<?php foreach ( $pains as $pain ) : ?>
					<li><?php echo esc_html( $pain ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( get_the_content() ) : ?>
	<section class="zone-b zone-b--alt section section--narrow">
		<div class="container">
			<h2><?php esc_html_e( 'The Dazhen approach', 'dazhen' ); ?></h2>
			<div class="prose"><?php the_content(); ?></div>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( $products ) : ?>
	<section class="zone-b section">
		<div class="container">
			<h2><?php esc_html_e( 'Recommended products', 'dazhen' ); ?></h2>
			<div class="grid grid--4">
				<?php foreach ( $products as $product_id ) : ?>
					<?php dazhen_product_card( $product_id ); ?>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<?php
	// Related cases sharing this scenario's regions (simple: latest 3).
	$cases = new WP_Query( array(
		'post_type'      => 'case_study',
		'posts_per_page' => 3,
	) );
	if ( $cases->have_posts() ) :
		?>
		<section class="zone-b zone-b--alt section">
			<div class="container">
				<h2><?php esc_html_e( 'Related success cases', 'dazhen' ); ?></h2>
				<div class="grid grid--3">
					<?php
					while ( $cases->have_posts() ) :
						$cases->the_post();
						dazhen_case_card( get_the_ID() );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/form-inquiry' ); ?>

	<?php
endwhile;

get_footer();
