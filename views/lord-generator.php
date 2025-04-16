<?php
// Prevent direct access
if (!defined('ABSPATH')) exit;
?>

<div class="wrap local-seo-god-wrap">
    <h1><?php echo esc_html($this->plugin_name); ?> - The Lord Generator</h1>
    
    <div class="local-seo-god-lord-generator">
        <div class="local-seo-god-mode-selector">
            <h2>Choose Your Power</h2>
            
            <p class="description">
                Select which power you want to harness. Each option provides different capabilities for your SEO strategy.
            </p>
            
            <div class="power-options">
                <div class="power-option" id="hercules-option">
                    <div class="power-header">
                        <h3>Hercules</h3>
                        <span class="power-badge">Tag Replacement</span>
                    </div>
                    <div class="power-description">
                        <p>Enhance existing pages with dynamic tag replacements. Perfect for updating your homepage or key landing pages with business-specific information and dynamic service links.</p>
                        <ul>
                            <li>Replace tags on existing pages</li>
                            <li>Preview changes before applying</li>
                            <li>Apply to one or multiple pages at once</li>
                            <li>Uses your business information for replacements</li>
                        </ul>
                    </div>
                    <div class="power-action">
                        <button type="button" class="button button-primary select-power" data-power="hercules">Select Hercules</button>
                    </div>
                </div>
                
                <div class="power-option" id="zeus-option">
                    <div class="power-header">
                        <h3>Zeus Mode</h3>
                        <span class="power-badge">Bulk Page Generation</span>
                    </div>
                    <div class="power-description">
                        <p>Create multiple location-specific landing pages at once. Perfect for service-based businesses targeting different locations or specialized service variations.</p>
                        <ul>
                            <li>Generate dozens or hundreds of pages</li>
                            <li>Use templates from existing pages</li>
                            <li>Dynamic keyword replacement</li>
                            <li>Batch processing for high volume</li>
                        </ul>
                    </div>
                    <div class="power-action">
                        <button type="button" class="button button-primary select-power" data-power="zeus">Select Zeus Mode</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Hercules Content (Initially Hidden) -->
        <div class="lord-generator-content" id="hercules-content" style="display: none;">
            <?php include(plugin_dir_path(dirname(__FILE__)) . 'views/tag-replacements-content.php'); ?>
        </div>
        
        <!-- Zeus Content (Initially Hidden) -->
        <div class="lord-generator-content" id="zeus-content" style="display: none;">
            <?php include(plugin_dir_path(dirname(__FILE__)) . 'views/create-pages-content.php'); ?>
        </div>
    </div>
</div>

<style>
.local-seo-god-mode-selector {
    margin-bottom: 30px;
}

.power-options {
    display: flex;
    gap: 30px;
    margin-top: 20px;
}

.power-option {
    flex: 1;
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 20px;
    background-color: #fff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.power-option:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.power-header {
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
    margin-bottom: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.power-header h3 {
    margin: 0;
    color: #23282d;
}

.power-badge {
    background-color: #f0f0f1;
    padding: 5px 10px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 500;
}

.power-description {
    color: #50575e;
    margin-bottom: 20px;
}

.power-description ul {
    list-style-type: disc;
    margin-left: 20px;
}

.power-action {
    text-align: center;
}

#hercules-option {
    border-top: 4px solid #00a0d2;
}

#hercules-option .power-badge {
    background-color: #e5f5fa;
    color: #0071a1;
}

#zeus-option {
    border-top: 4px solid #8e44ad;
}

#zeus-option .power-badge {
    background-color: #f4ecf7;
    color: #8e44ad;
}

.lord-generator-content {
    margin-top: 30px;
    background: #fff;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Handle power selection
    $('.select-power').on('click', function() {
        var power = $(this).data('power');
        
        // Hide all content sections
        $('.lord-generator-content').hide();
        
        // Show selected content
        $('#' + power + '-content').fadeIn();
        
        // Update UI to reflect selection
        $('.power-option').removeClass('active-power');
        $('#' + power + '-option').addClass('active-power');
        
        // Scroll to the content
        $('html, body').animate({
            scrollTop: $('#' + power + '-content').offset().top - 50
        }, 500);
    });
});
</script> 