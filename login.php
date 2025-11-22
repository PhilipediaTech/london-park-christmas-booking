<?php
/**
 * User Login Page
 * London Community Park Christmas Event Booking System
 */

$pageTitle = 'Login';
require_once 'includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect(SITE_URL . '/admin/index.php');
    } else {
        redirect(SITE_URL . '/user/dashboard.php');
    }
}

$error = '';
$oldUsername = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Invalid form submission. Please try again.';
    } else {
        $oldUsername = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validation
        if (empty($oldUsername) || empty($password)) {
            $error = 'Please enter both username/email and password';
        } else {
            // Attempt login
            $result = loginUser($pdo, $oldUsername, $password);
            
            if ($result['success']) {
                $_SESSION['success'] = 'Welcome back, ' . $_SESSION['first_name'] . '!';
                
                // Redirect based on role
                if ($result['role'] === 'admin') {
                    redirect(SITE_URL . '/admin/index.php');
                } else {
                    redirect(SITE_URL . '/user/dashboard.php');
                }
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Generate CSRF token
$csrfToken = generateCsrfToken();
?>

<div class="auth-container">
    <div class="card auth-card">
        <div class="card-header">
            <h2>🔑 Welcome Back!</h2>
            <p>Login to your account</p>
        </div>
        
        <div class="card-body">
            <?php echo displayMessage(); ?>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <span class="alert-icon">✕</span>
                    <?php echo sanitize($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" data-validate>
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
                <div class="form-group">
                    <label for="username" class="required">Username or Email</label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           class="form-control" 
                           placeholder="Enter your username or email"
                           value="<?php echo sanitize($oldUsername); ?>"
                           required
                           autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password" class="required">Password</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control" 
                           placeholder="Enter your password"
                           required>
                </div>
                
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    🎄 Login
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="<?php echo SITE_URL; ?>/register.php">Register here</a></p>
            </div>
        </div>
    </div>
</div>

<!-- Demo Credentials Info -->
<div class="container" style="max-width: 450px; margin-top: 20px;">
    <div class="card">
        <div class="card-body" style="text-align: center; padding: 15px;">
            <p style="margin-bottom: 10px;"><strong>🎅 Demo Credentials:</strong></p>
            <p style="font-size: 0.9rem; color: #666;">
                <strong>Admin:</strong> admin / admin123<br>
                <strong>User:</strong> Register a new account
            </p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>