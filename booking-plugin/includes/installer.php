<?php
if(!defined('ABSPATH')) exit;

function bp_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $slots_table = $wpdb->prefix . 'booking_slots';
    $reservations_table = $wpdb->prefix . 'booking_reservations';

    $sql = "
    CREATE TABLE IF NOT EXISTS $slots_table (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        slot_datetime DATETIME NOT NULL,
        slot_status VARCHAR(20) NOT NULL DEFAULT 'available',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;

    CREATE TABLE IF NOT EXISTS $reservations_table (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        slot_id BIGINT UNSIGNED NOT NULL,
        user_email VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (slot_id) REFERENCES $slots_table(id) ON DELETE CASCADE
    ) $charset_collate;
    ";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
