#!/bin/bash

# Blog System Deployment Script for Hostinger
# This script uploads the blog system files to your Hostinger hosting

echo "🚀 Deploying Blog System to Hostinger..."

# Configuration
HOSTINGER_HOST="salmon-badger-390791.hostingersite.com"
FTP_USER="u425323865"
FTP_PASS="your_ftp_password_here"  # Replace with your actual FTP password

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if lftp is installed
if ! command -v lftp &> /dev/null; then
    echo -e "${RED}❌ lftp is not installed. Installing via Homebrew...${NC}"
    if command -v brew &> /dev/null; then
        brew install lftp
    else
        echo -e "${RED}❌ Homebrew not found. Please install lftp manually or use the curl alternative.${NC}"
        exit 1
    fi
fi

# Create temporary directory
TEMP_DIR=$(mktemp -d)
echo -e "${YELLOW}📁 Created temporary directory: $TEMP_DIR${NC}"

# Copy necessary files
echo -e "${YELLOW}📋 Copying blog system files...${NC}"

# Copy API files
mkdir -p "$TEMP_DIR/api"
cp api/publish-blog.php "$TEMP_DIR/api/"
cp api/update-blog-index.php "$TEMP_DIR/api/"

# Copy blog files
mkdir -p "$TEMP_DIR/blogs"
cp blogs/index.json "$TEMP_DIR/blogs/"

# Copy updated blog publisher
cp blog-publisher.html "$TEMP_DIR/"

# Copy blog details page
cp blog-detailsright-sidebar.html "$TEMP_DIR/"

# Copy blog fetcher script
mkdir -p "$TEMP_DIR/js"
cp js/blog-fetcher.js "$TEMP_DIR/js/"

echo -e "${GREEN}✅ Files copied successfully${NC}"

# Upload to Hostinger
echo -e "${YELLOW}🔄 Starting file upload...${NC}"

lftp -c "
set ftp:ssl-allow no;
set ftp:passive-mode on;
open ftp://$FTP_USER:$FTP_PASS@$HOSTINGER_HOST;
cd public_html;
mirror -R $TEMP_DIR .;
quit
"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Blog system deployed successfully!${NC}"
    echo -e "${GREEN}🌐 Your blog publisher is now available at: https://$HOSTINGER_HOST/blog-publisher.html${NC}"
    echo -e "${GREEN}📝 Your blog page is available at: https://$HOSTINGER_HOST/blog-detailsright-sidebar.html${NC}"
else
    echo -e "${RED}❌ Deployment failed. Please check your FTP credentials and try again.${NC}"
fi

# Cleanup
rm -rf "$TEMP_DIR"
echo -e "${YELLOW}🧹 Cleaned up temporary files${NC}"

echo -e "${GREEN}🎉 Deployment complete!${NC}"
