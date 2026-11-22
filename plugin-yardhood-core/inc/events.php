<?php
/**
 * Yard Guide events: public event pages, RSVP, join-room tracking, calendar file.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function yh_register_events_cpt() {
	register_post_type( 'yh_event', array(
		'labels'       => array(
			'name'          => 'Yard Guide',
			'singular_name' => 'Event',
			'add_new_item'  => 'Add event',
			'edit_item'     => 'Edit event',
			'all_items'     => 'All events',
			'menu_name'     => 'Yard Guide',
		),
		'public'       => true,
		'has_archive'  => 'yard-guide',
		'rewrite'      => array( 'slug' => 'events', 'with_front' => false ),
		'menu_icon'    => 'dashicons-calendar-alt',
		'menu_position'=> 21,
		'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
		'show_in_rest' => true,
	) );
}
add_action( 'init', 'yh_register_events_cpt' );

/* ---------- Admin meta box ---------- */

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'yh_event_details', 'Event details', 'yh_event_metabox', 'yh_event', 'normal', 'high' );
	add_meta_box( 'yh_event_people', 'RSVPs and room joins', 'yh_event_people_box', 'yh_event', 'side' );
} );

function yh_event_metabox( $post ) {
	wp_nonce_field( 'yh_event_save', 'yh_event_nonce' );
	$m = function ( $k, $d = '' ) use ( $post ) {
		$v = get_post_meta( $post->ID, $k, true );
		return '' === $v ? $d : $v;
	};
	$groups = array();
	if ( function_exists( 'groups_get_groups' ) ) {
		$res = groups_get_groups( array( 'per_page' => 200, 'show_hidden' => true ) );
		$groups = isset( $res['groups'] ) ? $res['groups'] : array();
	}
	?>
	<style>.yh-f{display:grid;grid-template-columns:200px 1fr;gap:10px 16px;align-items:center}.yh-f input[type=text],.yh-f input[type=url],.yh-f input[type=number],.yh-f select,.yh-f textarea{width:100%}.yh-f .d{grid-column:2;color:#666;font-size:12px;margin-top:-6px}</style>
	<div class="yh-f">
		<label>Status</label>
		<select name="yh_confirmation">
			<option value="draft" <?php selected( $m( '_yh_confirmation', 'draft' ), 'draft' ); ?>>Draft lineup (not confirmed)</option>
			<option value="confirmed" <?php selected( $m( '_yh_confirmation' ), 'confirmed' ); ?>>Confirmed</option>
		</select>
		<div class="d">Draft events show a "not confirmed yet" label and collect "keep me posted" interest instead of RSVPs.</div>

		<label>Starts (Eastern)</label>
		<input type="datetime-local" name="yh_start" value="<?php echo esc_attr( $m( '_yh_start' ) ); ?>">

		<label>Length (minutes)</label>
		<input type="number" min="15" step="5" name="yh_duration" value="<?php echo esc_attr( $m( '_yh_duration', 150 ) ); ?>">

		<label>Lane</label>
		<select name="yh_lane">
			<?php foreach ( yh_lanes() as $k => $l ) : ?>
				<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $m( '_yh_lane', 'movie-night' ), $k ); ?>><?php echo esc_html( $l['label'] ); ?></option>
			<?php endforeach; ?>
		</select>

		<label>Short line for the guide</label>
		<input type="text" name="yh_note" value="<?php echo esc_attr( $m( '_yh_note' ) ); ?>" placeholder="Holiday kickoff.">

		<label>Host</label>
		<input type="text" name="yh_host" value="<?php echo esc_attr( $m( '_yh_host' ) ); ?>" placeholder="Hosted by ...">

		<label>Space (group)</label>
		<select name="yh_group">
			<option value="0">None</option>
			<?php foreach ( $groups as $g ) : ?>
				<option value="<?php echo (int) $g->id; ?>" <?php selected( (int) $m( '_yh_group', 0 ), (int) $g->id ); ?>><?php echo esc_html( $g->name ); ?></option>
			<?php endforeach; ?>
		</select>

		<label>Where to watch</label>
		<input type="text" name="yh_watch" value="<?php echo esc_attr( $m( '_yh_watch', 'Bring your own copy from the service you already use.' ) ); ?>">
		<div class="d">Check the title's current streamer before publishing.</div>

		<label>Room link (private)</label>
		<input type="url" name="yh_room" value="<?php echo esc_attr( $m( '_yh_room' ) ); ?>" placeholder="https://zoom.us/j/...">
		<div class="d">Only members who RSVP see the Join button, and only near start time. Never shown on the public page.</div>

		<label>Room opens (minutes before)</label>
		<input type="number" min="0" step="5" name="yh_opens" value="<?php echo esc_attr( $m( '_yh_opens', 15 ) ); ?>">

		<label>Recording</label>
		<label><input type="checkbox" name="yh_recorded" value="1" <?php checked( $m( '_yh_recorded' ), '1' ); ?>> Part of this night is recorded (reaction room)</label>
		<div class="d">If checked, RSVP asks each person to opt in to being on camera, or join without being recorded.</div>

		<label>Capacity</label>
		<input type="number" min="0" name="yh_capacity" value="<?php echo esc_attr( $m( '_yh_capacity', 0 ) ); ?>">
		<div class="d">0 means no limit.</div>
	</div>
	<?php
}

function yh_event_people_box( $post ) {
	$r     = yh_rsvps( $post->ID );
	$joins = get_post_meta( $post->ID, '_yh_joins', true );
	$joins = is_array( $joins ) ? $joins : array();
	$on    = 0;
	foreach ( $r as $row ) {
		if ( ! empty( $row['rec'] ) && 'yes' === $row['rec'] ) {
			$on++;
		}
	}
	echo '<p><strong>' . count( $r ) . '</strong> ' . ( yh_is_confirmed( $post->ID ) ? 'RSVPs' : 'interested' ) . '<br><strong>' . count( $joins ) . '</strong> joined the room<br><strong>' . (int) $on . '</strong> opted in to camera</p>';
	if ( $r ) {
		echo '<ul style="max-height:220px;overflow:auto">';
		foreach ( $r as $uid => $row ) {
			$u = get_userdata( $uid );
			if ( $u ) {
				echo '<li>' . esc_html( $u->display_name ) . ( isset( $joins[ $uid ] ) ? ' · joined' : '' ) . ( ! empty( $row['rec'] ) && 'yes' === $row['rec'] ? ' · camera ok' : '' ) . '</li>';
			}
		}
		echo '</ul>';
	}
}

add_action( 'save_post_yh_event', function ( $post_id ) {
	if ( ! isset( $_POST['yh_event_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['yh_event_nonce'] ) ), 'yh_event_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$text = array( 'yh_start' => '_yh_start', 'yh_note' => '_yh_note', 'yh_host' => '_yh_host', 'yh_watch' => '_yh_watch', 'yh_lane' => '_yh_lane' );
	foreach ( $text as $f => $k ) {
		update_post_meta( $post_id, $k, isset( $_POST[ $f ] ) ? sanitize_text_field( wp_unslash( $_POST[ $f ] ) ) : '' );
	}
	update_post_meta( $post_id, '_yh_confirmation', ( isset( $_POST['yh_confirmation'] ) && 'confirmed' === $_POST['yh_confirmation'] ) ? 'confirmed' : 'draft' );
	update_post_meta( $post_id, '_yh_room', isset( $_POST['yh_room'] ) ? esc_url_raw( wp_unslash( $_POST['yh_room'] ) ) : '' );
	foreach ( array( 'yh_duration' => '_yh_duration', 'yh_opens' => '_yh_opens', 'yh_capacity' => '_yh_capacity', 'yh_group' => '_yh_group' ) as $f => $k ) {
		update_post_meta( $post_id, $k, isset( $_POST[ $f ] ) ? absint( $_POST[ $f ] ) : 0 );
	}
	update_post_meta( $post_id, '_yh_recorded', empty( $_POST['yh_recorded'] ) ? '0' : '1' );
	// Sortable timestamp.
	$start = yh_event_start( $post_id );
	update_post_meta( $post_id, '_yh_start_ts', $start ? $start->getTimestamp() : 0 );
} );

/* ---------- Admin list columns ---------- */

add_filter( 'manage_yh_event_posts_columns', function ( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['yh_start'] = 'Starts';
			$new['yh_lane']  = 'Lane';
			$new['yh_conf']  = 'Lineup';
			$new['yh_rsvp']  = 'RSVP / Joined';
		}
	}
	unset( $new['date'] );
	return $new;
} );

add_action( 'manage_yh_event_posts_custom_column', function ( $col, $post_id ) {
	switch ( $col ) {
		case 'yh_start':
			$s = yh_event_start( $post_id );
			echo $s ? esc_html( $s->format( 'D M j, g:i a' ) ) : '<em>No date yet</em>';
			break;
		case 'yh_lane':
			echo esc_html( yh_lane_label( get_post_meta( $post_id, '_yh_lane', true ) ) );
			break;
		case 'yh_conf':
			echo yh_is_confirmed( $post_id ) ? 'Confirmed' : '<span style="color:#a0522d">Draft lineup</span>';
			break;
		case 'yh_rsvp':
			$j = get_post_meta( $post_id, '_yh_joins', true );
			echo count( yh_rsvps( $post_id ) ) . ' / ' . ( is_array( $j ) ? count( $j ) : 0 );
			break;
	}
}, 10, 2 );

/* ---------- Front-end query: Yard Guide shows upcoming, soonest first ---------- */

add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( $q->is_post_type_archive( 'yh_event' ) ) {
		$q->set( 'posts_per_page', 60 );
		$q->set( 'meta_key', '_yh_start_ts' );
		$q->set( 'orderby', 'meta_value_num' );
		$q->set( 'order', 'ASC' );
		$past = ! empty( $_GET['past'] );
		$q->set( 'meta_query', array(
			array(
				'key'     => '_yh_start_ts',
				'value'   => time() - 6 * HOUR_IN_SECONDS,
				'compare' => $past ? '<' : '>=',
				'type'    => 'NUMERIC',
			),
		) );
		if ( $past ) {
			$q->set( 'order', 'DESC' );
		}
		if ( ! empty( $_GET['lane'] ) && array_key_exists( $_GET['lane'], yh_lanes() ) ) {
			$mq   = $q->get( 'meta_query' );
			$mq[] = array( 'key' => '_yh_lane', 'value' => sanitize_key( $_GET['lane'] ) );
			$q->set( 'meta_query', $mq );
		}
	}
} );

/** Upcoming published events (for the cover and feature pages). */
function yh_upcoming_events( $limit = 4, $lane = '' ) {
	$mq = array(
		array( 'key' => '_yh_start_ts', 'value' => time() - 6 * HOUR_IN_SECONDS, 'compare' => '>=', 'type' => 'NUMERIC' ),
	);
	if ( $lane ) {
		$mq[] = array( 'key' => '_yh_lane', 'value' => (array) $lane, 'compare' => 'IN' );
	}
	return get_posts( array(
		'post_type'      => 'yh_event',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'meta_key'       => '_yh_start_ts',
		'orderby'        => 'meta_value_num',
		'order'          => 'ASC',
		'meta_query'     => $mq,
	) );
}

/* ---------- RSVP ---------- */

function yh_rsvp_form( $post_id ) {
	$confirmed = yh_is_confirmed( $post_id );
	$end       = yh_event_end( $post_id );
	$is_over   = $end && $end->getTimestamp() < time();
	ob_start();
	if ( $is_over ) {
		echo '<p class="yh-muted">This night has passed. See what is coming up in the <a href="' . esc_url( get_post_type_archive_link( 'yh_event' ) ) . '">Yard Guide</a>.</p>';
		return ob_get_clean();
	}
	if ( ! is_user_logged_in() ) {
		$back = get_permalink( $post_id );
		echo '<a class="yh-btn yh-btn-gold" href="' . esc_url( yh_join_url( $back ) ) . '">' . ( $confirmed ? 'Join free to RSVP' : 'Join free to get updates' ) . '</a>';
		echo ' <a class="yh-link" href="' . esc_url( yh_signin_url( $back ) ) . '">Already a member? Sign in</a>';
		return ob_get_clean();
	}
	$has  = yh_user_has_rsvp( $post_id );
	$rec  = '1' === get_post_meta( $post_id, '_yh_recorded', true );
	$cap  = (int) get_post_meta( $post_id, '_yh_capacity', true );
	$full = $cap && count( yh_rsvps( $post_id ) ) >= $cap && ! $has;
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="yh-rsvp">';
	wp_nonce_field( 'yh_rsvp_' . $post_id );
	echo '<input type="hidden" name="action" value="yh_rsvp"><input type="hidden" name="event" value="' . (int) $post_id . '">';
	if ( $has ) {
		echo '<p class="yh-ok">' . ( $confirmed ? 'You are on the list.' : 'We will keep you posted.' ) . '</p>';
		echo '<button class="yh-btn yh-btn-ghost" name="do" value="cancel">' . ( $confirmed ? 'Cancel my RSVP' : 'Stop updates' ) . '</button>';
	} elseif ( $full ) {
		echo '<p class="yh-muted">This room is full.</p>';
	} else {
		if ( $confirmed && $rec ) {
			echo '<fieldset class="yh-rec"><legend>Part of this night is recorded in a reaction room.</legend>';
			echo '<label><input type="radio" name="rec" value="no" checked> Join without being recorded</label>';
			echo '<label><input type="radio" name="rec" value="yes"> I am open to being on camera in the reaction room (I can change my mind before it starts)</label>';
			echo '</fieldset>';
		}
		echo '<button class="yh-btn yh-btn-gold" name="do" value="rsvp">' . ( $confirmed ? 'RSVP' : 'Keep me posted' ) . '</button>';
	}
	echo '</form>';
	if ( $has && $confirmed ) {
		echo yh_join_button( $post_id ); // phpcs:ignore
	}
	return ob_get_clean();
}

function yh_join_button( $post_id ) {
	$start = yh_event_start( $post_id );
	$end   = yh_event_end( $post_id );
	if ( ! $start ) {
		return '';
	}
	$opens = (int) get_post_meta( $post_id, '_yh_opens', true );
	$open  = $start->getTimestamp() - $opens * 60;
	$now   = time();
	$room  = get_post_meta( $post_id, '_yh_room', true );
	if ( $now >= $open && $now <= $end->getTimestamp() && $room ) {
		return '<p><a class="yh-btn yh-btn-plum" href="' . esc_url( add_query_arg( 'yh_join', $post_id, home_url( '/' ) ) ) . '">Join the room now</a></p>';
	}
	return '<p class="yh-muted">The Join button shows up here ' . ( $opens ? $opens . ' minutes before start' : 'at start time' ) . '. <a class="yh-link" href="' . esc_url( add_query_arg( 'yh_ics', $post_id, home_url( '/' ) ) ) . '">Add to my calendar</a></p>';
}

add_action( 'admin_post_yh_rsvp', 'yh_handle_rsvp' );
add_action( 'admin_post_nopriv_yh_rsvp', function () {
	$id = isset( $_POST['event'] ) ? absint( $_POST['event'] ) : 0;
	wp_safe_redirect( yh_signin_url( $id ? get_permalink( $id ) : home_url( '/' ) ) );
	exit;
} );

function yh_handle_rsvp() {
	$id = isset( $_POST['event'] ) ? absint( $_POST['event'] ) : 0;
	if ( ! $id || 'yh_event' !== get_post_type( $id ) || 'publish' !== get_post_status( $id ) ) {
		wp_die( 'Event not found.' );
	}
	check_admin_referer( 'yh_rsvp_' . $id );
	$uid = get_current_user_id();
	$r   = yh_rsvps( $id );
	$do  = isset( $_POST['do'] ) ? sanitize_key( $_POST['do'] ) : 'rsvp';
	if ( 'cancel' === $do ) {
		unset( $r[ $uid ] );
	} else {
		$cap = (int) get_post_meta( $id, '_yh_capacity', true );
		if ( ! $cap || count( $r ) < $cap || isset( $r[ $uid ] ) ) {
			$r[ $uid ] = array(
				't'   => time(),
				'rec' => ( isset( $_POST['rec'] ) && 'yes' === $_POST['rec'] ) ? 'yes' : 'no',
			);
		}
	}
	update_post_meta( $id, '_yh_rsvps', $r );
	wp_safe_redirect( get_permalink( $id ) . '#rsvp' );
	exit;
}

/* ---------- Join room (logged, then redirect) and calendar file ---------- */

add_action( 'template_redirect', function () {
	if ( isset( $_GET['yh_join'] ) ) {
		$id = absint( $_GET['yh_join'] );
		if ( ! $id || 'yh_event' !== get_post_type( $id ) ) {
			return;
		}
		if ( ! is_user_logged_in() ) {
			wp_safe_redirect( yh_signin_url( get_permalink( $id ) ) );
			exit;
		}
		$uid = get_current_user_id();
		if ( ! yh_user_has_rsvp( $id, $uid ) || ! yh_is_confirmed( $id ) ) {
			wp_safe_redirect( get_permalink( $id ) . '#rsvp' );
			exit;
		}
		$start = yh_event_start( $id );
		$end   = yh_event_end( $id );
		$opens = (int) get_post_meta( $id, '_yh_opens', true );
		$room  = get_post_meta( $id, '_yh_room', true );
		if ( ! $room || ! $start || time() < $start->getTimestamp() - $opens * 60 || time() > $end->getTimestamp() ) {
			wp_safe_redirect( get_permalink( $id ) . '#rsvp' );
			exit;
		}
		$j = get_post_meta( $id, '_yh_joins', true );
		$j = is_array( $j ) ? $j : array();
		if ( ! isset( $j[ $uid ] ) ) {
			$j[ $uid ] = time();
			update_post_meta( $id, '_yh_joins', $j );
		}
		wp_redirect( $room ); // External room host, not limited to this site.
		exit;
	}
	if ( isset( $_GET['yh_ics'] ) ) {
		$id    = absint( $_GET['yh_ics'] );
		$start = $id ? yh_event_start( $id ) : null;
		if ( ! $start || 'publish' !== get_post_status( $id ) ) {
			return;
		}
		$end = yh_event_end( $id );
		$s   = clone $start;
		$e   = clone $end;
		$s->setTimezone( new DateTimeZone( 'UTC' ) );
		$e->setTimezone( new DateTimeZone( 'UTC' ) );
		$esc = function ( $t ) {
			return str_replace( array( '\\', ',', ';', "\n" ), array( '\\\\', '\,', '\;', '\n' ), $t );
		};
		header( 'Content-Type: text/calendar; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="yardhood-' . $id . '.ics"' );
		echo "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Yardhood//Yard Guide//EN\r\nBEGIN:VEVENT\r\n";
		echo 'UID:yh-' . (int) $id . '@' . wp_parse_url( home_url(), PHP_URL_HOST ) . "\r\n";
		echo 'DTSTAMP:' . gmdate( 'Ymd\THis\Z' ) . "\r\n";
		echo 'DTSTART:' . $s->format( 'Ymd\THis\Z' ) . "\r\nDTEND:" . $e->format( 'Ymd\THis\Z' ) . "\r\n";
		echo 'SUMMARY:' . $esc( 'Yardhood: ' . get_the_title( $id ) ) . "\r\n";
		echo 'DESCRIPTION:' . $esc( 'Join from the event page: ' . get_permalink( $id ) ) . "\r\n";
		echo 'URL:' . get_permalink( $id ) . "\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n";
		exit;
	}
}, 5 );
