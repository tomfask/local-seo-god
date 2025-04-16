<?php

/**
 * AI Handler for Local SEO God
 * 
 * Handles AI content generation for the Local SEO God plugin
 */
class LocalSeoGod_AI_Handler {

    /**
     * OpenAI API key
     * @var string
     */
    private $api_key;
    
    /**
     * AI SOP content
     * @var array
     */
    private $ai_sop_files = array();
    
    /**
     * SOP files already loaded flag
     * @var boolean
     */
    private $sop_files_loaded = false;
    
    /**
     * Constructor
     * 
     * @param string $api_key OpenAI API key
     */
    public function __construct($api_key) {
        $this->api_key = $api_key;
    }
    
    /**
     * Load individual SOP files for each tag type
     * 
     * @return boolean True if files were loaded successfully
     */
    private function load_sop_files() {
        // Only load files once
        if ($this->sop_files_loaded) {
            return true;
        }
        
        $start_time = microtime(true);
        $base_dir = plugin_dir_path(dirname(__FILE__)) . 'views/';
        
        // Define the mapping of individual SOP files
        $sop_files = array(
            'ai-home-introduction' => 'ai-home-introduction.md',
            'ai-why-us' => 'ai-why-us.md',
            'ai-service-overview' => 'ai-service-overview.md',
            'ai-why-us-section' => 'ai-why-us-section.md',
            'ai-faq-services' => 'ai-faq-services.md'
            // No fallback to ai-content-sop.md as per request - using individual SOPs only
        );
        
        // Load each SOP file content
        foreach ($sop_files as $tag_type => $filename) {
            $file_path = $base_dir . $filename;
            if (file_exists($file_path)) {
                $this->ai_sop_files[$tag_type] = file_get_contents($file_path);
                error_log("Local SEO God: Loaded SOP file for $tag_type");
            } else {
                error_log("Local SEO God: SOP file not found for $tag_type at $file_path");
                $this->ai_sop_files[$tag_type] = '';
            }
        }
        
        // Add fallback to generic SOP file
        $generic_sop_path = $base_dir . 'ai-content-sop.md';
        if (file_exists($generic_sop_path)) {
            $this->ai_sop_files['generic'] = file_get_contents($generic_sop_path);
            error_log("Local SEO God: Loaded generic SOP file for fallback");
        } else {
            error_log("Local SEO God: Generic SOP file not found at $generic_sop_path");
        }
        
        // If we couldn't load any SOP files, log an error
        if (empty($this->ai_sop_files)) {
            error_log('Local SEO God: Failed to load any SOP files. AI content generation may not work correctly.');
            return false;
        }
        
        $this->sop_files_loaded = true;
        $load_time = microtime(true) - $start_time;
        error_log("Local SEO God: SOP files loaded in {$load_time} seconds");
        
        return true;
    }
    
    /**
     * Set the API key
     * 
     * @param string $api_key OpenAI API key
     */
    public function set_api_key($api_key) {
        $this->api_key = $api_key;
    }
    
    /**
     * Clean generated content
     * 
     * @param string $content The content to clean
     * @return string The cleaned content
     */
    private function clean_generated_content($content) {
        // Remove code blocks if present
        $content = preg_replace('/```(?:html|php)?\s*(.*?)\s*```/s', '$1', $content);
        
        // Remove any markdown formatting
        $content = preg_replace('/\*\*(.*?)\*\*/', '$1', $content);
        $content = preg_replace('/\*(.*?)\*/', '$1', $content);
        $content = preg_replace('/__(.*?)__/', '$1', $content);
        $content = preg_replace('/_(.*?)_/', '$1', $content);
        
        // Clean up excessive whitespace
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        $content = trim($content);
        
        return $content;
    }
    
    /**
     * Replace remaining placeholders in content
     * 
     * @param string $content The content with placeholders
     * @param array $business_info Business information for replacement
     * @return string The content with placeholders replaced
     */
    private function replace_remaining_placeholders($content, $business_info) {
        // Replace any remaining tags or placeholders with actual values
        $patterns = array(
            '/\{Business-?Name\}/i' => $business_info['business_name'],
            '/\{GMB-?Service\}/i' => $business_info['gmb_service'],
            '/\{Target-?Location\}/i' => $business_info['target_location'],
            '/\{Main-?Keyword\}/i' => $business_info['main_keyword'],
            '/\{domain(?:-?name)?\}/i' => $business_info['domain'],
            '/\{Business-?Description\}/i' => $business_info['business_description'],
        );
        
        // Replace service and area tags with actual values
        if (isset($business_info['services']) && is_array($business_info['services'])) {
            foreach ($business_info['services'] as $index => $service) {
                $service_num = $index + 1;
                $patterns['/\{Service-' . $service_num . '\}/i'] = $service;
            }
        }
        
        if (isset($business_info['service_areas']) && is_array($business_info['service_areas'])) {
            foreach ($business_info['service_areas'] as $index => $area) {
                $area_num = $index + 1;
                $patterns['/\{area-' . $area_num . '\}/i'] = $area;
            }
        }
        
        // Replace "your business", "your service", etc.
        $content = str_replace(
            array('Your Business', 'your business', 'Your Service', 'your service', 'Your Location', 'your location'),
            array($business_info['business_name'], $business_info['business_name'], $business_info['gmb_service'], $business_info['gmb_service'], $business_info['target_location'], $business_info['target_location']),
            $content
        );
        
        // Replace placeholders with patterns
        foreach ($patterns as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }
        
        return $content;
    }
    
    /**
     * Normalize business info to ensure all required fields are present
     * 
     * @param array $business_info Business information
     * @return array Normalized business information
     */
    private function normalize_business_info($business_info) {
        $defaults = array(
            'business_name' => 'Your Business',
            'gmb_service' => 'Local Service',
            'target_location' => 'Your Location',
            'main_keyword' => 'Local Service Provider',
            'domain' => 'example.com',
            'business_description' => 'A local business providing quality services',
            'services' => array(),
            'service_areas' => array(),
            'target_keywords' => array()
        );
        
        $normalized = array_merge($defaults, $business_info);
        
        // Ensure arrays are properly initialized
        foreach (array('services', 'service_areas', 'target_keywords') as $array_key) {
            if (!isset($normalized[$array_key]) || !is_array($normalized[$array_key])) {
                $normalized[$array_key] = array();
            }
        }
        
        return $normalized;
    }
    
    /**
     * Get the appropriate SOP content for a tag
     * 
     * @param string $tag_name The tag name (without braces)
     * @return string The SOP content for the tag
     */
    private function get_sop_content_for_tag($tag_name) {
        // Load SOP files if needed
        if (!$this->sop_files_loaded) {
            $this->load_sop_files();
        }
        
        // Base tag name (remove numbers for numbered tags)
        $base_tag = preg_replace('/-\d+$/', '', $tag_name);
        
        // Special handling for FAQ tags
        if (strpos($tag_name, 'faq-title') !== false || strpos($tag_name, 'faq-answer') !== false) {
            if (strpos($tag_name, 'service-faq') !== false) {
                if (isset($this->ai_sop_files['ai-faq-services'])) {
                    return $this->ai_sop_files['ai-faq-services'];
                } else {
                    error_log("Local SEO God: Missing SOP file for FAQ services tag: $tag_name");
                    return '';
                }
            }
        }
        
        // For the main tag types, use their dedicated SOP files
        $main_tags = array(
            'ai-home-introduction',
            'ai-why-us',
            'ai-service-overview',
            'ai-why-us-section'
        );
        
        if (in_array($base_tag, $main_tags)) {
            if (isset($this->ai_sop_files[$base_tag])) {
                return $this->ai_sop_files[$base_tag];
            } else {
                error_log("Local SEO God: Missing SOP file for tag: $base_tag");
                return '';
            }
        }
        
        // If no specific SOP file is found, return the generic SOP file content
        if (isset($this->ai_sop_files['generic'])) {
            error_log('Local SEO God: Using generic SOP for tag: ' . $tag_name);
            return $this->ai_sop_files['generic'];
        }
        
        // If even the generic SOP is missing, return empty string
        error_log("Local SEO God: No SOP content available for tag: $tag_name");
        return '';
    }
    
    /**
     * Get section instructions from the proper SOP file for a specific tag
     * 
     * @param string $tag The tag to find instructions for
     * @param array $business_info Business information for tag replacement
     * @return string The instructions for the tag with placeholders replaced
     */
    private function get_section_instructions($tag, $business_info) {
        // Remove curly braces from tag
        $tag_name = str_replace(array('{', '}'), '', $tag);
        
        // First normalize business info to ensure all required fields have values
        $normalized_info = $this->normalize_business_info($business_info);
        
        error_log('Local SEO God: Getting instructions for tag: ' . $tag_name);
        
        // Get the appropriate SOP content for this tag
        $tag_sop_content = $this->get_sop_content_for_tag($tag_name);
        
        if (empty($tag_sop_content)) {
            error_log('Local SEO God: No SOP content found for tag: ' . $tag_name . '. Please check if the corresponding SOP file exists.');
            return '';
        }
        
        // Special handling for different tag types
        $instructions = '';
        
        // For home introduction
        if ($tag_name === 'ai-home-introduction') {
            $instructions = "GENERATING HOME INTRODUCTION FOR " . $normalized_info['business_name'] . "\n\n";
            $instructions .= "CRITICAL INSTRUCTION: You MUST EXACTLY follow the format and structure as provided in the example below.\n\n";
            $instructions .= $tag_sop_content;
            
            // Extract example and structure for clarity
            if (preg_match('/Example:(.*?)Validation/s', $tag_sop_content, $example_match)) {
                $example = trim($example_match[1]);
                $instructions .= "\n\nYOU MUST FOLLOW THIS EXACT FORMAT (adapt to the business details):\n" . $example;
                $instructions .= "\n\nDO NOT DEVIATE FROM THIS STRUCTURE IN ANY WAY. The format is non-negotiable.";
            }
        }
        // For service overview - ensure strict HTML format
        else if ($tag_name === 'ai-service-overview') {
            $current_service = isset($normalized_info['service']) ? $normalized_info['service'] : 
                (isset($normalized_info['services'][0]) ? $normalized_info['services'][0] : $normalized_info['gmb_service']);
            
            $instructions = "GENERATING SERVICE OVERVIEW FOR " . $current_service . " BY " . $normalized_info['business_name'] . "\n\n";
            $instructions .= "CRITICAL INSTRUCTION: You MUST EXACTLY follow the HTML format provided below without any modifications to the structure.\n\n";
            
            // Extract HTML Output Format example - this is crucial for proper formatting
            if (preg_match('/HTML Output Format example(.*?)Validation/s', $tag_sop_content, $example_match)) {
                $html_format = trim($example_match[1]);
                
                // Replace any placeholder content with specific instructions
                $html_format = str_replace('{Business-Name}', $normalized_info['business_name'], $html_format);
                $html_format = str_replace('{Target-Location}', $normalized_info['target_location'], $html_format);
                $html_format = str_replace('{Single-Service}', $current_service, $html_format);
                
                // Keep the aspect placeholders but add clear instructions
                $html_format = str_replace('{Aspect-1}', '[FIRST key aspect of the ' . $current_service . ' service]', $html_format);
                $html_format = str_replace('{Aspect-2}', '[SECOND key aspect of the ' . $current_service . ' service]', $html_format);
                $html_format = str_replace('{Aspect-3}', '[THIRD key aspect of the ' . $current_service . ' service]', $html_format);
                $html_format = str_replace('{Aspect-4}', '[FOURTH key aspect of the ' . $current_service . ' service]', $html_format);
                $html_format = str_replace('{Aspect-5}', '[FIFTH key aspect of the ' . $current_service . ' service]', $html_format);
                
                $instructions .= "YOU MUST RETURN CONTENT IN THIS EXACT HTML FORMAT (with your own content for the service aspects):\n\n";
                $instructions .= $html_format;
                $instructions .= "\n\nCRITICAL: You MUST maintain all HTML tags (<p>, <ul>, <li>) exactly as shown above.";
            } else {
                $instructions .= $tag_sop_content;
            }
            
            // Add specific service context and final reminders
            $instructions .= "\n\nIMPORTANT: This overview is specifically for " . $current_service . 
                " provided by " . $normalized_info['business_name'] . " in " . $normalized_info['target_location'] . ".";
            
            $instructions .= "\n\nFINAL REMINDER: Your output MUST include:
1. The EXACT same HTML structure shown above
2. The text 'Our " . $current_service . " includes:' exactly as written here
3. A bulleted list with 5 aspects of the service, properly formatted with <ul> and <li> tags
4. Do NOT alter any HTML formatting or tag structure";
        }
        // For FAQ questions - strict separation handling
        else if (preg_match('/^ai-(?:service-)?faq-title-(\d+)$/', $tag_name, $faq_matches)) {
            $faq_number = $faq_matches[1];
            $current_service = isset($normalized_info['service']) ? $normalized_info['service'] : 
                (isset($normalized_info['services'][0]) ? $normalized_info['services'][0] : $normalized_info['gmb_service']);
            
            $instructions = "GENERATING FAQ QUESTION #$faq_number FOR " . $current_service . "\n\n";
            $instructions .= "CRITICAL INSTRUCTION: You are ONLY writing a QUESTION about " . $current_service . ", NOT an answer.\n\n";
            $instructions .= "The question must be related to " . $current_service . " in " . $normalized_info['target_location'] . ".\n\n";
            
            // Try to find the specific example based on question number
            if (preg_match('/FAQ Title ' . $faq_number . '.*?Example:(.*?)(?=----|\Z)/s', $tag_sop_content, $title_match) ||
                preg_match('/Title Tag: \{ai-service-faq-title-' . $faq_number . '\}.*?Example:(.*?)(?=----|\Z)/s', $tag_sop_content, $title_match)) {
                $example = trim($title_match[1]);
                $instructions .= "Question format example:\n" . $example . "\n\n";
            } else {
                // Generic question examples if specific one not found
                $instructions .= "Example question formats:\n";
                $instructions .= "- How much does " . $current_service . " cost in " . $normalized_info['target_location'] . "?\n";
                $instructions .= "- What is the best type of " . $current_service . " for homes in " . $normalized_info['target_location'] . "?\n";
                $instructions .= "- How long does it take to complete " . $current_service . " in " . $normalized_info['target_location'] . "?\n\n";
            }
            
            $instructions .= "CRITICAL REMINDER: I need ONLY the question. DO NOT provide the answer.";
            $instructions .= "\nKeep the question concise (10-15 words) and focused on " . $current_service . ".";
            $instructions .= "\nDO NOT include any HTML formatting unless explicitly shown in the example.";
        }
        // For FAQ answers - strict separation handling
        else if (preg_match('/^ai-(?:service-)?faq-answer-(\d+)$/', $tag_name, $faq_matches)) {
            $faq_number = $faq_matches[1];
            $current_service = isset($normalized_info['service']) ? $normalized_info['service'] : 
                (isset($normalized_info['services'][0]) ? $normalized_info['services'][0] : $normalized_info['gmb_service']);
            
            $instructions = "GENERATING FAQ ANSWER #$faq_number FOR " . $current_service . "\n\n";
            $instructions .= "CRITICAL INSTRUCTION: You are ONLY writing an ANSWER about " . $current_service . ", NOT a question.\n\n";
            $instructions .= "The answer must relate to " . $current_service . " in " . $normalized_info['target_location'] . ".\n\n";
            
            // Try to find the specific example based on answer number
            if (preg_match('/FAQ Answer ' . $faq_number . '.*?Example:(.*?)(?=----|\Z)/s', $tag_sop_content, $answer_match) ||
                preg_match('/Answer Tag: \{ai-service-faq-answer-' . $faq_number . '\}.*?Example:(.*?)(?=----|\Z)/s', $tag_sop_content, $answer_match)) {
                $example = trim($answer_match[1]);
                $instructions .= "Answer format example:\n" . $example . "\n\n";
            } else {
                // Generic answer guidelines if specific one not found
                $instructions .= "Answer Guidelines:\n";
                $instructions .= "- Write a concise but informative answer (75-150 words)\n";
                $instructions .= "- Provide specific information about " . $current_service . " as offered by " . $normalized_info['business_name'] . "\n";
                $instructions .= "- Include location-specific details relevant to " . $normalized_info['target_location'] . "\n";
                $instructions .= "- Maintain a professional, authoritative tone\n\n";
            }
            
            $instructions .= "CRITICAL REMINDER: I need ONLY the answer. DO NOT repeat the question.";
            $instructions .= "\nThe answer should be informative (75-150 words).";
            $instructions .= "\nDO NOT include any HTML formatting unless explicitly shown in the example.";
        }
        // Default handling for any other tags
        else {
            $instructions = "GENERATING CONTENT FOR " . $tag_name . " BY " . $normalized_info['business_name'] . "\n\n";
            $instructions .= $tag_sop_content;
        }
        
        // Add business context to all instructions
        $business_context = "BUSINESS CONTEXT:\n";
        $business_context .= "Business Name: " . $normalized_info['business_name'] . "\n";
        $business_context .= "Main Service: " . $normalized_info['gmb_service'] . "\n";
        $business_context .= "Target Location: " . $normalized_info['target_location'] . "\n";
        $business_context .= "Main Keyword: " . $normalized_info['main_keyword'] . "\n";
        
        // Add more specific context if available
        if (isset($normalized_info['business_description']) && !empty($normalized_info['business_description'])) {
            $business_context .= "Business Description: " . $normalized_info['business_description'] . "\n";
        }
        
        // Add service-specific context if relevant
        if (isset($normalized_info['service']) && !empty($normalized_info['service'])) {
            $business_context .= "Current Service: " . $normalized_info['service'] . "\n";
        }
        
        // Add area-specific context if relevant
        if (isset($normalized_info['area']) && !empty($normalized_info['area'])) {
            $business_context .= "Current Area: " . $normalized_info['area'] . "\n";
        }
        
        // Final reminder about exact format
        $final_reminder = "\n\nFINAL REMINDER: You MUST adhere to the EXACT format shown in the example. Do not add any extra elements, sections, or formatting not shown in the example.";
        
        // Combine all instructions
        $complete_instructions = $instructions . "\n\n" . $business_context . $final_reminder;
        
        // Preprocess instructions to replace any business info tags
        return $this->preprocess_instructions($complete_instructions, $normalized_info);
    }
    
    /**
     * Preprocess instructions for tag replacements
     *
     * @param string $instructions The raw instructions with tags to replace
     * @param array $business_info Business information array
     * @param array $extra_info Extra information for specific replacements
     * @return string The processed instructions with all tags replaced
     */
    public function preprocess_instructions($instructions, $business_info, $extra_info = array()) {
        $time_start = microtime(true);
        $logger = LocalSeoGod_Logger::get_instance();
        $logger->log('AI Handler: Beginning instruction preprocessing');
        
        // Create an associative array of patterns to replace
        $patterns = array(
            '{Business-Name}' => isset($business_info['business_name']) ? $business_info['business_name'] : '',
            '{GMB-Service}' => isset($extra_info['service']) ? $extra_info['service'] : '',
            '{Target-Location}' => isset($extra_info['area']) ? $extra_info['area'] : '',
            '{Business-Type}' => isset($business_info['business_type']) ? $business_info['business_type'] : '',
            '{GMB-Description}' => isset($business_info['business_description']) ? $business_info['business_description'] : '',
            '{Business-Address}' => isset($business_info['business_address']) ? $business_info['business_address'] : '',
            '{Business-City}' => isset($business_info['business_city']) ? $business_info['business_city'] : '',
            '{Business-State}' => isset($business_info['business_state']) ? $business_info['business_state'] : '',
            '{Business-Zip}' => isset($business_info['business_zip']) ? $business_info['business_zip'] : '',
            '{Business-Phone}' => isset($business_info['business_phone']) ? $business_info['business_phone'] : '',
            '{Business-Email}' => isset($business_info['business_email']) ? $business_info['business_email'] : '',
            '{Business-Website}' => isset($business_info['business_website']) ? $business_info['business_website'] : '',
            '{Business-Hours}' => isset($business_info['business_hours']) ? $business_info['business_hours'] : '',
        );
        
        // Add service and area related keys if available
        if (!empty($business_info['services']) && is_array($business_info['services'])) {
            $patterns['{All-GMB-Services}'] = implode(', ', $business_info['services']);
        }
        
        if (!empty($business_info['service_areas']) && is_array($business_info['service_areas'])) {
            $patterns['{All-Service-Areas}'] = implode(', ', $business_info['service_areas']);
        }
        
        // Replace area specific services if available
        if (isset($extra_info['area']) && !empty($extra_info['area'])) {
            $area = $extra_info['area'];
            $patterns['{Area-Specific-Services}'] = $this->get_area_specific_service_list($business_info, $area);
        }
        
        // Service specific replacements
        if (isset($extra_info['service']) && !empty($extra_info['service'])) {
            $service = $extra_info['service'];
            $patterns['{Service-Related-Areas}'] = $this->get_service_related_area_list($business_info, $service);
        }
        
        // Count the total patterns to replace
        $logger->log('AI Handler: Replacing ' . count($patterns) . ' patterns in instructions');
        
        // Replace all patterns in the instructions
        foreach ($patterns as $pattern => $replacement) {
            if (empty($replacement)) {
                $logger->log("AI Handler: Warning - Empty replacement for pattern: $pattern");
                continue;
            }
            $instructions = str_replace($pattern, $replacement, $instructions);
        }
        
        // Check for any unprocessed tags
        preg_match_all('/{[^}]+}/', $instructions, $matches);
        if (!empty($matches[0])) {
            $logger->log('AI Handler: Warning - Unprocessed tags found: ' . implode(', ', $matches[0]));
        }
        
        $time_end = microtime(true);
        $logger->log('AI Handler: Instruction preprocessing completed in ' . ($time_end - $time_start) . ' seconds');
        
        return $instructions;
    }

    /**
     * Regenerate content for a specific tag
     *
     * @param string $tag The AI tag to regenerate content for
     * @param array $business_info Business information array
     * @param array $extra_info Extra information for specific replacements
     * @return string|false The generated content or false on failure
     */
    public function regenerate_content_for_tag($tag, $business_info = array(), $extra_info = array()) {
        $logger = LocalSeoGod_Logger::get_instance();
        $logger->log("AI Handler: Regenerating content for tag: $tag");
        
        // Check if the api key exists
        if (empty($this->api_key)) {
            $logger->log('AI Handler: Error - No API key provided for content regeneration');
            return false;
        }
        
        // Determine the content type based on the tag
        $content_type = $this->get_content_type_from_tag($tag);
        if (!$content_type) {
            $logger->log("AI Handler: Error - Unknown content type for tag: $tag");
            return false;
        }
        
        $logger->log("AI Handler: Content type for tag $tag is: $content_type");
        
        // Get SOP content for the tag
        $sop_content = $this->get_sop_content_for_tag($tag);
        if (!$sop_content) {
            $logger->log("AI Handler: Error - No SOP content found for tag: $tag");
            return false;
        }
        
        // Get word count requirement for this content type
        $word_count = $this->get_word_count_for_content_type($content_type);
        
        // Prepare the instructions with tag replacements
        $instructions = $this->get_prompt_for_tag($tag, $business_info);
        
        // Enhanced FAQ handling
        if (strpos($tag, 'faq') !== false) {
            // Create a stronger, more explicit instruction for FAQ format
            $faq_instructions = "\n\nVERY IMPORTANT FORMATTING INSTRUCTIONS:
1. Format your response as distinct question-answer pairs ONLY.
2. Each question MUST be formatted as an <h3> heading (using the <h3> tag).
3. Each answer MUST follow its question as a paragraph.
4. DO NOT create a continuous paragraph of text.
5. DO NOT include any preamble or conclusion text.
6. EXAMPLE FORMAT:
   <h3>What is [specific service question]?</h3>
   [Answer to the question in paragraph form]
   
   <h3>How does [another specific question]?</h3>
   [Answer to this question in paragraph form]";
            
            $instructions .= $faq_instructions;
        }
        
        // Add word count instruction if specified
        if ($word_count > 0) {
            $instructions .= "\n\nProvide approximately $word_count words of content.";
        }
        
        $logger->log("AI Handler: Sending request to API for tag: $tag");
        
        // Call the API to generate content
        $response = $this->call_api_for_content($instructions);
        
        if ($response === false) {
            $logger->log("AI Handler: Error - API request failed for tag: $tag");
            return false;
        }
        
        // Post-processing for FAQs to ensure proper formatting
        if (strpos($tag, 'faq') !== false && !empty($response)) {
            $logger->log("AI Handler: Post-processing FAQ response to ensure proper formatting");
            $response = $this->format_faq_response($response);
        }
        
        $logger->log("AI Handler: Successfully generated content for tag: $tag");
        
        return $response;
    }

    /**
     * Format FAQ response to ensure proper HTML structure
     *
     * @param string $content The raw content from the API
     * @return string Properly formatted FAQ content
     */
    private function format_faq_response($content) {
        $logger = LocalSeoGod_Logger::get_instance();
        
        // If content already has h3 tags, it's likely properly formatted
        if (strpos($content, '<h3>') !== false) {
            $logger->log("AI Handler: FAQ content already contains h3 tags, returning as is");
            return $content;
        }
        
        $logger->log("AI Handler: Reformatting FAQ response to proper HTML structure");
        
        // Look for patterns that might indicate questions
        // 1. Lines ending with question marks
        // 2. Lines in all caps or starting with "Q:"
        // 3. Numbered questions (1., 2., etc.)
        
        $lines = explode("\n", $content);
        $formatted_content = '';
        $in_answer = false;
        $answer_buffer = '';
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Check if this line looks like a question
            $is_question = (
                (substr($line, -1) === '?') || // Ends with question mark
                (preg_match('/^Q[.:]/i', $line)) || // Starts with Q: or Q.
                (preg_match('/^[0-9]+[.)]/', $line)) || // Starts with number followed by . or )
                (strtoupper($line) === $line && strlen($line) > 10) // All caps and reasonably long
            );
            
            if ($is_question) {
                // If we were in an answer, finish it before starting new question
                if ($in_answer && !empty($answer_buffer)) {
                    $formatted_content .= "<p>" . trim($answer_buffer) . "</p>\n\n";
                    $answer_buffer = '';
                }
                
                // Clean up the question text
                $question = preg_replace('/^Q[.:]\\s*/i', '', $line); // Remove Q: or Q.
                $question = preg_replace('/^[0-9]+[.)]\\s*/', '', $question); // Remove numbering
                
                // Add the question as h3
                $formatted_content .= "<h3>" . $question . "</h3>\n";
                $in_answer = true;
            } 
            elseif ($in_answer) {
                // We're in an answer, accumulate the text
                $answer_buffer .= " " . $line;
            } 
            else {
                // Not in an answer and not a question, treat as introductory text
                $formatted_content .= "<p>" . $line . "</p>\n";
            }
        }
        
        // Don't forget to add the last answer if there is one
        if ($in_answer && !empty($answer_buffer)) {
            $formatted_content .= "<p>" . trim($answer_buffer) . "</p>\n";
        }
        
        $logger->log("AI Handler: FAQ reformatting complete");
        
        return $formatted_content;
    }

    /**
     * Make API request to OpenAI
     * 
     * @param string $system_prompt The system prompt for the API
     * @param string $user_prompt The user prompt for the API
     * @return array|WP_Error The API response or error
     */
    private function make_api_request($system_prompt, $user_prompt) {
        error_log('Local SEO God: Making API request to OpenAI');
        
        $api_url = 'https://api.openai.com/v1/chat/completions';
        
        $headers = array(
            'Authorization' => 'Bearer ' . $this->api_key,
            'Content-Type' => 'application/json'
        );
        
        $data = array(
            'model' => 'gpt-4o-mini',  // Using GPT-4o-mini as requested
            'messages' => array(
                array(
                    'role' => 'system',
                    'content' => $system_prompt
                ),
                array(
                    'role' => 'user',
                    'content' => $user_prompt
                )
            ),
            'max_tokens' => 1500,
            'temperature' => 0.7,
            'top_p' => 1,
            'frequency_penalty' => 0,
            'presence_penalty' => 0
        );
        
        $args = array(
            'headers' => $headers,
            'body' => json_encode($data),
            'timeout' => 60,
            'redirection' => 5,
            'httpversion' => '1.1',
            'blocking' => true,
            'data_format' => 'body'
        );
        
        error_log('Local SEO God: API request data - ' . print_r($data, true));
        
        $response = wp_remote_post($api_url, $args);
        
        if (is_wp_error($response)) {
            error_log('Local SEO God: API request error - ' . $response->get_error_message());
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        error_log('Local SEO God: API response code - ' . $response_code);
        
        if ($response_code !== 200) {
            error_log('Local SEO God: API error response - ' . $response_body);
            return new WP_Error('api_error', 'OpenAI API returned error: ' . $response_body);
        }
        
        $response_data = json_decode($response_body, true);
        
        if (empty($response_data['choices'][0]['message']['content'])) {
            error_log('Local SEO God: Invalid API response format');
            return new WP_Error('invalid_response', 'Invalid response format from OpenAI API');
        }
        
        $content = $response_data['choices'][0]['message']['content'];
        error_log('Local SEO God: Generated content length - ' . strlen($content));
        
        return $this->clean_generated_content($content);
    }

    /**
     * Get content type from tag
     * 
     * @param string $tag The AI tag
     * @return string|false The content type or false if unknown
     */
    private function get_content_type_from_tag($tag) {
        // Convert tag to lowercase for case-insensitive matching
        $tag = strtolower($tag);
        
        if (strpos($tag, 'introduction') !== false) {
            return 'introduction';
        } else if (strpos($tag, 'service-overview') !== false) {
            return 'service_overview';
        } else if (strpos($tag, 'why-us') !== false) {
            return 'why_us';
        } else if (strpos($tag, 'faq-title') !== false) {
            return 'faq_title';
        } else if (strpos($tag, 'faq-answer') !== false) {
            return 'faq_answer';
        }
        
        return false;
    }
    
    /**
     * Get word count requirement for content type
     * 
     * @param string $content_type The content type
     * @return int The recommended word count (0 means no specific requirement)
     */
    private function get_word_count_for_content_type($content_type) {
        switch ($content_type) {
            case 'introduction':
                return 100;
            case 'service_overview':
                return 250;
            case 'why_us':
                return 200;
            case 'faq_title':
                return 0; // Just a short question
            case 'faq_answer':
                return 100;
            default:
                return 0;
        }
    }

    /**
     * Get area-specific service list
     *
     * @param array $business_info Business information array
     * @param string $area The specific area
     * @return string HTML-formatted list of services for that area
     */
    private function get_area_specific_service_list($business_info, $area) {
        $logger = LocalSeoGod_Logger::get_instance();
        
        if (empty($business_info['services']) || !is_array($business_info['services']) || empty($area)) {
            $logger->log('Warning: Cannot create area-specific service list, missing services or area');
            return '';
        }
        
        $services = $business_info['services'];
        $domain = isset($business_info['domain']) ? rtrim($business_info['domain'], '/') : '';
        
        if (empty($domain)) {
            $logger->log('Warning: Cannot create area-specific service list, missing domain');
            return '';
        }
        
        $html = '';
        foreach ($services as $service) {
            $url = "https://www.{$domain}/" . sanitize_title($service) . '-' . sanitize_title($area);
            $html .= "<a href=\"{$url}\">{$service} {$area}</a>, ";
        }
        
        return rtrim($html, ', ');
    }
    
    /**
     * Get service-related area list
     *
     * @param array $business_info Business information array
     * @param string $service The specific service
     * @return string HTML-formatted list of areas for that service
     */
    private function get_service_related_area_list($business_info, $service) {
        $logger = LocalSeoGod_Logger::get_instance();
        
        if (empty($business_info['service_areas']) || !is_array($business_info['service_areas']) || empty($service)) {
            $logger->log('Warning: Cannot create service-related area list, missing areas or service');
            return '';
        }
        
        $areas = $business_info['service_areas'];
        $domain = isset($business_info['domain']) ? rtrim($business_info['domain'], '/') : '';
        
        if (empty($domain)) {
            $logger->log('Warning: Cannot create service-related area list, missing domain');
            return '';
        }
        
        $html = '';
        foreach ($areas as $area) {
            $url = "https://www.{$domain}/" . sanitize_title($service) . '-' . sanitize_title($area);
            $html .= "<a href=\"{$url}\">{$service} {$area}</a>, ";
        }
        
        return rtrim($html, ', ');
    }

    /**
     * Get the appropriate prompt/instructions for a tag
     *
     * @param string $tag The AI tag
     * @param array $normalized_info Normalized business information
     * @return string The complete instructions for the AI
     */
    private function get_prompt_for_tag($tag, $normalized_info) {
        $logger = LocalSeoGod_Logger::get_instance();
        $logger->log("Preparing prompt for tag: $tag");
        
        // Clean up tag name for easier processing
        $tag_name = strtolower(trim($tag, '{}'));
        
        // Get the SOP content for this tag
        $tag_sop_content = $this->get_sop_content_for_tag($tag);
        
        if (!$tag_sop_content) {
            $logger->log("No SOP content found for tag: $tag");
            return "ERROR: NO SOP CONTENT FOUND FOR THIS TAG";
        }
        
        $instructions = '';
        
        // Handle FAQ tags specially
        if (preg_match('/ai-service-faq-(title|answer)-(\d+)/i', $tag_name, $matches)) {
            $faq_type = $matches[1]; // title or answer
            $faq_number = $matches[2]; // numeric index
            
            if ($faq_type === 'title') {
                $instructions = "GENERATE A FAQ QUESTION ABOUT " . strtoupper($normalized_info['service']) . " IN " . strtoupper($normalized_info['area']) . "\n\n";
                $instructions .= "Your task is to write a compelling, SEO-friendly question that a potential customer might ask about " . $normalized_info['service'] . " in " . $normalized_info['area'] . ".\n\n";
                $instructions .= "CRITICAL: This is ONLY the question - approximately 10-15 words. The answer will be generated separately.\n\n";
                $instructions .= "QUESTION FORMAT EXAMPLES:\n";
                $instructions .= "- What is the cost of [service] in [area]?\n";
                $instructions .= "- How long does [service] take to complete in [area]?\n";
                $instructions .= "- Why should I choose professional [service] for my [area] home?\n\n";
                $instructions .= "I want an engaging question for FAQ #" . $faq_number . " about " . $normalized_info['service'] . " in " . $normalized_info['area'] . ".";
            } else { // answer
                $instructions = "GENERATE A FAQ ANSWER ABOUT " . strtoupper($normalized_info['service']) . " IN " . strtoupper($normalized_info['area']) . "\n\n";
                $instructions .= "Your task is to write a helpful, informative answer about " . $normalized_info['service'] . " in " . $normalized_info['area'] . " that a business would provide to potential customers.\n\n";
                $instructions .= "The answer should be:\n";
                $instructions .= "- Approximately 75-150 words\n";
                $instructions .= "- Informative and educational\n";
                $instructions .= "- Include at least one location-specific detail about " . $normalized_info['area'] . "\n";
                $instructions .= "- Mention the business name (" . $normalized_info['business_name'] . ") positively\n";
                $instructions .= "- Maintain a professional, authoritative tone\n\n";
                $instructions .= "CRITICAL REMINDER: I need ONLY the answer. DO NOT repeat the question.";
                $instructions .= "\nThe answer should be informative (75-150 words).";
                $instructions .= "\nDO NOT include any HTML formatting unless explicitly shown in the example.";
            }
        }
        // Default handling for any other tags
        else {
            $instructions = "GENERATING CONTENT FOR " . $tag_name . " BY " . $normalized_info['business_name'] . "\n\n";
            $instructions .= $tag_sop_content;
        }
        
        // Add business context to all instructions
        $business_context = "BUSINESS CONTEXT:\n";
        $business_context .= "Business Name: " . $normalized_info['business_name'] . "\n";
        $business_context .= "Main Service: " . $normalized_info['gmb_service'] . "\n";
        $business_context .= "Target Location: " . $normalized_info['target_location'] . "\n";
        $business_context .= "Main Keyword: " . $normalized_info['main_keyword'] . "\n";
        
        // Add more specific context if available
        if (isset($normalized_info['business_description']) && !empty($normalized_info['business_description'])) {
            $business_context .= "Business Description: " . $normalized_info['business_description'] . "\n";
        }
        
        // Add service-specific context if relevant
        if (isset($normalized_info['service']) && !empty($normalized_info['service'])) {
            $business_context .= "Current Service: " . $normalized_info['service'] . "\n";
        }
        
        // Add area-specific context if relevant
        if (isset($normalized_info['area']) && !empty($normalized_info['area'])) {
            $business_context .= "Current Area: " . $normalized_info['area'] . "\n";
        }
        
        // Final reminder about exact format
        $final_reminder = "\n\nFINAL REMINDER: You MUST adhere to the EXACT format shown in the example. Do not add any extra elements, sections, or formatting not shown in the example.";
        
        // Combine all instructions
        $complete_instructions = $instructions . "\n\n" . $business_context . $final_reminder;
        
        // Preprocess instructions to replace any business info tags
        return $this->preprocess_instructions($complete_instructions, $normalized_info);
    }

    /**
     * Generate content using AI
     *
     * @param array $tags Tags to generate content for
     * @param array $business_info Business information
     * @param string $content_so_far Content generated so far
     * @return array|WP_Error Generated content or error
     */
    public function generate_content($tags = array(), $business_info = array(), $content_so_far = '') {
        try {
            // If business_info isn't provided, get it from unified config
            if (empty($business_info)) {
                $config = get_option('local_seo_god_config', array());
                $business_info = isset($config['business']) ? $config['business'] : get_option('local_seo_god_business_info', array());
            }
            
            // Ensure business info has needed fields
            $business_info = $this->normalize_business_info($business_info);
            
            $generated_content = array();
            
            // If there are specific tags to generate, process them
            if (!empty($tags)) {
                foreach ($tags as $tag) {
                    $tag_content = $this->generate_content_for_tag($tag, $business_info, $content_so_far);
                    if ($tag_content) {
                        $generated_content[$tag] = $tag_content;
                    }
                }
            }
            
            return $generated_content;
            
        } catch (Exception $e) {
            return new WP_Error('ai_generation_error', $e->getMessage());
        }
    }
}