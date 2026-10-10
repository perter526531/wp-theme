<?php
/**
 * Single case study.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$country   = get_post_meta( get_the_ID(), '_dz_case_country', true );
	$flag      = get_post_meta( get_the_ID(), '_dz_case_flag', true );
	$order     = get_post_meta( get_the_ID(), '_dz_case_order', true );
	$need      = get_post_meta( get_the_ID(), '_dz_case_need', true );
	$challenge = get_post_meta( get_the_ID(), '_dz_case_challenge', true );
	$feedback  = get_post_meta( get_the_ID(), '_dz_case_feedback', true );
	?>

	<header class="zone-b page-hero page-hero--case">
		<div class="container">
			<?php dazhen_breadcrumbs(); ?>
			<?php if ( $country ) : ?>
				<p class="chip chip--region"><?php echo esc_html( trim( $flag . ' ' . $country ) ); ?></p>
			<?php endif; ?>
			<h1 class="page-hero__title page-hero__title--ink"><?php echo esc_html( get_the_title() ); ?></h1>
			<?php if ( $order ) : ?>
				<p class="chip"><?php echo esc_html( $order ); ?></p>
			<?php endif; ?>
		</div>
	</header>

	<section class="zone-b section section--narrow">
		<div class="container">
			<?php if ( $need ) : ?>
				<h2><?php esc_html_e( 'Requirement', 'dazhen' ); ?></h2>
				<p class="prose"><?php echo esc_html( $need ); ?></p>
			<?php endif; ?>

			<?php if ( $challenge ) : ?>
				<h2><?php esc_html_e( 'Challenge', 'dazhen' ); ?></h2>
				<p class="prose"><?php echo esc_html( $challenge ); ?></p>
			<?php endif; ?>

			<?php if ( get_the_content() ) : ?>
				<h2><?php esc_html_e( 'Solution', 'dazhen' ); ?></h2>
				<div class="prose"><?php the_content(); ?></div>
			<?php endif; ?>

			<?php if ( $feedback ) : ?>
				<blockquote class="case-feedback">
					<?php echo esc_html( $feedback ); ?>
				</blockquote>
			<?php endif; ?>

			<?php
			$related_ids = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( get_the_ID(), '_dz_case_products', true ) ) ) );
			if ( $related_ids ) :
				?>
				<h2><?php esc_html_e( 'Products in this project', 'dazhen' ); ?></h2>
				<div class="grid grid--3">
					<?php foreach ( $related_ids as $related_id ) : ?>
						<?php dazhen_product_card( $related_id ); ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php get_template_part( 'template-parts/form-inquiry' ); ?>

	<?php
endwhile;

get_footer();
