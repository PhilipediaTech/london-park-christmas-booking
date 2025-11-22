<?php
/**
 * User Profile Page
 * London Community Park Christmas Event Booking System
 */

$pageTitle = 'My Profile';
require_once '../includes/header.php';

// Require user login
requireLogin();

// Get user information
$user = getUserById($pdo, $_SESSION['user_id']);
$errors = [];

// Process profile update form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        // Handle profile update
        if (isset($_POST['update_profile'])) {
            $data = [
                'first_name' => sanitize($_POST['first_name'] ?? ''),
                'last_name' => sanitize($_POST['last_name'] ?? ''),
                'email' => sanitize($_POST['email'] ?? ''),
                'phone' => sanitize($_POST['phone'] ?? ''),
                'address' => sanitize($_POST['address'] ?? '')
            ];
            
            // Validation
            if (empty($data['first_name'])) $errors[] = 'First name is required';
            if (empty($data['last_name'])) $errors[] = 'Last name is required';
            if (empty($data['email'])) $errors[] = 'Email is required';
            elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address';
            
            if (empty($errors)) {
                $result = updateProfile($pdo, $_SESSION['user_id'], $data);
                if ($result['success']) {
                    $_SESSION['success'] = $result['message'];
                    redirect(SITE_URL . '/user/profile.php');
                } else {
                    $errors[] = $result['message'];
                }
            }
        }
        
        // Handle password change
        if (isset($_POST['change_password'])) {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($currentPassword)) $errors[] = 'Current password is required';
            if (empty($newPassword)) $errors[] = 'New password is required';
            elseif (strlen($newPassword) < 6) $errors[] = 'New password must be at least 6 characters';
            if ($newPassword !== $confirmPassword) $errors[] = 'New passwords do not match';
            
            if (empty($errors)) {
                $result = updatePassword($pdo, $_SESSION['user_id'], $currentPassword, $newPassword);
                if ($result['success']) {
                    $_SESSION['success'] = $result['message'];
                    redirect(SITE_URL . '/user/profile.php');
                } else {
                    $errors[] = $result['message'];
                }
            }
        }
        
        // Refresh user data after update
        $user = getUserById($pdo, $_SESSION['user_id']);
    }
}

// Generate CSRF token
$csrfToken = generateCsrfToken();
?>

<!-- Page Header -->
<section class="hero" style="padding: 40px 20px;">
    <div class="container">
        <h1>👤 My Profile</h1>
        <p>Manage your account information</p>
    </div>
</section>

<div class="container">
    
    <?php echo displayMessage(); ?>
    
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
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px;">
        
        <!-- Profile Information -->
        <div class="card">
            <div class="card-header">
                <h2>📝 Profile Information</h2>
                <p>Update your personal details</p>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <input type="hidden" name="update_profile" value="1">
                    
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
                    
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" 
                               class="form-control" 
                               value="<?php echo sanitize($user['username']); ?>"
                               disabled>
                        <small style="color: #666;">Username cannot be changed</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        💾 Save Changes
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Change Password -->
        <div class="card">
            <div class="card-header">
                <h2>🔒 Change Password</h2>
                <p>Update your security credentials</p>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <input type="hidden" name="change_password" value="1">
                    
                    <div class="form-group">
                        <label for="current_password" class="required">Current Password</label>
                        <input type="password" 
                               id="current_password" 
                               name="current_password" 
                               class="form-control" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password" class="required">New Password</label>
                        <input type="password" 
                               id="new_password" 
                               name="new_password" 
                               class="form-control" 
                               required>
                        <small style="color: #666;">Minimum 6 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="required">Confirm New Password</label>
                        <input type="password" 
                               id="confirm_password" 
                               name="confirm_password" 
                               class="form-control" 
                               required>
                    </div>
                    
                    <button type="submit" class="btn btn-success">
                        🔐 Update Password
                    </button>
                </form>
            </div>
        </div>
        
    </div>
    
    <!-- Back to Dashboard -->
    <div style="margin-top: 30px;">
        <a href="<?php echo SITE_URL; ?>/user/dashboard.php" class="btn btn-gold">
            ← Back to Dashboard
        </a>
    </div>
    
</div>

<?php require_once '../includes/footer.php'; ?>