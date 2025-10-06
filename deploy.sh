#!/bin/bash

# KraftHaus Website Deployment Script
# Run this script to deploy your website to Hostinger

echo "🚀 Starting deployment to Hostinger..."

# Configuration
FTP_HOST="your-ftp-host.com"
FTP_USER="your-ftp-username"
FTP_PASS="your-ftp-password"
REMOTE_DIR="/public_html/"

# Files to exclude
EXCLUDE_LIST="--exclude=.git --exclude=.github --exclude=node_modules --exclude=.DS_Store --exclude=Thumbs.db --exclude=README.md --exclude=deploy.sh"

# Deploy using lftp
lftp -c "
set ftp:ssl-allow no
open -u $FTP_USER,$FTP_PASS $FTP_HOST
lcd .
cd $REMOTE_DIR
mirror -R --delete --verbose $EXCLUDE_LIST . .
bye
"

echo "✅ Deployment completed!"

# Optional: Test the deployment
echo "🧪 Testing deployment..."
curl -s -o /dev/null -w "%{http_code}" https://your-domain.com/

echo "🎉 Website deployed successfully!"
