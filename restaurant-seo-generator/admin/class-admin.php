<?php
/**
 * Admin Interface Class
 *
 * Handles WordPress admin menu and pages
 */

if (!defined('ABSPATH')) {
    exit;
}

class RSG_Admin {

    private $db;

    public function __construct() {
        $this->db = new RSG_Database();

        // Add admin menu
        add_action('admin_menu', [$this, 'add_admin_menu']);

        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'SEO Generator',
            'SEO Generator',
            'manage_options',
            'restaurant-seo-generator',
            [$this, 'render_dashboard'],
            'dashicons-edit-page',
            30
        );

        add_submenu_page(
            'restaurant-seo-generator',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'restaurant-seo-generator',
            [$this, 'render_dashboard']
        );

        add_submenu_page(
            'restaurant-seo-generator',
            'Settings',
            'Settings',
            'manage_options',
            'rsg-settings',
            [$this, 'render_settings']
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_assets($hook) {
        // Only load on plugin pages
        if (strpos($hook, 'restaurant-seo-generator') === false && strpos($hook, 'rsg-') === false) {
            return;
        }

        // CSS
        wp_enqueue_style(
            'rsg-admin-css',
            RSG_PLUGIN_URL . 'admin/css/admin.css',
            [],
            RSG_VERSION
        );

        // JavaScript
        wp_enqueue_script(
            'rsg-admin-js',
            RSG_PLUGIN_URL . 'admin/js/admin.js',
            ['jquery'],
            RSG_VERSION,
            true
        );

        // Localize script
        wp_localize_script('rsg-admin-js', 'rsgAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rsg_nonce'),
        ]);
    }

    /**
     * Render dashboard
     */
    public function render_dashboard() {
        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'sites';
        $site_id = isset($_GET['site_id']) ? intval($_GET['site_id']) : 0;

        ?>
        <div class="wrap rsg-wrap">
            <h1>Restaurant SEO Generator</h1>

            <?php
            switch ($tab) {
                case 'sites':
                    $this->render_sites_tab();
                    break;

                case 'new-site':
                    include RSG_PLUGIN_DIR . 'admin/views/site-profile-form.php';
                    break;

                case 'generate':
                    if ($site_id) {
                        include RSG_PLUGIN_DIR . 'admin/views/keyword-research.php';
                    } else {
                        echo '<p>Please select a site first.</p>';
                    }
                    break;

                case 'library':
                    if ($site_id) {
                        include RSG_PLUGIN_DIR . 'admin/views/content-library.php';
                    } else {
                        echo '<p>Please select a site first.</p>';
                    }
                    break;

                default:
                    $this->render_sites_tab();
            }
            ?>
        </div>
        <?php
    }

    /**
     * Render sites tab
     */
    private function render_sites_tab() {
        $sites = $this->db->get_all_sites();
        ?>
        <div class="rsg-sites-list">
            <h2>Your Sites</h2>

            <?php if (empty($sites)): ?>
                <div class="rsg-empty-state">
                    <p>You haven't created any sites yet. Let's get started!</p>
                    <a href="<?php echo admin_url('admin.php?page=restaurant-seo-generator&tab=new-site'); ?>" class="button button-primary button-large">
                        Create Your First Site
                    </a>
                </div>
            <?php else: ?>
                <div class="rsg-sites-grid">
                    <?php foreach ($sites as $site): ?>
                        <div class="rsg-site-card">
                            <div class="rsg-site-icon">
                                <?php echo $this->get_business_icon($site['business_type']); ?>
                            </div>
                            <div class="rsg-site-info">
                                <h3><?php echo esc_html($site['site_name']); ?></h3>
                                <p class="rsg-site-meta">
                                    <?php echo esc_html($site['business_type']); ?> • <?php echo esc_html($site['location']); ?>
                                </p>
                                <p class="rsg-site-pages">
                                    <?php echo intval($site['page_count']); ?> pages generated
                                </p>
                            </div>
                            <div class="rsg-site-actions">
                                <a href="<?php echo admin_url('admin.php?page=restaurant-seo-generator&tab=library&site_id=' . $site['id']); ?>" class="button">
                                    View Pages
                                </a>
                                <a href="<?php echo admin_url('admin.php?page=restaurant-seo-generator&tab=generate&site_id=' . $site['id']); ?>" class="button button-primary">
                                    Generate Content
                                </a>
                                <button class="button button-link rsg-delete-site" data-site-id="<?php echo $site['id']; ?>">
                                    Delete
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <p>
                    <a href="<?php echo admin_url('admin.php?page=restaurant-seo-generator&tab=new-site'); ?>" class="button button-primary">
                        + Add New Site
                    </a>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render settings page
     */
    public function render_settings() {
        // Save settings
        if (isset($_POST['rsg_save_settings']) && check_admin_referer('rsg_settings', 'rsg_settings_nonce')) {
            $api_key = sanitize_text_field($_POST['rsg_claude_api_key']);
            $model = sanitize_text_field($_POST['rsg_default_model']);

            update_option('rsg_claude_api_key', $api_key);
            update_option('rsg_default_model', $model);

            echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
        }

        $api_key = get_option('rsg_claude_api_key', '');
        $model = get_option('rsg_default_model', 'claude-sonnet-4-20250514');

        include RSG_PLUGIN_DIR . 'admin/views/settings.php';
    }

    /**
     * Get business icon emoji
     */
    private function get_business_icon($business_type) {
        $type = strtolower($business_type);

        if (strpos($type, 'restaurant') !== false || strpos($type, 'pizza') !== false) return '🍕';
        if (strpos($type, 'bar') !== false) return '🍸';
        if (strpos($type, 'cafe') !== false || strpos($type, 'coffee') !== false) return '☕';
        if (strpos($type, 'bakery') !== false) return '🥐';
        if (strpos($type, 'plumb') !== false) return '🔧';
        if (strpos($type, 'electric') !== false) return '⚡';
        if (strpos($type, 'hvac') !== false) return '🌡️';
        if (strpos($type, 'landscape') !== false) return '🌳';
        if (strpos($type, 'dentist') !== false || strpos($type, 'dental') !== false) return '🦷';
        if (strpos($type, 'chiropract') !== false) return '💆';
        if (strpos($type, 'yoga') !== false) return '🧘';
        if (strpos($type, 'gym') !== false || strpos($type, 'fitness') !== false) return '💪';
        if (strpos($type, 'salon') !== false || strpos($type, 'hair') !== false) return '💇';
        if (strpos($type, 'barber') !== false) return '✂️';
        if (strpos($type, 'lawyer') !== false || strpos($type, 'attorney') !== false) return '⚖️';
        if (strpos($type, 'account') !== false) return '💼';

        return '🏢';
    }
}
