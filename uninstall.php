<?php

// Prevent direct access.
defined( 'ABSPATH' ) or exit;

// if uninstall.php is not called by WordPress, die
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

// look all custom post types inside /includes/models/custom/ and delete them from database
$custom_post_types = glob(plugin_dir_path(__FILE__) . '/includes/models/custom/*.php');

foreach ( $custom_post_types as $custom_post_type ) {
    $custom_post_type = basename( $custom_post_type, '.php' );
    $custom_post_type = str_replace( '-', '_', $custom_post_type );
    $post_type = strtolower( $custom_post_type);

    if ( !post_type_exists( $post_type ) ) {
        continue;
    }

    // Delete all posts of this post type
    global $wpdb;
    $wpdb->delete($wpdb->prefix . 'posts', ['post_type' => $post_type]);
}