<?php
/**
 * Template Name: Policy / Info page
 */
get_header();
while ( have_posts() ) :
	the_post();
	?>
	<div class="yh-wrap-full">
		<section class="yh-section yh-plum yh-page-top yh-policy-head">
			<div class="yh-container">
				<?php echo yh_t_kicker( 'Yardhood' ); // phpcs:ignore ?>
				<h1 class="yh-h1"><?php the_title(); ?></h1>
			</div>
		</section>
		<section class="yh-section yh-cream">
			<div class="yh-container">
				<div class="yh-prose yh-policy"><?php the_content(); ?></div>
			</div>
		</section>
	</div>
	<?php
endwhile;
get_footer();
