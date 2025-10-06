/**
 * Blog Fetcher for KraftHaus Website
 * Fetches blog posts from Hostinger hosting and displays them dynamically
 */

class BlogFetcher {
    constructor() {
        this.blogIndexUrl = 'blogs/index.json';
        this.blogPosts = [];
        this.init();
    }

    async init() {
        try {
            await this.fetchBlogPosts();
            this.displayBlogPosts();
        } catch (error) {
            console.error('Error initializing blog fetcher:', error);
            this.showError('Unable to load blog posts. Please try again later.');
        }
    }

    async fetchBlogPosts() {
        try {
            const response = await fetch(this.blogIndexUrl);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            this.blogPosts = await response.json();
            console.log('Blog posts loaded from Hostinger:', this.blogPosts);
        } catch (error) {
            console.error('Error fetching blog posts from Hostinger:', error);
            // Fallback to static content if Hostinger is unavailable
            this.blogPosts = this.getFallbackPosts();
        }
    }

    getFallbackPosts() {
        return [
            {
                id: "1",
                title: "Awesome Photography Module Design",
                slug: "awesome-photography-module-design",
                date: "2024-01-15T10:00:00.000Z",
                imageUrl: "images/blog/11.jpg",
                excerpt: "If you want to have an effective content with the latest trends. When you think of creating content most people's minds go straight to blogging.",
                content: "<p>If you want to have an effective content with the latest trends. When you think of creating content most people's minds go straight to blogging.</p>"
            },
            {
                id: "2",
                title: "Interactive Journey Design",
                slug: "interactive-journey-design", 
                date: "2024-01-20T14:30:00.000Z",
                imageUrl: "images/blog/12.jpg",
                excerpt: "Creating interactive experiences that engage users and drive conversions through thoughtful design and user experience.",
                content: "<p>Creating interactive experiences that engage users and drive conversions through thoughtful design and user experience.</p>"
            },
            {
                id: "3",
                title: "Digital Marketing Strategies",
                slug: "digital-marketing-strategies",
                date: "2024-01-25T09:15:00.000Z",
                imageUrl: "images/blog/13.jpg",
                excerpt: "Explore the latest digital marketing strategies that are driving results for businesses in 2024.",
                content: "<p>Explore the latest digital marketing strategies that are driving results for businesses in 2024.</p>"
            },
            {
                id: "4",
                title: "Creative Design Solutions",
                slug: "creative-design-solutions",
                date: "2024-01-30T16:45:00.000Z",
                imageUrl: "images/blog/14.jpg",
                excerpt: "Discover how creative design solutions can transform your brand and engage your audience effectively.",
                content: "<p>Discover how creative design solutions can transform your brand and engage your audience effectively.</p>"
            }
        ];
    }

    displayBlogPosts() {
        const blogContainer = document.querySelector('.blog-list-area .container-150 .row .col-lg-12');
        
        if (!blogContainer) {
            console.error('Blog container not found');
            return;
        }

        // Clear existing content
        blogContainer.innerHTML = '';

        // Display each blog post
        this.blogPosts.forEach((post, index) => {
            const blogPostElement = this.createBlogPostElement(post, index);
            blogContainer.appendChild(blogPostElement);
        });
    }

    createBlogPostElement(post, index) {
        const postDate = new Date(post.date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        const blogPostDiv = document.createElement('div');
        blogPostDiv.className = 'single-blog-list-one rts-skew-up-gsap';
        blogPostDiv.style = '';

        blogPostDiv.innerHTML = `
            <a href="blogs/${post.slug}.html" class="thumbnail">
                <img src="${post.imageUrl}" alt="blog_images">
            </a>
            <div class="blog-content-area">
                <span>Photography / Module Design</span>
                <a href="blogs/${post.slug}.html">
                    <h3 class="title quote animated fadeIn" style="">
                        <div class="split-parent">
                            <div class="split-line" style="display: block; text-align: start; position: relative; translate: none; rotate: none; scale: none; transform: translate(0px, 0px); color: white !important;">
                                ${post.title}
                            </div>
                        </div>
                        <div class="split-parent">
                            <div class="split-line" style="display: block; text-align: start; position: relative; translate: none; rotate: none; scale: none; transform: translate(0px, 0px); color: white !important;">
                                Interactive Journey
                            </div>
                        </div>
                    </h3>
                </a>
                <p class="disc">
                    ${post.excerpt}
                </p>
                <a href="blogs/${post.slug}.html" class="read-more-bb">Read More</a>
            </div>
        `;

        return blogPostDiv;
    }

    showError(message) {
        const blogContainer = document.querySelector('.blog-list-area .container-150 .row .col-lg-12');
        if (blogContainer) {
            blogContainer.innerHTML = `
                <div class="error-message" style="text-align: center; padding: 40px; color: #666;">
                    <h3>Unable to Load Blog Posts</h3>
                    <p>${message}</p>
                    <button onclick="location.reload()" class="rts-btn btn-primary">Try Again</button>
                </div>
            `;
        }
    }

    // Method to refresh blog posts (can be called manually)
    async refresh() {
        await this.fetchBlogPosts();
        this.displayBlogPosts();
    }
}

// Initialize blog fetcher when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.blogFetcher = new BlogFetcher();
});

// Auto-refresh every 10 minutes to check for new posts from Hostinger
setInterval(() => {
    if (window.blogFetcher) {
        window.blogFetcher.refresh();
    }
}, 600000); // 10 minutes
