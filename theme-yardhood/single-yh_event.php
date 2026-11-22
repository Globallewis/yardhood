<?php
/**
 * Public event page. Room link stays private; RSVP and Join live here.
 */
get_header();
while ( have_posts() ) :
	the_post();
	$id    = get_the_ID();
	$start = yh_event_start( $id );
	$end   = yh_event_end( $id );
	$lane  = get_post_meta( $id, '_yh_lane', true );
	$lanes = yh_lanes();
	$img   = has_post_thumbnail() ? get_the_post_thumbnail_url( $id, 'large' ) : yh_img( isset( $lanes[ $lane ] ) ? $lanes[ $lane ]['img'] : 'movie-screen' );
	$conf  = yh_is_confirmed( $id );
	$host  = get_post_meta( $id, '_yh_host', true );
	$watch = get_post_meta( $id, '_yh_watch', true );
	$gid   = (int) get_post_meta( $id, '_yh_group', true );
	$count = count( yh_rsvps( $id ) );
	?>
	<div class="yh-wrap-full">
		<section class="yh-feature-hero yh-feature-hero-short">
			<img src="<?php echo esc_url( $img ); ?>" alt="" fetchpriority="high">
			<span class="yh-feature-scrim" aria-hidden="true"></span>
			<div class="yh-container yh-feature-hero-text">
				<?php echo yh_t_kicker( yh_lane_label( $lane ) ); // phpcs:ignore ?>
				<?php if ( ! $conf ) : ?><span class="yh-badge-draft yh-badge-big">Draft lineup · not confirmed yet</span><?php endif; ?>
				<h1 class="yh-h1"><?php the_title(); ?></h1>
				<?php if ( $start ) : ?>
					<p class="yh-event-when"><?php echo esc_html( $start->format( 'l, F j' ) ); ?> · <?php echo esc_html( $start->format( 'g:i a' ) ); ?> Eastern</p>
				<?php endif; ?>
			</div>
		</section>

		<section class="yh-section yh-cream">
			<div class="yh-container yh-event-grid">
				<div class="yh-event-body yh-prose">
					<?php if ( has_excerpt() ) : ?><p class="yh-lede-dark"><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
					<?php the_content(); ?>
					<?php if ( in_array( $lane, array( 'movie-night', 'watchalong' ), true ) ) : ?>
						<div class="yh-leader-frame yh-leader-event"><?php echo yh_film_leader( $start ? 'Countdown starts ' . $start->format( 'g:i a' ) . ' Eastern' : 'Film begins shortly' ); // phpcs:ignore ?></div>
						<h3>How it works</h3>
						<ol>
							<li>RSVP here. You will get the Join button on this page a few minutes before start.</li>
							<li>Have your own copy ready. Countdown, then everybody presses play together.</li>
							<li>React in the thread, then stay after the credits to discuss it together.</li>
						</ol>
					<?php endif; ?>
				</div>
				<aside class="yh-event-card" id="rsvp">
					<dl>
						<?php if ( $start ) : ?>
							<dt>When</dt><dd><?php echo esc_html( $start->format( 'D, M j' ) . ', ' . $start->format( 'g:i a' ) . ' to ' . $end->format( 'g:i a' ) ); ?> ET</dd>
						<?php endif; ?>
						<?php if ( $host ) : ?><dt>Host</dt><dd><?php echo esc_html( $host ); ?></dd><?php endif; ?>
						<?php if ( $watch ) : ?><dt>Watch</dt><dd><?php echo esc_html( $watch ); ?></dd><?php endif; ?>
						<?php if ( $gid && function_exists( 'groups_get_group' ) ) : $g = groups_get_group( $gid ); ?>
							<dt>Space</dt><dd><a href="<?php echo esc_url( bp_get_group_permalink( $g ) ); ?>"><?php echo esc_html( $g->name ); ?></a></dd>
						<?php endif; ?>
						<?php if ( $count ) : ?><dt><?php echo $conf ? 'Going' : 'Interested'; ?></dt><dd><?php echo (int) $count; ?></dd><?php endif; ?>
					</dl>
					<?php echo yh_rsvp_form( $id ); // phpcs:ignore ?>
					<?php if ( $start && $conf ) : ?>
						<p class="yh-small"><a href="<?php echo esc_url( add_query_arg( 'yh_ics', $id, home_url( '/' ) ) ); ?>">Add to calendar</a></p>
					<?php endif; ?>
				</aside>
			</div>
		</section>
	</div>
	<?php
endwhile;
get_footer();
