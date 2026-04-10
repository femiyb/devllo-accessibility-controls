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
        $settings = Settings::get();

        // Do not output the UI if the widget is disabled.
        if ( empty( $settings['enabled'] ) ) {
            return;
        }

        // Feature flags.
        $f_contrast          = ! empty( $settings['feature_contrast'] );
        $f_dark_mode         = ! empty( $settings['feature_dark_mode'] );
        $f_grayscale         = ! empty( $settings['feature_grayscale'] );
        $f_brightness        = ! empty( $settings['feature_brightness'] );
        $f_text_size         = ! empty( $settings['feature_text_size'] );
        $f_letter_spacing    = ! empty( $settings['feature_letter_spacing'] );
        $f_line_spacing      = ! empty( $settings['feature_line_spacing'] );
        $f_word_spacing      = ! empty( $settings['feature_word_spacing'] );
        $f_dyslexia          = ! empty( $settings['feature_dyslexia'] );
        $f_align_left        = ! empty( $settings['feature_align_left'] );
        $f_reading_mode      = ! empty( $settings['feature_reading_mode'] );
        $f_reading_guide     = ! empty( $settings['feature_reading_guide'] );
        $f_reading_mask      = ! empty( $settings['feature_reading_mask'] );
        $f_big_cursor        = ! empty( $settings['feature_big_cursor'] );
        $f_highlight_links   = ! empty( $settings['feature_highlight_links'] );
        $f_focus_enhanced    = ! empty( $settings['feature_focus_enhanced'] );
        $f_reduced_motion    = ! empty( $settings['feature_reduced_motion'] );
        $f_hide_images       = ! empty( $settings['feature_hide_images'] );

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
        $button_label      = ! empty( $settings['button_label'] ) ? $settings['button_label'] : __( 'Accessibility Options', 'devllo-accessibility-controls' );
        $button_bg_color   = ! empty( $settings['button_bg_color'] ) ? $settings['button_bg_color'] : '#111111';
        $button_text_color = ! empty( $settings['button_text_color'] ) ? $settings['button_text_color'] : '#ffffff';
        $button_size       = ! empty( $settings['button_size'] ) ? $settings['button_size'] : 'medium';
        $button_icon       = ! empty( $settings['button_icon'] ) ? $settings['button_icon'] : 'text_only';

        $size_map = [
            'small'  => '0.75rem 0.75rem',
            'medium' => '0.75rem 1rem',
            'large'  => '1rem 1.5rem',
        ];
        $padding = isset( $size_map[ $button_size ] ) ? $size_map[ $button_size ] : $size_map['medium'];

        $inline_style = sprintf(
            'background-color:%s;color:%s;padding:%s;',
            esc_attr( $button_bg_color ),
            esc_attr( $button_text_color ),
            esc_attr( $padding )
        );
        ?>
        <button
            type="button"
            class="da11y-trigger <?php echo esc_attr( $position_class ); ?>"
            aria-expanded="false"
            aria-label="<?php echo esc_attr__( 'Accessibility Controls', 'devllo-accessibility-controls' ); ?>"
            style="<?php echo esc_attr( $inline_style ); ?>"
        >
            <?php if ( $button_icon === 'icon_only' || $button_icon === 'icon_and_text' ) : ?>
                <span class="da11y-trigger-icon dashicons dashicons-universal-access-alt" aria-hidden="true"></span>
            <?php endif; ?>
            <?php if ( $button_icon === 'text_only' || $button_icon === 'icon_and_text' ) : ?>
                <span class="da11y-trigger-label"><?php echo esc_html( $button_label ); ?></span>
            <?php endif; ?>
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

                <div class="da11y-accordion">
                    <section class="da11y-accordion-group">
                        <button
                            type="button"
                            class="da11y-accordion-toggle"
                            aria-expanded="true"
                            aria-controls="da11y-group-text"
                        >
                            <span class="da11y-accordion-title">
                                <?php echo esc_html__( 'Text', 'devllo-accessibility-controls' ); ?>
                            </span>
                            <span class="da11y-accordion-indicator" aria-hidden="true"></span>
                        </button>
                        <div
                            id="da11y-group-text"
                            class="da11y-accordion-panel"
                        >
                            <?php
                            // Section: Text size controls.
                            if ( $f_text_size ) :
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
                            <?php endif; ?>

                            <?php
                            // Section: Spacing controls (line height and paragraph spacing).
                            if ( $f_line_spacing ) :
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
                            <?php endif; ?>

                            <?php if ( $f_letter_spacing ) : ?>
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
                            <?php endif; ?>

                            <?php if ( $f_word_spacing ) : ?>
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
                            <?php endif; ?>

                            <?php if ( $f_align_left ) : ?>
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
                            <?php endif; ?>

                            <?php
                            // Section: Dyslexia-friendly reading mode (if enabled).
                            if ( $f_dyslexia ) :
                            ?>
                                <section class="da11y-section da11y-section-dyslexia">
                                    <h3>
                                        <?php echo esc_html__( 'Dyslexia-friendly font', 'devllo-accessibility-controls' ); ?>
                                    </h3>
                                    <div class="da11y-controls-row">
                                        <button
                                            type="button"
                                            class="da11y-dyslexia-toggle"
                                            aria-pressed="false"
                                        >
                                            <?php echo esc_html__( 'Toggle dyslexia-friendly font', 'devllo-accessibility-controls' ); ?>
                                        </button>
                                    </div>
                                </section>
                            <?php endif; ?>
                        </div>
                    </section>

                    <section class="da11y-accordion-group">
                        <button
                            type="button"
                            class="da11y-accordion-toggle"
                            aria-expanded="false"
                            aria-controls="da11y-group-reading"
                        >
                            <span class="da11y-accordion-title">
                                <?php echo esc_html__( 'Reading', 'devllo-accessibility-controls' ); ?>
                            </span>
                            <span class="da11y-accordion-indicator" aria-hidden="true"></span>
                        </button>
                        <div
                            id="da11y-group-reading"
                            class="da11y-accordion-panel"
                            hidden
                        >
                            <?php if ( $f_reading_mode ) : ?>
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
                            <?php endif; ?>

                            <?php if ( $f_reading_guide ) : ?>
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
                            <?php endif; ?>

                            <?php if ( $f_reading_mask ) : ?>
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
                            <?php endif; ?>
                        </div>
                    </section>

                    <section class="da11y-accordion-group">
                        <button
                            type="button"
                            class="da11y-accordion-toggle"
                            aria-expanded="false"
                            aria-controls="da11y-group-visual"
                        >
                            <span class="da11y-accordion-title">
                                <?php echo esc_html__( 'Visual', 'devllo-accessibility-controls' ); ?>
                            </span>
                            <span class="da11y-accordion-indicator" aria-hidden="true"></span>
                        </button>
                        <div
                            id="da11y-group-visual"
                            class="da11y-accordion-panel"
                            hidden
                        >
                            <?php
                            // High contrast mode (implemented via contrast toggle inside themes).
                            if ( $f_contrast ) :
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
                            <?php endif; ?>

                            <?php if ( $f_grayscale ) : ?>
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
                            <?php endif; ?>

                            <?php if ( $f_dark_mode ) : ?>
                                <section class="da11y-section da11y-section-themes">
                                    <h3>
                                        <?php echo esc_html__( 'Colour themes', 'devllo-accessibility-controls' ); ?>
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
                            <?php endif; ?>

                            <?php if ( $f_brightness ) : ?>
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
                            <?php endif; ?>
                        </div>
                    </section>

                    <section class="da11y-accordion-group">
                        <button
                            type="button"
                            class="da11y-accordion-toggle"
                            aria-expanded="false"
                            aria-controls="da11y-group-navigation"
                        >
                            <span class="da11y-accordion-title">
                                <?php echo esc_html__( 'Navigation', 'devllo-accessibility-controls' ); ?>
                            </span>
                            <span class="da11y-accordion-indicator" aria-hidden="true"></span>
                        </button>
                        <div
                            id="da11y-group-navigation"
                            class="da11y-accordion-panel"
                            hidden
                        >
                            <?php if ( $f_big_cursor ) : ?>
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
                            <?php endif; ?>

                            <?php if ( $f_highlight_links ) : ?>
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
                            <?php endif; ?>

                            <?php if ( $f_focus_enhanced ) : ?>
                                <section class="da11y-section da11y-section-focus">
                                    <h3>
                                        <?php echo esc_html__( 'Focus outlines', 'devllo-accessibility-controls' ); ?>
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
                            <?php endif; ?>

                            <?php if ( $f_hide_images ) : ?>
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
                            <?php endif; ?>

                            <?php if ( $reduced_motion_enabled && $f_reduced_motion ) : ?>
                                <section class="da11y-section da11y-section-motion">
                                    <h3>
                                        <?php echo esc_html__( 'Reduce motion', 'devllo-accessibility-controls' ); ?>
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
                        </div>
                    </section>
                </div>

                <div class="da11y-section da11y-section-reset">
                    <button
                        type="button"
                        class="da11y-reset-all"
                    >
                        <?php echo esc_html__( 'Reset all settings', 'devllo-accessibility-controls' ); ?>
                    </button>
                </div>

                <?php
                $statement_url = ! empty( $settings['accessibility_statement_url'] ) ? $settings['accessibility_statement_url'] : '';
                if ( $statement_url ) :
                ?>
                <div class="da11y-section da11y-section-statement">
                    
                        <a href="<?php echo esc_url( $statement_url ); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <?php echo esc_html__( 'Accessibility statement', 'devllo-accessibility-controls' ); ?>
                    </a>
                </div>
                <?php endif; ?>
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
