<?php
/**
 * Sidebar widgets that make the member area feel lived in.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class YH_Widget_Base extends WP_Widget {
	protected function open( $args, $title ) {
		echo $args['before_widget']; // phpcs:ignore
		echo '<div class="yh-w">';
		if ( $title ) {
			echo '<h3 class="yh-w-title">' . esc_html( $title ) . '</h3>';
		}
	}
	protected function close( $args ) {
		echo '</div>';
		echo $args['after_widget']; // phpcs:ignore
	}
	public function form( $instance ) {
		echo '<p>No settings. This widget fills itself from Yardhood content.</p>';
		return 'noform';
	}
}

class YH_Widget_Coming_Up extends YH_Widget_Base {
	public function __construct() {
		parent::__construct( 'yh_coming_up', 'Yardhood: Coming up', array( 'description' => 'Next events from the Yard Guide.' ) );
	}
	public function widget( $args, $instance ) {
		$events = yh_upcoming_events( 4 );
		if ( function_exists( 'bp_is_group' ) && bp_is_group() ) {
			$gid = bp_get_current_group_id();
			$events = array_values( array_filter( yh_upcoming_events( 12 ), function ( $e ) use ( $gid ) {
				return (int) get_post_meta( $e->ID, '_yh_group', true ) === (int) $gid;
			} ) );
			$events = array_slice( $events, 0, 4 );
		}
		$this->open( $args, 'Coming up' );
		if ( ! $events && function_exists( 'bp_is_group' ) && bp_is_group() ) {
			echo '<p class="yh-w-empty">No gatherings scheduled for this room yet. Elsewhere in the Yard:</p>';
			$events = yh_upcoming_events( 3 );
		}
		if ( ! $events ) {
			echo '<p class="yh-w-empty">New gatherings are being added. Check back soon.</p>';
		} else {
			echo '<ul class="yh-w-events">';
			foreach ( $events as $e ) {
				$s = yh_event_start( $e->ID );
				echo '<li><a href="' . esc_url( get_permalink( $e ) ) . '"><span class="yh-w-date"><b>' . esc_html( $s ? $s->format( 'M' ) : '' ) . '</b>' . esc_html( $s ? $s->format( 'j' ) : 'TBA' ) . '</span><span class="yh-w-ev"><span class="yh-w-ev-t">' . esc_html( get_the_title( $e ) ) . '</span><span class="yh-w-ev-m">' . esc_html( ( $s ? $s->format( 'D g:i a' ) . ' ET · ' : '' ) . yh_lane_label( get_post_meta( $e->ID, '_yh_lane', true ) ) ) . '</span></span></a></li>';
			}
			echo '</ul>';
		}
		echo '<a class="yh-w-more" href="' . esc_url( get_post_type_archive_link( 'yh_event' ) ) . '">Full Yard Guide</a>';
		$this->close( $args );
	}
}

class YH_Widget_Spaces extends YH_Widget_Base {
	public function __construct() {
		parent::__construct( 'yh_spaces', 'Yardhood: The Yard spaces', array( 'description' => 'Official spaces with photos.' ) );
	}
	public function widget( $args, $instance ) {
		$this->open( $args, 'The Yard' );
		echo '<ul class="yh-w-spaces">';
		foreach ( yh_official_spaces() as $s ) {
			echo '<li><a href="' . esc_url( yh_space_url( $s['slug'] ) ) . '"><img src="' . esc_url( yh_img( $s['img'] ) ) . '" alt="" loading="lazy"><span><span class="yh-w-ev-t">' . esc_html( $s['name'] ) . '</span><span class="yh-w-ev-m">' . esc_html( $s['dek'] ) . '</span></span></a></li>';
		}
		echo '</ul>';
		echo '<a class="yh-w-more" href="' . esc_url( yh_page_url( 'the-hood' ) ) . '">Find member groups in The Hood</a>';
		$this->close( $args );
	}
}

class YH_Widget_Start_Here extends YH_Widget_Base {
	public function __construct() {
		parent::__construct( 'yh_start_here', 'Yardhood: Start here', array( 'description' => 'Welcome steps for members.' ) );
	}
	public function widget( $args, $instance ) {
		if ( ! is_user_logged_in() ) {
			return;
		}
		$uid    = get_current_user_id();
		$avatar = function_exists( 'bp_get_user_has_avatar' ) && bp_get_user_has_avatar( $uid );
		$groups = function_exists( 'groups_total_groups_for_user' ) ? (int) groups_total_groups_for_user( $uid ) : 0;
		$rsvp   = (bool) get_posts( array( 'post_type' => 'yh_event', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_query' => array( array( 'key' => '_yh_rsvps', 'value' => 'i:' . $uid . ';', 'compare' => 'LIKE' ) ) ) );
		$profile = function_exists( 'bp_members_get_user_url' ) ? bp_members_get_user_url( $uid ) : ( function_exists( 'bp_core_get_user_domain' ) ? bp_core_get_user_domain( $uid ) : home_url( '/' ) );
		$steps  = array(
			array( $avatar, 'Add a photo and your Top Three', trailingslashit( $profile ) . 'profile/edit/' ),
			array( $groups > 0, 'Join a space in The Yard', yh_page_url( 'the-yard' ) ),
			array( $rsvp, 'RSVP to a movie night', get_post_type_archive_link( 'yh_event' ) ),
			array( false, 'Share your story', yh_page_url( 'submit-your-story' ) ),
		);
		$done = count( array_filter( wp_list_pluck( $steps, 0 ) ) );
		$this->open( $args, 'Start here' );
		echo '<div class="yh-w-progress" style="--p:' . (int) round( $done / 4 * 100 ) . '%"><span></span></div><p class="yh-w-sub">' . (int) $done . ' of 4 done</p><ol class="yh-w-steps">';
		foreach ( $steps as $s ) {
			echo '<li class="' . ( $s[0] ? 'is-done' : '' ) . '"><a href="' . esc_url( $s[2] ) . '">' . esc_html( $s[1] ) . '</a></li>';
		}
		echo '</ol>';
		$this->close( $args );
	}
}

class YH_Widget_Host extends YH_Widget_Base {
	public function __construct() {
		parent::__construct( 'yh_host', 'Yardhood: Host your people', array( 'description' => 'Community Host offer.' ) );
	}
	public function widget( $args, $instance ) {
		$this->open( $args, '' );
		echo '<div class="yh-w-host"><img src="' . esc_url( yh_img( 'dinner-table' ) ) . '" alt="" loading="lazy"><div class="yh-w-host-t"><span class="yh-kicker">Host your people</span><strong>Family groups, game nights, reunions, book clubs.</strong><span>First month free, then $10 a month.</span><a class="yh-btn yh-btn-gold" href="' . esc_url( yh_page_url( 'the-hood' ) . '#host' ) . '">Start a group</a></div></div>';
		$this->close( $args );
	}
}

add_action( 'widgets_init', function () {
	register_widget( 'YH_Widget_Coming_Up' );
	register_widget( 'YH_Widget_Spaces' );
	register_widget( 'YH_Widget_Start_Here' );
	register_widget( 'YH_Widget_Host' );
} );

/** Put the widgets in BuddyBoss sidebars (keeps anything else already there). */
function yh_place_widgets() {
	$map = array(
		'activity_left'  => array( 'yh_start_here', 'yh_spaces' ),
		'activity_right' => array( 'yh_coming_up', 'yh_host' ),
		'groups'         => array( 'yh_coming_up', 'yh_host' ),
		'group'          => array( 'yh_coming_up', 'yh_host' ),
		'members'        => array( 'yh_start_here', 'yh_coming_up' ),
		'profile'        => array( 'yh_spaces' ),
		'user_activity'  => array( 'yh_coming_up' ),
	);
	$sidebars = get_option( 'sidebars_widgets', array() );
	foreach ( $map as $sidebar => $bases ) {
		$current = isset( $sidebars[ $sidebar ] ) ? (array) $sidebars[ $sidebar ] : array();
		foreach ( $bases as $base ) {
			$already = array_filter( $current, function ( $w ) use ( $base ) {
				return 0 === strpos( $w, $base . '-' );
			} );
			if ( $already ) {
				continue;
			}
			$inst = get_option( 'widget_' . $base, array() );
			if ( ! is_array( $inst ) ) {
				$inst = array();
			}
			$num = 1;
			while ( isset( $inst[ $num ] ) ) {
				$num++;
			}
			$inst[ $num ]          = array();
			$inst['_multiwidget'] = 1;
			update_option( 'widget_' . $base, $inst );
			$current[] = $base . '-' . $num;
		}
		$sidebars[ $sidebar ] = $current;
	}
	update_option( 'sidebars_widgets', $sidebars );
}
