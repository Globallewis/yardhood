<?php
/**
 * Perspectives: the member opinion and essay column, organized by the 12 Pillars.
 * Members write with a two-part headline and guiding questions; every essay waits
 * for editor review. Columnists (pen-name voices) are labeled as columnists.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function yh_pillars() {
	return array(
		'family'       => array( 'Family', "What did your family teach you without ever saying it out loud?\nWhat would you keep for the next generation, and what would you change?" ),
		'self'         => array( 'Self', "What do you know about yourself now that you wish you knew ten years ago?\nWhere do you feel most like yourself, and why?" ),
		'purpose'      => array( 'Purpose & Passion', "When did you realize what you were meant to do with your life?\nWhat keeps you going when it gets hard?" ),
		'money'        => array( 'Security, Money & Economics', "What did you learn about money growing up, and is it still true for you?\nWhat does security actually look like in your life?" ),
		'community'    => array( 'Community', "Who showed up for you when it counted?\nWhat makes a place feel like home?" ),
		'health'       => array( 'Body & Health', "What does taking care of your body look like in a real week?\nWhat one habit changed things for you?" ),
		'spirituality' => array( 'Spirituality', "What grounds you when life gets loud?\nWhere do you find meaning outside of work?" ),
		'mind'         => array( 'Mental Health & Mind', "What helps you clear your head?\nWhat do you wish people understood about carrying a lot?" ),
		'creativity'   => array( 'Creativity, Expression & Play', "What do you make just because you love making it?\nWhen did you last play with no goal at all?" ),
		'environment'  => array( 'Environment & Space', "How does the place you live shape how you feel?\nWhat would you change about your space if you could?" ),
		'legacy'       => array( 'Legacy & Transition', "What do you want to leave behind?\nWhat change taught you the most?" ),
		'mastery'      => array( 'Integration & Mastery', "What does a whole, well-lived life look like to you?\nWhere do the parts of your life finally line up?" ),
	);
}

function yh_default_prompts() {
	return "What are your top three thoughts on this, and why?\nWhat did you see or live through that shaped your view?\nWhat do you want a reader to think about after they finish?";
}

add_action( 'init', function () {
	register_post_type( 'yh_perspective', array(
		'labels'        => array(
			'name'          => 'Perspectives',
			'singular_name' => 'Perspective',
			'add_new_item'  => 'Add perspective',
			'edit_item'     => 'Edit perspective',
			'all_items'     => 'All perspectives',
		),
		'public'        => true,
		'has_archive'   => 'perspectives',
		'rewrite'       => array( 'slug' => 'perspectives', 'with_front' => false ),
		'menu_icon'     => 'dashicons-format-chat',
		'menu_position' => 23,
		'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'revisions' ),
		'show_in_rest'  => true,
	) );
	register_taxonomy( 'yh_pillar', array( 'yh_perspective', 'yh_story' ), array(
		'labels'            => array( 'name' => 'Pillars', 'singular_name' => 'Pillar', 'edit_item' => 'Edit pillar' ),
		'hierarchical'      => true,
		'public'            => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'pillar', 'with_front' => false ),
	) );
}, 11 );

/* Seed pillars, the writing page and the menu item once. */
add_action( 'admin_init', function () {
	if ( get_option( 'yh_perspectives_v1' ) ) {
		return;
	}
	$i = 1;
	foreach ( yh_pillars() as $slug => $d ) {
		if ( ! term_exists( $slug, 'yh_pillar' ) ) {
			$t = wp_insert_term( $d[0], 'yh_pillar', array( 'slug' => $slug, 'description' => $d[1] ) );
			if ( ! is_wp_error( $t ) ) {
				update_term_meta( $t['term_id'], '_yh_order', $i );
			}
		}
		$i++;
	}
	if ( function_exists( 'yh_upsert_page' ) ) {
		yh_upsert_page( 'write-a-perspective', 'Write a Perspective', 'page-templates/write.php', '[yardhood_perspective_form]' );
	}
	$menu = wp_get_nav_menu_object( 'Yardhood Main' );
	if ( $menu ) {
		$items = wp_get_nav_menu_items( $menu->term_id );
		$has   = false;
		$after = 0;
		foreach ( (array) $items as $it ) {
			if ( false !== strpos( $it->url, '/perspectives/' ) ) {
				$has = true;
			}
			if ( false !== strpos( $it->url, '/stories/' ) ) {
				$after = (int) $it->menu_order;
			}
		}
		if ( ! $has ) {
			foreach ( (array) $items as $it ) {
				if ( $after && (int) $it->menu_order > $after ) {
					wp_update_nav_menu_item( $menu->term_id, $it->ID, array(
						'menu-item-title'     => $it->title,
						'menu-item-url'       => $it->url,
						'menu-item-type'      => $it->type,
						'menu-item-object'    => $it->object,
						'menu-item-object-id' => $it->object_id,
						'menu-item-status'    => 'publish',
						'menu-item-classes'   => implode( ' ', (array) $it->classes ),
						'menu-item-position'  => (int) $it->menu_order + 1,
					) );
				}
			}
			wp_update_nav_menu_item( $menu->term_id, 0, array(
				'menu-item-title'    => 'Perspectives',
				'menu-item-url'      => home_url( '/perspectives/' ),
				'menu-item-type'     => 'custom',
				'menu-item-status'   => 'publish',
				'menu-item-position' => $after ? $after + 1 : 0,
			) );
		}
	}
	flush_rewrite_rules();
	update_option( 'yh_perspectives_v1', 1 );
} );

function yh_pillar_terms() {
	$terms = get_terms( array( 'taxonomy' => 'yh_pillar', 'hide_empty' => false ) );
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	usort( $terms, function ( $a, $b ) {
		return (int) get_term_meta( $a->term_id, '_yh_order', true ) - (int) get_term_meta( $b->term_id, '_yh_order', true );
	} );
	return $terms;
}

/* Pillar pages show essays and stories together. */
add_action( 'pre_get_posts', function ( $q ) {
	if ( ! is_admin() && $q->is_main_query() && $q->is_tax( 'yh_pillar' ) ) {
		$q->set( 'post_type', array( 'yh_perspective', 'yh_story' ) );
	}
} );

/* ---------- Editor fields ---------- */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'yh_persp', 'Headline and byline', 'yh_perspective_metabox', 'yh_perspective', 'normal', 'high' );
} );

function yh_perspective_metabox( $post ) {
	wp_nonce_field( 'yh_persp_meta', 'yh_persp_nonce' );
	$sub  = get_post_meta( $post->ID, '_yh_subtitle', true );
	$by   = get_post_meta( $post->ID, '_yh_byline', true );
	$col  = get_post_meta( $post->ID, '_yh_columnist', true );
	$bio  = get_post_meta( $post->ID, '_yh_bio', true );
	echo '<p><label><strong>Subtitle</strong> (the line under the headline)<br><input type="text" name="_yh_subtitle" value="' . esc_attr( $sub ) . '" style="width:100%"></label></p>';
	echo '<p><label><strong>Byline</strong> (name shown on the essay)<br><input type="text" name="_yh_byline" value="' . esc_attr( $by ) . '" style="width:100%"></label></p>';
	echo '<p><label><input type="checkbox" name="_yh_columnist" value="1" ' . checked( $col, '1', false ) . '> This is a Yardhood columnist (pen name). Shows a "Yardhood Columnist" label instead of "Member".</label></p>';
	echo '<p><label><strong>Short bio</strong> (shown at the end)<br><textarea name="_yh_bio" rows="3" style="width:100%">' . esc_textarea( $bio ) . '</textarea></label></p>';
	$sub_raw = get_post_meta( $post->ID, '_yh_submission', true );
	if ( $sub_raw ) {
		echo '<p style="color:#666">Submitted ' . esc_html( $sub_raw ) . '</p>';
	}
}

add_action( 'save_post_yh_perspective', function ( $post_id ) {
	if ( ! isset( $_POST['yh_persp_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['yh_persp_nonce'] ) ), 'yh_persp_meta' ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, '_yh_subtitle', sanitize_text_field( wp_unslash( $_POST['_yh_subtitle'] ?? '' ) ) );
	update_post_meta( $post_id, '_yh_byline', sanitize_text_field( wp_unslash( $_POST['_yh_byline'] ?? '' ) ) );
	update_post_meta( $post_id, '_yh_columnist', empty( $_POST['_yh_columnist'] ) ? '0' : '1' );
	update_post_meta( $post_id, '_yh_bio', sanitize_textarea_field( wp_unslash( $_POST['_yh_bio'] ?? '' ) ) );
} );

/* Pending count on the admin menu so new essays are easy to spot. */
add_action( 'admin_menu', function () {
	global $menu;
	$n = (int) wp_count_posts( 'yh_perspective' )->pending;
	if ( ! $n || ! is_array( $menu ) ) {
		return;
	}
	foreach ( $menu as $k => $m ) {
		if ( isset( $m[2] ) && 'edit.php?post_type=yh_perspective' === $m[2] ) {
			$menu[ $k ][0] .= ' <span class="awaiting-mod"><span class="pending-count">' . $n . '</span></span>';
		}
	}
}, 99 );

function yh_perspective_byline( $id ) {
	$by = get_post_meta( $id, '_yh_byline', true );
	if ( ! $by ) {
		$by = get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $id ) );
	}
	return $by;
}

function yh_perspective_label( $id ) {
	return '1' === get_post_meta( $id, '_yh_columnist', true ) ? 'Yardhood Columnist' : 'Member Perspective';
}

/* ---------- The writing form ---------- */
add_shortcode( 'yardhood_perspective_form', function () {
	if ( isset( $_GET['sent'] ) ) {
		return '<div class="yh-notice yh-notice-ok"><h3>Thank you. Your perspective is in.</h3><p>An editor will read it and may suggest small edits before it runs. You will get an email when it is published.</p></div>';
	}
	if ( ! is_user_logged_in() ) {
		return '<div class="yh-notice"><h3>Members write Perspectives.</h3><p>Joining is free. Once you are in, come back here and write.</p><p><a class="yh-btn yh-btn-gold" href="' . esc_url( wp_registration_url() ) . '">Join free</a> <a class="yh-btn yh-btn-plum" href="' . esc_url( wp_login_url( get_permalink() ) ) . '">Sign in</a></p></div>';
	}
	$err   = isset( $_GET['yh_err'] ) ? sanitize_text_field( wp_unslash( $_GET['yh_err'] ) ) : '';
	$u     = wp_get_current_user();
	$terms = yh_pillar_terms();
	$pre   = isset( $_GET['pillar'] ) ? sanitize_key( wp_unslash( $_GET['pillar'] ) ) : '';
	ob_start();
	if ( $err ) {
		echo '<div class="yh-notice yh-notice-err">' . esc_html( $err ) . '</div>';
	}
	?>
	<form class="yh-form yh-persp-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'yh_submit_perspective', 'yh_nonce' ); ?>
		<input type="hidden" name="action" value="yh_submit_perspective">
		<div class="yh-hp" aria-hidden="true"><label>Leave this empty <input type="text" name="yh_website" tabindex="-1" autocomplete="off"></label></div>
		<fieldset>
			<legend>Your topic</legend>
			<label>Pillar
				<select name="pillar" id="yh-pillar" required>
					<option value="" data-prompts="<?php echo esc_attr( yh_default_prompts() ); ?>">Choose one</option>
					<?php foreach ( $terms as $t ) : ?>
						<option value="<?php echo esc_attr( $t->slug ); ?>" data-prompts="<?php echo esc_attr( $t->description ); ?>" <?php selected( $pre, $t->slug ); ?>><?php echo esc_html( $t->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<div class="yh-prompts" id="yh-prompts" aria-live="polite">
				<p class="yh-prompts-kick">Questions to get you started</p>
				<ul><?php foreach ( explode( "\n", yh_default_prompts() ) as $q ) : ?><li><?php echo esc_html( $q ); ?></li><?php endforeach; ?></ul>
				<p class="yh-help">Use them or ignore them. They are a starting point, not a form to fill in.</p>
			</div>
		</fieldset>
		<fieldset>
			<legend>Your headline</legend>
			<label>Title <span>(your main point, in a few words)</span><input type="text" name="title" required maxlength="120" placeholder="Why every block needs a porch"></label>
			<label>Subtitle <span>(one line that explains it)</span><input type="text" name="subtitle" required maxlength="180" placeholder="What front steps taught me about trust"></label>
		</fieldset>
		<fieldset>
			<legend>Your perspective</legend>
			<label>Write it your way <span>(about 300 to 1,200 words works well)</span><textarea name="body" rows="16" required maxlength="12000"></textarea></label>
			<label>Byline <span>(the name readers will see)</span><input type="text" name="byline" required maxlength="80" value="<?php echo esc_attr( $u->display_name ); ?>"></label>
		</fieldset>
		<fieldset class="yh-consent">
			<legend>Permissions</legend>
			<label class="yh-check"><input type="checkbox" name="c_own" value="1" required> These are my own words and my own views.</label>
			<label class="yh-check"><input type="checkbox" name="c_publish" value="1" required> Yardhood may edit lightly for length and clarity and publish this on yardhood.com and in its back issues. I keep the copyright.</label>
			<label class="yh-check"><input type="checkbox" name="c_social" value="1"> Yardhood may share short excerpts on social media.</label>
			<label class="yh-check"><input type="checkbox" name="c_terms" value="1" required> I have read and agree to the <a href="<?php echo esc_url( yh_page_url( 'story-permissions' ) ); ?>" target="_blank" rel="noopener">Story Permissions</a> and <a href="<?php echo esc_url( yh_page_url( 'community-guidelines' ) ); ?>" target="_blank" rel="noopener">Community Guidelines</a>, which may be updated from time to time.</label>
		</fieldset>
		<button type="submit" class="yh-btn yh-btn-gold">Send my perspective</button>
		<p class="yh-help">Nothing is published until an editor reads it.</p>
	</form>
	<script>
	(function(){var s=document.getElementById('yh-pillar'),b=document.getElementById('yh-prompts');if(!s||!b)return;
	function upd(){var o=s.options[s.selectedIndex],p=(o&&o.getAttribute('data-prompts'))||s.options[0].getAttribute('data-prompts');var ul=b.querySelector('ul');ul.innerHTML='';p.split(/\n+/).forEach(function(q){q=q.trim();if(!q)return;var li=document.createElement('li');li.textContent=q;ul.appendChild(li);});}
	s.addEventListener('change',upd);upd();})();
	</script>
	<?php
	return ob_get_clean();
} );

add_action( 'admin_post_yh_submit_perspective', 'yh_handle_perspective' );
add_action( 'admin_post_nopriv_yh_submit_perspective', function () {
	wp_safe_redirect( wp_login_url( yh_page_url( 'write-a-perspective' ) ) );
	exit;
} );

function yh_handle_perspective() {
	$back = remove_query_arg( array( 'yh_err', 'sent' ), wp_get_referer() ? wp_get_referer() : yh_page_url( 'write-a-perspective' ) );
	$fail = function ( $m ) use ( $back ) {
		wp_safe_redirect( add_query_arg( 'yh_err', rawurlencode( $m ), $back ) );
		exit;
	};
	if ( ! isset( $_POST['yh_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['yh_nonce'] ) ), 'yh_submit_perspective' ) ) {
		$fail( 'The form expired. Please try again.' );
	}
	if ( ! empty( $_POST['yh_website'] ) ) {
		wp_safe_redirect( add_query_arg( 'sent', 1, $back ) );
		exit;
	}
	$uid = get_current_user_id();
	$key = 'yh_persp_' . $uid;
	if ( (int) get_transient( $key ) >= 3 ) {
		$fail( 'You have sent three today. Please wait until tomorrow to send another.' );
	}
	$p      = wp_unslash( $_POST );
	$title  = sanitize_text_field( $p['title'] ?? '' );
	$sub    = sanitize_text_field( $p['subtitle'] ?? '' );
	$body   = sanitize_textarea_field( $p['body'] ?? '' );
	$by     = sanitize_text_field( $p['byline'] ?? '' );
	$pillar = sanitize_key( $p['pillar'] ?? '' );
	if ( ! $title || ! $sub || ! $by || ! term_exists( $pillar, 'yh_pillar' ) ) {
		$fail( 'Please add a title, a subtitle, a byline and a pillar.' );
	}
	if ( strlen( $body ) < 300 ) {
		$fail( 'Please write a little more. About 300 words or more works best.' );
	}
	if ( empty( $p['c_own'] ) || empty( $p['c_publish'] ) || empty( $p['c_terms'] ) ) {
		$fail( 'Please check the required permission boxes.' );
	}
	$id = wp_insert_post( array(
		'post_type'    => 'yh_perspective',
		'post_status'  => 'pending',
		'post_title'   => $title,
		'post_content' => wpautop( esc_html( $body ) ),
		'post_excerpt' => $sub,
		'post_author'  => $uid,
	), true );
	if ( is_wp_error( $id ) ) {
		$fail( 'Something went wrong saving it. Please try again.' );
	}
	wp_set_object_terms( $id, $pillar, 'yh_pillar' );
	update_post_meta( $id, '_yh_subtitle', $sub );
	update_post_meta( $id, '_yh_byline', $by );
	update_post_meta( $id, '_yh_columnist', '0' );
	update_post_meta( $id, '_yh_submission', wp_date( 'M j, Y g:i a' ) . ' · social excerpts: ' . ( empty( $p['c_social'] ) ? 'no' : 'yes' ) );
	set_transient( $key, (int) get_transient( $key ) + 1, DAY_IN_SECONDS );
	wp_mail( get_option( 'admin_email' ), 'New Perspective to review: ' . $title, $by . " sent a Perspective.\n\n" . $title . "\n" . $sub . "\n\nReview it here: " . admin_url( 'post.php?post=' . $id . '&action=edit' ) );
	wp_safe_redirect( add_query_arg( 'sent', 1, $back ) );
	exit;
}

/* Email the writer when their essay goes live. */
add_action( 'transition_post_status', function ( $new, $old, $post ) {
	if ( 'yh_perspective' !== $post->post_type || 'publish' !== $new || 'publish' === $old ) {
		return;
	}
	if ( '1' === get_post_meta( $post->ID, '_yh_columnist', true ) ) {
		return;
	}
	$u = get_userdata( (int) $post->post_author );
	if ( $u && ! user_can( $u, 'manage_options' ) ) {
		wp_mail( $u->user_email, 'Your Perspective is live on Yardhood', "Your piece \"" . $post->post_title . "\" is published.\n\nRead it and share it: " . get_permalink( $post ) );
	}
}, 10, 3 );
