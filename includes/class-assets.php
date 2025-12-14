<?php

namespace DA11Y;

defined( 'ABSPATH' ) || exit;

final class Assets {

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ] );
    }

    /**
     * Enqueue frontend assets.
     *
     * @return void
     */
    public function enqueue_frontend_assets() {
        if ( is_admin() ) {
            return;
        }

        // Respect plugin settings; do not enqueue if disabled.
        $settings = Settings::get();
        if ( empty( $settings['enabled'] ) ) {
            return;
        }

        // CSS.
        wp_enqueue_style(
            'da11y-frontend',
            DA11Y_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            DA11Y_PLUGIN_VERSION
        );

        // JS.
        wp_enqueue_script(
            'da11y-frontend',
            DA11Y_PLUGIN_URL . 'assets/js/frontend.js',
            [],
            DA11Y_PLUGIN_VERSION,
            true
        );

        // Localized config for JS.
        $config = [
            'features' => [
                'textSize' => true,
                'contrast' => true,
            ],
            'defaults' => [
                'textSize' => 0,
                'contrast' => false,
            ],
            'settings' => [
                'enabled'        => ! empty( $settings['enabled'] ),
                'buttonPosition' => isset( $settings['button_position'] ) ? $settings['button_position'] : 'bottom_right',
            ],
        ];

        wp_localize_script( 'da11y-frontend', 'da11yConfig', $config );
    }
}
