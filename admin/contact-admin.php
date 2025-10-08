<?php
// Contact Form Admin Panel
// Simple admin interface to view and manage contact form submissions

session_start();

// Simple authentication (you should implement proper authentication)
$admin_username = 'admin';
$admin_password = 'admin123'; // Change this password!

if (isset($_POST['login'])) {
    if ($_POST['username'] === $admin_username && $_POST['password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $login_error = 'Invalid credentials';
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: contact-admin.php');
    exit;
}

// Check if logged in
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    // Show login form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - KraftHaus Contact</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
            .login-container { max-width: 400px; margin: 100px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .form-group { margin-bottom: 20px; }
            label { display: block; margin-bottom: 5px; font-weight: bold; }
            input[type="text"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
            button { background: #007cba; color: white; padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
            button:hover { background: #005a87; }
            .error { color: red; margin-bottom: 15px; }
            h2 { text-align: center; margin-bottom: 30px; color: #333; }
        </style>
    </head>
    <body>
        <div class="login-container">
            <h2>Admin Login</h2>
            <?php if (isset($login_error)): ?>
                <div class="error"><?php echo htmlspecialchars($login_error); ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" name="login">Login</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Include database configuration
require_once '../config/database.php';

// Handle status updates
if (isset($_POST['update_status'])) {
    $id = (int)$_POST['submission_id'];
    $status = $_POST['status'];
    $notes = $_POST['notes'];
    
    try {
        $pdo = new PDO($dsn, $username, $password, $options);
        $stmt = $pdo->prepare("UPDATE contact_submissions SET status = ?, notes = ? WHERE id = ?");
        $stmt->execute([$status, $notes, $id]);
        $update_success = 'Status updated successfully';
    } catch (PDOException $e) {
        $update_error = 'Error updating status: ' . $e->getMessage();
    }
}

// Get submissions
try {
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Get filter parameters
    $status_filter = $_GET['status'] ?? '';
    $search = $_GET['search'] ?? '';
    
    // Build query
    $sql = "SELECT * FROM contact_submissions WHERE 1=1";
    $params = [];
    
    if ($status_filter) {
        $sql .= " AND status = ?";
        $params[] = $status_filter;
    }
    
    if ($search) {
        $sql .= " AND (name LIKE ? OR email LIKE ? OR message LIKE ?)";
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param]);
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $submissions = $stmt->fetchAll();
    
    // Get statistics
    $stats_stmt = $pdo->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_count,
        SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read_count,
        SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END) as replied_count
        FROM contact_submissions");
    $stats = $stats_stmt->fetch();
    
} catch (PDOException $e) {
    $db_error = 'Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form Admin - KraftHaus</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #eee; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center; }
        .stat-number { font-size: 2em; font-weight: bold; color: #007cba; }
        .stat-label { color: #666; margin-top: 5px; }
        .filters { display: flex; gap: 15px; margin-bottom: 20px; align-items: center; flex-wrap: wrap; }
        .filters input, .filters select { padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background: #007cba; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn:hover { opacity: 0.8; }
        .submissions-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .submissions-table th, .submissions-table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .submissions-table th { background: #f8f9fa; font-weight: bold; }
        .submissions-table tr:hover { background: #f5f5f5; }
        .status { padding: 4px 8px; border-radius: 4px; font-size: 0.8em; font-weight: bold; }
        .status-new { background: #d4edda; color: #155724; }
        .status-read { background: #fff3cd; color: #856404; }
        .status-replied { background: #d1ecf1; color: #0c5460; }
        .status-archived { background: #f8d7da; color: #721c24; }
        .message-preview { max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 5% auto; padding: 20px; width: 80%; max-width: 600px; border-radius: 8px; }
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close:hover { color: black; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .form-group textarea { height: 100px; resize: vertical; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Contact Form Submissions</h1>
            <a href="?logout=1" class="btn btn-secondary">Logout</a>
        </div>

        <?php if (isset($update_success)): ?>
            <div class="success"><?php echo htmlspecialchars($update_success); ?></div>
        <?php endif; ?>

        <?php if (isset($update_error)): ?>
            <div class="error"><?php echo htmlspecialchars($update_error); ?></div>
        <?php endif; ?>

        <?php if (isset($db_error)): ?>
            <div class="error"><?php echo htmlspecialchars($db_error); ?></div>
        <?php else: ?>

        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Submissions</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['new_count']; ?></div>
                <div class="stat-label">New</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['read_count']; ?></div>
                <div class="stat-label">Read</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['replied_count']; ?></div>
                <div class="stat-label">Replied</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters">
            <form method="GET" style="display: flex; gap: 10px; align-items: center;">
                <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="status">
                    <option value="">All Status</option>
                    <option value="new" <?php echo $status_filter === 'new' ? 'selected' : ''; ?>>New</option>
                    <option value="read" <?php echo $status_filter === 'read' ? 'selected' : ''; ?>>Read</option>
                    <option value="replied" <?php echo $status_filter === 'replied' ? 'selected' : ''; ?>>Replied</option>
                    <option value="archived" <?php echo $status_filter === 'archived' ? 'selected' : ''; ?>>Archived</option>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="contact-admin.php" class="btn btn-secondary">Clear</a>
            </form>
        </div>

        <!-- Submissions Table -->
        <table class="submissions-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Service</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $submission): ?>
                <tr>
                    <td><?php echo $submission['id']; ?></td>
                    <td><?php echo htmlspecialchars($submission['name']); ?></td>
                    <td><a href="mailto:<?php echo htmlspecialchars($submission['email']); ?>"><?php echo htmlspecialchars($submission['email']); ?></a></td>
                    <td><a href="tel:<?php echo htmlspecialchars($submission['phone']); ?>"><?php echo htmlspecialchars($submission['phone']); ?></a></td>
                    <td><?php echo htmlspecialchars($submission['service']); ?></td>
                    <td class="message-preview"><?php echo htmlspecialchars($submission['message']); ?></td>
                    <td><span class="status status-<?php echo $submission['status']; ?>"><?php echo ucfirst($submission['status']); ?></span></td>
                    <td><?php echo date('M j, Y g:i A', strtotime($submission['created_at'])); ?></td>
                    <td>
                        <button onclick="openModal(<?php echo htmlspecialchars(json_encode($submission)); ?>)" class="btn btn-primary">View/Edit</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (empty($submissions)): ?>
            <p style="text-align: center; margin-top: 40px; color: #666;">No submissions found.</p>
        <?php endif; ?>

        <?php endif; ?>
    </div>

    <!-- Modal -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>View/Edit Submission</h2>
            <form method="POST" id="editForm">
                <input type="hidden" name="submission_id" id="modal_id">
                
                <div class="form-group">
                    <label>Name:</label>
                    <input type="text" id="modal_name" readonly>
                </div>
                
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" id="modal_email" readonly>
                </div>
                
                <div class="form-group">
                    <label>Phone:</label>
                    <input type="tel" id="modal_phone" readonly>
                </div>
                
                <div class="form-group">
                    <label>Service:</label>
                    <input type="text" id="modal_service" readonly>
                </div>
                
                <div class="form-group">
                    <label>Message:</label>
                    <textarea id="modal_message" readonly></textarea>
                </div>
                
                <div class="form-group">
                    <label>Status:</label>
                    <select name="status" id="modal_status">
                        <option value="new">New</option>
                        <option value="read">Read</option>
                        <option value="replied">Replied</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Notes:</label>
                    <textarea name="notes" id="modal_notes" placeholder="Add notes about this submission..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Submitted:</label>
                    <input type="text" id="modal_created_at" readonly>
                </div>
                
                <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
            </form>
        </div>
    </div>

    <script>
        // Modal functionality
        const modal = document.getElementById('modal');
        const closeBtn = document.querySelector('.close');

        function openModal(submission) {
            document.getElementById('modal_id').value = submission.id;
            document.getElementById('modal_name').value = submission.name;
            document.getElementById('modal_email').value = submission.email;
            document.getElementById('modal_phone').value = submission.phone;
            document.getElementById('modal_service').value = submission.service;
            document.getElementById('modal_message').value = submission.message;
            document.getElementById('modal_status').value = submission.status;
            document.getElementById('modal_notes').value = submission.notes || '';
            document.getElementById('modal_created_at').value = new Date(submission.created_at).toLocaleString();
            
            modal.style.display = 'block';
        }

        closeBtn.onclick = function() {
            modal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
