<?php
/**
 * Brand settings for BuddyBoss Theme, the Yardhood house account, and starter posts.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function yh_apply_theme_colors() {
	$o = get_option( 'buddyboss_theme_options', array() );
	if ( ! is_array( $o ) ) {
		$o = array();
	}
	$plum = '#3B1E35';
	$plum2 = '#4A2840';
	$gold = '#C79A3A';
	$paper = '#F6EFE4';
	$set = array(
		'accent_color'                   => $plum,
		'body_background'                => $paper,
		'light_background_blocks'        => '#FFFBF4',
		'body_blocks_border'             => '#E0D2BE',
		'buddyboss_theme_group_cover_bg' => $plum2,
		'heading_text_color'             => $plum,
		'body_text_color'                => '#4A3A33',
		'alternate_text_color'           => '#7A6658',
		'primary_button_background'      => array( 'regular' => $plum, 'hover' => $plum2, 'active' => false ),
		'primary_button_border'          => array( 'regular' => $plum, 'hover' => $plum2, 'active' => false ),
		'primary_button_text_color'      => array( 'regular' => $paper, 'hover' => $paper, 'active' => false ),
		'secondary_button_background'    => array( 'regular' => '#F1E4CF', 'hover' => $gold, 'active' => false ),
		'secondary_button_border'        => array( 'regular' => '#E0D2BE', 'hover' => $gold, 'active' => false ),
		'secondary_button_text_color'    => array( 'regular' => $plum, 'hover' => $plum, 'active' => false ),
		'header_background'              => $plum,
		'header_alternate_background'    => $plum2,
		'header_links'                   => $paper,
		'header_links_hover'             => '#E3C57A',
		'sidenav_background'             => $plum,
		'sidenav_text_color'             => array( 'regular' => $paper, 'hover' => '#E3C57A', 'active' => $plum ),
		'sidenav_menu_background_color'  => array( 'regular' => $plum, 'hover' => $plum2, 'active' => $gold ),
		'sidenav_count_text_color'       => array( 'regular' => $plum, 'hover' => $plum, 'active' => $gold ),
		'sidenav_count_background_color' => array( 'regular' => '#E3C57A', 'hover' => $gold, 'active' => $plum ),
		'footer_background'              => $plum,
		'footer_widget_background'       => $plum,
		'footer_text_color'              => '#D9CBBB',
		'footer_menu_link_color'         => array( 'regular' => '#D9CBBB', 'hover' => '#E3C57A', 'active' => $paper ),
		'login_register_link_color'      => array( 'regular' => '#8E4B2F', 'hover' => $plum, 'active' => false ),
		'login_register_button_background_color' => array( 'regular' => $gold, 'hover' => '#B8892E', 'active' => false ),
		'login_register_button_border_color'     => array( 'regular' => $gold, 'hover' => '#B8892E', 'active' => false ),
		'login_register_button_text_color'       => array( 'regular' => $plum, 'hover' => $plum, 'active' => false ),
		'label_background_color'         => '#F1E4CF',
		'label_text_color'               => $plum,
		'tooltip_background'             => $plum,
		'tooltip_color'                  => $paper,
		'default_notice_bg_color'        => $plum,
		'success_notice_bg_color'        => '#4B3F78',
		'warning_notice_bg_color'        => '#C79A3A',
		'error_notice_bg_color'          => '#8E4B2F',
		'button_default_radius'          => 8,
		'header_shadow'                  => 0,
		'contact_button_text'            => 'Contact us at <a href="mailto:hello@yardhood.com">hello@yardhood.com</a>',
		'copyright_text'                 => '© [buddyboss_current_year] Yardhood. The Community Culture Magazine.',
	);
	foreach ( $set as $k => $v ) {
		$o[ $k ] = $v;
	}
	update_option( 'buddyboss_theme_options', $o );
	// BuddyBoss caches the generated CSS; clear it so new colors show.
	delete_transient( 'buddyboss_theme_compressed_custom_css' );
	delete_transient( 'buddyboss_theme_compressed_elementor_custom_css' );
	delete_option( 'buddyboss_theme_custom_css_cache' );
	return 'Theme colors set to the Yardhood palette (no more default blue).';
}

/* ---------- House account ---------- */

function yh_house_user_id() {
	return (int) get_option( 'yh_house_user', 0 );
}

add_filter( 'bp_core_fetch_avatar_url', function ( $url, $params ) {
	if ( ! empty( $params['object'] ) && 'user' === $params['object'] && (int) $params['item_id'] === yh_house_user_id() && yh_house_user_id() ) {
		if ( false !== strpos( $url, 'profile-avatar' ) || false !== strpos( $url, 'mystery' ) || false !== strpos( $url, 'gravatar' ) ) {
			return yh_img( 'house' );
		}
	}
	return $url;
}, 20, 2 );
add_filter( 'bp_core_fetch_avatar', function ( $html, $params ) {
	if ( ! empty( $params['object'] ) && 'user' === $params['object'] && (int) $params['item_id'] === yh_house_user_id() && yh_house_user_id() ) {
		if ( false !== strpos( $html, 'profile-avatar' ) || false !== strpos( $html, 'mystery' ) || false !== strpos( $html, 'gravatar' ) ) {
			return preg_replace( '/src=["\'][^"\']+["\']/', 'src="' . esc_url( yh_img( 'house' ) ) . '"', $html, 1 );
		}
	}
	return $html;
}, 20, 2 );
add_filter( 'get_avatar_url', function ( $url, $id_or_email ) {
	$uid = is_numeric( $id_or_email ) ? (int) $id_or_email : ( is_object( $id_or_email ) && isset( $id_or_email->user_id ) ? (int) $id_or_email->user_id : 0 );
	return ( $uid && $uid === yh_house_user_id() ) ? yh_img( 'house' ) : $url;
}, 20, 2 );

function yh_setup_house_account() {
	$uid = get_current_user_id();
	update_option( 'yh_house_user', $uid );
	wp_update_user( array( 'ID' => $uid, 'display_name' => 'Yardhood', 'first_name' => 'Yardhood', 'nickname' => 'Yardhood' ) );
	if ( function_exists( 'xprofile_set_field_data' ) ) {
		xprofile_set_field_data( 1, $uid, 'Yardhood' );
		$about = xprofile_get_field_id_from_name( 'About me' );
		if ( $about ) {
			xprofile_set_field_data( $about, $uid, 'The Community Culture Magazine. Movie nights, real conversations, and people worth knowing.' );
		}
		$nick = xprofile_get_field_id_from_name( 'Nickname' );
		if ( $nick ) {
			xprofile_set_field_data( $nick, $uid, 'Yardhood' );
		}
	}
	return 'This admin account now shows as "Yardhood" with the Y monogram. It runs the official spaces.';
}

/* ---------- Starter posts so spaces are not empty ---------- */

function yh_seed_posts() {
	if ( ! function_exists( 'groups_post_update' ) ) {
		return array( 'Groups are not active.' );
	}
	$out   = array();
	$seeds = array(
		'movie-nights'          => "Welcome to Movie Nights. The winter lineup starts Friday, December 11 with the Friday trilogy, and every night starts at 8 pm Eastern.\n\nRSVP from the Yard Guide, bring your own copy, and stay after the credits so we can discuss it together.\n\nFirst question for the room: what is the one holiday movie your family watches every single year?",
		'the-cookout'           => "Welcome to The Cookout. This is where we plan live cook-alongs, trade grill tips and argue about potato salad.\n\nTell us: who is the grill master in your family, and what are they known for?",
		'sunday-dinner'         => "Welcome to Sunday Dinner. Show us your plate.\n\nPost a photo of what is on your table, the recipe if you want to share it, and the story behind it. Who taught you? Who always asks for seconds?",
		'artist-corner'         => "Welcome to Artist Corner. Share what you are working on, ask for feedback, and cheer each other on.\n\nEvery month we look here for the next cover artist. Post a piece in progress and tell us what it is about.",
		'people-worth-knowing'  => "Welcome to People Worth Knowing. This is the home of our features: artists, business owners and neighbors on how they got here.\n\nKnow someone whose story should be told? Tag them here, or send your own at yardhood.com/submit-your-story.",
		'life-skills'           => "Welcome to Life Skills. Everyday skills for grown folks, at your own pace: money, home, cooking, career moves and the stuff nobody sat us down to explain.\n\nWhat is one thing you wish someone had taught you earlier?",
	);
	$uid = get_current_user_id();
	foreach ( $seeds as $slug => $text ) {
		$gid = groups_get_id( $slug );
		if ( ! $gid || groups_get_groupmeta( $gid, 'yh_seeded' ) ) {
			continue;
		}
		$aid = groups_post_update( array( 'content' => $text, 'group_id' => $gid, 'user_id' => $uid ) );
		if ( $aid ) {
			groups_update_groupmeta( $gid, 'yh_seeded', $aid );
			$out[] = 'Welcome post added: ' . $slug;
		}
	}
	if ( function_exists( 'bp_activity_post_update' ) && ! get_option( 'yh_seeded_feed' ) ) {
		$aid = bp_activity_post_update( array( 'content' => "Welcome to Yardhood, the community culture magazine.\n\nPull up a chair: join a space in The Yard, RSVP to the winter movie nights in the Yard Guide, and say hello below. Tell us your name, your city and your Top Three.", 'user_id' => $uid ) );
		if ( $aid ) {
			update_option( 'yh_seeded_feed', $aid );
			$out[] = 'Welcome post added to the main feed.';
		}
	}
	return $out ? $out : array( 'Starter posts already in place.' );
}

/* ---------- Site email: send as Yardhood <hello@yardhood.com> ---------- */
function yh_mail_address() {
	$e = get_option( 'yh_contact_email', 'hello@yardhood.com' );
	return is_email( $e ) ? $e : 'hello@yardhood.com';
}
add_filter( 'wp_mail_from', function ( $from ) {
	return ( 0 === strpos( $from, 'wordpress@' ) || '' === $from ) ? yh_mail_address() : $from;
} );
add_filter( 'wp_mail_from_name', function ( $name ) {
	return ( 'WordPress' === $name || '' === $name ) ? 'Yardhood' : $name;
} );
add_action( 'phpmailer_init', function ( $m ) {
	if ( empty( $m->Sender ) ) {
		$m->Sender = yh_mail_address();
	}
} );
add_action( 'admin_post_yh_mail_test', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'No' );
	}
	$ok = wp_mail( yh_mail_address(), 'Yardhood test email', "This is a test from yardhood.com.\n\nIf you can read this, the site's email is working." );
	wp_die( 'YH_MAIL ' . ( $ok ? 'sent' : 'failed' ) );
} );

/* ---------- Email: send through the hello@ mailbox (SMTP) and brand the emails ---------- */
add_action( 'phpmailer_init', function ( $m ) {
	$pass = (string) get_option( 'yh_smtp_pass', '' );
	if ( ! $pass ) {
		return;
	}
	$addr = yh_mail_address();
	$m->isSMTP();
	$m->Host       = 'smtp.hostinger.com';
	$m->Port       = 465;
	$m->SMTPSecure = 'ssl';
	$m->SMTPAuth   = true;
	$m->Username   = $addr;
	$m->Password   = $pass;
	$m->setFrom( $addr, 'Yardhood', false );
	$m->Sender = $addr;
}, 99 );

function yh_email_shell( $inner ) {
	$logo = defined( 'YH_URL' ) ? YH_URL . 'assets/img/yh-icon-180.png' : '';
	return '<!doctype html><html><body style="margin:0;padding:0;background:#F6EFE4;">'
		. '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F6EFE4;"><tr><td align="center" style="padding:28px 12px;">'
		. '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#FFFFFF;border-radius:14px;overflow:hidden;">'
		. '<tr><td align="center" style="background:#3B1E35;padding:26px 24px 20px;">'
		. ( $logo ? '<img src="' . esc_url( $logo ) . '" width="56" height="56" alt="" style="display:block;border:0;border-radius:12px;margin:0 auto 10px;">' : '' )
		. '<div style="font-family:Georgia,\'Times New Roman\',serif;font-size:28px;letter-spacing:3px;color:#F6EFE4;">YARDHOOD</div>'
		. '<div style="font-family:Arial,sans-serif;font-size:11px;letter-spacing:3px;color:#E3C57A;margin-top:6px;">THE COMMUNITY CULTURE MAGAZINE</div></td></tr>'
		. '<tr><td style="padding:30px 32px;font-family:Arial,Helvetica,sans-serif;font-size:16px;line-height:1.6;color:#3B1E35;">' . $inner . '</td></tr>'
		. '<tr><td align="center" style="padding:18px 24px 26px;font-family:Arial,sans-serif;font-size:12px;color:#7A6372;border-top:1px solid #EFE4D2;">Yardhood &middot; <a href="' . esc_url( home_url( '/' ) ) . '" style="color:#9A6E1F;">yardhood.com</a> &middot; Questions? ' . esc_html( yh_mail_address() ) . '</td></tr>'
		. '</table></td></tr></table></body></html>';
}

/* Plain WordPress emails (password resets, notices) get the Yardhood look. */
add_filter( 'wp_mail', function ( $a ) {
	$h = is_array( $a['headers'] ) ? implode( "\n", $a['headers'] ) : (string) $a['headers'];
	if ( false !== stripos( $h, 'text/html' ) || false !== stripos( (string) $a['message'], '<html' ) ) {
		return $a;
	}
	$body = make_clickable( nl2br( esc_html( (string) $a['message'] ) ) );
	$body = preg_replace( '#<a #', '<a style="color:#9A6E1F;font-weight:bold;" ', $body );
	$a['message'] = yh_email_shell( $body );
	$extra        = 'Content-Type: text/html; charset=UTF-8';
	if ( is_array( $a['headers'] ) ) {
		$a['headers'][] = $extra;
	} else {
		$a['headers'] = trim( $a['headers'] . "\n" . $extra );
	}
	return $a;
} );

/* BuddyBoss emails (signup confirmation, notifications): brand colors, once. */
add_action( 'admin_init', function () {
	if ( get_option( 'yh_email_look_v1' ) ) {
		return;
	}
	$o = (array) get_option( 'bp_email_options', array() );
	$o = array_merge( $o, array(
		'email_bg'          => '#F6EFE4',
		'header_bg'         => '#3B1E35',
		'header_text_color' => '#F6EFE4',
		'header_text_size'  => 30,
		'body_bg'           => '#FFFFFF',
		'body_text_color'   => '#3B1E35',
		'body_text_size'    => 16,
		'highlight_color'   => '#9A6E1F',
		'footer_bg'         => '#F6EFE4',
		'footer_text_color' => '#7A6372',
		'footer_text_size'  => 12,
		'footer_text'       => 'Yardhood · The Community Culture Magazine · yardhood.com',
	) );
	update_option( 'bp_email_options', $o );
	update_option( 'yh_email_look_v1', 1 );
} );

/* Owner page: email sending section. */
add_action( 'yh_owner_page_sections', function () {
	$note = '';
	if ( isset( $_POST['yh_owner_mail'] ) && check_admin_referer( 'yh_owner_mail' ) ) {
		$p = isset( $_POST['yh_smtp_pass'] ) ? (string) wp_unslash( $_POST['yh_smtp_pass'] ) : '';
		if ( '' !== $p ) {
			update_option( 'yh_smtp_pass', $p, false );
			$err = '';
			add_action( 'wp_mail_failed', function ( $e ) use ( &$err ) {
				$err = $e->get_error_message();
			} );
			$ok   = wp_mail( yh_mail_address(), 'Yardhood email is connected', "This came through the hello@yardhood.com mailbox.\n\nMembers' emails will now land in the inbox instead of spam." );
			$note = $ok ? 'Connected. A test email was sent to ' . yh_mail_address() . '.' : 'That password did not work (' . $err . '). Nothing else changed; emails still send the old way.';
			if ( ! $ok ) {
				delete_option( 'yh_smtp_pass' );
			}
		}
	}
	echo '<h2>Email sending</h2>';
	if ( $note ) {
		echo '<div class="notice notice-info"><p>' . esc_html( $note ) . '</p></div>';
	}
	echo '<p>Status: <strong>' . ( get_option( 'yh_smtp_pass' ) ? 'connected to ' . esc_html( yh_mail_address() ) : 'not connected (emails may land in spam)' ) . '</strong></p>';
	echo '<form method="post">';
	wp_nonce_field( 'yh_owner_mail' );
	echo '<input type="hidden" name="yh_owner_mail" value="1"><p><input type="password" name="yh_smtp_pass" class="regular-text" autocomplete="new-password" placeholder="Password for ' . esc_attr( yh_mail_address() ) . '"></p><p><button class="button button-primary">Connect email</button></p></form>';
} );
