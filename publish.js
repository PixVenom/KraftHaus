/**
 * Blog Publishing System for Hostinger Static Website
 * 
 * This script handles:
 * 1. Form submission and validation
 * 2. HTML file generation for blog posts
 * 3. Hostinger API integration for file upload
 * 4. Success/error handling with user feedback
 */

// ============================================================================
// CONFIGURATION - UPDATE THESE VALUES FOR YOUR HOSTINGER SETUP
// ============================================================================

// Your Hostinger API credentials and endpoints
const HOSTINGER_CONFIG = {
    // Get your API token from Hostinger Control Panel > API
    API_TOKEN: 'YOUR_HOSTINGER_API_TOKEN_HERE',
    
    // Your website's domain (without https://)
    DOMAIN: 'yourdomain.com',
    
    // The folder path where blog posts will be stored
    BLOG_PATH: '/blogs/',
    
    // Hostinger API base URL
    API_BASE_URL: 'https://api.hostinger.com/v1'
};

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

/**
 * Generate a URL-friendly slug from a title
 * @param {string} title - The blog post title
 * @returns {string} - URL-friendly slug
 */
function generateSlug(title) {
    return title
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '') // Remove special characters
        .replace(/\s+/g, '-')         // Replace spaces with hyphens
        .replace(/-+/g, '-')         // Replace multiple hyphens with single
        .trim();
}

/**
 * Generate HTML content for a blog post
 * @param {Object} postData - Blog post data
 * @returns {string} - Complete HTML content
 */
function generateBlogHTML(postData) {
    const { title, content, imageUrl, slug } = postData;
    const currentDate = new Date().toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    return `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${title} - KraftHaus Blog</title>
    <meta name="description" content="${content.substring(0, 160)}...">
    
    <!-- Include your main CSS -->
    <link rel="stylesheet" href="../css/style.css">
    
    <style>
        .blog-post-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
            line-height: 1.6;
        }
        .blog-post-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .blog-post-title {
            font-size: 2.5rem;
            color: #1F1F25;
            margin-bottom: 20px;
            line-height: 1.2;
        }
        .blog-post-meta {
            color: #666;
            font-size: 1.1rem;
        }
        .blog-post-image {
            width: 100%;
            max-width: 600px;
            height: auto;
            border-radius: 8px;
            margin: 30px auto;
            display: block;
        }
        .blog-post-content {
            font-size: 1.1rem;
            color: #333;
        }
        .back-to-blogs {
            display: inline-block;
            margin-bottom: 30px;
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        .back-to-blogs:hover {
            color: #764ba2;
        }
    </style>
</head>
<body>
    <div class="blog-post-container">
        <a href="../blog-detailsright-sidebar.html" class="back-to-blogs">← Back to All Blogs</a>
        
        <header class="blog-post-header">
            <h1 class="blog-post-title">${title}</h1>
            <div class="blog-post-meta">Published on ${currentDate}</div>
        </header>
        
        ${imageUrl ? `<img src="${imageUrl}" alt="${title}" class="blog-post-image">` : ''}
        
        <div class="blog-post-content">
            ${content}
        </div>
    </div>
</body>
</html>`;
}

/**
 * Show status message to user
 * @param {string} message - Message to display
 * @param {string} type - 'success' or 'error'
 */
function showStatus(message, type) {
    const statusEl = document.getElementById('statusMessage');
    const loadingEl = document.getElementById('loading');
    
    // Hide loading spinner
    loadingEl.style.display = 'none';
    
    // Show status message
    statusEl.textContent = message;
    statusEl.className = `status-message status-${type}`;
    statusEl.style.display = 'block';
    
    // Auto-hide success messages after 5 seconds
    if (type === 'success') {
        setTimeout(() => {
            statusEl.style.display = 'none';
        }, 5000);
    }
}

/**
 * Show loading state
 */
function showLoading() {
    const loadingEl = document.getElementById('loading');
    const statusEl = document.getElementById('statusMessage');
    
    statusEl.style.display = 'none';
    loadingEl.style.display = 'block';
}

// ============================================================================
// HOSTINGER API INTEGRATION
// ============================================================================

/**
 * Upload file to Hostinger using their API
 * @param {string} filename - Name of the file to create
 * @param {string} content - File content (HTML)
 * @returns {Promise<Object>} - API response
 */
async function uploadToHostinger(filename, content) {
    const uploadUrl = `${HOSTINGER_CONFIG.API_BASE_URL}/domains/${HOSTINGER_CONFIG.DOMAIN}/files`;
    
    const requestBody = {
        path: `${HOSTINGER_CONFIG.BLOG_PATH}${filename}`,
        content: content,
        encoding: 'utf8'
    };

    try {
        const response = await fetch(uploadUrl, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${HOSTINGER_CONFIG.API_TOKEN}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(requestBody)
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(`Hostinger API Error: ${errorData.message || response.statusText}`);
        }

        return await response.json();
    } catch (error) {
        console.error('Upload error:', error);
        throw new Error(`Failed to upload to Hostinger: ${error.message}`);
    }
}

/**
 * Update the blog index file with new post information
 * @param {Object} postData - Blog post data
 */
async function updateBlogIndex(postData) {
    try {
        // First, try to get existing blog index
        const indexUrl = `${HOSTINGER_CONFIG.API_BASE_URL}/domains/${HOSTINGER_CONFIG.DOMAIN}/files`;
        const indexPath = `${HOSTINGER_CONFIG.BLOG_PATH}index.json`;
        
        let existingPosts = [];
        
        try {
            // Try to fetch existing index
            const getResponse = await fetch(`${indexUrl}?path=${indexPath}`, {
                headers: {
                    'Authorization': `Bearer ${HOSTINGER_CONFIG.API_TOKEN}`
                }
            });
            
            if (getResponse.ok) {
                const indexData = await getResponse.json();
                existingPosts = JSON.parse(indexData.content || '[]');
            }
        } catch (error) {
            console.log('No existing index found, creating new one');
        }
        
        // Add new post to the beginning of the array
        const newPost = {
            id: Date.now().toString(),
            title: postData.title,
            slug: postData.slug,
            date: new Date().toISOString(),
            imageUrl: postData.imageUrl || null,
            excerpt: postData.content.substring(0, 150) + '...'
        };
        
        existingPosts.unshift(newPost);
        
        // Upload updated index
        const indexContent = JSON.stringify(existingPosts, null, 2);
        await uploadToHostinger('index.json', indexContent);
        
    } catch (error) {
        console.warn('Could not update blog index:', error);
        // Don't throw error here as the main post was uploaded successfully
    }
}

// ============================================================================
// FORM HANDLING
// ============================================================================

/**
 * Handle form submission
 * @param {Event} event - Form submit event
 */
async function handleFormSubmit(event) {
    event.preventDefault();
    
    // Get form data
    const formData = new FormData(event.target);
    const title = formData.get('title').trim();
    const content = formData.get('content').trim();
    const imageUrl = formData.get('imageUrl').trim();
    
    // Validate required fields
    if (!title || !content) {
        showStatus('Please fill in all required fields.', 'error');
        return;
    }
    
    // Check if API token is configured
    if (HOSTINGER_CONFIG.API_TOKEN === 'YOUR_HOSTINGER_API_TOKEN_HERE') {
        showStatus('Please configure your Hostinger API token in publish.js', 'error');
        return;
    }
    
    // Generate slug and prepare post data
    const slug = generateSlug(title);
    const filename = `${slug}.html`;
    const postData = { title, content, imageUrl, slug };
    
    try {
        showLoading();
        
        // Generate HTML content
        const htmlContent = generateBlogHTML(postData);
        
        // Upload the blog post file
        await uploadToHostinger(filename, htmlContent);
        
        // Update blog index
        await updateBlogIndex(postData);
        
        // Success!
        showStatus(`✅ Blog post "${title}" published successfully!`, 'success');
        
        // Reset form
        event.target.reset();
        
    } catch (error) {
        console.error('Publishing error:', error);
        showStatus(`❌ Error publishing blog post: ${error.message}`, 'error');
    }
}

// ============================================================================
// INITIALIZATION
// ============================================================================

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('blogForm');
    const publishBtn = document.getElementById('publishBtn');
    
    // Add form submit handler
    form.addEventListener('submit', handleFormSubmit);
    
    // Add loading state to button
    form.addEventListener('submit', function() {
        publishBtn.disabled = true;
        publishBtn.textContent = 'Publishing...';
    });
    
    // Reset button state on error
    const statusEl = document.getElementById('statusMessage');
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                if (statusEl.classList.contains('status-error')) {
                    publishBtn.disabled = false;
                    publishBtn.textContent = '🚀 Publish Blog Post';
                }
            }
        });
    });
    observer.observe(statusEl, { attributes: true });
    
    console.log('Blog publishing system initialized');
    console.log('Remember to configure your Hostinger API credentials in publish.js');
});
