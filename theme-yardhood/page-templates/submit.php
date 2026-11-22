<?php
/**
 * Template Name: Submit Your Story
 */
get_header();
?>
<div class="yh-wrap-full">
	<section class="yh-section yh-cream yh-page-top" id="submit">
		<div class="yh-container yh-submit">
			<div class="yh-submit-intro">
				<?php echo yh_t_kicker( 'Artists, careers, entrepreneurs, neighbors' ); // phpcs:ignore ?>
				<h1 class="yh-h1">Submit Your <span class="yh-gold-text">Story</span></h1>
				<p class="yh-lede">Got a story worth knowing? Answer a few questions, add a photo or a short video, and an editor will turn it into a feature with you.</p>
				<img src="<?php echo esc_url( yh_img( 'artist-studio' ) ); ?>" alt="" loading="lazy" class="yh-submit-photo">
				<p class="yh-help">Prefer to talk it through? Check the box for a Zoom conversation and we will reach out if your story is selected. <a href="<?php echo esc_url( yh_page_url( 'story-permissions' ) ); ?>">How permissions work</a>.</p>
			</div>
			<div class="yh-submit-form">
				<?php
				while ( have_posts() ) {
					the_post();
					the_content();
				}
				?>
			</div>
		</div>
	</section>
</div>
<?php
get_footer();
