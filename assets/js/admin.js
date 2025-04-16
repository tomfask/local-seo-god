/**
 * Local SEO God Admin JS
 */
jQuery(document).ready(function($) {
    // Initialize tabs
    $('.nav-tab-wrapper a').on('click', function(e) {
        e.preventDefault();
        
        var targetTab = $(this).attr('href');
        
        // Update active tab
        $('.nav-tab-wrapper a').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        // Show target tab content
        $('.tab-content').removeClass('active').hide();
        $(targetTab).addClass('active').show();
        
        // Update URL hash
        window.location.hash = targetTab;
    });
    
    // Check URL hash on page load
    var hash = window.location.hash;
    if (hash) {
        $('.nav-tab-wrapper a[href="' + hash + '"]').trigger('click');
    }
    
    // Business information form
    $('#business-info-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        var submitButton = $(this).find('button[type="submit"]');
        
        submitButton.prop('disabled', true).text('Saving...');
        
        $.ajax({
            url: localSeoGod.ajaxUrl,
            type: 'POST',
            data: formData + '&action=local_seo_god_save_business_info&nonce=' + localSeoGod.nonce,
            success: function(response) {
                if (response.success) {
                    showNotice('success', 'Business information saved successfully!');
                } else {
                    showNotice('error', 'Error: ' + response.data);
                }
            },
            error: function() {
                showNotice('error', 'Server error while saving business information.');
            },
            complete: function() {
                submitButton.prop('disabled', false).text('Save Business Information');
            }
        });
    });
    
    // Word replacement form
    $('#word-replacement-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        var submitButton = $(this).find('button[type="submit"]');
        
        submitButton.prop('disabled', true).text('Saving...');
        
        $.ajax({
            url: localSeoGod.ajaxUrl,
            type: 'POST',
            data: formData + '&action=local_seo_god_save_word_replacement&nonce=' + localSeoGod.nonce,
            success: function(response) {
                if (response.success) {
                    $('#word-replacement-table').load(window.location.href + ' #word-replacement-table > *', function() {
                        initReplacementsUI();
                    });
                    
                    // Clear form
                    $('#word-replacement-form input[type="text"]').val('');
                    $('#word-replacement-form input[type="checkbox"]').prop('checked', false);
                    
                    showNotice('success', 'Word replacement added successfully!');
                } else {
                    showNotice('error', 'Error: ' + response.data);
                }
            },
            error: function() {
                showNotice('error', 'Server error while saving word replacement.');
            },
            complete: function() {
                submitButton.prop('disabled', false).text('Add Replacement');
            }
        });
    });
    
    // Initialize replacements UI
    function initReplacementsUI() {
        // Delete replacement
        $('.delete-replacement').on('click', function(e) {
            e.preventDefault();
            
            var replacementId = $(this).data('id');
            var row = $(this).closest('tr');
            
            if (confirm('Are you sure you want to delete this replacement? This cannot be undone.')) {
                $.ajax({
                    url: localSeoGod.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'local_seo_god_delete_word_replacement',
                        nonce: localSeoGod.nonce,
                        replacement_id: replacementId
                    },
                    success: function(response) {
                        if (response.success) {
                            row.fadeOut(300, function() {
                                $(this).remove();
                            });
                            
                            showNotice('success', 'Replacement deleted successfully!');
                        } else {
                            showNotice('error', 'Error: ' + response.data);
                        }
                    },
                    error: function() {
                        showNotice('error', 'Server error while deleting replacement.');
                    }
                });
            }
        });
        
        // Toggle editing
        $('.edit-replacement').on('click', function(e) {
            e.preventDefault();
            
            var row = $(this).closest('tr');
            row.find('.view-mode').hide();
            row.find('.edit-mode').show();
        });
        
        // Cancel editing
        $('.cancel-edit').on('click', function(e) {
            e.preventDefault();
            
            var row = $(this).closest('tr');
            row.find('.edit-mode').hide();
            row.find('.view-mode').show();
        });
        
        // Save edit
        $('.save-edit').on('click', function(e) {
            e.preventDefault();
            
            var row = $(this).closest('tr');
            var replacementId = $(this).data('id');
            var formData = row.find('input, select').serialize();
            
            $.ajax({
                url: localSeoGod.ajaxUrl,
                type: 'POST',
                data: formData + '&action=local_seo_god_update_word_replacement&nonce=' + localSeoGod.nonce + '&replacement_id=' + replacementId,
                success: function(response) {
                    if (response.success) {
                        $('#word-replacement-table').load(window.location.href + ' #word-replacement-table > *', function() {
                            initReplacementsUI();
                        });
                        
                        showNotice('success', 'Replacement updated successfully!');
                    } else {
                        showNotice('error', 'Error: ' + response.data);
                    }
                },
                error: function() {
                    showNotice('error', 'Server error while updating replacement.');
                }
            });
        });
    }
    
    // Call init function on load
    initReplacementsUI();
    
    // Create page form
    $('#create-page-form').on('submit', function(e) {
        e.preventDefault();
        
        // Check required fields
        var title = $('#page-title').val();
        var content = $('#page-content').val();
        
        if (!title || !content) {
            showNotice('error', 'Please fill in all required fields.');
            return;
        }
        
        var formData = $(this).serialize();
        var submitButton = $(this).find('button[type="submit"]');
        
        submitButton.prop('disabled', true).text('Creating Page...');
        
        $.ajax({
            url: localSeoGod.ajaxUrl,
            type: 'POST',
            data: formData + '&action=local_seo_god_create_page&nonce=' + localSeoGod.nonce,
            success: function(response) {
                if (response.success) {
                    // Show success message with link to the new page
                    $('#create-page-result').html('<div class="notice notice-success"><p>Page created successfully! <a href="' + response.data.permalink + '" target="_blank">View Page</a> | <a href="' + response.data.edit_url + '" target="_blank">Edit Page</a></p></div>').show();
                    
                    // Clear form
                    $('#create-page-form')[0].reset();
                } else {
                    showNotice('error', 'Error: ' + response.data);
                }
            },
            error: function() {
                showNotice('error', 'Server error while creating page.');
            },
            complete: function() {
                submitButton.prop('disabled', false).text('Create Page');
            }
        });
    });
    
    // Show notice function
    function showNotice(type, message) {
        var noticeClass = (type === 'success') ? 'notice-success' : 'notice-error';
        var notice = $('<div class="notice ' + noticeClass + ' is-dismissible"><p>' + message + '</p><button type="button" class="notice-dismiss"></button></div>');
        
        // Add notice to the top of the page
        $('#wpbody-content').prepend(notice);
        
        // Handle dismiss button
        notice.find('.notice-dismiss').on('click', function() {
            notice.fadeOut(300, function() {
                $(this).remove();
            });
        });
        
        // Auto-remove after 5 seconds
        setTimeout(function() {
            notice.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    // Tag replacements preview
    $('#preview-tags-button').on('click', function(e) {
        e.preventDefault();
        
        var pages = $('#selected-pages').val();
        var tagTemplate = $('#tag-template').val();
        
        if (!pages || !pages.length || !tagTemplate) {
            showNotice('error', 'Please select at least one page and a tag template.');
            return;
        }
        
        var submitButton = $(this);
        var originalText = submitButton.text();
        
        submitButton.prop('disabled', true).text('Generating Preview...');
        
        $.ajax({
            url: localSeoGod.ajaxUrl,
            type: 'POST',
            data: {
                action: 'local_seo_god_preview_tag_replacements',
                nonce: localSeoGod.nonce,
                pages: pages,
                template_id: tagTemplate
            },
            success: function(response) {
                if (response.success) {
                    $('#preview-container').html(response.data.html).show();
                } else {
                    showNotice('error', 'Error: ' + response.data);
                }
            },
            error: function() {
                showNotice('error', 'Server error while generating preview.');
            },
            complete: function() {
                submitButton.prop('disabled', false).text(originalText);
            }
        });
    });
    
    // Apply tag replacements
    $('#apply-tags-button').on('click', function(e) {
        e.preventDefault();
        
        var pages = $('#selected-pages').val();
        var tagTemplate = $('#tag-template').val();
        
        if (!pages || !pages.length || !tagTemplate) {
            showNotice('error', 'Please select at least one page and a tag template.');
            return;
        }
        
        if (!confirm('Are you sure you want to apply these tag replacements? This will modify the selected pages.')) {
            return;
        }
        
        var submitButton = $(this);
        var originalText = submitButton.text();
        
        submitButton.prop('disabled', true).text('Applying Tags...');
        
        $.ajax({
            url: localSeoGod.ajaxUrl,
            type: 'POST',
            data: {
                action: 'local_seo_god_apply_tag_replacements',
                nonce: localSeoGod.nonce,
                pages: pages,
                template_id: tagTemplate
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', 'Tag replacements applied successfully to ' + response.data.count + ' pages!');
                    $('#preview-container').html('').hide();
                } else {
                    showNotice('error', 'Error: ' + response.data);
                }
            },
            error: function() {
                showNotice('error', 'Server error while applying tag replacements.');
            },
            complete: function() {
                submitButton.prop('disabled', false).text(originalText);
            }
        });
    });
    
    // Zeus Mode functionality
    if ($('.zeus-section').length > 0) {
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
                    url: localSeoGod.ajaxUrl,
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
                            showNotice('error', 'Error: ' + response.data);
                            $('#proceed-to-formula').prop('disabled', true);
                        }
                    },
                    error: function() {
                        showNotice('error', 'Server error while loading page preview.');
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
            
            // Show loading indicator
            $('#preview-pages-table').html('<tr><td colspan="3">Loading preview...</td></tr>');
            
            // Fetch actual business info for accurate previews
            $.ajax({
                url: localSeoGod.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'local_seo_god_get_business_info',
                    nonce: localSeoGod.nonce
                },
                success: function(response) {
                    if (!response.success) {
                        $('#preview-pages-table').html('<tr><td colspan="3">Error: ' + response.data + '</td></tr>');
                        return;
                    }
                    
                    var businessInfo = response.data;
                    
                    // Get counts from the UI
                    var serviceCount = parseInt($('#service-location-count').text());
                    var areaCount = parseInt($('#keyword-area-count').text());
                    var combinedCount = parseInt($('#service-area-count').text());
                    
                    // Use actual business info or fallbacks
                    var services = businessInfo.services || [];
                    var areas = businessInfo.service_areas || [];
                    var domain = businessInfo.domain || 'example.com';
                    var targetLocation = businessInfo.target_location || 'Location';
                    var mainKeyword = businessInfo.main_keyword || 'Keyword';
                    var gmbService = businessInfo.gmb_service || 'Service';
                    var businessName = businessInfo.business_name || 'Business';
                    
                    // Track tags that will be used in pages
                    var allTags = new Set();
                    
                    switch(selectedFormula) {
                        case 'service-location':
                            // Service|TargetLocation formula
                            for (var i = 1; i <= serviceCount && i <= services.length; i++) {
                                var service = services[i-1];
                                pagesToGenerate.push({
                                    title: service + ' ' + targetLocation + ' | ' + gmbService + ' Near Me',
                                    url: 'https://www.' + domain + '/' + sanitizeForUrl(service) + '-' + sanitizeForUrl(targetLocation),
                                    service_index: i,
                                    area_index: null,
                                    formula: 'service-location',
                                    tags: ['Business-Name', 'service', 'Service-'+i, 'GMB-Service', 'Target-Location']
                                });
                                
                                // Add tags to the set
                                allTags.add('Business-Name');
                                allTags.add('service');
                                allTags.add('Service-'+i);
                                allTags.add('GMB-Service');
                                allTags.add('Target-Location');
                            }
                            break;
                            
                        case 'keyword-area':
                            // MainKeyword|ServiceArea formula
                            for (var i = 1; i <= areaCount && i <= areas.length; i++) {
                                var area = areas[i-1];
                                var wordLiner = getRandomWordLiner();
                                pagesToGenerate.push({
                                    title: wordLiner + ' ' + mainKeyword + ' ' + area + ' | ' + gmbService + ' Near Me',
                                    url: 'https://www.' + domain + '/' + sanitizeForUrl(area) + '-' + sanitizeForUrl(mainKeyword),
                                    service_index: null,
                                    area_index: i,
                                    formula: 'keyword-area',
                                    tags: ['Business-Name', 'area', 'area-'+i, 'Main-Keyword', 'GMB-Service', 'One-WordLiner']
                                });
                                
                                // Add tags to the set
                                allTags.add('Business-Name');
                                allTags.add('area');
                                allTags.add('area-'+i);
                                allTags.add('Main-Keyword');
                                allTags.add('GMB-Service');
                                allTags.add('One-WordLiner');
                            }
                            break;
                            
                        case 'service-area':
                            // Service|ServiceArea formula
                            var serviceIndex = 1;
                            var areaIndex = 1;
                            
                            for (var s = 1; s <= services.length && serviceIndex <= combinedCount; s++) {
                                for (var a = 1; a <= areas.length && serviceIndex <= combinedCount; a++) {
                                    var service = services[s-1];
                                    var area = areas[a-1];
                                    
                                    pagesToGenerate.push({
                                        title: service + ' ' + area + ' | ' + gmbService + ' Near Me',
                                        url: 'https://www.' + domain + '/' + sanitizeForUrl(service) + '-' + sanitizeForUrl(area),
                                        service_index: s,
                                        area_index: a,
                                        formula: 'service-area',
                                        tags: ['Business-Name', 'service', 'Service-'+s, 'area', 'area-'+a, 'GMB-Service']
                                    });
                                    
                                    // Add tags to the set
                                    allTags.add('Business-Name');
                                    allTags.add('service');
                                    allTags.add('Service-'+s);
                                    allTags.add('area');
                                    allTags.add('area-'+a);
                                    allTags.add('GMB-Service');
                                    
                                    serviceIndex++;
                                    if (serviceIndex > combinedCount) break;
                                }
                            }
                            break;
                    }
                    
                    // Check if AI tags might be present in the template
                    $.ajax({
                        url: localSeoGod.ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'local_seo_god_check_ai_tags',
                            nonce: localSeoGod.nonce,
                            template_page_id: selectedTemplateId
                        },
                        success: function(aiResponse) {
                            if (aiResponse.success && aiResponse.data.has_ai_tags) {
                                // Add AI tags to the set
                                aiResponse.data.ai_tags.forEach(function(tag) {
                                    allTags.add(tag.replace(/[{}]/g, ''));
                                });
                            }
                            
                            // Now render the table with all collected tags
                            renderPreviewTable(pagesToGenerate, Array.from(allTags));
                        },
                        error: function() {
                            // Proceed without AI tag information
                            renderPreviewTable(pagesToGenerate, Array.from(allTags));
                        }
                    });
                },
                error: function() {
                    $('#preview-pages-table').html('<tr><td colspan="3">Error loading business information.</td></tr>');
                }
            });
        }
        
        // Function to render the preview table with color-coded tags
        function renderPreviewTable(pages, allTags) {
            if (pages.length === 0) {
                $('#preview-pages-table').html('<tr><td colspan="3">No pages to generate with the current selection.</td></tr>');
                return;
            }
            
            // Generate color map for tags
            var tagColors = {};
            var colors = [
                '#FFD700', '#FF6347', '#98FB98', '#87CEFA', '#DDA0DD', 
                '#F0E68C', '#E6E6FA', '#FFA07A', '#B0E0E6', '#FFDAB9'
            ];
            
            allTags.forEach(function(tag, index) {
                tagColors[tag] = colors[index % colors.length];
            });
            
            // Generate header with legend
            var legendHtml = '<div class="tag-legend" style="margin-bottom: 15px; display: flex; flex-wrap: wrap; gap: 10px;">';
            legendHtml += '<strong style="width: 100%; margin-bottom: 5px;">Tag Legend:</strong>';
            
            allTags.forEach(function(tag) {
                legendHtml += '<span class="tag-item" style="background-color: ' + tagColors[tag] + '; padding: 2px 5px; border-radius: 3px; margin-right: 5px;">' + tag + '</span>';
            });
            
            legendHtml += '</div>';
            
            // Add the legend before the table
            $('#preview-legend').html(legendHtml);
            
            // Generate table rows
            var tableHtml = '';
            
            pages.forEach(function(page) {
                tableHtml += '<tr>';
                tableHtml += '<td>' + page.title + '</td>';
                tableHtml += '<td><a href="' + page.url + '" target="_blank">' + page.url + '</a></td>';
                
                // Tags column with color coding
                tableHtml += '<td class="tag-column" style="display: flex; flex-wrap: wrap; gap: 5px;">';
                
                if (page.tags && page.tags.length > 0) {
                    page.tags.forEach(function(tag) {
                        var color = tagColors[tag] || '#e0e0e0';
                        tableHtml += '<span class="tag-item" style="background-color: ' + color + '; padding: 2px 5px; border-radius: 3px;">' + tag + '</span>';
                    });
                }
                
                tableHtml += '</td>';
                tableHtml += '</tr>';
            });
            
            $('#preview-pages-table').html(tableHtml);
        }
        
        // Helper function to sanitize strings for URLs
        function sanitizeForUrl(str) {
            return str.toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
        }
        
        // Get a random word liner
        function getRandomWordLiner() {
            var words = [
                'Professional',
                'Expert',
                'Reliable',
                'Affordable',
                'Best',
                'Trusted',
                'Experienced',
                'Local',
                'Top',
                'Leading',
                'Certified',
                'Licensed',
                'Insured',
                'Quality',
                'Premium',
                'Dependable',
                'Unmatched',
                'Superior',
                'Outstanding',
                'Highly-Rated',
                'Top-Rated',
                'Top-Reviewed',
                'Popular'
            ];
            return words[Math.floor(Math.random() * words.length)];
        }
        
        // Start page generation
        $('#start-generation').on('click', function() {
            if (pagesToGenerate.length === 0) {
                showNotice('error', 'No pages to generate. Please go back and select a different formula.');
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
            var batchSize = 5; // Process 5 pages at a time
            var processingDelay = 500; // Delay between pages in ms
            var batchDelay = 2000; // Delay between batches in ms
            
            // Update progress UI
            $('.progress-container').show();
            $('.total-pages').text(totalPages);
            $('.current-page').text('0');
            $('.progress-bar').css('width', '0%');
            
            // Process pages in batches
            function processBatch() {
                if (currentPage >= totalPages) {
                    // All pages processed, show results
                    showResults(createdPages, errors);
                    return;
                }
                
                var batchEnd = Math.min(currentPage + batchSize, totalPages);
                var batchPromises = [];
                
                // Create a delayed promise for each page in the batch
                for (var i = currentPage; i < batchEnd; i++) {
                    batchPromises.push(createDelayedPagePromise(i, (i - currentPage) * processingDelay));
                }
                
                // Process all promises in the batch
                Promise.all(batchPromises)
                    .then(function() {
                        currentPage = batchEnd;
                        // Update progress UI
                        $('.current-page').text(currentPage);
                        $('.progress-bar').css('width', (currentPage / totalPages * 100) + '%');
                        
                        // Process next batch after delay
                        setTimeout(processBatch, batchDelay);
                    })
                    .catch(function(error) {
                        console.error('Error processing batch:', error);
                        // Continue with next batch anyway
                        currentPage = batchEnd;
                        setTimeout(processBatch, batchDelay);
                    });
            }
            
            // Create a delayed promise for a single page
            function createDelayedPagePromise(index, delay) {
                return new Promise(function(resolve, reject) {
                    setTimeout(function() {
                        var pageData = pagesToGenerate[index];
                        
                        // Create the page via AJAX
                        $.ajax({
                            url: localSeoGod.ajaxUrl,
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
                                resolve();
                            },
                            error: function(xhr, status, error) {
                                errors.push({
                                    title: pageData.title,
                                    error: 'Server error: ' + error
                                });
                                resolve(); // Still resolve to continue with other pages
                            }
                        });
                    }, delay);
                });
            }
            
            // Start the batch processing
            processBatch();
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
    }
    
    // AI Content Functions
    function initAIContentPreview() {
        // Variables to store AI content
        let aiContent = {};
        let aiTagsFound = false;
        
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
                url: localSeoGod.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'local_seo_god_get_ai_content_preview',
                    nonce: localSeoGod.nonce,
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
                .replace(/{tag}/g, tag)
                .replace(/{tag_name}/g, tagName)
                .replace(/{content}/g, content);
            
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
                url: localSeoGod.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'local_seo_god_regenerate_ai_content',
                    nonce: localSeoGod.nonce,
                    post_id: postId,
                    template_name: templateName
                },
                success: function(response) {
                    $('.ai-loading').hide();
                    
                    if (response.success) {
                        // Store the regenerated AI content
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
                    $('.ai-error').text('An error occurred while regenerating AI content. Please try again.').show();
                }
            });
        });
    }
    
    // Load tag preview
    $('#get-tag-preview').on('click', function() {
        const pageId = $('#page_id').val();
        const template = $('#template').val();
        
        if (!pageId) {
            alert('Please select a page first');
            return;
        }
        
        // Show loading and hide preview
        $('#loading-preview').show();
        $('#preview-result').hide();
        
        // Make AJAX request to get tag preview
        $.ajax({
            url: localSeoGod.ajaxUrl,
            type: 'POST',
            data: {
                action: 'local_seo_god_preview_tag_replacements',
                nonce: localSeoGod.nonce,
                post_id: pageId,
                template_name: template
            },
            success: function(response) {
                // Hide loading indicator
                $('#loading-preview').hide();
                
                if (response.success) {
                    const previewData = response.data;
                    
                    // Show preview result
                    $('#preview-result').empty().show();
                    
                    // Create tabs for different views
                    const $tabsContainer = $('<div class="preview-tabs"></div>');
                    const $tabLinks = $('<div class="preview-tab-links"></div>');
                    const $tabContent = $('<div class="preview-tab-content"></div>');
                    
                    $tabLinks.append('<a href="#preview-tab-replacements" class="preview-tab-link active">Tag Replacements</a>');
                    $tabLinks.append('<a href="#preview-tab-preview" class="preview-tab-link">Content Preview</a>');
                    
                    $tabsContainer.append($tabLinks);
                    $tabsContainer.append($tabContent);
                    
                    $('#preview-result').append($tabsContainer);
                    
                    // Replacements tab
                    const $replacementsTab = $('<div id="preview-tab-replacements" class="preview-tab-pane active"></div>');
                    
                    // Generate replacements table
                    const $replacementsTable = $('<table class="wp-list-table widefat fixed striped"></table>');
                    $replacementsTable.append('<thead><tr><th>Tag</th><th>Value</th></tr></thead>');
                    const $tbody = $('<tbody></tbody>');
                    
                    // Add each replacement
                    $.each(previewData.replacements, function(tag, value) {
                        $tbody.append(`<tr><td><code>${tag}</code></td><td>${value}</td></tr>`);
                    });
                    
                    $replacementsTable.append($tbody);
                    $replacementsTab.append('<h3>Tag Replacements</h3>');
                    $replacementsTab.append($replacementsTable);
                    
                    $tabContent.append($replacementsTab);
                    
                    // Preview tab
                    const $previewTab = $('<div id="preview-tab-preview" class="preview-tab-pane"></div>');
                    
                    // Add title preview
                    $previewTab.append('<h3>Title Preview</h3>');
                    $previewTab.append(`<div class="preview-title">${previewData.title.replaced}</div>`);
                    
                    // Add content preview
                    $previewTab.append('<h3>Content Preview</h3>');
                    $previewTab.append(`<div class="preview-content">${previewData.content.replaced}</div>`);
                    
                    $tabContent.append($previewTab);
                    
                    // Check if there are AI tags
                    if (previewData.has_ai_tags) {
                        const aiTagsContainer = $('<div class="ai-tags-container"></div>');
                        aiTagsContainer.append('<h3>AI Content Tags Found</h3>');
                        aiTagsContainer.append('<p>The following AI tags were found in the content. Click "Generate AI Content" to preview.</p>');
                        
                        const aiTagsList = $('<ul></ul>');
                        $.each(previewData.ai_tags, function(tag, tagName) {
                            aiTagsList.append(`<li><span class="ai-tag"><span class="dashicons dashicons-admin-customizer ai-tag-icon"></span>${tag}</span> - ${tagName}</li>`);
                        });
                        
                        aiTagsContainer.append(aiTagsList);
                        
                        if (previewData.ai_enabled) {
                            const generateButton = $('<button type="button" class="button button-primary" id="generate-ai-content">Generate AI Content</button>');
                            aiTagsContainer.append(generateButton);
                        } else {
                            aiTagsContainer.append('<div class="notice notice-warning inline"><p>AI content generation is disabled. Please configure your API key in the plugin settings.</p></div>');
                        }
                        
                        $('#preview-result').append(aiTagsContainer);
                    }
                    
                    // Add apply tags button
                    const applyButton = $('<div class="apply-tag-button"></div>');
                    applyButton.append('<button type="button" class="button button-primary" id="apply-tags">Apply Tag Replacements</button>');
                    $('#preview-result').append(applyButton);
                    
                    // Tab switching
                    $('.preview-tab-link').on('click', function(e) {
                        e.preventDefault();
                        
                        // Get the target tab
                        const target = $(this).attr('href');
                        
                        // Remove active class from all tabs and panes
                        $('.preview-tab-link').removeClass('active');
                        $('.preview-tab-pane').removeClass('active');
                        
                        // Add active class to clicked tab and corresponding pane
                        $(this).addClass('active');
                        $(target).addClass('active');
                    });
                } else {
                    // Show error message
                    $('#preview-result').html('<div class="notice notice-error"><p>' + response.data + '</p></div>').show();
                }
            },
            error: function() {
                // Hide loading indicator and show error message
                $('#loading-preview').hide();
                $('#preview-result').html('<div class="notice notice-error"><p>Error getting preview</p></div>').show();
            }
        });
    });
}); 