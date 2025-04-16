<?php
// Prevent direct access
if (!defined('ABSPATH')) exit;

global $wpdb;
$table_name = $wpdb->prefix . $this->table_name;
$replacements = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC", ARRAY_A);
?>

<div class="wrap local-seo-god-wrap">
    <h1><?php echo esc_html($this->plugin_name); ?> - Word Replacements</h1>
    
    <div class="local-seo-god-replacements">
        <div class="local-seo-god-card">
            <h2>Manage Word Replacements</h2>
            <div class="card-content">
                <p>This feature allows you to replace words or phrases across your entire site. Add the word to be replaced on the left, and what to change it to on the right.</p>
                
                <div class="local-seo-god-status-message"></div>
                
                <form method="post" action="" class="local-seo-god-form local-seo-god-replacements-form">
                    <?php wp_nonce_field('local_seo_god_replacements_nonce', 'replacements_nonce'); ?>
                    
                    <div class="local-seo-god-replacements-list">
                        <table class="widefat">
                            <thead>
                                <tr>
                                    <th width="40">Delete</th>
                                    <th>Original</th>
                                    <th>Replacement</th>
                                    <th class="checkbox-cell">Posts</th>
                                    <th class="checkbox-cell">Pages</th>
                                    <th class="checkbox-cell">Titles</th>
                                    <th class="checkbox-cell">Comments</th>
                                    <th class="checkbox-cell">Case Insensitive</th>
                                    <th class="checkbox-cell">Whole Word</th>
                                    <th class="checkbox-cell">Regex</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($replacements)) : ?>
                                    <?php foreach ($replacements as $i => $item) : ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="delete[<?php echo $i; ?>]" class="local-seo-god-delete-replacement">
                                                <input type="hidden" name="id[<?php echo $i; ?>]" value="<?php echo esc_attr($item['id']); ?>">
                                            </td>
                                            <td>
                                                <input type="text" name="original[<?php echo $i; ?>]" value="<?php echo esc_attr($this->decode_base64($item['original'])); ?>" class="widefat">
                                            </td>
                                            <td>
                                                <textarea name="replacement[<?php echo $i; ?>]" class="widefat" rows="3"><?php echo esc_textarea($item['replacement']); ?></textarea>
                                            </td>
                                            <td class="checkbox-cell">
                                                <input type="checkbox" name="in_posts[<?php echo $i; ?>]" value="yes" <?php checked($item['in_posts'], 'yes'); ?>>
                                            </td>
                                            <td class="checkbox-cell">
                                                <input type="checkbox" name="in_pages[<?php echo $i; ?>]" value="yes" <?php checked($item['in_pages'], 'yes'); ?>>
                                            </td>
                                            <td class="checkbox-cell">
                                                <input type="checkbox" name="in_titles[<?php echo $i; ?>]" value="yes" <?php checked($item['in_titles'], 'yes'); ?>>
                                            </td>
                                            <td class="checkbox-cell">
                                                <input type="checkbox" name="in_comments[<?php echo $i; ?>]" value="yes" <?php checked($item['in_comments'], 'yes'); ?>>
                                            </td>
                                            <td class="checkbox-cell">
                                                <input type="checkbox" name="in_sensitive[<?php echo $i; ?>]" value="yes" <?php checked($item['in_sensitive'], 'yes'); ?>>
                                            </td>
                                            <td class="checkbox-cell">
                                                <input type="checkbox" name="in_wordonly[<?php echo $i; ?>]" value="yes" <?php checked($item['in_wordonly'], 'yes'); ?>>
                                            </td>
                                            <td class="checkbox-cell">
                                                <input type="checkbox" name="in_regex[<?php echo $i; ?>]" value="yes" <?php checked($item['in_regex'], 'yes'); ?>>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="delete[0]" class="local-seo-god-delete-replacement">
                                            <input type="hidden" name="id[0]" value="">
                                        </td>
                                        <td>
                                            <input type="text" name="original[0]" value="" class="widefat">
                                        </td>
                                        <td>
                                            <textarea name="replacement[0]" class="widefat" rows="3"></textarea>
                                        </td>
                                        <td class="checkbox-cell">
                                            <input type="checkbox" name="in_posts[0]" value="yes" checked>
                                        </td>
                                        <td class="checkbox-cell">
                                            <input type="checkbox" name="in_pages[0]" value="yes" checked>
                                        </td>
                                        <td class="checkbox-cell">
                                            <input type="checkbox" name="in_titles[0]" value="yes">
                                        </td>
                                        <td class="checkbox-cell">
                                            <input type="checkbox" name="in_comments[0]" value="yes">
                                        </td>
                                        <td class="checkbox-cell">
                                            <input type="checkbox" name="in_sensitive[0]" value="yes" checked>
                                        </td>
                                        <td class="checkbox-cell">
                                            <input type="checkbox" name="in_wordonly[0]" value="yes">
                                        </td>
                                        <td class="checkbox-cell">
                                            <input type="checkbox" name="in_regex[0]" value="yes">
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <p>
                        <button type="button" class="button local-seo-god-add-replacement">+ Add New Replacement</button>
                    </p>
                    
                    <div class="local-seo-god-help">
                        <p><span class="local-seo-god-expandable-toggle">Need help with replacements?</span></p>
                        <div class="local-seo-god-expandable-content">
                            <h4>Instructions:</h4>
                            <ul>
                                <li><strong>Original:</strong> The word or phrase you want to replace.</li>
                                <li><strong>Replacement:</strong> What the original word will be replaced with.</li>
                                <li><strong>Posts/Pages/Titles/Comments:</strong> Where the replacement will occur.</li>
                                <li><strong>Case Insensitive:</strong> Whether to match regardless of case (e.g., "City" and "city").</li>
                                <li><strong>Whole Word:</strong> Only replace the word if it's a complete word (not part of another word).</li>
                                <li><strong>Regex:</strong> Use regular expressions for advanced pattern matching.</li>
                            </ul>
                            
                            <h4>Examples:</h4>
                            <ol>
                                <li><strong>Basic:</strong> To replace "plumber" with "licensed plumber", enter "plumber" in Original and "licensed plumber" in Replacement.</li>
                                <li><strong>With Regex:</strong> To replace multiple city names with a bold version, use "(Chicago|New York|Boston)" in Original with Regex enabled, and "&lt;strong&gt;$1&lt;/strong&gt;" in Replacement.</li>
                            </ol>
                            
                            <p><strong>Note:</strong> Be careful with replacements to avoid unintended consequences. Test on a few pages first.</p>
                        </div>
                    </div>
                    
                    <p class="submit">
                        <input type="submit" name="submit" class="button button-primary" value="Save Changes">
                    </p>
                </form>
            </div>
        </div>
    </div>
</div> 