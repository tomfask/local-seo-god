<?php
// Prevent direct access
if (!defined('ABSPATH')) exit;

// Get available tag templates
$templates = $this->get_tag_templates();

// Get posts and pages
$args = array(
    'post_type' => array('post', 'page'),
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC'
);
$posts = get_posts($args);
?>

<div class="wrap local-seo-god-wrap">
    <h1><?php echo esc_html($this->plugin_name); ?> - Tag Replacements</h1>
    
    <div class="local-seo-god-tag-replacements">
        <div class="local-seo-god-card">
            <h2>Apply Tag Replacements</h2>
            
            <p class="description">
                Use this tool to apply tag-based replacements to your pages. First, select one or more pages, 
                then choose a tag template to apply. The system will automatically replace tags with your business information.
            </p>
            
            <?php 
            // Check if business info is set up
            $business_info = get_option('local_seo_god_business_info', array());
            $business_setup = !empty($business_info['business_name']) && !empty($business_info['services']) && !empty($business_info['domain']);
            
            if (!$business_setup) : 
            ?>
                <div class="local-seo-god-error-message">
                    <p><strong>Business information is not set up properly.</strong></p>
                    <p>Please go to <a href="<?php echo admin_url('admin.php?page=' . $this->base_name . '_settings'); ?>">Settings &rarr; Business Information</a> to set up your business details before using tag replacements.</p>
                </div>
            <?php else : ?>
                
                <form id="local-seo-god-tag-form" class="local-seo-god-form">
                    <?php wp_nonce_field('local-seo-god-nonce', 'tag_nonce'); ?>
                    
                    <div class="form-group">
                        <label for="post-selector">Select Page(s) to Update:</label>
                        <select id="post-selector" name="post_ids[]" class="regular-text" multiple="multiple" required style="min-height: 150px; width: 100%;">
                            <?php foreach ($posts as $post) : ?>
                                <option value="<?php echo esc_attr($post->ID); ?>">
                                    <?php echo esc_html($post->post_title); ?> 
                                    (<?php echo esc_html(get_post_type_object($post->post_type)->labels->singular_name); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">Hold Ctrl/Cmd to select multiple pages</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="template-selector">Select Tag Template:</label>
                        <select id="template-selector" name="template_name" class="regular-text" required>
                            <option value="">-- Select a template --</option>
                            <?php foreach ($templates as $key => $template) : ?>
                                <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($template['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">Choose which template to apply</p>
                    </div>
                    
                    <div id="template-description" class="form-group" style="display: none;">
                        <div class="template-info"></div>
                    </div>
                    
                    <div class="form-group template-actions">
                        <button type="button" id="preview-replacements" class="button">Preview Replacements</button>
                        <button type="submit" id="apply-replacements" class="button button-primary" disabled>Apply Replacements</button>
                    </div>
                </form>
                
                <div id="replacements-preview" style="display: none;">
                    <h3>Tag Replacements Preview</h3>
                    
                    <div class="preview-content">
                        <div id="replacement-table-container">
                            <h4>Tags & Replacements</h4>
                            <table class="widefat" id="replacement-table">
                                <thead>
                                    <tr>
                                        <th>Tag</th>
                                        <th>Replacement</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Replacement rows will be added here dynamically -->
                                </tbody>
                            </table>
                        </div>
                        
                        <div id="preview-text-container">
                            <div id="preview-title-container">
                                <h4>Title</h4>
                                <table class="widefat">
                                    <tr>
                                        <th style="width: 50%">Original</th>
                                        <th style="width: 50%">With Replacements</th>
                                    </tr>
                                    <tr>
                                        <td id="original-title"></td>
                                        <td id="replaced-title"></td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div id="preview-content-container">
                                <h4>Content Preview (First 500 characters)</h4>
                                <table class="widefat">
                                    <tr>
                                        <th style="width: 50%">Original</th>
                                        <th style="width: 50%">With Replacements</th>
                                    </tr>
                                    <tr>
                                        <td id="original-content"></td>
                                        <td id="replaced-content"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="application-status" style="display: none;"></div>
                
                <div class="local-seo-god-help">
                    <h3>Available Tag Templates</h3>
                    
                    <?php foreach ($templates as $key => $template) : ?>
                        <div class="template-info-card">
                            <h4><?php echo esc_html($template['name']); ?></h4>
                            <p><?php echo esc_html($template['description']); ?></p>
                            
                            <div class="tag-list">
                                <strong>Available Tags:</strong>
                                <ul>
                                    <?php foreach ($template['tags'] as $tag => $description) : ?>
                                        <li><code><?php echo esc_html($tag); ?></code> - <?php echo esc_html($description); ?></li>
                                    <?php endforeach; ?>
                                    
                                    <?php if ($key === 'homepage') : ?>
                                        <?php 
                                        // Add dynamic service tags
                                        for ($i = 1; $i <= count($business_info['services']); $i++) : 
                                        ?>
                                            <li><code>{Service-<?php echo $i; ?>}</code> - Replaced with link to service #<?php echo $i; ?></li>
                                        <?php endfor; ?>
                                        
                                        <?php 
                                        // Add dynamic area tags
                                        for ($i = 1; $i <= count($business_info['service_areas']); $i++) : 
                                        ?>
                                            <li><code>{area-<?php echo $i; ?>}</code> - Replaced with service area #<?php echo $i; ?></li>
                                        <?php endfor; ?>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.local-seo-god-error-message {
    background-color: #f8d7da;
    border-left: 4px solid #dc3545;
    padding: 12px;
    margin: 20px 0;
}

.local-seo-god-error-message p {
    margin: 0;
    padding: 5px 0;
}

.template-info-card {
    background-color: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 3px;
    padding: 15px;
    margin-bottom: 20px;
}

.template-info-card h4 {
    margin-top: 0;
    margin-bottom: 10px;
}

.tag-list {
    margin-top: 10px;
}

.tag-list ul {
    margin-left: 20px;
}

.tag-list code {
    background-color: #f1f1f1;
    padding: 2px 4px;
    border-radius: 3px;
}

.preview-content {
    margin-top: 15px;
}

#replacement-table-container {
    margin-bottom: 20px;
}

#replacement-table td {
    word-break: break-word;
}

#preview-text-container {
    margin-top: 20px;
}

#preview-title-container {
    margin-bottom: 20px;
}

#preview-content-container {
    margin-bottom: 20px;
}

#application-status {
    background-color: #d4edda;
    border-left: 4px solid #28a745;
    padding: 12px;
    margin: 20px 0;
}

.local-seo-god-error {
    background-color: #f8d7da;
    border-left: 4px solid #dc3545;
    padding: 12px;
    margin: 20px 0;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Show template description when template is selected
    $('#template-selector').on('change', function() {
        var templateName = $(this).val();
        
        if (templateName) {
            // Show description based on template
            var templateInfo = '';
            
            <?php foreach ($templates as $key => $template) : ?>
                if (templateName === '<?php echo esc_js($key); ?>') {
                    templateInfo = '<?php echo esc_js($template['description']); ?>';
                }
            <?php endforeach; ?>
            
            $('#template-description').show();
            $('#template-description .template-info').html('<p>' + templateInfo + '</p>');
        } else {
            $('#template-description').hide();
        }
    });
    
    // Preview replacements
    $('#preview-replacements').on('click', function() {
        var postId = $('#post-selector').val();
        var templateName = $('#template-selector').val();
        
        if (!postId || postId.length === 0 || !templateName) {
            alert('Please select at least one page and a template before previewing.');
            return;
        }
        
        // Use the first selected post for preview
        var firstPostId = postId[0];
        
        $(this).prop('disabled', true).text('Loading preview...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'local_seo_god_preview_tag_replacements',
                nonce: '<?php echo wp_create_nonce('local-seo-god-nonce'); ?>',
                post_id: firstPostId,
                template_name: templateName
            },
            success: function(response) {
                if (response.success) {
                    // Show preview section
                    $('#replacements-preview').show();
                    
                    // Clear previous preview content
                    $('#replacement-table tbody').empty();
                    
                    // Add replacements to the table
                    $.each(response.data.replacements, function(tag, replacement) {
                        var row = '<tr>' +
                            '<td><code>' + tag + '</code></td>' +
                            '<td>' + replacement + '</td>' +
                            '</tr>';
                        $('#replacement-table tbody').append(row);
                    });
                    
                    // Update title preview
                    $('#original-title').text(response.data.title.original);
                    $('#replaced-title').text(response.data.title.replaced);
                    
                    // Update content preview (first 500 chars)
                    var originalContent = response.data.content.original;
                    var replacedContent = response.data.content.replaced;
                    
                    if (originalContent.length > 500) {
                        originalContent = originalContent.substring(0, 500) + '...';
                    }
                    
                    if (replacedContent.length > 500) {
                        replacedContent = replacedContent.substring(0, 500) + '...';
                    }
                    
                    $('#original-content').text(originalContent);
                    $('#replaced-content').text(replacedContent);
                    
                    // Enable apply button
                    $('#apply-replacements').prop('disabled', false);
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('Server error while generating preview.');
            },
            complete: function() {
                $('#preview-replacements').prop('disabled', false).text('Preview Replacements');
            }
        });
    });
    
    // Apply replacements
    $('#local-seo-god-tag-form').on('submit', function(e) {
        e.preventDefault();
        
        var postIds = $('#post-selector').val();
        var templateName = $('#template-selector').val();
        
        if (!postIds || postIds.length === 0 || !templateName) {
            alert('Please select at least one page and a template before applying.');
            return;
        }
        
        if (!confirm('Are you sure you want to apply tag replacements to ' + postIds.length + ' page(s)? This action cannot be undone.')) {
            return;
        }
        
        $('#apply-replacements').prop('disabled', true).text('Applying replacements...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'local_seo_god_apply_tag_replacements',
                nonce: '<?php echo wp_create_nonce('local-seo-god-nonce'); ?>',
                post_ids: postIds,
                template_name: templateName
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    var message = 'Successfully updated ' + response.data.updated + ' page(s).';
                    
                    if (response.data.errors.length > 0) {
                        message += '<br><br>Errors:<br>' + response.data.errors.join('<br>');
                    }
                    
                    $('#application-status').html(message).show();
                    
                    // Scroll to status message
                    $('html, body').animate({
                        scrollTop: $('#application-status').offset().top - 100
                    }, 500);
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('Server error while applying replacements.');
            },
            complete: function() {
                $('#apply-replacements').prop('disabled', false).text('Apply Replacements');
            }
        });
    });
});
</script> 