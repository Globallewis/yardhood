<?php
/**
 * A feature article: headline, dek, name tag, Top Three line, portrait, body, pull quote, video.
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
	$credit = get_post_meta( $id, '_yh_photo_credit', true );
	$tt     = get_the_terms( $id, 'yh_story_type' );
	$series = get_the_terms( $id, 'yh_series' );
	$kicker = ( $series && ! is_wp_error( $series ) ? $series[0]->name . ' · ' : '' ) . ( $tt && ! is_wp_error( $tt ) ? $tt[0]->name : 'Story' );
	$embed  = $video ? wp_oembed_get( $video, array( 'width' => 960 ) ) : '';
	?>
	<article class="yh-wrap-full yh-article">
		<header class="yh-article-head yh-plum">
			<div class="yh-container yh-article-grid">
				<div class="yh-article-title">
					<?php echo yh_t_kicker( $kicker ); // phpcs:ignore ?>
					<h1 class="yh-h1"><?php the_title(); ?></h1>
					<?php if ( $dek ) : ?><p class="yh-lede"><?php echo esc_html( $dek ); ?></p><?php endif; ?>
					<p class="yh-byline">
						<?php echo esc_html( implode( ' · ', array_filter( array( $name, $disc, $city ) ) ) ); ?>
						<span aria-hidden="true">·</span> <?php echo esc_html( yh_read_time( $id ) ); ?>
					</p>
					<?php echo yh_top_three_line( $id ); // phpcs:ignore ?>
					<?php if ( $embed ) : ?><a class="yh-btn yh-btn-gold" href="#interview">Watch the interview</a><?php endif; ?>
				</div>
				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="yh-portrait">
						<?php the_post_thumbnail( 'large', array( 'loading' => 'eager' ) ); ?>
						<?php if ( $name ) : ?><span class="yh-name-tag"><?php echo esc_html( $name ); ?><?php echo $disc ? '<small>' . esc_html( $disc ) . '</small>' : ''; ?></span><?php endif; ?>
						<?php if ( $credit ) : ?><figcaption><?php echo esc_html( $credit ); ?></figcaption><?php endif; ?>
					</figure>
				<?php endif; ?>
			</div>
		</header>
		<div class="yh-cream yh-section">
			<div class="yh-container yh-article-body">
				<div class="yh-prose yh-dropcap">
					<?php the_content(); ?>
				</div>
				<?php if ( $pull ) : ?>
					<blockquote class="yh-pull">&ldquo;<?php echo esc_html( $pull ); ?>&rdquo;<?php if ( $name ) : ?><cite><?php echo esc_html( $name ); ?></cite><?php endif; ?></blockquote>
				<?php endif; ?>
				<?php if ( $embed ) : ?>
					<section id="interview" class="yh-interview">
						<h2 class="yh-h2">The full conversation</h2>
						<div class="yh-embed"><?php echo $embed; // phpcs:ignore ?></div>
					</section>
				<?php endif; ?>
			</div>
		</div>
		<section class="yh-section yh-plum">
			<div class="yh-container yh-split">
				<div>
					<h2 class="yh-h2">Got a story worth knowing?</h2>
					<p>Artists, makers, business owners and neighbors. Send a short video or answer a few questions.</p>
				</div>
				<a class="yh-btn yh-btn-gold" href="<?php echo esc_url( yh_page_url( 'submit-your-story' ) ); ?>">Submit your story</a>
			</div>
		</section>
	</article>
	<?php
endwhile;
get_footer();
