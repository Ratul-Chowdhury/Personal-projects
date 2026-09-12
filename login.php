<?php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header('Location: admin/dashboard.php');
    exit;
}

$identifier = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($identifier) || empty($password)) {
        $error = 'Please fill in both fields.';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, full_name, email, username, password_hash, role FROM users WHERE email = ? OR username = ?");
        mysqli_stmt_bind_param($stmt, "ss", $identifier, $identifier);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_activity'] = time();
            $_SESSION['logged_in_at'] = time();

            if ($user['role'] === 'customer') {
                header('Location: rooms.php');
            } else {
                header('Location: admin/dashboard.php');
            }
            exit;
        } else {
            $error = 'Invalid email/username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Log In - Grand Hotel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="support-bar">For Bookings, Support, or Password Recovery, Call: +1-800-555-0199</div>
    <div class="header">
        <h1>GRAND HOTEL</h1>
    </div>

    <div class="container">
        <div class="card auth-card">
            <h2>Log In</h2>

            <?php if ($error) : ?>
                <p class="error"><?= e($error) ?></p>
            <?php endif; ?>

            <form method="post" action="login.php">
                <p>
                    <label>Username or Email</label><br>
                    <input type="text" name="identifier" value="<?= e($identifier) ?>" required>
                </p>
                <p>
                    <label>Password</label><br>
                    <input type="password" name="password" required>
                </p>
                <p>
                    <button type="submit">Log In</button>
                </p>
            </form>

            <div class="auth-divider">or</div>

            <form method="get" action="signup.php" style="margin:0;">
                <button type="submit" class="secondary-btn">Register New Account</button>
            </form>

            <p class="auth-hint">Staff: admin / receptionist / roomservice (Password: 123)</p>
        </div>
    </div>
</body>
</html>