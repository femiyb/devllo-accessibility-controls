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
        // Trigger button that opens the accessibility dialog.
        ?>
        <button
            type="button"
            class="da11y-trigger <?php echo esc_attr( $position_class ); ?>"
            aria-label="<?php echo esc_attr__( 'Accessibility Controls', 'devllo-accessibility-controls' ); ?>"
        >
            <?php echo esc_html__( 'Accessibility Options', 'devllo-accessibility-controls' ); ?>
        </button>

        <?php
        // Dialog backdrop and panel (initially hidden; JS will toggle visibility).
        ?>
        <div
            class="da11y-dialog-backdrop"
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
                            <?php echo esc_html__( 'Motion', 'devllo-accessibility-controls' ); ?>
                        </h3>
                        <div class="da11y-controls-row">
                            <button
                                type="button"
                                class="da11y-reduced-motion-toggle"
                                aria-pressed="false"
                            >
                                <?php echo esc_html__( 'Reduce motion', 'devllo-accessibility-controls' ); ?>
                            </button>
                        </div>
                    </section>
                <?php endif; ?>

                <?php
                // Section: High contrast mode.
                ?>
                <section class="da11y-section da11y-section-contrast">
                    <h3>
                        <?php echo esc_html__( 'High contrast', 'devllo-accessibility-controls' ); ?>
                    </h3>
                    <div class="da11y-controls-row">
                        <button
                            type="button"
                            class="da11y-contrast-toggle"
                            aria-pressed="false"
                        >
                            <?php echo esc_html__( 'Toggle high contrast', 'devllo-accessibility-controls' ); ?>
                        </button>
                    </div>
                </section>

                <?php
                // Global reset: clears all accessibility preferences.
                ?>
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
    }
}
