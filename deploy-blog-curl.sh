#!/bin/bash

# Blog System Deployment Script using curl for Hostinger
# Alternative to lftp for systems where lftp is not available

echo "🚀 Deploying Blog System to Hostinger using curl..."

# Configuration
HOSTINGER_HOST="salmon-badger-390791.hostingersite.com"
FTP_USER="u425323865"
FTP_PASS="your_ftp_password_here"  # Replace with your actual FTP password

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}📋 Preparing files for upload...${NC}"

# Create a simple upload function
upload_file() {
    local local_file="$1"
    local remote_path="$2"
    
    echo -e "${YELLOW}📤 Uploading: $remote_path${NC}"
    
    curl -T "$local_file" \
         --user "$FTP_USER:$FTP_PASS" \
         "ftp://$HOSTINGER_HOST/public_html/$remote_path" \
         --ftp-create-dirs
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ Uploaded: $remote_path${NC}"
    else
        echo -e "${RED}❌ Failed to upload: $remote_path${NC}"
    fi
}

# Upload API files
upload_file "api/publish-blog.php" "api/publish-blog.php"
upload_file "api/update-blog-index.php" "api/update-blog-index.php"

# Upload blog files
upload_file "blogs/index.json" "blogs/index.json"

# Upload main files
upload_file "blog-publisher.html" "blog-publisher.html"
upload_file "blog-detailsright-sidebar.html" "blog-detailsright-sidebar.html"

# Upload JavaScript files
upload_file "js/blog-fetcher.js" "js/blog-fetcher.js"

echo -e "${GREEN}🎉 Blog system deployment complete!${NC}"
echo -e "${GREEN}🌐 Your blog publisher is now available at: https://$HOSTINGER_HOST/blog-publisher.html${NC}"
echo -e "${GREEN}📝 Your blog page is available at: https://$HOSTINGER_HOST/blog-detailsright-sidebar.html${NC}"

echo -e "${YELLOW}📋 Next steps:${NC}"
echo -e "${YELLOW}1. Update your Vercel blog publisher to point to: https://$HOSTINGER_HOST${NC}"
echo -e "${YELLOW}2. Test the blog publishing system${NC}"
echo -e "${YELLOW}3. Create your first blog post!${NC}"
