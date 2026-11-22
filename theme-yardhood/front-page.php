<?php
/**
 * The Cover: Winter Issue entrance.
 */
get_header();
$join   = function_exists( 'yh_join_url' ) ? yh_join_url( home_url( '/' ) ) : wp_registration_url();
$member = is_user_logged_in();
?>
<div class="yh-wrap-full">
	<section class="yh-cover" aria-label="The Winter Issue cover">
		<img class="yh-cover-photo" src="<?php echo esc_url( yh_img( 'cover' ) ); ?>" alt="A woman in a brown faux-fur coat and cream knit sits wrapped in a blanket at a dusk backyard movie night, with string lights, a fire pit and neighbors laughing behind her." width="1122" height="1402" fetchpriority="high">
		<div class="yh-cover-scrim" aria-hidden="true"></div>
		<h1 class="yh-masthead">YARDHOOD</h1>
		<img class="yh-cover-hair" src="<?php echo esc_url( yh_img( 'hair' ) ); ?>" alt="" aria-hidden="true" width="1122" height="1402">
		<div class="yh-cover-inner">
			<span class="yh-tagline">The Community Culture Magazine</span>
			<div class="yh-issue-box"><span>The<br>Winter<br>Issue</span></div>
			<span class="yh-issue-mobile">The Winter Issue</span>
			<div class="yh-cover-lines">
				<div class="yh-cover-left">
					<a class="yh-coverline yh-coverline-lead" href="<?php echo esc_url( yh_page_url( 'movie-nights' ) ); ?>">Movie Nights.<br><span class="yh-gold">Real</span> Conversations.</a>
					<span class="yh-rule" aria-hidden="true"></span>
					<a class="yh-coverline yh-coverline-sub" href="<?php echo esc_url( get_post_type_archive_link( 'yh_story' ) ); ?>">Artists, Stories &amp; People Worth Knowing</a>
					<a class="yh-coverline yh-coverline-plus" href="<?php echo esc_url( yh_space_url( 'sunday-dinner' ) ); ?>">Plus: Sunday Dinner · Show Us Your Plate</a>
					<a class="yh-btn yh-btn-gold yh-cover-open" href="<?php echo esc_url( yh_page_url( 'inside-the-issue' ) ); ?>">Open the issue</a>
				</div>
				<div class="yh-cover-right">
					<?php
					$next = function_exists( 'yh_upcoming_events' ) ? yh_upcoming_events( 1 ) : array();
					if ( $next ) :
						$ns = yh_event_start( $next[0]->ID );
						?>
						<a class="yh-next" href="<?php echo esc_url( get_permalink( $next[0] ) ); ?>" data-start="<?php echo esc_attr( $ns ? $ns->getTimestamp() : 0 ); ?>">
							<span class="yh-next-k">Next watch party</span>
							<span class="yh-next-t"><?php echo esc_html( get_the_title( $next[0] ) ); ?></span>
							<span class="yh-next-w"><?php echo esc_html( $ns ? $ns->format( 'D, M j' ) . ' · ' . $ns->format( 'g:i a' ) . ' ET' : '' ); ?></span>
							<span class="yh-next-c" aria-live="off"></span>
						</a>
					<?php endif; ?>
					<?php if ( $member ) : ?>
						<a class="yh-join-big" href="<?php echo esc_url( get_post_type_archive_link( 'yh_event' ) ); ?>">Yard Guide</a>
						<span class="yh-join-url">What is on this week</span>
					<?php else : ?>
						<a class="yh-join-big" href="<?php echo esc_url( $join ); ?>">Join Free</a>
						<span class="yh-join-url">yardhood.com</span>
					<?php endif; ?>
				</div>
			</div>
			<p class="yh-cover-credit">Your art could be next month's cover. <a href="<?php echo esc_url( yh_page_url( 'submit-your-story' ) ); ?>">Submit at yardhood.com.</a></p>
		</div>
	</section>

	<section class="yh-toc yh-cream" aria-labelledby="yh-inside">
		<div class="yh-container yh-toc-grid">
			<div>
				<?php echo yh_t_kicker( 'This issue' ); // phpcs:ignore ?>
				<h2 id="yh-inside" class="yh-h2">Inside the Winter Issue</h2>
				<a class="yh-link-strong" href="<?php echo esc_url( yh_page_url( 'inside-the-issue' ) ); ?>">Open the issue</a>
			</div>
			<a class="yh-toc-item" href="<?php echo esc_url( yh_page_url( 'movie-nights' ) ); ?>"><span class="yh-toc-n">01</span><span class="yh-toc-t">Movie Nights</span><span class="yh-toc-d">The winter lineup, how watch parties work, and where to bring your opinions.</span></a>
			<a class="yh-toc-item" href="<?php echo esc_url( get_post_type_archive_link( 'yh_story' ) ); ?>"><span class="yh-toc-n">02</span><span class="yh-toc-t">People Worth Knowing</span><span class="yh-toc-d">Artists and neighbors on where they started and what they are building.</span></a>
			<a class="yh-toc-item" href="<?php echo esc_url( yh_space_url( 'sunday-dinner' ) ); ?>"><span class="yh-toc-n">03</span><span class="yh-toc-t">Sunday Dinner</span><span class="yh-toc-d">Show us your plate. Recipes, traditions and the stories behind them.</span></a>
		</div>
	</section>

	<section class="yh-section yh-cream yh-people" aria-labelledby="yh-people">
		<div class="yh-container">
			<div class="yh-section-head">
				<div>
					<?php echo yh_t_kicker( 'Find your people' ); // phpcs:ignore ?>
					<h2 id="yh-people" class="yh-h2">Pull up a chair.</h2>
				</div>
			</div>
			<div class="yh-doors">
				<a class="yh-door" href="<?php echo esc_url( yh_page_url( 'the-yard' ) ); ?>">
					<img src="<?php echo esc_url( yh_img( 'cookout' ) ); ?>" alt="" loading="lazy">
					<span class="yh-tile-scrim" aria-hidden="true"></span>
					<span class="yh-door-text"><span class="yh-tile-kick">Official spaces</span><span class="yh-door-name">The Yard</span><span class="yh-tile-dek">Movie Nights, The Cookout, Sunday Dinner, Artist Corner and more. Free to join.</span><span class="yh-banner-cta">Step into the Yard</span></span>
				</a>
				<a class="yh-door" href="<?php echo esc_url( yh_page_url( 'the-hood' ) ); ?>">
					<img src="<?php echo esc_url( yh_img( 'hood-street' ) ); ?>" alt="" loading="lazy">
					<span class="yh-tile-scrim" aria-hidden="true"></span>
					<span class="yh-door-text"><span class="yh-tile-kick">Member communities</span><span class="yh-door-name">The Hood</span><span class="yh-tile-dek">Groups started by members. Find yours, or host your own.</span><span class="yh-banner-cta">Walk the Hood</span></span>
				</a>
			</div>
		</div>
	</section>

	<?php
	$upcoming = function_exists( 'yh_upcoming_events' ) ? yh_upcoming_events( 4 ) : array();
	?>
	<section class="yh-section yh-plum" aria-labelledby="yh-guide">
		<div class="yh-container">
			<div class="yh-section-head">
				<div>
					<?php echo yh_t_kicker( 'The Yard Guide' ); // phpcs:ignore ?>
					<h2 id="yh-guide" class="yh-h2">Coming up</h2>
				</div>
				<a class="yh-link-strong" href="<?php echo esc_url( get_post_type_archive_link( 'yh_event' ) ); ?>">See the full guide</a>
			</div>
			<?php if ( $upcoming ) : ?>
				<div class="yh-listings">
					<?php foreach ( $upcoming as $e ) { echo yh_t_event_card( $e ); } // phpcs:ignore ?>
				</div>
			<?php else : ?>
				<p class="yh-empty">The winter lineup posts here soon. <?php if ( ! $member ) : ?><a href="<?php echo esc_url( $join ); ?>">Join free</a> to get the first look.<?php endif; ?></p>
			<?php endif; ?>
		</div>
	</section>
</div>
<?php
get_footer();
