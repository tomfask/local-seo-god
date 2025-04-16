<?php
/**
 * Test script for Wikipedia links shortcode functionality
 * 
 * This is a standalone script to test the functionality of the placeslinks shortcode
 * without requiring a WordPress installation.
 */

// Define ABSPATH to simulate WordPress environment
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

// Mock WordPress shortcode functions
if (!function_exists('add_shortcode')) {
    function add_shortcode($tag, $callback) {
        global $shortcode_tags;
        $shortcode_tags[$tag] = $callback;
    }
}

if (!function_exists('do_shortcode')) {
    function do_shortcode($content) {
        global $shortcode_tags;
        
        if (empty($shortcode_tags) || !is_array($shortcode_tags)) {
            return $content;
        }
        
        $pattern = get_shortcode_regex();
        return preg_replace_callback("/$pattern/s", 'do_shortcode_tag', $content);
    }
}

if (!function_exists('get_shortcode_regex')) {
    function get_shortcode_regex() {
        global $shortcode_tags;
        $tagnames = array_keys($shortcode_tags);
        $tagregexp = join('|', array_map('preg_quote', $tagnames));
        return '\\[(\\[?)(' . $tagregexp . ')(?![\\w-])([^\\]\\/]*(?:\\/(?!\\])[^\\]\\/]*)*?)(?:(\\/)\\]|\\](?:([^\\[]*+(?:\\[(?!\\/\\2\\])[^\\[]*+)*+)\\[\\/\\2\\])?)(\\]?)';
    }
}

if (!function_exists('do_shortcode_tag')) {
    function do_shortcode_tag($m) {
        global $shortcode_tags;
        
        if ($m[1] == '[' && $m[6] == ']') {
            return substr($m[0], 1, -1);
        }
        
        $tag = $m[2];
        $attr = shortcode_parse_atts($m[3]);
        
        if (isset($m[5])) {
            $content = $m[5];
            $content = do_shortcode($content);
        } else {
            $content = null;
        }
        
        $callback = $shortcode_tags[$tag];
        return call_user_func($callback, $attr, $content, $tag);
    }
}

if (!function_exists('shortcode_parse_atts')) {
    function shortcode_parse_atts($text) {
        $atts = array();
        $pattern = '/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
        $text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
        
        if (preg_match_all($pattern, $text, $match, PREG_SET_ORDER)) {
            foreach ($match as $m) {
                if (!empty($m[1])) {
                    $atts[strtolower($m[1])] = stripcslashes($m[2]);
                } elseif (!empty($m[3])) {
                    $atts[strtolower($m[3])] = stripcslashes($m[4]);
                } elseif (!empty($m[5])) {
                    $atts[strtolower($m[5])] = stripcslashes($m[6]);
                } elseif (isset($m[7]) && strlen($m[7])) {
                    $atts[] = stripcslashes($m[7]);
                } elseif (isset($m[8])) {
                    $atts[] = stripcslashes($m[8]);
                }
            }
        } else {
            $atts = ltrim($text);
        }
        
        return $atts;
    }
}

if (!function_exists('esc_url')) {
    function esc_url($url) {
        // Simple version just for testing
        return filter_var($url, FILTER_SANITIZE_URL);
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text) {
        // Simple version just for testing
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

// Initialize global variable for shortcodes
global $shortcode_tags;
$shortcode_tags = array();

// Include the Wikipedia links class
require_once __DIR__ . '/includes/class-wikipedia-links.php';

// Test content
$test_content = <<<EOT
[placeslinks]
Located in [[Melbourne]], our service area extends to suburbs like [[South Yarra]], [[Brighton]], and [[St Kilda]].
We also serve customers in [[Mornington, Victoria]] and [[Geelong]].
[/placeslinks]
EOT;

// Process the shortcode
$processed_content = do_shortcode($test_content);

// Output the results
echo "Original content:\n";
echo $test_content;
echo "\n\n";

echo "Processed content:\n";
echo $processed_content;
echo "\n"; 