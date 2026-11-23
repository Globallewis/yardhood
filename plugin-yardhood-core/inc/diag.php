<?php
/**
 * Temporary stability logging for the Nov 23 504 investigation. Remove after launch week.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$GLOBALS['yh_t0'] = microtime( true );

add_filter( 'pre_http_request', function ( $pre, $args, $url ) {
	$GLOBALS['yh_http'][ md5( $url ) ] = microtime( true );
	return $pre;
}, 1, 3 );

add_action( 'http_api_debug', function ( $resp, $ctx, $class, $args, $url ) {
	$k   = md5( $url );
	$d   = isset( $GLOBALS['yh_http'][ $k ] ) ? microtime( true ) - $GLOBALS['yh_http'][ $k ] : 0;
	$log = (array) get_option( 'yh_diag_http', array() );
	$log[] = array( gmdate( 'm-d H:i:s' ), round( $d, 2 ), wp_parse_url( $url, PHP_URL_HOST ) . wp_parse_url( $url, PHP_URL_PATH ), is_wp_error( $resp ) ? $resp->get_error_message() : wp_remote_retrieve_response_code( $resp ), isset( $_SERVER['REQUEST_URI'] ) ? substr( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), 0, 80 ) : '' );
	update_option( 'yh_diag_http', array_slice( $log, -60 ), false );
}, 10, 5 );

add_action( 'shutdown', function () {
	$d = microtime( true ) - $GLOBALS['yh_t0'];
	if ( $d < 4 ) {
		return;
	}
	$log   = (array) get_option( 'yh_diag_slow', array() );
	$log[] = array( gmdate( 'm-d H:i:s' ), round( $d, 2 ), isset( $_SERVER['REQUEST_URI'] ) ? substr( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), 0, 120 ) : '', wp_doing_cron() ? 'cron' : ( wp_doing_ajax() ? 'ajax' : '' ), is_user_logged_in() ? 'in' : 'out', round( memory_get_peak_usage() / 1048576 ) . 'MB' );
	update_option( 'yh_diag_slow', array_slice( $log, -60 ), false );
} );

/* Unfinished-request marker: a request killed by a timeout never reaches shutdown, so its marker stays. */
add_action( 'init', function () {
	if ( wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}
	$GLOBALS['yh_mark'] = 'yh_open_' . substr( md5( microtime() . wp_rand() ), 0, 10 );
	set_transient( $GLOBALS['yh_mark'], gmdate( 'm-d H:i:s' ) . ' ' . ( isset( $_SERVER['REQUEST_URI'] ) ? substr( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), 0, 100 ) : '' ) . ' ' . ( is_user_logged_in() ? 'in' : 'out' ), 3 * DAY_IN_SECONDS );
}, 1 );

add_action( 'shutdown', function () {
	if ( ! empty( $GLOBALS['yh_mark'] ) ) {
		delete_transient( $GLOBALS['yh_mark'] );
	}
}, 1 );

add_action( 'admin_post_yh_diag', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'No' );
	}
	global $wpdb;
	$cron  = _get_cron_array();
	$late  = 0;
	$names = array();
	foreach ( (array) $cron as $ts => $hooks ) {
		if ( $ts < time() - 600 ) {
			$late++;
			$names = array_merge( $names, array_keys( $hooks ) );
		}
	}
	wp_send_json( array(
		'php'             => PHP_VERSION,
		'mem'             => ini_get( 'memory_limit' ),
		'max_exec'        => ini_get( 'max_execution_time' ),
		'object_cache'    => wp_using_ext_object_cache(),
		'cron_late'       => $late,
		'cron_late_hooks' => array_values( array_unique( $names ) ),
		'plugins'         => get_option( 'active_plugins' ),
		'slow'            => get_option( 'yh_diag_slow', array() ),
		'http'            => get_option( 'yh_diag_http', array() ),
		'open'            => $wpdb->get_col( "SELECT option_value FROM {$wpdb->options} WHERE option_name LIKE '\\_transient\\_yh\\_open\\_%'" ),
	) );
} );

add_action( 'admin_post_yh_stripe_test', function () {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'No' ); }
	$a = yh_stripe_get( 'checkout/sessions?limit=3' );
	$b = yh_stripe_get( 'subscriptions?limit=3&status=all' );
	wp_send_json( array(
		'sessions' => is_wp_error( $a ) ? $a->get_error_message() : count( $a['data'] ) . ' readable',
		'subs'     => is_wp_error( $b ) ? $b->get_error_message() : array_map( function ( $s ) { return $s['status'] . ' ' . ( $s['items']['data'][0]['price']['unit_amount'] ?? '' ); }, $b['data'] ),
		'cron_next' => wp_next_scheduled( 'yh_check_host_subs' ) ? gmdate( 'Y-m-d H:i', wp_next_scheduled( 'yh_check_host_subs' ) ) : 'none',
		'pending' => count( (array) get_option( 'yh_host_pending', array() ) ),
	) );
} );
