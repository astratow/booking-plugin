<?php
if(!defined('ABSPATH')) exit;

add_action('rest_api_init', function() {
    register_rest_route('booking/v1', '/slots', array(
        'methods' => 'GET',
        'callback' => 'bp_get_slots',
        'permission_callback' => '__return_true',
    ));
});

function bp_get_slots() {
    global $wpdb;
    $table = $wpdb->prefix . 'booking_slots';
    $results = $wpdb->get_results("SELECT * FROM $table WHERE slot_status = 'available' ORDER BY slot_datetime ASC");
    return rest_ensure_response($results);
}
