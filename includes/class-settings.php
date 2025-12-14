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
 * - Rendering a simple settings page under Settings → Accessibility Controls.
 */
class Settings {

    /**
     * Option name used to store plugin settings.
     *
     * @var string
     */
    const OPTION_NAME = 'da11y_settings';

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
            add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
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

        // Button position: sanitize and fallback to default if invalid.
        $allowed_positions = [ 'bottom_right', 'bottom_left', 'top_right', 'top_left' ];
        $position          = isset( $input['button_position'] )
            ? sanitize_text_field( $input['button_position'] )
            : $defaults['button_position'];

        if ( ! in_array( $position, $allowed_positions, true ) ) {
            $position = $defaults['button_position'];
        }

        return [
            'enabled'         => $enabled,
            'button_position' => $position,
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
            'button_position' => 'bottom_right',
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
