#!/bin/bash

# Local SEO God - Build Script
# This script creates a distribution package of the plugin

# Set the plugin name
PLUGIN_NAME="local-seo-god"

# Create a temporary directory
TEMP_DIR="./build"
mkdir -p "$TEMP_DIR"

# Create the plugin directory
mkdir -p "$TEMP_DIR/$PLUGIN_NAME"

# Copy files to the build directory
echo "Copying plugin files..."
cp -r assets includes views vendor *.php LICENSE.txt README.md "$TEMP_DIR/$PLUGIN_NAME/"

# Create languages directory if it doesn't exist
echo "Setting up languages directory..."
mkdir -p "$TEMP_DIR/$PLUGIN_NAME/languages"
touch "$TEMP_DIR/$PLUGIN_NAME/languages/local-seo-god.pot"

# Remove development files and directories
echo "Removing development files..."
rm -f "$TEMP_DIR/$PLUGIN_NAME/test-wikipedia-shortcode.php"
rm -f "$TEMP_DIR/$PLUGIN_NAME/script.sh"
rm -f "$TEMP_DIR/$PLUGIN_NAME/script.sh.save"
rm -f "$TEMP_DIR/$PLUGIN_NAME/build.sh"
rm -rf "$TEMP_DIR/$PLUGIN_NAME/.git"
rm -f "$TEMP_DIR/$PLUGIN_NAME/.DS_Store"
find "$TEMP_DIR/$PLUGIN_NAME" -name ".git*" -exec rm -rf {} \; 2>/dev/null || true
find "$TEMP_DIR/$PLUGIN_NAME" -name "*.bak" -exec rm -f {} \; 2>/dev/null || true
find "$TEMP_DIR/$PLUGIN_NAME" -name "*.log" -exec rm -f {} \; 2>/dev/null || true
find "$TEMP_DIR/$PLUGIN_NAME" -name ".DS_Store" -exec rm -f {} \; 2>/dev/null || true

# Check if zip command is available
if ! command -v zip &> /dev/null; then
    echo "Error: zip command not found. Please install zip and try again."
    rm -rf "$TEMP_DIR"
    exit 1
fi

# Create a zip file
echo "Creating zip file..."
cd "$TEMP_DIR" || exit
zip -r "../$PLUGIN_NAME.zip" "$PLUGIN_NAME" -x "*.git*" "*.DS_Store" "*.bak" "*.log"
cd .. || exit

# Cleanup
echo "Cleaning up..."
rm -rf "$TEMP_DIR"

echo "Build completed: $PLUGIN_NAME.zip"
echo "You can now upload this file to your WordPress site or distribute it." 