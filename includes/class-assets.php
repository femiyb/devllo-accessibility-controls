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

        // Load OpenDyslexic font for dyslexia-friendly mode.
        wp_enqueue_style(
                'da11y-opendyslexic',
                'https://cdn.jsdelivr.net/npm/@fontsource/opendyslexic@latest/400.css',
                [],
                null
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
                'textSize'      => 0,
                'contrast'      => false,
                'dyslexia'      => false,
                'reducedMotion' => false,
                'spacing'       => 0,
                'theme'         => 'default',
                'readingMode'   => false,
                'grayscale'     => false,
                'letterSpacing' => 0,
                'readingGuide'  => false,
                'bigCursor'     => false,
                'highlightLinks' => false,
                'focusEnhanced' => false,
                'brightness'    => 0,
                'wordSpacing'   => 0,
                'alignLeft'     => false,
                'readingMask'     => false,
                'hideImages'      => false,

            ],
            'settings' => [
                'enabled'        => ! empty( $settings['enabled'] ),
                'buttonPosition' => isset( $settings['button_position'] ) ? $settings['button_position'] : 'bottom_right',
            ],
            'features' => [
                'contrast'         => ! empty( $settings['feature_contrast'] ),
                'darkMode'         => ! empty( $settings['feature_dark_mode'] ),
                'grayscale'        => ! empty( $settings['feature_grayscale'] ),
                'brightness'       => ! empty( $settings['feature_brightness'] ),
                'textSize'         => ! empty( $settings['feature_text_size'] ),
                'letterSpacing'    => ! empty( $settings['feature_letter_spacing'] ),
                'lineSpacing'      => ! empty( $settings['feature_line_spacing'] ),
                'wordSpacing'      => ! empty( $settings['feature_word_spacing'] ),
                'dyslexia'         => ! empty( $settings['feature_dyslexia'] ),
                'alignLeft'        => ! empty( $settings['feature_align_left'] ),
                'readingMode'      => ! empty( $settings['feature_reading_mode'] ),
                'readingGuide'     => ! empty( $settings['feature_reading_guide'] ),
                'readingMask'      => ! empty( $settings['feature_reading_mask'] ),
                'bigCursor'        => ! empty( $settings['feature_big_cursor'] ),
                'highlightLinks'   => ! empty( $settings['feature_highlight_links'] ),
                'focusEnhanced'    => ! empty( $settings['feature_focus_enhanced'] ),
                'reducedMotion'    => ! empty( $settings['feature_reduced_motion'] ),
                'hideImages'       => ! empty( $settings['feature_hide_images'] ),
                'keyboardShortcut' => ! empty( $settings['feature_keyboard_shortcut'] ),
            ],
                    ];

        /**
         * Filter the frontend configuration passed to JavaScript.
         *
         * @param array $config   Configuration array.
         * @param array $settings Plugin settings.
         */
        $config = apply_filters( 'da11y_frontend_config', $config, $settings );

        wp_localize_script( 'da11y-frontend', 'da11yConfig', $config );
    }
}
