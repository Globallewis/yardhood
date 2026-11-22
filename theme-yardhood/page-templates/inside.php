<?php
/**
 * Template Name: Inside the Issue
 */
get_header();
$toc = array(
	array( '01', 'The Cookout', 'Grills on, cameras on. Cook along live and plan your own.', 'cookout', '40% 50%', yh_space_url( 'the-cookout' ) ),
	array( '02', 'People Worth Knowing', 'Artists and neighbors on the turn that changed everything.', 'artist-studio', '72% 35%', get_post_type_archive_link( 'yh_story' ) ),
	array( '03', 'After the Credits', 'Movie nights, and the conversations that run long after.', 'movie-screen', '50% 40%', yh_page_url( 'movie-nights' ) ),
	array( '04', 'Sunday Dinner', 'Show us your plate. The recipes and the people behind them.', 'plate', '50% 50%', yh_space_url( 'sunday-dinner' ) ),
	array( '05', 'The Yard Guide', 'Every watch party, cookout and live room this month.', 'dinner-table', '50% 50%', get_post_type_archive_link( 'yh_event' ) ),
);
?>
<div class="yh-wrap-full">
	<section class="yh-inside yh-cream">
		<div class="yh-container yh-inside-grid">
			<div class="yh-inside-toc">
				<?php echo yh_t_kicker( 'The Winter Issue' ); // phpcs:ignore ?>
				<h1 class="yh-h1">Inside<br>This Issue</h1>
				<span class="yh-rule" aria-hidden="true"></span>
				<ol class="yh-contents">
					<?php foreach ( $toc as $t ) : ?>
						<li>
							<a href="<?php echo esc_url( $t[5] ); ?>">
								<span class="yh-contents-thumb"><img src="<?php echo esc_url( yh_img( $t[3] ) ); ?>" alt="" loading="lazy" style="object-position:<?php echo esc_attr( $t[4] ); ?>"></span>
								<span class="yh-contents-text"><span class="yh-contents-title"><?php echo esc_html( $t[1] ); ?></span><span class="yh-contents-dek"><?php echo esc_html( $t[2] ); ?></span></span>
								<span class="yh-contents-n" aria-hidden="true"><?php echo esc_html( $t[0] ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
			<article class="yh-editor-letter">
				<div class="yh-editor-photo"><img src="<?php echo esc_url( yh_img( 'cover' ) ); ?>" alt="Neighbors laughing around a fire pit under string lights at dusk." loading="lazy"></div>
				<div class="yh-editor-text">
					<?php echo yh_t_kicker( 'From the editor' ); // phpcs:ignore ?>
					<h2 class="yh-h2">A Space for Creatives, Thinkers, Doers &amp; Neighbors.</h2>
					<p>Yardhood started with a simple idea: the best conversations happen when people pull up a chair. This winter we are turning on the screen, setting the table and sitting down with the people building something worth knowing about.</p>
					<a class="yh-btn yh-btn-plum" href="<?php echo esc_url( get_post_type_archive_link( 'yh_story' ) ); ?>">Start reading</a>
				</div>
			</article>
		</div>
	</section>

	<section class="yh-banners" aria-label="Features">
		<a class="yh-banner" href="<?php echo esc_url( yh_space_url( 'sunday-dinner' ) ); ?>">
			<img src="<?php echo esc_url( yh_img( 'dinner-table' ) ); ?>" alt="" loading="lazy">
			<span class="yh-banner-scrim" aria-hidden="true"></span>
			<span class="yh-banner-text"><span class="yh-kicker">Sunday Dinner</span><span class="yh-banner-h">Real people. Good food.<br>Deeper conversations.</span><span class="yh-banner-cta">Show us your plate</span></span>
		</a>
		<a class="yh-banner" href="<?php echo esc_url( yh_page_url( 'movie-nights' ) ); ?>">
			<img src="<?php echo esc_url( yh_img( 'movie-screen' ) ); ?>" alt="" loading="lazy">
			<span class="yh-banner-scrim" aria-hidden="true"></span>
			<span class="yh-banner-text"><span class="yh-kicker">Movie Nights</span><span class="yh-banner-h">Watch.<br>Discuss together.</span><span class="yh-banner-cta">View the schedule</span></span>
		</a>
		<a class="yh-banner" href="<?php echo esc_url( yh_page_url( 'the-hood' ) ); ?>">
			<img src="<?php echo esc_url( yh_img( 'hood-street' ) ); ?>" alt="" loading="lazy">
			<span class="yh-banner-scrim" aria-hidden="true"></span>
			<span class="yh-banner-text"><span class="yh-kicker">Around the Hood</span><span class="yh-banner-h">The people, places<br>and ideas in our community.</span><span class="yh-banner-cta">Explore the Hood</span></span>
		</a>
	</section>
</div>
<?php
get_footer();
