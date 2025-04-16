#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${YELLOW}Preparing Local SEO God plugin update${NC}"
echo "---------------------------------------------------------"

# Rebuild the plugin
echo -e "${GREEN}Building plugin zip file...${NC}"
chmod +x build.sh && ./build.sh

# Check if build was successful
if [ $? -ne 0 ]; then
    echo -e "${RED}Build failed. Please check the error messages above.${NC}"
    exit 1
fi

echo -e "${GREEN}Plugin update file created successfully!${NC}"
echo "---------------------------------------------------------"
echo -e "${YELLOW}Method 1: If you've set up GitHub auto-updates:${NC}"
echo "1. Commit and push your changes to GitHub"
echo "2. Create a new release on GitHub with tag v1.1.0"
echo "3. Upload the local-seo-god.zip file to the release"
echo "4. In WordPress, you'll see an update notification - just click 'Update Now'"
echo ""
echo -e "${YELLOW}Method 2: Manual update (if auto-updates are not set up):${NC}"
echo "1. Go to your WordPress admin dashboard"
echo "2. Navigate to Plugins"
echo "3. Deactivate the Local SEO God plugin"
echo "4. Delete the Local SEO God plugin"
echo "5. Install the new plugin by uploading the zip file (local-seo-god.zip)"
echo "6. Activate the plugin"
echo "---------------------------------------------------------"
echo -e "${GREEN}The zip file is located at:${NC} $(pwd)/local-seo-god.zip" 