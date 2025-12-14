<?php
/**
 * Plugin Name: Devllo Accessibility Controls
 * Plugin URI: https://example.com
 * Description: Visitor-controlled accessibility enhancements for WordPress websites.
 * Version: 0.1.0
 * Author: Your Name
 * Author URI: https://example.com
 * Text Domain: devllo-accessibility-controls
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.6
 * Requires PHP: 7.4
 */


// safety check and constant definitions
defined( 'ABSPATH' ) || exit;

define( 'DA11Y_PLUGIN_VERSION', '0.1.0' );
define( 'DA11Y_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'DA11Y_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// require the main plugin class and initialize the plugin
require_once DA11Y_PLUGIN_PATH . 'includes/class-plugin.php';

function da11y_init_plugin() {
    return DA11Y\Plugin::instance();
}

// hook into plugins_loaded action
add_action( 'plugins_loaded', 'da11y_init_plugin' );


