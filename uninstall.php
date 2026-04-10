<?php
// Only run when WordPress is uninstalling the plugin.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

delete_option( 'da11y_settings' );
delete_option( 'da11y_checklist' );
delete_option( 'da11y_check_results' );