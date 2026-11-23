<?php
/**
 * A Story feature: photo hero, feature text with side rail, pull quote, video, responses, keep reading.
 */
get_header();
while ( have_posts() ) :
	the_post();
	$id     = get_the_ID();
	$name   = get_post_meta( $id, '_yh_name', true );
	$disc   = get_post_meta( $id, '_yh_discipline', true );
	$city   = get_post_meta( $id, '_yh_city', true );
	$dek    = get_post_meta( $id, '_yh_dek', true );
	$pull   = get_post_meta( $id, '_yh_pull', true );
	$video  = get_post_meta( $id, '_yh_video', true );
	$tt     = get_the_terms( $id, 'yh_story_type' );
	$series = get_the_terms( $id, 'yh_series' );
	$pt     = get_the_terms( $id, 'yh_pillar' );
	$topic  = $pt && ! is_wp_error( $pt ) ? $pt[0] : null;
	$kicker = ( $series && ! is_wp_error( $series ) ? $series[0]->name . ' · ' : '' ) . ( $tt && ! is_wp_error( $tt ) ? $tt[0]->name : 'Story' );
	$embed  = $video ? wp_oembed_get( $video, array( 'width' => 960 ) ) : '';
	$who    = implode( ' · ', array_filter( array( $name, $disc, $city ) ) );
	?>
	<article class="yh-wrap-full yh-article">
		<?php echo yh_t_article_hero( $id, $kicker, $dek, $who . ' · ' . yh_read_time( $id ) ); // phpcs:ignore ?>
		<div class="yh-cream yh-section yh-abody-wrap">
			<div class="yh-container yh-abody">
				<div class="yh-abody-main">
					<?php echo yh_top_three_line( $id ); // phpcs:ignore ?>
					<?php if ( $pull ) : ?>
						<blockquote class="yh-pull yh-pull-top">&ldquo;<?php echo esc_html( $pull ); ?>&rdquo;<?php if ( $name ) : ?><cite><?php echo esc_html( $name ); ?></cite><?php endif; ?></blockquote>
					<?php endif; ?>
					<div class="yh-prose yh-dropcap"><?php the_content(); ?></div>
					<?php if ( $embed ) : ?>
						<section id="interview" class="yh-interview">
							<h2 class="yh-h2">The full conversation</h2>
							<div class="yh-embed"><?php echo $embed; // phpcs:ignore ?></div>
						</section>
					<?php endif; ?>
					<?php echo yh_t_responses( $id ); // phpcs:ignore ?>
				</div>
				<?php echo yh_t_article_rail( $id, $name ? $name : 'Yardhood', $tt && ! is_wp_error( $tt ) ? $tt[0]->name : 'Story', $disc ? $disc . ( $city ? ', ' . $city : '' ) : '', $topic, yh_page_url( 'submit-your-story' ), 'Tell your story' ); // phpcs:ignore ?>
			</div>
		</div>
		<?php echo yh_t_more_reads( $id, array( 'yh_story', 'yh_perspective' ) ); // phpcs:ignore ?>
	</article>
	<?php
endwhile;
get_footer();
