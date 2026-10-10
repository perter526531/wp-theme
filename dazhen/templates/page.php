<?php
/**
 * Static page.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<header class="zone-a page-hero">
		<div class="container">
			<?php dazhen_breadcrumbs(); ?>
			<h1 class="page-hero__title"><?php echo esc_html( get_the_title() ); ?></h1>
		</div>
	</header>

	<section class="zone-b section">
		<div class="container container--narrow">
			<div class="prose"><?php the_content(); ?></div>
		</div>
	</section>
	<?php
endwhile;

get_footer();
