<?php
// Prevent direct access
if (!defined('ABSPATH')) exit;

global $wpdb;

// Pagination settings
$per_page = 20;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $per_page;

// Search functionality
$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
$search_query = '';
if (!empty($search)) {
    $search_query = $wpdb->prepare("AND p.post_title LIKE %s", '%' . $wpdb->esc_like($search) . '%');
}

// Template filter
$template_filter = isset($_GET['template']) ? intval($_GET['template']) : 0;
$template_query = '';
if ($template_filter > 0) {
    $template_query = $wpdb->prepare("AND lp.template_id = %d", $template_filter);
}

// Get pages with pagination
$pages_table = $wpdb->prefix . 'lsg_pages';
$templates_table = $wpdb->prefix . 'lsg_templates';
$query = $wpdb->prepare(
    "SELECT lp.*, t.template_name, p.post_title, p.post_status, p.post_type
    FROM {$pages_table} AS lp
    LEFT JOIN {$templates_table} AS t ON lp.template_id = t.id
    LEFT JOIN {$wpdb->posts} AS p ON lp.page_post_id = p.ID
    WHERE 1=1 {$search_query} {$template_query}
    ORDER BY lp.id DESC
    LIMIT %d OFFSET %d",
    $per_page, $offset
);
$pages = $wpdb->get_results($query);

// Get total pages for pagination
$total_query = "SELECT COUNT(lp.id) FROM {$pages_table} AS lp
    LEFT JOIN {$wpdb->posts} AS p ON lp.page_post_id = p.ID
    WHERE 1=1 {$search_query} {$template_query}";
$total_pages = $wpdb->get_var($total_query);
$total_pages_num = ceil($total_pages / $per_page);

// Get templates for filter dropdown
$templates = $wpdb->get_results("SELECT * FROM $templates_table ORDER BY template_name ASC");
?>

<div class="wrap local-seo-god-wrap">
    <h1><?php echo esc_html($this->plugin_name); ?> - Manage Pages</h1>
    
    <div class="local-seo-god-manage-pages">
        <div class="local-seo-god-card">
            <h2>Generated Pages</h2>
            <div class="card-content">
                <div class="local-seo-god-filters">
                    <div class="local-seo-god-search-box">
                        <form method="get" action="">
                            <input type="hidden" name="page" value="<?php echo esc_attr($this->base_name . '_manage'); ?>">
                            <?php if ($template_filter > 0) : ?>
                                <input type="hidden" name="template" value="<?php echo esc_attr($template_filter); ?>">
                            <?php endif; ?>
                            <input type="search" id="local-seo-god-search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Search pages...">
                            <input type="submit" id="local-seo-god-search-button" class="button" value="Search">
                        </form>
                    </div>
                    
                    <div class="local-seo-god-filter-box">
                        <form method="get" action="">
                            <input type="hidden" name="page" value="<?php echo esc_attr($this->base_name . '_manage'); ?>">
                            <?php if (!empty($search)) : ?>
                                <input type="hidden" name="s" value="<?php echo esc_attr($search); ?>">
                            <?php endif; ?>
                            <select name="template" onchange="this.form.submit()">
                                <option value="0">All Templates</option>
                                <?php foreach ($templates as $template) : ?>
                                    <option value="<?php echo esc_attr($template->id); ?>" <?php selected($template_filter, $template->id); ?>>
                                        <?php echo esc_html($template->template_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                </div>
                
                <div class="local-seo-god-bulk-actions">
                    <select id="local-seo-god-bulk-action">
                        <option value="">Bulk Actions</option>
                        <option value="update">Update Pages</option>
                        <option value="delete">Delete Pages</option>
                    </select>
                    <button type="button" id="local-seo-god-bulk-action-apply" class="button">Apply</button>
                </div>
                
                <div class="local-seo-god-bulk-status"></div>
                
                <?php if (empty($pages)) : ?>
                    <p>No pages found.</p>
                <?php else : ?>
                    <table class="widefat local-seo-god-pages-list">
                        <thead>
                            <tr>
                                <th class="check-column"><input type="checkbox" id="local-seo-god-select-all"></th>
                                <th>Title</th>
                                <th>Template</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pages as $page) : 
                                // Skip if the post was deleted outside our plugin
                                if (!$page->post_title) continue;
                                
                                $row_class = '';
                                if ($page->post_status !== 'publish') {
                                    $row_class = ' class="status-' . esc_attr($page->post_status) . '"';
                                }
                                
                                $replacement_data = json_decode($page->replacement_data, true);
                            ?>
                                <tr id="page-row-<?php echo esc_attr($page->id); ?>"<?php echo $row_class; ?>>
                                    <td>
                                        <input type="checkbox" class="local-seo-god-page-checkbox" value="<?php echo esc_attr($page->id); ?>">
                                    </td>
                                    <td>
                                        <strong>
                                            <a href="<?php echo get_permalink($page->page_post_id); ?>" target="_blank">
                                                <?php echo esc_html($page->post_title); ?>
                                            </a>
                                        </strong>
                                        <div class="row-actions">
                                            <span class="edit"><a href="<?php echo get_edit_post_link($page->page_post_id); ?>" target="_blank">Edit</a> | </span>
                                            <span class="view"><a href="<?php echo get_permalink($page->page_post_id); ?>" target="_blank">View</a></span>
                                        </div>
                                    </td>
                                    <td><?php echo esc_html($page->template_name); ?></td>
                                    <td>
                                        <span class="local-seo-god-status <?php echo esc_attr($page->post_status); ?>"></span>
                                        <?php echo esc_html(ucfirst($page->post_status)); ?>
                                    </td>
                                    <td><?php echo date('F j, Y', strtotime($page->created_at)); ?></td>
                                    <td><?php echo date('F j, Y', strtotime($page->last_updated)); ?></td>
                                    <td>
                                        <div class="local-seo-god-page-actions">
                                            <button type="button" class="button button-small local-seo-god-view-replacements" 
                                                    data-id="<?php echo esc_attr($page->id); ?>"
                                                    data-replacements='<?php echo esc_attr(json_encode($replacement_data)); ?>'>
                                                View Replacements
                                            </button>
                                            <a href="<?php echo admin_url('admin.php?page=' . $this->base_name . '_manage&delete_page=' . $page->id . '&_wpnonce=' . wp_create_nonce('delete_page_' . $page->id)); ?>" 
                                               class="button button-small" 
                                               onclick="return confirm('Are you sure you want to delete this page? This cannot be undone.');">
                                                Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if ($total_pages_num > 1) : ?>
                        <div class="local-seo-god-pagination">
                            <?php
                            $base_url = admin_url('admin.php?page=' . $this->base_name . '_manage');
                            if (!empty($search)) {
                                $base_url .= '&s=' . urlencode($search);
                            }
                            if ($template_filter > 0) {
                                $base_url .= '&template=' . $template_filter;
                            }
                            
                            // Previous page
                            if ($current_page > 1) {
                                echo '<a href="' . $base_url . '&paged=' . ($current_page - 1) . '" class="page-numbers prev">&laquo; Previous</a>';
                            }
                            
                            // Page numbers
                            $start = max(1, $current_page - 2);
                            $end = min($total_pages_num, $current_page + 2);
                            
                            if ($start > 1) {
                                echo '<a href="' . $base_url . '&paged=1" class="page-numbers">1</a>';
                                if ($start > 2) {
                                    echo '<span class="page-numbers dots">...</span>';
                                }
                            }
                            
                            for ($i = $start; $i <= $end; $i++) {
                                if ($i == $current_page) {
                                    echo '<span class="page-numbers current">' . $i . '</span>';
                                } else {
                                    echo '<a href="' . $base_url . '&paged=' . $i . '" class="page-numbers">' . $i . '</a>';
                                }
                            }
                            
                            if ($end < $total_pages_num) {
                                if ($end < $total_pages_num - 1) {
                                    echo '<span class="page-numbers dots">...</span>';
                                }
                                echo '<a href="' . $base_url . '&paged=' . $total_pages_num . '" class="page-numbers">' . $total_pages_num . '</a>';
                            }
                            
                            // Next page
                            if ($current_page < $total_pages_num) {
                                echo '<a href="' . $base_url . '&paged=' . ($current_page + 1) . '" class="page-numbers next">Next &raquo;</a>';
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                    
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Modal for viewing replacements -->
    <div id="local-seo-god-replacements-modal" style="display: none;">
        <div class="local-seo-god-modal-content">
            <span class="local-seo-god-modal-close">&times;</span>
            <h2>Replacement Values</h2>
            <div id="local-seo-god-replacements-content"></div>
        </div>
    </div>
</div>

<style>
/* Status colors */
.local-seo-god-status {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 5px;
}
.local-seo-god-status.publish {
    background-color: #46b450;
}
.local-seo-god-status.draft {
    background-color: #ffb900;
}
.local-seo-god-status.trash {
    background-color: #dc3232;
}
.local-seo-god-status.pending {
    background-color: #00a0d2;
}

/* Status row styling */
.local-seo-god-pages-list tr.status-draft {
    background-color: #fef7e5;
}
.local-seo-god-pages-list tr.status-trash {
    background-color: #fbeaea;
}
.local-seo-god-pages-list tr.status-pending {
    background-color: #e5f5fa;
}

/* Filters and actions area */
.local-seo-god-filters {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
}
.local-seo-god-bulk-actions {
    margin-bottom: 15px;
}
.local-seo-god-page-actions {
    display: flex;
    gap: 5px;
}

/* Modal styling */
#local-seo-god-replacements-modal {
    position: fixed;
    z-index: 100000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.4);
}
.local-seo-god-modal-content {
    background-color: #fefefe;
    margin: 10% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 50%;
    max-width: 600px;
    border-radius: 4px;
    position: relative;
}
.local-seo-god-modal-close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    position: absolute;
    top: 10px;
    right: 15px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Select all checkbox
    $('#local-seo-god-select-all').on('change', function() {
        $('.local-seo-god-page-checkbox').prop('checked', $(this).prop('checked'));
    });
    
    // Replacements modal
    $('.local-seo-god-view-replacements').on('click', function() {
        var replacements = $(this).data('replacements');
        var html = '<table class="widefat">';
        html += '<thead><tr><th>Keyword</th><th>Value</th></tr></thead><tbody>';
        
        for (var key in replacements) {
            html += '<tr>';
            html += '<td>' + key + '</td>';
            html += '<td>' + replacements[key] + '</td>';
            html += '</tr>';
        }
        
        html += '</tbody></table>';
        
        $('#local-seo-god-replacements-content').html(html);
        $('#local-seo-god-replacements-modal').show();
    });
    
    // Close modal
    $('.local-seo-god-modal-close').on('click', function() {
        $('#local-seo-god-replacements-modal').hide();
    });
    
    // Close modal when clicking outside
    $(window).on('click', function(e) {
        if ($(e.target).is('#local-seo-god-replacements-modal')) {
            $('#local-seo-god-replacements-modal').hide();
        }
    });
});
</script> 