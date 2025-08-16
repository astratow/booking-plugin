<?php
/**
 * Plugin Name: Booking Plugin
 * Description: Plugin to book a meeting 
 * Version: 0.1
 * Author: Artur
 */
if(!defined('ABSPATH')) exit;

// basic autoload
foreach(glob(plugin_dir_path(__FILE__) . '/includes/*.php') as $file) {
    require_once $file;
}

// activation hook
register_activation_hook(__FILE__, 'bp_install');
// deactivation hook
register_deactivation_hook(__FILE__, 'bp_deactivate');

function bp_install() {
    bp_create_tables();
}

function bp_deactivate() {
    // cleanup
}

// Shortcode
add_shortcode('booking_button', function() {
    $slots_url = esc_url( rest_url('booking/v1/slots') );
    ob_start(); ?>
    <button id="bp-book-btn">Book Meeting</button>
    <div id="bp-slots"></div>
    <script>
        window.bpSlotsUrl = "<?php echo $slots_url; ?>";
    </script>
    <script src="<?php echo plugin_dir_url(__FILE__); ?>assets/booking.js"></script>
    <?php
    return ob_get_clean();
});
