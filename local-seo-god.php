<?php
/**
 * Plugin Name: Local SEO God
 * Plugin URI: https://localseo.god/
 * Description: Create unlimited location-specific landing pages for local businesses using AI and dynamic templates.
 * Version: 1.1.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: SEO God
 * Author URI: https://localseo.god/
 * License: GPL2
 * Text Domain: local-seo-god
 * Domain Path: /languages
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('LOCAL_SEO_GOD_VERSION', '1.1.0');
define('LOCAL_SEO_GOD_PATH', plugin_dir_path(__FILE__));
define('LOCAL_SEO_GOD_URL', plugin_dir_url(__FILE__));
define('LOCAL_SEO_GOD_FILE', __FILE__);
define('LOCAL_SEO_GOD_REPO_TYPE', 'github');
define('LOCAL_SEO_GOD_REPO_URL', 'https://github.com/tomfask/local-seo-god');
define('LOCAL_SEO_GOD_BASENAME', plugin_basename(__FILE__));

// Load text domain for internationalization
add_action('plugins_loaded', 'local_seo_god_load_textdomain');
function local_seo_god_load_textdomain() {
    load_plugin_textdomain('local-seo-god', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

// Include required files
require_once plugin_dir_path(__FILE__) . 'includes/class-ai-handler.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-update-checker.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-wikipedia-links.php';

/**
 * Create a logger class
 */
class LocalSeoGod_Logger {
    private static $instance = null;
    
    private function __construct() {
        // Private constructor to prevent direct object creation
    }
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Log a message to the error log
     */
    public function log($message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Local SEO God: ' . $message);
        }
    }
}

/**
 * Main plugin class
 */
class LocalSeoGod {
    // Plugin variables
    private $version = '1.1.0';
    private $plugin_name = 'Local SEO God';
    private $table_name = 'local_seo_god';
    private $base_name = 'local_seo_god';
    private $logger;
    
    // Database fields for word replacement
    private $fields = array(
        'original'     => 'Original',
        'replacement'  => 'Replacement',
        'in_posts'     => 'Posts',
        'in_pages'     => 'Pages',
        'in_titles'    => 'Titles',
        'in_comments'  => 'Comments',
        'in_sensitive' => 'Case Insensitive',
        'in_wordonly'  => 'Whole Word',
        'in_regex'     => 'Regex'
    );

    /**
     * Constructor
     */
    public function __construct() {
        $this->base_name = 'local_seo_god';
        $this->logger = LocalSeoGod_Logger::get_instance();
        
        // Hook into WordPress
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Register AJAX handlers
        $this->register_ajax_handlers();
        
        // Setup shortcodes
        add_shortcode('local-seo-god-places-links', array($this, 'placeslinks_shortcode'));
        
        // Initialize admin API
        $this->init_admin_api();
        
        // Register activation hook
        register_activation_hook(__FILE__, array($this, 'activate'));
    }

    /**
     * Plugin activation
     */
    public function activate() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . $this->table_name;
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            original TEXT NOT NULL,
            replacement TEXT NOT NULL,
            in_posts VARCHAR(3) DEFAULT 'yes' NOT NULL,
            in_comments VARCHAR(3) DEFAULT '0' NOT NULL,
            in_pages VARCHAR(3) DEFAULT 'yes' NOT NULL,
            in_titles VARCHAR(3) DEFAULT '0' NOT NULL,
            in_sensitive VARCHAR(3) DEFAULT 'yes' NOT NULL,
            in_wordonly VARCHAR(3) DEFAULT '0' NOT NULL,
            in_regex VARCHAR(3) DEFAULT '0' NOT NULL,
            UNIQUE KEY id (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Create tables for page templates and created pages
        $templates_table = $wpdb->prefix . 'lsg_templates';
        $sql = "CREATE TABLE $templates_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            template_name VARCHAR(255) NOT NULL,
            template_post_id bigint(20) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY id (id)
        ) $charset_collate;";
        dbDelta($sql);
        
        $pages_table = $wpdb->prefix . 'lsg_pages';
        $sql = "CREATE TABLE $pages_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            template_id mediumint(9) NOT NULL,
            page_post_id bigint(20) NOT NULL,
            replacement_data LONGTEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            last_updated DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY id (id),
            KEY template_id (template_id),
            KEY page_post_id (page_post_id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Add random secret key for security
        if (!get_option('local_seo_god_secret')) {
            add_option('local_seo_god_secret', wp_generate_password(32, false));
        }
        
        // Add version option
        add_option('local_seo_god_version', $this->version);
    }

    /**
     * Add admin menu
     */
    public function admin_menu() {
        // Main menu
        add_menu_page(
            $this->plugin_name,
            $this->plugin_name,
            'manage_options',
            $this->base_name,
            array($this, 'admin_page_dashboard'),
            'dashicons-chart-area',
            25
        );

        // Submenu - dashboard
        add_submenu_page(
            $this->base_name,
            $this->plugin_name . ' - Dashboard',
            'Dashboard',
            'manage_options',
            $this->base_name,
            array($this, 'admin_page_dashboard')
        );

        // Submenu - The Lord Generator (replacing Create Pages and Tag Replacements)
        add_submenu_page(
            $this->base_name,
            $this->plugin_name . ' - The Lord Generator',
            'The Lord Generator',
            'manage_options',
            $this->base_name . '_generator',
            array($this, 'lord_generator_view')
        );

        // Submenu - manage pages
        add_submenu_page(
            $this->base_name,
            $this->plugin_name . ' - Manage Pages',
            'Manage Pages',
            'manage_options',
            $this->base_name . '_pages',
            array($this, 'admin_page_manage')
        );

        // Submenu - replacements
        add_submenu_page(
            $this->base_name,
            $this->plugin_name . ' - Word Replacements',
            'Word Replacements',
            'manage_options',
            $this->base_name . '_replacements',
            array($this, 'admin_page_replacements')
        );

        // Submenu - Wikipedia Links
        add_submenu_page(
            $this->base_name,
            $this->plugin_name . ' - Wikipedia Links',
            'Wikipedia Links',
            'manage_options',
            $this->base_name . '_wikipedia_links',
            array($this, 'wikipedia_links_page')
        );

        // Submenu - settings
        add_submenu_page(
            $this->base_name,
            $this->plugin_name . ' - Settings',
            'Settings',
            'manage_options',
            $this->base_name . '_settings',
            array($this, 'admin_page_settings')
        );
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, $this->base_name) !== false) {
            wp_enqueue_style('local-seo-god-admin', plugins_url('assets/css/admin.css', __FILE__));
            wp_enqueue_script('local-seo-god-admin', plugins_url('assets/js/admin.js', __FILE__), array('jquery'), $this->version, true);
            
            // Add localization for the script
            wp_localize_script('local-seo-god-admin', 'localSeoGod', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('local-seo-god-nonce'),
                'version' => $this->version
            ));
        }
    }

    /**
     * Add settings link to plugin page
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="admin.php?page=' . $this->base_name . '">Dashboard</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Dashboard admin page
     */
    public function admin_page_dashboard() {
        include(plugin_dir_path(__FILE__) . 'views/dashboard.php');
    }

    /**
     * Create pages admin page
     */
    public function admin_page_create() {
        include(plugin_dir_path(__FILE__) . 'views/create-pages.php');
    }

    /**
     * Manage pages admin page
     */
    public function admin_page_manage() {
        include(plugin_dir_path(__FILE__) . 'views/manage-pages.php');
    }

    /**
     * Word replacements admin page
     */
    public function admin_page_replacements() {
        include(plugin_dir_path(__FILE__) . 'views/replacements.php');
    }

    /**
     * Settings admin page
     */
    public function admin_page_settings() {
        include(plugin_dir_path(__FILE__) . 'views/settings.php');
    }

    /**
     * Get replacements from database
     */
    private function get_replacements() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . $this->table_name;
        $cache_key = 'local_seo_god_replacements';
        $replacements = wp_cache_get($cache_key);
        
        if ($replacements === false) {
            $replacements = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
            wp_cache_set($cache_key, $replacements, '', 60); // Cache for 60 seconds
        }
        
        return $replacements;
    }

    /**
     * Process content and apply replacements
     */
    private function process_replacements($content, $type = '') {
        $original = $replacement = array();
        $b = $i = '';
        $n = 1;
        
        foreach ($this->get_replacements() as $item) {
            $b = ($item['in_wordonly'] == 'yes') ? '\b' : '';
            $i = ($item['in_sensitive'] == 'yes') ? 'i' : '';
            
            $replace = false;
            
            switch ($type) {
                case 'comment':
                    if ($item['in_comments'] == 'yes') {
                        $replace = true;
                    }
                    break;
                
                case 'title':
                    if ($item['in_titles'] == 'yes') {
                        $replace = true;
                    }
                    break;
                
                default:
                    if (is_page() && $item['in_pages'] == 'yes') {
                        $replace = true;
                    }
                    if (!is_page() && $item['in_posts'] == 'yes') {
                        $replace = true;
                    }
            }
            
            if ($replace) {
                $ori = $this->decode_base64($item['original']);
                $ori = ($item['in_regex'] !== 'yes') ? preg_quote($ori) : $ori;
                $original[$n] = "/$b" . $ori . "$b/$i";
                $replacement[$n] = htmlspecialchars_decode($item['replacement']);
                $n++;
            }
        }
        
        $content = preg_replace($original, $replacement, $content);
        return $content;
    }

    /**
     * Filter content
     */
    public function filter_content($content) {
        return $this->process_replacements($content);
    }

    /**
     * Filter title
     */
    public function filter_title($title) {
        return $this->process_replacements($title, 'title');
    }

    /**
     * Filter comment
     */
    public function filter_comment($content, $comment = '') {
        if ($comment && $comment->comment_approved == '1') {
            $content = $this->process_replacements($content, 'comment');
        }
        return $content;
    }

    /**
     * Decode base64 if needed
     */
    private function decode_base64($string) {
        return base64_decode($string, true) ? base64_decode($string) : $string;
    }

    /**
     * Create pages using template and replacements (AJAX handler)
     */
    public function ajax_create_pages() {
        check_ajax_referer('local-seo-god-nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
            return;
        }
        
        $template_id = intval($_POST['template_id']);
        $replacements = isset($_POST['replacements']) ? $_POST['replacements'] : array();
        
        if (empty($template_id) || empty($replacements)) {
            wp_send_json_error('Missing required data');
            return;
        }
        
        global $wpdb;
        
        // Get template post
        $templates_table = $wpdb->prefix . 'lsg_templates';
        $template = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $templates_table WHERE id = %d",
            $template_id
        ));
        
        if (!$template) {
            wp_send_json_error('Template not found');
            return;
        }
        
        $template_post = get_post($template->template_post_id);
        
        if (!$template_post) {
            wp_send_json_error('Template post not found');
            return;
        }
        
        $created_pages = array();
        $pages_table = $wpdb->prefix . 'lsg_pages';
        
        // Process each replacement set
        foreach ($replacements as $replacement_set) {
            // Apply replacements to title and content
            $title = $template_post->post_title;
            $content = $template_post->post_content;
            
            foreach ($replacement_set as $search => $replace) {
                $title = str_replace('{' . $search . '}', $replace, $title);
                $content = str_replace('{' . $search . '}', $replace, $content);
            }
            
            // Create the new post
            $new_post_id = wp_insert_post(array(
                'post_title'    => $title,
                'post_content'  => $content,
                'post_status'   => 'publish',
                'post_type'     => $template_post->post_type,
                'post_author'   => get_current_user_id(),
                'post_excerpt'  => $template_post->post_excerpt,
                'comment_status' => $template_post->comment_status,
                'ping_status'   => $template_post->ping_status,
            ));
            
            if (!is_wp_error($new_post_id)) {
                // Copy template post meta
                $post_meta = get_post_meta($template_post->ID);
                if ($post_meta) {
                    foreach ($post_meta as $key => $values) {
                        foreach ($values as $value) {
                            add_post_meta($new_post_id, $key, maybe_unserialize($value));
                        }
                    }
                }
                
                // Copy template taxonomies
                $taxonomies = get_object_taxonomies($template_post->post_type);
                foreach ($taxonomies as $taxonomy) {
                    $terms = wp_get_post_terms($template_post->ID, $taxonomy, array('fields' => 'ids'));
                    if (!is_wp_error($terms)) {
                        wp_set_post_terms($new_post_id, $terms, $taxonomy);
                    }
                }
                
                // Store the page in our custom table
                $wpdb->insert(
                    $pages_table,
                    array(
                        'template_id' => $template_id,
                        'page_post_id' => $new_post_id,
                        'replacement_data' => json_encode($replacement_set),
                    )
                );
                
                $created_pages[] = array(
                    'id' => $new_post_id,
                    'title' => $title,
                    'permalink' => get_permalink($new_post_id)
                );
            }
        }
        
        wp_send_json_success(array(
            'created' => count($created_pages),
            'pages' => $created_pages
        ));
    }

    /**
     * Save word replacements (AJAX handler)
     */
    public function ajax_save_replacements() {
        check_ajax_referer('local-seo-god-nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
            return;
        }
        
        $data = $_POST;
        
        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;
        
        // Process deletions
        if (!empty($data['delete'])) {
            foreach ($data['delete'] as $id) {
                $wpdb->delete($table_name, array('id' => intval($id)));
            }
        }
        
        // Process updates and inserts
        if (!empty($data['original'])) {
            foreach ($data['original'] as $i => $original) {
                // Skip empty originals
                if (empty($original)) continue;
                
                $replacement = isset($data['replacement'][$i]) ? $data['replacement'][$i] : '';
                
                $item = array(
                    'original' => base64_encode(trim($original)),
                    'replacement' => trim($replacement),
                    'in_posts' => isset($data['in_posts'][$i]) ? 'yes' : '0',
                    'in_comments' => isset($data['in_comments'][$i]) ? 'yes' : '0',
                    'in_pages' => isset($data['in_pages'][$i]) ? 'yes' : '0',
                    'in_titles' => isset($data['in_titles'][$i]) ? 'yes' : '0',
                    'in_sensitive' => isset($data['in_sensitive'][$i]) ? 'yes' : '0',
                    'in_wordonly' => isset($data['in_wordonly'][$i]) ? 'yes' : '0',
                    'in_regex' => isset($data['in_regex'][$i]) ? 'yes' : '0',
                );
                
                if (!empty($data['id'][$i])) {
                    // Update
                    $wpdb->update(
                        $table_name,
                        $item,
                        array('id' => intval($data['id'][$i]))
                    );
                } else {
                    // Insert
                    $wpdb->insert($table_name, $item);
                }
            }
        }
        
        // Clear cache
        wp_cache_delete('local_seo_god_replacements');
        
        wp_send_json_success('Replacements saved successfully');
    }

    /**
     * Register template (AJAX handler)
     */
    public function ajax_register_template() {
        check_ajax_referer('local-seo-god-nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
            return;
        }
        
        $template_name = sanitize_text_field($_POST['template_name']);
        $template_post_id = intval($_POST['template_post_id']);
        
        if (empty($template_name) || empty($template_post_id)) {
            wp_send_json_error('Missing required data');
            return;
        }
        
        if (!get_post($template_post_id)) {
            wp_send_json_error('Selected post/page does not exist');
            return;
        }
        
        global $wpdb;
        $templates_table = $wpdb->prefix . 'lsg_templates';
        
        $wpdb->insert(
            $templates_table,
            array(
                'template_name' => $template_name,
                'template_post_id' => $template_post_id
            )
        );
        
        if ($wpdb->last_error) {
            wp_send_json_error('Failed to register template: ' . $wpdb->last_error);
            return;
        }
        
        wp_send_json_success(array(
            'id' => $wpdb->insert_id,
            'message' => 'Template registered successfully'
        ));
    }
    
    /**
     * Get template preview (AJAX handler)
     */
    public function ajax_get_template() {
        check_ajax_referer('local-seo-god-nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
            return;
        }
        
        $template_id = intval($_POST['template_id']);
        
        if (empty($template_id)) {
            wp_send_json_error('Missing template ID');
            return;
        }
        
        global $wpdb;
        $templates_table = $wpdb->prefix . 'lsg_templates';
        
        $template = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $templates_table WHERE id = %d",
            $template_id
        ));
        
        if (!$template) {
            wp_send_json_error('Template not found');
            return;
        }
        
        $post = get_post($template->template_post_id);
        
        if (!$post) {
            wp_send_json_error('Template post not found');
            return;
        }
        
        // Highlight placeholder tags for better visibility in the preview
        $content = $post->post_content;
        $content = preg_replace('/\{([^}]+)\}/', '<span class="lsg-placeholder">{$1}</span>', $content);
        
        wp_send_json_success(array(
            'title' => $post->post_title,
            'content' => wpautop($content)
        ));
    }
    
    /**
     * Execute bulk actions on pages (AJAX handler)
     */
    public function ajax_bulk_action() {
        check_ajax_referer('local-seo-god-nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
            return;
        }
        
        $action = sanitize_text_field($_POST['bulk_action']);
        $page_ids = isset($_POST['page_ids']) ? array_map('intval', $_POST['page_ids']) : array();
        
        if (empty($action) || empty($page_ids)) {
            wp_send_json_error('Missing required data');
            return;
        }
        
        global $wpdb;
        $pages_table = $wpdb->prefix . 'lsg_pages';
        
        switch ($action) {
            case 'delete':
                $count = 0;
                foreach ($page_ids as $page_id) {
                    // Get page information
                    $page = $wpdb->get_row($wpdb->prepare(
                        "SELECT * FROM $pages_table WHERE id = %d",
                        $page_id
                    ));
                    
                    if ($page) {
                        // Delete the actual WordPress post
                        wp_delete_post($page->page_post_id, true); // Force delete
                        
                        // Remove from our custom table
                        $wpdb->delete($pages_table, array('id' => $page_id));
                        $count++;
                    }
                }
                
                wp_send_json_success("Successfully deleted $count pages");
                break;
                
            case 'update':
                $count = 0;
                foreach ($page_ids as $page_id) {
                    // Get page information
                    $page = $wpdb->get_row($wpdb->prepare(
                        "SELECT * FROM $pages_table WHERE id = %d",
                        $page_id
                    ));
                    
                    if ($page) {
                        // Get template
                        $templates_table = $wpdb->prefix . 'lsg_templates';
                        $template = $wpdb->get_row($wpdb->prepare(
                            "SELECT * FROM $templates_table WHERE id = %d",
                            $page->template_id
                        ));
                        
                        if ($template) {
                            $template_post = get_post($template->template_post_id);
                            $replacement_data = json_decode($page->replacement_data, true);
                            
                            if ($template_post && $replacement_data) {
                                // Apply replacements to title and content
                                $title = $template_post->post_title;
                                $content = $template_post->post_content;
                                
                                foreach ($replacement_data as $search => $replace) {
                                    $title = str_replace('{' . $search . '}', $replace, $title);
                                    $content = str_replace('{' . $search . '}', $replace, $content);
                                }
                                
                                // Update the post
                                wp_update_post(array(
                                    'ID' => $page->page_post_id,
                                    'post_title' => $title,
                                    'post_content' => $content
                                ));
                                
                                // Update last_updated time
                                $wpdb->update(
                                    $pages_table,
                                    array('last_updated' => current_time('mysql')),
                                    array('id' => $page_id)
                                );
                                
                                $count++;
                            }
                        }
                    }
                }
                
                wp_send_json_success("Successfully updated $count pages");
                break;
                
            default:
                wp_send_json_error('Invalid action');
                break;
        }
    }
    
    /**
     * Export settings and data (AJAX handler)
     */
    public function ajax_export_settings() {
        check_ajax_referer('local_seo_god_export_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
            return;
        }
        
        global $wpdb;
        
        // Get settings
        $settings = get_option('local_seo_god_settings', array());
        
        // Get word replacements
        $table_name = $wpdb->prefix . $this->table_name;
        $replacements = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
        
        // Get templates
        $templates_table = $wpdb->prefix . 'lsg_templates';
        $templates = $wpdb->get_results("SELECT * FROM $templates_table", ARRAY_A);
        
        $export_data = array(
            'plugin_version' => $this->version,
            'export_date' => current_time('mysql'),
            'settings' => $settings,
            'replacements' => $replacements,
            'templates' => $templates
        );
        
        wp_send_json_success($export_data);
    }

    /**
     * Link shortcode
     */
    public function shortcode_link($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
            'text' => '',
            'class' => '',
            'target' => '_self'
        ), $atts);
        
        if (empty($atts['id'])) {
            return '';
        }
        
        $post_id = intval($atts['id']);
        $link_text = !empty($atts['text']) ? $atts['text'] : get_the_title($post_id);
        $class = !empty($atts['class']) ? ' class="' . esc_attr($atts['class']) . '"' : '';
        $target = ' target="' . esc_attr($atts['target']) . '"';
        
        $url = get_permalink($post_id);
        
        if (!$url) {
            return '';
        }
        
        return '<a href="' . esc_url($url) . '"' . $class . $target . '>' . esc_html($link_text) . '</a>';
    }

    /**
     * Register admin scripts
     */
    public function admin_scripts() {
        wp_enqueue_style('local-seo-god-admin-css', plugin_dir_url(__FILE__) . 'assets/css/admin.css', array(), $this->version);
        wp_enqueue_script('local-seo-god-admin-js', plugin_dir_url(__FILE__) . 'assets/js/admin.js', array('jquery'), $this->version, true);
        wp_localize_script('local-seo-god-admin-js', 'localSeoGod', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('local-seo-god-nonce'),
            'exportNonce' => wp_create_nonce('local_seo_god_export_nonce')
        ));
    }
    
    /**
     * Initialize admin
     */
    public function admin_init() {
        // Register consolidated settings
        register_setting(
            'local_seo_god_settings_group',
            'local_seo_god_config',
            array($this, 'sanitize_all_settings')
        );
        
        // Add settings sections
        add_settings_section(
            'general_section',
            'General Settings',
            null,
            $this->base_name . '_settings'
        );
        
        add_settings_section(
            'business_section',
            'Business Information',
            null,
            $this->base_name . '_settings'
        );
        
        add_settings_section(
            'ai_section',
            'AI Settings',
            null,
            $this->base_name . '_settings'
        );
        
        // Migrate settings if needed
        $this->maybe_migrate_settings();
    }
    
    /**
     * Migrate from old settings structure to new unified structure
     */
    private function maybe_migrate_settings() {
        // Check if we've already migrated
        if (get_option('local_seo_god_config')) {
            return;
        }
        
        $config = array(
            'general' => get_option('local_seo_god_settings', array()),
            'business' => get_option('local_seo_god_business_info', array()),
            'ai' => get_option('local_seo_god_ai_settings', array())
        );
        
        // Save the unified config
        update_option('local_seo_god_config', $config);
        
        // Create migration flag (we'll keep the old options for now as a backup)
        update_option('local_seo_god_settings_migrated', true);
        
        // Set a transient to show a notice about the migration
        set_transient('local_seo_god_settings_migrated_notice', true, 60 * 60 * 24); // 24 hours
    }
    
    /**
     * Display admin notice after settings migration
     */
    public function display_migration_notice() {
        // Check if we should display the notice
        if (get_transient('local_seo_god_settings_migrated_notice')) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>Local SEO God:</strong> Settings have been migrated to a new unified structure. All your settings have been preserved.</p>
            </div>
            <?php
            // Delete the transient so the notice is only shown once
            delete_transient('local_seo_god_settings_migrated_notice');
        }
    }
    
    /**
     * Comprehensive sanitize function for all settings
     */
    public function sanitize_all_settings($input) {
        $output = get_option('local_seo_god_config', array(
            'general' => array(),
            'business' => array(),
            'ai' => array()
        ));
        
        // Sanitize general settings
        if (isset($input['general'])) {
            $output['general'] = $this->sanitize_settings($input['general']);
        }
        
        // Sanitize business info
        if (isset($input['business'])) {
            $output['business'] = $this->sanitize_business_info($input['business']);
        }
        
        // Sanitize AI settings
        if (isset($input['ai'])) {
            $output['ai'] = $this->sanitize_ai_settings($input['ai']);
        }
        
        return $output;
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($settings) {
        $sanitized = array();
        
        // Sanitize default post status
        $sanitized['default_post_status'] = isset($settings['default_post_status']) ? 
            sanitize_text_field($settings['default_post_status']) : 'publish';
        
        // Sanitize page title format
        $sanitized['page_title_format'] = isset($settings['page_title_format']) ? 
            sanitize_text_field($settings['page_title_format']) : '{keyword} in {location}';
        
        // Sanitize auto-linking settings
        $sanitized['enable_auto_linking'] = isset($settings['enable_auto_linking']) ? 
            (bool) $settings['enable_auto_linking'] : false;
        
        $sanitized['auto_link_limit'] = isset($settings['auto_link_limit']) ? 
            intval($settings['auto_link_limit']) : 3;
        
        // Check if we need to regenerate the security key
        if (isset($settings['regenerate_key']) && $settings['regenerate_key']) {
            update_option('local_seo_god_secret', wp_generate_password(32, false));
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize business information
     */
    public function sanitize_business_info($business_info) {
        $sanitized = array();
        
        // Sanitize text fields
        $text_fields = array('business_name', 'gmb_service', 'target_location', 'main_keyword', 'target_keywords', 'domain', 'business_description');
        foreach ($text_fields as $field) {
            $sanitized[$field] = isset($business_info[$field]) ? sanitize_text_field($business_info[$field]) : '';
        }
        
        // Sanitize URL fields
        $url_fields = array('social_instagram', 'social_facebook', 'social_gmb');
        foreach ($url_fields as $field) {
            $sanitized[$field] = isset($business_info[$field]) ? esc_url_raw($business_info[$field]) : '';
        }
        
        // Sanitize services array
        $sanitized['services'] = array();
        if (isset($business_info['services']) && is_array($business_info['services'])) {
            foreach ($business_info['services'] as $service) {
                if (!empty($service)) {
                    $sanitized['services'][] = sanitize_text_field($service);
                }
            }
        }
        
        // Sanitize service areas array
        $sanitized['service_areas'] = array();
        if (isset($business_info['service_areas']) && is_array($business_info['service_areas'])) {
            foreach ($business_info['service_areas'] as $area) {
                if (!empty($area)) {
                    $sanitized['service_areas'][] = sanitize_text_field($area);
                }
            }
        }
        
        return $sanitized;
    }

    /**
     * Sanitize AI settings
     */
    public function sanitize_ai_settings($ai_settings) {
        $sanitized = array();
        
        // Sanitize API key
        $sanitized['openai_api_key'] = isset($ai_settings['openai_api_key']) ? sanitize_text_field($ai_settings['openai_api_key']) : '';
        
        // Sanitize boolean values
        $sanitized['enable_ai_content'] = isset($ai_settings['enable_ai_content']) ? (bool) $ai_settings['enable_ai_content'] : false;
        
        return $sanitized;
    }

    /**
     * Save AI settings
     */
    public function save_ai_settings() {
        // Verify nonce
        if (!isset($_POST['local_seo_god_nonce']) || !wp_verify_nonce($_POST['local_seo_god_nonce'], 'local_seo_god_save_ai_settings')) {
            wp_die('Security check failed. Please try again.');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('Permission denied.');
        }
        
        // Get current config
        $config = get_option('local_seo_god_config', array(
            'general' => array(),
            'business' => array(),
            'ai' => array()
        ));
        
        // Sanitize AI settings
        if (isset($_POST['local_seo_god_ai_settings'])) {
            $ai_settings = array(
                'openai_api_key' => sanitize_text_field($_POST['local_seo_god_ai_settings']['openai_api_key']),
                'enable_ai_content' => isset($_POST['local_seo_god_ai_settings']['enable_ai_content']) ? true : false
            );
            
            // Update the AI section of the config
            $config['ai'] = $ai_settings;
            
            // Save the updated config
            update_option('local_seo_god_config', $config);
            
            // Also update the legacy option for backward compatibility
            update_option('local_seo_god_ai_settings', $ai_settings);
        }
        
        // Redirect back to settings page with success message
        wp_redirect(add_query_arg(array('page' => 'local-seo-god_settings', 'tab' => 'ai-settings-tab', 'updated' => 'true'), admin_url('admin.php')));
        exit;
    }

    /**
     * Save business information
     */
    public function save_business_info() {
        // Verify nonce
        if (!isset($_POST['local_seo_god_nonce']) || !wp_verify_nonce($_POST['local_seo_god_nonce'], 'local_seo_god_save_business_info')) {
            wp_die('Security check failed. Please try again.');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die('Permission denied.');
        }
        
        // Get current config
        $config = get_option('local_seo_god_config', array(
            'general' => array(),
            'business' => array(),
            'ai' => array()
        ));
        
        // Sanitize business information
        if (isset($_POST['local_seo_god_business_info'])) {
            $business_info = $this->sanitize_business_info($_POST['local_seo_god_business_info']);
            
            // Update the business section of the config
            $config['business'] = $business_info;
            
            // Save the updated config
            update_option('local_seo_god_config', $config);
            
            // Also update the legacy option for backward compatibility
            update_option('local_seo_god_business_info', $business_info);
        }
        
        // Redirect back to settings page with success message
        wp_redirect(add_query_arg(array('page' => 'local-seo-god_settings', 'tab' => 'business-tab', 'updated' => 'true'), admin_url('admin.php')));
        exit;
    }

    /**
     * Add tag-based replacements view
     */
    public function tag_replacements_view() {
        include(plugin_dir_path(__FILE__) . 'views/tag-replacements.php');
    }
    
    /**
     * Get tag template by name
     * 
     * @param string $template_name The name of the template
     * @return array|bool The tag template array or false if not found
     */
    public function get_tag_template($template_name) {
        $templates = $this->get_tag_templates();
        
        if (isset($templates[$template_name])) {
            return $templates[$template_name];
        }
        
        return false;
    }
    
    /**
     * Get all available tag templates
     * 
     * @return array All available tag templates
     */
    public function get_tag_templates() {
        // Define the templates based on the requirements
        return array(
            'homepage' => array(
                'name' => 'Homepage Tags',
                'description' => 'Tags for home page content replacement',
                'tags' => array(
                    '{One-WordLiner}' => 'Randomly selected descriptive word for your business (each instance gets a different word)',
                    '{Main-Keyword}' => 'Your main target keyword',
                    '{GMB-Service}' => 'Your main Google My Business service category',
                    '{Business-Name}' => 'Your business name',
                    '{Target-Location}' => 'Your main target location',
                    '{Target-Keyword}' => 'Additional target keywords (each instance gets a different keyword when multiple are entered)',
                    // Service tags are handled dynamically
                )
            )
            // Additional templates can be added here in the future
        );
    }
    
    /**
     * Apply tag replacements to content
     * 
     * @param string $content The content to replace tags in
     * @param string $template_name The name of the template to use
     * @return string The content with replaced tags
     */
    public function apply_tag_replacements($content, $template_name) {
        // Get business information from unified config
        $config = get_option('local_seo_god_config', array());
        $business_info = isset($config['business']) ? $config['business'] : array();
        
        // Define the replacements
        $replacements = array(
            '{Main-Keyword}' => isset($business_info['main_keyword']) ? $business_info['main_keyword'] : '',
            '{GMB-Service}' => isset($business_info['gmb_service']) ? $business_info['gmb_service'] : '',
            '{Business-Name}' => isset($business_info['business_name']) ? $business_info['business_name'] : '',
            '{Target-Location}' => isset($business_info['target_location']) ? $business_info['target_location'] : '',
            '{domain}' => isset($business_info['domain']) ? $business_info['domain'] : '',
            '{Business-Description}' => isset($business_info['business_description']) ? $business_info['business_description'] : '',
        );
        
        // Handle Target-Keyword separately if there are multiple targets
        $target_keywords_display = isset($business_info['target_keywords']) ? $business_info['target_keywords'] : '';
        $multiple_target_keywords = false;
        
        if (!empty($target_keywords_display)) {
            $target_keywords_array = array_map('trim', explode(',', $target_keywords_display));
            if (count($target_keywords_array) > 1) {
                $target_keywords_display = implode(', ', $target_keywords_array) . ' <em>(each instance gets a different keyword)</em>';
                $multiple_target_keywords = true;
            }
        }
        
        // Replace basic tags (except One-WordLiner and Target-Keyword when multiple exist)
        $content = str_replace(array_keys($replacements), array_values($replacements), $content);
        
        // Handle One-WordLiner tag differently - replace each occurrence with a different random word
        $content = preg_replace_callback('/\{One-WordLiner\}/', function($matches) {
            return $this->get_random_one_liner();
        }, $content);
        
        // Handle Target-Keyword tag when there are multiple keywords
        if (isset($target_keywords_array) && count($target_keywords_array) > 1) {
            // Keep track of which keyword index to use next
            $keyword_index = 0;
            $keyword_count = count($target_keywords_array);
            
            // Replace each occurrence with a different keyword
            $content = preg_replace_callback('/\{Target-Keyword\}/', function($matches) use (&$keyword_index, $target_keywords_array, $keyword_count) {
                $keyword = $target_keywords_array[$keyword_index];
                $keyword_index = ($keyword_index + 1) % $keyword_count; // Cycle through keywords
                return $keyword;
            }, $content);
        } else if (isset($business_info['target_keywords']) && !empty($business_info['target_keywords'])) {
            $content = str_replace('{Target-Keyword}', $business_info['target_keywords'], $content);
        }
        
        // Handle service tags with HTML replacement
        if (isset($business_info['services']) && is_array($business_info['services'])) {
            foreach ($business_info['services'] as $index => $service) {
                $service_num = $index + 1;
                $tag = "{Service-$service_num}";
                
                // For homepage template, replace with links
                if ($template_name === 'homepage') {
                    $domain = isset($business_info['domain']) ? rtrim($business_info['domain'], '/') : '';
                    $target_location = isset($business_info['target_location']) ? $business_info['target_location'] : '';
                    
                    if (!empty($domain) && !empty($service)) {
                        $link = sprintf(
                            '<a href="https://www.%s/%s-%s">%s</a>',
                            esc_attr($domain),
                            sanitize_title($service),
                            sanitize_title($target_location),
                            esc_html($service)
                        );
                        $content = str_replace($tag, $link, $content);
                    }
                } else {
                    // Simple text replacement for other templates
                    $content = str_replace($tag, $service, $content);
                }
            }
        }
        
        // Handle area tags
        if (isset($business_info['service_areas']) && is_array($business_info['service_areas'])) {
            foreach ($business_info['service_areas'] as $index => $area) {
                $area_num = $index + 1;
                $tag = "{area-$area_num}";
                
                // Simple text replacement
                $content = str_replace($tag, $area, $content);
            }
        }
        
        return $content;
    }
    
    /**
     * AJAX handler for tag replacements preview
     */
    public function ajax_preview_tag_replacements() {
        check_ajax_referer('local-seo-god-nonce', 'nonce');
        
        if (!current_user_can('edit_pages')) {
            wp_send_json_error('Permission denied');
            return;
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $template_name = isset($_POST['template_name']) ? sanitize_text_field($_POST['template_name']) : '';
        
        if (empty($post_id) || empty($template_name)) {
            wp_send_json_error('Missing required data');
            return;
        }
        
        $post = get_post($post_id);
        
        if (!$post) {
            wp_send_json_error('Post not found');
            return;
        }
        
        // Get templates
        $template = $this->get_tag_template($template_name);
        
        if (!$template) {
            wp_send_json_error('Template not found');
            return;
        }
        
        // Get business info
        $business_info = get_option('local_seo_god_business_info', array());
        
        // Get sample one-liners for preview (3 examples)
        $one_liner_examples = array();
        for ($i = 0; $i < 3; $i++) {
            $one_liner_examples[] = $this->get_random_one_liner();
        }
        
        // Build tag replacement table
        $preview_data = array(
            'title' => array(
                'original' => $post->post_title,
                'replaced' => $this->apply_tag_replacements($post->post_title, $template_name)
            ),
            'content' => array(
                'original' => $post->post_content,
                'replaced' => $this->apply_tag_replacements($post->post_content, $template_name)
            ),
            'replacements' => array()
        );
        
        // Process target keywords for preview
        $target_keywords_display = isset($business_info['target_keywords']) ? $business_info['target_keywords'] : '';
        $multiple_target_keywords = false;
        
        if (!empty($target_keywords_display)) {
            $target_keywords_array = array_map('trim', explode(',', $target_keywords_display));
            if (count($target_keywords_array) > 1) {
                $target_keywords_display = implode(', ', $target_keywords_array) . ' <em>(each instance gets a different keyword)</em>';
                $multiple_target_keywords = true;
            }
        }
        
        // Add basic replacements
        $preview_data['replacements']['{Main-Keyword}'] = isset($business_info['main_keyword']) ? $business_info['main_keyword'] : '';
        $preview_data['replacements']['{GMB-Service}'] = isset($business_info['gmb_service']) ? $business_info['gmb_service'] : '';
        $preview_data['replacements']['{Business-Name}'] = isset($business_info['business_name']) ? $business_info['business_name'] : '';
        $preview_data['replacements']['{Target-Location}'] = isset($business_info['target_location']) ? $business_info['target_location'] : '';
        $preview_data['replacements']['{Target-Keyword}'] = $target_keywords_display;
        $preview_data['replacements']['{domain}'] = isset($business_info['domain']) ? $business_info['domain'] : '';
        $preview_data['replacements']['{One-WordLiner}'] = implode(', ', $one_liner_examples) . ' <em>(each instance gets a different word)</em>';
        $preview_data['replacements']['{Business-Description}'] = isset($business_info['business_description']) ? $business_info['business_description'] : '';
        
        // Add service tags
        if (isset($business_info['services']) && is_array($business_info['services'])) {
            foreach ($business_info['services'] as $index => $service) {
                $service_num = $index + 1;
                $tag = "{Service-$service_num}";
                
                if ($template_name === 'homepage') {
                    $domain = isset($business_info['domain']) ? rtrim($business_info['domain'], '/') : '';
                    $target_location = isset($business_info['target_location']) ? $business_info['target_location'] : '';
                    
                    if (!empty($domain) && !empty($service)) {
                        $link = sprintf(
                            '<a href="https://www.%s/%s-%s">%s</a>',
                            esc_attr($domain),
                            sanitize_title($service),
                            sanitize_title($target_location),
                            esc_html($service)
                        );
                        $preview_data['replacements'][$tag] = $link;
                    } else {
                        $preview_data['replacements'][$tag] = $service;
                    }
                } else {
                    $preview_data['replacements'][$tag] = $service;
                }
            }
        }
        
        // Add area tags
        if (isset($business_info['service_areas']) && is_array($business_info['service_areas'])) {
            foreach ($business_info['service_areas'] as $index => $area) {
                $area_num = $index + 1;
                $tag = "{area-$area_num}";
                $preview_data['replacements'][$tag] = $area;
            }
        }
        
        wp_send_json_success($preview_data);
    }
    
    /**
     * AJAX handler for applying tag replacements
     */
    public function ajax_apply_tag_replacements() {
        check_ajax_referer('local-seo-god-nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
            return;
        }
        
        $post_ids = isset($_POST['post_ids']) ? array_map('intval', (array) $_POST['post_ids']) : array();
        $template_name = isset($_POST['template_name']) ? sanitize_text_field($_POST['template_name']) : '';
        
        if (empty($post_ids) || empty($template_name)) {
            wp_send_json_error('Missing required data');
            return;
        }
        
        $updated = 0;
        $errors = array();
        
        foreach ($post_ids as $post_id) {
            $post = get_post($post_id);
            
            if (!$post) {
                $errors[] = "Post ID $post_id not found";
                continue;
            }
            
            // Apply replacements
            $updated_title = $this->apply_tag_replacements($post->post_title, $template_name);
            $updated_content = $this->apply_tag_replacements($post->post_content, $template_name);
            
            // Update the post
            $result = wp_update_post(array(
                'ID' => $post_id,
                'post_title' => $updated_title,
                'post_content' => $updated_content
            ), true);
            
            if (is_wp_error($result)) {
                $errors[] = "Error updating post ID $post_id: " . $result->get_error_message();
            } else {
                $updated++;
            }
        }
        
        wp_send_json_success(array(
            'updated' => $updated,
            'errors' => $errors
        ));
    }

    /**
     * Get a random one-liner word
     * 
     * @return string A random one-liner word
     */
    private function get_random_one_liner() {
        // Path to the one-liner file
        $one_liner_file = plugin_dir_path(__FILE__) . 'views/one-liner-prompts.md';
        
        // Check if the file exists
        if (!file_exists($one_liner_file)) {
            return 'Professional'; // Default fallback
        }
        
        // Read the file
        $content = file_get_contents($one_liner_file);
        if (empty($content)) {
            return 'Professional'; // Default fallback
        }
        
        // Parse the file to extract words
        $lines = explode("\n", $content);
        $words = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            // Skip empty lines, headers, and comments
            if (empty($line) || strpos($line, '#') === 0 || strpos($line, 'One-Word Liner') === 0 || 
                strpos($line, '(Choose') === 0) {
                continue;
            }
            // Add non-empty lines to the words array
            if (!empty($line)) {
                $words[] = $line;
            }
        }
        
        // If no words found, return default
        if (empty($words)) {
            return 'Professional';
        }
        
        // Return a random word
        return $words[array_rand($words)];
    }

    /**
     * Display The Lord Generator view
     */
    public function lord_generator_view() {
        include(plugin_dir_path(__FILE__) . 'views/lord-generator.php');
    }

    /**
     * AJAX handler to get a preview of a selected page
     */
    public function ajax_get_page_preview() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'local-seo-god-nonce')) {
            wp_send_json_error('Invalid security token.');
        }
        
        // Check page ID
        if (!isset($_POST['page_id']) || empty($_POST['page_id'])) {
            wp_send_json_error('No page ID provided.');
        }
        
        $page_id = intval($_POST['page_id']);
        $page = get_post($page_id);
        
        if (!$page) {
            wp_send_json_error('Page not found.');
        }
        
        // Get page content
        $content = apply_filters('the_content', $page->post_content);
        
        // Create a simple preview with highlighted tags
        $title = $page->post_title;
        
        // Highlight tags in both title and content
        $tag_pattern = '/\{([^}]+)\}/';
        $title = preg_replace($tag_pattern, '<span class="lsg-tag">{\1}</span>', $title);
        $content = preg_replace($tag_pattern, '<span class="lsg-tag">{\1}</span>', $content);
        
        wp_send_json_success(array(
            'title' => $title,
            'content' => $content
        ));
    }
    
    /**
     * AJAX handler to create a bulk page
     */
    public function ajax_create_bulk_page() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'local-seo-god-nonce')) {
            wp_send_json_error('Invalid security token.');
        }
        
        // Check template page ID
        if (!isset($_POST['template_page_id']) || empty($_POST['template_page_id'])) {
            wp_send_json_error('No template page ID provided.');
        }
        
        // Check formula
        if (!isset($_POST['formula']) || empty($_POST['formula'])) {
            wp_send_json_error('No formula type provided.');
        }
        
        $template_page_id = intval($_POST['template_page_id']);
        $formula = sanitize_text_field($_POST['formula']);
        $service_index = isset($_POST['service_index']) ? intval($_POST['service_index']) : null;
        $area_index = isset($_POST['area_index']) ? intval($_POST['area_index']) : null;
        
        // Get business information
        $business_info = get_option('local_seo_god_business_info', array());
        
        // Verify we have the necessary data
        if (empty($business_info)) {
            wp_send_json_error('Business information is not set up properly.');
        }
        
        // Get template page
        $template_page = get_post($template_page_id);
        if (!$template_page) {
            wp_send_json_error('Template page not found.');
        }
        
        // Prepare data based on formula
        $page_data = $this->prepare_bulk_page_data($formula, $service_index, $area_index, $business_info);
        if (is_wp_error($page_data)) {
            wp_send_json_error($page_data->get_error_message());
        }
        
        // Create the page
        $page_id = $this->create_bulk_page($template_page, $page_data);
        if (is_wp_error($page_id)) {
            wp_send_json_error($page_id->get_error_message());
        }
        
        // Get information about the created page
        $new_page = get_post($page_id);
        $permalink = get_permalink($page_id);
        $edit_url = get_edit_post_link($page_id, 'raw');
        
        wp_send_json_success(array(
            'id' => $page_id,
            'title' => $new_page->post_title,
            'permalink' => $permalink,
            'edit_url' => $edit_url
        ));
    }
    
    /**
     * Prepare data for bulk page creation based on formula
     */
    private function prepare_bulk_page_data($formula, $service_index, $area_index, $business_info) {
        // If business_info is empty, try to get from unified config
        if (empty($business_info)) {
            $config = get_option('local_seo_god_config', array());
            $business_info = isset($config['business']) ? $config['business'] : array();
        }
        
        $services = $business_info['services'] ?? array();
        $areas = $business_info['service_areas'] ?? array();
        $domain = $business_info['domain'] ?? '';
        $target_location = $business_info['target_location'] ?? '';
        $main_keyword = $business_info['main_keyword'] ?? '';
        $gmb_service = $business_info['gmb_service'] ?? '';
        $business_name = $business_info['business_name'] ?? '';
        $business_description = $business_info['business_description'] ?? '';
        
        $page_data = array(
            'title' => '',
            'slug' => '',
            'replacements' => array()
        );
        
        // Common replacements across all formulas
        $page_data['replacements'] = array(
            'GMB-Service' => $gmb_service,
            'Domain-Name' => $domain,
            'Target-Location' => $target_location,
            'Main-Keyword' => $main_keyword,
            'Business-Name' => $business_name,
            'Business-Description' => $business_description,
            'One-WordLiner' => $this->get_random_one_word_liner()
        );
        
        // Handle different formula types
        switch ($formula) {
            case 'service-location':
                if (!isset($services[$service_index - 1])) {
                    return false;
                }
                
                $service = $services[$service_index - 1];
                
                // Build page title
                $page_data['title'] = $service . ' ' . $target_location . ' | ' . $gmb_service . ' Near Me';
                
                // Build page slug
                $page_data['slug'] = sanitize_title($service . '-' . $target_location);
                
                // Add specific replacements
                $page_data['replacements']['service'] = $service;
                $page_data['replacements']['Service-(prefix number)'] = $service;
                
                // Add numbered service prefixes
                for ($i = 1; $i <= count($services); $i++) {
                    $page_data['replacements']['Service-' . $i] = $services[$i - 1];
                }
                
                // Add service-specific list
                $services_html = $this->generate_services_list_html(
                    $services,
                    $domain,
                    $target_location,
                    $service
                );
                $page_data['replacements']['services-list'] = $services_html;
                
                break;
                
            case 'keyword-area':
                if (!isset($areas[$area_index - 1])) {
                    return false;
                }
                
                $area = $areas[$area_index - 1];
                $one_word_liner = $this->get_random_one_word_liner();
                
                // Build page title
                $page_data['title'] = $one_word_liner . ' ' . $main_keyword . ' ' . $area . ' | ' . $gmb_service . ' Near Me';
                
                // Build page slug
                $page_data['slug'] = sanitize_title($area . '-' . $main_keyword);
                
                // Add specific replacements
                $page_data['replacements']['area'] = $area;
                $page_data['replacements']['area-(prefix number)'] = $area;
                $page_data['replacements']['One-WordLiner'] = $one_word_liner;
                
                // Add numbered area prefixes
                for ($i = 1; $i <= count($areas); $i++) {
                    $page_data['replacements']['area-' . $i] = $areas[$i - 1];
                }
                
                // Add area-specific list
                $areas_html = $this->generate_areas_list_html(
                    $areas,
                    $domain,
                    $main_keyword,
                    $area
                );
                $page_data['replacements']['service-area-list'] = $areas_html;
                
                break;
                
            case 'service-area':
                if (!isset($services[$service_index - 1]) || !isset($areas[$area_index - 1])) {
                    return false;
                }
                
                $service = $services[$service_index - 1];
                $area = $areas[$area_index - 1];
                
                // Build page title
                $page_data['title'] = $service . ' ' . $area . ' | ' . $gmb_service . ' Near Me';
                
                // Build page slug
                $page_data['slug'] = sanitize_title($service . '-' . $area);
                
                // Add specific replacements
                $page_data['replacements']['service'] = $service;
                $page_data['replacements']['Service-(prefix number)'] = $service;
                $page_data['replacements']['area'] = $area;
                $page_data['replacements']['area-(prefix number)'] = $area;
                
                // Add numbered service prefixes
                for ($i = 1; $i <= count($services); $i++) {
                    $page_data['replacements']['Service-' . $i] = $services[$i - 1];
                }
                
                // Add numbered area prefixes
                for ($i = 1; $i <= count($areas); $i++) {
                    $page_data['replacements']['area-' . $i] = $areas[$i - 1];
                }
                
                // Add area-specific services list
                $area_specific_services_html = $this->generate_area_specific_services_html(
                    $services,
                    $domain,
                    $area,
                    $service
                );
                $page_data['replacements']['area-specific-services-list'] = $area_specific_services_html;
                
                break;
                
            default:
                return false;
        }
        
        return $page_data;
    }
    
    /**
     * Apply replacements to text
     */
    private function apply_bulk_replacements($text, $replacements) {
        if (empty($text) || empty($replacements)) {
            return $text;
        }
        
        // First handle the special list tags
        foreach (array('services-list', 'service-area-list', 'area-specific-services-list') as $list_tag) {
            if (isset($replacements[$list_tag])) {
                $text = str_replace('{'.$list_tag.'}', $replacements[$list_tag], $text);
            }
        }
        
        // Replace all other tags, including special patterns
        foreach ($replacements as $tag => $replacement) {
            // Skip the list tags we already processed
            if (in_array($tag, array('services-list', 'service-area-list', 'area-specific-services-list'))) {
                continue;
            }
            
            // Don't add curly braces for pattern tags
            if ($tag === 'Service-(prefix number)' || $tag === 'area-(prefix number)') {
                $text = preg_replace('/\{Service-\(prefix number\)\}/', $replacement, $text);
                $text = preg_replace('/\{area-\(prefix number\)\}/', $replacement, $text);
                continue;
            }
            
            // Regular numeric service tags
            if (preg_match('/^Service-\d+$/', $tag)) {
                $text = str_replace('{'.$tag.'}', $replacement, $text);
                continue;
            }
            
            // Regular numeric area tags
            if (preg_match('/^area-\d+$/', $tag)) {
                $text = str_replace('{'.$tag.'}', $replacement, $text);
                continue;
            }
            
            // All other regular tags
            $text = str_replace('{'.$tag.'}', $replacement, $text);
        }
        
        return $text;
    }
    
    /**
     * Create a bulk page using the template and prepared data
     */
    private function create_bulk_page($template_page, $page_data) {
        // Start timing for performance analysis
        $start_time = microtime(true);
        $logger = LocalSeoGod_Logger::get_instance();
        $logger->log('Starting bulk page creation process');
        
        // Prepare post data
        $post_data = array(
            'post_title' => $page_data['title'],
            'post_name' => $page_data['slug'],
            'post_content' => $template_page->post_content,
            'post_status' => 'publish',
            'post_type' => $template_page->post_type,
            'post_author' => get_current_user_id(),
            'comment_status' => $template_page->comment_status,
            'ping_status' => $template_page->ping_status
        );
        
        // Apply replacements to the content before insertion to save an update operation
        $post_data['post_content'] = $this->apply_bulk_replacements($post_data['post_content'], $page_data['replacements']);
        $post_data['post_title'] = $this->apply_bulk_replacements($post_data['post_title'], $page_data['replacements']);
        
        // Check if content has AI tags before loading AI components (optimization)
        $has_ai_tags = (strpos($post_data['post_content'], '{ai-') !== false);
        
        // Process AI content before insertion if needed
        if ($has_ai_tags) {
            $logger->log('Content contains AI tags - checking AI settings');
            
            // Process AI content tags if present and AI is enabled
            $ai_settings = get_option('local_seo_god_ai_settings', array());
            $ai_enabled = !empty($ai_settings['openai_api_key']) && isset($ai_settings['enable_ai_content']) && $ai_settings['enable_ai_content'];
            
            $logger->log('AI enabled status: ' . ($ai_enabled ? 'true' : 'false'));
            
            if ($ai_enabled) {
                // Extract AI tags - use cached results for performance
                $cache_key = 'lsg_ai_tags_' . md5($post_data['post_content']);
                $ai_tags = wp_cache_get($cache_key);
                
                if (false === $ai_tags) {
                    $logger->log('Extracting AI tags from content');
                    $ai_tags = $this->extract_ai_tags($post_data['post_content']);
                    wp_cache_set($cache_key, $ai_tags, '', 60); // Cache for 1 minute
                } else {
                    $logger->log('Using cached AI tags');
                }
                
                $logger->log('Found ' . count($ai_tags) . ' AI tags in bulk page content');
                
                if (!empty($ai_tags)) {
                    $logger->log('AI tags found: ' . implode(', ', $ai_tags));
                    
                    // Initialize AI handler
                    $ai_handler = new LocalSeoGod_AI_Handler($ai_settings['openai_api_key']);
                    
                    // Prepare business info for AI content generation
                    $normalized_info = $this->prepare_business_info_for_ai($page_data);
                    
                    // Generate AI content
                    $ai_start_time = microtime(true);
                    $ai_content = $ai_handler->generate_content($ai_tags, $normalized_info, '');
                    $ai_end_time = microtime(true);
                    $logger->log('AI content generation took ' . ($ai_end_time - $ai_start_time) . ' seconds');
                    
                    if (is_wp_error($ai_content)) {
                        $logger->log('AI generation error: ' . $ai_content->get_error_message());
                    } else {
                        $logger->log('AI content generated successfully for ' . count($ai_content) . ' tags');
                        
                        // Apply AI content replacements directly to post_content before insertion
                        foreach ($ai_content as $tag => $generated_content) {
                            $logger->log('Replacing tag: ' . $tag . ' with content length: ' . strlen($generated_content));
                            $post_data['post_content'] = str_replace($tag, $generated_content, $post_data['post_content']);
                        }
                        
                        $logger->log('AI content applied to page content');
                    }
                } else {
                    $logger->log('No AI tags found in content despite {ai- being present');
                }
            } else {
                $logger->log('AI processing skipped - AI is not enabled or API key is missing');
            }
        }
        
        // Create the page with all content already processed
        $page_id = wp_insert_post($post_data);
        
        if (is_wp_error($page_id)) {
            $logger->log('Error creating page: ' . $page_id->get_error_message());
            return $page_id;
        }
        
        $logger->log('Page created successfully with ID: ' . $page_id);
        
        // Copy template page meta
        $meta_keys = get_post_custom_keys($template_page->ID);
        if ($meta_keys) {
            foreach ($meta_keys as $key) {
                // Skip WordPress internal meta
                if (in_array($key, array('_edit_lock', '_edit_last', '_wp_page_template', '_wp_old_slug'))) {
                    continue;
                }
                
                $values = get_post_custom_values($key, $template_page->ID);
                foreach ($values as $value) {
                    add_post_meta($page_id, $key, maybe_unserialize($value));
                }
            }
            $logger->log('Copied template page meta to new page');
        }
        
        // Save associations with template and formula
        update_post_meta($page_id, '_lsg_template_page_id', $template_page->ID);
        update_post_meta($page_id, '_lsg_formula_type', $page_data['formula_type'] ?? '');
        update_post_meta($page_id, '_lsg_created_by_bulk', true);
        
        $end_time = microtime(true);
        $total_time = $end_time - $start_time;
        $logger->log('Bulk page creation completed in ' . $total_time . ' seconds');
        
        return $page_id;
    }
    
    /**
     * Prepare business info for AI content generation
     * 
     * @param array $page_data The page data including replacements
     * @return array Normalized business info for AI generation
     */
    private function prepare_business_info_for_ai($page_data) {
        // Get full business info
        $business_info = get_option('local_seo_god_business_info', array());
        
        // Merge business info with page-specific replacements
        $complete_business_info = array_merge($business_info, $page_data['replacements']);
        
        // Normalize keys for the AI handler
        $normalized_info = array(
            'business_name' => $complete_business_info['Business-Name'] ?? '',
            'gmb_service' => $complete_business_info['GMB-Service'] ?? '',
            'target_location' => $complete_business_info['Target-Location'] ?? '',
            'main_keyword' => $complete_business_info['Main-Keyword'] ?? '',
            'domain' => $complete_business_info['domain'] ?? $complete_business_info['Domain-Name'] ?? '',
            'business_description' => $complete_business_info['Business-Description'] ?? '',
        );
        
        // Add important page-specific information
        if (isset($complete_business_info['service'])) {
            $normalized_info['service'] = $complete_business_info['service'];
        }
        
        if (isset($complete_business_info['area'])) {
            $normalized_info['area'] = $complete_business_info['area'];
        }
        
        // Handle services
        if (isset($complete_business_info['services']) && is_array($complete_business_info['services'])) {
            $normalized_info['services'] = $complete_business_info['services'];
        } else {
            // Extract services from individual Service-X tags
            $normalized_info['services'] = array();
            foreach ($complete_business_info as $key => $value) {
                if (preg_match('/^Service-(\d+)$/', $key, $matches)) {
                    $index = (int)$matches[1] - 1;
                    $normalized_info['services'][$index] = $value;
                }
            }
            if (!empty($normalized_info['services'])) {
                ksort($normalized_info['services']);
            }
        }
        
        // Handle service areas
        if (isset($complete_business_info['service_areas']) && is_array($complete_business_info['service_areas'])) {
            $normalized_info['service_areas'] = $complete_business_info['service_areas'];
        } else {
            // Extract service areas from individual area-X tags
            $normalized_info['service_areas'] = array();
            foreach ($complete_business_info as $key => $value) {
                if (preg_match('/^area-(\d+)$/', $key, $matches)) {
                    $index = (int)$matches[1] - 1;
                    $normalized_info['service_areas'][$index] = $value;
                }
            }
            if (!empty($normalized_info['service_areas'])) {
                ksort($normalized_info['service_areas']);
            }
        }
        
        return $normalized_info;
    }

    /**
     * Extract AI tags from content
     * 
     * @param string $content The content to extract AI tags from
     * @return array Array of AI tags found in content
     */
    private function extract_ai_tags($content) {
        // Use a more efficient approach to tag extraction
        $ai_tags = array();
        
        // Match all {ai-*} tags with a single regex
        if (preg_match_all('/\{ai-[a-zA-Z0-9-]+(?:-\d+)?\}/i', $content, $matches)) {
            // Remove duplicate tags and return
            return array_unique($matches[0]);
        }
        
        // If no tags found with the main pattern, try the known tags directly
        // This is faster than using additional regex patterns
        $known_tags = array(
            '{ai-home-introduction}',
            '{ai-service-overview}',
            '{ai-why-us}',
            '{ai-why-us-section}'
        );
        
        // Add FAQ tags
        for ($i = 1; $i <= 5; $i++) {
            $known_tags[] = "{ai-service-faq-title-$i}";
            $known_tags[] = "{ai-service-faq-answer-$i}";
        }
        
        foreach ($known_tags as $tag) {
            if (strpos($content, $tag) !== false) {
                $ai_tags[] = $tag;
            }
        }
        
        return $ai_tags;
    }
    
    /**
     * Copy post meta data from source to destination
     */
    private function copy_post_meta($source_id, $destination_id) {
        $post_meta = get_post_meta($source_id);
        
        if (empty($post_meta)) {
            return;
        }
        
        // Keys to exclude from copying
        $exclude_keys = array(
            '_edit_lock',
            '_edit_last',
            '_wp_page_template',
            '_lsg_template_page_id',
            '_lsg_generated_formula',
            '_lsg_service_index',
            '_lsg_area_index'
        );
        
        foreach ($post_meta as $key => $values) {
            if (in_array($key, $exclude_keys)) {
                continue;
            }
            
            foreach ($values as $value) {
                update_post_meta($destination_id, $key, maybe_unserialize($value));
            }
        }
    }
    
    /**
     * Generate HTML for services list
     */
    private function generate_services_list_html($services, $domain, $target_location, $current_service) {
        $html = '';
        
        foreach ($services as $service) {
            // Include all services (including current)
            // Fix double slash by removing trailing slashes from domain
            $domain = rtrim($domain, '/');
            $service_url = 'https://www.' . $domain . '/' . sanitize_title($service . '-' . $target_location);
            $html .= '<a href="' . esc_url($service_url) . '">' . esc_html($service) . '</a> ';
        }
        
        return trim($html);
    }
    
    /**
     * Generate HTML for service areas list
     */
    private function generate_areas_list_html($areas, $domain, $main_keyword, $current_area) {
        $html = '';
        
        foreach ($areas as $area) {
            // Include all areas (including current)
            // Fix double slash by removing trailing slashes from domain
            $domain = rtrim($domain, '/');
            $area_url = 'https://www.' . $domain . '/' . sanitize_title($area . '-' . $main_keyword);
            $html .= '<a href="' . esc_url($area_url) . '">' . esc_html($area) . '</a> ';
        }
        
        return trim($html);
    }
    
    /**
     * Generate HTML for area-specific services list
     */
    private function generate_area_specific_services_html($services, $domain, $area, $current_service) {
        $html = '';
        
        foreach ($services as $service) {
            // Include all services (including current)
            // Fix double slash by removing trailing slashes from domain
            $domain = rtrim($domain, '/');
            $service_url = 'https://www.' . $domain . '/' . sanitize_title($service . '-' . $area);
            $html .= '<a href="' . esc_url($service_url) . '">' . esc_html($service) . ' ' . esc_html($area) . '</a> ';
        }
        
        return trim($html);
    }
    
    /**
     * Get a random one-word liner from the predefined list
     */
    private function get_random_one_word_liner() {
        $one_word_liners = array(
            'Expert',
            'Trustworthy',
            'Best-Rated',
            'Skilled',
            'Certified',
            'Licensed',
            'Accredited',
            'Professional',
            'Trained',
            'Experienced',
            'Master',
            'Elite',
            'Reliable',
            'Reputable',
            'Established',
            'Proven',
            'Verified',
            'Recognised',
            'High-Quality',
            'Premier',
            'Top-Tier',
            'Five-Star',
            'Leading',
            'Unmatched',
            'Superior',
            'Outstanding',
            'Highly-Rated',
            'Top-Rated',
            'Top-Reviewed',
            'Popular'
        );
        
        return $one_word_liners[array_rand($one_word_liners)];
    }

    /**
     * Initialize admin API
     */
    public function init_admin_api() {
        add_action('admin_init', array($this, 'admin_init'));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'));
        
        // Add notice for settings migration
        add_action('admin_notices', array($this, 'display_migration_notice'));
        
        // Register custom post types if needed
        // add_action('init', array($this, 'register_post_types'));
        
        // Add content filters
        add_filter('the_content', array($this, 'filter_content'));
        add_filter('the_title', array($this, 'filter_title'));
        add_filter('get_comment_text', array($this, 'filter_comment'), 10, 2);
        
        // Register shortcodes
        add_shortcode('lsg-link', array($this, 'shortcode_link'));
        
        // Process forms
        if (is_admin()) {
            // Handle AI settings form submission
            if (isset($_POST['local_seo_god_ai_settings_submit'])) {
                add_action('init', array($this, 'save_ai_settings'));
            }
            
            // Handle business info form submission
            if (isset($_POST['local_seo_god_business_info_submit'])) {
                add_action('init', array($this, 'save_business_info'));
            }
        }
    }

    /**
     * Register AJAX handlers
     */
    public function register_ajax_handlers() {
        // Save replacements
        add_action('wp_ajax_local_seo_god_save_replacements', array($this, 'ajax_save_replacements'));
        
        // Create pages
        add_action('wp_ajax_local_seo_god_create_pages', array($this, 'ajax_create_pages'));
        
        // Register template
        add_action('wp_ajax_local_seo_god_register_template', array($this, 'ajax_register_template'));
        
        // Get template preview
        add_action('wp_ajax_local_seo_god_get_template', array($this, 'ajax_get_template'));
        
        // Bulk action
        add_action('wp_ajax_local_seo_god_bulk_action', array($this, 'ajax_bulk_action'));
        
        // Preview tag replacements
        add_action('wp_ajax_local_seo_god_preview_tag_replacements', array($this, 'ajax_preview_tag_replacements'));
        
        // Apply tag replacements
        add_action('wp_ajax_local_seo_god_apply_tag_replacements', array($this, 'ajax_apply_tag_replacements'));
        
        // Get page preview
        add_action('wp_ajax_local_seo_god_get_page_preview', array($this, 'ajax_get_page_preview'));
        
        // Create bulk page
        add_action('wp_ajax_local_seo_god_create_bulk_page', array($this, 'ajax_create_bulk_page'));
        
        // Get business info for AJAX
        add_action('wp_ajax_local_seo_god_get_business_info', array($this, 'ajax_get_business_info'));
        
        // Get AI content preview
        add_action('wp_ajax_local_seo_god_get_ai_content_preview', array($this, 'ajax_get_ai_content_preview'));
        
        // Check AI tags
        add_action('wp_ajax_local_seo_god_check_ai_tags', array($this, 'ajax_check_ai_tags'));
    }

    /**
     * AJAX handler to check if a template page contains AI tags
     */
    public function ajax_check_ai_tags() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'local-seo-god-nonce')) {
            wp_send_json_error('Invalid security token.');
        }
        
        // Check for template page ID
        if (!isset($_POST['template_page_id']) || empty($_POST['template_page_id'])) {
            wp_send_json_error('No template page ID provided.');
        }
        
        $template_page_id = intval($_POST['template_page_id']);
        $template_page = get_post($template_page_id);
        
        if (!$template_page) {
            wp_send_json_error('Template page not found.');
        }
        
        // Extract AI tags from content
        $ai_tags = $this->extract_ai_tags($template_page->post_content);
        
        wp_send_json_success(array(
            'tags' => $ai_tags,
            'has_ai_tags' => !empty($ai_tags)
        ));
    }

    /**
     * AJAX handler for getting business information
     */
    public function ajax_get_business_info() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'local-seo-god-nonce')) {
            wp_send_json_error('Invalid security token.');
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_pages')) {
            wp_send_json_error('Permission denied.');
            return;
        }
        
        // Get business information from unified config
        $config = get_option('local_seo_god_config', array());
        $business_info = isset($config['business']) ? $config['business'] : array();
        
        // Try legacy business info if unified config is empty
        if (empty($business_info)) {
            $business_info = get_option('local_seo_god_business_info', array());
        }
        
        // Validate and normalize business information
        $validated_info = $this->validate_business_info($business_info);
        
        // Log errors if validation failed
        if (isset($validated_info['errors']) && !empty($validated_info['errors'])) {
            $logger = LocalSeoGod_Logger::get_instance();
            $logger->log('Business info validation errors: ' . json_encode($validated_info['errors']));
        }
        
        // Add one word liners for convenience
        $validated_info['one_word_liners'] = $this->get_one_word_liners_array();
        
        // Send the data
        wp_send_json_success($validated_info);
    }
    
    /**
     * Validate and normalize business information for page generation
     * 
     * @param array $business_info Raw business information
     * @return array Validated and normalized business information
     */
    private function validate_business_info($business_info) {
        $result = array(
            'domain' => '',
            'target_location' => '',
            'main_keyword' => '',
            'gmb_service' => '',
            'business_name' => '',
            'business_description' => '',
            'services' => array(),
            'service_areas' => array(),
            'errors' => array()
        );
        
        // Validate required fields
        $required_fields = array('domain', 'target_location', 'main_keyword', 'gmb_service', 'business_name');
        foreach ($required_fields as $field) {
            if (empty($business_info[$field])) {
                $result['errors'][] = 'Missing ' . str_replace('_', ' ', $field);
            } else {
                $result[$field] = sanitize_text_field($business_info[$field]);
            }
        }
        
        // Business description is optional
        $result['business_description'] = isset($business_info['business_description']) ? 
            sanitize_textarea_field($business_info['business_description']) : '';
            
        // Validate arrays
        if (empty($business_info['services']) || !is_array($business_info['services'])) {
            $result['errors'][] = 'No services defined';
        } else {
            // Sanitize each service
            foreach ($business_info['services'] as $service) {
                if (!empty($service)) {
                    $result['services'][] = sanitize_text_field($service);
                }
            }
            
            if (empty($result['services'])) {
                $result['errors'][] = 'All service entries were empty';
            }
        }
        
        if (empty($business_info['service_areas']) || !is_array($business_info['service_areas'])) {
            $result['errors'][] = 'No service areas defined';
        } else {
            // Sanitize each service area
            foreach ($business_info['service_areas'] as $area) {
                if (!empty($area)) {
                    $result['service_areas'][] = sanitize_text_field($area);
                }
            }
            
            if (empty($result['service_areas'])) {
                $result['errors'][] = 'All service area entries were empty';
            }
        }
        
        return $result;
    }

    /**
     * Get an array of one-word liners for random selection
     */
    private function get_one_word_liners_array() {
        return array(
            'Professional',
            'Expert',
            'Reliable',
            'Affordable',
            'Best',
            'Trusted',
            'Experienced',
            'Local',
            'Top',
            'Leading',
            'Certified',
            'Licensed',
            'Insured',
            'Quality',
            'Premium',
            'Dependable',
            'Unmatched',
            'Superior',
            'Outstanding',
            'Highly-Rated',
            'Top-Rated',
            'Top-Reviewed',
            'Popular'
        );
    }

    /**
     * AJAX handler for getting AI content preview
     */
    public function ajax_get_ai_content_preview() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'local-seo-god-nonce')) {
            wp_send_json_error('Invalid security token.');
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_pages')) {
            wp_send_json_error('Permission denied.');
            return;
        }
        
        // Get AI settings from unified config
        $config = get_option('local_seo_god_config', array());
        $ai_settings = isset($config['ai']) ? $config['ai'] : array();
        
        // Check if AI is enabled and API key exists
        if (empty($ai_settings['openai_api_key']) || !isset($ai_settings['enable_ai_content']) || !$ai_settings['enable_ai_content']) {
            wp_send_json_error('AI content generation is not enabled or API key is missing.');
            return;
        }
        
        // Check for tag and business info
        if (!isset($_POST['tag']) || empty($_POST['tag'])) {
            wp_send_json_error('No AI tag provided.');
            return;
        }
        
        $tag = sanitize_text_field($_POST['tag']);
        $business_info = isset($_POST['business_info']) ? $_POST['business_info'] : array();
        
        // Initialize AI handler
        require_once plugin_dir_path(__FILE__) . 'includes/class-ai-handler.php';
        $ai_handler = new LocalSeoGod_AI_Handler($ai_settings['openai_api_key']);
        
        // Generate preview content
        $content = $ai_handler->regenerate_content_for_tag($tag, $business_info);
        
        if ($content === false) {
            wp_send_json_error('Failed to generate AI content preview.');
            return;
        }
        
        wp_send_json_success(array(
            'tag' => $tag,
            'content' => $content
        ));
    }

    /**
     * Render Wikipedia Links demo page
     */
    public function wikipedia_links_page() {
        require_once plugin_dir_path(__FILE__) . 'views/wikipedia-links-demo.php';
    }
}

// Initialize the plugin
new LocalSeoGod(); 