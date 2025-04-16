<?php
// Prevent direct access
if (!defined('ABSPATH')) exit;

global $wpdb;

// Get registered templates
$templates_table = $wpdb->prefix . 'lsg_templates';
$templates = $wpdb->get_results(
    "SELECT * FROM $templates_table ORDER BY template_name ASC"
);

// Get existing pages/posts for template selection
$args = array(
    'post_type' => array('page', 'post'),
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC'
);
$posts = get_posts($args);

// Get business information
$business_info = get_option('local_seo_god_business_info', array());
$has_services = !empty($business_info['services']) && is_array($business_info['services']) && count($business_info['services']) > 0;
$has_areas = !empty($business_info['service_areas']) && is_array($business_info['service_areas']) && count($business_info['service_areas']) > 0;
$has_domain = !empty($business_info['domain']);
$has_target_location = !empty($business_info['target_location']);
$has_main_keyword = !empty($business_info['main_keyword']);
$has_gmb_service = !empty($business_info['gmb_service']);
$business_info_complete = $has_services && $has_areas && $has_domain && $has_target_location && $has_main_keyword && $has_gmb_service;
?>

<div class="zeus-section">
    <h2>Zeus Mode - Bulk Page Generation</h2>
    
    <p class="description">
        Use the godly powers of Zeus to generate multiple location-specific landing pages at once.
        Select an existing page to use as a template, then choose a formula type to create multiple variations.
    </p>
    
    <?php if (!$business_info_complete) : ?>
    <div class="local-seo-god-notice notice-warning">
        <p><strong>Business information is incomplete.</strong> Please go to <a href="<?php echo admin_url('admin.php?page=' . $this->base_name . '_settings'); ?>">Settings</a> to provide all required business details:</p>
        <ul>
            <?php if (!$has_services) : ?><li>Services provided</li><?php endif; ?>
            <?php if (!$has_areas) : ?><li>Service areas</li><?php endif; ?>
            <?php if (!$has_domain) : ?><li>Website domain</li><?php endif; ?>
            <?php if (!$has_target_location) : ?><li>Target location</li><?php endif; ?>
            <?php if (!$has_main_keyword) : ?><li>Main keyword</li><?php endif; ?>
            <?php if (!$has_gmb_service) : ?><li>GMB service</li><?php endif; ?>
        </ul>
    </div>
    <?php else : ?>
    
    <!-- Tabs -->
    <div class="zeus-tabs">
        <ul class="zeus-tab-links">
            <li class="active"><a href="#select-template" class="zeus-tab">1. Select Page Template</a></li>
            <li><a href="#choose-formula" class="zeus-tab">2. Choose Formula</a></li>
            <li><a href="#review-generate" class="zeus-tab">3. Review & Generate</a></li>
        </ul>
        
        <div class="zeus-tab-content">
            <!-- Select Template Tab -->
            <div id="select-template" class="zeus-tab-pane active">
                <h3>Select Existing Page as Template</h3>
                <p>Choose an existing WordPress page to use as the base for your generated pages. The page should contain appropriate tags that will be replaced in the generated pages.</p>
                
                <form id="zeus-template-form" class="local-seo-god-form">
                    <div class="form-group">
                        <label for="source-page">Select Source Page:</label>
                        <select id="source-page" name="source_page_id" class="regular-text" required>
                            <option value="">-- Select a page or post --</option>
                            <?php foreach ($posts as $post) : ?>
                                <option value="<?php echo esc_attr($post->ID); ?>">
                                    <?php echo esc_html($post->post_title); ?> 
                                    (<?php echo esc_html(get_post_type_object($post->post_type)->labels->singular_name); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">Select an existing page/post to use as your template</p>
                    </div>
                    
                    <div id="selected-template-preview" style="display: none;">
                        <h4>Selected Template</h4>
                        <div class="template-preview-content">
                            <div class="preview-title"></div>
                            <div class="preview-content"></div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" id="proceed-to-formula" class="button button-primary" disabled>Continue to Formula Selection</button>
                    </div>
                </form>
            </div>
            
            <!-- Choose Formula Tab -->
            <div id="choose-formula" class="zeus-tab-pane">
                <h3>Choose Bulk Generation Formula</h3>
                <p>Select which formula to use for generating your pages. This determines how pages will be created and what URL structure they'll follow.</p>
                
                <div class="formula-options">
                    <div class="formula-option" id="formula-service-location">
                        <div class="formula-header">
                            <h4>Service|TargetLocation</h4>
                            <span class="formula-example">"Concrete Driveways Melbourne"</span>
                        </div>
                        <div class="formula-description">
                            <p>Creates one page per service offered, focused on your main target location.</p>
                            <ul>
                                <li><strong>Pages to create:</strong> <span id="service-location-count"><?php echo count($business_info['services']); ?></span></li>
                                <li><strong>URL format:</strong> www.yourdomain.com/service-targetlocation</li>
                                <li><strong>Title format:</strong> Service TargetLocation | GMB Service Near Me</li>
                            </ul>
                        </div>
                        <div class="formula-action">
                            <button type="button" class="select-formula button" data-formula="service-location">Select</button>
                        </div>
                    </div>
                    
                    <div class="formula-option" id="formula-keyword-area">
                        <div class="formula-header">
                            <h4>MainKeyword|ServiceArea</h4>
                            <span class="formula-example">"Concreter Mount Martha"</span>
                        </div>
                        <div class="formula-description">
                            <p>Creates one page per service area, focused on your main keyword.</p>
                            <ul>
                                <li><strong>Pages to create:</strong> <span id="keyword-area-count"><?php echo count($business_info['service_areas']); ?></span></li>
                                <li><strong>URL format:</strong> www.yourdomain.com/area-mainkeyword</li>
                                <li><strong>Title format:</strong> One-WordLiner MainKeyword Area | GMB Service Near Me</li>
                            </ul>
                        </div>
                        <div class="formula-action">
                            <button type="button" class="select-formula button" data-formula="keyword-area">Select</button>
                        </div>
                    </div>
                    
                    <div class="formula-option" id="formula-service-area">
                        <div class="formula-header">
                            <h4>Service|ServiceArea</h4>
                            <span class="formula-example">"Concrete Driveways Mount Martha"</span>
                        </div>
                        <div class="formula-description">
                            <p>Creates a page for each service in each service area (Service × ServiceArea).</p>
                            <ul>
                                <li><strong>Pages to create:</strong> <span id="service-area-count"><?php echo count($business_info['services']) * count($business_info['service_areas']); ?></span></li>
                                <li><strong>URL format:</strong> www.yourdomain.com/service-area</li>
                                <li><strong>Title format:</strong> Service Area | GMB Service Near Me</li>
                            </ul>
                        </div>
                        <div class="formula-action">
                            <button type="button" class="select-formula button" data-formula="service-area">Select</button>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions formula-actions">
                    <button type="button" id="back-to-template" class="button">Back to Template Selection</button>
                    <button type="button" id="proceed-to-review" class="button button-primary" disabled>Continue to Review</button>
                </div>
            </div>
            
            <!-- Review & Generate Tab -->
            <div id="review-generate" class="zeus-tab-pane">
                <h3>Review & Generate Pages</h3>
                <div id="generation-review">
                    <div class="generation-summary">
                        <h4>Summary</h4>
                        <div class="review-details">
                            <p><strong>Template Page:</strong> <span id="review-template-name"></span></p>
                            <p><strong>Formula Type:</strong> <span id="review-formula-type"></span></p>
                            <p><strong>Pages to Generate:</strong> <span id="review-page-count"></span></p>
                        </div>
                    </div>
                    
                    <div class="generation-preview">
                        <h4>Pages to Generate</h4>
                        <div id="preview-legend">
                            <!-- Tag legend will be dynamically inserted here -->
                        </div>
                        <div class="preview-pages-list">
                            <table class="widefat">
                                <thead>
                                    <tr>
                                        <th>Page Title</th>
                                        <th>URL</th>
                                        <th>Tags Used</th>
                                    </tr>
                                </thead>
                                <tbody id="preview-pages-table">
                                    <!-- Dynamically filled -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="form-actions review-actions">
                        <button type="button" id="back-to-formula" class="button">Back to Formula Selection</button>
                        <button type="button" id="start-generation" class="button button-primary button-hero">
                            <span class="zeus-lightning">⚡</span> Generate Pages <span class="zeus-lightning">⚡</span>
                        </button>
                    </div>
                </div>
                
                <div id="generation-progress" style="display: none;">
                    <h4>Generating Pages...</h4>
                    <div class="progress-bar-container">
                        <div class="progress-bar"></div>
                    </div>
                    <p class="progress-status">Generating page <span class="current-page">0</span> of <span class="total-pages">0</span></p>
                </div>
                
                <div id="generation-results" style="display: none;">
                    <h4>Generation Complete</h4>
                    <div class="results-summary"></div>
                    <div class="created-pages-list">
                        <table class="widefat">
                            <thead>
                                <tr>
                                    <th>Page Title</th>
                                    <th>URL</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="created-pages-table">
                                <!-- Dynamically filled -->
                            </tbody>
                        </table>
                    </div>
                    <div class="form-actions results-actions">
                        <button type="button" id="restart-generation" class="button button-primary">Start Over</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.zeus-tabs {
    margin-top: 20px;
}

.zeus-tab-links {
    margin: 0;
    padding: 0;
    list-style: none;
    display: flex;
    border-bottom: 1px solid #ccc;
}

.zeus-tab-links li {
    margin: 0;
    margin-right: 8px;
    display: inline-block;
}

.zeus-tab-links a {
    display: block;
    padding: 10px 15px;
    text-decoration: none;
    color: #555;
    border: 1px solid #ccc;
    border-bottom: none;
    background: #f7f7f7;
    border-radius: 3px 3px 0 0;
}

.zeus-tab-links li.active a {
    background: #fff;
    border-bottom: 1px solid #fff;
    margin-bottom: -1px;
    color: #23282d;
    font-weight: bold;
}

.zeus-tab-pane {
    display: none;
    padding: 20px;
    border: 1px solid #ccc;
    border-top: none;
}

.zeus-tab-pane.active {
    display: block;
}

/* Tag Legend Styling */
.tag-legend {
    margin-bottom: 20px;
    padding: 15px;
    background: #f9f9f9;
    border: 1px solid #e5e5e5;
    border-radius: 4px;
}

.tag-legend h5 {
    margin-top: 0;
    margin-bottom: 10px;
    font-size: 14px;
}

ul.tag-list {
    margin: 0;
    padding: 0;
    list-style: none;
    display: flex;
    flex-wrap: wrap;
}

ul.tag-list li {
    margin: 0 15px 10px 0;
    display: flex;
    align-items: center;
}

.tag-color {
    display: inline-block;
    width: 16px;
    height: 16px;
    margin-right: 6px;
    border-radius: 3px;
}

.page-tag {
    display: inline-block;
    padding: 3px 6px;
    margin: 2px;
    border-radius: 3px;
    color: white;
    font-size: 12px;
    font-weight: bold;
}

.error-message {
    color: #d63638;
    padding: 10px;
    background-color: #ffebe8;
    border: 1px solid #c00;
    border-radius: 3px;
}

.error-header {
    font-weight: bold;
    color: #d63638;
    background: #ffebe8;
    padding: 8px;
    text-align: left;
}

.formula-option {
    border: 1px solid #ddd;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
    transition: all 0.3s ease;
}

.formula-option.selected {
    border-color: #2271b1;
    box-shadow: 0 0 0 1px #2271b1;
}

.formula-option:hover {
    background: #f8f8f8;
}

.formula-header {
    margin-bottom: 10px;
}

.formula-example {
    color: #757575;
    font-size: 13px;
    font-style: italic;
    display: block;
    margin-top: 5px;
}

.formula-description {
    margin-bottom: 15px;
}

.formula-actions {
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}

.spinner.is-active {
    visibility: visible;
    display: inline-block;
}

.zeus-lightning {
    color: #f9a825;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
}

.generation-summary {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.preview-pages-list {
    max-height: 500px;
    overflow-y: auto;
    margin-bottom: 20px;
    border: 1px solid #e5e5e5;
}

.generation-preview h4 {
    margin-top: 20px;
}

.alternate {
    background-color: #f9f9f9;
}

.review-actions {
    margin-top: 30px;
}

.progress-bar-container {
    height: 20px;
    background-color: #f0f0f0;
    border-radius: 4px;
    margin: 15px 0;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background-color: #2271b1;
    width: 0%;
    transition: width 0.3s ease;
}

.progress-status {
    text-align: center;
    font-weight: bold;
}

.created-pages-list {
    margin: 20px 0;
    max-height: 500px;
    overflow-y: auto;
}

#start-generation {
    font-size: 16px;
    text-align: center;
    background-color: #8e44ad;
    border-color: #7d3c98;
}

#start-generation:hover {
    background-color: #9b59b6;
    border-color: #8e44ad;
}

.form-actions {
    margin-top: 20px;
    display: flex;
    justify-content: space-between;
}

.form-actions button:only-child {
    margin-left: auto;
}

.formula-actions, .review-actions, .results-actions {
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.local-seo-god-notice {
    background: #fff8e5;
    border-left: 4px solid #ffb900;
    padding: 12px;
    margin: 20px 0;
}

@media (max-width: 782px) {
    .zeus-tab-links {
        flex-direction: column;
    }
    
    .zeus-tab-links li {
        margin-bottom: 5px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Variables to store state
    var selectedTemplateId = '';
    var selectedTemplateTitle = '';
    var selectedFormula = '';
    var formulaLabels = {
        'service-location': 'Service|TargetLocation',
        'keyword-area': 'MainKeyword|ServiceArea',
        'service-area': 'Service|ServiceArea'
    };
    var pagesToGenerate = [];
    
    // Tab functionality
    function switchTab(tabId) {
        $('.zeus-tab-links li').removeClass('active');
        $('.zeus-tab-links li a[href="#' + tabId + '"]').parent().addClass('active');
        $('.zeus-tab-pane').removeClass('active').hide();
        $('#' + tabId).addClass('active').show();
    }
    
    $('.zeus-tab').on('click', function(e) {
        e.preventDefault();
        var targetTab = $(this).attr('href').substring(1);
        switchTab(targetTab);
    });
    
    // Source page selection
    $('#source-page').on('change', function() {
        var pageId = $(this).val();
        selectedTemplateId = pageId;
        
        if (pageId) {
            // Load preview of selected page
            $('#selected-template-preview .preview-title, #selected-template-preview .preview-content').empty();
            $('#selected-template-preview').show().find('.preview-content').html('<p>Loading preview...</p>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'local_seo_god_get_page_preview',
                    nonce: localSeoGod.nonce,
                    page_id: pageId
                },
                success: function(response) {
                    if (response.success) {
                        $('#selected-template-preview .preview-title').html(response.data.title);
                        $('#selected-template-preview .preview-content').html(response.data.content);
                        selectedTemplateTitle = response.data.title;
                        $('#proceed-to-formula').prop('disabled', false);
                    } else {
                        alert('Error: ' + response.data);
                        $('#proceed-to-formula').prop('disabled', true);
                    }
                },
                error: function() {
                    alert('Server error while loading page preview.');
                    $('#proceed-to-formula').prop('disabled', true);
                }
            });
        } else {
            $('#selected-template-preview').hide();
            $('#proceed-to-formula').prop('disabled', true);
        }
    });
    
    // Continue to formula selection
    $('#proceed-to-formula').on('click', function() {
        switchTab('choose-formula');
    });
    
    // Back to template selection
    $('#back-to-template').on('click', function() {
        switchTab('select-template');
    });
    
    // Select formula
    $('.select-formula').on('click', function() {
        var formula = $(this).data('formula');
        selectedFormula = formula;
        
        $('.formula-option').removeClass('selected');
        $('#formula-' + formula).addClass('selected');
        $('#proceed-to-review').prop('disabled', false);
    });
    
    // Continue to review
    $('#proceed-to-review').on('click', function() {
        // Generate preview of pages to be created
        generatePagesPreview();
        
        // Update review info
        $('#review-template-name').text(selectedTemplateTitle);
        $('#review-formula-type').text(formulaLabels[selectedFormula]);
        $('#review-page-count').text(pagesToGenerate.length);
        
        // Show review tab
        switchTab('review-generate');
    });
    
    // Back to formula selection
    $('#back-to-formula').on('click', function() {
        switchTab('choose-formula');
    });
    
    // Generate pages preview based on selected formula
    function generatePagesPreview() {
        pagesToGenerate = [];
        var businessInfo = <?php echo json_encode($business_info); ?>;
        
        // Reset previous data
        $('#preview-pages-table').html('<tr><td colspan="3"><div class="spinner is-active" style="float:none;margin:0 auto;"></div> Loading preview...</td></tr>');
        $('#review-page-count').text('0');
        $('#preview-legend').empty();
        
        // Validate business info is complete
        if (!businessInfo || Object.keys(businessInfo).length === 0) {
            $('#preview-pages-table').html('<tr><td colspan="3" class="error-message">Business information is incomplete. Please update your settings first.</td></tr>');
            return;
        }
        
        // Validate required fields exist
        var domain = businessInfo.domain || '';
        var targetLocation = businessInfo.target_location || '';
        var mainKeyword = businessInfo.main_keyword || '';
        var gmbService = businessInfo.gmb_service || '';
        var services = businessInfo.services || [];
        var areas = businessInfo.service_areas || [];
        
        var missingFields = [];
        if (!domain) missingFields.push('Domain');
        if (!targetLocation) missingFields.push('Target Location');
        if (!mainKeyword) missingFields.push('Main Keyword');
        if (!gmbService) missingFields.push('GMB Service');
        if (services.length === 0) missingFields.push('Services');
        if (areas.length === 0) missingFields.push('Service Areas');
        
        if (missingFields.length > 0) {
            $('#preview-pages-table').html('<tr><td colspan="3" class="error-message">Missing required business information: ' + 
                missingFields.join(', ') + '. Please update your <a href="<?php echo admin_url('admin.php?page=' . $this->base_name . '_settings'); ?>">settings</a>.</td></tr>');
            return;
        }
        
        // Make AJAX request to get business info with latest data
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'local_seo_god_get_business_info',
                nonce: localSeoGod.nonce
            },
            success: function(response) {
                if (!response || !response.success || !response.data) {
                    $('#preview-pages-table').html('<tr><td colspan="3" class="error-message">Error loading business information.</td></tr>');
                    return;
                }
                
                // Use the latest business info from the server
                businessInfo = response.data;
                domain = businessInfo.domain || 'yourdomain.com';
                targetLocation = businessInfo.target_location || 'Location';
                mainKeyword = businessInfo.main_keyword || 'Keyword';
                gmbService = businessInfo.gmb_service || 'Service';
                services = businessInfo.services || [];
                areas = businessInfo.service_areas || [];
                
                // Set of all tags used for color coding
                var allTags = new Set();
                
                switch(selectedFormula) {
                    case 'service-location':
                        if (services.length === 0) {
                            $('#preview-pages-table').html('<tr><td colspan="3" class="error-message">No services defined in your business settings.</td></tr>');
                            return;
                        }
                        
                        // Service|TargetLocation formula
                        services.forEach(function(service, index) {
                            var serviceNum = index + 1;
                            var title = service + ' ' + targetLocation + ' | ' + gmbService + ' Near Me';
                            var url = 'https://www.' + domain + '/' + service.toLowerCase().replace(/\s+/g, '-') + '-' + targetLocation.toLowerCase().replace(/\s+/g, '-');
                            
                            // Track tags used
                            allTags.add('Service-' + serviceNum);
                            allTags.add('Target-Location');
                            allTags.add('GMB-Service');
                            
                            pagesToGenerate.push({
                                title: title,
                                url: url,
                                service_index: serviceNum,
                                area_index: null,
                                formula: 'service-location',
                                tags: ['Service-' + serviceNum, 'Target-Location', 'GMB-Service']
                            });
                        });
                        break;
                        
                    case 'keyword-area':
                        if (areas.length === 0) {
                            $('#preview-pages-table').html('<tr><td colspan="3" class="error-message">No service areas defined in your business settings.</td></tr>');
                            return;
                        }
                        
                        // MainKeyword|ServiceArea formula
                        areas.forEach(function(area, index) {
                            var areaNum = index + 1;
                            var title = '[One-WordLiner] ' + mainKeyword + ' ' + area + ' | ' + gmbService + ' Near Me';
                            var url = 'https://www.' + domain + '/' + area.toLowerCase().replace(/\s+/g, '-') + '-' + mainKeyword.toLowerCase().replace(/\s+/g, '-');
                            
                            // Track tags used
                            allTags.add('One-WordLiner');
                            allTags.add('Main-Keyword');
                            allTags.add('area-' + areaNum);
                            allTags.add('GMB-Service');
                            
                            pagesToGenerate.push({
                                title: title,
                                url: url,
                                service_index: null,
                                area_index: areaNum,
                                formula: 'keyword-area',
                                tags: ['One-WordLiner', 'Main-Keyword', 'area-' + areaNum, 'GMB-Service']
                            });
                        });
                        break;
                        
                    case 'service-area':
                        if (services.length === 0 || areas.length === 0) {
                            $('#preview-pages-table').html('<tr><td colspan="3" class="error-message">Both services and service areas must be defined in your business settings.</td></tr>');
                            return;
                        }
                        
                        // Service|ServiceArea formula
                        services.forEach(function(service, serviceIndex) {
                            var serviceNum = serviceIndex + 1;
                            
                            areas.forEach(function(area, areaIndex) {
                                var areaNum = areaIndex + 1;
                                var title = service + ' ' + area + ' | ' + gmbService + ' Near Me';
                                var url = 'https://www.' + domain + '/' + service.toLowerCase().replace(/\s+/g, '-') + '-' + area.toLowerCase().replace(/\s+/g, '-');
                                
                                // Track tags used
                                allTags.add('Service-' + serviceNum);
                                allTags.add('area-' + areaNum);
                                allTags.add('GMB-Service');
                                
                                pagesToGenerate.push({
                                    title: title,
                                    url: url,
                                    service_index: serviceNum,
                                    area_index: areaNum,
                                    formula: 'service-area',
                                    tags: ['Service-' + serviceNum, 'area-' + areaNum, 'GMB-Service']
                                });
                            });
                        });
                        break;
                }
                
                // Update the page count in the summary
                $('#review-page-count').text(pagesToGenerate.length);
                
                // Render the preview table with color-coded tags
                renderPreviewTable(pagesToGenerate, Array.from(allTags));
            },
            error: function() {
                $('#preview-pages-table').html('<tr><td colspan="3" class="error-message">Error loading business information. Please try again.</td></tr>');
            }
        });
    }
    
    // Function to render the preview table with color-coded tags
    function renderPreviewTable(pages, allTags) {
        if (!pages || pages.length === 0) {
            $('#preview-pages-table').html('<tr><td colspan="3">No pages to generate with the current selection.</td></tr>');
            return;
        }
        
        // Generate color codes for each tag
        var colors = [
            '#f44336', '#e91e63', '#9c27b0', '#673ab7', '#3f51b5', '#2196f3', '#03a9f4', 
            '#00bcd4', '#009688', '#4caf50', '#8bc34a', '#cddc39', '#ffeb3b', '#ffc107', '#ff9800'
        ];
        
        var tagColors = {};
        allTags.forEach(function(tag, index) {
            tagColors[tag] = colors[index % colors.length];
        });
        
        // Create legend for tag colors
        var legendHtml = '<div class="tag-legend">';
        legendHtml += '<h5>Tag Legend:</h5>';
        legendHtml += '<ul class="tag-list">';
        
        allTags.forEach(function(tag) {
            var displayTag = '{' + tag + '}';
            legendHtml += '<li><span class="tag-color" style="background-color: ' + tagColors[tag] + ';"></span> ' + displayTag + '</li>';
        });
        
        legendHtml += '</ul></div>';
        $('#preview-legend').html(legendHtml);
        
        // Create the table rows
        var tableHtml = '';
        pages.forEach(function(page, index) {
            tableHtml += '<tr' + (index % 2 === 0 ? ' class="alternate"' : '') + '>';
            
            // Title column
            tableHtml += '<td>' + page.title + '</td>';
            
            // URL column
            tableHtml += '<td><a href="' + page.url + '" target="_blank">' + page.url + '</a></td>';
            
            // Tags column
            tableHtml += '<td>';
            if (page.tags && page.tags.length > 0) {
                page.tags.forEach(function(tag) {
                    tableHtml += '<span class="page-tag" style="background-color: ' + tagColors[tag] + ';">{' + tag + '}</span>';
                });
            }
            tableHtml += '</td>';
            
            tableHtml += '</tr>';
        });
        
        $('#preview-pages-table').html(tableHtml);
    }
    
    // Start page generation
    $('#start-generation').on('click', function() {
        if (pagesToGenerate.length === 0) {
            alert('No pages to generate. Please go back and select a different formula.');
            return;
        }
        
        if (!confirm('Are you sure you want to generate ' + pagesToGenerate.length + ' pages? This cannot be undone.')) {
            return;
        }
        
        // Show progress UI
        $('#generation-review').hide();
        $('#generation-progress').show();
        $('.total-pages').text(pagesToGenerate.length);
        
        // Start the generation process
        generatePages();
    });
    
    // Function to handle the page generation process
    function generatePages() {
        var totalPages = pagesToGenerate.length;
        var currentPage = 0;
        var createdPages = [];
        var errors = [];
        
        // Process pages one by one
        function processNextPage() {
            if (currentPage >= totalPages) {
                // All pages processed, show results
                showResults(createdPages, errors);
                return;
            }
            
            var pageData = pagesToGenerate[currentPage];
            currentPage++;
            
            // Update progress UI
            $('.current-page').text(currentPage);
            $('.progress-bar').css('width', (currentPage / totalPages * 100) + '%');
            
            // Create the page via AJAX
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'local_seo_god_create_bulk_page',
                    nonce: localSeoGod.nonce,
                    template_page_id: selectedTemplateId,
                    service_index: pageData.service_index,
                    area_index: pageData.area_index,
                    formula: pageData.formula
                },
                success: function(response) {
                    if (response.success) {
                        createdPages.push(response.data);
                    } else {
                        errors.push({
                            title: pageData.title,
                            error: response.data
                        });
                    }
                    
                    // Process next page
                    processNextPage();
                },
                error: function() {
                    errors.push({
                        title: pageData.title,
                        error: 'Server error while creating page'
                    });
                    
                    // Process next page
                    processNextPage();
                }
            });
        }
        
        // Start processing
        processNextPage();
    }
    
    // Show results of page generation
    function showResults(createdPages, errors) {
        $('#generation-progress').hide();
        $('#generation-results').show();
        
        var summaryHtml = '<p>Successfully created ' + createdPages.length + ' pages.';
        if (errors.length > 0) {
            summaryHtml += ' ' + errors.length + ' pages could not be created.';
        }
        summaryHtml += '</p>';
        
        $('.results-summary').html(summaryHtml);
        
        // Show created pages
        var tableHtml = '';
        createdPages.forEach(function(page) {
            tableHtml += '<tr>';
            tableHtml += '<td>' + page.title + '</td>';
            tableHtml += '<td><a href="' + page.permalink + '" target="_blank">' + page.permalink + '</a></td>';
            tableHtml += '<td><a href="' + page.edit_url + '" target="_blank">Edit</a> | <a href="' + page.permalink + '" target="_blank">View</a></td>';
            tableHtml += '</tr>';
        });
        
        // Show errors if any
        if (errors.length > 0) {
            tableHtml += '<tr><td colspan="3" class="error-header">The following pages could not be created:</td></tr>';
            errors.forEach(function(error) {
                tableHtml += '<tr class="error-row">';
                tableHtml += '<td>' + error.title + '</td>';
                tableHtml += '<td colspan="2" class="error-message">' + error.error + '</td>';
                tableHtml += '</tr>';
            });
        }
        
        $('#created-pages-table').html(tableHtml);
    }
    
    // Restart generation process
    $('#restart-generation').on('click', function() {
        $('#generation-results').hide();
        switchTab('select-template');
        
        // Reset form
        $('#source-page').val('');
        $('#selected-template-preview').hide();
        $('.formula-option').removeClass('selected');
        $('#proceed-to-formula, #proceed-to-review').prop('disabled', true);
        pagesToGenerate = [];
    });
});
</script> 