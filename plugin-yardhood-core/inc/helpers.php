<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** URL of a bundled image. */
function yh_img( $name ) {
	return YH_URL . 'assets/img/' . $name . '.webp?v=' . YH_VERSION;
}

/** Lane definitions for the Yard Guide. */
function yh_lanes() {
	return array(
		'movie-night'   => array( 'label' => 'Movie Night', 'img' => 'movie-screen' ),
		'watchalong'    => array( 'label' => 'Watchalong', 'img' => 'movie-screen' ),
		'live-thread'   => array( 'label' => 'Live Thread', 'img' => 'movie-screen' ),
		'cookout'       => array( 'label' => 'The Cookout', 'img' => 'cookout' ),
		'sunday-dinner' => array( 'label' => 'Sunday Dinner', 'img' => 'plate' ),
		'artist-corner' => array( 'label' => 'Artist Corner', 'img' => 'artist-studio' ),
		'conversation'  => array( 'label' => 'Conversation', 'img' => 'library' ),
		'around-hood'   => array( 'label' => 'Around the Hood', 'img' => 'hood-street' ),
	);
}

function yh_lane_label( $lane ) {
	$l = yh_lanes();
	return isset( $l[ $lane ] ) ? $l[ $lane ]['label'] : 'Event';
}

/** Event start as DateTime in site timezone, or null. */
function yh_event_start( $post_id ) {
	$raw = get_post_meta( $post_id, '_yh_start', true );
	if ( ! $raw ) {
		return null;
	}
	try {
		return new DateTime( $raw, wp_timezone() );
	} catch ( Exception $e ) {
		return null;
	}
}

function yh_event_end( $post_id ) {
	$start = yh_event_start( $post_id );
	if ( ! $start ) {
		return null;
	}
	$mins = (int) get_post_meta( $post_id, '_yh_duration', true );
	$mins = $mins > 0 ? $mins : 150;
	$end  = clone $start;
	$end->modify( '+' . $mins . ' minutes' );
	return $end;
}

function yh_is_confirmed( $post_id ) {
	return 'confirmed' === get_post_meta( $post_id, '_yh_confirmation', true );
}

function yh_rsvps( $post_id ) {
	$r = get_post_meta( $post_id, '_yh_rsvps', true );
	return is_array( $r ) ? $r : array();
}

function yh_user_has_rsvp( $post_id, $user_id = 0 ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	return $user_id && isset( yh_rsvps( $post_id )[ $user_id ] );
}

/** Register / join URLs that send people back where they were. */
function yh_join_url( $redirect = '' ) {
	$redirect = $redirect ? $redirect : home_url( add_query_arg( array() ) );
	if ( function_exists( 'bp_get_signup_page' ) && bp_get_signup_page() ) {
		return add_query_arg( 'redirect_to', rawurlencode( $redirect ), bp_get_signup_page() );
	}
	return wp_registration_url();
}

function yh_signin_url( $redirect = '' ) {
	return wp_login_url( $redirect ? $redirect : home_url( add_query_arg( array() ) ) );
}

/** Plain list of official space slugs used across templates. */
function yh_official_spaces() {
	return array(
		array( 'slug' => 'movie-nights', 'name' => 'Movie Nights', 'img' => 'movie-screen', 'pos' => '50% 40%', 'kick' => '8 pm ET', 'dek' => 'Watch together. Discuss it together after.', 'bg' => '#3B1E35' ),
		array( 'slug' => 'the-cookout', 'name' => 'The Cookout', 'img' => 'cookout', 'pos' => '45% 50%', 'kick' => 'Live', 'dek' => 'Cook along on camera and plan your own.', 'bg' => '#8E4B2F' ),
		array( 'slug' => 'sunday-dinner', 'name' => 'Sunday Dinner', 'img' => 'plate', 'pos' => '50% 50%', 'kick' => 'Weekly', 'dek' => 'Show us your plate and the story behind it.', 'bg' => '#4A2C1D' ),
		array( 'slug' => 'artist-corner', 'name' => 'Artist Corner', 'img' => 'artist-studio', 'pos' => '75% 35%', 'kick' => 'Create', 'dek' => 'Work in progress, feedback and the monthly cover pick.', 'bg' => '#7A5A1E' ),
		array( 'slug' => 'people-worth-knowing', 'name' => 'People Worth Knowing', 'img' => 'hood-street', 'pos' => '88% 45%', 'kick' => 'Stories', 'dek' => 'Read the features. Submit your own.', 'bg' => '#4B3F78' ),
		array( 'slug' => 'life-skills', 'name' => 'Real Skills For Life', 'img' => 'library', 'pos' => '35% 50%', 'kick' => 'Learn', 'dek' => 'Got something to teach? Teach it. Want to learn? Pull up a seat.', 'bg' => '#3B1E35' ),
	);
}

/** Link to an official space's group, falling back to The Yard page. */
function yh_space_url( $slug ) {
	if ( function_exists( 'groups_get_id' ) && function_exists( 'bp_get_group_permalink' ) ) {
		$gid = groups_get_id( $slug );
		if ( $gid ) {
			$group = groups_get_group( $gid );
			return bp_get_group_permalink( $group );
		}
	}
	$p = get_page_by_path( 'the-yard' );
	return $p ? get_permalink( $p ) : home_url( '/' );
}

function yh_page_url( $slug ) {
	$p = get_page_by_path( $slug );
	return $p ? get_permalink( $p ) : home_url( '/' . $slug . '/' );
}
