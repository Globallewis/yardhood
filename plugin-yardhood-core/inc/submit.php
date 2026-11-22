<?php
/**
 * Submit Your Story: one form for artists, careers, entrepreneurs and community.
 * Saves a Story as "Pending review" with the raw answers and the permissions given.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode( 'yardhood_submit_story', 'yh_submit_story_form' );

function yh_submit_story_form() {
	if ( isset( $_GET['submitted'] ) ) {
		return '<div class="yh-notice yh-notice-ok"><h3>Thank you. Your story is in.</h3><p>An editor will read it and reach out by email before anything is published. You can send another anytime.</p></div>';
	}
	$err = isset( $_GET['yh_err'] ) ? sanitize_text_field( wp_unslash( $_GET['yh_err'] ) ) : '';
	$u   = wp_get_current_user();
	$max = size_format( wp_max_upload_size() );
	ob_start();
	if ( $err ) {
		echo '<div class="yh-notice yh-notice-err">' . esc_html( $err ) . '</div>';
	}
	?>
	<form class="yh-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'yh_submit_story', 'yh_nonce' ); ?>
		<input type="hidden" name="action" value="yh_submit_story">
		<div class="yh-hp" aria-hidden="true"><label>Leave this empty <input type="text" name="yh_website" tabindex="-1" autocomplete="off"></label></div>

		<fieldset>
			<legend>About you</legend>
			<label>Your name <span>(how you want it shown)</span><input type="text" name="name" required maxlength="120" value="<?php echo esc_attr( $u->exists() ? $u->display_name : '' ); ?>"></label>
			<label>Email <span>(never published)</span><input type="email" name="email" required maxlength="160" value="<?php echo esc_attr( $u->exists() ? $u->user_email : '' ); ?>"></label>
			<div class="yh-row">
				<label>What you do<input type="text" name="discipline" maxlength="120" placeholder="Painter, chef, nurse, barber, founder"></label>
				<label>City<input type="text" name="city" maxlength="120"></label>
			</div>
			<label>Story type
				<select name="type" required>
					<option value="">Choose one</option>
					<?php foreach ( yh_story_types() as $k => $label ) : ?>
						<option value="<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</fieldset>

		<fieldset>
			<legend>Your story</legend>
			<label>When did you realize what you were meant to do with your life?<textarea name="q_open" rows="4" maxlength="3000" required></textarea></label>
			<label>Tell us the journey. Where you started, the turning point, what you are building now.<textarea name="q_journey" rows="7" maxlength="8000"></textarea></label>
			<label>What do you want to leave behind for the people coming after you?<textarea name="q_legacy" rows="3" maxlength="3000"></textarea></label>
		</fieldset>

		<fieldset>
			<legend>The Top Three <span>(optional)</span></legend>
			<p class="yh-help">What are the three most important things in your life? Name them your way.</p>
			<div class="yh-row yh-row-3">
				<input type="text" name="top1" maxlength="60" aria-label="First">
				<input type="text" name="top2" maxlength="60" aria-label="Second">
				<input type="text" name="top3" maxlength="60" aria-label="Third">
			</div>
			<label class="yh-check"><input type="checkbox" name="top_publish" value="1"> You can show my Top Three on my feature.</label>
		</fieldset>

		<fieldset>
			<legend>Photos, work and video</legend>
			<label>A portrait of you <span>(JPG or PNG, up to 10 MB)</span><input type="file" name="portrait" accept="image/jpeg,image/png,image/webp"></label>
			<label>Up to 3 pieces of your work <span>(images, up to 10 MB each)</span><input type="file" name="work[]" accept="image/jpeg,image/png,image/webp" multiple></label>
			<label>Video link <span>(up to 3 minutes; YouTube, Vimeo, Google Drive or Instagram)</span><input type="url" name="video_url" maxlength="400" placeholder="https://"></label>
			<label>Or upload a short video <span>(MP4 or MOV, up to <?php echo esc_html( $max ); ?>)</span><input type="file" name="video" accept="video/mp4,video/quicktime,video/webm"></label>
			<label>Links <span>(your site, shop, portfolio, socials)</span><textarea name="links" rows="2" maxlength="1000"></textarea></label>
		</fieldset>

		<fieldset class="yh-consent">
			<legend>Permissions</legend>
			<p class="yh-help">You keep the copyright to your story and your work. These boxes give Yardhood a non-exclusive license to use it as described in the <a href="<?php echo esc_url( yh_page_url( 'story-permissions' ) ); ?>" target="_blank" rel="noopener">Story Permissions</a>. If you want something taken down later, email us and we will review it.</p>
			<label class="yh-check"><input type="checkbox" name="c_age" value="1" required> I am 18 or older.</label>
			<label class="yh-check"><input type="checkbox" name="c_own" value="1" required> This is my story, and I own or have the right to share everything I am sending.</label>
			<label class="yh-check"><input type="checkbox" name="c_publish" value="1" required> Yardhood may publish an edited feature from what I send, on yardhood.com and in its back issues.</label>
			<label class="yh-check"><input type="checkbox" name="c_video" value="1"> If I sent a video, Yardhood may publish it with my feature.</label>
			<label class="yh-check"><input type="checkbox" name="c_social" value="1"> Yardhood may share short excerpts on social media to promote my feature.</label>
			<label class="yh-check"><input type="checkbox" name="c_interview" value="1"> I am open to a longer Zoom conversation if I am selected.</label>
			<label class="yh-check"><input type="checkbox" name="c_plaza" value="1"> I own a business and want to hear about listing it on <a href="https://legacyplaza.co" target="_blank" rel="noopener">legacyplaza.co</a>.</label>
			<label class="yh-check"><input type="checkbox" name="c_terms" value="1" required> I have read and agree to the <a href="<?php echo esc_url( yh_page_url( 'story-permissions' ) ); ?>" target="_blank" rel="noopener">Story Permissions</a>, which may be updated from time to time.</label>
		</fieldset>

		<button type="submit" class="yh-btn yh-btn-gold">Send my story</button>
		<p class="yh-help">Nothing is published until an editor reviews it with you.</p>
	</form>
	<?php
	return ob_get_clean();
}

add_action( 'admin_post_yh_submit_story', 'yh_handle_story_submission' );
add_action( 'admin_post_nopriv_yh_submit_story', 'yh_handle_story_submission' );

function yh_handle_story_submission() {
	$back = wp_get_referer() ? wp_get_referer() : yh_page_url( 'submit-your-story' );
	$back = remove_query_arg( array( 'yh_err', 'submitted' ), $back );
	$fail = function ( $msg ) use ( $back ) {
		wp_safe_redirect( add_query_arg( 'yh_err', rawurlencode( $msg ), $back ) . '#submit' );
		exit;
	};

	if ( ! isset( $_POST['yh_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['yh_nonce'] ) ), 'yh_submit_story' ) ) {
		$fail( 'The form expired. Please try again.' );
	}
	if ( ! empty( $_POST['yh_website'] ) ) {
		wp_safe_redirect( add_query_arg( 'submitted', 1, $back ) );
		exit;
	}
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'x';
	$key = 'yh_sub_' . md5( $ip );
	$n   = (int) get_transient( $key );
	if ( $n >= 4 ) {
		$fail( 'Thanks for the enthusiasm. Please wait an hour before sending another story.' );
	}

	$p    = wp_unslash( $_POST );
	$name = sanitize_text_field( $p['name'] ?? '' );
	$mail = sanitize_email( $p['email'] ?? '' );
	$type = sanitize_key( $p['type'] ?? '' );
	if ( ! $name || ! is_email( $mail ) || ! array_key_exists( $type, yh_story_types() ) ) {
		$fail( 'Please add your name, a valid email and a story type.' );
	}
	if ( empty( $p['c_age'] ) || empty( $p['c_own'] ) || empty( $p['c_publish'] ) || empty( $p['c_terms'] ) ) {
		$fail( 'Please check the required permission boxes, including the Story Permissions agreement.' );
	}
	$open    = sanitize_textarea_field( $p['q_open'] ?? '' );
	$journey = sanitize_textarea_field( $p['q_journey'] ?? '' );
	$legacy  = sanitize_textarea_field( $p['q_legacy'] ?? '' );
	if ( strlen( $open ) < 10 ) {
		$fail( 'Please answer the first question so we can get to know you.' );
	}

	$content  = "<!-- Submitted answers. Edit into a feature before publishing. -->\n";
	$content .= '<h3>When did you realize what you were meant to do with your life?</h3>' . wpautop( esc_html( $open ) );
	if ( $journey ) {
		$content .= '<h3>The journey</h3>' . wpautop( esc_html( $journey ) );
	}
	if ( $legacy ) {
		$content .= '<h3>What do you want to leave behind for the people coming after you?</h3>' . wpautop( esc_html( $legacy ) );
	}

	$post_id = wp_insert_post( array(
		'post_type'    => 'yh_story',
		'post_status'  => 'pending',
		'post_title'   => $name . ' · ' . yh_story_types()[ $type ],
		'post_content' => $content,
		'post_author'  => get_current_user_id(),
	), true );
	if ( is_wp_error( $post_id ) ) {
		$fail( 'Something went wrong saving your story. Please try again.' );
	}
	wp_set_object_terms( $post_id, yh_story_types()[ $type ], 'yh_story_type' );

	$meta = array(
		'_yh_name'        => $name,
		'_yh_discipline'  => sanitize_text_field( $p['discipline'] ?? '' ),
		'_yh_city'        => sanitize_text_field( $p['city'] ?? '' ),
		'_yh_video'       => esc_url_raw( $p['video_url'] ?? '' ),
		'_yh_top1'        => sanitize_text_field( $p['top1'] ?? '' ),
		'_yh_top2'        => sanitize_text_field( $p['top2'] ?? '' ),
		'_yh_top3'        => sanitize_text_field( $p['top3'] ?? '' ),
		'_yh_top_publish' => empty( $p['top_publish'] ) ? '0' : '1',
		'_yh_contact'     => $mail,
	);
	foreach ( $meta as $k => $v ) {
		update_post_meta( $post_id, $k, $v );
	}

	// Uploads.
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$uploaded = array();
	$img_ok   = array( 'image/jpeg', 'image/png', 'image/webp' );
	$vid_ok   = array( 'video/mp4', 'video/quicktime', 'video/webm' );
	$one = function ( $file, $allowed, $max_bytes ) use ( $post_id ) {
		if ( empty( $file['name'] ) || UPLOAD_ERR_OK !== $file['error'] ) {
			return 0;
		}
		if ( $file['size'] > $max_bytes ) {
			return 0;
		}
		$check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'] );
		if ( empty( $check['type'] ) || ! in_array( $check['type'], $allowed, true ) ) {
			return 0;
		}
		$_FILES['yh_single'] = $file;
		$id = media_handle_upload( 'yh_single', $post_id );
		return is_wp_error( $id ) ? 0 : $id;
	};
	if ( ! empty( $_FILES['portrait'] ) ) {
		$pid = $one( $_FILES['portrait'], $img_ok, 10 * MB_IN_BYTES );
		if ( $pid ) {
			set_post_thumbnail( $post_id, $pid );
			$uploaded['portrait'] = wp_get_attachment_url( $pid );
		}
	}
	if ( ! empty( $_FILES['work']['name'] ) && is_array( $_FILES['work']['name'] ) ) {
		$count = min( 3, count( $_FILES['work']['name'] ) );
		for ( $i = 0; $i < $count; $i++ ) {
			$f = array(
				'name'     => $_FILES['work']['name'][ $i ],
				'type'     => $_FILES['work']['type'][ $i ],
				'tmp_name' => $_FILES['work']['tmp_name'][ $i ],
				'error'    => $_FILES['work']['error'][ $i ],
				'size'     => $_FILES['work']['size'][ $i ],
			);
			$wid = $one( $f, $img_ok, 10 * MB_IN_BYTES );
			if ( $wid ) {
				$uploaded[ 'work ' . ( $i + 1 ) ] = wp_get_attachment_url( $wid );
			}
		}
	}
	if ( ! empty( $_FILES['video'] ) ) {
		$vid = $one( $_FILES['video'], $vid_ok, wp_max_upload_size() );
		if ( $vid ) {
			$uploaded['video file'] = wp_get_attachment_url( $vid );
		}
	}

	$yes = function ( $k ) use ( $p ) {
		return empty( $p[ $k ] ) ? 'no' : 'yes';
	};
	$record = array(
		'Submitted'                    => wp_date( 'M j, Y g:i a T' ),
		'Name'                         => $name,
		'Email'                        => $mail,
		'Story type'                   => yh_story_types()[ $type ],
		'Links'                        => sanitize_textarea_field( $p['links'] ?? '' ),
		'Video link'                   => $meta['_yh_video'],
		'Files'                        => $uploaded ? implode( "\n", array_map( function ( $k, $v ) { return $k . ': ' . $v; }, array_keys( $uploaded ), $uploaded ) ) : 'none',
		'18 or older'                  => $yes( 'c_age' ),
		'Owns / has rights'            => $yes( 'c_own' ),
		'Publish feature + back issues'=> $yes( 'c_publish' ),
		'Publish video'                => $yes( 'c_video' ),
		'Social excerpts'              => $yes( 'c_social' ),
		'Show Top Three'               => $meta['_yh_top_publish'] ? 'yes' : 'no',
		'Open to Zoom interview'       => $yes( 'c_interview' ),
		'Legacy Plaza info'            => $yes( 'c_plaza' ),
		'Agreed to Story Permissions'  => $yes( 'c_terms' ) . ' (version ' . get_option( 'yh_permissions_version', '2026-11-22' ) . ')',
		'Logged-in member'             => is_user_logged_in() ? 'yes (user ' . get_current_user_id() . ')' : 'no',
	);
	update_post_meta( $post_id, '_yh_submission', $record );
	set_transient( $key, $n + 1, HOUR_IN_SECONDS );

	wp_mail(
		get_option( 'admin_email' ),
		'New Yardhood story: ' . $name,
		"A new story is waiting for review.\n\n" . $name . ' · ' . yh_story_types()[ $type ] . "\n\nReview it here: " . admin_url( 'post.php?post=' . $post_id . '&action=edit' )
	);

	wp_safe_redirect( add_query_arg( 'submitted', 1, $back ) . '#submit' );
	exit;
}

/* Simple "request to host a group" form for The Hood page. */
add_shortcode( 'yardhood_host_request', function () {
	if ( isset( $_GET['host_sent'] ) ) {
		return '<div class="yh-notice yh-notice-ok"><p>Got it. We will email you about hosting your group.</p></div>';
	}
	if ( ! is_user_logged_in() ) {
		return '<p><a class="yh-btn yh-btn-gold" href="' . esc_url( yh_join_url() ) . '">Join free to host a group</a></p>';
	}
	ob_start();
	?>
	<form class="yh-form yh-form-compact" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'yh_host_request', 'yh_nonce' ); ?>
		<input type="hidden" name="action" value="yh_host_request">
		<label>Group name<input type="text" name="gname" required maxlength="100"></label>
		<label>Who is it for, in one line?<input type="text" name="gwho" required maxlength="200"></label>
		<label>Which fits best?
			<select name="gplan">
				<option value="free">A free community I organize</option>
				<option value="paid">I want to charge members</option>
				<option value="large">Large rooms, 50+ people</option>
			</select>
		</label>
		<button class="yh-btn yh-btn-gold">Request to host</button>
	</form>
	<?php
	return ob_get_clean();
} );

add_action( 'admin_post_yh_host_request', function () {
	if ( ! isset( $_POST['yh_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['yh_nonce'] ) ), 'yh_host_request' ) ) {
		wp_die( 'Form expired.' );
	}
	$u    = wp_get_current_user();
	$name = sanitize_text_field( wp_unslash( $_POST['gname'] ?? '' ) );
	$who  = sanitize_text_field( wp_unslash( $_POST['gwho'] ?? '' ) );
	$plan = sanitize_key( $_POST['gplan'] ?? 'free' );
	$log  = get_option( 'yh_host_requests', array() );
	$log[] = array( 't' => time(), 'user' => $u->ID, 'name' => $name, 'who' => $who, 'plan' => $plan );
	update_option( 'yh_host_requests', $log, false );
	wp_mail( get_option( 'admin_email' ), 'Yardhood host request: ' . $name, $u->display_name . ' (' . $u->user_email . ") wants to host.\n\nGroup: " . $name . "\nFor: " . $who . "\nPlan: " . $plan );
	$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	wp_safe_redirect( add_query_arg( 'host_sent', 1, remove_query_arg( 'host_sent', $back ) ) );
	exit;
} );
