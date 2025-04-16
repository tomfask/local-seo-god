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

<div class="hercules-section">
    <h2>Hercules - Tag Based Enhancement</h2>
    
    <p class="description">
        Use this tool to apply tag-based replacements to your existing pages. Select one or more pages, 
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
                        <table>
                            <?php foreach ($template['tags'] as $tag => $description) : ?>
                                <tr>
                                    <td><code><?php echo esc_html($tag); ?></code></td>
                                    <td><?php echo esc_html($description); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            
                            <?php if ($key === 'homepage') : ?>
                                <?php 
                                // Add dynamic service tags
                                for ($i = 1; $i <= count($business_info['services']); $i++) : 
                                ?>
                                    <tr>
                                        <td><code>{Service-<?php echo $i; ?>}</code></td>
                                        <td>Replaced with link to service #<?php echo $i; ?></td>
                                    </tr>
                                <?php endfor; ?>
                                
                                <?php 
                                // Add dynamic area tags
                                for ($i = 1; $i <= count($business_info['service_areas']); $i++) : 
                                ?>
                                    <tr>
                                        <td><code>{area-<?php echo $i; ?>}</code></td>
                                        <td>Replaced with service area #<?php echo $i; ?></td>
                                    </tr>
                                <?php endfor; ?>
                            <?php endif; ?>
                            <tr>
                                <td><code>{Target-Keyword}</code></td>
                                <td>Additional target keywords (each instance gets a different keyword when multiple are entered)</td>
                            </tr>
                            <tr>
                                <td><code>{Business-Description}</code></td>
                                <td>Detailed description of your business</td>
                            </tr>
                            <tr>
                                <td><code>{One-WordLiner}</code></td>
                                <td>Random one-word descriptor (e.g., "Expert", "Professional", "Skilled")</td>
                            </tr>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Preview loading indicator -->
<div id="loading-preview" style="display: none;">
    <img src="<?php echo admin_url('images/spinner.gif'); ?>" alt="Loading...">
    <span>Loading preview...</span>
</div>

<!-- Preview result container -->
<div id="preview-result" style="display: none;"></div>

<!-- AI Content Preview -->
<div class="ai-content-preview" id="ai-content-preview" style="display: none;">
    <div class="ai-preview-header">
        <h3>AI Content Preview</h3>
        <button type="button" class="button button-secondary" id="ai-preview-back">Back to Tags</button>
    </div>
    
    <div class="ai-loading">
        <img src="<?php echo admin_url('images/spinner.gif'); ?>" alt="Loading...">
        <span>Generating AI content... This may take a moment.</span>
    </div>
    
    <div class="ai-error" style="display: none;"></div>
    
    <div id="ai-sections-container"></div>
    
    <div class="ai-controls">
        <button type="button" class="button button-primary" id="ai-apply-content">Apply AI Content</button>
        <button type="button" class="button button-secondary" id="ai-cancel">Cancel</button>
    </div>
</div>

<script id="ai-section-template" type="text/template">
    <div class="ai-section" data-tag="{tag}">
        <div class="ai-section-header">
            <span>{tag_name}</span>
            <div>
                <a href="#" class="ai-toggle-view" data-view="formatted">View Formatted</a>
                <a href="#" class="ai-toggle-view" data-view="raw" style="display: none;">View Raw HTML</a>
            </div>
        </div>
        <div class="ai-section-content">
            <div class="ai-content-raw">{content}</div>
            <div class="ai-content-formatted" style="display: none;"></div>
            
            <div class="ai-controls">
                <button type="button" class="button ai-regenerate">Regenerate</button>
                <button type="button" class="button ai-edit">Edit</button>
            </div>
            
            <div class="ai-regenerate-form" style="display: none;">
                <p><strong>Additional Instructions</strong> (Optional)</p>
                <textarea class="ai-instructions-textarea" placeholder="E.g., Make it more professional, add more details about our services, etc."></textarea>
                <div class="ai-controls">
                    <button type="button" class="button button-primary ai-submit-regenerate">Submit</button>
                    <button type="button" class="button ai-cancel-regenerate">Cancel</button>
                </div>
            </div>
            
            <div class="ai-edit-form" style="display: none;">
                <p><strong>Edit Content</strong></p>
                <textarea class="ai-edit-textarea" style="width: 100%; min-height: 200px;"></textarea>
                <div class="ai-controls">
                    <button type="button" class="button button-primary ai-submit-edit">Save</button>
                    <button type="button" class="button ai-cancel-edit">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</script>

<!-- Add a hidden field to store the AI content data -->
<input type="hidden" id="ai-content-data" name="ai_content_data" value="">

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
    // Variables to store AI content
    let aiContent = {};
    let aiTagsFound = false;
    
    // Function to update the tag preview output
    function updatePreview(preview_data) {
        // ... existing code ...
        
        // Check if there are AI tags
        if (preview_data.has_ai_tags) {
            aiTagsFound = true;
            const aiTagsContainer = $('<div class="ai-tags-container"></div>');
            aiTagsContainer.append('<h3>AI Content Tags Found</h3>');
            aiTagsContainer.append('<p>The following AI tags were found in the content. Click "Generate AI Content" to preview.</p>');
            
            const aiTagsList = $('<ul></ul>');
            $.each(preview_data.ai_tags, function(tag, tagName) {
                aiTagsList.append(`<li><span class="ai-tag"><span class="dashicons dashicons-admin-customizer ai-tag-icon"></span>${tag}</span> - ${tagName}</li>`);
            });
            
            aiTagsContainer.append(aiTagsList);
            
            if (preview_data.ai_enabled) {
                const generateButton = $('<button type="button" class="button button-primary" id="generate-ai-content">Generate AI Content</button>');
                aiTagsContainer.append(generateButton);
            } else {
                aiTagsContainer.append('<div class="notice notice-warning inline"><p>AI content generation is disabled. Please configure your API key in the plugin settings.</p></div>');
            }
            
            $('#preview-result').append(aiTagsContainer);
        }
    }
    
    // Handle Generate AI Content button click
    $(document).on('click', '#generate-ai-content', function(e) {
        e.preventDefault();
        
        const postId = $('#page_id').val();
        const templateName = $('#template').val();
        
        if (!postId) {
            alert('Please select a page first.');
            return;
        }
        
        // Hide the preview result and show AI content preview
        $('#preview-result').hide();
        $('#ai-content-preview').show();
        $('.ai-loading').show();
        $('.ai-error').hide();
        $('#ai-sections-container').empty();
        
        // Make AJAX request to get AI content
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'local_seo_god_get_ai_content_preview',
                nonce: $('#_wpnonce').val(),
                post_id: postId,
                template_name: templateName
            },
            success: function(response) {
                $('.ai-loading').hide();
                
                if (response.success) {
                    // Store the AI content
                    aiContent = response.data;
                    
                    // Render each AI section
                    $.each(aiContent, function(tag, content) {
                        renderAISection(tag, content);
                    });
                } else {
                    $('.ai-error').text(response.data).show();
                }
            },
            error: function() {
                $('.ai-loading').hide();
                $('.ai-error').text('An error occurred while generating AI content. Please try again.').show();
            }
        });
    });
    
    // Render an AI section
    function renderAISection(tag, content) {
        const tagName = tag.replace(/[{}]/g, '');
        const template = $('#ai-section-template').html();
        
        const sectionHtml = template
            .replace('{tag}', tag)
            .replace('{tag_name}', tagName)
            .replace('{content}', content);
        
        const $section = $(sectionHtml);
        $('#ai-sections-container').append($section);
        
        // Initialize the formatted content view
        const $formatted = $section.find('.ai-content-formatted');
        $formatted.html(content);
    }
    
    // Toggle between raw and formatted views
    $(document).on('click', '.ai-toggle-view', function(e) {
        e.preventDefault();
        
        const $this = $(this);
        const view = $this.data('view');
        const $section = $this.closest('.ai-section');
        
        if (view === 'formatted') {
            $section.find('.ai-content-raw').hide();
            $section.find('.ai-content-formatted').show();
            $this.hide();
            $section.find('.ai-toggle-view[data-view="raw"]').show();
        } else {
            $section.find('.ai-content-formatted').hide();
            $section.find('.ai-content-raw').show();
            $this.hide();
            $section.find('.ai-toggle-view[data-view="formatted"]').show();
        }
    });
    
    // Handle regenerate button click
    $(document).on('click', '.ai-regenerate', function() {
        const $section = $(this).closest('.ai-section');
        $section.find('.ai-regenerate-form').show();
        $section.find('.ai-controls').first().hide();
    });
    
    // Handle cancel regenerate button click
    $(document).on('click', '.ai-cancel-regenerate', function() {
        const $section = $(this).closest('.ai-section');
        $section.find('.ai-regenerate-form').hide();
        $section.find('.ai-controls').first().show();
    });
    
    // Handle submit regenerate button click
    $(document).on('click', '.ai-submit-regenerate', function() {
        const $section = $(this).closest('.ai-section');
        const tag = $section.data('tag');
        const instructions = $section.find('.ai-instructions-textarea').val();
        const templateName = $('#template').val();
        
        $section.find('.ai-regenerate-form').hide();
        $section.find('.ai-loading').show();
        
        // Make AJAX request to regenerate content
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'local_seo_god_regenerate_ai_content',
                nonce: $('#_wpnonce').val(),
                tag: tag,
                instructions: instructions,
                template_name: templateName
            },
            success: function(response) {
                $section.find('.ai-loading').hide();
                $section.find('.ai-controls').first().show();
                
                if (response.success) {
                    // Update the content
                    const newContent = response.data.content;
                    aiContent[tag] = newContent;
                    
                    $section.find('.ai-content-raw').text(newContent);
                    $section.find('.ai-content-formatted').html(newContent);
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                $section.find('.ai-loading').hide();
                $section.find('.ai-controls').first().show();
                alert('An error occurred. Please try again.');
            }
        });
    });
    
    // Handle edit button click
    $(document).on('click', '.ai-edit', function() {
        const $section = $(this).closest('.ai-section');
        const content = $section.find('.ai-content-raw').text();
        
        $section.find('.ai-edit-textarea').val(content);
        $section.find('.ai-edit-form').show();
        $section.find('.ai-controls').first().hide();
    });
    
    // Handle cancel edit button click
    $(document).on('click', '.ai-cancel-edit', function() {
        const $section = $(this).closest('.ai-section');
        $section.find('.ai-edit-form').hide();
        $section.find('.ai-controls').first().show();
    });
    
    // Handle submit edit button click
    $(document).on('click', '.ai-submit-edit', function() {
        const $section = $(this).closest('.ai-section');
        const tag = $section.data('tag');
        const content = $section.find('.ai-edit-textarea').val();
        
        // Update the content
        aiContent[tag] = content;
        
        $section.find('.ai-content-raw').text(content);
        $section.find('.ai-content-formatted').html(content);
        
        $section.find('.ai-edit-form').hide();
        $section.find('.ai-controls').first().show();
    });
    
    // Handle back button click
    $(document).on('click', '#ai-preview-back', function() {
        $('#ai-content-preview').hide();
        $('#preview-result').show();
    });
    
    // Handle apply AI content button click
    $(document).on('click', '#ai-apply-content', function() {
        const postId = $('#page_id').val();
        const templateName = $('#template').val();
        
        // Store the AI content data in the hidden field
        $('#ai-content-data').val(JSON.stringify(aiContent));
        
        // Show loading state
        $('.ai-loading').show();
        
        // Make AJAX request to apply the content
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'local_seo_god_apply_ai_content',
                nonce: $('#_wpnonce').val(),
                post_id: postId,
                ai_content: JSON.stringify(aiContent),
                template_name: templateName
            },
            success: function(response) {
                $('.ai-loading').hide();
                
                if (response.success) {
                    // Show success message
                    alert('AI content applied successfully!');
                    
                    // Redirect to the edit URL
                    window.location.href = response.data.edit_url;
                } else {
                    $('.ai-error').text(response.data).show();
                }
            },
            error: function() {
                $('.ai-loading').hide();
                $('.ai-error').text('An error occurred while applying AI content. Please try again.').show();
            }
        });
    });
    
    // Handle cancel button click
    $(document).on('click', '#ai-cancel', function() {
        $('#ai-content-preview').hide();
        $('#preview-result').show();
    });
});
</script> 