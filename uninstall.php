<?php
/**
 * Uninstall Local SEO God
 *
 * Removes all data when plugin is uninstalled via WordPress admin.
 */

// If uninstall is not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove database tables
global $wpdb;

// Plugin tables
$tables = array(
    $wpdb->prefix . 'local_seo_god',  // Word replacements table
    $wpdb->prefix . 'lsg_templates',  // Templates table
    $wpdb->prefix . 'lsg_pages',      // Created pages table
);

foreach ($tables as $table) {
    $wpdb->query("DROP TABLE IF EXISTS $table");
}

// Remove options
$options = array(
    'local_seo_god_version',
    'local_seo_god_settings',
    'local_seo_god_secret',
);

foreach ($options as $option) {
    delete_option($option);
}

// Clear any cached data
wp_cache_flush(); 