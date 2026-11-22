<?php
/**
 * Pre-launch lock: logged-out visitors see a holding page until launch is approved.
 * Testers can pass ?yh_preview=KEY once to get a 30-day preview cookie.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {
	if ( isset( $_GET['yh_preview'] ) ) {
		$key = (string) get_option( 'yh_prelaunch_key' );
		if ( $key && hash_equals( $key, sanitize_text_field( wp_unslash( $_GET['yh_preview'] ) ) ) ) {
			setcookie( 'yh_preview', wp_hash( $key ), time() + 30 * DAY_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
			$_COOKIE['yh_preview'] = wp_hash( $key );
		}
	}
}, 1 );

function yh_prelaunch_active() {
	return (bool) get_option( 'yh_prelaunch', 1 );
}

function yh_has_preview_pass() {
	if ( is_user_logged_in() ) {
		return true;
	}
	$key = (string) get_option( 'yh_prelaunch_key' );
	return $key && isset( $_COOKIE['yh_preview'] ) && hash_equals( wp_hash( $key ), (string) $_COOKIE['yh_preview'] );
}

add_action( 'template_redirect', function () {
	if ( ! yh_prelaunch_active() || yh_has_preview_pass() ) {
		return;
	}
	// Let people reach registration and activation during private testing only with a pass.
	status_header( 200 );
	nocache_headers();
	header( 'X-Robots-Tag: noindex, nofollow', true );
	?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Yardhood | The Community Culture Magazine</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
<style>
html,body{margin:0;height:100%;background:#3B1E35;color:#F6EFE4;font-family:'DM Sans',system-ui,sans-serif}
.w{min-height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:48px 24px;box-sizing:border-box;
background:linear-gradient(180deg,rgba(59,30,53,.55),rgba(59,30,53,.92)),url('<?php echo esc_url( yh_img( 'movie-screen' ) ); ?>') center/cover}
h1{font-family:'Playfair Display',serif;font-weight:700;letter-spacing:.04em;font-size:clamp(56px,13vw,140px);margin:0;line-height:.9}
.t{color:#E3C57A;letter-spacing:.24em;text-transform:uppercase;font-size:13px;margin:18px 0 28px}
p{max-width:520px;font-size:18px;line-height:1.55;margin:0 auto}
.s{margin-top:36px;font-size:13px;opacity:.75}
a{color:#E3C57A}
</style></head><body><div class="w">
<h1>YARDHOOD</h1>
<div class="t">The Community Culture Magazine</div>
<p>The Winter Issue is being set up. Movie nights, real conversations, and people worth knowing. Come back soon.</p>
<div class="s"><a href="<?php echo esc_url( wp_login_url() ); ?>">Team sign in</a></div>
</div></body></html><?php
	exit;
}, 0 );

// Keep search engines out while locked.
add_filter( 'wp_robots', function ( $robots ) {
	if ( yh_prelaunch_active() ) {
		$robots['noindex']  = true;
		$robots['nofollow'] = true;
	}
	return $robots;
} );
