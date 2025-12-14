<?php
namespace DA11Y;

defined( 'ABSPATH' ) || exit;

final class Plugin {
    /**
     * The single instance of the plugin class.
     * 
     * @var Plugin|null
     */
    private static $instance = null;


    /**
     * get the singleton instance.
     * 
     * @return Plugin
     */
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    /**
     * 
     * Boot the plugin
     * 
     * @return void
     */
    private function init() {
        $this->load_textdomain();
        $this->includes();
        $this->setup_components();
    }

    /**
     * Load the plugin textdomain for translations.
     * 
     * @return void
     */
    private function load_textdomain() {
        load_plugin_textdomain(
            'devllo-accessibility-controls',
            false,
            basename( DA11Y_PLUGIN_PATH ) . '/languages/'
        );
    }

    /**
     * Include required files.
     * 
     * @return void
     */
    private function includes() {
        require_once DA11Y_PLUGIN_PATH . 'includes/class-assets.php';
        require_once DA11Y_PLUGIN_PATH . 'includes/class-devllo-accessibility-controls.php';
        require_once DA11Y_PLUGIN_PATH . 'includes/class-settings.php';
    }

    /**
     * Setup plugin components.
     * 
     * @return void
     */
    private function setup_components() {
        new Assets();
        new Accessibility_Controls();
        new Settings();
    }

}