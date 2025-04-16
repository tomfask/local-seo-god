<?php
/**
 * Plugin Update Checker
 * 
 * Integrates with plugin-update-checker library to handle plugin updates from GitHub, BitBucket, or GitLab.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class LocalSeoGod_Update_Checker {
    /**
     * Constructor
     */
    public function __construct() {
        // Only proceed if we're in the admin area
        if (!is_admin()) {
            return;
        }

        // Initialize the update checker
        $this->init_update_checker();
    }

    /**
     * Initialize the update checker
     */
    private function init_update_checker() {
        // Check if plugin-update-checker is already loaded
        if (!class_exists('Puc_v4_Factory') && !class_exists('Puc_v5_Factory')) {
            $plugin_update_checker_file = LOCAL_SEO_GOD_PATH . 'vendor/plugin-update-checker/plugin-update-checker.php';
            
            // Check if the file exists before including it
            if (!file_exists($plugin_update_checker_file)) {
                // Log error or notify admin
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Local SEO God: Plugin Update Checker library not found.');
                }
                return;
            }
            
            // Include plugin-update-checker
            require_once $plugin_update_checker_file;
        }

        // Define repository details with fallbacks
        $repo_type = defined('LOCAL_SEO_GOD_REPO_TYPE') ? LOCAL_SEO_GOD_REPO_TYPE : 'github';
        $repo_url = defined('LOCAL_SEO_GOD_REPO_URL') ? LOCAL_SEO_GOD_REPO_URL : '';

        if (empty($repo_url)) {
            // Default to GitHub repository if not defined
            $repo_url = 'https://github.com/username/local-seo-god';
            
            // If no repo URL is available, exit early
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Local SEO God: Repository URL not defined.');
            }
            return;
        }

        // Create update checker instance
        if (class_exists('Puc_v5_Factory')) {
            $update_checker = Puc_v5_Factory::buildUpdateChecker(
                $repo_url,
                LOCAL_SEO_GOD_FILE,
                'local-seo-god'
            );
        } elseif (class_exists('Puc_v4_Factory')) {
            $update_checker = Puc_v4_Factory::buildUpdateChecker(
                $repo_url,
                LOCAL_SEO_GOD_FILE,
                'local-seo-god'
            );
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Local SEO God: Compatible Plugin Update Checker factory not found.');
            }
            return;
        }

        // Configure based on repository type
        switch ($repo_type) {
            case 'github':
                $this->setup_github_updater($update_checker);
                break;
            case 'bitbucket':
                $this->setup_bitbucket_updater($update_checker);
                break;
            case 'gitlab':
                $this->setup_gitlab_updater($update_checker);
                break;
        }
    }

    /**
     * Setup GitHub updater
     */
    private function setup_github_updater($update_checker) {
        if (!method_exists($update_checker, 'getVcsApi')) {
            return;
        }

        $update_checker->setBranch('main');
        
        // Set authentication if defined
        if (defined('LOCAL_SEO_GOD_GITHUB_ACCESS_TOKEN') && LOCAL_SEO_GOD_GITHUB_ACCESS_TOKEN) {
            $update_checker->getVcsApi()->setAuthentication(LOCAL_SEO_GOD_GITHUB_ACCESS_TOKEN);
        }
        
        // Set release assets directory if defined
        if (defined('LOCAL_SEO_GOD_GITHUB_RELEASE_ASSETS_DIR') && LOCAL_SEO_GOD_GITHUB_RELEASE_ASSETS_DIR) {
            $update_checker->getVcsApi()->setReleaseAssetsDirectory(LOCAL_SEO_GOD_GITHUB_RELEASE_ASSETS_DIR);
        }
    }

    /**
     * Setup BitBucket updater
     */
    private function setup_bitbucket_updater($update_checker) {
        if (!method_exists($update_checker, 'getVcsApi')) {
            return;
        }

        $update_checker->setBranch('main');
        
        // Set authentication if defined
        if (defined('LOCAL_SEO_GOD_BITBUCKET_USERNAME') && defined('LOCAL_SEO_GOD_BITBUCKET_PASSWORD')) {
            $update_checker->getVcsApi()->setAuthentication(
                LOCAL_SEO_GOD_BITBUCKET_USERNAME,
                LOCAL_SEO_GOD_BITBUCKET_PASSWORD
            );
        }
    }

    /**
     * Setup GitLab updater
     */
    private function setup_gitlab_updater($update_checker) {
        if (!method_exists($update_checker, 'getVcsApi')) {
            return;
        }

        $update_checker->setBranch('main');
        
        // Set authentication if defined
        if (defined('LOCAL_SEO_GOD_GITLAB_ACCESS_TOKEN') && LOCAL_SEO_GOD_GITLAB_ACCESS_TOKEN) {
            $update_checker->getVcsApi()->setAuthentication(LOCAL_SEO_GOD_GITLAB_ACCESS_TOKEN);
        }
    }
}

// Initialize the update checker
new LocalSeoGod_Update_Checker(); 