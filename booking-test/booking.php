<?php
/**
 * Plugin Name: Booking Test
 * Description: Minimalny, jednoplikowy test rezerwacji + REST
 * Version: 0.1.0
 * Author: —
 */
if (!defined('ABSPATH')) exit;

/** AKTYWACJA: tabele + seed */
register_activation_hook(__FILE__, function () {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $slots_table = $wpdb->prefix . 'booking_slots';

    // Tabela slotów (prosto)
    $sql = "CREATE TABLE {$slots_table} (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        slot_datetime DATETIME NOT NULL,
        slot_status VARCHAR(20) NOT NULL DEFAULT 'available',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    // Seed przykładowych slotów (tylko jeśli pusto)
    $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$slots_table}");
    if ($count === 0) {
        $times = [
            gmdate('Y-m-d H:i:s', time() + 24 * 3600),
            gmdate('Y-m-d H:i:s', time() + 26 * 3600),
            gmdate('Y-m-d H:i:s', time() + 48 * 3600),
        ];
        foreach ($times as $t) {
            $wpdb->insert($slots_table, [
                'slot_datetime' => $t,
                'slot_status'   => 'available',
            ]);
        }
    }
});

/** REST: GET /wp-json/booking/v1/slots */
add_action('rest_api_init', function () {
    register_rest_route('booking/v1', '/slots', [
        'methods'             => 'GET',
        'callback'            => 'booking_test_get_slots',
        'permission_callback' => '__return_true',
    ]);
});

function booking_test_get_slots(\WP_REST_Request $req) {
    global $wpdb;
    $table = $wpdb->prefix . 'booking_slots';
    $rows  = $wpdb->get_results("SELECT id, slot_datetime, slot_status FROM {$table} WHERE slot_status='available' ORDER BY slot_datetime ASC");
    return rest_ensure_response($rows);
}

/** Shortcode: [booking_button] — przycisk + fetch slotów */
add_shortcode('booking_button', function () {
    $slots_url = esc_url(rest_url('booking/v1/slots'));
    ob_start(); ?>
    <button id="bp-book-btn">Umów spotkanie</button>
    <div id="bp-slots" style="margin-top:8px;"></div>
    <script>
    (function () {
        var btn = document.getElementById('bp-book-btn');
        var out = document.getElementById('bp-slots');
        var url = "<?php echo $slots_url; ?>";

        btn.addEventListener('click', function () {
            fetch(url, { credentials: 'same-origin' })
            .then(function (res) { return res.text(); })
            .then(function (text) {
                try {
                    var data = JSON.parse(text);
                    if (!Array.isArray(data)) { out.textContent = 'Niepoprawny JSON.'; return; }
                    out.innerHTML = data.map(function (s) {
                        return '<div>' + s.slot_datetime + '</div>';
                    }).join('');
                } catch (e) {
                    console.error('Serwer nie zwrócił JSON. Odpowiedź:', text);
                    out.textContent = 'Błąd: serwer nie zwrócił JSON (zobacz konsolę).';
                }
            })
            .catch(function (err) {
                console.error(err);
                out.textContent = 'Błąd sieci.';
            });
        });
    })();
    </script>
    <?php
    return ob_get_clean();
});