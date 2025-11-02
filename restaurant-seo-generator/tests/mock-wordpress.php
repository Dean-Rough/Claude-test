<?php
/**
 * Mock WordPress Functions for Testing
 *
 * This file provides mock WordPress functions so we can test
 * plugin functionality without a full WordPress installation.
 */

// Prevent direct access
if (!defined('TESTING_MODE')) {
    define('TESTING_MODE', true);
}

// Define WordPress constants
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

if (!defined('WPINC')) {
    define('WPINC', 'wp-includes');
}

/**
 * Mock WordPress Database Class
 */
class wpdb {
    public $prefix = 'wp_';
    public $last_error = '';
    public $insert_id = 1;
    public $queries = [];

    private $mock_data = [];

    public function prepare($query, ...$args) {
        // Simple prepare - just replace %s and %d
        $query = preg_replace('/%s/', "'%s'", $query);
        return vsprintf($query, $args);
    }

    public function get_results($query, $output = OBJECT) {
        $this->queries[] = $query;

        // Mock responses based on query
        if (strpos($query, 'wp_rsg_sites') !== false) {
            return $this->mock_data['sites'] ?? [];
        }

        return [];
    }

    public function get_row($query, $output = OBJECT) {
        $results = $this->get_results($query, $output);
        return $results[0] ?? null;
    }

    public function get_var($query) {
        $this->queries[] = $query;
        return null;
    }

    public function insert($table, $data, $format = null) {
        $this->queries[] = "INSERT INTO $table";
        $this->insert_id++;
        return true;
    }

    public function update($table, $data, $where, $format = null, $where_format = null) {
        $this->queries[] = "UPDATE $table";
        return true;
    }

    public function delete($table, $where, $where_format = null) {
        $this->queries[] = "DELETE FROM $table";
        return true;
    }

    public function get_charset_collate() {
        return 'utf8mb4';
    }

    public function set_mock_data($key, $data) {
        $this->mock_data[$key] = $data;
    }
}

// Global wpdb instance
$wpdb = new wpdb();

/**
 * Mock WordPress Functions
 */

function add_action($hook, $callback, $priority = 10, $args = 1) {
    // Mock - just return true
    return true;
}

function add_filter($hook, $callback, $priority = 10, $args = 1) {
    return true;
}

function register_activation_hook($file, $callback) {
    // For testing, we can call the callback
    if (defined('RUN_ACTIVATION_HOOK') && RUN_ACTIVATION_HOOK) {
        call_user_func($callback);
    }
    return true;
}

function register_deactivation_hook($file, $callback) {
    return true;
}

function add_menu_page($page_title, $menu_title, $capability, $menu_slug, $callback, $icon = '', $position = null) {
    return true;
}

function add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $callback = '') {
    return true;
}

function wp_enqueue_style($handle, $src = '', $deps = [], $ver = false, $media = 'all') {
    return true;
}

function wp_enqueue_script($handle, $src = '', $deps = [], $ver = false, $in_footer = false) {
    return true;
}

function wp_localize_script($handle, $object_name, $l10n) {
    return true;
}

function admin_url($path = '', $scheme = 'admin') {
    return 'http://example.com/wp-admin/' . $path;
}

function home_url($path = '', $scheme = null) {
    return 'http://example.com/' . $path;
}

function plugin_dir_path($file) {
    return dirname($file) . '/';
}

function plugin_dir_url($file) {
    return 'http://example.com/wp-content/plugins/' . basename(dirname($file)) . '/';
}

function get_option($option, $default = false) {
    static $options = [];
    return $options[$option] ?? $default;
}

function add_option($option, $value = '', $deprecated = '', $autoload = 'yes') {
    static $options = [];
    $options[$option] = $value;
    return true;
}

function update_option($option, $value, $autoload = null) {
    static $options = [];
    $options[$option] = $value;
    return true;
}

function delete_option($option) {
    return true;
}

function get_transient($transient) {
    static $transients = [];
    return $transients[$transient] ?? false;
}

function set_transient($transient, $value, $expiration = 0) {
    static $transients = [];
    $transients[$transient] = $value;
    return true;
}

function delete_transient($transient) {
    return true;
}

function sanitize_text_field($str) {
    return strip_tags(trim($str));
}

function sanitize_textarea_field($str) {
    return sanitize_text_field($str);
}

function esc_html($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function esc_attr($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function esc_url($url) {
    return filter_var($url, FILTER_SANITIZE_URL);
}

function esc_url_raw($url) {
    return esc_url($url);
}

function esc_js($text) {
    return addslashes($text);
}

function wp_nonce_field($action, $name = '_wpnonce', $referer = true, $echo = true) {
    $nonce = '<input type="hidden" name="' . $name . '" value="test_nonce" />';
    if ($echo) {
        echo $nonce;
    }
    return $nonce;
}

function wp_create_nonce($action) {
    return 'test_nonce_' . $action;
}

function check_admin_referer($action, $query_arg = '_wpnonce') {
    return true;
}

function wp_remote_get($url, $args = []) {
    // Mock HTTP response
    return [
        'response' => ['code' => 200],
        'body' => json_encode(['data' => 'test'])
    ];
}

function wp_remote_post($url, $args = []) {
    // Mock HTTP response
    return [
        'response' => ['code' => 200],
        'body' => json_encode(['content' => [['text' => 'Mock response']]])
    ];
}

function wp_remote_retrieve_response_code($response) {
    return $response['response']['code'] ?? 500;
}

function wp_remote_retrieve_body($response) {
    return $response['body'] ?? '';
}

function is_wp_error($thing) {
    return false;
}

function wp_send_json_success($data = null) {
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

function wp_send_json_error($data = null) {
    echo json_encode(['success' => false, 'data' => $data]);
    exit;
}

function wpautop($text) {
    return '<p>' . str_replace("\n\n", "</p>\n<p>", $text) . '</p>';
}

function selected($selected, $current = true, $echo = true) {
    $result = $selected == $current ? ' selected="selected"' : '';
    if ($echo) {
        echo $result;
    }
    return $result;
}

function wp_mkdir_p($target) {
    return mkdir($target, 0755, true);
}

// Define WordPress constants
if (!defined('OBJECT')) {
    define('OBJECT', 'OBJECT');
}
if (!defined('ARRAY_A')) {
    define('ARRAY_A', 'ARRAY_A');
}

/**
 * Test Helper Functions
 */

function mock_wordpress_environment() {
    global $wpdb;
    return $wpdb;
}

function reset_mock_data() {
    global $wpdb;
    $wpdb = new wpdb();
}

function get_mock_queries() {
    global $wpdb;
    return $wpdb->queries;
}

echo "✅ WordPress mock environment loaded\n";
