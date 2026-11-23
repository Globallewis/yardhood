<?php
/**
 * A Perspective: photo hero, essay with side rail, responses, keep reading.
 */
get_header();
while ( have_posts() ) :
	the_post();
	$id    = get_the_ID();
	$sub   = get_post_meta( $id, '_yh_subtitle', true );
	$bio   = get_post_meta( $id, '_yh_bio', true );
	$pt    = get_the_terms( $id, 'yh_pillar' );
	$topic = $pt && ! is_wp_error( $pt ) ? $pt[0] : null;
	$write = yh_page_url( 'write-a-perspective' );
	$by    = yh_perspective_byline( $id );
	$label = yh_perspective_label( $id );
	?>
	<article class="yh-wrap-full yh-article yh-persp">
		<?php echo yh_t_article_hero( $id, $label . ( $topic ? ' · ' . $topic->name : '' ), $sub, 'By ' . $by . ' · ' . yh_read_time( $id ) ); // phpcs:ignore ?>
		<div class="yh-cream yh-section yh-abody-wrap">
			<div class="yh-container yh-abody">
				<div class="yh-abody-main">
					<div class="yh-prose yh-dropcap"><?php the_content(); ?></div>
					<p class="yh-help yh-persp-note">Perspectives are the views of their writers, not Yardhood.</p>
					<?php echo yh_t_responses( $id ); // phpcs:ignore ?>
				</div>
				<?php echo yh_t_article_rail( $id, $by, $label, $bio, $topic, $topic ? add_query_arg( 'pillar', $topic->slug, $write ) : $write, 'Write your own take' ); // phpcs:ignore ?>
			</div>
		</div>
		<?php echo yh_t_more_reads( $id, array( 'yh_perspective', 'yh_story' ) ); // phpcs:ignore ?>
	</article>
	<?php
endwhile;
get_footer();
