<?php
/**
 * Engagement on Perspectives and Stories: reactions, member responses,
 * and a line in the community feed when a piece is published.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function yh_engage_types() {
	return array( 'yh_perspective', 'yh_story' );
}

function yh_reactions() {
	return array(
		'felt'  => 'Felt this',
		'think' => 'Made me think',
		'share' => 'Passing it on',
	);
}

/* Responses: open on published pieces, members only. */
add_filter( 'comments_open', function ( $open, $post_id ) {
	$p = get_post( $post_id );
	if ( $p && in_array( $p->post_type, yh_engage_types(), true ) && 'publish' === $p->post_status ) {
		return true;
	}
	return $open;
}, 20, 2 );

add_action( 'admin_init', function () {
	if ( get_option( 'yh_engage_v1' ) ) {
		return;
	}
	update_option( 'comment_registration', 1 );
	foreach ( yh_engage_types() as $t ) {
		update_option( 'bp-feed-custom-post-type-' . $t, 1 );
		update_option( 'bp-feed-custom-post-type-' . $t . '-comments', 1 );
	}
	update_option( 'yh_engage_v1', 1 );
} );

/* Community feed: "X published a new Perspective". */
add_action( 'bp_init', function () {
	if ( ! function_exists( 'bp_activity_set_post_type_tracking_args' ) ) {
		return;
	}
	$labels = array( 'yh_perspective' => 'Perspective', 'yh_story' => 'Story' );
	foreach ( $labels as $t => $label ) {
		add_post_type_support( $t, 'buddypress-activity' );
		bp_activity_set_post_type_tracking_args( $t, array(
			'action_id'                => 'new_' . $t,
			'bp_activity_admin_filter' => 'New ' . $label,
			'bp_activity_front_filter' => $label . 's',
			'bp_activity_new_post'     => '%1$s published a new ' . $label . ': %2$s',
			'bp_activity_new_post_ms'  => '%1$s published a new ' . $label . ': %2$s',
			'contexts'                 => array( 'activity', 'member' ),
			'comment_action_id'        => 'new_' . $t . '_comment',
			'bp_activity_new_comment'  => '%1$s responded to %2$s',
			'position'                 => 60,
		) );
	}
}, 20 );

/* Reactions. */
function yh_reaction_counts( $id ) {
	$out = array();
	foreach ( yh_reactions() as $k => $l ) {
		$out[ $k ] = (int) get_post_meta( $id, '_yh_react_' . $k, true );
	}
	return $out;
}

function yh_user_reactions( $id, $uid = 0 ) {
	$uid = $uid ? $uid : get_current_user_id();
	if ( ! $uid ) {
		return array();
	}
	$all = (array) get_user_meta( $uid, '_yh_reacted', true );
	return isset( $all[ $id ] ) ? (array) $all[ $id ] : array();
}

add_action( 'wp_ajax_yh_react', function () {
	check_ajax_referer( 'yh_react', 'n' );
	$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
	$k  = isset( $_POST['k'] ) ? sanitize_key( wp_unslash( $_POST['k'] ) ) : '';
	$p  = get_post( $id );
	if ( ! $p || ! in_array( $p->post_type, yh_engage_types(), true ) || ! array_key_exists( $k, yh_reactions() ) ) {
		wp_send_json_error();
	}
	$uid  = get_current_user_id();
	$all  = (array) get_user_meta( $uid, '_yh_reacted', true );
	$mine = isset( $all[ $id ] ) ? (array) $all[ $id ] : array();
	$n    = (int) get_post_meta( $id, '_yh_react_' . $k, true );
	if ( in_array( $k, $mine, true ) ) {
		$mine = array_values( array_diff( $mine, array( $k ) ) );
		$n    = max( 0, $n - 1 );
		$on   = false;
	} else {
		$mine[] = $k;
		$n++;
		$on = true;
	}
	$all[ $id ] = $mine;
	update_user_meta( $uid, '_yh_reacted', $all );
	update_post_meta( $id, '_yh_react_' . $k, $n );
	wp_send_json_success( array( 'n' => $n, 'on' => $on ) );
} );
