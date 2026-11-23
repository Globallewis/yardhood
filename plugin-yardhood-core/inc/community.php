<?php
/**
 * Community glue for BuddyBoss: group types, 18+ signup check, host request log screen.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'bp_groups_register_group_types', function () {
	if ( ! function_exists( 'bp_groups_register_group_type' ) ) {
		return;
	}
	bp_groups_register_group_type( 'official', array(
		'labels'                => array( 'name' => 'The Yard', 'singular_name' => 'Official space' ),
		'has_directory'         => 'the-yard',
		'show_in_create_screen' => false,
		'show_in_list'          => true,
		'description'           => 'Official Yardhood spaces.',
	) );
	bp_groups_register_group_type( 'member', array(
		'labels'                => array( 'name' => 'The Hood', 'singular_name' => 'Member group' ),
		'has_directory'         => 'the-hood',
		'show_in_create_screen' => true,
		'create_screen_checked' => true,
		'show_in_list'          => true,
		'description'           => 'Groups started by members.',
	) );
} );

// New groups made by members default to the member type.
add_action( 'groups_group_create_complete', function ( $group_id ) {
	if ( function_exists( 'bp_groups_get_group_type' ) && ! bp_groups_get_group_type( $group_id ) ) {
		bp_groups_set_group_type( $group_id, 'member' );
	}
} );

/* 18+ confirmation on the signup form. */
add_action( 'bp_before_registration_submit_buttons', function () {
	echo '<div class="yh-adult-check" style="margin:16px 0"><label><input type="checkbox" name="yh_adult" value="1" ' . checked( ! empty( $_POST['yh_adult'] ), true, false ) . '> I am 18 or older. Yardhood is an adult community.</label>';
	do_action( 'bp_yh_adult_errors' );
	echo '</div>';
} );

add_action( 'bp_signup_validate', function () {
	if ( empty( $_POST['yh_adult'] ) ) {
		buddypress()->signup->errors['yh_adult'] = 'Please confirm you are 18 or older.';
	}
} );

add_action( 'bp_yh_adult_errors', function () {
	$errors = isset( buddypress()->signup->errors ) ? buddypress()->signup->errors : array();
	if ( ! empty( $errors['yh_adult'] ) ) {
		echo '<div class="error">' . esc_html( $errors['yh_adult'] ) . '</div>';
	}
} );

add_action( 'bp_core_signup_user', function ( $user_id ) {
	update_user_meta( $user_id, '_yh_adult_confirmed', time() );
} );

/* Plain WordPress registration fallback. */
add_action( 'register_form', function () {
	echo '<p><label><input type="checkbox" name="yh_adult" value="1"> I am 18 or older.</label></p>';
} );
add_filter( 'registration_errors', function ( $errors ) {
	if ( empty( $_POST['yh_adult'] ) ) {
		$errors->add( 'yh_adult', 'Please confirm you are 18 or older.' );
	}
	return $errors;
} );
add_action( 'user_register', function ( $user_id ) {
	if ( ! empty( $_POST['yh_adult'] ) ) {
		update_user_meta( $user_id, '_yh_adult_confirmed', time() );
	}
} );

/* Use the Yardhood (BuddyBoss Theme) look on community pages, not ReadyLaunch. */
add_filter( 'bb_is_readylaunch_enabled', '__return_false', 99 );

/* Official spaces use the issue photography until a real avatar or cover is uploaded. */
function yh_space_image_for_group( $group_id ) {
	if ( ! function_exists( 'groups_get_group' ) ) {
		return '';
	}
	$g = groups_get_group( $group_id );
	if ( empty( $g->slug ) ) {
		return '';
	}
	foreach ( yh_official_spaces() as $s ) {
		if ( $s['slug'] === $g->slug ) {
			return yh_img( $s['img'] );
		}
	}
	return '';
}

add_filter( 'bp_core_fetch_avatar_url', function ( $url, $params ) {
	if ( empty( $params['object'] ) || 'group' !== $params['object'] || empty( $params['item_id'] ) ) {
		return $url;
	}
	if ( $url && false === strpos( $url, 'group-avatar' ) && false === strpos( $url, 'mystery' ) && false === strpos( $url, 'default' ) ) {
		return $url;
	}
	$img = yh_space_image_for_group( (int) $params['item_id'] );
	return $img ? $img : $url;
}, 20, 2 );

add_filter( 'bp_core_fetch_avatar', function ( $html, $params ) {
	if ( empty( $params['object'] ) || 'group' !== $params['object'] || empty( $params['item_id'] ) ) {
		return $html;
	}
	if ( false === strpos( $html, 'group-avatar' ) && false === strpos( $html, 'mystery' ) && false === strpos( $html, 'default' ) ) {
		return $html;
	}
	$img = yh_space_image_for_group( (int) $params['item_id'] );
	if ( ! $img ) {
		return $html;
	}
	return preg_replace( '/src=["\'][^"\']+["\']/', 'src="' . esc_url( $img ) . '" style="object-fit:cover"', $html, 1 );
}, 20, 2 );

add_filter( 'bp_attachments_pre_get_attachment', function ( $pre, $r ) {
	if ( null !== $pre || empty( $r['object_dir'] ) || 'groups' !== $r['object_dir'] || empty( $r['type'] ) || 'cover-image' !== $r['type'] ) {
		return $pre;
	}
	$upload = function_exists( 'bp_attachments_uploads_dir_get' ) ? bp_attachments_uploads_dir_get() : array();
	if ( ! empty( $upload['basedir'] ) ) {
		$dir = trailingslashit( $upload['basedir'] ) . 'groups/' . (int) $r['item_id'] . '/cover-image';
		if ( is_dir( $dir ) && glob( $dir . '/*' ) ) {
			return $pre; // A real cover exists.
		}
	}
	$img = yh_space_image_for_group( (int) $r['item_id'] );
	return $img ? $img : $pre;
}, 10, 2 );

/* ---------- Hosting: who may create a group ---------- */

function yh_user_is_host( $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	if ( ! $user_id ) {
		return false;
	}
	if ( user_can( $user_id, 'manage_options' ) ) {
		return true;
	}
	$level = (int) get_option( 'yh_host_level_id', 0 );
	if ( $level && function_exists( 'pmpro_hasMembershipLevel' ) && pmpro_hasMembershipLevel( $level, $user_id ) ) {
		return true;
	}
	return (bool) get_user_meta( $user_id, '_yh_host', true );
}

add_filter( 'bp_user_can_create_groups', function ( $can ) {
	return yh_user_is_host() ? true : $can;
}, 20 );

/* Send people who try to create a group without a plan to the hosting offer. */
add_action( 'bp_screens', function () {
	if ( function_exists( 'bp_is_group_create' ) && bp_is_group_create() && is_user_logged_in() && ! yh_user_is_host() && bp_restrict_group_creation() ) {
		wp_safe_redirect( yh_page_url( 'the-hood' ) . '#host' );
		exit;
	}
}, 1 );

/* Rooms open on the conversation (Feed), not the member list. */
add_filter( 'bp_groups_default_extension', function () {
	return 'activity';
}, 99 );

/* Room intro above the feed: what this room is for, the next gathering, how to take part. Real data only. */
function yh_room_intro() {
	static $done = false;
	if ( $done || ! function_exists( 'bp_is_group' ) || ! bp_is_group() ) {
		return;
	}
	$done  = true;
	$group = groups_get_current_group();
	if ( ! $group ) {
		return;
	}
	$space = null;
	foreach ( yh_official_spaces() as $sp ) {
		if ( $sp['slug'] === $group->slug ) {
			$space = $sp;
		}
	}
	$purpose = $space ? $space['dek'] : wp_trim_words( wp_strip_all_tags( $group->description ), 30, '…' );
	$next    = null;
	foreach ( yh_upcoming_events( 12 ) as $e ) {
		if ( (int) get_post_meta( $e->ID, '_yh_group', true ) === (int) $group->id ) {
			$next = $e;
			break;
		}
	}
	$member = is_user_logged_in() && groups_is_user_member( get_current_user_id(), $group->id );
	echo '<section class="yh-room-intro">';
	echo '<div class="yh-room-intro-main"><span class="yh-room-intro-k">This room</span><p class="yh-room-intro-p">' . esc_html( $purpose ) . '</p></div>';
	if ( $next ) {
		$s = yh_event_start( $next->ID );
		echo '<a class="yh-room-intro-next" href="' . esc_url( get_permalink( $next ) ) . '"><span class="yh-room-intro-k">Next here</span><span class="yh-room-intro-t">' . esc_html( get_the_title( $next ) ) . '</span><span class="yh-room-intro-d">' . esc_html( $s ? $s->format( 'D, M j · g:i a' ) . ' ET' : '' ) . '</span></a>';
	}
	if ( ! $member ) {
		echo '<p class="yh-room-intro-join">Join the room to post and reply. Reading is open to everyone.</p>';
	}
	echo '</section>';
}
add_action( 'bp_before_group_activity_post_form', 'yh_room_intro', 5 );
add_action( 'bp_before_group_activity_content', 'yh_room_intro', 5 );
