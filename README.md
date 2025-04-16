# Local SEO God

A powerful WordPress plugin for local SEO optimization and AI-powered content generation.

## Features

### AI Content Generation
- Generate high-quality, SEO-optimized content for service pages, location pages, and more
- Use Standard Operating Procedures (SOPs) to ensure content meets specific quality standards
- Customize templates with business-specific information

### Zeus Mode Bulk Generation
Quickly generate multiple location or service pages with:
1. Template selection
2. Formula-based generation (Service + Location)
3. Automatic tag replacement
4. Publication status configuration

### Smart Linking System
- **Internal Linking**: Automatically link service mentions to appropriate service pages
- **Wikipedia Links**: Convert location mentions to Wikipedia links using the `[placeslinks]` shortcode
- **Google Business Profile**: Link business names to Google Business Profile

### Tag Replacement System
Replace standardized tags with your custom business information:
- {{BUSINESS_NAME}}
- {{BUSINESS_CITY}}
- {{PHONE_NUMBER}}
- And many more

## Installation

1. Upload the plugin files to the `/wp-content/plugins/local-seo-god` directory
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Configure your settings including OpenAI API key and business information

## Usage

### Wikipedia Links Shortcode

Use the `[placeslinks]` shortcode to automatically convert location mentions to Wikipedia links:

```
[placeslinks]
Located in [[Melbourne]], our service area extends to suburbs like [[South Yarra]] and [[Brighton]].
We also serve customers in [[Mornington, Victoria]] and [[Geelong]].
[/placeslinks]
```

This will convert the text inside the double square brackets to Wikipedia links.

### Zeus Mode Bulk Generation

1. Navigate to "Local SEO God" → "The Lord Generator" in your WordPress dashboard
2. Select the "Zeus Mode" tab
3. Choose a template and generation formula
4. Configure publication settings
5. Click "Generate Pages"

### Business Information

Set up your business information in the settings panel to enable automatic tag replacement in all generated content.

## Documentation

For detailed documentation on how to use each feature, please refer to:

- [AI Content Generation SOP](views/ai-content-sop.md)
- [Bulk Generation Guide](views/bulk-generation-sop.md)
- [Wikipedia Links SOP](views/placeslinks-sop.md)

## Requirements

- WordPress 5.5 or higher
- PHP 7.2 or higher
- OpenAI API key

## License

This plugin is licensed under the GPL v2 or later.

## Support

For support or feature requests, please contact the developer.
