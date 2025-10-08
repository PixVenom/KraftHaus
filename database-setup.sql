-- Database setup script for KraftHaus Contact Form
-- Optimized for the exact form structure in contact.html
-- Run this script in your Hostinger database to create the necessary tables

-- Create database (uncomment if you need to create a new database)
-- CREATE DATABASE IF NOT EXISTS krafthaus_contact;
-- USE krafthaus_contact;

-- Create contact_submissions table - matches exact form fields
CREATE TABLE IF NOT EXISTS contact_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    
    -- Form fields (exact match to contact form)
    name VARCHAR(255) NOT NULL COMMENT 'User full name from form',
    email VARCHAR(255) NOT NULL COMMENT 'User email address',
    phone VARCHAR(50) NOT NULL COMMENT 'User phone number',
    service ENUM(
        'Branding',
        'Marketing Strategy', 
        'Social Media Marketing',
        'Web Development',
        'UI/UX Design',
        'Content Production',
        'Influencer Marketing',
        'Performance Marketing'
    ) NOT NULL COMMENT 'Selected service from dropdown',
    message TEXT NOT NULL COMMENT 'User message/project description',
    
    -- System fields
    ip_address VARCHAR(45) COMMENT 'User IP address for security',
    user_agent TEXT COMMENT 'Browser information',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Submission timestamp',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Last update timestamp',
    
    -- Management fields
    status ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new' COMMENT 'Submission status',
    admin_notes TEXT NULL COMMENT 'Internal notes for admin',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium' COMMENT 'Priority level',
    
    -- Indexes for performance
    INDEX idx_email (email),
    INDEX idx_created_at (created_at),
    INDEX idx_status (status),
    INDEX idx_service (service),
    INDEX idx_priority (priority),
    INDEX idx_name (name),
    INDEX idx_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='KraftHaus contact form submissions';

-- Create admin_users table for managing submissions
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'viewer') DEFAULT 'viewer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Admin users for contact form management';

-- Note: Admin users can be added manually after table creation
-- Default credentials should be: admin / admin123 (change after first login!)

-- Create email_logs table to track email notifications
CREATE TABLE IF NOT EXISTS email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT,
    email_type ENUM('notification', 'auto_reply', 'follow_up') NOT NULL,
    recipient_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('sent', 'failed', 'pending') DEFAULT 'pending',
    error_message TEXT NULL,
    retry_count INT DEFAULT 0,
    FOREIGN KEY (submission_id) REFERENCES contact_submissions(id) ON DELETE CASCADE,
    INDEX idx_submission_id (submission_id),
    INDEX idx_sent_at (sent_at),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Email notification tracking';

-- Create service_analytics table for tracking service popularity
CREATE TABLE IF NOT EXISTS service_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(100) NOT NULL,
    submission_count INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_service (service_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Service popularity tracking';

-- Note: Service analytics will be populated automatically as submissions are received

-- Create views for easy querying and reporting

-- Recent submissions view with formatted data
CREATE OR REPLACE VIEW recent_submissions AS
SELECT 
    id,
    name,
    email,
    phone,
    service,
    LEFT(message, 100) as message_preview,
    created_at,
    status,
    priority,
    admin_notes,
    ip_address,
    DATEDIFF(NOW(), created_at) as days_old
FROM contact_submissions 
ORDER BY created_at DESC;

-- Service statistics view
CREATE OR REPLACE VIEW service_statistics AS
SELECT 
    service,
    COUNT(*) as total_submissions,
    SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_count,
    SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_count,
    SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END) as replied_count,
    SUM(CASE WHEN status = 'archived' THEN 1 ELSE 0 END) as archived_count,
    ROUND(AVG(DATEDIFF(NOW(), created_at)), 1) as avg_days_to_process
FROM contact_submissions 
GROUP BY service 
ORDER BY total_submissions DESC;

-- Monthly submission trends view
CREATE OR REPLACE VIEW monthly_trends AS
SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    COUNT(*) as submissions,
    COUNT(DISTINCT service) as services_requested,
    COUNT(DISTINCT email) as unique_contacts
FROM contact_submissions 
GROUP BY DATE_FORMAT(created_at, '%Y-%m')
ORDER BY month DESC;

-- High priority submissions view
CREATE OR REPLACE VIEW high_priority_submissions AS
SELECT 
    id,
    name,
    email,
    phone,
    service,
    message,
    created_at,
    status,
    priority,
    admin_notes
FROM contact_submissions 
WHERE priority IN ('high', 'urgent') 
    AND status != 'archived'
ORDER BY 
    CASE priority 
        WHEN 'urgent' THEN 1 
        WHEN 'high' THEN 2 
        ELSE 3 
    END,
    created_at ASC;

-- ========================================
-- SAMPLE QUERIES FOR TESTING AND MANAGEMENT
-- ========================================

-- View all submissions with full details
-- SELECT * FROM contact_submissions ORDER BY created_at DESC;

-- View new submissions that need attention
-- SELECT * FROM contact_submissions WHERE status = 'new' ORDER BY priority ASC, created_at ASC;

-- Count submissions by service (using the view)
-- SELECT * FROM service_statistics;

-- View submissions from last 7 days
-- SELECT * FROM contact_submissions WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY created_at DESC;

-- View high priority submissions
-- SELECT * FROM high_priority_submissions;

-- Update submission status and add notes
-- UPDATE contact_submissions SET status = 'read', admin_notes = 'Customer called back - interested in web development' WHERE id = 1;

-- Update priority level
-- UPDATE contact_submissions SET priority = 'high' WHERE service = 'Web Development' AND status = 'new';

-- Search submissions by name or email
-- SELECT * FROM contact_submissions WHERE name LIKE '%John%' OR email LIKE '%@gmail.com%';

-- Get submission statistics for dashboard
-- SELECT 
--     COUNT(*) as total_submissions,
--     SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_count,
--     SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_count,
--     SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END) as replied_count,
--     SUM(CASE WHEN priority = 'urgent' THEN 1 ELSE 0 END) as urgent_count
-- FROM contact_submissions;

-- Update service analytics (run this after each new submission)
-- UPDATE service_analytics sa 
-- JOIN (
--     SELECT service, COUNT(*) as count 
--     FROM contact_submissions 
--     GROUP BY service
-- ) cs ON sa.service_name = cs.service 
-- SET sa.submission_count = cs.count;

-- Archive old submissions (older than 1 year)
-- UPDATE contact_submissions SET status = 'archived' WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR) AND status != 'archived';

-- Export submissions to CSV format
-- SELECT 
--     id,
--     name,
--     email,
--     phone,
--     service,
--     message,
--     status,
--     priority,
--     created_at
-- FROM contact_submissions 
-- WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
-- ORDER BY created_at DESC;
