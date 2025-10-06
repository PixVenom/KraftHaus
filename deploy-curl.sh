#!/bin/bash

# KraftHaus Website Deployment Script (using curl)
# Run this script to deploy your website to Hostinger

echo "🚀 Starting deployment to Hostinger..."

# Configuration
FTP_HOST="82.25.107.242"
FTP_USER="Arryan"
FTP_PASS="Krafthaus@123"
REMOTE_DIR="/public_html/website2/Krafthaus/"

# Check if required tools are available
if ! command -v curl &> /dev/null; then
    echo "❌ curl is required but not installed. Please install curl first."
    exit 1
fi

# Create a temporary directory for files to upload
TEMP_DIR=$(mktemp -d)
echo "📁 Created temporary directory: $TEMP_DIR"

# Copy files to temp directory (excluding unwanted files)
echo "📋 Copying files..."
rsync -av --exclude='.git' --exclude='.github' --exclude='node_modules' --exclude='.DS_Store' --exclude='Thumbs.db' --exclude='README.md' --exclude='deploy.sh' --exclude='deploy-curl.sh' --exclude='.gitignore' . "$TEMP_DIR/"

# Function to upload files via FTP using curl
upload_file() {
    local local_file="$1"
    local remote_file="$2"
    
    # Convert local path to remote path
    remote_path="${local_file#$TEMP_DIR}"
    remote_path="${remote_path#/}"
    remote_path="$REMOTE_DIR$remote_path"
    
    echo "📤 Uploading: $remote_path"
    
    # Upload file using curl
    curl -T "$local_file" \
         --user "$FTP_USER:$FTP_PASS" \
         "ftp://$FTP_HOST$remote_path" \
         --silent --show-error
    
    if [ $? -eq 0 ]; then
        echo "✅ Uploaded: $remote_path"
    else
        echo "❌ Failed to upload: $remote_path"
    fi
}

# Upload all files
echo "🔄 Starting file upload..."
find "$TEMP_DIR" -type f | while read -r file; do
    upload_file "$file"
done

# Clean up
rm -rf "$TEMP_DIR"
echo "🧹 Cleaned up temporary directory"

echo "✅ Deployment completed!"

# Optional: Test the deployment
echo "🧪 Testing deployment..."
if curl -s -o /dev/null -w "%{http_code}" https://your-domain.com/ | grep -q "200"; then
    echo "🎉 Website deployed successfully and is accessible!"
else
    echo "⚠️  Deployment completed but website may not be accessible yet (this is normal for new deployments)"
fi
