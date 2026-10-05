<?php
/**
 * Plugin Name:       FlowDesk Pro
 * Description:       A modern WordPress lead and client management plugin.
 * Version:           1.0.0
 * Author:            Saif
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       flowdesk-pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Plugin constants.
 */
define( 'FDP_VERSION', '1.0.0' );

define( 'FDP_FILE', __FILE__ );

define( 'FDP_PATH', plugin_dir_path( __FILE__ ) );

define( 'FDP_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load admin functionality.
 */
require_once FDP_PATH . 'admin/admin-menu.php';
/**
 * Load lead system.
 */
require_once FDP_PATH . 'includes/lead-post-type.php';

/**
 * Load Lead Details system.
 */
require_once FDP_PATH . 'includes/lead-meta-boxes.php';

/**
 * Load Lead admin columns.
 */
require_once FDP_PATH . 'includes/lead-columns.php';

/**
 * Load Internal Notes system.
 */
require_once FDP_PATH . 'includes/lead-notes.php';

/**
 * Load AJAX functionality.
 */
require_once FDP_PATH . 'includes/ajax.php';

/**
 * Load frontend Lead Form.
 */
require_once FDP_PATH . 'public/form.php';

require_once FDP_PATH . 'admin/settings.php';

require_once FDP_PATH . 'includes/export.php';

require_once FDP_PATH . 'includes/activity.php';

/**
 * Runs when the plugin is activated.
 */
function fdp_activate() {

	update_option( 'flowdesk_pro_version', FDP_VERSION );

}

register_activation_hook( __FILE__, 'fdp_activate' );


/**
 * Runs when the plugin is deactivated.
 */
function fdp_deactivate() {

	// Deactivation tasks will be added later.

}

register_deactivation_hook( __FILE__, 'fdp_deactivate' );