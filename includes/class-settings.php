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
     * Option name used to store basic check results.
     *
     * @var string
     */
    const CHECK_RESULTS_OPTION_NAME = 'da11y_check_results';

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
            add_action( 'admin_init', [ $this, 'maybe_run_basic_checks' ] );
            add_action( 'admin_menu', [ $this, 'add_menu_page' ] );
            add_action( 'admin_menu', [ $this, 'add_guidance_page' ] );
            add_action( 'admin_init', [ $this, 'maybe_generate_statement_page' ] );
        }

        // Frontend admin-only integrations.
        if ( ! is_admin() ) {
            add_action( 'admin_bar_menu', [ $this, 'add_frontend_quick_check_item' ], 100 );
            add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_quick_check_assets' ] );
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

        // Main section.
        add_settings_section(
            'da11y_main_section',
            __( 'General', 'devllo-accessibility-controls' ),
            '__return_null',
            'da11y_settings_page'
        );

        add_settings_field(
            'da11y_enabled',
            __( 'Enable Accessibility Controls', 'devllo-accessibility-controls' ),
            [ $this, 'render_enabled_field' ],
            'da11y_settings_page',
            'da11y_main_section'
        );

        add_settings_field(
            'da11y_button_position',
            __( 'Button position', 'devllo-accessibility-controls' ),
            [ $this, 'render_button_position_field' ],
            'da11y_settings_page',
            'da11y_main_section'
        );

        // Visual section.
        add_settings_section(
            'da11y_visual_section',
            __( 'Visual', 'devllo-accessibility-controls' ),
            '__return_null',
            'da11y_settings_page'
        );

        foreach ( [
            'feature_contrast'   => __( 'High contrast', 'devllo-accessibility-controls' ),
            'feature_dark_mode'  => __( 'Dark mode', 'devllo-accessibility-controls' ),
            'feature_grayscale'  => __( 'Grayscale', 'devllo-accessibility-controls' ),
            'feature_brightness' => __( 'Brightness control', 'devllo-accessibility-controls' ),
        ] as $key => $label ) {
            add_settings_field(
                'da11y_' . $key,
                $label,
                [ $this, 'render_feature_field' ],
                'da11y_settings_page',
                'da11y_visual_section',
                [ 'key' => $key, 'label' => $label ]
            );
        }

        // Text section.
        add_settings_section(
            'da11y_text_section',
            __( 'Text', 'devllo-accessibility-controls' ),
            '__return_null',
            'da11y_settings_page'
        );

        foreach ( [
            'feature_text_size'      => __( 'Text size', 'devllo-accessibility-controls' ),
            'feature_letter_spacing' => __( 'Letter spacing', 'devllo-accessibility-controls' ),
            'feature_line_spacing'   => __( 'Line spacing', 'devllo-accessibility-controls' ),
            'feature_word_spacing'   => __( 'Word spacing', 'devllo-accessibility-controls' ),
        ] as $key => $label ) {
            add_settings_field(
                'da11y_' . $key,
                $label,
                [ $this, 'render_feature_field' ],
                'da11y_settings_page',
                'da11y_text_section',
                [ 'key' => $key, 'label' => $label ]
            );
        }

        // Reading section.
        add_settings_section(
            'da11y_reading_section',
            __( 'Reading', 'devllo-accessibility-controls' ),
            '__return_null',
            'da11y_settings_page'
        );

        foreach ( [
            'feature_dyslexia'      => __( 'Dyslexia-friendly font', 'devllo-accessibility-controls' ),
            'feature_align_left'    => __( 'Text align left', 'devllo-accessibility-controls' ),
            'feature_reading_mode'  => __( 'Reading mode', 'devllo-accessibility-controls' ),
            'feature_reading_guide' => __( 'Reading guide', 'devllo-accessibility-controls' ),
            'feature_reading_mask'  => __( 'Reading mask', 'devllo-accessibility-controls' ),
        ] as $key => $label ) {
            add_settings_field(
                'da11y_' . $key,
                $label,
                [ $this, 'render_feature_field' ],
                'da11y_settings_page',
                'da11y_reading_section',
                [ 'key' => $key, 'label' => $label ]
            );
        }

        // Navigation section.
        add_settings_section(
            'da11y_navigation_section',
            __( 'Navigation', 'devllo-accessibility-controls' ),
            '__return_null',
            'da11y_settings_page'
        );

        foreach ( [
            'feature_big_cursor'        => __( 'Big cursor', 'devllo-accessibility-controls' ),
            'feature_highlight_links'   => __( 'Link highlighting', 'devllo-accessibility-controls' ),
            'feature_focus_enhanced'    => __( 'Focus outline enhancement', 'devllo-accessibility-controls' ),
            'feature_reduced_motion'    => __( 'Reduce animations', 'devllo-accessibility-controls' ),
            'feature_hide_images'       => __( 'Hide images', 'devllo-accessibility-controls' ),
            'feature_keyboard_shortcut' => __( 'Keyboard shortcut (Alt+A)', 'devllo-accessibility-controls' ),
        ] as $key => $label ) {
            add_settings_field(
                'da11y_' . $key,
                $label,
                [ $this, 'render_feature_field' ],
                'da11y_settings_page',
                'da11y_navigation_section',
                [ 'key' => $key, 'label' => $label ]
            );
        }

        // Widget customisation section.
        add_settings_section(
            'da11y_widget_section',
            __( 'Widget customisation', 'devllo-accessibility-controls' ),
            '__return_null',
            'da11y_settings_page'
        );

        add_settings_field(
            'da11y_button_label',
            __( 'Button label', 'devllo-accessibility-controls' ),
            [ $this, 'render_button_label_field' ],
            'da11y_settings_page',
            'da11y_widget_section'
        );

        add_settings_field(
            'da11y_button_bg_color',
            __( 'Button background colour', 'devllo-accessibility-controls' ),
            [ $this, 'render_button_bg_color_field' ],
            'da11y_settings_page',
            'da11y_widget_section'
        );

        add_settings_field(
            'da11y_button_text_color',
            __( 'Button text colour', 'devllo-accessibility-controls' ),
            [ $this, 'render_button_text_color_field' ],
            'da11y_settings_page',
            'da11y_widget_section'
        );

        add_settings_field(
            'da11y_button_size',
            __( 'Button size', 'devllo-accessibility-controls' ),
            [ $this, 'render_button_size_field' ],
            'da11y_settings_page',
            'da11y_widget_section'
        );

        add_settings_field(
            'da11y_button_icon',
            __( 'Button style', 'devllo-accessibility-controls' ),
            [ $this, 'render_button_icon_field' ],
            'da11y_settings_page',
            'da11y_widget_section'
        );

        // Accessibility statement section.
        add_settings_section(
            'da11y_statement_section',
            __( 'Accessibility statement', 'devllo-accessibility-controls' ),
            [ $this, 'render_statement_section_description' ],
            'da11y_settings_page'
        );

        add_settings_field(
            'da11y_accessibility_statement_page',
            __( 'Statement page', 'devllo-accessibility-controls' ),
            [ $this, 'render_statement_page_field' ],
            'da11y_settings_page',
            'da11y_statement_section'
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
     * Maybe run basic automated checks when requested.
     *
     * @return void
     */
    public function maybe_run_basic_checks() {
        if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( ! isset( $_GET['da11y_run_checks'] ) ) {
            return;
        }

        check_admin_referer( 'da11y_run_checks' );

        $results = $this->run_basic_checks();

        update_option( self::CHECK_RESULTS_OPTION_NAME, $results );

        // Redirect back to the guidance page without the query arg.
        $redirect = remove_query_arg( 'da11y_run_checks' );
        wp_safe_redirect( $redirect );
        exit;
    }

    /**
     * Maybe generate an accessibility statement page.
     *
     * @return void
     */
    public function maybe_generate_statement_page() {
        if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( ! isset( $_GET['da11y_generate_statement'] ) ) {
            return;
        }

        check_admin_referer( 'da11y_generate_statement' );

        $content = $this->get_statement_template();

        $page_id = wp_insert_post( [
            'post_title'   => __( 'Accessibility Statement', 'devllo-accessibility-controls' ),
            'post_content' => $content,
            'post_status'  => 'draft',
            'post_type'    => 'page',
        ] );

        if ( $page_id && ! is_wp_error( $page_id ) ) {
            $settings = self::get();
            $settings['accessibility_statement_page_id'] = $page_id;
            $settings['accessibility_statement_url']     = get_permalink( $page_id );
            update_option( self::OPTION_NAME, $settings );
        }

        $redirect = remove_query_arg( 'da11y_generate_statement' );
        wp_safe_redirect( $redirect );
        exit;
    }

    /**
     * Get the accessibility statement template content.
     *
     * @return string
     */
    protected function get_statement_template() {
        $site_name = get_bloginfo( 'name' );
        $site_url  = home_url();

        return sprintf(
            '<!-- wp:paragraph -->
    <p>%1$s is committed to ensuring digital accessibility for people with disabilities. We continually improve the user experience for everyone and apply relevant accessibility standards.</p>
    <!-- /wp:paragraph -->

    <!-- wp:heading -->
    <h2>Measures we take</h2>
    <!-- /wp:heading -->

    <!-- wp:paragraph -->
    <p>%1$s takes the following measures to ensure accessibility:</p>
    <!-- /wp:paragraph -->

    <!-- wp:list -->
    <ul>
    <li>We use the Devllo Accessibility Controls plugin to provide visitors with tools to adjust text size, contrast, spacing, and other display settings to suit their needs.</li>
    <li>We aim to use clear, simple language throughout our content.</li>
    <li>We provide alternative text for informative images.</li>
    <li>We aim to ensure our site is navigable by keyboard.</li>
    </ul>
    <!-- /wp:list -->

    <!-- wp:heading -->
    <h2>Known limitations</h2>
    <!-- /wp:heading -->

    <!-- wp:paragraph -->
    <p>While we aim to make our site as accessible as possible, some content or features may not yet meet all accessibility standards. We are working to address these issues.</p>
    <!-- /wp:paragraph -->

    <!-- wp:heading -->
    <h2>Feedback and contact</h2>
    <!-- /wp:heading -->

    <!-- wp:paragraph -->
    <p>We welcome your feedback on the accessibility of %1$s. If you experience any barriers or have suggestions for improvement, please contact us.</p>
    <!-- /wp:paragraph -->

    <!-- wp:heading -->
    <h2>Technical specifications</h2>
    <!-- /wp:heading -->

    <!-- wp:paragraph -->
    <p>This site aims to conform to the Web Content Accessibility Guidelines (WCAG) 2.1 Level AA. These guidelines explain how to make web content more accessible to people with disabilities.</p>
    <!-- /wp:paragraph -->

    <!-- wp:paragraph -->
    <p><em>This statement was generated using Devllo Accessibility Controls. It should be reviewed and updated to accurately reflect your site\'s accessibility efforts.</em></p>
    <!-- /wp:paragraph -->',
            esc_html( $site_name ),
            esc_url( $site_url )
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
     * Add a "Quick accessibility check" item to the frontend admin bar for admins.
     *
     * @param \WP_Admin_Bar $admin_bar Admin bar instance.
     * @return void
     */
    public function add_frontend_quick_check_item( $admin_bar ) {
        if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( is_admin() ) {
            return;
        }

        $admin_bar->add_node(
            [
                'id'    => 'da11y-quick-check',
                'title' => __( 'Accessibility quick check', 'devllo-accessibility-controls' ),
                'href'  => '#',
                'meta'  => [
                    'class' => 'da11y-quick-check-menu-item',
                ],
            ]
        );
    }

    /**
     * Enqueue frontend assets for the quick accessibility check panel (admins only).
     *
     * @return void
     */
    public function enqueue_frontend_quick_check_assets() {
        if ( is_admin() || ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        wp_enqueue_style(
            'da11y-frontend-quick-check',
            DA11Y_PLUGIN_URL . 'assets/css/frontend-quick-check.css',
            [],
            DA11Y_PLUGIN_VERSION
        );

        wp_enqueue_script(
            'da11y-frontend-quick-check',
            DA11Y_PLUGIN_URL . 'assets/js/frontend-quick-check.js',
            [],
            DA11Y_PLUGIN_VERSION,
            true
        );
    }

    /**
     * Sanitize settings before saving.
     *
     * @param array $input Raw input from the settings form.
     * @return array Sanitized settings.
     */
    public function sanitize( $input ) {
        $input    = is_array( $input ) ? $input : [];
        $defaults = self::get_defaults();

        // Enabled.
        $enabled = ! empty( $input['enabled'] );

        // Button position.
        $allowed_positions = [ 'bottom_right', 'bottom_left', 'top_right', 'top_left' ];
        $position          = isset( $input['button_position'] )
            ? sanitize_text_field( $input['button_position'] )
            : $defaults['button_position'];

        if ( ! in_array( $position, $allowed_positions, true ) ) {
            $position = $defaults['button_position'];
        }

        // Feature toggles — sanitize each as boolean.
        $feature_keys = [
            'feature_contrast',
            'feature_dark_mode',
            'feature_grayscale',
            'feature_brightness',
            'feature_text_size',
            'feature_letter_spacing',
            'feature_line_spacing',
            'feature_word_spacing',
            'feature_dyslexia',
            'feature_align_left',
            'feature_reading_mode',
            'feature_reading_guide',
            'feature_reading_mask',
            'feature_big_cursor',
            'feature_highlight_links',
            'feature_focus_enhanced',
            'feature_reduced_motion',
            'feature_hide_images',
            'feature_keyboard_shortcut',
        ];

        $features = [];
        foreach ( $feature_keys as $key ) {
            $features[ $key ] = ! empty( $input[ $key ] );
        }

        // Button label.
        $button_label = isset( $input['button_label'] )
            ? sanitize_text_field( $input['button_label'] )
            : '';

        // Button colours.
        $button_bg_color = isset( $input['button_bg_color'] )
            ? sanitize_hex_color( $input['button_bg_color'] )
            : '#111111';

        $button_text_color = isset( $input['button_text_color'] )
            ? sanitize_hex_color( $input['button_text_color'] )
            : '#ffffff';

        // Button size.
        $allowed_sizes = [ 'small', 'medium', 'large' ];
        $button_size   = isset( $input['button_size'] )
            ? sanitize_text_field( $input['button_size'] )
            : 'medium';
        if ( ! in_array( $button_size, $allowed_sizes, true ) ) {
            $button_size = 'medium';
        }

        // Button icon.
        $allowed_icons = [ 'text_only', 'icon_only', 'icon_and_text' ];
        $button_icon   = isset( $input['button_icon'] )
            ? sanitize_text_field( $input['button_icon'] )
            : 'text_only';
        if ( ! in_array( $button_icon, $allowed_icons, true ) ) {
            $button_icon = 'text_only';
        }

        // Accessibility statement page ID — derive URL from it.
        $accessibility_statement_page_id = isset( $input['accessibility_statement_page_id'] )
            ? absint( $input['accessibility_statement_page_id'] )
            : 0;

        $accessibility_statement_url = $accessibility_statement_page_id
            ? get_permalink( $accessibility_statement_page_id )
            : '';

        return array_merge(
            [
                'enabled'          => $enabled,
                'button_position'  => $position,
                'button_label'     => $button_label,
                'button_bg_color'  => $button_bg_color,
                'button_text_color'=> $button_text_color,
                'button_size'      => $button_size,
                'button_icon'      => $button_icon,
                'accessibility_statement_url'     => $accessibility_statement_url,
                'accessibility_statement_page_id' => $accessibility_statement_page_id,
            ],
            $features
        );
            }

    /**
     * Get default settings.
     *
     * @return array
     */
    public static function get_defaults() {
        $defaults = [
            'enabled'                => true,
            'button_position'        => 'bottom_right',

            // Visual
            'feature_contrast'       => true,
            'feature_dark_mode'      => true,
            'feature_grayscale'      => true,
            'feature_brightness'     => false,

            // Text
            'feature_text_size'      => true,
            'feature_letter_spacing' => true,
            'feature_line_spacing'   => true,
            'feature_word_spacing'   => false,

            // Reading
            'feature_dyslexia'       => true,
            'feature_align_left'     => true,
            'feature_reading_mode'   => true,
            'feature_reading_guide'  => false,
            'feature_reading_mask'   => false,

            // Navigation
            'feature_big_cursor'          => true,
            'feature_highlight_links'     => true,
            'feature_focus_enhanced'      => true,
            'feature_reduced_motion'      => true,
            'feature_hide_images'         => false,
            'feature_keyboard_shortcut'   => true,

            // Widget customisation.
            'button_label'      => '',
            'button_bg_color'   => '#111111',
            'button_text_color' => '#ffffff',
            'button_size'       => 'medium',
            'button_icon'       => 'text_only',
            'accessibility_statement_url' => '',
            'accessibility_statement_page_id' => 0,
        ];

        return apply_filters( 'da11y_default_settings', $defaults );
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
            // Perceivable
            'text_resize' => [
                'title'       => __( 'Text is resizable', 'devllo-accessibility-controls' ),
                'description' => __( 'Check that text can be resized up to 200% without loss of content or functionality.', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 1.4.4 Resize text',
            ],
            'contrast' => [
                'title'       => __( 'Sufficient colour contrast', 'devllo-accessibility-controls' ),
                'description' => __( 'Verify that text and essential UI elements meet at least WCAG AA contrast ratios (4.5:1 for normal text, 3:1 for large text).', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 1.4.3 Contrast (Minimum)',
            ],
            'colour_alone' => [
                'title'       => __( 'Colour is not the only visual means of conveying information', 'devllo-accessibility-controls' ),
                'description' => __( 'Ensure information is not conveyed by colour alone — use labels, patterns, or icons alongside colour.', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 1.4.1 Use of Colour',
            ],
            'images_alt' => [
                'title'       => __( 'Images have meaningful alternatives', 'devllo-accessibility-controls' ),
                'description' => __( 'Check that informative images have appropriate alternative text and decorative images are marked accordingly.', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 1.1.1 Non-text Content',
            ],
            'captions' => [
                'title'       => __( 'Videos have captions', 'devllo-accessibility-controls' ),
                'description' => __( 'Ensure all pre-recorded video content has accurate captions for users who are deaf or hard of hearing.', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 1.2.2 Captions (Prerecorded)',
            ],
            'audio_description' => [
                'title'       => __( 'Audio content has transcripts or descriptions', 'devllo-accessibility-controls' ),
                'description' => __( 'Provide transcripts for audio-only content and audio descriptions for video content where visual information is not conveyed in the audio.', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 1.2.1 Audio-only and Video-only',
            ],
            'reflow' => [
                'title'       => __( 'Content reflows at 400% zoom', 'devllo-accessibility-controls' ),
                'description' => __( 'Check that content can be viewed at 400% zoom on a 1280px wide screen without requiring horizontal scrolling.', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 1.4.10 Reflow',
            ],

            // Operable
            'keyboard' => [
                'title'       => __( 'All functionality is keyboard accessible', 'devllo-accessibility-controls' ),
                'description' => __( 'Verify that all interactive elements — menus, forms, modals, carousels — can be operated using only a keyboard.', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 2.1.1 Keyboard',
            ],
            'keyboard_trap' => [
                'title'       => __( 'No keyboard traps', 'devllo-accessibility-controls' ),
                'description' => __( 'Ensure keyboard focus is never trapped in a component — users can always navigate away using the keyboard.', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 2.1.2 No Keyboard Trap',
            ],
            'skip_links' => [
                'title'       => __( 'Skip navigation links are present', 'devllo-accessibility-controls' ),
                'description' => __( 'Provide a skip link at the top of each page so keyboard users can bypass repeated navigation and jump to main content.', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 2.4.1 Bypass Blocks',
            ],
            'page_title' => [
                'title'       => __( 'Pages have descriptive titles', 'devllo-accessibility-controls' ),
                'description' => __( 'Ensure every page has a unique, descriptive title that identifies its topic or purpose.', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 2.4.2 Page Titled',
            ],
            'focus_visible' => [
                'title'       => __( 'Visible keyboard focus', 'devllo-accessibility-controls' ),
                'description' => __( 'Ensure keyboard focus is always visible and not removed by custom styles.', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 2.4.7 Focus Visible',
            ],
            'focus_order' => [
                'title'       => __( 'Focus order is logical', 'devllo-accessibility-controls' ),
                'description' => __( 'Verify that the keyboard tab order follows a logical sequence that matches the visual layout of the page.', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 2.4.3 Focus Order',
            ],
            'link_purpose' => [
                'title'       => __( 'Link purpose is clear', 'devllo-accessibility-controls' ),
                'description' => __( 'Ensure link text clearly describes the destination or purpose. Avoid generic text like "click here" or "read more".', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 2.4.4 Link Purpose',
            ],
            'flashing' => [
                'title'       => __( 'No content flashes more than 3 times per second', 'devllo-accessibility-controls' ),
                'description' => __( 'Ensure no content flashes or strobes more than three times per second to avoid triggering seizures.', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 2.3.1 Three Flashes or Below Threshold',
            ],
            'motion' => [
                'title'       => __( 'Motion is optional and not overwhelming', 'devllo-accessibility-controls' ),
                'description' => __( 'Ensure motion, animations, and auto-playing content are limited, optional, and respect reduced-motion preferences.', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 2.2.2 Pause, Stop, Hide',
            ],

            // Understandable
            'language' => [
                'title'       => __( 'Page language is set', 'devllo-accessibility-controls' ),
                'description' => __( 'Ensure the primary language of each page is identified using the lang attribute on the html element.', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 3.1.1 Language of Page',
            ],
            'consistent_navigation' => [
                'title'       => __( 'Navigation is consistent across pages', 'devllo-accessibility-controls' ),
                'description' => __( 'Verify that navigation menus, headers, and footers appear in the same location and order across all pages.', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 3.2.3 Consistent Navigation',
            ],
            'error_messages' => [
                'title'       => __( 'Error messages are descriptive', 'devllo-accessibility-controls' ),
                'description' => __( 'Ensure form errors clearly identify which field has an error and describe what the user needs to do to fix it.', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 3.3.1 Error Identification',
            ],
            'form_labels' => [
                'title'       => __( 'All form inputs have labels', 'devllo-accessibility-controls' ),
                'description' => __( 'Verify that every form input has a visible label or accessible name so users know what information is required.', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 1.3.1 Info and Relationships',
            ],
            'error_prevention' => [
                'title'       => __( 'Error prevention for important submissions', 'devllo-accessibility-controls' ),
                'description' => __( 'For forms that submit important data (purchases, legal agreements), provide a way to review, correct, or confirm before final submission.', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 3.3.4 Error Prevention',
            ],

            // Robust
            'valid_html' => [
                'title'       => __( 'HTML markup is valid', 'devllo-accessibility-controls' ),
                'description' => __( 'Check that pages use valid, well-formed HTML with no duplicate IDs or improperly nested elements.', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 4.1.1 Parsing',
            ],
            'aria' => [
                'title'       => __( 'ARIA is used correctly', 'devllo-accessibility-controls' ),
                'description' => __( 'Ensure ARIA roles, states, and properties are used correctly and only where native HTML cannot provide the same semantics.', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 4.1.2 Name, Role, Value',
            ],
            'status_messages' => [
                'title'       => __( 'Status messages are programmatically determined', 'devllo-accessibility-controls' ),
                'description' => __( 'Ensure status messages (success, error, loading) are conveyed to assistive technologies without requiring focus, using aria-live regions.', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 4.1.3 Status Messages',
            ],
            'name_role_value' => [
                'title'       => __( 'Interactive elements have accessible names', 'devllo-accessibility-controls' ),
                'description' => __( 'Verify that all buttons, links, and form controls have accessible names that clearly describe their purpose to assistive technologies.', 'devllo-accessibility-controls' ),
                'wcag'        => 'WCAG 2.1 – 4.1.2 Name, Role, Value',
            ],
        ];
    }


    /**
     * Run a small set of basic automated accessibility checks.
     *
     * @return array
     */
    protected function run_basic_checks() {
        $results = [
            'last_run' => time(),
            'checks'   => [],
        ];

        $results['checks']['skip_link']       = $this->check_skip_link();
        $results['checks']['focus_outline']   = $this->check_focus_outline();
        $results['checks']['font_size']       = $this->check_font_size_heuristic();
        $results['checks']['headings']        = $this->check_heading_structure();
        $results['checks']['image_alt_usage'] = $this->check_image_alt_usage();
        $results['checks']['form_labels']     = $this->check_form_labels();
        $results['checks']['lang_attribute']  = $this->check_lang_attribute();
        $results['checks']['viewport']        = $this->check_viewport_meta();
        $results['checks']['link_text']       = $this->check_generic_link_text();
        $results['checks']['empty_buttons']   = $this->check_empty_buttons();
        $results['checks']['table_headers']   = $this->check_table_headers();
        $results['checks']['pdf_links']       = $this->check_pdf_links();
        $results['checks']['autoplay_media']  = $this->check_autoplay_media();

        return $results;
    }

    /**
     * Check for presence of a skip link on the homepage.
     *
     * @return array
     */
    protected function check_skip_link() {
        $url      = home_url( '/' );
        $response = wp_remote_get(
            $url,
            [
                'timeout' => 5,
            ]
        );

        if ( is_wp_error( $response ) ) {
            return [
                'status'  => 'error',
                'message' => sprintf(
                    /* translators: %s: error message */
                    __( 'Skip link check: could not fetch the homepage (%s).', 'devllo-accessibility-controls' ),
                    $response->get_error_message()
                ),
            ];
        }

        $body = wp_remote_retrieve_body( $response );

        if ( ! is_string( $body ) || '' === $body ) {
            return [
                'status'  => 'error',
                'message' => __( 'Skip link check: the homepage response was empty.', 'devllo-accessibility-controls' ),
            ];
        }

        // Only inspect the beginning of the document.
        $snippet = substr( $body, 0, 10000 );

        if ( preg_match( '/<a[^>]+href=["\']#(main|content|primary|skip)/i', $snippet ) ) {
            return [
                'status'  => 'ok',
                'message' => __( 'Skip link check: a skip link appears to be present on the homepage.', 'devllo-accessibility-controls' ),
            ];
        }

        return [
            'status'  => 'warn',
            'message' => __( 'Skip link check: no skip link was detected on the homepage; consider adding one to improve keyboard navigation.', 'devllo-accessibility-controls' ),
        ];
    }

    /**
     * Check for CSS that removes focus outlines in the active theme stylesheets.
     *
     * @return array
     */
    protected function check_focus_outline() {
        $paths   = [];
        $paths[] = trailingslashit( get_stylesheet_directory() ) . 'style.css';

        if ( get_stylesheet_directory() !== get_template_directory() ) {
            $paths[] = trailingslashit( get_template_directory() ) . 'style.css';
        }

        $found_removal    = false;
        $found_replacement = false;

        foreach ( $paths as $path ) {
            if ( ! file_exists( $path ) || ! is_readable( $path ) ) {
                continue;
            }

            $contents = file_get_contents( $path );

            if ( false === $contents ) {
                continue;
            }

            if ( preg_match( '/outline\s*:\s*(none|0)\s*;?/i', $contents ) ) {
                $found_removal = true;
            }

            // Check for a replacement focus style.
            if ( preg_match( '/:focus\s*{[^}]*outline\s*:/i', $contents ) ) {
                $found_replacement = true;
            }
        }

        if ( $found_removal && ! $found_replacement ) {
            return [
                'status'  => 'warn',
                'message' => __( 'Focus outline check: CSS that removes focus outlines was detected in the active theme with no apparent replacement focus style. Ensure a visible focus style is provided.', 'devllo-accessibility-controls' ),
            ];
        }

        if ( $found_removal && $found_replacement ) {
            return [
                'status'  => 'info',
                'message' => __( 'Focus outline check: CSS that removes focus outlines was detected but a replacement focus style also appears to be present. Verify it is visible enough.', 'devllo-accessibility-controls' ),
            ];
        }

        return [
            'status'  => 'ok',
            'message' => __( 'Focus outline check: no focus-outline removal was detected in the main theme stylesheets.', 'devllo-accessibility-controls' ),
        ];
    }

    /**
     * Heuristic base font size check.
     *
     * @return array
     */
    protected function check_font_size_heuristic() {
        // First try theme.json.
        $theme_json_path = trailingslashit( get_stylesheet_directory() ) . 'theme.json';

        if ( file_exists( $theme_json_path ) && is_readable( $theme_json_path ) ) {
            $json = file_get_contents( $theme_json_path );

            if ( $json ) {
                $data = json_decode( $json, true );
                $font_size = isset( $data['settings']['typography']['fontSize'] )
                    ? $data['settings']['typography']['fontSize']
                    : null;

                if ( $font_size ) {
                    return [
                        'status'  => 'info',
                        'message' => sprintf(
                            __( 'Font size check: theme.json detected. Base font size setting found. Verify that your base font size is comfortably readable (16px or larger recommended).', 'devllo-accessibility-controls' )
                        ),
                    ];
                }
            }
        }

        // Fall back to style.css.
        $path = trailingslashit( get_stylesheet_directory() ) . 'style.css';

        if ( ! file_exists( $path ) || ! is_readable( $path ) ) {
            return [
                'status'  => 'info',
                'message' => __( 'Font size check: could not find a readable theme stylesheet. Verify that your base font size is 16px or larger.', 'devllo-accessibility-controls' ),
            ];
        }

        $contents = file_get_contents( $path );

        if ( false === $contents ) {
            return [
                'status'  => 'info',
                'message' => __( 'Font size check: could not read the theme stylesheet. Verify that your base font size is 16px or larger.', 'devllo-accessibility-controls' ),
            ];
        }

        if ( preg_match( '/(html|body)\s*{[^}]*font-size\s*:\s*([\d.]+)px/i', $contents, $matches ) ) {
            $size = (float) $matches[2];

            if ( $size < 16 ) {
                return [
                    'status'  => 'warn',
                    'message' => sprintf(
                        __( 'Font size check: base font size appears to be approximately %spx. This may be small for some users.', 'devllo-accessibility-controls' ),
                        $size
                    ),
                ];
            }

            return [
                'status'  => 'ok',
                'message' => sprintf(
                    __( 'Font size check: base font size appears to be approximately %spx.', 'devllo-accessibility-controls' ),
                    $size
                ),
            ];
        }

        return [
            'status'  => 'info',
            'message' => __( 'Font size check: could not automatically determine a base font size. Verify manually that text is comfortably readable at 16px or larger.', 'devllo-accessibility-controls' ),
        ];
    }

    /**
     * Check heading structure on the homepage.
     *
     * @return array
     */
    protected function check_heading_structure() {
        $url      = home_url( '/' );
        $response = wp_remote_get(
            $url,
            [
                'timeout' => 5,
            ]
        );

        if ( is_wp_error( $response ) ) {
            return [
                'status'  => 'error',
                'message' => sprintf(
                    /* translators: %s: error message */
                    __( 'Heading check: could not fetch the homepage (%s).', 'devllo-accessibility-controls' ),
                    $response->get_error_message()
                ),
            ];
        }

        $body = wp_remote_retrieve_body( $response );

        if ( ! is_string( $body ) || '' === $body ) {
            return [
                'status'  => 'error',
                'message' => __( 'Heading check: the homepage response was empty.', 'devllo-accessibility-controls' ),
            ];
        }

        preg_match_all( '/<h([1-6])\b[^>]*>/i', $body, $matches );

        if ( empty( $matches[1] ) ) {
            return [
                'status'  => 'info',
                'message' => __( 'Heading check: no headings were detected on the homepage. Ensure there is a clear heading structure.', 'devllo-accessibility-controls' ),
            ];
        }

        $levels = array_map( 'intval', $matches[1] );
        $h1_count = count( array_filter( $levels, static function ( $level ) {
            return 1 === (int) $level;
        } ) );

        $has_jump = false;
        $previous = null;
        foreach ( $levels as $level ) {
            if ( null !== $previous && $level > $previous + 1 ) {
                $has_jump = true;
                break;
            }
            $previous = $level;
        }

        if ( $h1_count === 0 ) {
            return [
                'status'  => 'info',
                'message' => __( 'Heading check: no H1 heading was detected on the homepage. Ensure there is a clear main heading.', 'devllo-accessibility-controls' ),
            ];
        }

        if ( $h1_count > 1 ) {
            return [
                'status'  => 'warn',
                'message' => __( 'Heading check: multiple H1 headings were detected on the homepage; review heading structure for clarity.', 'devllo-accessibility-controls' ),
            ];
        }

        if ( $has_jump ) {
            return [
                'status'  => 'info',
                'message' => __( 'Heading check: heading levels on the homepage may skip levels (e.g. H1 to H3); review for a logical outline.', 'devllo-accessibility-controls' ),
            ];
        }

        return [
            'status'  => 'ok',
            'message' => __( 'Heading check: no obvious heading structure issues were detected on the homepage.', 'devllo-accessibility-controls' ),
        ];
    }

    /**
     * Sample image alt usage on the homepage.
     *
     * @return array
     */
    protected function check_image_alt_usage() {
        $url      = home_url( '/' );
        $response = wp_remote_get(
            $url,
            [
                'timeout' => 5,
            ]
        );

        if ( is_wp_error( $response ) ) {
            return [
                'status'  => 'error',
                'message' => sprintf(
                    /* translators: %s: error message */
                    __( 'Image alt check: could not fetch the homepage (%s).', 'devllo-accessibility-controls' ),
                    $response->get_error_message()
                ),
            ];
        }

        $body = wp_remote_retrieve_body( $response );

        if ( ! is_string( $body ) || '' === $body ) {
            return [
                'status'  => 'error',
                'message' => __( 'Image alt check: the homepage response was empty.', 'devllo-accessibility-controls' ),
            ];
        }

        preg_match_all( '/<img\b[^>]*>/i', $body, $matches );
        $images = $matches[0] ?? [];

        if ( empty( $images ) ) {
            return [
                'status'  => 'info',
                'message' => __( 'Image alt check: no images were detected on the homepage.', 'devllo-accessibility-controls' ),
            ];
        }

        $limit            = 100;
        $total_sampled    = 0;
        $with_alt         = 0;
        $decorative_alt   = 0;
        $missing_alt      = 0;

        foreach ( $images as $img ) {
            if ( $total_sampled >= $limit ) {
                break;
            }

            $total_sampled++;

            if ( preg_match( '/\balt=["\']([^"\']*)["\']/i', $img, $alt_match ) ) {
                $alt_value = $alt_match[1];

                if ( '' === $alt_value ) {
                    $decorative_alt++;
                } else {
                    $with_alt++;
                }
            } else {
                $missing_alt++;
            }
        }

        $message = sprintf(
            /* translators: 1: total images sampled, 2: with alt, 3: decorative (empty alt), 4: missing alt */
            __( 'Image alt check: sampled %1$d images on the homepage – %2$d with alt text, %3$d decorative (empty alt), %4$d with missing alt attributes.', 'devllo-accessibility-controls' ),
            $total_sampled,
            $with_alt,
            $decorative_alt,
            $missing_alt
        );

        $status = 'ok';
        if ( $missing_alt > 0 ) {
            $status = 'warn';
        }

        return [
            'status'  => $status,
            'message' => $message,
        ];
    }

    /**
     * Heuristic check for form label presence on the homepage.
     *
     * @return array
     */
    protected function check_form_labels() {
        $url      = home_url( '/' );
        $response = wp_remote_get(
            $url,
            [
                'timeout' => 5,
            ]
        );

        if ( is_wp_error( $response ) ) {
            return [
                'status'  => 'error',
                'message' => sprintf(
                    /* translators: %s: error message */
                    __( 'Form label check: could not fetch the homepage (%s).', 'devllo-accessibility-controls' ),
                    $response->get_error_message()
                ),
            ];
        }

        $body = wp_remote_retrieve_body( $response );

        if ( ! is_string( $body ) || '' === $body ) {
            return [
                'status'  => 'error',
                'message' => __( 'Form label check: the homepage response was empty.', 'devllo-accessibility-controls' ),
            ];
        }

        // Collect all label for= attributes.
        $label_for_ids = [];
        if ( preg_match_all( '/<label\b[^>]*for=["\']([^"\']+)["\']/i', $body, $label_matches ) ) {
            $label_for_ids = array_map( 'trim', $label_matches[1] );
        }

        // Inputs/selects/textareas to inspect.
        $controls = [];
        if ( preg_match_all( '/<(input|select|textarea)\b[^>]*>/i', $body, $control_matches ) ) {
            $controls = $control_matches[0];
        }

        if ( empty( $controls ) ) {
            return [
                'status'  => 'info',
                'message' => __( 'Form label check: no form controls were detected on the homepage.', 'devllo-accessibility-controls' ),
            ];
        }

        $total_controls = 0;
        $likely_labeled = 0;
        $unlabeled      = 0;

        foreach ( $controls as $control ) {
            $total_controls++;

            // Ignore hidden inputs.
            if ( preg_match( '/<input\b[^>]*type=["\']hidden["\']/i', $control ) ) {
                $total_controls--;
                continue;
            }

            $has_aria = preg_match( '/\baria-label=["\']([^"\']+)["\']/i', $control )
                || preg_match( '/\baria-labelledby=["\']([^"\']+)["\']/i', $control );

            $has_for_label = false;
            if ( preg_match( '/\bid=["\']([^"\']+)["\']/i', $control, $id_match ) ) {
                $control_id    = $id_match[1];
                $has_for_label = in_array( $control_id, $label_for_ids, true );
            }

            if ( $has_aria || $has_for_label ) {
                $likely_labeled++;
            } else {
                $unlabeled++;
            }
        }

        if ( 0 === $total_controls ) {
            return [
                'status'  => 'info',
                'message' => __( 'Form label check: no visible form controls were detected on the homepage.', 'devllo-accessibility-controls' ),
            ];
        }

        $message = sprintf(
            /* translators: 1: total controls, 2: likely labeled, 3: unlabeled */
            __( 'Form label check: sampled %1$d form controls on the homepage – %2$d appear to have labels or accessible names, %3$d appear unlabeled.', 'devllo-accessibility-controls' ),
            $total_controls,
            $likely_labeled,
            $unlabeled
        );

        $status = 'ok';
        if ( $unlabeled > 0 ) {
            $status = 'warn';
        }

        return [
            'status'  => $status,
            'message' => $message,
        ];
    }

    /**
     * Check for lang attribute on the html element.
     *
     * @return array
     */
    protected function check_lang_attribute() {
        $url      = home_url( '/' );
        $response = wp_remote_get( $url, [ 'timeout' => 5 ] );

        if ( is_wp_error( $response ) ) {
            return [
                'status'  => 'error',
                'message' => sprintf(
                    __( 'Language check: could not fetch the homepage (%s).', 'devllo-accessibility-controls' ),
                    $response->get_error_message()
                ),
            ];
        }

        $body = wp_remote_retrieve_body( $response );

        if ( preg_match( '/<html[^>]+lang=["\'][^"\']+["\']/i', $body ) ) {
            return [
                'status'  => 'ok',
                'message' => __( 'Language check: a lang attribute was detected on the html element.', 'devllo-accessibility-controls' ),
            ];
        }

        return [
            'status'  => 'warn',
            'message' => __( 'Language check: no lang attribute was detected on the html element. Add a lang attribute to help screen readers use the correct language.', 'devllo-accessibility-controls' ),
        ];
    }

    /**
     * Check for viewport meta tag that prevents zooming.
     *
     * @return array
     */
    protected function check_viewport_meta() {
        $url      = home_url( '/' );
        $response = wp_remote_get( $url, [ 'timeout' => 5 ] );

        if ( is_wp_error( $response ) ) {
            return [
                'status'  => 'error',
                'message' => sprintf(
                    __( 'Viewport check: could not fetch the homepage (%s).', 'devllo-accessibility-controls' ),
                    $response->get_error_message()
                ),
            ];
        }

        $body    = wp_remote_retrieve_body( $response );
        $snippet = substr( $body, 0, 5000 );

        if ( preg_match( '/user-scalable\s*=\s*no/i', $snippet ) ) {
            return [
                'status'  => 'warn',
                'message' => __( 'Viewport check: user-scalable=no was detected in the viewport meta tag. This prevents users from zooming and fails WCAG 1.4.4.', 'devllo-accessibility-controls' ),
            ];
        }

        if ( preg_match( '/maximum-scale\s*=\s*1/i', $snippet ) ) {
            return [
                'status'  => 'warn',
                'message' => __( 'Viewport check: maximum-scale=1 was detected in the viewport meta tag. This may prevent users from zooming on some devices.', 'devllo-accessibility-controls' ),
            ];
        }

        return [
            'status'  => 'ok',
            'message' => __( 'Viewport check: no zoom-blocking viewport settings were detected.', 'devllo-accessibility-controls' ),
        ];
    }

    /**
     * Check for generic link text.
     *
     * @return array
     */
    protected function check_generic_link_text() {
        $url      = home_url( '/' );
        $response = wp_remote_get( $url, [ 'timeout' => 5 ] );

        if ( is_wp_error( $response ) ) {
            return [
                'status'  => 'error',
                'message' => sprintf(
                    __( 'Link text check: could not fetch the homepage (%s).', 'devllo-accessibility-controls' ),
                    $response->get_error_message()
                ),
            ];
        }

        $body = wp_remote_retrieve_body( $response );

        preg_match_all( '/<a[^>]*>(.*?)<\/a>/is', $body, $matches );

        $generic_phrases = [ 'click here', 'read more', 'learn more', 'more info', 'here', 'link', 'this' ];
        $generic_count   = 0;

        foreach ( $matches[1] as $link_text ) {
            $text = strtolower( trim( strip_tags( $link_text ) ) );
            if ( in_array( $text, $generic_phrases, true ) ) {
                $generic_count++;
            }
        }

        if ( $generic_count > 0 ) {
            return [
                'status'  => 'warn',
                'message' => sprintf(
                    __( 'Link text check: %d link(s) with generic text (e.g. "click here", "read more") were detected on the homepage. Use descriptive link text instead.', 'devllo-accessibility-controls' ),
                    $generic_count
                ),
            ];
        }

        return [
            'status'  => 'ok',
            'message' => __( 'Link text check: no obviously generic link text was detected on the homepage.', 'devllo-accessibility-controls' ),
        ];
    }

    /**
     * Check for empty buttons.
     *
     * @return array
     */
    protected function check_empty_buttons() {
        $url      = home_url( '/' );
        $response = wp_remote_get( $url, [ 'timeout' => 5 ] );

        if ( is_wp_error( $response ) ) {
            return [
                'status'  => 'error',
                'message' => sprintf(
                    __( 'Button check: could not fetch the homepage (%s).', 'devllo-accessibility-controls' ),
                    $response->get_error_message()
                ),
            ];
        }

        $body = wp_remote_retrieve_body( $response );

        preg_match_all( '/<button([^>]*)>(.*?)<\/button>/is', $body, $matches );

        $empty_count = 0;

        foreach ( $matches as $index => $match ) {
            $attrs = $matches[1][ $index ] ?? '';
            $inner = $matches[2][ $index ] ?? '';

            $text        = trim( strip_tags( $inner ) );
            $has_aria    = preg_match( '/aria-label=["\'][^"\']+["\']/i', $attrs );
            $has_title   = preg_match( '/title=["\'][^"\']+["\']/i', $attrs );

            if ( empty( $text ) && ! $has_aria && ! $has_title ) {
                $empty_count++;
            }
        }

        if ( $empty_count > 0 ) {
            return [
                'status'  => 'warn',
                'message' => sprintf(
                    __( 'Button check: %d button(s) with no visible text or accessible name were detected. Ensure all buttons have a descriptive label.', 'devllo-accessibility-controls' ),
                    $empty_count
                ),
            ];
        }

        return [
            'status'  => 'ok',
            'message' => __( 'Button check: no obviously unlabeled buttons were detected on the homepage.', 'devllo-accessibility-controls' ),
        ];
    }

    /**
     * Check for tables without header cells.
     *
     * @return array
     */
    protected function check_table_headers() {
        $url      = home_url( '/' );
        $response = wp_remote_get( $url, [ 'timeout' => 5 ] );

        if ( is_wp_error( $response ) ) {
            return [
                'status'  => 'error',
                'message' => sprintf(
                    __( 'Table check: could not fetch the homepage (%s).', 'devllo-accessibility-controls' ),
                    $response->get_error_message()
                ),
            ];
        }

        $body = wp_remote_retrieve_body( $response );

        preg_match_all( '/<table[^>]*>(.*?)<\/table>/is', $body, $table_matches );

        if ( empty( $table_matches[0] ) ) {
            return [
                'status'  => 'info',
                'message' => __( 'Table check: no tables were detected on the homepage.', 'devllo-accessibility-controls' ),
            ];
        }

        $tables_without_headers = 0;

        foreach ( $table_matches[1] as $table_content ) {
            if ( ! preg_match( '/<th[\s>]/i', $table_content ) ) {
                $tables_without_headers++;
            }
        }

        if ( $tables_without_headers > 0 ) {
            return [
                'status'  => 'warn',
                'message' => sprintf(
                    __( 'Table check: %d table(s) without header cells were detected. Ensure data tables have proper th elements.', 'devllo-accessibility-controls' ),
                    $tables_without_headers
                ),
            ];
        }

        return [
            'status'  => 'ok',
            'message' => __( 'Table check: all detected tables appear to have header cells.', 'devllo-accessibility-controls' ),
        ];
    }

    /**
     * Check for links to PDF files.
     *
     * @return array
     */
    protected function check_pdf_links() {
        $url      = home_url( '/' );
        $response = wp_remote_get( $url, [ 'timeout' => 5 ] );

        if ( is_wp_error( $response ) ) {
            return [
                'status'  => 'error',
                'message' => sprintf(
                    __( 'PDF link check: could not fetch the homepage (%s).', 'devllo-accessibility-controls' ),
                    $response->get_error_message()
                ),
            ];
        }

        $body = wp_remote_retrieve_body( $response );

        preg_match_all( '/<a[^>]+href=["\'][^"\']*\.pdf["\'][^>]*>/i', $body, $matches );

        $pdf_count = count( $matches[0] );

        if ( $pdf_count > 0 ) {
            return [
                'status'  => 'info',
                'message' => sprintf(
                    __( 'PDF link check: %d link(s) to PDF files were detected. Ensure PDF documents are accessible or provide an accessible HTML alternative.', 'devllo-accessibility-controls' ),
                    $pdf_count
                ),
            ];
        }

        return [
            'status'  => 'ok',
            'message' => __( 'PDF link check: no links to PDF files were detected on the homepage.', 'devllo-accessibility-controls' ),
        ];
    }

    /**
     * Check for auto-playing media.
     *
     * @return array
     */
    protected function check_autoplay_media() {
        $url      = home_url( '/' );
        $response = wp_remote_get( $url, [ 'timeout' => 5 ] );

        if ( is_wp_error( $response ) ) {
            return [
                'status'  => 'error',
                'message' => sprintf(
                    __( 'Autoplay check: could not fetch the homepage (%s).', 'devllo-accessibility-controls' ),
                    $response->get_error_message()
                ),
            ];
        }

        $body = wp_remote_retrieve_body( $response );

        $autoplay_count = 0;

        preg_match_all( '/<(video|audio)[^>]+autoplay[^>]*>/i', $body, $matches );
        $autoplay_count += count( $matches[0] );

        if ( $autoplay_count > 0 ) {
            return [
                'status'  => 'warn',
                'message' => sprintf(
                    __( 'Autoplay check: %d auto-playing media element(s) were detected. Auto-playing media can be disorienting — ensure users can pause or stop it.', 'devllo-accessibility-controls' ),
                    $autoplay_count
                ),
            ];
        }

        return [
            'status'  => 'ok',
            'message' => __( 'Autoplay check: no auto-playing media was detected on the homepage.', 'devllo-accessibility-controls' ),
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
            <div class="notice notice-info inline">
                <p>
                    <?php esc_html_e( 'Dark mode is available to visitors but is experimental. It uses a CSS invert approach that works on most themes but may produce unexpected results on some designs. Consider testing it on your theme before promoting it to visitors.', 'devllo-accessibility-controls' ); ?>
                </p>
            </div>
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

        $items       = self::get_checklist_items();
        $statuses    = self::get_checklist_statuses();
        $results     = get_option( self::CHECK_RESULTS_OPTION_NAME, [] );

        $last_run    = isset( $results['last_run'] ) ? (int) $results['last_run'] : 0;
        $check_msgs  = isset( $results['checks'] ) && is_array( $results['checks'] ) ? $results['checks'] : [];

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

            <h2><?php esc_html_e( 'Basic automated checks', 'devllo-accessibility-controls' ); ?></h2>
            <p class="description">
                <?php esc_html_e( 'These checks are limited and informational. They are not a full audit and do not guarantee compliance.', 'devllo-accessibility-controls' ); ?>
            </p>

            <?php
            $run_url = wp_nonce_url(
                add_query_arg(
                    'da11y_run_checks',
                    1,
                    admin_url( 'options-general.php?page=da11y_accessibility_guidance' )
                ),
                'da11y_run_checks'
            );
            ?>
            <p>
                <a href="<?php echo esc_url( $run_url ); ?>" class="button button-secondary">
                    <?php esc_html_e( 'Run basic accessibility checks', 'devllo-accessibility-controls' ); ?>
                </a>
            </p>

            <?php if ( $last_run ) : ?>
                <p>
                    <?php
                    printf(
                        /* translators: %s: human-readable datetime */
                        esc_html__( 'Last run: %s', 'devllo-accessibility-controls' ),
                        esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last_run ) )
                    );
                    ?>
                </p>
                <?php if ( ! empty( $check_msgs ) ) : ?>
                    <ul>
                        <?php foreach ( $check_msgs as $check_id => $check ) : ?>
                            <li>
                                <?php echo esc_html( $check['message'] ?? '' ); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            <?php else : ?>
                <p class="description">
                    <?php esc_html_e( 'No basic checks have been run yet.', 'devllo-accessibility-controls' ); ?>
                </p>
            <?php endif; ?>

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

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render a generic feature toggle checkbox.
     *
     * @param array $args Field arguments.
     * @return void
     */
    public function render_feature_field( $args ) {
        $settings = self::get();
        $key      = $args['key'];
        $label    = $args['label'];
        $checked  = ! empty( $settings[ $key ] );
        ?>
        <label>
            <input
                type="checkbox"
                name="<?php echo esc_attr( self::OPTION_NAME ); ?>[<?php echo esc_attr( $key ); ?>]"
                value="1"
                <?php checked( $checked ); ?>
            />
            <?php echo esc_html( $label ); ?>
        </label>
        <?php
    }

    /**
     * Render the button label field.
     *
     * @return void
     */
    public function render_button_label_field() {
        $settings = self::get();
        ?>
        <input
            type="text"
            name="<?php echo esc_attr( self::OPTION_NAME ); ?>[button_label]"
            value="<?php echo esc_attr( $settings['button_label'] ); ?>"
            placeholder="<?php echo esc_attr__( 'Accessibility Options', 'devllo-accessibility-controls' ); ?>"
            class="regular-text"
        />
        <p class="description">
            <?php esc_html_e( 'Leave blank to use the default label.', 'devllo-accessibility-controls' ); ?>
        </p>
        <?php
    }

    /**
     * Render the button background colour field.
     *
     * @return void
     */
    public function render_button_bg_color_field() {
        $settings = self::get();
        ?>
        <input
            type="color"
            name="<?php echo esc_attr( self::OPTION_NAME ); ?>[button_bg_color]"
            value="<?php echo esc_attr( $settings['button_bg_color'] ); ?>"
        />
        <?php
    }

    /**
     * Render the button text colour field.
     *
     * @return void
     */
    public function render_button_text_color_field() {
        $settings = self::get();
        ?>
        <input
            type="color"
            name="<?php echo esc_attr( self::OPTION_NAME ); ?>[button_text_color]"
            value="<?php echo esc_attr( $settings['button_text_color'] ); ?>"
        />
        <?php
    }

    /**
     * Render the button size field.
     *
     * @return void
     */
    public function render_button_size_field() {
        $settings = self::get();
        $sizes    = [
            'small'  => __( 'Small', 'devllo-accessibility-controls' ),
            'medium' => __( 'Medium', 'devllo-accessibility-controls' ),
            'large'  => __( 'Large', 'devllo-accessibility-controls' ),
        ];
        ?>
        <select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[button_size]">
            <?php foreach ( $sizes as $value => $label ) : ?>
                <option
                    value="<?php echo esc_attr( $value ); ?>"
                    <?php selected( $settings['button_size'], $value ); ?>
                >
                    <?php echo esc_html( $label ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Render the button icon/style field.
     *
     * @return void
     */
    public function render_button_icon_field() {
        $settings = self::get();
        $styles   = [
            'text_only'    => __( 'Text only', 'devllo-accessibility-controls' ),
            'icon_only'    => __( 'Icon only', 'devllo-accessibility-controls' ),
            'icon_and_text'=> __( 'Icon and text', 'devllo-accessibility-controls' ),
        ];
        ?>
        <select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[button_icon]">
            <?php foreach ( $styles as $value => $label ) : ?>
                <option
                    value="<?php echo esc_attr( $value ); ?>"
                    <?php selected( $settings['button_icon'], $value ); ?>
                >
                    <?php echo esc_html( $label ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Render the accessibility statement section description.
     *
     * @return void
     */
    public function render_statement_section_description() {
        ?>
        <p>
            <?php esc_html_e( 'An accessibility statement is a public declaration of your commitment to accessibility. It tells visitors what you have done to make your site accessible and how to contact you if they encounter barriers.', 'devllo-accessibility-controls' ); ?>
        </p>
        <?php
        $settings = self::get();
        $page_id  = ! empty( $settings['accessibility_statement_page_id'] ) ? (int) $settings['accessibility_statement_page_id'] : 0;
        $page     = $page_id ? get_post( $page_id ) : null;

        if ( $page && $page->post_status !== 'trash' ) {
            $edit_url    = get_edit_post_link( $page_id );
            $preview_url = get_permalink( $page_id );
            ?>
            <p>
                <?php esc_html_e( 'A statement page has been generated.', 'devllo-accessibility-controls' ); ?>
                <a href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Edit page', 'devllo-accessibility-controls' ); ?></a>
                &nbsp;|&nbsp;
                <a href="<?php echo esc_url( $preview_url ); ?>" target="_blank"><?php esc_html_e( 'Preview', 'devllo-accessibility-controls' ); ?></a>
            </p>
            <?php
        }

        $generate_url = wp_nonce_url(
            add_query_arg(
                'da11y_generate_statement',
                1,
                admin_url( 'options-general.php?page=da11y_settings_page' )
            ),
            'da11y_generate_statement'
        );
        ?>
        <p>
            <a href="<?php echo esc_url( $generate_url ); ?>" class="button button-secondary">
                <?php esc_html_e( 'Generate accessibility statement page', 'devllo-accessibility-controls' ); ?>
            </a>
        </p>
        <?php
    }

    /**
     * Render the accessibility statement page selector.
     *
     * @return void
     */
    public function render_statement_page_field() {
        $settings = self::get();
        $page_id  = ! empty( $settings['accessibility_statement_page_id'] ) ? (int) $settings['accessibility_statement_page_id'] : 0;

        wp_dropdown_pages( [
            'name'              => esc_attr( self::OPTION_NAME ) . '[accessibility_statement_page_id]',
            'selected'          => $page_id,
            'show_option_none'  => __( '— Select a page —', 'devllo-accessibility-controls' ),
            'option_none_value' => 0,
        ] );

        $p = $page_id ? get_post( $page_id ) : null;
        if ( $p && $p->post_status === 'draft' ) {
            ?>
            <p class="description">
                <?php esc_html_e( 'This page is currently a draft. Publish it before linking to it from the widget.', 'devllo-accessibility-controls' ); ?>
            </p>
            <?php
        }
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
