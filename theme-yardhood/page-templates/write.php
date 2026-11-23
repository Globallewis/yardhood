<?php
/**
 * Template Name: Write a Perspective
 */
get_header();
?>
<div class="yh-wrap-full">
	<section class="yh-section yh-cream yh-page-top" id="write">
		<div class="yh-container yh-submit">
			<div class="yh-submit-intro">
				<?php echo yh_t_kicker( 'Perspectives' ); // phpcs:ignore ?>
				<h1 class="yh-h1">Say what <span class="yh-gold-text">you see</span></h1>
				<p class="yh-lede">Perspectives is the column where members make their case. Pick a topic, give it a headline, and write it the way you would say it at the table.</p>
				<img src="<?php echo esc_url( yh_img( 'library' ) ); ?>" alt="" loading="lazy" class="yh-submit-photo">
				<p class="yh-help">Every piece is read by an editor before it runs. Thoughtful disagreement is welcome. Attacks on people are not. <a href="<?php echo esc_url( yh_page_url( 'community-guidelines' ) ); ?>">Community Guidelines</a>.</p>
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
