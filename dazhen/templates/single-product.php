<?php
/**
 * Single product — gallery, bullets, structured spec table, related, inquiry.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$model   = get_post_meta( get_the_ID(), '_dz_product_model', true );
	$tagline = get_post_meta( get_the_ID(), '_dz_product_tagline', true );
	$bullets = array_filter( array_map( 'trim', explode( "\n", (string) get_post_meta( get_the_ID(), '_dz_product_bullets', true ) ) ) );
	$gallery = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( get_the_ID(), '_dz_product_gallery', true ) ) ) );
	$specs   = dazhen_get_specs_grouped( get_the_ID() );
	?>

	<header class="zone-a page-hero page-hero--product">
		<div class="container">
			<?php dazhen_breadcrumbs(); ?>
			<div class="page-hero--product__grid">
				<div class="page-hero--product__media">
					<?php if ( has_post_thumbnail() ) : ?>
						<?php the_post_thumbnail( 'dazhen-large', array( 'class' => 'page-hero--product__img' ) ); ?>
					<?php endif; ?>
				</div>
				<div class="page-hero--product__info">
					<p class="eyebrow eyebrow--a"><?php echo wp_kses_post( get_the_term_list( get_the_ID(), 'product_series', '', ' / ' ) ); ?></p>
					<h1 class="page-hero__title"><?php echo esc_html( $model ? $model : get_the_title() ); ?></h1>
					<?php if ( $tagline ) : ?>
						<p class="page-hero__sub"><?php echo esc_html( $tagline ); ?></p>
					<?php endif; ?>
					<?php if ( $bullets ) : ?>
						<ul class="page-hero--product__bullets">
							<?php foreach ( $bullets as $bullet ) : ?>
								<li><?php echo esc_html( $bullet ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<div class="page-hero--product__actions">
						<a class="btn btn--cta" href="#inquiry"><?php esc_html_e( 'Inquire about this model', 'dazhen' ); ?></a>
					</div>
				</div>
			</div>
		</div>
	</header>

	<?php if ( ! empty( get_the_content() ) ) : ?>
	<section class="zone-b section section--narrow">
		<div class="container">
			<h2><?php esc_html_e( 'Overview', 'dazhen' ); ?></h2>
			<div class="prose"><?php the_content(); ?></div>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( $gallery ) : ?>
	<section class="zone-b zone-b--alt section section--narrow">
		<div class="container">
			<h2><?php esc_html_e( 'Gallery', 'dazhen' ); ?></h2>
			<div class="gallery">
				<?php foreach ( $gallery as $attachment_id ) : ?>
					<?php echo wp_get_attachment_image( $attachment_id, 'dazhen-card', false, array( 'class' => 'gallery__item' ) ); ?>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( $specs ) : ?>
	<section class="zone-b section section--narrow">
		<div class="container">
			<h2><?php esc_html_e( 'Specifications', 'dazhen' ); ?></h2>
			<?php foreach ( $specs as $group => $rows ) : ?>
				<table class="spec-table">
					<?php if ( $group ) : ?>
						<caption><?php echo esc_html( $group ); ?></caption>
					<?php endif; ?>
					<tbody>
						<?php foreach ( $rows as $row ) : ?>
							<tr>
								<th scope="row"><?php echo esc_html( $row['name'] ); ?></th>
								<td><?php echo esc_html( trim( $row['value'] . ' ' . $row['unit'] ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<?php
	$series_terms = get_the_terms( get_the_ID(), 'product_series' );
	if ( $series_terms && ! is_wp_error( $series_terms ) ) {
		$related = new WP_Query( array(
			'post_type'      => 'product',
			'posts_per_page' => 4,
			'post__not_in'   => array( get_the_ID() ),
			'tax_query'      => array(
				array(
					'taxonomy' => 'product_series',
					'field'    => 'term_id',
					'terms'    => wp_list_pluck( $series_terms, 'term_id' ),
				),
			),
		) );
		if ( $related->have_posts() ) :
			?>
			<section class="zone-b zone-b--alt section">
				<div class="container">
					<h2><?php esc_html_e( 'Related products', 'dazhen' ); ?></h2>
					<div class="grid grid--4">
						<?php
						while ( $related->have_posts() ) :
							$related->the_post();
							dazhen_product_card( get_the_ID() );
						endwhile;
						wp_reset_postdata();
						?>
					</div>
				</div>
			</section>
			<?php
		endif;
	}
	?>

	<?php get_template_part( 'template-parts/form-inquiry', null, array( 'product' => $model ? $model : get_the_title() ) ); ?>

	<?php
endwhile;

get_footer();
