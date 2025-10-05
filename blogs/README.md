# Blog System Directory

This directory contains the automated blog system files.

## Structure

- `index.json` - Contains metadata for all blog posts (automatically updated)
- `*.html` - Individual blog post files (automatically generated)

## How it works

1. **Admin Interface** (`../admin.html`) - Create and publish new blog posts
2. **Publishing Script** (`../publish.js`) - Handles file generation and Hostinger API upload
3. **Blog Listing** (`../blogs.html`) - Public-facing blog index page

## Configuration

Before using the system, update the configuration in `../publish.js`:

```javascript
const HOSTINGER_CONFIG = {
    API_TOKEN: 'YOUR_HOSTINGER_API_TOKEN_HERE',
    DOMAIN: 'yourdomain.com',
    BLOG_PATH: '/blogs/',
    API_BASE_URL: 'https://api.hostinger.com/v1'
};
```

## Getting Your Hostinger API Token

1. Log into your Hostinger Control Panel
2. Go to API section
3. Generate a new API token
4. Copy the token and paste it in `publish.js`

## File Permissions

Make sure the `/blogs/` directory has write permissions for the API to upload files.
