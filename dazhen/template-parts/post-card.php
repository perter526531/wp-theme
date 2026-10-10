<?php
/**
 * Article card — blog lists (index, archives, search).
 *
 * Featured image (when set) + category badge + date + title + excerpt +
 * read link + estimated reading time. Designed for posts without media
 * as well (text card, matching the confirmed blog design).
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

$categories = get_the_category( get_the_ID() );
$category   = $categories[0] ?? null;
$minutes    = dazhen_reading_time( get_the_ID() );
?>
<article class="card-article" data-dz-reveal>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="card-article__media" href="<?php echo esc_url( (string) get_permalink() ); ?>" tabindex="-1" aria-hidden="true">
			<?php the_post_thumbnail( 'dazhen-card', array( 'alt' => esc_attr( (string) get_the_title() ) ) ); ?>
		</a>
	<?php endif; ?>

	<div class="card-article__meta">
		<time class="card-article__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
		<?php if ( $category ) : ?>
			<a class="card-article__cat" href="<?php echo esc_url( get_category_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a>
		<?php endif; ?>
	</div>

	<h3 class="card-article__title">
		<a href="<?php echo esc_url( (string) get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
	</h3>
	<p class="card-article__excerpt"><?php echo esc_html( wp_trim_words( (string) get_the_excerpt(), 24 ) ); ?></p>

	<div class="card-article__foot">
		<a class="card-article__more" href="<?php echo esc_url( (string) get_permalink() ); ?>"><?php esc_html_e( 'Read article →', 'dazhen' ); ?></a>
		<span class="card-article__minutes"><?php printf( esc_html__( '%d min', 'dazhen' ), (int) $minutes ); ?></span>
	</div>
</article>
