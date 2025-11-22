<?php
/**
 * Admin - Edit User
 * London Community Park Christmas Event Booking System
 */

$pageTitle = 'Edit User';
require_once '../includes/header.php';

// Require admin access
requireLogin();
requireAdmin();

// Get user ID
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($userId <= 0) {
    $_SESSION['error'] = 'Invalid user ID';
    redirect(SITE_URL . '/admin/users.php');
}

// Get user
$user = getUserById($pdo, $userId);

if (!$user) {
    $_SESSION['error'] = 'User not found';
    redirect(SITE_URL . '/admin/users.php');
}

$errors = [];

// Process form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        // Sanitize input
        $firstName = sanitize($_POST['first_name'] ?? '');
        $lastName = sanitize($_POST['last_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        $role = sanitize($_POST['role'] ?? 'customer');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $newPassword = $_POST['new_password'] ?? '';
        
        // Validation
        if (empty($firstName)) $errors[] = 'First name is required';
        if (empty($lastName)) $errors[] = 'Last name is required';
        if (empty($email)) $errors[] = 'Email is required';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address';
        
        // Check if email is taken by another user
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
        $stmt->execute([$email, $userId]);
        if ($stmt->fetch()) {
            $errors[] = 'Email is already in use by another account';
        }
        
        // Password validation if provided
        if (!empty($newPassword) && strlen($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters';
        }
        
        if (empty($errors)) {
            try {
                // Update user
                $sql = "UPDATE users SET 
                        first_name = ?, 
                        last_name = ?, 
                        email = ?, 
                        phone = ?, 
                        address = ?, 
                        role = ?, 
                        is_active = ?";
                $params = [$firstName, $lastName, $email, $phone, $address, $role, $isActive];
                
                // Add password update if provided
                if (!empty($newPassword)) {
                    $sql .= ", password = ?";
                    $params[] = password_hash($newPassword, PASSWORD_DEFAULT);
                }
                
                $sql .= " WHERE user_id = ?";
                $params[] = $userId;
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                $_SESSION['success'] = 'User updated successfully!';
                redirect(SITE_URL . '/admin/users.php');
                
            } catch (PDOException $e) {
                $errors[] = 'Update failed. Please try again.';
            }
        }
        
        // Refresh user data
        $user = getUserById($pdo, $userId);
    }
}

// Generate CSRF token
$csrfToken = generateCsrfToken();
?>

<!-- Page Header -->
<section class="hero" style="padding: 40px 20px;">
    <div class="container">
        <h1>✏️ Edit User</h1>
        <p>Editing: <?php echo sanitize($user['first_name'] . ' ' . $user['last_name']); ?></p>
    </div>
</section>

<div class="container">
    
    <?php echo displayMessage(); ?>
    
    <div style="max-width: 700px; margin: 0 auto;">
        <div class="card">
            <div class="card-header">
                <h2>📝 User Information</h2>
                <p>Update user details</p>
            </div>
            <div class="card-body">
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <span class="alert-icon">✕</span>
                        <ul style="margin: 0; padding-left: 20px;">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo sanitize($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <!-- User Info Summary -->
                <div style="background: var(--frost-blue); padding: 15px; border-radius: 10px; margin-bottom: 25px; display: flex; align-items: center; gap: 20px;">
                    <div style="width: 60px; height: 60px; background: <?php echo $user['role'] === 'admin' ? 'var(--christmas-red)' : 'var(--christmas-green)'; ?>; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2rem;">
                        <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                    </div>
                    <div>
                        <strong>Username:</strong> @<?php echo sanitize($user['username']); ?><br>
                        <strong>User ID:</strong> #<?php echo $user['user_id']; ?><br>
                        <strong>Registered:</strong> <?php echo formatDate($user['created_at']); ?>
                    </div>
                </div>
                
                <form method="POST" action="" data-validate>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name" class="required">First Name</label>
                            <input type="text" 
                                   id="first_name" 
                                   name="first_name" 
                                   class="form-control" 
                                   value="<?php echo sanitize($user['first_name']); ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name" class="required">Last Name</label>
                            <input type="text" 
                                   id="last_name" 
                                   name="last_name" 
                                   class="form-control" 
                                   value="<?php echo sanitize($user['last_name']); ?>"
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" 
                               class="form-control" 
                               value="<?php echo sanitize($user['username']); ?>"
                               disabled>
                        <small style="color: #666;">Username cannot be changed</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="required">Email Address</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-control" 
                               value="<?php echo sanitize($user['email']); ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               class="form-control" 
                               value="<?php echo sanitize($user['phone']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" 
                                  name="address" 
                                  class="form-control" 
                                  rows="3"><?php echo sanitize($user['address']); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="role" class="required">User Role</label>
                            <select id="role" name="role" class="form-control" required>
                                <option value="customer" <?php echo $user['role'] === 'customer' ? 'selected' : ''; ?>>
                                    Customer
                                </option>
                                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>
                                    Administrator
                                </option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Account Status</label>
                            <div style="padding: 12px 0;">
                                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                                    <input type="checkbox" 
                                           name="is_active" 
                                           <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                                    <span>Account is Active</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <hr style="margin: 25px 0;">
                    
                    <h4 style="color: var(--christmas-green); margin-bottom: 15px;">🔒 Change Password (Optional)</h4>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" 
                               id="new_password" 
                               name="new_password" 
                               class="form-control" 
                               placeholder="Leave blank to keep current password">
                        <small style="color: #666;">Minimum 6 characters. Leave blank if you don't want to change the password.</small>
                    </div>
                    
                    <div style="margin-top: 25px; display: flex; gap: 15px;">
                        <button type="submit" class="btn btn-primary">
                            💾 Save Changes
                        </button>
                        <a href="<?php echo SITE_URL; ?>/admin/users.php" class="btn btn-gold">
                            ← Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
</div>

<?php require_once '../includes/footer.php'; ?>