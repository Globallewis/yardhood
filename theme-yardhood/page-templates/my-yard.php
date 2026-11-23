<?php
/**
 * Template Name: Your Yard (member home)
 * Visual direction: "The Lights Are On" (Nov 23 handoff). Every module shows real data and leads to a real action.
 */
if ( ! is_user_logged_in() ) {
	wp_safe_redirect( wp_login_url( get_permalink() ) );
	exit;
}
get_header();
$u      = wp_get_current_user();
$uid    = $u->ID;
$first  = $u->first_name ? $u->first_name : $u->display_name;
$events = function_exists( 'yh_upcoming_events' ) ? yh_upcoming_events( 6 ) : array();
$now    = time();
$live   = null;
$mine   = array();
foreach ( $events as $e ) {
	$s = yh_event_start( $e->ID );
	$x = yh_event_end( $e->ID );
	if ( $s && $x && $s->getTimestamp() - 15 * MINUTE_IN_SECONDS <= $now && $now <= $x->getTimestamp() ) {
		$live = $live ? $live : $e;
	}
	if ( yh_user_has_rsvp( $e->ID, $uid ) ) {
		$mine[] = $e;
	}
}
$future = array_values( array_filter( $events, function ( $e ) use ( $now ) {
	$s = yh_event_start( $e->ID );
	return $s && $s->getTimestamp() > $now;
} ) );
$next   = $future ? $future[0] : null;

/* Spaces with their newest real post. */
$icons = array(
	'movie-nights'         => '<path d="M4 6h16v12H4z"/><path d="M10 9.5v5l4-2.5z"/>',
	'the-cookout'          => '<path d="M5 11h14a7 7 0 0 1-14 0z"/><path d="M9 4c0 1.5 1 1.5 1 3M13 4c0 1.5 1 1.5 1 3M8 18l-2 3M16 18l2 3"/>',
	'sunday-dinner'        => '<path d="M7 3v8M5 3v4a2 2 0 0 0 4 0V3M7 11v10"/><path d="M17 3c-2 2-2 6 0 8v10"/>',
	'artist-corner'        => '<path d="M12 3a9 9 0 0 0 0 18c1.5 0 2-1 1.5-2s0-2 1.5-2H18a3 3 0 0 0 3-3 9 9 0 0 0-9-11z"/><circle cx="7.5" cy="11" r="1"/><circle cx="12" cy="7.5" r="1"/><circle cx="16.5" cy="11" r="1"/>',
	'people-worth-knowing' => '<circle cx="9" cy="8" r="3"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><path d="M16 4.5a3 3 0 0 1 0 6M18 14c2 .8 3 3 3 6"/>',
	'life-skills'          => '<path d="M4 5h6a2 2 0 0 1 2 2v13a2 2 0 0 0-2-2H4z"/><path d="M20 5h-6a2 2 0 0 0-2 2v13a2 2 0 0 1 2-2h6z"/>',
);
$spaces = array();
foreach ( ( function_exists( 'yh_official_spaces' ) ? yh_official_spaces() : array() ) as $s ) {
	$s['latest'] = '';
	$s['when']   = '';
	$s['ts']     = 0;
	if ( function_exists( 'groups_get_id' ) ) {
		$gid = groups_get_id( $s['slug'] );
		if ( $gid ) {
			$g = groups_get_group( $gid );
			if ( ! empty( $g->last_activity ) ) {
				$s['ts']   = strtotime( $g->last_activity . ' UTC' );
				$s['when'] = ( $now - $s['ts'] < DAY_IN_SECONDS ) ? 'Active today' : 'Active ' . human_time_diff( $s['ts'] ) . ' ago';
			}
			if ( function_exists( 'bp_activity_get' ) ) {
				$a = bp_activity_get( array( 'filter' => array( 'object' => 'groups', 'primary_id' => $gid, 'action' => 'activity_update' ), 'per_page' => 1, 'display_comments' => false ) );
				if ( ! empty( $a['activities'][0] ) ) {
					$txt         = preg_replace( '/^\s*Welcome to [^.]+\.\s*/i', '', wp_strip_all_tags( $a['activities'][0]->content ) );
					$s['latest'] = wp_trim_words( $txt, 12, '…' );
				}
			}
		}
	}
	$spaces[] = $s;
}
$recent = $spaces;
usort( $recent, function ( $a, $b ) {
	return $b['ts'] - $a['ts'];
} );

/* Magazine + Perspectives. */
$mag   = get_posts( array( 'post_type' => array( 'yh_story', 'yh_perspective' ), 'posts_per_page' => 3, 'post_status' => 'publish' ) );
$voice = get_posts( array( 'post_type' => 'yh_perspective', 'posts_per_page' => 3, 'post_status' => 'publish' ) );

/* Contribution links. */
$top3_url = function_exists( 'bp_loggedin_user_domain' ) ? trailingslashit( bp_loggedin_user_domain() ) . 'profile/edit/' : '';
if ( $top3_url && function_exists( 'xprofile_get_field_id_from_name' ) ) {
	$fid = xprofile_get_field_id_from_name( 'The Top Three' );
	$f   = $fid ? xprofile_get_field( $fid ) : null;
	if ( $f ) {
		$top3_url .= 'group/' . (int) $f->group_id . '/';
	}
}
$table = array(
	array( 'Show Us Your Plate', 'A meal, a recipe, or the story behind it', yh_space_url( 'sunday-dinner' ), '<path d="M7 3v8M5 3v4a2 2 0 0 0 4 0V3M7 11v10"/><path d="M17 3c-2 2-2 6 0 8v10"/>' ),
	array( 'Drop a Track', 'A song we need in the rotation', yh_space_url( 'the-cookout' ), '<path d="M9 18V5l11-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="17" cy="16" r="3"/>' ),
	array( 'Your Top Three', 'The three things that matter most to you', $top3_url, '<path d="M9 6h11M9 12h11M9 18h11"/><path d="M4 6h1M4 12h1M4 18h1"/>' ),
);
$sundays = array(
	array( 'Go nearby', 'Visit a local spot you have been meaning to support, then bring back a photo, a thought or a recommendation.' ),
	array( 'Ask for the recipe', 'Call an elder and ask for one recipe that has never been written down. Share it if they say yes.' ),
	array( 'One small audit', 'Look at one subscription or bill you pay every month and decide if it still earns its place.' ),
	array( 'Send the note', 'Text someone who helped you get where you are and tell them exactly what they did.' ),
);
$sunday = $sundays[ (int) gmdate( 'W' ) % count( $sundays ) ];
$resp_n = (int) get_comments( array( 'user_id' => $uid, 'count' => true, 'status' => 'approve' ) );
$grp_n  = function_exists( 'groups_total_groups_for_user' ) ? (int) groups_total_groups_for_user( $uid ) : 0;
$svg    = function ( $paths, $size = 22 ) {
	return '<svg viewBox="0 0 24 24" width="' . $size . '" height="' . $size . '" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths . '</svg>';
};
?>
<div class="yh-wrap-full yh-yard2">
	<section class="yh-y2-hero">
		<img class="yh-y2-hero-img" src="<?php echo esc_url( yh_img( 'dinner-table' ) ); ?>" alt="" fetchpriority="high">
		<span class="yh-y2-hero-scrim" aria-hidden="true"></span>
		<div class="yh-y2-hero-text">
			<span class="yh-y2-kick">The Yardhood · Welcome back, <?php echo esc_html( $first ); ?></span>
			<h1 class="yh-y2-title">The Lights Are On</h1>
			<p class="yh-y2-sub">A cultural home for our stories, tastes, ideas and people, in good company.</p>
			<div class="yh-y2-ctas">
				<a class="yh-btn yh-btn-gold" href="#this-week">Step into this week's gatherings <span aria-hidden="true">&rarr;</span></a>
				<a class="yh-btn yh-y2-ghost" href="<?php echo esc_url( get_post_type_archive_link( 'yh_perspective' ) ); ?>">Read the magazine</a>
			</div>
		</div>
	</section>

	<div class="yh-y2-grid">
		<main class="yh-y2-main">
			<section id="this-week" class="yh-y2-sec">
				<header class="yh-y2-head">
					<h2>This Week in the Yard</h2>
					<p>What is open and where the conversation is.</p>
					<a href="<?php echo esc_url( yh_page_url( 'the-yard' ) ); ?>">All spaces <span aria-hidden="true">&rarr;</span></a>
				</header>
				<div class="yh-y2-spaces">
					<?php foreach ( $spaces as $s ) : ?>
						<a class="yh-y2-space" href="<?php echo esc_url( yh_space_url( $s['slug'] ) ); ?>">
							<span class="yh-y2-space-img"><img src="<?php echo esc_url( yh_img( $s['img'] ) ); ?>" alt="" loading="lazy" style="object-position:<?php echo esc_attr( $s['pos'] ); ?>"></span>
							<span class="yh-y2-space-body">
								<span class="yh-y2-space-k"><?php echo $svg( isset( $icons[ $s['slug'] ] ) ? $icons[ $s['slug'] ] : '<circle cx="12" cy="12" r="8"/>', 16 ); // phpcs:ignore ?><?php echo esc_html( $s['name'] ); ?></span>
								<span class="yh-y2-space-t"><?php echo esc_html( $s['latest'] ? $s['latest'] : $s['dek'] ); ?></span>
								<span class="yh-y2-space-f"><span><?php echo esc_html( $s['when'] ? $s['when'] : 'Open' ); ?></span><span class="yh-y2-pill">Go in</span></span>
							</span>
						</a>
					<?php endforeach; ?>
				</div>
			</section>

			<?php if ( $mag ) : ?>
				<section class="yh-y2-sec">
					<header class="yh-y2-head">
						<h2>From the Magazine</h2>
						<p>Culture, creativity and the life we are building together.</p>
						<a href="<?php echo esc_url( get_post_type_archive_link( 'yh_story' ) ); ?>">Explore the magazine <span aria-hidden="true">&rarr;</span></a>
					</header>
					<div class="yh-y2-mag">
						<?php foreach ( $mag as $i => $p ) : $b = yh_t_feature_bits( $p->ID ); ?>
							<a class="yh-y2-mag-item<?php echo 0 === $i ? ' is-big' : ''; ?>" href="<?php echo esc_url( get_permalink( $p ) ); ?>">
								<img src="<?php echo esc_url( $b['img'] ); ?>" alt="" loading="lazy">
								<span class="yh-y2-mag-text">
									<span class="yh-y2-chip"><?php echo esc_html( $b['topic'] ? $b['topic'] : $b['label'] ); ?></span>
									<span class="yh-y2-mag-t"><?php echo esc_html( get_the_title( $p ) ); ?></span>
									<?php if ( $b['sub'] ) : ?><span class="yh-y2-mag-s"><?php echo esc_html( $b['sub'] ); ?></span><?php endif; ?>
								</span>
							</a>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<section class="yh-y2-sec yh-y2-split">
				<div>
					<header class="yh-y2-head">
						<h2>Perspectives</h2>
						<a href="<?php echo esc_url( get_post_type_archive_link( 'yh_perspective' ) ); ?>">All perspectives <span aria-hidden="true">&rarr;</span></a>
					</header>
					<?php if ( $voice ) : ?>
						<div class="yh-y2-voices">
							<?php foreach ( $voice as $p ) : $b = yh_t_feature_bits( $p->ID ); ?>
								<a class="yh-y2-voice" href="<?php echo esc_url( get_permalink( $p ) ); ?>">
									<img src="<?php echo esc_url( $b['img'] ); ?>" alt="" loading="lazy">
									<span><span class="yh-y2-voice-n"><?php echo esc_html( $b['by'] ); ?></span><span class="yh-y2-voice-t"><?php echo esc_html( get_the_title( $p ) ); ?></span><span class="yh-y2-voice-l"><?php echo esc_html( $b['label'] ); ?></span></span>
								</a>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<p class="yh-empty">Be the first voice. <a href="<?php echo esc_url( yh_page_url( 'write-a-perspective' ) ); ?>">Write a perspective</a>.</p>
					<?php endif; ?>
				</div>
				<a class="yh-y2-sunday" href="<?php echo esc_url( yh_space_url( 'sunday-dinner' ) ); ?>">
					<span class="yh-y2-sunday-k"><?php echo $svg( '<path d="M5 19c8 0 14-6 14-14C11 5 5 11 5 19z"/><path d="M5 19l7-7"/>', 18 ); // phpcs:ignore ?>Try This Before Sunday</span>
					<span class="yh-y2-sunday-t"><?php echo esc_html( $sunday[0] ); ?></span>
					<span class="yh-y2-sunday-p"><?php echo esc_html( $sunday[1] ); ?></span>
					<span class="yh-y2-sunday-go">Share how it went at Sunday Dinner <span aria-hidden="true">&rarr;</span></span>
				</a>
			</section>
		</main>

		<aside class="yh-y2-rail">
			<section class="yh-y2-card yh-y2-porch">
				<h2>Porch Light</h2>
				<?php if ( $live ) : ?>
					<p class="yh-y2-porch-on"><span class="yh-y2-glow" aria-hidden="true"></span>The porch light is on. <a href="<?php echo esc_url( get_permalink( $live ) ); ?>"><?php echo esc_html( get_the_title( $live ) ); ?></a> is happening now.</p>
				<?php elseif ( $next ) : $ns = yh_event_start( $next->ID ); ?>
					<p class="yh-y2-porch-next">Next up: <a href="<?php echo esc_url( get_permalink( $next ) ); ?>"><?php echo esc_html( get_the_title( $next ) ); ?></a><br><span><?php echo esc_html( $ns->format( 'l, M j' ) . ' · ' . $ns->format( 'g:i a' ) . ' ET' ); ?></span></p>
				<?php endif; ?>
				<ul class="yh-y2-porch-list">
					<?php foreach ( array_slice( array_filter( $recent, function ( $s ) { return $s['ts']; } ), 0, 3 ) as $s ) : ?>
						<li><a href="<?php echo esc_url( yh_space_url( $s['slug'] ) ); ?>"><?php echo esc_html( $s['name'] ); ?></a><span><?php echo esc_html( $s['when'] ); ?></span></li>
					<?php endforeach; ?>
				</ul>
			</section>

			<section class="yh-y2-card">
				<h2>Bring Something to the Table</h2>
				<p class="yh-y2-card-sub">Share, recommend, reflect or start a conversation.</p>
				<div class="yh-y2-table">
					<?php foreach ( $table as $t ) : if ( ! $t[2] ) { continue; } ?>
						<a class="yh-y2-act" href="<?php echo esc_url( $t[2] ); ?>"><?php echo $svg( $t[3], 26 ); // phpcs:ignore ?><span class="yh-y2-act-t"><?php echo esc_html( $t[0] ); ?></span><span class="yh-y2-act-s"><?php echo esc_html( $t[1] ); ?></span></a>
					<?php endforeach; ?>
				</div>
			</section>

			<section class="yh-y2-card">
				<h2>My Yardhood</h2>
				<ul class="yh-y2-mine">
					<li><a href="#this-week"><?php echo $svg( '<rect x="4" y="5" width="16" height="15" rx="2"/><path d="M4 10h16M9 3v4M15 3v4"/>', 18 ); // phpcs:ignore ?>Gatherings you are going to <b><?php echo count( $mine ); ?></b></a></li>
					<li><a href="<?php echo esc_url( function_exists( 'bp_loggedin_user_domain' ) ? trailingslashit( bp_loggedin_user_domain() ) . 'groups/' : '#' ); ?>"><?php echo $svg( '<circle cx="9" cy="8" r="3"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/>', 18 ); // phpcs:ignore ?>Your rooms <b><?php echo (int) $grp_n; ?></b></a></li>
					<li><a href="<?php echo esc_url( get_post_type_archive_link( 'yh_perspective' ) ); ?>"><?php echo $svg( '<path d="M4 5h16v11H8l-4 4z"/>', 18 ); // phpcs:ignore ?>Your responses <b><?php echo (int) $resp_n; ?></b></a></li>
				</ul>
				<?php if ( $future ) : ?>
					<div class="yh-y2-events">
						<?php foreach ( array_slice( $future, 0, 3 ) as $e ) : $es = yh_event_start( $e->ID ); $going = yh_user_has_rsvp( $e->ID, $uid ); ?>
							<a class="yh-y2-event" href="<?php echo esc_url( get_permalink( $e ) ); ?>">
								<span class="yh-y2-event-d"><b><?php echo esc_html( $es->format( 'j' ) ); ?></b><?php echo esc_html( $es->format( 'M' ) ); ?></span>
								<span class="yh-y2-event-t"><?php echo esc_html( get_the_title( $e ) ); ?><span><?php echo esc_html( $es->format( 'D · g:i a' ) . ' ET' ); ?></span></span>
								<span class="yh-y2-pill<?php echo $going ? ' is-on' : ''; ?>"><?php echo $going ? 'Going' : 'RSVP'; ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</section>
		</aside>
	</div>
</div>
<?php
get_footer();
