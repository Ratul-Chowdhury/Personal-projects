<?php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header('Location: admin/dashboard.php');
    exit;
}

$errors = [];
$full_name = '';
$email = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    if ($full_name === '') {
        $errors[] = 'Full name is required.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if ($username === '') {
        $errors[] = 'Username is required.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    if ($password !== $confirm) {
        $errors[] = 'The two passwords do not match.';
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? OR username = ?");
        mysqli_stmt_bind_param($stmt, 'ss', $email, $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $existing = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($existing) {
            $checkStmt = mysqli_prepare($conn, "SELECT email, username FROM users WHERE email = ? OR username = ?");
            mysqli_stmt_bind_param($checkStmt, 'ss', $email, $username);
            mysqli_stmt_execute($checkStmt);
            $checkResult = mysqli_stmt_get_result($checkStmt);
            while ($row = mysqli_fetch_assoc($checkResult)) {
                if ($row['email'] === $email) {
                    $errors[] = 'This email is already registered.';
                }
                if ($row['username'] === $username) {
                    $errors[] = 'This username is already taken.';
                }
            }
            mysqli_stmt_close($checkStmt);
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "INSERT INTO users (full_name, email, username, password_hash, role) VALUES (?, ?, ?, ?, ?)");
        $role = 'customer';
        mysqli_stmt_bind_param($stmt, 'sssss', $full_name, $email, $username, $hash, $role);
        mysqli_stmt_execute($stmt);
        $new_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);

        // UPDATED: Redirect to login page instead of auto-logging in
        header('Location: login.php?signup=success');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sign Up - Grand Hotel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="support-bar">For Bookings, Support, or Password Recovery, Call: +1-800-555-0199</div>
    <div class="header">
        <h1>GRAND HOTEL</h1>
    </div>

    <div class="container">
        <div class="card auth-card">
            <h2>Create an Account</h2>

            <?php if (!empty($errors)): ?>
                <ul class="error-list">
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <form method="post" action="signup.php">
                <p>
                    <label>Full Name</label><br>
                    <input type="text" name="full_name" value="<?= e($full_name) ?>" required>
                </p>
                <p>
                    <label>Email</label><br>
                    <input type="email" name="email" value="<?= e($email) ?>" required>
                </p>
                <p>
                    <label>Username</label><br>
                    <input type="text" name="username" value="<?= e($username) ?>" required>
                </p>
                <p>
                    <label>Password (min 8 characters)</label><br>
                    <input type="password" name="password" required>
                </p>
                <p>
                    <label>Confirm Password</label><br>
                    <input type="password" name="confirm_password" required>
                </p>
                <p>
                    <button type="submit">Sign Up</button>
                </p>
            </form>

            <div class="auth-divider">or</div>

            <form method="get" action="login.php" style="margin:0;">
                <button type="submit" class="secondary-btn">Already have an account? Log In</button>
            </form>
        </div>
    </div>
</body>
</html>