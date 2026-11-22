<?php
/**
 * Plugin Name: Yardhood Core
 * Description: Yard Guide events with RSVP, Stories, Submit Your Story, pre-launch lock and site setup for Yardhood.
 * Version: 1.0.0
 * Author: Yardhood
 * Text Domain: yardhood
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'YH_VERSION', '1.3.2' );
define( 'YH_DIR', plugin_dir_path( __FILE__ ) );
define( 'YH_URL', plugin_dir_url( __FILE__ ) );

require_once YH_DIR . 'inc/helpers.php';
require_once YH_DIR . 'inc/prelaunch.php';
require_once YH_DIR . 'inc/events.php';
require_once YH_DIR . 'inc/stories.php';
require_once YH_DIR . 'inc/submit.php';
require_once YH_DIR . 'inc/community.php';
require_once YH_DIR . 'inc/billing.php';
require_once YH_DIR . 'inc/widgets.php';
require_once YH_DIR . 'inc/brand.php';
require_once YH_DIR . 'inc/content.php';
require_once YH_DIR . 'inc/setup.php';

register_activation_hook( __FILE__, function () {
	yh_register_events_cpt();
	yh_register_stories_cpt();
	if ( ! get_option( 'yh_prelaunch_key' ) ) {
		update_option( 'yh_prelaunch_key', wp_generate_password( 16, false ) );
	}
	if ( false === get_option( 'yh_prelaunch' ) ) {
		update_option( 'yh_prelaunch', 1 );
	}
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
