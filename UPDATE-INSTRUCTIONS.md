# How to Update Local SEO God Plugin

This document explains how to update your WordPress plugin with the fixed Zeus Mode functionality and configure auto-updates.

## Option 1: Set Up GitHub with Auto-Updates (Recommended)

This method allows you to push updates to GitHub and then have WordPress automatically notify you when updates are available.

1. Create a new repository on GitHub:
   - Go to [GitHub](https://github.com/) and sign in
   - Click the "+" in the top right and select "New repository"
   - Name: `local-seo-god`
   - Set as Private
   - Do not initialize with README, .gitignore, or license
   - Click "Create repository"

2. Run the GitHub setup script:
   ```
   ./setup-github.sh
   ```

3. Enter your GitHub username when prompted.

4. The script will:
   - Update the plugin file with your GitHub username
   - Connect your local repository to GitHub
   - Push all code and tags

5. Create a release on GitHub:
   - Go to your repository on GitHub
   - Click on "Releases" in the right sidebar
   - Click "Draft a new release"
   - Choose tag "v1.1.0"
   - Title: "Local SEO God v1.1.0"
   - Description: "Fixed Zeus Mode preview loading issues"
   - Drag and drop the `local-seo-god.zip` file to the "Attach binaries" section
   - Click "Publish release"

6. Install the plugin on WordPress:
   - Upload the `local-seo-god.zip` file to your WordPress site
   - Activate the plugin

7. Future updates:
   - Whenever you make changes, increment the version number in `local-seo-god.php`
   - Push the changes to GitHub
   - Create a new release with the corresponding tag
   - WordPress will show an "Update Available" notification automatically

## Option 2: Direct WordPress Update

Use this method if you just want to update your plugin quickly without setting up auto-updates:

1. Run the update helper script:
   ```
   ./update-wordpress.sh
   ```

2. The script will rebuild the plugin zip file automatically.

3. Once complete, follow these steps in WordPress:
   - Go to your WordPress admin dashboard
   - Navigate to Plugins
   - Deactivate the Local SEO God plugin
   - Delete the Local SEO God plugin
   - Add New > Upload Plugin > select the `local-seo-god.zip` file
   - Activate the plugin

## Manual Update (If Scripts Don't Work)

If you encounter issues with the scripts:

1. Rebuild the plugin:
   ```
   chmod +x build.sh && ./build.sh
   ```

2. Find the zip file at:
   ```
   [current directory]/local-seo-god.zip
   ```

3. Upload this zip file to your WordPress site as described in Option 2.

## Changes in This Update

The key fixes in this update include:

- Fixed Zeus Mode preview loading issues
- Enhanced error handling and validation
- Improved UI for tag visualization
- Added better user feedback during page generation
- Fixed URL formatting and sanitization
- Added auto-update capability via GitHub

## How Auto-Updates Work

The plugin uses the plugin-update-checker library to connect to your GitHub repository. When a new release is created on GitHub with a higher version number, WordPress will detect this and show an update notification.

To ensure auto-updates work correctly:

1. Always increment the version number in `local-seo-god.php` when making changes
2. Always create proper GitHub releases with the zip file attached
3. Make sure the tag name matches the version number (e.g., `v1.1.0` for version 1.1.0)

## Need Help?

If you encounter any issues with the update process, please contact support for assistance. 