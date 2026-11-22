<?php
/**
 * Tools > Yardhood: one-time setup steps and the launch switch.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', function () {
	add_management_page( 'Yardhood', 'Yardhood setup', 'manage_options', 'yardhood-setup', 'yh_setup_page' );
} );

function yh_home_root() {
	// e.g. /home/u123/domains/yardhood.com/public_html/ -> /home/u123/
	if ( preg_match( '#^(/home/[^/]+/)#', ABSPATH, $m ) ) {
		return $m[1];
	}
	return '';
}

function yh_bb_source_dirs() {
	$root = yh_home_root();
	$base = $root . 'domains/thewildernesscollective.co/public_html/portal/wp-content/';
	return array(
		'buddyboss-platform'     => array( $base . 'plugins/buddyboss-platform', WP_PLUGIN_DIR . '/buddyboss-platform' ),
		'buddyboss-platform-pro' => array( $base . 'plugins/buddyboss-platform-pro', WP_PLUGIN_DIR . '/buddyboss-platform-pro' ),
		'buddyboss-theme'        => array( $base . 'themes/buddyboss-theme', get_theme_root() . '/buddyboss-theme' ),
	);
}

/** Read-only on the source; never writes to the ILA portal. */
function yh_copy_dir( $src, $dst ) {
	if ( ! is_dir( $src ) ) {
		return new WP_Error( 'nosrc', 'Source not found: ' . $src );
	}
	if ( is_dir( $dst ) ) {
		return new WP_Error( 'exists', basename( $dst ) . ' is already installed.' );
	}
	$tmp = $dst . '.yhtmp';
	wp_mkdir_p( $tmp );
	$it = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $src, FilesystemIterator::SKIP_DOTS ), RecursiveIteratorIterator::SELF_FIRST );
	$n  = 0;
	foreach ( $it as $item ) {
		$target = $tmp . substr( $item->getPathname(), strlen( $src ) );
		if ( $item->isLink() ) {
			continue;
		}
		if ( $item->isDir() ) {
			wp_mkdir_p( $target );
		} else {
			if ( ! copy( $item->getPathname(), $target ) ) {
				return new WP_Error( 'copy', 'Could not copy ' . $item->getPathname() );
			}
			$n++;
		}
	}
	rename( $tmp, $dst );
	return $n;
}

function yh_setup_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$msg = array();
	if ( isset( $_POST['yh_step'] ) && check_admin_referer( 'yh_setup' ) ) {
		@set_time_limit( 600 ); // phpcs:ignore
		$step = sanitize_key( $_POST['yh_step'] );
		if ( 0 === strpos( $step, 'copy_' ) ) {
			$which = substr( $step, 5 );
			$dirs  = yh_bb_source_dirs();
			if ( isset( $dirs[ $which ] ) ) {
				$r     = yh_copy_dir( $dirs[ $which ][0], $dirs[ $which ][1] );
				$msg[] = is_wp_error( $r ) ? 'Error: ' . $r->get_error_message() : 'Copied ' . $which . ' (' . (int) $r . ' files).';
			}
		} elseif ( 'site' === $step ) {
			$msg = array_merge( $msg, yh_setup_site() );
		} elseif ( 'bp' === $step ) {
			$msg = array_merge( $msg, yh_setup_buddyboss() );
		} elseif ( 'events' === $step ) {
			$msg = array_merge( $msg, yh_setup_draft_events() );
		} elseif ( 'pages_v2' === $step ) {
			$msg = array_merge( $msg, yh_setup_pages_v2() );
		} elseif ( 'lineup_publish' === $step ) {
			$msg = array_merge( $msg, yh_publish_lineup() );
		} elseif ( 'billing' === $step ) {
			$link = isset( $_POST['yh_link'] ) ? esc_url_raw( wp_unslash( $_POST['yh_link'] ) ) : '';
			update_option( 'yh_host_payment_link', $link );
			$key = isset( $_POST['yh_key'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['yh_key'] ) ) ) : '';
			if ( $key && 0 !== strpos( $key, 'sk_' ) ) {
				update_option( 'yh_stripe_restricted_key', $key, false );
			} elseif ( $key ) {
				$msg[] = 'That looks like a full secret key. Please create a restricted key (starts with rk_) with read access to Checkout Sessions and Subscriptions instead.';
			}
			if ( isset( $_POST['yh_mail'] ) && is_email( wp_unslash( $_POST['yh_mail'] ) ) ) {
				update_option( 'yh_contact_email', sanitize_email( wp_unslash( $_POST['yh_mail'] ) ) );
			}
			$msg[] = 'Billing settings saved.';
		} elseif ( 'brand' === $step ) {
			$msg[] = yh_apply_theme_colors();
			yh_place_widgets();
			$msg[] = 'Widgets placed in the feed, groups, members and profile sidebars.';
			$msg[] = yh_setup_house_account();
			$msg   = array_merge( $msg, yh_seed_posts() );
		} elseif ( 'profile_fields' === $step ) {
			$msg = array_merge( $msg, yh_setup_profile_fields() );
		} elseif ( 'launch_on' === $step ) {
			update_option( 'yh_prelaunch', 0 );
			$msg[] = 'Pre-launch lock is OFF. The site is public.';
		} elseif ( 'launch_off' === $step ) {
			update_option( 'yh_prelaunch', 1 );
			$msg[] = 'Pre-launch lock is ON. Only signed-in people and preview-link holders see the site.';
		}
	}
	$key     = get_option( 'yh_prelaunch_key' );
	$locked  = yh_prelaunch_active();
	$dirs    = yh_bb_source_dirs();
	$btn     = function ( $step, $label, $primary = false ) {
		echo '<form method="post" style="display:inline-block;margin:0 8px 8px 0">';
		wp_nonce_field( 'yh_setup' );
		echo '<input type="hidden" name="yh_step" value="' . esc_attr( $step ) . '"><button class="button' . ( $primary ? ' button-primary' : '' ) . '">' . esc_html( $label ) . '</button></form>';
	};
	echo '<div class="wrap"><h1>Yardhood setup</h1>';
	foreach ( $msg as $m ) {
		echo '<div class="notice notice-info"><p>' . esc_html( $m ) . '</p></div>';
	}
	echo '<h2>1. BuddyBoss files</h2><p>Copies the BuddyBoss files you already own from the ILA server folder. The ILA site is only read, never changed.</p>';
	foreach ( $dirs as $k => $d ) {
		$have = is_dir( $d[1] );
		echo '<p><strong>' . esc_html( $k ) . '</strong>: ' . ( $have ? 'installed' : ( is_dir( $d[0] ) ? 'ready to copy' : 'source not found' ) ) . '</p>';
		if ( ! $have && is_dir( $d[0] ) ) {
			$btn( 'copy_' . $k, 'Copy ' . $k );
		}
	}
	echo '<h2>2. Site structure</h2>';
	$btn( 'site', 'Create pages, menu and settings', true );
	$btn( 'bp', 'Set up BuddyBoss spaces' );
	$btn( 'events', 'Add draft winter lineup (unpublished)' );
	$btn( 'pages_v2', 'Update policy, hosting pages and footer' );
	$btn( 'lineup_publish', 'Publish and confirm the approved winter lineup' );
	$btn( 'profile_fields', 'Add Yardhood profile fields' );
	$btn( 'brand', 'Apply brand colors, widgets, house account and welcome posts' );
	echo '<h2>4. Community Host billing (Stripe)</h2><form method="post">';
	wp_nonce_field( 'yh_setup' );
	echo '<input type="hidden" name="yh_step" value="billing">';
	echo '<p><label>Stripe Payment Link for Community Host ($10/mo, 30-day trial)<br><input type="url" name="yh_link" class="regular-text" value="' . esc_attr( get_option( 'yh_host_payment_link', '' ) ) . '" placeholder="https://buy.stripe.com/..."></label></p>';
	echo '<p class="description">In Stripe, set the Payment Link to redirect after payment to: <code>' . esc_html( home_url( '/the-hood/?yh_session={CHECKOUT_SESSION_ID}' ) ) . '</code></p>';
	echo '<p><label>Stripe restricted key (read-only: Checkout Sessions, Subscriptions) ' . ( get_option( 'yh_stripe_restricted_key' ) ? '<strong>saved</strong>' : '<em>not saved</em>' ) . '<br><input type="password" name="yh_key" class="regular-text" autocomplete="off" placeholder="rk_live_..."></label></p>';
	echo '<p><label>Contact email shown on policy pages<br><input type="email" name="yh_mail" class="regular-text" value="' . esc_attr( get_option( 'yh_contact_email', 'hello@yardhood.com' ) ) . '"></label></p>';
	echo '<p><button class="button button-primary">Save billing settings</button></p></form>';
	$pending = get_option( 'yh_host_pending', array() );
	if ( $pending ) {
		echo '<p><strong>Checkouts waiting for manual approval:</strong></p><ul>';
		foreach ( $pending as $uid => $row ) {
			$u = get_userdata( $uid );
			echo '<li>' . esc_html( $u ? $u->display_name . ' (' . $u->user_email . ')' : $uid ) . ' · ' . esc_html( wp_date( 'M j, g:i a', $row['t'] ) ) . ' · <a href="' . esc_url( get_edit_user_link( $uid ) ) . '">open profile</a></li>';
		}
		echo '</ul>';
	}
	echo '<h2>3. Launch</h2><p>Status: <strong>' . ( $locked ? 'Private (pre-launch lock on)' : 'Public' ) . '</strong></p>';
	echo '<p>Preview link for testers: <code>' . esc_html( add_query_arg( 'yh_preview', $key, home_url( '/' ) ) ) . '</code></p>';
	$locked ? $btn( 'launch_on', 'Open the site to the public' ) : $btn( 'launch_off', 'Turn the pre-launch lock back on' );
	$req = get_option( 'yh_host_requests', array() );
	echo '<h2>Host requests</h2>';
	if ( ! $req ) {
		echo '<p>None yet.</p>';
	} else {
		echo '<table class="widefat striped"><thead><tr><th>When</th><th>Member</th><th>Group</th><th>For</th><th>Plan</th></tr></thead><tbody>';
		foreach ( array_reverse( $req ) as $r ) {
			$u = get_userdata( $r['user'] );
			echo '<tr><td>' . esc_html( wp_date( 'M j, g:i a', $r['t'] ) ) . '</td><td>' . esc_html( $u ? $u->display_name . ' (' . $u->user_email . ')' : '?' ) . '</td><td>' . esc_html( $r['name'] ) . '</td><td>' . esc_html( $r['who'] ) . '</td><td>' . esc_html( $r['plan'] ) . '</td></tr>';
		}
		echo '</tbody></table>';
	}
	echo '</div>';
}

function yh_upsert_page( $slug, $title, $template = '', $content = '' ) {
	$p = get_page_by_path( $slug );
	if ( $p ) {
		$id = $p->ID;
	} else {
		$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $title, 'post_content' => $content ) );
	}
	if ( $template && $id && ! is_wp_error( $id ) ) {
		update_post_meta( $id, '_wp_page_template', $template );
	}
	return $id;
}

function yh_setup_site() {
	$out = array();
	update_option( 'timezone_string', 'America/New_York' );
	update_option( 'blog_public', 0 );
	update_option( 'blogdescription', 'The Community Culture Magazine' );
	update_option( 'users_can_register', 1 );
	update_option( 'default_role', 'subscriber' );
	update_option( 'default_comment_status', 'closed' );
	if ( ! get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}
	$cover = yh_upsert_page( 'cover', 'Cover' );
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $cover );
	$pages = array(
		'inside-the-issue'  => array( 'Inside the Issue', 'page-templates/inside.php', '' ),
		'movie-nights'      => array( 'Movie Nights', 'page-templates/movie-nights.php', '' ),
		'the-yard'          => array( 'The Yard', 'page-templates/the-yard.php', '' ),
		'the-hood'          => array( 'The Hood', 'page-templates/the-hood.php', '' ),
		'submit-your-story' => array( 'Submit Your Story', 'page-templates/submit.php', '[yardhood_submit_story]' ),
		'community-guidelines' => array( 'Community Guidelines', '', yh_guidelines_text() ),
		'story-permissions' => array( 'Story Permissions', '', yh_permissions_text() ),
	);
	foreach ( $pages as $slug => $d ) {
		yh_upsert_page( $slug, $d[0], $d[1], $d[2] );
	}
	$out[] = 'Pages created. Cover set as the front page. Search engines blocked while private.';

	// Menu.
	$menu_name = 'Yardhood Main';
	$menu      = wp_get_nav_menu_object( $menu_name );
	$menu_id   = $menu ? $menu->term_id : wp_create_nav_menu( $menu_name );
	if ( ! is_wp_error( $menu_id ) && ! wp_get_nav_menu_items( $menu_id ) ) {
		$add = function ( $title, $url ) use ( $menu_id ) {
			wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => $title, 'menu-item-url' => $url, 'menu-item-status' => 'publish', 'menu-item-type' => 'custom' ) );
		};
		$add( 'Cover', home_url( '/' ) );
		$add( 'Inside the Issue', home_url( '/inside-the-issue/' ) );
		$add( 'The Yard', home_url( '/the-yard/' ) );
		$add( 'The Hood', home_url( '/the-hood/' ) );
		$add( 'Stories', home_url( '/stories/' ) );
		$add( 'Yard Guide', home_url( '/yard-guide/' ) );
	}
	if ( ! is_wp_error( $menu_id ) ) {
		$loc = get_theme_mod( 'nav_menu_locations', array() );
		foreach ( array( 'header-menu', 'header-menu-logout', 'mobile-menu-logged-in', 'mobile-menu-logged-out', 'primary' ) as $l ) {
			$loc[ $l ] = $menu_id;
		}
		set_theme_mod( 'nav_menu_locations', $loc );
		$out[] = 'Main menu set: Cover, Inside the Issue, The Yard, The Hood, Stories, Yard Guide.';
	}

	foreach ( yh_story_types() as $k => $label ) {
		if ( ! term_exists( $label, 'yh_story_type' ) ) {
			wp_insert_term( $label, 'yh_story_type', array( 'slug' => $k ) );
		}
	}
	foreach ( yh_story_series() as $s ) {
		if ( ! term_exists( $s, 'yh_series' ) ) {
			wp_insert_term( $s, 'yh_series' );
		}
	}
	$out[] = 'Story types and series labels added.';
	flush_rewrite_rules();
	return $out;
}

function yh_setup_buddyboss() {
	$out = array();
	if ( ! function_exists( 'bp_get_option' ) ) {
		return array( 'Turn on BuddyBoss Platform first.' );
	}
	$active = bp_get_option( 'bp-active-components', array() );
	$had_groups = ! empty( $active['groups'] );
	foreach ( array( 'members', 'xprofile', 'settings', 'notifications', 'groups', 'activity', 'messages', 'media', 'friends' ) as $c ) {
		$active[ $c ] = 1;
	}
	bp_update_option( 'bp-active-components', $active );
	// Create component tables and directory pages the same way BuddyBoss's settings screen does.
	if ( function_exists( 'buddypress' ) ) {
		$schema = buddypress()->plugin_dir . 'bp-core/admin/bp-core-admin-schema.php';
		if ( file_exists( $schema ) ) {
			require_once $schema;
			if ( function_exists( 'bp_core_install' ) ) {
				bp_core_install( $active );
			}
		}
		if ( function_exists( 'bp_core_add_page_mappings' ) ) {
			bp_core_add_page_mappings( $active );
		}
	}
	if ( ! $had_groups || ! function_exists( 'groups_create_group' ) ) {
		return array( 'Components switched on (groups, activity, messages, media, friends). Run this step once more to create the spaces.' );
	}
	// Only admins create groups until host plans are live; members use "Request to host".
	bp_update_option( 'bp_restrict_group_creation', 1 );
	bp_update_option( 'hide-loggedout-adminbar', 1 );
	bp_update_option( 'bb_rl_enabled', 0 );
	update_option( 'bb_rl_enabled', 0 );

	$admin = get_current_user_id();
	foreach ( yh_official_spaces() as $s ) {
		$gid = groups_get_id( $s['slug'] );
		if ( ! $gid ) {
			$gid = groups_create_group( array(
				'creator_id'   => $admin,
				'name'         => $s['name'],
				'slug'         => $s['slug'],
				'description'  => $s['dek'],
				'status'       => 'public',
				'enable_forum' => 0,
			) );
			if ( $gid ) {
				groups_update_groupmeta( $gid, 'total_member_count', 1 );
				groups_update_groupmeta( $gid, 'last_activity', bp_core_current_time() );
				$out[] = 'Created space: ' . $s['name'];
			}
		}
		if ( $gid && function_exists( 'bp_groups_set_group_type' ) ) {
			bp_groups_set_group_type( $gid, 'official' );
		}
	}
	$out[] = 'Official spaces are public groups tagged "The Yard". Member group creation is limited to admins until hosting plans are switched on.';
	return $out;
}

function yh_find_event( $title ) {
	$q = get_posts( array( 'post_type' => 'yh_event', 'post_status' => 'any', 'title' => $title, 'posts_per_page' => 1, 'fields' => 'ids' ) );
	return $q ? $q[0] : 0;
}

function yh_setup_draft_events() {
	$out    = array();
	$lineup = array(
		array( 'Friday (1995)', '2026-12-11T20:00', 'Trilogy weekend kicks off.' ),
		array( 'Next Friday (2000)', '2026-12-12T20:00', 'Part two.' ),
		array( 'Friday After Next (2002)', '2026-12-13T20:00', 'Close out the trilogy.' ),
		array( 'This Christmas (2007)', '2026-12-15T20:00', 'Holiday kickoff.' ),
		array( 'The Best Man (1999)', '2026-12-19T20:00', 'Catch up before Monday.' ),
		array( 'The Best Man Holiday (2013)', '2026-12-21T20:00', 'First night of winter. Flagship night.' ),
		array( 'Home Alone (1990)', '2026-12-24T20:00', 'Christmas Eve.' ),
	);
	$group = function_exists( 'groups_get_id' ) ? (int) groups_get_id( 'movie-nights' ) : 0;
	foreach ( $lineup as $e ) {
		if ( yh_find_event( $e[0] ) ) {
			continue;
		}
		$id = wp_insert_post( array(
			'post_type'    => 'yh_event',
			'post_status'  => 'draft',
			'post_title'   => $e[0],
			'post_excerpt' => $e[2],
			'post_content' => '<p>Bring your own copy, press play with the room at 8 pm Eastern, and stay after the credits to discuss it together.</p>',
		) );
		update_post_meta( $id, '_yh_start', $e[1] );
		update_post_meta( $id, '_yh_duration', 180 );
		update_post_meta( $id, '_yh_lane', 'movie-night' );
		update_post_meta( $id, '_yh_note', $e[2] );
		update_post_meta( $id, '_yh_confirmation', 'draft' );
		update_post_meta( $id, '_yh_opens', 15 );
		update_post_meta( $id, '_yh_group', $group );
		update_post_meta( $id, '_yh_watch', 'Bring your own copy from the service you already use.' );
		$dt = new DateTime( $e[1], wp_timezone() );
		update_post_meta( $id, '_yh_start_ts', $dt->getTimestamp() );
		$out[] = 'Draft added: ' . $e[0];
	}
	if ( ! yh_find_event( 'The Best Man: The Final Chapters watchalong' ) ) {
		$id = wp_insert_post( array(
			'post_type'    => 'yh_event',
			'post_status'  => 'draft',
			'post_title'   => 'The Best Man: The Final Chapters watchalong',
			'post_excerpt' => 'Proposed weekly watchalong after The Best Man Holiday. Dates, host and streaming access not set.',
			'post_content' => '<p>Proposed: one episode a week for eight weeks, with first-timer reactions. Not scheduled.</p>',
		) );
		update_post_meta( $id, '_yh_lane', 'watchalong' );
		update_post_meta( $id, '_yh_confirmation', 'draft' );
		update_post_meta( $id, '_yh_group', $group );
		$out[] = 'Draft added: The Best Man: The Final Chapters watchalong (no date).';
	}
	$out[] = 'All lineup entries are unpublished drafts marked "Draft lineup". Publish and mark Confirmed when you approve each one.';
	return $out;
}

function yh_guidelines_text() {
	return "<p>Yardhood is an adult community (18+) for culture, conversation and people worth knowing. Keep it the kind of yard you want to come back to.</p>\n<h3>Bring good energy</h3><p>Disagree about the movie, not about each other's worth. No harassment, hate, threats or targeting anyone.</p>\n<h3>Respect the room</h3><p>Do not record, screenshot or share anyone from a room without their permission. Reaction rooms are opt-in only.</p>\n<h3>Share what is yours</h3><p>Post your own photos, art and stories. Do not stream or share copies of movies or shows. Everybody brings their own copy.</p>\n<h3>Keep it grown, keep it kind</h3><p>No sexual content, no spam, no selling in other people's spaces without asking the host.</p>\n<h3>Something wrong?</h3><p>Use the report button on any post or profile, or message a moderator. We review every report.</p>";
}

function yh_permissions_text() {
	return "<p>When you submit your story to Yardhood, you keep the copyright to your story and your work.</p>\n<p>By checking the boxes on the form you give Yardhood a non-exclusive license to edit and publish a feature from what you send on yardhood.com and in its back issues. Video and social media use only apply if you check those boxes. Any book or printed collection would need a separate agreement with you.</p>\n<p>Nothing is published until an editor reviews it with you. If you want something taken down later, email us and we will review the request.</p>";
}


function yh_setup_pages_v2() {
	$out   = array();
	$pages = array(
		'story-permissions'    => array( 'Story Permissions', yh_copy_story_permissions(), 'page-templates/policy.php' ),
		'terms-of-use'         => array( 'Terms of Use', yh_copy_terms(), 'page-templates/policy.php' ),
		'privacy-policy'       => array( 'Privacy Policy', yh_copy_privacy(), 'page-templates/policy.php' ),
		'community-guidelines' => array( 'Community Guidelines', yh_guidelines_text(), 'page-templates/policy.php' ),
		'hosting'              => array( 'Hosting on Yardhood', yh_copy_hosting(), 'page-templates/policy.php' ),
	);
	foreach ( $pages as $slug => $d ) {
		$p  = get_page_by_path( $slug );
		$id = $p ? $p->ID : 0;
		$arr = array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $d[0], 'post_content' => $d[1] );
		if ( $id ) {
			$arr['ID'] = $id;
			wp_update_post( $arr );
		} else {
			$id = wp_insert_post( $arr );
		}
		update_post_meta( $id, '_wp_page_template', $d[2] );
		$out[] = 'Updated page: ' . $d[0];
		if ( 'privacy-policy' === $slug ) {
			update_option( 'wp_page_for_privacy_policy', $id );
		}
	}
	update_option( 'yh_permissions_version', '2026-11-22' );

	// Footer menu.
	$name = 'Yardhood Footer';
	$menu = wp_get_nav_menu_object( $name );
	$mid  = $menu ? $menu->term_id : wp_create_nav_menu( $name );
	if ( ! is_wp_error( $mid ) ) {
		foreach ( (array) wp_get_nav_menu_items( $mid ) as $item ) {
			wp_delete_post( $item->ID, true );
		}
		foreach ( array(
			'Submit Your Story'    => '/submit-your-story/',
			'Hosting'              => '/hosting/',
			'Community Guidelines' => '/community-guidelines/',
			'Story Permissions'    => '/story-permissions/',
			'Terms of Use'         => '/terms-of-use/',
			'Privacy Policy'       => '/privacy-policy/',
		) as $t => $u ) {
			wp_update_nav_menu_item( $mid, 0, array( 'menu-item-title' => $t, 'menu-item-url' => home_url( $u ), 'menu-item-status' => 'publish', 'menu-item-type' => 'custom' ) );
		}
		$loc = get_theme_mod( 'nav_menu_locations', array() );
		foreach ( array( 'footer-menu', 'footer-menu-secondary', 'footer-secondary-menu', 'footer' ) as $l ) {
			$loc[ $l ] = $mid;
		}
		set_theme_mod( 'nav_menu_locations', $loc );
		$out[] = 'Footer links set.';
	}
	return $out;
}

function yh_publish_lineup() {
	$out    = array();
	$titles = array( 'Friday (1995)', 'Next Friday (2000)', 'Friday After Next (2002)', 'This Christmas (2007)', 'The Best Man (1999)', 'The Best Man Holiday (2013)', 'Home Alone (1990)' );
	foreach ( $titles as $t ) {
		$id = yh_find_event( $t );
		if ( ! $id ) {
			continue;
		}
		update_post_meta( $id, '_yh_confirmation', 'confirmed' );
		wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ) );
		$out[] = 'Published: ' . $t;
	}
	$out[] = 'Room links are still empty. Add the Zoom link to each night before it starts.';
	return $out;
}


function yh_setup_profile_fields() {
	if ( ! function_exists( 'xprofile_insert_field' ) ) {
		return array( 'Profiles component is not active.' );
	}
	global $wpdb;
	$out   = array();
	$group = (int) get_option( 'yh_xprofile_about_group', 0 );
	if ( ! $group || ! xprofile_get_field_group( $group ) ) {
		$group = (int) xprofile_insert_field_group( array( 'name' => 'About', 'description' => 'Optional. Fill in after you join.', 'can_delete' => true ) );
		update_option( 'yh_xprofile_about_group', $group );
	}
	$fields = array(
		array( 'City', 'textbox', 'Where you are now.' ),
		array( 'What I do', 'textbox', 'Your work, craft or calling, in your words.' ),
		array( 'About me', 'textarea', 'A few lines so people know who they are talking to.' ),
		array( 'The Top Three', 'textbox', 'The three most important things in your life, your way. Example: Family · Art · Peace' ),
		array( 'Links', 'url', 'Your site, shop or portfolio.' ),
	);
	foreach ( $fields as $i => $f ) {
		$existing = xprofile_get_field_id_from_name( $f[0] );
		if ( $existing ) {
			// Keep these off the signup form: they live in the About group.
			$wpdb->update( buddypress()->profile->table_name_fields, array( 'group_id' => $group ), array( 'id' => $existing ) );
			$wpdb->update( buddypress()->profile->table_name_fields, array( 'group_id' => $group ), array( 'parent_id' => $existing ) );
			$out[] = 'Moved to About: ' . $f[0];
			continue;
		}
		$id = xprofile_insert_field( array(
			'field_group_id' => $group,
			'name'           => $f[0],
			'type'           => $f[1],
			'description'    => $f[2],
			'is_required'    => false,
			'field_order'    => 10 + $i,
			'can_delete'     => true,
		) );
		if ( $id ) {
			bp_xprofile_update_field_meta( $id, 'default_visibility', 'public' );
			bp_xprofile_update_field_meta( $id, 'allow_custom_visibility', 'allowed' );
			bp_xprofile_update_field_meta( $id, 'signup_position', '' );
			$out[] = 'Added profile field: ' . $f[0];
		}
	}
	return $out ? $out : array( 'Profile fields already in place.' );
}
