<?php
/**
 * Plugin Name: Devllo Accessibility Controls
 * Plugin URI: https://devllo.com/product/devllo-accessibility-controls/
 * Description: Visitor-controlled accessibility enhancements for WordPress websites.
 * Version: 0.7.2
 * Author: Devllo Plugins
 * Author URI: https://devllo.com/
 * Text Domain: devllo-accessibility-controls
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.9
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */


// safety check and constant definitions
defined( 'ABSPATH' ) || exit;

define( 'DA11Y_PLUGIN_VERSION', '0.7.2' );
define( 'DA11Y_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'DA11Y_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// require the main plugin class and initialize the plugin
require_once DA11Y_PLUGIN_PATH . 'includes/class-plugin.php';

function da11y_init_plugin() {
    return DA11Y\Plugin::instance();
}

// hook into plugins_loaded action
add_action( 'plugins_loaded', 'da11y_init_plugin' );
