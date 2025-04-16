#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${YELLOW}Setting up GitHub repository for Local SEO God plugin with auto-update capability${NC}"
echo "---------------------------------------------------------"

# Get GitHub username
read -p "Enter your GitHub username: " USERNAME

if [ -z "$USERNAME" ]; then
    echo -e "${RED}Error: GitHub username is required${NC}"
    exit 1
fi

# Update the plugin file with the correct GitHub username
echo -e "${GREEN}Updating plugin file with your GitHub username...${NC}"
sed -i '' "s/GITHUB_USERNAME/$USERNAME/g" local-seo-god.php

# Rebuild the plugin with updated GitHub username
echo -e "${GREEN}Rebuilding plugin with your GitHub username...${NC}"
chmod +x build.sh && ./build.sh

# Commit the changes
echo -e "${GREEN}Committing changes...${NC}"
git add local-seo-god.php local-seo-god.zip
git commit -m "Update GitHub username and prepare for auto-updates"

# Create GitHub repository and push code
echo -e "${GREEN}Setting up remote repository...${NC}"
git remote add origin "https://github.com/$USERNAME/local-seo-god.git"

echo -e "${GREEN}Setting main branch...${NC}"
git branch -M main

echo -e "${GREEN}Pushing code to GitHub...${NC}"
git push -u origin main

# Push tags to include release
echo -e "${GREEN}Pushing tags for releases...${NC}"
git push --tags

echo -e "${GREEN}Setup complete!${NC}"
echo "Repository URL: https://github.com/$USERNAME/local-seo-god"
echo "---------------------------------------------------------"
echo -e "${YELLOW}IMPORTANT: Next steps for auto-updates${NC}"
echo "1. Go to your GitHub repository: https://github.com/$USERNAME/local-seo-god"
echo "2. Click on 'Releases' on the right sidebar"
echo "3. Click on 'Draft a new release'"
echo "4. Choose the tag v1.1.0"
echo "5. Title: 'Local SEO God v1.1.0'"
echo "6. Description: 'Fixed Zeus Mode preview loading issues'"
echo "7. Attach the local-seo-god.zip file by dragging it into the binaries section"
echo "8. Click 'Publish release'"
echo ""
echo "Once you've done this, your WordPress plugin will check for updates from GitHub."
echo "You can now install the plugin on your WordPress site, and it will update automatically!"
echo "---------------------------------------------------------"
echo -e "${YELLOW}To update your WordPress plugin:${NC}"
echo "1. Go to your WordPress admin dashboard"
echo "2. Navigate to Plugins"
echo "3. Deactivate the Local SEO God plugin"
echo "4. Delete the Local SEO God plugin"
echo "5. Install the new plugin by uploading the zip file (local-seo-god.zip)"
echo "6. Activate the plugin"
echo "---------------------------------------------------------" 