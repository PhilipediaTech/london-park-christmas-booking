<?php
/**
 * User Registration Page
 * London Community Park Christmas Event Booking System
 */

$pageTitle = 'Register';
require_once 'includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(SITE_URL . '/index.php');
}

$errors = [];
$old = [];

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        // Sanitize and validate input
        $old['username'] = sanitize($_POST['username'] ?? '');
        $old['email'] = sanitize($_POST['email'] ?? '');
        $old['first_name'] = sanitize($_POST['first_name'] ?? '');
        $old['last_name'] = sanitize($_POST['last_name'] ?? '');
        $old['phone'] = sanitize($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($old['username'])) {
            $errors[] = 'Username is required';
        } elseif (strlen($old['username']) < 3) {
            $errors[] = 'Username must be at least 3 characters';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $old['username'])) {
            $errors[] = 'Username can only contain letters, numbers, and underscores';
        }
        
        if (empty($old['email'])) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address';
        }
        
        if (empty($old['first_name'])) {
            $errors[] = 'First name is required';
        }
        
        if (empty($old['last_name'])) {
            $errors[] = 'Last name is required';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters';
        }
        
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match';
        }
        
        // If no validation errors, attempt registration
        if (empty($errors)) {
            $result = registerUser($pdo, [
                'username' => $old['username'],
                'email' => $old['email'],
                'password' => $password,
                'first_name' => $old['first_name'],
                'last_name' => $old['last_name'],
                'phone' => $old['phone'],
                'role' => 'customer'
            ]);
            
            if ($result['success']) {
                $_SESSION['success'] = $result['message'];
                redirect(SITE_URL . '/login.php');
            } else {
                $errors[] = $result['message'];
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
            <h2>📝 Create Your Account</h2>
            <p>Join us for magical Christmas events!</p>
        </div>
        
        <div class="card-body">
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
            
            <form method="POST" action="" data-validate>
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name" class="required">First Name</label>
                        <input type="text" 
                               id="first_name" 
                               name="first_name" 
                               class="form-control" 
                               placeholder="Enter your first name"
                               value="<?php echo $old['first_name'] ?? ''; ?>"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name" class="required">Last Name</label>
                        <input type="text" 
                               id="last_name" 
                               name="last_name" 
                               class="form-control" 
                               placeholder="Enter your last name"
                               value="<?php echo $old['last_name'] ?? ''; ?>"
                               required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="username" class="required">Username</label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           class="form-control" 
                           placeholder="Choose a username"
                           value="<?php echo $old['username'] ?? ''; ?>"
                           required>
                    <small style="color: #666;">Letters, numbers, and underscores only</small>
                </div>
                
                <div class="form-group">
                    <label for="email" class="required">Email Address</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-control" 
                           placeholder="Enter your email"
                           value="<?php echo $old['email'] ?? ''; ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" 
                           id="phone" 
                           name="phone" 
                           class="form-control" 
                           placeholder="Enter your phone number (optional)"
                           value="<?php echo $old['phone'] ?? ''; ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password" class="required">Password</label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control" 
                               placeholder="Create a password"
                               required>
                        <small style="color: #666;">Minimum 6 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password" class="required">Confirm Password</label>
                        <input type="password" 
                               id="confirm_password" 
                               name="confirm_password" 
                               class="form-control" 
                               placeholder="Confirm your password"
                               required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="terms" required>
                        <span>I agree to the Terms & Conditions and Privacy Policy</span>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    🎄 Create Account
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="<?php echo SITE_URL; ?>/login.php">Login here</a></p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>