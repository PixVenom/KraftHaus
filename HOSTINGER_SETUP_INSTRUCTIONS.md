# KraftHaus Contact Form - Hostinger Setup Instructions

This guide will help you set up the contact form database integration on your Hostinger hosting account.

## Prerequisites

- Hostinger hosting account with PHP support
- Access to Hostinger control panel (hPanel)
- Database access (MySQL)

## Step 1: Database Setup

### 1.1 Create Database
1. Log into your Hostinger hPanel
2. Go to **Databases** → **MySQL Databases**
3. Create a new database:
   - Database name: `krafthaus_contact` (or your preferred name)
   - Note down the database name

### 1.2 Create Database User
1. In the same MySQL Databases section
2. Create a new user:
   - Username: `krafthaus_user` (or your preferred name)
   - Password: Generate a strong password
   - Note down both username and password

### 1.3 Assign User to Database
1. In the "Add User to Database" section
2. Select the user and database you just created
3. Grant **ALL PRIVILEGES** to the user
4. Click **Make Changes**

### 1.4 Import Database Structure
1. Go to **Databases** → **phpMyAdmin**
2. Select your database from the left sidebar
3. Click on the **SQL** tab
4. Copy and paste the contents of `database-setup.sql` file
5. Click **Go** to execute the SQL

## Step 2: File Upload

### 2.1 Upload Files
Upload the following files to your website's root directory (usually `public_html`):

```
public_html/
├── contact.html (updated)
├── contact-form-handler.php
├── config/
│   └── database.php
└── database-setup.sql (optional - for reference)
```

### 2.2 Set File Permissions
Set the following permissions:
- `config/` directory: 755
- `config/database.php`: 644
- `contact-form-handler.php`: 644

## Step 3: Configuration

### 3.1 Update Database Configuration
Edit `config/database.php` and update the following values:

```php
$host = 'localhost'; // Usually 'localhost' for Hostinger
$dbname = 'your_actual_database_name'; // Replace with your database name
$username = 'your_actual_username'; // Replace with your database username
$password = 'your_actual_password'; // Replace with your database password
```

### 3.2 Update Email Configuration
In the same file, update email settings:

```php
define('FROM_EMAIL', 'noreply@yourdomain.com'); // Your domain email
define('ADMIN_EMAIL', 'hello@krafthaus.in'); // Where to receive notifications
```

## Step 4: Email Configuration (Optional)

### 4.1 SMTP Settings
If you want to use SMTP for sending emails:

1. In Hostinger hPanel, go to **Email Accounts**
2. Create an email account (e.g., `noreply@yourdomain.com`)
3. Note down the email credentials
4. Update SMTP settings in `config/database.php`:

```php
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'noreply@yourdomain.com');
define('SMTP_PASSWORD', 'your_email_password');
```

## Step 5: Testing

### 5.1 Test the Form
1. Visit your contact page: `https://yourdomain.com/contact.html`
2. Fill out the form with test data
3. Submit the form
4. Check for success/error messages

### 5.2 Verify Database Storage
1. Go to phpMyAdmin
2. Select your database
3. Click on `contact_submissions` table
4. Verify that your test submission was stored

### 5.3 Check Email Notifications
1. Check the admin email inbox for notification emails
2. Verify that the email contains all form data

## Step 6: Security Considerations

### 6.1 File Permissions
Ensure proper file permissions:
```bash
chmod 644 config/database.php
chmod 644 contact-form-handler.php
chmod 755 config/
```

### 6.2 Database Security
- Use strong passwords for database users
- Regularly backup your database
- Monitor database access logs

### 6.3 Form Security
The form includes:
- Input validation and sanitization
- SQL injection prevention (PDO prepared statements)
- XSS protection
- Rate limiting (configurable)

## Step 7: Monitoring and Maintenance

### 7.1 View Submissions
You can view form submissions by:
1. Accessing phpMyAdmin
2. Running queries on the `contact_submissions` table
3. Using the provided sample queries in `database-setup.sql`

### 7.2 Common Queries
```sql
-- View all submissions
SELECT * FROM contact_submissions ORDER BY created_at DESC;

-- View new submissions
SELECT * FROM contact_submissions WHERE status = 'new';

-- Count submissions by service
SELECT service, COUNT(*) as count FROM contact_submissions GROUP BY service;
```

### 7.3 Backup
Regularly backup your database:
1. In phpMyAdmin, select your database
2. Go to **Export** tab
3. Choose **Quick** export method
4. Click **Go** to download backup

## Troubleshooting

### Common Issues

#### 1. Database Connection Error
- Verify database credentials in `config/database.php`
- Check if database user has proper permissions
- Ensure database exists

#### 2. Form Not Submitting
- Check browser console for JavaScript errors
- Verify `contact-form-handler.php` is accessible
- Check file permissions

#### 3. Email Not Sending
- Verify email configuration
- Check if SMTP settings are correct
- Test with a simple PHP mail() function

#### 4. 500 Internal Server Error
- Check PHP error logs in Hostinger hPanel
- Verify file permissions
- Check for syntax errors in PHP files

### Debug Mode
To enable debug mode, edit `contact-form-handler.php`:
```php
// Remove or comment out these lines in production
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Support

If you encounter issues:
1. Check Hostinger's documentation
2. Review PHP error logs
3. Test with simple form data
4. Verify all file paths and permissions

## Files Created

- `contact-form-handler.php` - Main form processing script
- `config/database.php` - Database configuration
- `database-setup.sql` - Database structure and sample data
- `contact.html` - Updated contact form (modified)
- `HOSTINGER_SETUP_INSTRUCTIONS.md` - This setup guide

## Next Steps

After successful setup:
1. Test the form thoroughly
2. Set up regular database backups
3. Monitor form submissions
4. Consider adding a admin panel for managing submissions
5. Implement additional security measures as needed
