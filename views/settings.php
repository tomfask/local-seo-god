<?php
// Prevent direct access
if (!defined('ABSPATH')) exit;

// Get current unified config
$config = get_option('local_seo_god_config', array(
    'general' => array(
        'default_post_status' => 'publish',
        'page_title_format' => '{keyword} in {location}',
        'enable_auto_linking' => false,
        'auto_link_limit' => 3
    ),
    'business' => array(
        'business_name' => '',
        'gmb_service' => '',
        'target_location' => '',
        'main_keyword' => '',
        'target_keywords' => '',
        'services' => array(),
        'service_areas' => array(),
        'domain' => '',
        'social_instagram' => '',
        'social_facebook' => '',
        'social_gmb' => '',
        'business_description' => ''
    ),
    'ai' => array(
        'openai_api_key' => '',
        'enable_ai_content' => false
    )
));

// Extract individual sections for backward compatibility
$settings = $config['general'];
$business_info = $config['business'];
$ai_settings = $config['ai'];

// Get active tab from URL if available
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
?>

<div class="wrap local-seo-god-wrap">
    <h1><?php echo esc_html($this->plugin_name); ?> - Settings</h1>
    
    <?php if (isset($_GET['updated']) && $_GET['updated'] === 'true'): ?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e('Settings saved successfully!', 'local-seo-god'); ?></p>
    </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_api_key'): ?>
    <div class="notice notice-error is-dismissible">
        <p><?php _e('Invalid OpenAI API key. AI content generation has been disabled.', 'local-seo-god'); ?></p>
    </div>
    <?php endif; ?>
    
    <div class="local-seo-god-settings">
        <div class="local-seo-god-card">
            <div class="local-seo-god-tabs-container">
                <ul class="local-seo-god-tabs nav-tab-wrapper">
                    <li><a href="#general-tab" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>" data-tab="general">General Settings</a></li>
                    <li><a href="#business-tab" class="nav-tab <?php echo $active_tab === 'business-tab' ? 'nav-tab-active' : ''; ?>" data-tab="business-tab">Business Information</a></li>
                    <li><a href="#auto-linking-tab" class="nav-tab <?php echo $active_tab === 'auto-linking-tab' ? 'nav-tab-active' : ''; ?>" data-tab="auto-linking-tab">Auto-Linking Settings</a></li>
                    <li><a href="#advanced-tab" class="nav-tab <?php echo $active_tab === 'advanced-tab' ? 'nav-tab-active' : ''; ?>" data-tab="advanced-tab">Advanced Settings</a></li>
                    <li><a href="#ai-settings" class="nav-tab <?php echo $active_tab === 'ai-settings-tab' ? 'nav-tab-active' : ''; ?>" data-tab="ai-settings-tab">AI Settings</a></li>
                </ul>
                
                <!-- Unified Settings Form -->
                <form method="post" action="options.php" id="local-seo-god-settings-form">
                    <?php settings_fields('local_seo_god_settings_group'); ?>
                    
                    <div id="general-tab" class="local-seo-god-tab-content" <?php echo $active_tab !== 'general' ? 'style="display: none;"' : ''; ?>>
                        <h2>General Settings</h2>
                        
                        <div class="form-group">
                            <label for="default-post-status">Default Post Status:</label>
                            <select id="default-post-status" name="local_seo_god_config[general][default_post_status]" class="regular-text">
                                <option value="publish" <?php selected($settings['default_post_status'], 'publish'); ?>>Published</option>
                                <option value="draft" <?php selected($settings['default_post_status'], 'draft'); ?>>Draft</option>
                                <option value="pending" <?php selected($settings['default_post_status'], 'pending'); ?>>Pending Review</option>
                            </select>
                            <p class="description">Set the default status for newly created pages</p>
                        </div>
                        
                        <div class="form-group">
                            <label for="page-title-format">Default Page Title Format:</label>
                            <input type="text" id="page-title-format" name="local_seo_god_config[general][page_title_format]" class="regular-text" value="<?php echo esc_attr($settings['page_title_format']); ?>">
                            <p class="description">Use {keyword} and {location} placeholders (Example: "{keyword} in {location}")</p>
                        </div>
                    </div>
                    
                    <div id="auto-linking-tab" class="local-seo-god-tab-content" <?php echo $active_tab !== 'auto-linking-tab' ? 'style="display: none;"' : ''; ?>>
                        <h2>Auto-Linking Settings</h2>
                        
                        <div class="form-group">
                            <label for="enable-auto-linking">
                                <input type="checkbox" id="enable-auto-linking" name="local_seo_god_config[general][enable_auto_linking]" value="1" <?php checked($settings['enable_auto_linking'], true); ?>>
                                Enable Auto-Linking
                            </label>
                            <p class="description">Automatically create links when keyword matches are found</p>
                        </div>
                        
                        <div class="form-group auto-link-settings" <?php echo $settings['enable_auto_linking'] ? '' : 'style="display: none;"'; ?>>
                            <label for="auto-link-limit">Maximum Links Per Page:</label>
                            <input type="number" id="auto-link-limit" name="local_seo_god_config[general][auto_link_limit]" class="small-text" value="<?php echo intval($settings['auto_link_limit']); ?>" min="1" max="20">
                            <p class="description">Limit the number of automatic links created per page (1-20)</p>
                        </div>
                    </div>
                    
                    <div id="advanced-tab" class="local-seo-god-tab-content" <?php echo $active_tab !== 'advanced-tab' ? 'style="display: none;"' : ''; ?>>
                        <h2>Advanced Settings</h2>
                        
                        <div class="form-group">
                            <label for="regenerate-security-key">
                                <input type="checkbox" id="regenerate-security-key" name="local_seo_god_config[general][regenerate_key]" value="1">
                                Regenerate Security Key
                            </label>
                            <p class="description">Regenerate the security key used for API access</p>
                        </div>
                        
                        <h3>Export/Import Settings</h3>
                        
                        <div class="form-group">
                            <button type="button" id="export-settings" class="button">Export Settings and Data</button>
                            <p class="description">Download a backup of your settings and data</p>
                        </div>
                        
                        <div class="form-group">
                            <label for="import-settings">Import Settings:</label>
                            <input type="file" id="import-settings" name="import_settings" accept=".json">
                            <p class="description">Upload a previously exported settings file</p>
                        </div>
                    </div>
                    
                    <div id="business-tab" class="local-seo-god-tab-content" <?php echo $active_tab !== 'business-tab' ? 'style="display: none;"' : ''; ?>>
                        <h2>Business Information</h2>
                        <p class="description">Enter your business details below. This information will be used for tag replacements throughout your website.</p>
                        
                        <div class="form-group">
                            <label for="business-name">Business Name:</label>
                            <input type="text" id="business-name" name="local_seo_god_config[business][business_name]" class="regular-text" value="<?php echo esc_attr($business_info['business_name']); ?>" required>
                            <p class="description">Your business name. Tag: {Business-Name}</p>
                        </div>
                        
                        <div class="form-group">
                            <label for="gmb-service">GMB Service (Main Category):</label>
                            <input type="text" id="gmb-service" name="local_seo_god_config[business][gmb_service]" class="regular-text" value="<?php echo esc_attr($business_info['gmb_service']); ?>" required>
                            <p class="description">Your main service category (e.g., Concrete Contractor, Plumber). Tag: {GMB-Service}</p>
                        </div>
                        
                        <div class="form-group">
                            <label for="target-location">Target Location:</label>
                            <input type="text" id="target-location" name="local_seo_god_config[business][target_location]" class="regular-text" value="<?php echo esc_attr($business_info['target_location']); ?>" required>
                            <p class="description">Your main city or area for service. Tag: {Target-Location}</p>
                        </div>
                        
                        <div class="form-group">
                            <label for="main-keyword">Main Keyword:</label>
                            <input type="text" id="main-keyword" name="local_seo_god_config[business][main_keyword]" class="regular-text" value="<?php echo esc_attr($business_info['main_keyword']); ?>" required>
                            <p class="description">Your most important keyword. Tag: {Main-Keyword}</p>
                        </div>
                        
                        <div class="form-group">
                            <label for="target-keywords">Target Keywords:</label>
                            <input type="text" id="target-keywords" name="local_seo_god_config[business][target_keywords]" class="regular-text" value="<?php echo esc_attr($business_info['target_keywords']); ?>" required>
                            <p class="description">Additional target keywords. Tag: {Target-Keyword}</p>
                        </div>
                        
                        <div class="form-group">
                            <label for="services">Services Provided:</label>
                            <div id="services-container">
                                <?php 
                                if (!empty($business_info['services'])) {
                                    foreach ($business_info['services'] as $index => $service) {
                                        $service_num = $index + 1;
                                        echo '<div class="service-item">';
                                        echo '<input type="text" name="local_seo_god_config[business][services][]" value="' . esc_attr($service) . '" class="regular-text" required>';
                                        echo '<button type="button" class="button remove-service">Remove</button>';
                                        echo '<span class="description">Tag: {Service-' . $service_num . '}</span>';
                                        echo '</div>';
                                    }
                                } else {
                                    echo '<div class="service-item">';
                                    echo '<input type="text" name="local_seo_god_config[business][services][]" class="regular-text" required>';
                                    echo '<button type="button" class="button remove-service">Remove</button>';
                                    echo '<span class="description">Tag: {Service-1}</span>';
                                    echo '</div>';
                                }
                                ?>
                            </div>
                            <button type="button" class="button" id="add-service">Add Service</button>
                            <p class="description">List of services you provide. Used for tags like {Service-1}, {Service-2}, etc.</p>
                        </div>
                        
                        <div class="form-group">
                            <label for="service-areas">Service Areas:</label>
                            <div id="areas-container">
                                <?php 
                                if (!empty($business_info['service_areas'])) {
                                    foreach ($business_info['service_areas'] as $index => $area) {
                                        $area_num = $index + 1;
                                        echo '<div class="area-item">';
                                        echo '<input type="text" name="local_seo_god_config[business][service_areas][]" value="' . esc_attr($area) . '" class="regular-text" required>';
                                        echo '<button type="button" class="button remove-area">Remove</button>';
                                        echo '<span class="description">Tag: {area-' . $area_num . '}</span>';
                                        echo '</div>';
                                    }
                                } else {
                                    echo '<div class="area-item">';
                                    echo '<input type="text" name="local_seo_god_config[business][service_areas][]" class="regular-text" required>';
                                    echo '<button type="button" class="button remove-area">Remove</button>';
                                    echo '<span class="description">Tag: {area-1}</span>';
                                    echo '</div>';
                                }
                                ?>
                            </div>
                            <button type="button" class="button" id="add-area">Add Service Area</button>
                            <p class="description">Locations you service. Used for tags like {area-1}, {area-2}, etc.</p>
                        </div>
                        
                        <div class="form-group">
                            <label for="domain">Website Domain Name:</label>
                            <input type="text" id="domain" name="local_seo_god_config[business][domain]" class="regular-text" value="<?php echo esc_attr($business_info['domain']); ?>" required>
                            <p class="description">Your full domain name including extension (e.g., example.com.au). Tag: {domain}</p>
                        </div>
                        
                        <div class="form-group">
                            <label for="business_description">Business Description:</label>
                            <textarea id="business_description" name="local_seo_god_config[business][business_description]" class="large-text" rows="4" placeholder="We are a family owned business with over 30+ years of experience..."><?php echo esc_textarea($business_info['business_description'] ?? ''); ?></textarea>
                            <p class="description">Provide a detailed description of your business. This will be available as {Business-Description} tag.</p>
                        </div>
                        
                        <h3>Social Media Links (Optional)</h3>
                        
                        <div class="form-group">
                            <label for="social-instagram">Instagram:</label>
                            <input type="url" id="social-instagram" name="local_seo_god_config[business][social_instagram]" class="regular-text" value="<?php echo esc_attr($business_info['social_instagram']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="social-facebook">Facebook:</label>
                            <input type="url" id="social-facebook" name="local_seo_god_config[business][social_facebook]" class="regular-text" value="<?php echo esc_attr($business_info['social_facebook']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="social-gmb">GMB Profile:</label>
                            <input type="url" id="social-gmb" name="local_seo_god_config[business][social_gmb]" class="regular-text" value="<?php echo esc_attr($business_info['social_gmb']); ?>">
                        </div>
                    </div>
                    
                    <div id="ai-settings" class="local-seo-god-tab-content" <?php if ($active_tab !== 'ai-settings-tab') echo 'style="display:none;"'; ?>>
                        <h2>AI Content Settings</h2>
                    
                        <div class="local-seo-god-settings-container">
                            <div class="local-seo-god-settings-card">
                                <div class="local-seo-god-settings-card-header">
                                    <h3>OpenAI Settings</h3>
                                </div>
                                <div class="local-seo-god-settings-card-body">
                                    <div class="form-group">
                                        <label for="openai-api-key">OpenAI API Key</label>
                                        <input type="text" name="local_seo_god_config[ai][openai_api_key]" id="openai-api-key" class="regular-text" value="<?php echo esc_attr($ai_settings['openai_api_key'] ?? ''); ?>">
                                        <p class="description">Enter your OpenAI API key to enable AI content generation.</p>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="enable-ai-content">
                                            <input type="checkbox" name="local_seo_god_config[ai][enable_ai_content]" id="enable-ai-content" value="1" <?php checked(isset($ai_settings['enable_ai_content']) && $ai_settings['enable_ai_content']); ?>>
                                            Enable AI Content Generation
                                        </label>
                                        <p class="description">When enabled, content with AI tags will be generated using OpenAI.</p>
                                    </div>
                                    
                                    <div class="form-group">
                                        <h4>Available AI Tags:</h4>
                                        <ul>
                                            <li><code>{ai-home-introduction}</code> - Generate an introduction for home pages</li>
                                            <li><code>{ai-service-overview}</code> - Generate service overview content</li>
                                            <li><code>{ai-why-us}</code> - Generate "Why Choose Us" section</li>
                                            <li><code>{ai-why-us-section}</code> - Generate "Why Choose Us" section with more detail</li>
                                            <li><code>{ai-service-faq-title-1}</code> to <code>{ai-service-faq-title-5}</code> - Generate FAQ questions</li>
                                            <li><code>{ai-service-faq-answer-1}</code> to <code>{ai-service-faq-answer-5}</code> - Generate FAQ answers</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <p class="submit">
                        <input type="submit" name="submit" id="submit" class="button button-primary" value="Save All Settings">
                        <span class="spinner" style="float: none; margin-top: 0;"></span>
                        <span class="save-status"></span>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Handle tab switching
    $('.local-seo-god-tabs a').on('click', function(e) {
        e.preventDefault();
        
        // Update active tab
        $('.local-seo-god-tabs a').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        // Show active content
        var targetId = $(this).attr('href');
        $('.local-seo-god-tab-content').hide();
        $(targetId).show();
        
        // Update URL with tab parameter without page reload
        var tab = $(this).data('tab');
        if (history.pushState) {
            var newUrl = window.location.href.split('&tab=')[0] + '&tab=' + tab;
            window.history.pushState({path: newUrl}, '', newUrl);
        }
    });
    
    // Toggle auto-link settings
    $('#enable-auto-linking').on('change', function() {
        if ($(this).is(':checked')) {
            $('.auto-link-settings').show();
        } else {
            $('.auto-link-settings').hide();
        }
    });
    
    // Add visual feedback for form submission
    $('#local-seo-god-settings-form').on('submit', function() {
        var $form = $(this);
        var $spinner = $form.find('.spinner');
        var $status = $form.find('.save-status');
        
        // Show spinner
        $spinner.addClass('is-active');
        
        // Disable submit button
        $form.find('input[type="submit"]').prop('disabled', true);
        
        // Clear previous status
        $status.text('').removeClass('success error');
        
        // Show saving message
        $status.text('Saving...').addClass('saving');
    });
    
    // Export settings
    $('#export-settings').on('click', function() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'local_seo_god_export_settings',
                nonce: '<?php echo wp_create_nonce('local_seo_god_export_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    // Create a download link
                    var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(response.data));
                    var downloadAnchorNode = document.createElement('a');
                    downloadAnchorNode.setAttribute("href", dataStr);
                    downloadAnchorNode.setAttribute("download", "local_seo_god_settings_" + new Date().toISOString().slice(0,10) + ".json");
                    document.body.appendChild(downloadAnchorNode);
                    downloadAnchorNode.click();
                    downloadAnchorNode.remove();
                }
            }
        });
    });
    
    // Add service
    $('#add-service').on('click', function() {
        var serviceCount = $('.service-item').length + 1;
        var newService = `
            <div class="service-item">
                <input type="text" name="local_seo_god_config[business][services][]" class="regular-text" required>
                <button type="button" class="button remove-service">Remove</button>
                <span class="description">Tag: {Service-${serviceCount}}</span>
            </div>
        `;
        $('#services-container').append(newService);
    });
    
    // Remove service
    $(document).on('click', '.remove-service', function() {
        if ($('.service-item').length > 1) {
            $(this).parent().remove();
            // Update tag numbers
            $('.service-item').each(function(index) {
                $(this).find('.description').text('Tag: {Service-' + (index + 1) + '}');
            });
        } else {
            alert('You must have at least one service.');
        }
    });
    
    // Add service area
    $('#add-area').on('click', function() {
        var areaCount = $('.area-item').length + 1;
        var newArea = `
            <div class="area-item">
                <input type="text" name="local_seo_god_config[business][service_areas][]" class="regular-text" required>
                <button type="button" class="button remove-area">Remove</button>
                <span class="description">Tag: {area-${areaCount}}</span>
            </div>
        `;
        $('#areas-container').append(newArea);
    });
    
    // Remove service area
    $(document).on('click', '.remove-area', function() {
        if ($('.area-item').length > 1) {
            $(this).parent().remove();
            // Update tag numbers
            $('.area-item').each(function(index) {
                $(this).find('.description').text('Tag: {area-' + (index + 1) + '}');
            });
        } else {
            alert('You must have at least one service area.');
        }
    });
    
    // Check if we should activate a tab based on URL
    var urlParams = new URLSearchParams(window.location.search);
    var tabParam = urlParams.get('tab');
    if (tabParam) {
        $('.local-seo-god-tabs a[data-tab="' + tabParam + '"]').trigger('click');
    }
});
</script>

<style>
.spinner.is-active {
    visibility: visible;
}
.save-status {
    margin-left: 10px;
    font-weight: bold;
}
.save-status.saving {
    color: #0073aa;
}
.save-status.success {
    color: #46b450;
}
.save-status.error {
    color: #dc3232;
}
</style> 