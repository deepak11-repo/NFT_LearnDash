<?php
/*
Plugin Name: LearnDash Certificate NFT Generator
Plugin URI: https://mywebsite.com/learndash-certificate-nft-generator
Description: This plugin enables the generation of NFTs for certificates issued by LearnDash upon course completion.
Version: 1.0
Author: Deepak Naidu
Author URI: https://mywebsite.com
License: GPL2
*/


/**
 * Registers the activation hook for the plugin.
 * This function will be called when the plugin is activated.
 */
register_activation_hook(__FILE__, 'activate_custom_plugin');


/**
 * Registers the activation hook callback.
 * This will call the my_custom_plugin_activation_check() function
 * when the plugin is activated.
 */
add_action('admin_init', 'my_custom_plugin_activation_check');


/**
 * Includes the activation script that handles plugin activation tasks.
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/activation/activation.php';


add_action('learndash_course_completed', 'custom_function_on_course_completion'); 
function custom_function_on_course_completion($data) {
    error_log('custom_function_on_course_completion called');
    global $wpdb;
    $table_name = $wpdb->prefix . 'course_completion_data';
    $post_title = $data['course']->post_title;
    $display_name = $data['user']->data->display_name;
    $user_email = $data['user']->data->user_email;
    $course_enrollment_timestamp = strtotime($data['user']->data->user_registered);
    $course_enrollment = date('Y-m-d', $course_enrollment_timestamp);
    $course_completion_date = date('Y-m-d'); 
    error_log("Course completion data - Post Title: $post_title, Display Name: $display_name, User Email: $user_email, Course Enrollment: $course_enrollment, Completion Date: $course_completion_date");
    $wpdb->insert(
        $table_name,
        array(
            'post_title' => $post_title,
            'display_name' => $display_name,
            'user_email' => $user_email,
            'course_enrollment' => $course_enrollment,
            'course_completion_date' => $course_completion_date,
        )
    );

}