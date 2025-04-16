<?php
// Prevent direct access
if (!defined('ABSPATH')) exit;

// Get templates
global $wpdb;
$templates_table = $wpdb->prefix . 'lsg_templates';
$templates = $wpdb->get_results("SELECT * FROM $templates_table ORDER BY template_name ASC");

// Get existing WordPress pages/posts for template selection
$args = array(
    'post_type' => array('page', 'post'),
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC'
);
$posts = get_posts($args);
?>

<div class="wrap local-seo-god-wrap">
    <h1><?php echo esc_html($this->plugin_name); ?> - Create Pages</h1>
    
    <div class="local-seo-god-create-pages">
        <div class="local-seo-god-card">
            <h2>Create New Landing Pages</h2>
            <div class="card-content">
                <p>Use this tool to create multiple location-based pages from a template. First, register a template if you haven't already, then add keyword replacement sets to generate pages.</p>
                
                <div class="local-seo-god-tabs-container">
                    <ul class="local-seo-god-tabs nav-tab-wrapper">
                        <li><a href="#template-tab" class="nav-tab nav-tab-active">1. Register Template</a></li>
                        <li><a href="#create-tab" class="nav-tab">2. Create Pages</a></li>
                    </ul>
                    
                    <div id="template-tab" class="local-seo-god-tab-content">
                        <form method="post" action="" class="local-seo-god-form local-seo-god-template-form">
                            <?php wp_nonce_field('local_seo_god_template_nonce', 'template_nonce'); ?>
                            
                            <div class="form-group">
                                <label for="template-name">Template Name:</label>
                                <input type="text" id="template-name" name="template_name" class="regular-text" required>
                                <p class="description">Give your template a descriptive name (e.g., "Service Page Template")</p>
                            </div>
                            
                            <div class="form-group">
                                <label for="template-post">Select Existing Page/Post:</label>
                                <select id="template-post" name="template_post_id" class="regular-text" required>
                                    <option value="">-- Select a page or post --</option>
                                    <?php foreach ($posts as $post) : ?>
                                        <option value="<?php echo esc_attr($post->ID); ?>"><?php echo esc_html($post->post_title); ?> (<?php echo esc_html($post->post_type); ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">Select an existing page or post to use as your template</p>
                            </div>
                            
                            <div class="local-seo-god-help">
                                <p><strong>Important:</strong> Your template should contain placeholder tags like <code>{city}</code>, <code>{state}</code>, etc. These will be replaced when creating pages.</p>
                            </div>
                            
                            <p class="submit">
                                <input type="submit" name="submit_template" class="button button-primary" value="Register Template">
                            </p>
                        </form>
                        
                        <div class="local-seo-god-template-status"></div>
                        
                        <?php if (!empty($templates)) : ?>
                            <div class="local-seo-god-templates-list">
                                <h3>Registered Templates</h3>
                                <table class="widefat">
                                    <thead>
                                        <tr>
                                            <th>Template Name</th>
                                            <th>Source Page/Post</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($templates as $template) : 
                                            $source_post = get_post($template->template_post_id);
                                            if (!$source_post) continue;
                                        ?>
                                            <tr>
                                                <td><?php echo esc_html($template->template_name); ?></td>
                                                <td>
                                                    <a href="<?php echo get_edit_post_link($source_post->ID); ?>" target="_blank">
                                                        <?php echo esc_html($source_post->post_title); ?>
                                                    </a>
                                                    (<?php echo esc_html($source_post->post_type); ?>)
                                                </td>
                                                <td><?php echo date('F j, Y', strtotime($template->created_at)); ?></td>
                                                <td>
                                                    <a href="<?php echo admin_url('admin.php?page=' . $this->base_name . '_create&delete_template=' . $template->id . '&_wpnonce=' . wp_create_nonce('delete_template_' . $template->id)); ?>" class="button button-small" onclick="return confirm('Are you sure you want to delete this template?');">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div id="create-tab" class="local-seo-god-tab-content" style="display: none;">
                        <form method="post" action="" class="local-seo-god-form local-seo-god-create-form">
                            <?php wp_nonce_field('local_seo_god_create_nonce', 'create_nonce'); ?>
                            
                            <div class="form-group">
                                <label for="local-seo-god-template">Select Template:</label>
                                <select id="local-seo-god-template" name="template_id" class="regular-text" required>
                                    <option value="">-- Select a template --</option>
                                    <?php foreach ($templates as $template) : ?>
                                        <option value="<?php echo esc_attr($template->id); ?>"><?php echo esc_html($template->template_name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div id="local-seo-god-template-preview"></div>
                            
                            <h3>Replacement Sets</h3>
                            <p>Each set will create a new page with the replacements specified.</p>
                            
                            <div id="local-seo-god-replacements-container" class="local-seo-god-replacements-container"></div>
                            
                            <button type="button" class="button local-seo-god-add-set-btn">Add Replacement Set</button>
                            
                            <div class="local-seo-god-help">
                                <p>For each set, add keyword/value pairs to replace in the template.</p>
                                <p>Example: <code>keyword</code> = city, <code>value</code> = Chicago</p>
                            </div>
                            
                            <p class="submit">
                                <input type="submit" name="submit_create" class="button button-primary" value="Create Pages">
                            </p>
                        </form>
                        
                        <div class="local-seo-god-creation-status"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.lsg-placeholder {
    background-color: #f7f7c3;
    padding: 2px 4px;
    border-radius: 3px;
    font-weight: bold;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Handle template registration via AJAX
    $('.local-seo-god-template-form').on('submit', function(e) {
        e.preventDefault();
        
        var templateName = $('#template-name').val();
        var templatePostId = $('#template-post').val();
        var submitBtn = $(this).find('input[type="submit"]');
        var statusDiv = $('.local-seo-god-template-status');
        
        if (!templateName || !templatePostId) {
            statusDiv.html('<div class="local-seo-god-error">Please fill out all fields.</div>');
            return;
        }
        
        submitBtn.prop('disabled', true);
        statusDiv.html('<div class="local-seo-god-info">Registering template...</div>');
        
        $.ajax({
            url: localSeoGod.ajaxUrl,
            type: 'POST',
            data: {
                action: 'local_seo_god_register_template',
                nonce: localSeoGod.nonce,
                template_name: templateName,
                template_post_id: templatePostId
            },
            success: function(response) {
                if (response.success) {
                    statusDiv.html('<div class="local-seo-god-success">' + response.data.message + '</div>');
                    // Reload the page to show the new template
                    window.location.reload();
                } else {
                    statusDiv.html('<div class="local-seo-god-error">Error: ' + response.data + '</div>');
                }
            },
            error: function() {
                statusDiv.html('<div class="local-seo-god-error">Server error! Please try again.</div>');
            },
            complete: function() {
                submitBtn.prop('disabled', false);
            }
        });
    });
});
</script> 