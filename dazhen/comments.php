<?php
/**
 * The comments template — discussion under blog articles.
 *
 * Design: B-zone cards, quiet typography, the amber send button.
 *
 * @package Dazhen
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
        return;
}
?>

<section class="zone-b section comments-section" id="comments">
        <div class="container container--narrow">
                <?php if ( have_comments() ) : ?>
                        <h2 class="comments-section__title">
                                <?php
                                printf(
                                        /* translators: %s: comment count */
                                        esc_html( _n( '%s comment', '%s comments', get_comments_number(), 'dazhen' ) ),
                                        esc_html( number_format_i18n( get_comments_number() ) )
                                );
                                ?>
                        </h2>

                        <ol class="comment-list">
                                <?php
                                wp_list_comments( array(
                                        'style'       => 'ol',
                                        'short_ping'  => true,
                                        'avatar_size' => 48,
                                ) );
                                ?>
                        </ol>

                        <?php
                        the_comments_pagination( array(
                                'mid_size'  => 2,
                                'prev_text' => '← ' . __( 'Previous', 'dazhen' ),
                                'next_text' => __( 'Next', 'dazhen' ) . ' →',
                        ) );
                        ?>
                <?php endif; ?>

                <?php if ( ! comments_open() && get_comments_number() ) : ?>
                        <p class="comments-section__closed"><?php esc_html_e( 'Comments are closed.', 'dazhen' ); ?></p>
                <?php endif; ?>

                <?php
                comment_form( array(
                        'title_reply'         => __( 'Leave a comment', 'dazhen' ),
                        'title_reply_before'  => '<h2 class="comments-section__title comment-reply-title">',
                        'title_reply_after'   => '</h2>',
                        'label_submit'        => __( 'Post comment', 'dazhen' ),
                        'class_submit'        => 'btn btn--cta dz-comment-submit',
                        'comment_notes_before' => '',
                        'comment_field'       => '<p class="dz-comment-field"><label for="comment">' . esc_html__( 'Comment *', 'dazhen' ) . '</label><textarea id="comment" name="comment" rows="5" required></textarea></p>',
                ) );
                ?>
        </div>
</section>
