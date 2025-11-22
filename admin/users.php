<?php
/**
 * Admin - User Management (CRUD)
 * London Community Park Christmas Event Booking System
 */

$pageTitle = 'Manage Users';
require_once '../includes/header.php';

// Require admin access
requireLogin();
requireAdmin();

// Handle search and filters
$search = sanitize($_GET['search'] ?? '');
$roleFilter = sanitize($_GET['role'] ?? '');

// Build query
$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR username LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

if (!empty($roleFilter)) {
    $sql .= " AND role = ?";
    $params[] = $roleFilter;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// Count by role
$stmt = $pdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
$roleCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<!-- Page Header -->
<section class="hero" style="padding: 40px 20px;">
    <div class="container">
        <h1>👥 User Management</h1>
        <p>View, add, edit, and delete users (CRUD Operations)</p>
    </div>
</section>

<div class="container">
    
    <?php echo displayMessage(); ?>
    
    <!-- Statistics -->
    <div class="stats-grid" style="margin-bottom: 30px;">
        <div class="stat-card">
            <div class="stat-number"><?php echo count($users); ?></div>
            <div class="stat-label">Total Users (Filtered)</div>
        </div>
        <div class="stat-card green">
            <div class="stat-number"><?php echo $roleCounts['customer'] ?? 0; ?></div>
            <div class="stat-label">Customers</div>
        </div>
        <div class="stat-card gold">
            <div class="stat-number"><?php echo $roleCounts['admin'] ?? 0; ?></div>
            <div class="stat-label">Administrators</div>
        </div>
    </div>
    
    <!-- Search and Filter -->
    <div class="card" style="margin-bottom: 30px;">
        <div class="card-body">
            <form method="GET" action="" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
                <div class="form-group" style="flex: 1; min-width: 200px; margin-bottom: 0;">
                    <label for="search">Search Users</label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           class="form-control" 
                           placeholder="Name, email, or username..."
                           value="<?php echo sanitize($search); ?>">
                </div>
                
                <div class="form-group" style="min-width: 150px; margin-bottom: 0;">
                    <label for="role">Filter by Role</label>
                    <select id="role" name="role" class="form-control">
                        <option value="">All Roles</option>
                        <option value="customer" <?php echo $roleFilter === 'customer' ? 'selected' : ''; ?>>Customers</option>
                        <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admins</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">🔍 Search</button>
                <a href="<?php echo SITE_URL; ?>/admin/users.php" class="btn btn-gold">Clear</a>
                <a href="<?php echo SITE_URL; ?>/admin/add_user.php" class="btn btn-success">➕ Add New User</a>
            </form>
        </div>
    </div>
    
    <!-- Users Table -->
    <div class="card">
        <div class="card-header">
            <h2>📋 Users List</h2>
        </div>
        <div class="card-body">
            <?php if (empty($users)): ?>
                <p style="text-align: center; padding: 40px; color: #666;">
                    No users found matching your criteria.
                </p>
            <?php else: ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>#<?php echo $user['user_id']; ?></td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div style="width: 40px; height: 40px; background: <?php echo $user['role'] === 'admin' ? 'var(--christmas-red)' : 'var(--christmas-green)'; ?>; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.8rem;">
                                                <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <strong><?php echo sanitize($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                                                <br><small style="color: #666;">@<?php echo sanitize($user['username']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo sanitize($user['email']); ?></td>
                                    <td><?php echo sanitize($user['phone'] ?: '-'); ?></td>
                                    <td>
                                        <span class="badge <?php echo $user['role'] === 'admin' ? 'badge-danger' : 'badge-success'; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $user['is_active'] ? 'badge-success' : 'badge-warning'; ?>">
                                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($user['created_at']); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="<?php echo SITE_URL; ?>/admin/edit_user.php?id=<?php echo $user['user_id']; ?>" 
                                               class="btn btn-sm btn-primary" title="Edit">
                                                ✏️ Edit
                                            </a>
                                            <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                                <a href="<?php echo SITE_URL; ?>/admin/delete_user.php?id=<?php echo $user['user_id']; ?>" 
                                                   class="btn btn-sm btn-danger btn-delete" 
                                                   data-confirm="Are you sure you want to delete this user? This action cannot be undone."
                                                   title="Delete">
                                                    🗑️ Delete
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div style="margin-top: 20px; text-align: center; color: #666;">
                    Showing <?php echo count($users); ?> user(s)
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Back Button -->
    <div style="margin-top: 30px;">
        <a href="<?php echo SITE_URL; ?>/admin/index.php" class="btn btn-gold">
            ← Back to Dashboard
        </a>
    </div>
    
</div>

<?php require_once '../includes/footer.php'; ?>