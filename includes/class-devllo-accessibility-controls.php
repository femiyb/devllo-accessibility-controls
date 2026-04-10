<?php

namespace DA11Y;

// Prevent direct access to this file.
defined( 'ABSPATH' ) || exit;

/**
 * Frontend accessibility controls.
 *
 * Responsible for outputting the trigger button and the accessibility dialog
 * on the frontend. JavaScript will handle opening/closing the dialog and
 * applying user-selected preferences.
 */
class Accessibility_Controls {

    /**
     * Constructor.
     *
     * Hooks the render method into the frontend footer so the controls
     * appear on every public page (when the plugin is enabled).
     *
     * @return void
     */
    public function __construct() {
        add_action( 'wp_footer', [ $this, 'render' ] );
    }

    /**
     * Output the accessibility controls markup.
     *
     * Renders:
     * - A trigger button that opens the accessibility dialog.
     * - A dialog container with title, description, and basic controls.
     *
     * @return void
     */
    public function render() {
        $settings         = Settings::get();

        // Do not output the UI if the widget is disabled.
        if ( empty( $settings['enabled'] ) ) {
            return;
        }

        $dyslexia_enabled        = ! empty( $settings['dyslexia_enabled'] );
        $reduced_motion_enabled  = ! empty( $settings['reduced_motion_enabled'] );

        // Map stored position to a CSS class.
        $position        = isset( $settings['button_position'] ) ? $settings['button_position'] : 'bottom_right';
        $position_map    = [
            'bottom_right' => 'da11y-position-bottom-right',
            'bottom_left'  => 'da11y-position-bottom-left',
            'top_right'    => 'da11y-position-top-right',
            'top_left'     => 'da11y-position-top-left',
        ];
        $position_class  = isset( $position_map[ $position ] ) ? $position_map[ $position ] : $position_map['bottom_right'];

        ?>
        <?php
        /**
         * Fires before the accessibility trigger button is rendered.
         *
         * @param array $settings Plugin settings.
         */
        do_action( 'da11y_before_trigger', $settings );

        // Trigger button that opens the accessibility dialog.
        ?>
        <button
            type="button"
            class="da11y-trigger <?php echo esc_attr( $position_class ); ?>"
            aria-expanded="false"
            aria-label="<?php echo esc_attr__( 'Accessibility Controls', 'devllo-accessibility-controls' ); ?>"
        >
            <?php echo esc_html__( 'Accessibility Options', 'devllo-accessibility-controls' ); ?>
        </button>

        <?php
        /**
         * Fires after the accessibility trigger button is rendered.
         *
         * @param array $settings Plugin settings.
         */
        do_action( 'da11y_after_trigger', $settings );

        // Dialog backdrop and panel (initially hidden; JS will toggle visibility).
        ?>
        <?php
        /**
         * Fires before the accessibility dialog markup is rendered.
         *
         * @param array $settings Plugin settings.
         */
        do_action( 'da11y_before_dialog', $settings );
        ?>
        <div
            class="da11y-dialog-backdrop <?php echo esc_attr( $position_class ); ?>"
            hidden
        >
            <div
                class="da11y-dialog"
                role="dialog"
                aria-modal="true"
                aria-labelledby="da11y-dialog-title"
                aria-describedby="da11y-dialog-description"
            >
                <?php
                // Dialog title and description provide context for screen readers.
                ?>
                <button
                    type="button"
                    class="da11y-dialog-close"
                    aria-label="<?php echo esc_attr__( 'Close accessibility settings', 'devllo-accessibility-controls' ); ?>"
                >
                    &times;
                </button>
                <h2 id="da11y-dialog-title">
                    <?php echo esc_html__( 'Accessibility Settings', 'devllo-accessibility-controls' ); ?>
                </h2>
                <p id="da11y-dialog-description">
                    <?php echo esc_html__( 'Adjust text size and contrast to improve readability.', 'devllo-accessibility-controls' ); ?>
                </p>

                <?php
                // Section: Text size controls.
                ?>
                <section class="da11y-section da11y-section-text-size">
                    <h3>
                        <?php echo esc_html__( 'Text size', 'devllo-accessibility-controls' ); ?>
                    </h3>
                    <div class="da11y-controls-row">
                        <button
                            type="button"
                            class="da11y-text-smaller"
                        >
                            <?php echo esc_html__( 'Smaller', 'devllo-accessibility-controls' ); ?>
                        </button>
                        <button
                            type="button"
                            class="da11y-text-larger"
                        >
                            <?php echo esc_html__( 'Larger', 'devllo-accessibility-controls' ); ?>
                        </button>
                        <button
                            type="button"
                            class="da11y-text-reset"
                        >
                            <?php echo esc_html__( 'Reset', 'devllo-accessibility-controls' ); ?>
                        </button>
                    </div>
                </section>

                <?php
                // Section: Spacing controls (line height and paragraph spacing).
                ?>
                <section class="da11y-section da11y-section-spacing">
                    <h3>
                        <?php echo esc_html__( 'Line Spacing', 'devllo-accessibility-controls' ); ?>
                    </h3>
                    <div class="da11y-controls-row">
                        <button
                            type="button"
                            class="da11y-spacing-more"
                        >
                            <?php echo esc_html__( 'Increase spacing', 'devllo-accessibility-controls' ); ?>
                        </button>
                        <button
                            type="button"
                            class="da11y-spacing-reset"
                        >
                            <?php echo esc_html__( 'Reset spacing', 'devllo-accessibility-controls' ); ?>
                        </button>
                    </div>
                </section>

                <section class="da11y-section da11y-section-letter-spacing">
                    <h3>
                        <?php echo esc_html__( 'Letter spacing', 'devllo-accessibility-controls' ); ?>
                    </h3>
                    <div class="da11y-controls-row">
                        <button
                            type="button"
                            class="da11y-letter-spacing-more"
                        >
                            <?php echo esc_html__( 'Increase', 'devllo-accessibility-controls' ); ?>
                        </button>
                        <button
                            type="button"
                            class="da11y-letter-spacing-reset"
                        >
                            <?php echo esc_html__( 'Reset', 'devllo-accessibility-controls' ); ?>
                        </button>
                    </div>
                </section>

                <section class="da11y-section da11y-section-word-spacing">
                    <h3>
                        <?php echo esc_html__( 'Word spacing', 'devllo-accessibility-controls' ); ?>
                    </h3>
                    <div class="da11y-controls-row">
                        <button
                            type="button"
                            class="da11y-word-spacing-more"
                        >
                            <?php echo esc_html__( 'Increase', 'devllo-accessibility-controls' ); ?>
                        </button>
                        <button
                            type="button"
                            class="da11y-word-spacing-reset"
                        >
                            <?php echo esc_html__( 'Reset', 'devllo-accessibility-controls' ); ?>
                        </button>
                    </div>
                </section>

                <section class="da11y-section da11y-section-align-left">
                    <h3>
                        <?php echo esc_html__( 'Text alignment', 'devllo-accessibility-controls' ); ?>
                    </h3>
                    <div class="da11y-controls-row">
                        <button
                            type="button"
                            class="da11y-align-left-toggle"
                            aria-pressed="false"
                        >
                            <?php echo esc_html__( 'Align text left', 'devllo-accessibility-controls' ); ?>
                        </button>
                    </div>
                </section>

                <?php
                // Section: Dyslexia-friendly reading mode (if enabled).
                if ( $dyslexia_enabled ) :
                    ?>
                    <section class="da11y-section da11y-section-dyslexia">
                        <h3>
                            <?php echo esc_html__( 'Reading mode', 'devllo-accessibility-controls' ); ?>
                        </h3>
                        <div class="da11y-controls-row">
                            <button
                                type="button"
                                class="da11y-dyslexia-toggle"
                                aria-pressed="false"
                            >
                                <?php echo esc_html__( 'Dyslexia-friendly font', 'devllo-accessibility-controls' ); ?>
                            </button>
                        </div>
                    </section>
                <?php endif; ?>

                <?php
                // Section: Reduced motion (if enabled).
                if ( $reduced_motion_enabled ) :
                    ?>
                    <section class="da11y-section da11y-section-motion">
                        <h3>
                            <?php echo esc_html__( 'Reduce Motion', 'devllo-accessibility-controls' ); ?>
                        </h3>
                        <div class="da11y-controls-row">
                            <button
                                type="button"
                                class="da11y-reduced-motion-toggle"
                                aria-pressed="false"
                            >
                                <?php echo esc_html__( 'Reduce animations site-wide', 'devllo-accessibility-controls' ); ?>
                            </button>
                        </div>
                    </section>
                <?php endif; ?>

                <?php
                // Section: High contrast mode.
                ?>
                <section class="da11y-section da11y-section-grayscale">
                    <h3>
                        <?php echo esc_html__( 'Grayscale', 'devllo-accessibility-controls' ); ?>
                    </h3>
                    <div class="da11y-controls-row">
                        <button
                            type="button"
                            class="da11y-grayscale-toggle"
                            aria-pressed="false"
                        >
                            <?php echo esc_html__( 'Toggle grayscale', 'devllo-accessibility-controls' ); ?>
                        </button>
                    </div>
                </section>

                <?php
                // Section: Color themes.
                ?>
                <section class="da11y-section da11y-section-themes">
                    <h3>
                        <?php echo esc_html__( 'Color themes', 'devllo-accessibility-controls' ); ?>
                    </h3>
                    <div class="da11y-controls-row">
                        <button
                            type="button"
                            class="da11y-theme-button"
                            data-da11y-theme="default"
                            aria-pressed="true"
                        >
                            <?php echo esc_html__( 'Default', 'devllo-accessibility-controls' ); ?>
                        </button>
                        <button
                            type="button"
                            class="da11y-theme-button"
                            data-da11y-theme="dark-mode"
                            aria-pressed="false"
                        >
                            <?php echo esc_html__( 'Dark mode (beta)', 'devllo-accessibility-controls' ); ?>
                        </button>
                    </div>
                </section>

                <?php
                // Section: Reading mode.
                ?>
                <section class="da11y-section da11y-section-reading-mode">
                    <h3>
                        <?php echo esc_html__( 'Reading mode', 'devllo-accessibility-controls' ); ?>
                    </h3>
                    <div class="da11y-controls-row">
                        <button
                            type="button"
                            class="da11y-reading-mode-toggle"
                            aria-pressed="false"
                        >
                            <?php echo esc_html__( 'Toggle reading mode', 'devllo-accessibility-controls' ); ?>
                        </button>
                    </div>
                </section>

                <?php
                // Global reset: clears all accessibility preferences.
                ?>

                <section class="da11y-section da11y-section-reading-guide">
                    <h3>
                        <?php echo esc_html__( 'Reading guide', 'devllo-accessibility-controls' ); ?>
                    </h3>
                    <div class="da11y-controls-row">
                        <button
                            type="button"
                            class="da11y-reading-guide-toggle"
                            aria-pressed="false"
                        >
                            <?php echo esc_html__( 'Toggle reading guide', 'devllo-accessibility-controls' ); ?>
                        </button>
                    </div>
                </section>

                <section class="da11y-section da11y-section-reading-mask">
                    <h3>
                        <?php echo esc_html__( 'Reading mask', 'devllo-accessibility-controls' ); ?>
                    </h3>
                    <div class="da11y-controls-row">
                        <button
                            type="button"
                            class="da11y-reading-mask-toggle"
                            aria-pressed="false"
                        >
                            <?php echo esc_html__( 'Toggle reading mask', 'devllo-accessibility-controls' ); ?>
                        </button>
                    </div>
                </section>

                <section class="da11y-section da11y-section-big-cursor">
                    <h3>
                        <?php echo esc_html__( 'Cursor', 'devllo-accessibility-controls' ); ?>
                    </h3>
                    <div class="da11y-controls-row">
                        <button
                            type="button"
                            class="da11y-big-cursor-toggle"
                            aria-pressed="false"
                        >
                            <?php echo esc_html__( 'Large cursor', 'devllo-accessibility-controls' ); ?>
                        </button>
                    </div>
                </section>

                <section class="da11y-section da11y-section-hide-images">
                    <h3>
                        <?php echo esc_html__( 'Images', 'devllo-accessibility-controls' ); ?>
                    </h3>
                    <div class="da11y-controls-row">
                        <button
                            type="button"
                            class="da11y-hide-images-toggle"
                            aria-pressed="false"
                        >
                            <?php echo esc_html__( 'Hide images', 'devllo-accessibility-controls' ); ?>
                        </button>
                    </div>
                </section>

                <section class="da11y-section da11y-section-highlight-links">
                    <h3>
                        <?php echo esc_html__( 'Links', 'devllo-accessibility-controls' ); ?>
                    </h3>
                    <div class="da11y-controls-row">
                        <button
                            type="button"
                            class="da11y-highlight-links-toggle"
                            aria-pressed="false"
                        >
                            <?php echo esc_html__( 'Highlight links', 'devllo-accessibility-controls' ); ?>
                        </button>
                    </div>
                </section>

                <section class="da11y-section da11y-section-focus">
                    <h3>
                        <?php echo esc_html__( 'Focus', 'devllo-accessibility-controls' ); ?>
                    </h3>
                    <div class="da11y-controls-row">
                        <button
                            type="button"
                            class="da11y-focus-toggle"
                            aria-pressed="false"
                        >
                            <?php echo esc_html__( 'Enhance focus outlines', 'devllo-accessibility-controls' ); ?>
                        </button>
                    </div>
                </section>

                <section class="da11y-section da11y-section-brightness">
                    <h3>
                        <?php echo esc_html__( 'Brightness', 'devllo-accessibility-controls' ); ?>
                    </h3>
                    <div class="da11y-controls-row">
                        <button
                            type="button"
                            class="da11y-brightness-down"
                        >
                            <?php echo esc_html__( 'Dimmer', 'devllo-accessibility-controls' ); ?>
                        </button>
                        <button
                            type="button"
                            class="da11y-brightness-up"
                        >
                            <?php echo esc_html__( 'Brighter', 'devllo-accessibility-controls' ); ?>
                        </button>
                        <button
                            type="button"
                            class="da11y-brightness-reset"
                        >
                            <?php echo esc_html__( 'Reset', 'devllo-accessibility-controls' ); ?>
                        </button>
                    </div>
                </section>

                <div class="da11y-section da11y-section-reset">
                    <button
                        type="button"
                        class="da11y-reset-all"
                    >
                        <?php echo esc_html__( 'Reset all settings', 'devllo-accessibility-controls' ); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
        /**
         * Fires after the accessibility dialog markup is rendered.
         *
         * @param array $settings Plugin settings.
         */
        do_action( 'da11y_after_dialog', $settings );
        ?>
        <div class="da11y-reading-guide-line" aria-hidden="true"></div>
        <div class="da11y-reading-mask-top" aria-hidden="true"></div>
        <div class="da11y-reading-mask-bottom" aria-hidden="true"></div>
        <?php
    }
}
