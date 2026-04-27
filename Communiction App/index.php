<?php
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Communication App</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="landing-page">
    <main class="landing-shell">
        <section class="hero-card">
            <div class="hero-copy">
                <p class="eyebrow">Simple Messaging</p>
                <h1>Communication App</h1>
                <p class="hero-text">
                    Register, sign in, and chat in a clean two-person dashboard. Demo accounts are ready if you want to test immediately.
                </p>
                <div class="demo-users">
                    <span>Demo 1: <strong>person1</strong> / <strong>password123</strong></span>
                    <span>Demo 2: <strong>person2</strong> / <strong>password123</strong></span>
                </div>
            </div>
        </section>

        <section class="auth-card">
            <?php if ($error === 'invalid'): ?>
                <div class="notice error-message">Invalid username or password.</div>
            <?php elseif ($error === 'database'): ?>
                <div class="notice error-message">Database error. Please try again.</div>
            <?php elseif ($error === 'username_taken'): ?>
                <div class="notice error-message">That username is already in use.</div>
            <?php elseif ($error === 'invalid_registration'): ?>
                <div class="notice error-message">Registration failed. Check your details and try again.</div>
            <?php elseif ($error === 'csrf'): ?>
                <div class="notice error-message">Your form expired. Please try again.</div>
            <?php elseif ($error === 'login_required'): ?>
                <div class="notice error-message">Please sign in to continue.</div>
            <?php endif; ?>

            <?php if ($success === 'registered'): ?>
                <div class="notice success-message">Registration successful. You can log in now.</div>
            <?php endif; ?>

            <div class="auth-grid">
                <form action="login.php" method="POST" class="auth-panel">
                    <h2>Login</h2>
                    <div class="form-group">
                        <label for="login-username">Username</label>
                        <input type="text" id="login-username" name="username" required autocomplete="username">
                    </div>
                    <div class="form-group">
                        <label for="login-password">Password</label>
                        <input type="password" id="login-password" name="password" required autocomplete="current-password">
                    </div>
                    <button type="submit">Sign In</button>
                </form>

                <form action="register.php" method="POST" class="auth-panel">
                    <h2>Register</h2>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="form-group">
                        <label for="register-name">Full Name</label>
                        <input type="text" id="register-name" name="full_name" required autocomplete="name">
                    </div>
                    <div class="form-group">
                        <label for="register-username">Username</label>
                        <input type="text" id="register-username" name="username" required autocomplete="username">
                    </div>
                    <div class="form-group">
                        <label for="register-password">Password</label>
                        <input type="password" id="register-password" name="password" required autocomplete="new-password">
                    </div>
                    <button type="submit" class="secondary-button">Create Account</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
