<?php

function my_custom_plugin_activation_check() {
    if (!class_exists('SFWD_LMS')) { 
        deactivate_plugins('nft/nft.php'); 
        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
        add_action('admin_notices', 'my_custom_plugin_activation_error_notice');
    } 
}

function my_custom_plugin_activation_error_notice() {
    ?>
    <div class="notice notice-error is-dismissible">
        <p><?php _e('This plugin requires LearnDash to be installed and activated. Please install LearnDash and try again.', 'my-custom-plugin'); ?></p>
    </div>
    <?php
}

function activate_custom_plugin() {   
    create_custom_table(); 
}

function create_custom_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'course_completion_data';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        post_title varchar(255) NOT NULL,
        display_name varchar(255) NOT NULL,
        user_email varchar(255) NOT NULL,
        course_enrollment date NOT NULL,
        course_completion_date date NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    error_log('Table Created');
}


require_once plugin_dir_path( __FILE__ ) . '../admin/admin-functions.php';
require_once plugin_dir_path( __FILE__ ) . '../public/public-functions.php';