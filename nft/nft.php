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
