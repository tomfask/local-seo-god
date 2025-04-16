<?php
// Prevent direct access
if (!defined('ABSPATH')) exit;
?>

<div class="wrap local-seo-god-wrap">
    <h1><?php echo esc_html($this->plugin_name); ?> - Dashboard</h1>
    
    <div class="local-seo-god-dashboard">
        <div class="local-seo-god-card">
            <h2>Welcome to Local SEO God</h2>
            <div class="card-content">
                <p>Local SEO God helps you create and manage location-specific landing pages for your business. Use this plugin to automate your local SEO strategy by creating unlimited location-based pages.</p>
                <p>Here's how to get started:</p>
                <ol>
                    <li>Create a template page that will serve as the basis for all your location pages.</li>
                    <li>Use keyword tags like <code>{city}</code>, <code>{state}</code>, <code>{service}</code> in your template.</li>
                    <li>Go to <a href="<?php echo admin_url('admin.php?page=' . $this->base_name . '_create'); ?>">Create Pages</a> to generate location pages by specifying replacement values.</li>
                </ol>
            </div>
        </div>
        
        <div class="local-seo-god-row">
            <div class="local-seo-god-col">
                <div class="local-seo-god-card">
                    <h2>Statistics</h2>
                    <div class="card-content">
                        <?php
                        global $wpdb;
                        
                        // Get template count
                        $templates_table = $wpdb->prefix . 'lsg_templates';
                        $template_count = $wpdb->get_var("SELECT COUNT(*) FROM $templates_table");
                        
                        // Get created pages count
                        $pages_table = $wpdb->prefix . 'lsg_pages';
                        $pages_count = $wpdb->get_var("SELECT COUNT(*) FROM $pages_table");
                        
                        // Get replacements count
                        $replacements_table = $wpdb->prefix . $this->table_name;
                        $replacements_count = $wpdb->get_var("SELECT COUNT(*) FROM $replacements_table");
                        ?>
                        
                        <ul>
                            <li><strong>Templates:</strong> <?php echo intval($template_count); ?></li>
                            <li><strong>Created Pages:</strong> <?php echo intval($pages_count); ?></li>
                            <li><strong>Word Replacements:</strong> <?php echo intval($replacements_count); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="local-seo-god-col">
                <div class="local-seo-god-card">
                    <h2>Quick Links</h2>
                    <div class="card-content">
                        <ul>
                            <li><a href="<?php echo admin_url('admin.php?page=' . $this->base_name . '_create'); ?>">Create Pages</a> - Generate new location-specific pages</li>
                            <li><a href="<?php echo admin_url('admin.php?page=' . $this->base_name . '_manage'); ?>">Manage Pages</a> - View and manage your created pages</li>
                            <li><a href="<?php echo admin_url('admin.php?page=' . $this->base_name . '_words'); ?>">Word Replacements</a> - Manage global word replacements</li>
                            <li><a href="<?php echo admin_url('admin.php?page=' . $this->base_name . '_settings'); ?>">Settings</a> - Configure plugin settings</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="local-seo-god-card">
            <h2>How It Works</h2>
            <div class="card-content">
                <p>Local SEO God handles two different types of replacements:</p>
                
                <h3>1. Page Generation with Template Replacements</h3>
                <p>Create new pages from a template by replacing keywords in curly braces like <code>{city}</code> or <code>{service}</code>.</p>
                <ul>
                    <li>Select a template page to use as your base</li>
                    <li>Add keyword/value pairs (e.g., city → Chicago, service → Plumbing)</li>
                    <li>Generate new pages with all replaced content</li>
                </ul>
                
                <h3>2. Global Word Replacements</h3>
                <p>Automatically replace words or phrases across your entire site using regex or simple replacements.</p>
                <ul>
                    <li>Set up replacements to occur on posts, pages, titles, or comments</li>
                    <li>Use regex for advanced pattern matching</li>
                    <li>Control case sensitivity and whole word matching</li>
                </ul>
                
                <p><span class="local-seo-god-expandable-toggle">Need help with keyword formatting?</span></p>
                <div class="local-seo-god-expandable-content">
                    <p>For page templates, use curly braces around your keywords: <code>{keyword}</code></p>
                    <p>Examples:</p>
                    <ul>
                        <li><code>{city}</code> - Will be replaced with city names like "Chicago" or "New York"</li>
                        <li><code>{state}</code> - Will be replaced with state names</li>
                        <li><code>{service}</code> - Will be replaced with service names</li>
                        <li><code>{zip}</code> - Will be replaced with ZIP/postal codes</li>
                    </ul>
                    <p>You can use any keyword you want as long as you provide a replacement value for it when creating pages.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.local-seo-god-row {
    display: flex;
    margin: 0 -10px;
}

.local-seo-god-col {
    flex: 1;
    padding: 0 10px;
}

@media screen and (max-width: 782px) {
    .local-seo-god-row {
        flex-direction: column;
    }
    
    .local-seo-god-col {
        margin-bottom: 20px;
    }
}
</style> 