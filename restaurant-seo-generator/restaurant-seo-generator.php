<?php
/**
 * Plugin Name: Restaurant SEO Generator
 * Plugin URI: https://github.com/yourusername/restaurant-seo-generator
 * Description: Generate SEO-optimized content for local business websites using Claude AI. Simple keyword research → content generation → HTML output.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: restaurant-seo-generator
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Plugin version
define('RSG_VERSION', '1.0.0');

// Plugin directory path
define('RSG_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Plugin directory URL
define('RSG_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Activation hook - Create database tables
 */
register_activation_hook(__FILE__, 'rsg_activate');

function rsg_activate() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    // Table for site profiles
    $table_sites = $wpdb->prefix . 'rsg_sites';
    $sql_sites = "CREATE TABLE IF NOT EXISTS $table_sites (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        site_name varchar(255) NOT NULL,
        industry varchar(100) NOT NULL,
        business_type varchar(100) NOT NULL,
        location varchar(255) NOT NULL,
        service_area_type varchar(50) DEFAULT 'single_location',
        service_area_radius int(11) DEFAULT 15,
        target_audience text,
        brand_voice varchar(50) NOT NULL,
        unique_selling_point text,
        key_services text,
        certifications varchar(255),
        writing_sample text,
        profile_data longtext,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY industry (industry),
        KEY created_at (created_at)
    ) $charset_collate;";

    // Table for generated pages
    $table_pages = $wpdb->prefix . 'rsg_pages';
    $sql_pages = "CREATE TABLE IF NOT EXISTS $table_pages (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        site_id bigint(20) UNSIGNED NOT NULL,
        title varchar(255) NOT NULL,
        slug varchar(255) NOT NULL,
        primary_keyword varchar(255) NOT NULL,
        secondary_keywords text,
        intent varchar(50) NOT NULL,
        page_type varchar(50) DEFAULT 'standard',
        target_words int(11) DEFAULT 1000,
        priority varchar(20) DEFAULT 'medium',
        content_html longtext,
        content_json longtext,
        meta_title varchar(255),
        meta_description text,
        schema_markup longtext,
        status varchar(20) DEFAULT 'draft',
        word_count int(11) DEFAULT 0,
        seo_score int(11) DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY site_id (site_id),
        KEY status (status),
        KEY created_at (created_at),
        UNIQUE KEY site_slug (site_id, slug)
    ) $charset_collate;";

    // Table for keyword research cache
    $table_keywords = $wpdb->prefix . 'rsg_keywords';
    $sql_keywords = "CREATE TABLE IF NOT EXISTS $table_keywords (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        site_id bigint(20) UNSIGNED DEFAULT NULL,
        keyword varchar(255) NOT NULL,
        intent varchar(50),
        confidence float DEFAULT 0.5,
        source varchar(50) NOT NULL,
        search_volume int(11) DEFAULT 0,
        competition varchar(20),
        related_keywords text,
        cached_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY site_id (site_id),
        KEY keyword (keyword),
        KEY intent (intent),
        KEY cached_at (cached_at)
    ) $charset_collate;";

    // Table for competitor analysis
    $table_competitors = $wpdb->prefix . 'rsg_competitors';
    $sql_competitors = "CREATE TABLE IF NOT EXISTS $table_competitors (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        site_id bigint(20) UNSIGNED NOT NULL,
        url varchar(500) NOT NULL,
        title varchar(255),
        meta_description text,
        word_count int(11) DEFAULT 0,
        h1_count int(11) DEFAULT 0,
        h2_count int(11) DEFAULT 0,
        h3_count int(11) DEFAULT 0,
        h2_headings text,
        image_count int(11) DEFAULT 0,
        has_schema tinyint(1) DEFAULT 0,
        internal_links int(11) DEFAULT 0,
        analysis_data longtext,
        analyzed_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY site_id (site_id),
        KEY analyzed_at (analyzed_at)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_sites);
    dbDelta($sql_pages);
    dbDelta($sql_keywords);
    dbDelta($sql_competitors);

    // Set default options
    add_option('rsg_version', RSG_VERSION);
    add_option('rsg_claude_api_key', '');
    add_option('rsg_default_model', 'claude-sonnet-4-20250514');
}

/**
 * Deactivation hook
 */
register_deactivation_hook(__FILE__, 'rsg_deactivate');

function rsg_deactivate() {
    // Clean up transients
    delete_transient('rsg_generation_progress');
}

/**
 * Load plugin classes
 */
require_once RSG_PLUGIN_DIR . 'includes/class-database.php';
require_once RSG_PLUGIN_DIR . 'includes/class-claude-api.php';
require_once RSG_PLUGIN_DIR . 'includes/class-keyword-research.php';
require_once RSG_PLUGIN_DIR . 'includes/class-content-generator.php';
require_once RSG_PLUGIN_DIR . 'includes/class-schema-generator.php';

/**
 * Initialize admin interface
 */
if (is_admin()) {
    require_once RSG_PLUGIN_DIR . 'admin/class-admin.php';
    new RSG_Admin();
}

/**
 * AJAX handlers
 */
add_action('wp_ajax_rsg_test_api_key', 'rsg_ajax_test_api_key');
add_action('wp_ajax_rsg_save_site_profile', 'rsg_ajax_save_site_profile');
add_action('wp_ajax_rsg_delete_site', 'rsg_ajax_delete_site');
add_action('wp_ajax_rsg_generate_keywords', 'rsg_ajax_generate_keywords');
add_action('wp_ajax_rsg_classify_intent', 'rsg_ajax_classify_intent');
add_action('wp_ajax_rsg_analyze_competitor', 'rsg_ajax_analyze_competitor');
add_action('wp_ajax_rsg_generate_architecture', 'rsg_ajax_generate_architecture');
add_action('wp_ajax_rsg_generate_content', 'rsg_ajax_generate_content');
add_action('wp_ajax_rsg_regenerate_page', 'rsg_ajax_regenerate_page');
add_action('wp_ajax_rsg_delete_page', 'rsg_ajax_delete_page');
add_action('wp_ajax_rsg_get_generation_progress', 'rsg_ajax_get_generation_progress');

function rsg_ajax_test_api_key() {
    check_ajax_referer('rsg_nonce', 'nonce');

    $api_key = sanitize_text_field($_POST['api_key']);

    try {
        $api = new RSG_Claude_API($api_key);
        $result = $api->test_connection();

        wp_send_json_success($result);
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}

function rsg_ajax_save_site_profile() {
    check_ajax_referer('rsg_nonce', 'nonce');

    $db = new RSG_Database();

    $data = [
        'site_name' => sanitize_text_field($_POST['site_name']),
        'industry' => sanitize_text_field($_POST['industry']),
        'business_type' => sanitize_text_field($_POST['business_type']),
        'location' => sanitize_text_field($_POST['location']),
        'service_area_type' => sanitize_text_field($_POST['service_area_type']),
        'service_area_radius' => intval($_POST['service_area_radius']),
        'target_audience' => sanitize_textarea_field($_POST['target_audience']),
        'brand_voice' => sanitize_text_field($_POST['brand_voice']),
        'unique_selling_point' => sanitize_textarea_field($_POST['unique_selling_point']),
        'key_services' => sanitize_textarea_field($_POST['key_services']),
        'certifications' => sanitize_text_field($_POST['certifications']),
        'writing_sample' => sanitize_textarea_field($_POST['writing_sample']),
    ];

    $site_id = $db->save_site_profile($data);

    if ($site_id) {
        wp_send_json_success(['site_id' => $site_id, 'message' => 'Site profile saved successfully!']);
    } else {
        wp_send_json_error(['message' => 'Failed to save site profile.']);
    }
}

function rsg_ajax_delete_site() {
    check_ajax_referer('rsg_nonce', 'nonce');

    $site_id = intval($_POST['site_id']);
    $db = new RSG_Database();

    if ($db->delete_site($site_id)) {
        wp_send_json_success(['message' => 'Site deleted successfully!']);
    } else {
        wp_send_json_error(['message' => 'Failed to delete site.']);
    }
}

function rsg_ajax_generate_keywords() {
    check_ajax_referer('rsg_nonce', 'nonce');

    $seed_keywords = array_map('sanitize_text_field', $_POST['seed_keywords']);
    $location = sanitize_text_field($_POST['location']);
    $auto_expand = isset($_POST['auto_expand']) ? (bool)$_POST['auto_expand'] : true;

    $kr = new RSG_Keyword_Research();
    $keywords = $kr->generate_keywords($seed_keywords, $location, $auto_expand);

    wp_send_json_success(['keywords' => $keywords]);
}

function rsg_ajax_classify_intent() {
    check_ajax_referer('rsg_nonce', 'nonce');

    $keywords = array_map('sanitize_text_field', $_POST['keywords']);
    $site_name = sanitize_text_field($_POST['site_name']);

    $kr = new RSG_Keyword_Research();
    $classified = $kr->classify_keywords($keywords, $site_name);

    wp_send_json_success(['keywords' => $classified]);
}

function rsg_ajax_analyze_competitor() {
    check_ajax_referer('rsg_nonce', 'nonce');

    $url = esc_url_raw($_POST['url']);
    $site_id = intval($_POST['site_id']);

    $kr = new RSG_Keyword_Research();
    $analysis = $kr->analyze_competitor($url);

    if (isset($analysis['error'])) {
        wp_send_json_error($analysis);
    } else {
        // Save to database
        $db = new RSG_Database();
        $db->save_competitor_analysis($site_id, $analysis);

        wp_send_json_success($analysis);
    }
}

function rsg_ajax_generate_architecture() {
    check_ajax_referer('rsg_nonce', 'nonce');

    $site_id = intval($_POST['site_id']);
    $keywords = json_decode(stripslashes($_POST['keywords']), true);
    $competitor_urls = isset($_POST['competitor_urls']) ? json_decode(stripslashes($_POST['competitor_urls']), true) : [];

    try {
        $generator = new RSG_Content_Generator();
        $architecture = $generator->generate_site_architecture($site_id, $keywords, $competitor_urls);

        wp_send_json_success($architecture);
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}

function rsg_ajax_generate_content() {
    check_ajax_referer('rsg_nonce', 'nonce');

    $site_id = intval($_POST['site_id']);
    $pages = json_decode(stripslashes($_POST['pages']), true);

    try {
        $generator = new RSG_Content_Generator();
        $result = $generator->generate_all_pages($site_id, $pages);

        wp_send_json_success($result);
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}

function rsg_ajax_regenerate_page() {
    check_ajax_referer('rsg_nonce', 'nonce');

    $page_id = intval($_POST['page_id']);

    try {
        $generator = new RSG_Content_Generator();
        $result = $generator->regenerate_page($page_id);

        wp_send_json_success($result);
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}

function rsg_ajax_delete_page() {
    check_ajax_referer('rsg_nonce', 'nonce');

    $page_id = intval($_POST['page_id']);
    $db = new RSG_Database();

    if ($db->delete_page($page_id)) {
        wp_send_json_success(['message' => 'Page deleted successfully!']);
    } else {
        wp_send_json_error(['message' => 'Failed to delete page.']);
    }
}

function rsg_ajax_get_generation_progress() {
    check_ajax_referer('rsg_nonce', 'nonce');

    $progress = get_transient('rsg_generation_progress');

    if ($progress) {
        wp_send_json_success($progress);
    } else {
        wp_send_json_success(['current' => 0, 'total' => 0, 'page' => '', 'complete' => true]);
    }
}
