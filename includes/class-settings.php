<?php

namespace DA11Y;

// Prevent direct access to this file.
defined( 'ABSPATH' ) || exit;

/**
 * Plugin settings wrapper.
 *
 * Responsible for:
 * - Registering the main settings option.
 * - Providing a helper to retrieve settings merged with defaults.
 * - Rendering settings pages under Settings → Accessibility Controls / Accessibility Guidance.
 */
class Settings {

    /**
     * Option name used to store plugin settings.
     *
     * @var string
     */
    const OPTION_NAME = 'da11y_settings';

    /**
     * Option name used to store checklist statuses.
     *
     * @var string
     */
    const CHECKLIST_OPTION_NAME = 'da11y_checklist';

    /**
     * Constructor.
     *
     * Hooks into admin_init to register the settings and into admin_menu
     * to add the settings page.
     *
     * @return void
     */
    public function __construct() {
        if ( is_admin() ) {
            add_action( 'admin_init', [ $this, 'register' ] );
            add_action( 'admin_init', [ $this, 'register_checklist' ] );
            add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
            add_action( 'admin_menu', [ $this, 'add_guidance_page' ] );
        }
    }

    /**
     * Register the settings option.
     *
     * @return void
     */
    public function register() {
        register_setting(
            'da11y_settings_group',
            self::OPTION_NAME,
            [
                'type'              => 'array',
                'sanitize_callback' => [ $this, 'sanitize' ],
                'default'           => self::get_defaults(),
            ]
        );

        // Section for main settings.
        add_settings_section(
            'da11y_main_section',
            __( 'Accessibility Controls Settings', 'devllo-accessibility-controls' ),
            '__return_null',
            'da11y_settings_page'
        );

        // Field: Enable Accessibility Controls.
        add_settings_field(
            'da11y_enabled',
            __( 'Enable Accessibility Controls', 'devllo-accessibility-controls' ),
            [ $this, 'render_enabled_field' ],
            'da11y_settings_page',
            'da11y_main_section'
        );

        // Field: Enable Dyslexia-friendly mode.
        add_settings_field(
            'da11y_dyslexia_enabled',
            __( 'Enable dyslexia-friendly mode', 'devllo-accessibility-controls' ),
            [ $this, 'render_dyslexia_enabled_field' ],
            'da11y_settings_page',
            'da11y_main_section'
        );

        // Field: Enable reduced motion mode.
        add_settings_field(
            'da11y_reduced_motion_enabled',
            __( 'Enable reduced motion mode', 'devllo-accessibility-controls' ),
            [ $this, 'render_reduced_motion_enabled_field' ],
            'da11y_settings_page',
            'da11y_main_section'
        );

        // Field: Button position.
        add_settings_field(
            'da11y_button_position',
            __( 'Button position', 'devllo-accessibility-controls' ),
            [ $this, 'render_button_position_field' ],
            'da11y_settings_page',
            'da11y_main_section'
        );
    }

    /**
     * Register the checklist settings option.
     *
     * @return void
     */
    public function register_checklist() {
        register_setting(
            'da11y_checklist_group',
            self::CHECKLIST_OPTION_NAME,
            [
                'type'              => 'array',
                'sanitize_callback' => [ $this, 'sanitize_checklist' ],
                'default'           => self::get_checklist_defaults(),
            ]
        );
    }

    /**
     * Add the settings page under Settings → Accessibility Controls.
     *
     * @return void
     */
    public function add_menu_page() {
        add_options_page(
            __( 'Accessibility Controls', 'devllo-accessibility-controls' ),
            __( 'Accessibility Controls', 'devllo-accessibility-controls' ),
            'manage_options',
            'da11y_settings_page',
            [ $this, 'render_settings_page' ]
        );
    }

    /**
     * Add the guidance page under Settings → Accessibility Guidance.
     *
     * @return void
     */
    public function add_guidance_page() {
        add_options_page(
            __( 'Accessibility Guidance', 'devllo-accessibility-controls' ),
            __( 'Accessibility Guidance', 'devllo-accessibility-controls' ),
            'manage_options',
            'da11y_accessibility_guidance',
            [ $this, 'render_guidance_page' ]
        );
    }

    /**
     * Sanitize settings before saving.
     *
     * @param array $input Raw input from the settings form.
     * @return array Sanitized settings.
     */
    public function sanitize( $input ) {
        $input = is_array( $input ) ? $input : [];

        $defaults = self::get_defaults();

        // Enabled: treat missing checkbox as false (unchecked).
        $enabled = ! empty( $input['enabled'] );

        // Dyslexia feature toggle.
        $dyslexia_enabled = ! empty( $input['dyslexia_enabled'] );

        // Reduced motion feature toggle.
        $reduced_motion_enabled = ! empty( $input['reduced_motion_enabled'] );

        // Button position: sanitize and fallback to default if invalid.
        $allowed_positions = [ 'bottom_right', 'bottom_left', 'top_right', 'top_left' ];
        $position          = isset( $input['button_position'] )
            ? sanitize_text_field( $input['button_position'] )
            : $defaults['button_position'];

        if ( ! in_array( $position, $allowed_positions, true ) ) {
            $position = $defaults['button_position'];
        }

        return [
            'enabled'                => $enabled,
            'button_position'        => $position,
            'dyslexia_enabled'       => $dyslexia_enabled,
            'reduced_motion_enabled' => $reduced_motion_enabled,
        ];
    }

    /**
     * Get default settings.
     *
     * @return array
     */
    public static function get_defaults() {
        return [
            'enabled'         => true,
            'button_position'        => 'bottom_right',
            'dyslexia_enabled'       => true,
            'reduced_motion_enabled' => true,
        ];
    }

    /**
     * Get settings merged with defaults.
     *
     * @return array
     */
    public static function get() {
        $stored   = get_option( self::OPTION_NAME, [] );
        $defaults = self::get_defaults();

        if ( ! is_array( $stored ) ) {
            $stored = [];
        }

        return array_merge( $defaults, $stored );
    }

    /**
     * Get the default checklist statuses.
     *
     * @return array
     */
    public static function get_checklist_defaults() {
        $defaults = [];

        foreach ( self::get_checklist_items() as $id => $item ) {
            // Default everything to "needs_attention" so site owners review it.
            $defaults[ $id ] = 'needs_attention';
        }

        return $defaults;
    }

    /**
     * Get checklist statuses merged with defaults.
     *
     * @return array
     */
    public static function get_checklist_statuses() {
        $stored   = get_option( self::CHECKLIST_OPTION_NAME, [] );
        $defaults = self::get_checklist_defaults();

        if ( ! is_array( $stored ) ) {
            $stored = [];
        }

        return array_merge( $defaults, $stored );
    }

    /**
     * Sanitize checklist values before saving.
     *
     * @param array $input Raw checklist input.
     * @return array Sanitized checklist.
     */
    public function sanitize_checklist( $input ) {
        $input = is_array( $input ) ? $input : [];

        $allowed_statuses = [ 'reviewed', 'needs_attention', 'not_applicable' ];
        $defaults         = self::get_checklist_defaults();
        $items            = self::get_checklist_items();
        $sanitized        = [];

        foreach ( $items as $id => $item ) {
            $value = isset( $input[ $id ] ) ? sanitize_text_field( $input[ $id ] ) : $defaults[ $id ];

            if ( ! in_array( $value, $allowed_statuses, true ) ) {
                $value = $defaults[ $id ];
            }

            $sanitized[ $id ] = $value;
        }

        return $sanitized;
    }

    /**
     * Get the static list of checklist items.
     *
     * @return array
     */
    public static function get_checklist_items() {
        return [
            'text_resize' => [
                'title'       => __( 'Text is resizable', 'devllo-accessibility-controls' ),
                'description' => __( 'Check that text can be resized (e.g. 200%) without loss of content or functionality.', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 1.4.4 Resize text',
            ],
            'contrast' => [
                'title'       => __( 'Maintain sufficient color contrast', 'devllo-accessibility-controls' ),
                'description' => __( 'Verify that text and essential UI elements meet at least WCAG AA color contrast ratios.', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 1.4.3 Contrast (Minimum)',
            ],
            'focus_visible' => [
                'title'       => __( 'Visible keyboard focus', 'devllo-accessibility-controls' ),
                'description' => __( 'Ensure keyboard focus is always visible and not removed by custom styles.', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 2.4.7 Focus Visible',
            ],
            'images_alt' => [
                'title'       => __( 'Images have meaningful alternatives', 'devllo-accessibility-controls' ),
                'description' => __( 'Check that informative images have appropriate alternative text and decorative images are marked accordingly.', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 1.1.1 Non-text Content',
            ],
            'motion' => [
                'title'       => __( 'Motion is optional and not overwhelming', 'devllo-accessibility-controls' ),
                'description' => __( 'Ensure motion, animations, or auto-playing content are limited, optional, and respect reduced-motion preferences.', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 2.2.2 Pause, Stop, Hide',
            ],
        ];
    }

    /**
     * Render the settings page wrapper.
     *
     * @return void
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Accessibility Controls', 'devllo-accessibility-controls' ); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( 'da11y_settings_group' );
                do_settings_sections( 'da11y_settings_page' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render the accessibility guidance / checklist page.
     *
     * @return void
     */
    public function render_guidance_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $items    = self::get_checklist_items();
        $statuses = self::get_checklist_statuses();

        $status_labels = [
            'reviewed'       => __( 'Reviewed', 'devllo-accessibility-controls' ),
            'needs_attention'=> __( 'Needs attention', 'devllo-accessibility-controls' ),
            'not_applicable' => __( 'Not applicable', 'devllo-accessibility-controls' ),
        ];

        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Accessibility Guidance', 'devllo-accessibility-controls' ); ?></h1>

            <p>
                <?php esc_html_e( 'This page provides guidance and a checklist to help you think about accessibility on your site. It is not a compliance audit or legal advice.', 'devllo-accessibility-controls' ); ?>
            </p>

            <p>
                <strong><?php esc_html_e( 'Important:', 'devllo-accessibility-controls' ); ?></strong>
                <?php esc_html_e( 'These notes are informational only and do not guarantee ADA, WCAG, or any legal compliance.', 'devllo-accessibility-controls' ); ?>
            </p>

            <form action="options.php" method="post">
                <?php settings_fields( 'da11y_checklist_group' ); ?>

                <h2><?php esc_html_e( 'Accessibility Checklist', 'devllo-accessibility-controls' ); ?></h2>

                <table class="form-table" role="presentation">
                    <tbody>
                    <?php foreach ( $items as $id => $item ) : ?>
                        <tr>
                            <th scope="row">
                                <label for="da11y-checklist-<?php echo esc_attr( $id ); ?>">
                                    <?php echo esc_html( $item['title'] ); ?>
                                </label>
                                <?php if ( ! empty( $item['wcag'] ) ) : ?>
                                    <p class="description"><?php echo esc_html( $item['wcag'] ); ?></p>
                                <?php endif; ?>
                            </th>
                            <td>
                                <p><?php echo esc_html( $item['description'] ); ?></p>

                                <select
                                    id="da11y-checklist-<?php echo esc_attr( $id ); ?>"
                                    name="<?php echo esc_attr( self::CHECKLIST_OPTION_NAME ); ?>[<?php echo esc_attr( $id ); ?>]"
                                >
                                    <?php foreach ( $status_labels as $value => $label ) : ?>
                                        <option
                                            value="<?php echo esc_attr( $value ); ?>"
                                            <?php selected( $statuses[ $id ], $value ); ?>
                                        >
                                            <?php echo esc_html( $label ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <h2><?php esc_html_e( 'Informational checks', 'devllo-accessibility-controls' ); ?></h2>
                <p class="description">
                    <?php esc_html_e( 'These are prompts to help you manually review your site. They are not automated tests or a complete audit.', 'devllo-accessibility-controls' ); ?>
                </p>
                <ul>
                    <li><?php esc_html_e( 'Check that there is a visible “skip to content” link near the top of your pages.', 'devllo-accessibility-controls' ); ?></li>
                    <li><?php esc_html_e( 'Review your CSS to ensure focus outlines are not removed without providing an accessible alternative.', 'devllo-accessibility-controls' ); ?></li>
                    <li><?php esc_html_e( 'Verify that your base font size is large enough for comfortable reading (for example, 16px or larger).', 'devllo-accessibility-controls' ); ?></li>
                </ul>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render the "Enable Accessibility Controls" checkbox.
     *
     * @return void
     */
    public function render_enabled_field() {
        $settings = self::get();
        ?>
        <label>
            <input
                type="checkbox"
                name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enabled]"
                value="1"
                <?php checked( ! empty( $settings['enabled'] ) ); ?>
            />
            <?php esc_html_e( 'Show the accessibility widget on the frontend.', 'devllo-accessibility-controls' ); ?>
        </label>
        <?php
    }

    /**
     * Render the "Enable dyslexia-friendly mode" checkbox.
     *
     * @return void
     */
    public function render_dyslexia_enabled_field() {
        $settings = self::get();
        ?>
        <label>
            <input
                type="checkbox"
                name="<?php echo esc_attr( self::OPTION_NAME ); ?>[dyslexia_enabled]"
                value="1"
                <?php checked( ! empty( $settings['dyslexia_enabled'] ) ); ?>
            />
            <?php esc_html_e( 'Allow visitors to toggle a dyslexia-friendly reading mode.', 'devllo-accessibility-controls' ); ?>
        </label>
        <?php
    }

    /**
     * Render the "Enable reduced motion mode" checkbox.
     *
     * @return void
     */
    public function render_reduced_motion_enabled_field() {
        $settings = self::get();
        ?>
        <label>
            <input
                type="checkbox"
                name="<?php echo esc_attr( self::OPTION_NAME ); ?>[reduced_motion_enabled]"
                value="1"
                <?php checked( ! empty( $settings['reduced_motion_enabled'] ) ); ?>
            />
            <?php esc_html_e( 'Allow visitors to reduce motion in the accessibility controls UI.', 'devllo-accessibility-controls' ); ?>
        </label>
        <?php
    }

    /**
     * Render the "Button position" select field.
     *
     * @return void
     */
    public function render_button_position_field() {
        $settings = self::get();

        $positions = [
            'bottom_right' => __( 'Bottom right', 'devllo-accessibility-controls' ),
            'bottom_left'  => __( 'Bottom left', 'devllo-accessibility-controls' ),
            'top_right'    => __( 'Top right', 'devllo-accessibility-controls' ),
            'top_left'     => __( 'Top left', 'devllo-accessibility-controls' ),
        ];
        ?>
        <select
            name="<?php echo esc_attr( self::OPTION_NAME ); ?>[button_position]"
        >
            <?php foreach ( $positions as $value => $label ) : ?>
                <option
                    value="<?php echo esc_attr( $value ); ?>"
                    <?php selected( $settings['button_position'], $value ); ?>
                >
                    <?php echo esc_html( $label ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">
            <?php esc_html_e( 'Choose where the accessibility button appears on the screen.', 'devllo-accessibility-controls' ); ?>
        </p>
        <?php
    }
}
