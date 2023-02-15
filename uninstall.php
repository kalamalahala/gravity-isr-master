<?php

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

delete_option( 'preset_appt_days' );

delete_option( 'ci_form_id' );

delete_option( 'wcn_form_id' );

?>