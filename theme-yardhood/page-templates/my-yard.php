<?php
/**
 * Template Name: Your Yard (member home)
 * Gatherings first: your RSVPs, this week's rooms, a prompt, then the magazine.
 */
if ( ! is_user_logged_in() ) {
	wp_safe_redirect( wp_login_url( get_permalink() ) );
	exit;
}
get_header();
$u      = wp_get_current_user();
$first  = $u->first_name ? $u->first_name : $u->display_name;
$events = function_exists( 'yh_upcoming_events' ) ? yh_upcoming_events( 6 ) : array();
$mine   = array();
foreach ( $events as $e ) {
	if ( function_exists( 'yh_user_has_rsvp' ) && yh_user_has_rsvp( $e->ID, $u->ID ) ) {
		$mine[] = $e;
	}
}
$next    = $mine ? $mine[0] : ( $events ? $events[0] : null );
$spaces  = function_exists( 'yh_official_spaces' ) ? yh_official_spaces() : array();
$prompts = array(
	array( 'Sunday Dinner', 'Show us your plate', 'Post a photo of what you cooked or ordered this week, and the story behind it.', yh_space_url( 'sunday-dinner' ), 'Share your plate', 'plate' ),
	array( 'Perspectives', 'Make your case', 'Pick a topic and write what you see. An editor reads every piece.', yh_page_url( 'write-a-perspective' ), 'Write a perspective', 'library' ),
	array( 'Stories', 'Tell us how you got here', 'When did you realize what you were meant to do with your life? Start there.', yh_page_url( 'submit-your-story' ), 'Submit your story', 'artist-studio' ),
);
$feature = $prompts[ (int) gmdate( 'W' ) % count( $prompts ) ];
$reads   = get_posts( array( 'post_type' => array( 'yh_story', 'yh_perspective' ), 'posts_per_page' => 3, 'post_status' => 'publish' ) );
?>
<div class="yh-wrap-full yh-home">
	<section class="yh-section yh-plum yh-page-top">
		<div class="yh-container yh-home-hero">
			<div>
				<?php echo yh_t_kicker( 'Your Yard' ); // phpcs:ignore ?>
				<h1 class="yh-h1">Welcome back, <span class="yh-gold-text"><?php echo esc_html( $first ); ?></span></h1>
				<p class="yh-sub">Here is what is on in the Yard this week.</p>
			</div>
			<?php if ( $next ) : $ns = yh_event_start( $next->ID ); ?>
				<a class="yh-next yh-home-next" href="<?php echo esc_url( get_permalink( $next ) ); ?>" data-start="<?php echo esc_attr( $ns ? $ns->getTimestamp() : 0 ); ?>">
					<span class="yh-next-k"><?php echo $mine ? 'You are going' : 'Next watch party'; ?></span>
					<span class="yh-next-t"><?php echo esc_html( get_the_title( $next ) ); ?></span>
					<span class="yh-next-w"><?php echo esc_html( $ns ? $ns->format( 'D, M j' ) . ' · ' . $ns->format( 'g:i a' ) . ' ET' : '' ); ?></span>
					<span class="yh-next-c" aria-live="off"></span>
				</a>
			<?php endif; ?>
		</div>
	</section>

	<section class="yh-section yh-cream">
		<div class="yh-container">
			<div class="yh-home-head">
				<h2 class="yh-h2">This week in the Yard</h2>
				<a class="yh-link-strong" href="<?php echo esc_url( get_post_type_archive_link( 'yh_event' ) ); ?>">Full Yard Guide</a>
			</div>
			<?php if ( $events ) : ?>
				<div class="yh-listings">
					<?php foreach ( array_slice( $events, 0, 4 ) as $e ) { echo yh_t_event_card( $e ); } // phpcs:ignore ?>
				</div>
			<?php else : ?>
				<p class="yh-empty">New gatherings are being added. Check back soon.</p>
			<?php endif; ?>
		</div>
	</section>

	<section class="yh-section yh-cream yh-home-rooms">
		<div class="yh-container">
			<div class="yh-home-head">
				<h2 class="yh-h2">Your rooms</h2>
				<a class="yh-link-strong" href="<?php echo esc_url( yh_page_url( 'the-hood' ) ); ?>">Find more in The Hood</a>
			</div>
			<div class="yh-room-row">
				<?php
				foreach ( $spaces as $s ) :
					$when = '';
					if ( function_exists( 'groups_get_id' ) ) {
						$gid = groups_get_id( $s['slug'] );
						$g   = $gid ? groups_get_group( $gid ) : null;
						if ( $g && ! empty( $g->last_activity ) ) {
							$ts   = strtotime( $g->last_activity . ' UTC' );
							$when = ( time() - $ts < DAY_IN_SECONDS ) ? 'Active today' : 'Active ' . human_time_diff( $ts ) . ' ago';
						}
					}
					?>
					<a class="yh-room" href="<?php echo esc_url( yh_space_url( $s['slug'] ) ); ?>" style="--tile-bg:<?php echo esc_attr( $s['bg'] ); ?>">
						<img src="<?php echo esc_url( yh_img( $s['img'] ) ); ?>" alt="" loading="lazy" style="object-position:<?php echo esc_attr( $s['pos'] ); ?>">
						<span class="yh-room-text"><span class="yh-room-name"><?php echo esc_html( $s['name'] ); ?></span><?php if ( $when ) : ?><span class="yh-room-when"><?php echo esc_html( $when ); ?></span><?php endif; ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="yh-section yh-plum">
		<div class="yh-container yh-home-prompt">
			<img src="<?php echo esc_url( yh_img( $feature[5] ) ); ?>" alt="" loading="lazy">
			<div>
				<?php echo yh_t_kicker( 'This week\'s prompt · ' . $feature[0] ); // phpcs:ignore ?>
				<h2 class="yh-h2"><?php echo esc_html( $feature[1] ); ?></h2>
				<p><?php echo esc_html( $feature[2] ); ?></p>
				<a class="yh-btn yh-btn-gold" href="<?php echo esc_url( $feature[3] ); ?>"><?php echo esc_html( $feature[4] ); ?></a>
			</div>
		</div>
	</section>

	<section class="yh-section yh-cream">
		<div class="yh-container">
			<div class="yh-home-head">
				<h2 class="yh-h2">From the magazine</h2>
				<a class="yh-link-strong" href="<?php echo esc_url( get_post_type_archive_link( 'yh_perspective' ) ); ?>">Perspectives</a>
			</div>
			<?php if ( $reads ) : ?>
				<div class="yh-persp-list">
					<?php foreach ( $reads as $r ) : $st = 'yh_story' === $r->post_type; ?>
						<a class="yh-persp-item" href="<?php echo esc_url( get_permalink( $r ) ); ?>">
							<span class="yh-card-kick"><?php echo esc_html( $st ? 'Story' : yh_perspective_label( $r->ID ) ); ?></span>
							<span class="yh-persp-title"><?php echo esc_html( get_the_title( $r ) ); ?></span>
							<?php $sub = $st ? get_post_meta( $r->ID, '_yh_dek', true ) : get_post_meta( $r->ID, '_yh_subtitle', true ); if ( $sub ) : ?><span class="yh-persp-sub"><?php echo esc_html( $sub ); ?></span><?php endif; ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p class="yh-empty">The first features and Perspectives are being written now. <a href="<?php echo esc_url( yh_page_url( 'write-a-perspective' ) ); ?>">Write one</a>.</p>
			<?php endif; ?>
			<p class="yh-home-feed"><a class="yh-link-strong" href="<?php echo esc_url( function_exists( 'bp_get_activity_directory_permalink' ) ? bp_get_activity_directory_permalink() : home_url( '/' ) ); ?>">See everything members are posting</a></p>
		</div>
	</section>
</div>
<?php
get_footer();
