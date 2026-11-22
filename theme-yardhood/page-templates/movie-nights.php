<?php
/**
 * Template Name: Movie Nights
 */
get_header();
$events = function_exists( 'yh_upcoming_events' ) ? yh_upcoming_events( 12, array( 'movie-night', 'watchalong', 'live-thread' ) ) : array();
$join   = function_exists( 'yh_join_url' ) ? yh_join_url( get_permalink() ) : wp_registration_url();
?>
<div class="yh-wrap-full">
	<section class="yh-feature-hero">
		<img src="<?php echo esc_url( yh_img( 'movie-screen' ) ); ?>" alt="A vintage film countdown glows on an outdoor screen at a backyard movie night." fetchpriority="high">
		<span class="yh-feature-scrim" aria-hidden="true"></span>
		<div class="yh-container yh-feature-hero-text">
			<?php echo yh_t_kicker( 'The Yard · Feature' ); // phpcs:ignore ?>
			<h1 class="yh-h1">Movie Nights.<br><span class="yh-gold">Real</span> Conversations.</h1>
			<p class="yh-lede">More than a watch party. Bring your own copy, press play with the room at 8 pm Eastern, and stay for the part everybody remembers: discussing it together.</p>
			<p class="yh-actions">
				<?php if ( is_user_logged_in() ) : ?>
					<a class="yh-btn yh-btn-gold" href="<?php echo esc_url( yh_space_url( 'movie-nights' ) ); ?>">Go to the Movie Nights space</a>
				<?php else : ?>
					<a class="yh-btn yh-btn-gold" href="<?php echo esc_url( $join ); ?>">Join a watch party</a>
				<?php endif; ?>
				<a class="yh-btn yh-btn-ghost-light" href="#schedule">See the winter lineup</a>
			</p>
		</div>
	</section>

	<section id="schedule" class="yh-section yh-cream">
		<div class="yh-container">
			<?php echo yh_t_kicker( 'The Yard Guide' ); // phpcs:ignore ?>
			<h2 class="yh-h2">The Winter Lineup</h2>
			<p class="yh-sub">Every movie starts at 8 pm Eastern. Bring your own copy from the service you already use.</p>
			<?php if ( $events ) : ?>
				<div class="yh-listings yh-listings-light">
					<?php foreach ( $events as $e ) { echo yh_t_event_card( $e ); } // phpcs:ignore ?>
				</div>
			<?php else : ?>
				<p class="yh-empty">The lineup is being finalized. It posts here and in the Yard Guide as soon as each night is confirmed.</p>
			<?php endif; ?>
		</div>
	</section>

	<section class="yh-section yh-plum">
		<div class="yh-container">
			<div class="yh-leader-row">
				<div class="yh-leader-frame"><?php echo yh_film_leader(); // phpcs:ignore ?></div>
				<div>
					<?php echo yh_t_kicker( 'At 8 pm Eastern' ); // phpcs:ignore ?>
					<h2 class="yh-h2">Three, two, one. Press play.</h2>
					<p class="yh-lede">The room counts down together on screen, then everybody starts the movie at the same moment. Same scene, same laugh, same gasp, wherever you are.</p>
				</div>
			</div>
			<h2 class="yh-h2">How a movie night works</h2>
			<div class="yh-steps">
				<div class="yh-step"><span class="yh-step-n">1</span><h3>Bring your own copy</h3><p>Stream it from the service you already have.</p></div>
				<div class="yh-step"><span class="yh-step-n">2</span><h3>Join the room at 8 pm</h3><p>RSVP on the event page. The Join button opens a few minutes before start. Countdown, then everybody presses play at once.</p></div>
				<div class="yh-step"><span class="yh-step-n">3</span><h3>Talk it through</h3><p>React live in the thread, then stay after the credits for the real conversation.</p></div>
			</div>
			<p class="yh-note"><strong>Featured Reactions are optional.</strong> Some nights include a reaction room. You only appear in it if you sign up, and you can always join without being recorded.</p>
		</div>
	</section>
</div>
<?php
get_footer();
