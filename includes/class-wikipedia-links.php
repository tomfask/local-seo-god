<?php
/**
 * Wikipedia Links Handler Class
 *
 * Implements the placeslinks shortcode for linking geographic locations to Wikipedia
 *
 * @package LocalSeoGod
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class LocalSeoGod_Wikipedia_Links {

    /**
     * Initialize the class and set up hooks
     */
    public function __construct() {
        // Register the shortcode on init to ensure WordPress is fully loaded
        add_action('init', array($this, 'register_shortcode'));
    }

    /**
     * Register the shortcode with WordPress
     */
    public function register_shortcode() {
        add_shortcode('placeslinks', array($this, 'placeslinks_shortcode'));
    }

    /**
     * Process the placeslinks shortcode
     * 
     * Transforms content with Wikipedia-style links [[Location]] into HTML links
     * that point to the corresponding Wikipedia pages.
     * 
     * @param array $atts Shortcode attributes
     * @param string $content Content inside the shortcode
     * @return string Processed content with Wikipedia links
     */
    public function placeslinks_shortcode($atts, $content = null) {
        if (empty($content)) {
            return '';
        }

        // Process all instances of [[Location]] in the content
        $pattern = '/\[\[(.*?)\]\]/';
        $processed_content = preg_replace_callback($pattern, array($this, 'process_wikipedia_link'), $content);

        return $processed_content;
    }

    /**
     * Process individual Wikipedia-style links
     * 
     * @param array $matches Regex matches
     * @return string HTML link to Wikipedia article
     */
    private function process_wikipedia_link($matches) {
        if (empty($matches[1])) {
            return $matches[0]; // Return original text if match is empty
        }

        $location = trim($matches[1]);
        $wiki_url = $this->get_wikipedia_url($location);
        
        // Create the HTML link
        return '<a href="' . esc_url($wiki_url) . '" target="_blank" rel="noopener noreferrer">' . esc_html($location) . '</a>';
    }

    /**
     * Generate Wikipedia URL for a location
     * 
     * @param string $location Location name
     * @return string Wikipedia URL
     */
    private function get_wikipedia_url($location) {
        // Replace spaces with underscores for Wikipedia URL format
        $wiki_formatted = str_replace(' ', '_', $location);
        
        // Encode URL components correctly
        $wiki_formatted = rawurlencode($wiki_formatted);
        
        // Return full Wikipedia URL
        return 'https://en.wikipedia.org/wiki/' . $wiki_formatted;
    }
}

// Initialize the class
$local_seo_god_wikipedia_links = new LocalSeoGod_Wikipedia_Links(); 