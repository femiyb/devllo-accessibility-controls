<?php

namespace DA11Y;

defined( 'ABSPATH' ) || exit;

/**
 * Block editor integration for accessibility hints.
 */
class Editor {

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct() {
        add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ] );
    }

    /**
     * Enqueue block editor assets for the accessibility hints panel.
     *
     * @return void
     */
    public function enqueue_block_editor_assets() {
        wp_enqueue_script(
            'da11y-editor',
            DA11Y_PLUGIN_URL . 'assets/js/editor.js',
            [ 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-i18n' ],
            DA11Y_PLUGIN_VERSION,
            true
        );

        wp_enqueue_style(
            'da11y-editor',
            DA11Y_PLUGIN_URL . 'assets/css/editor.css',
            [],
            DA11Y_PLUGIN_VERSION
        );
    }
}
