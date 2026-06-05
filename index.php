<?php
// ============================================================
// index.php – Login & Registration (Page 1 of 3)
// ============================================================
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Already logged in? Go straight to the dashboard
if (isLoggedIn()) {
    header("Location: pages/dashboard.php");
    exit;
}

$errors = [];
$success = '';
$tab = 'login'; // which tab to show by default

// ---- Handle LOGIN form submission --------------------------
if (isset($_POST['login'])) {
    $tab = 'login';
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    // Server-side validation
    if (empty($email))    $errors[] = "Email is required.";
    if (empty($password)) $errors[] = "Password is required.";

    if (empty($errors)) {
        // Look up the user by email
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Verify the password against the stored hash
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: pages/dashboard.php");
                exit;
            } else {
                $errors[] = "Incorrect password.";
            }
        } else {
            $errors[] = "No account found with that email.";
        }
    }
}

// ---- Handle REGISTER form submission -----------------------
if (isset($_POST['register'])) {
    $tab      = 'register';
    $username = trim($_POST['username']         ?? '');
    $email    = trim($_POST['reg_email']        ?? '');
    $password = trim($_POST['reg_password']     ?? '');
    $confirm  = trim($_POST['confirm_password'] ?? '');

    // Server-side validation
    if (empty($username)) $errors[] = "Username is required.";
    if (empty($email))    $errors[] = "Email is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Enter a valid email address.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
    if ($password !== $confirm) $errors[] = "Passwords do not match.";

    if (empty($errors)) {
        // Check if email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $check->bind_param("ss", $email, $username);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $errors[] = "That email or username is already taken.";
        } else {
            // Hash the password securely before storing
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $ins = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $ins->bind_param("sss", $username, $email, $hashed);
            $ins->execute();
            $success = "Account created! You can now log in.";
            $tab = 'login';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Expense Tracker – Login</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">

        <!-- Logo / Heading -->
        <div class="text-center mb-4">
            <div class="brand-icon">💰</div>
            <h3 class="fw-800 mb-0" style="color:#4f46e5;font-weight:800;">ExpenseTracker</h3>
            <p class="text-muted small">Manage your money with ease</p>
        </div>

        <!-- Tab buttons -->
        <ul class="nav nav-pills nav-fill mb-4" id="authTab">
            <li class="nav-item">
                <a class="nav-link <?= $tab === 'login'    ? 'active' : '' ?>" href="#login"    data-bs-toggle="pill">Login</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $tab === 'register' ? 'active' : '' ?>" href="#register" data-bs-toggle="pill">Register</a>
            </li>
        </ul>

        <!-- Error / Success alerts -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger py-2">
                <?php foreach ($errors as $e): ?>
                    <div><?= sanitize($e) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success py-2"><?= sanitize($success) ?></div>
        <?php endif; ?>

        <div class="tab-content">

            <!-- ===== LOGIN TAB ===== -->
            <div class="tab-pane fade <?= $tab === 'login' ? 'show active' : '' ?>" id="login">
                <form method="POST" id="login-form" novalidate>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="you@email.com"
                               value="<?= sanitize($_POST['email'] ?? '') ?>" required>
                        <div class="invalid-feedback">Please enter your email.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password</label>
                        <input type="password" name="password" id="password" class="form-control"
                               placeholder="••••••••" required>
                        <div class="invalid-feedback">Please enter your password.</div>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary w-100 py-2 mt-1">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Log In
                    </button>
                </form>
                <p class="text-center text-muted small mt-3 mb-0">
                    Demo: <strong>demo@example.com</strong> / <strong>password</strong>
                </p>
            </div>

            <!-- ===== REGISTER TAB ===== -->
            <div class="tab-pane fade <?= $tab === 'register' ? 'show active' : '' ?>" id="register">
                <form method="POST" id="register-form" novalidate>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Username</label>
                        <input type="text" name="username" class="form-control" placeholder="johndoe"
                               value="<?= sanitize($_POST['username'] ?? '') ?>" required>
                        <div class="invalid-feedback">Username is required.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address</label>
                        <input type="email" name="reg_email" class="form-control" placeholder="you@email.com"
                               value="<?= sanitize($_POST['reg_email'] ?? '') ?>" required>
                        <div class="invalid-feedback">Please enter a valid email.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password <small class="text-muted">(min 6 chars)</small></label>
                        <input type="password" name="reg_password" id="reg_password" class="form-control"
                               placeholder="••••••••" required>
                        <div class="invalid-feedback">Password is too short.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Confirm Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control"
                               placeholder="••••••••" required>
                        <div class="invalid-feedback">Passwords do not match.</div>
                    </div>
                    <button type="submit" name="register" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-person-plus me-1"></i> Create Account
                    </button>
                </form>
            </div>

        </div><!-- /tab-content -->
    </div><!-- /auth-card -->
</div><!-- /auth-wrapper -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="js/app.js"></script>
</body>
</html>