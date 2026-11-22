<?php
/**
 * Community Host plan through a Stripe Payment Link.
 * After checkout, Stripe sends the member back with a session id. With a read-only
 * restricted key saved in Tools > Yardhood setup, the site confirms the checkout and
 * turns on hosting automatically. A daily check turns it off if the subscription ends.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function yh_billing_link() {
	return (string) get_option( 'yh_host_payment_link', '' );
}

function yh_billing_key() {
	return (string) get_option( 'yh_stripe_restricted_key', '' );
}

/** Checkout URL for the current member, prefilled and tagged so we can match them. */
function yh_host_checkout_url() {
	$link = yh_billing_link();
	if ( ! $link ) {
		return '';
	}
	if ( ! is_user_logged_in() ) {
		return $link;
	}
	$u = wp_get_current_user();
	return add_query_arg( array(
		'prefilled_email'     => rawurlencode( $u->user_email ),
		'client_reference_id' => 'yh-' . $u->ID,
	), $link );
}

function yh_stripe_get( $path ) {
	$key = yh_billing_key();
	if ( ! $key ) {
		return new WP_Error( 'nokey', 'No Stripe key saved.' );
	}
	$r = wp_remote_get( 'https://api.stripe.com/v1/' . ltrim( $path, '/' ), array(
		'timeout' => 20,
		'headers' => array( 'Authorization' => 'Bearer ' . $key ),
	) );
	if ( is_wp_error( $r ) ) {
		return $r;
	}
	$body = json_decode( wp_remote_retrieve_body( $r ), true );
	if ( wp_remote_retrieve_response_code( $r ) >= 400 ) {
		return new WP_Error( 'stripe', isset( $body['error']['message'] ) ? $body['error']['message'] : 'Stripe error' );
	}
	return $body;
}

/* Return from Stripe: /the-hood/?yh_session={CHECKOUT_SESSION_ID} */
add_action( 'template_redirect', function () {
	if ( empty( $_GET['yh_session'] ) ) {
		return;
	}
	$sid  = sanitize_text_field( wp_unslash( $_GET['yh_session'] ) );
	$back = remove_query_arg( 'yh_session' );
	if ( ! is_user_logged_in() ) {
		wp_safe_redirect( wp_login_url( home_url( add_query_arg( array() ) ) ) );
		exit;
	}
	$uid = get_current_user_id();
	$s   = yh_stripe_get( 'checkout/sessions/' . rawurlencode( $sid ) );
	if ( is_wp_error( $s ) ) {
		// No key yet or Stripe unreachable: record it for manual approval.
		$pending          = get_option( 'yh_host_pending', array() );
		$pending[ $uid ]  = array( 't' => time(), 'session' => $sid );
		update_option( 'yh_host_pending', $pending, false );
		wp_mail( get_option( 'admin_email' ), 'Yardhood host checkout to approve', 'Member ' . $uid . ' finished checkout (session ' . $sid . '). Turn on hosting for them in Users > their profile if Stripe shows the subscription.' );
		wp_safe_redirect( add_query_arg( 'host_status', 'pending', $back ) . '#host' );
		exit;
	}
	$ref_ok = empty( $s['client_reference_id'] ) || 'yh-' . $uid === $s['client_reference_id'];
	if ( 'complete' === ( $s['status'] ?? '' ) && $ref_ok && ! empty( $s['subscription'] ) ) {
		update_user_meta( $uid, '_yh_host', 1 );
		update_user_meta( $uid, '_yh_host_sub', sanitize_text_field( is_array( $s['subscription'] ) ? $s['subscription']['id'] : $s['subscription'] ) );
		update_user_meta( $uid, '_yh_host_since', time() );
		wp_safe_redirect( add_query_arg( 'host_status', 'active', $back ) . '#host' );
		exit;
	}
	wp_safe_redirect( add_query_arg( 'host_status', 'check', $back ) . '#host' );
	exit;
}, 6 );

/* Daily: turn hosting off when a subscription is canceled or unpaid. */
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'yh_check_host_subs' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'yh_check_host_subs' );
	}
} );
add_action( 'yh_check_host_subs', function () {
	if ( ! yh_billing_key() ) {
		return;
	}
	$users = get_users( array( 'meta_key' => '_yh_host_sub', 'fields' => 'ID' ) );
	foreach ( $users as $uid ) {
		$sub = get_user_meta( $uid, '_yh_host_sub', true );
		$s   = yh_stripe_get( 'subscriptions/' . rawurlencode( $sub ) );
		if ( is_wp_error( $s ) ) {
			continue;
		}
		$ok = in_array( $s['status'] ?? '', array( 'trialing', 'active', 'past_due' ), true );
		update_user_meta( $uid, '_yh_host', $ok ? 1 : 0 );
	}
} );

/* Admin: host toggle on user profile. */
add_action( 'show_user_profile', 'yh_host_profile_field' );
add_action( 'edit_user_profile', 'yh_host_profile_field' );
function yh_host_profile_field( $user ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$on  = (bool) get_user_meta( $user->ID, '_yh_host', true );
	$sub = get_user_meta( $user->ID, '_yh_host_sub', true );
	echo '<h2>Yardhood hosting</h2><table class="form-table"><tr><th>Community Host</th><td><label><input type="checkbox" name="yh_host" value="1" ' . checked( $on, true, false ) . '> Can create groups</label>' . ( $sub ? '<p class="description">Stripe subscription: ' . esc_html( $sub ) . '</p>' : '' ) . '</td></tr></table>';
}
add_action( 'personal_options_update', 'yh_host_profile_save' );
add_action( 'edit_user_profile_update', 'yh_host_profile_save' );
function yh_host_profile_save( $user_id ) {
	if ( current_user_can( 'manage_options' ) ) {
		update_user_meta( $user_id, '_yh_host', empty( $_POST['yh_host'] ) ? 0 : 1 );
	}
}
